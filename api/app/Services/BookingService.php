<?php

namespace App\Services;

use App\Enums\PromotionType;
use App\Models\Hotel;
use App\Models\Roomtype;
use App\Models\RoomRate;
use App\Models\Promotion;
use App\Models\ChildAgePolicy;
use App\Models\RoomPricingPolicy;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BookingService
{
    /**
     * Tìm các tổ hợp phòng còn trống và áp dụng khuyến mãi.
     *
     * @param int $hotelId
     * @param string $checkIn
     * @param string $checkOut
     * @param int $adults
     * @param int $children
     * @param array $childrenAges
     * @param string $language
     * @return array
     */
    public function findRoomCombinations($hotelId, $checkIn, $checkOut, $adults, $children, $childrenAges = [], $language = 'vi')
    {
        \Log::info("🔍 BookingService->findRoomCombinations called", [
            'hotelId' => $hotelId,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'language' => $language
        ]);

        // Set locale for translations
        app()->setLocale($language);

        // Sử dụng hotelId để tìm khách sạn
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return []; // Trả về mảng rỗng nếu không tìm thấy khách sạn
        }

        // Lấy chính sách độ tuổi trẻ em của khách sạn
        $childAgePolicy = $this->getChildAgePolicy($hotelId);

        // Phân tích độ tuổi trẻ em
        $childAgeAnalysis = $this->analyzeChildrenAges($childrenAges, $childAgePolicy);

        // 1. Tìm tất cả các loại phòng có thể chứa khách (không cần phải đủ một phòng)
        $roomTypes = $hotel->roomtypes()
            ->where('adult_capacity', '>', 0) // Chỉ cần có thể chứa ít nhất 1 người
            ->get();

        \Log::info("🏨 Found {$roomTypes->count()} room types for hotel {$hotelId}");
        foreach ($roomTypes as $roomType) {
            \Log::info("🏨 Room Type: {$roomType->id} - {$roomType->title} (capacity: {$roomType->adult_capacity}, extra bed: " . ($roomType->is_extra_bed_available ? 'YES' : 'NO') . ")");
        }
        // Fallback: Return sample data if no valid room types found or corrupted data
        // if ($roomTypes->isEmpty() || $roomTypes->every(function($rt) { return empty(trim($rt->name)); })) {
        //     return $this->getSampleRoomCombinations($hotelId, $checkIn, $checkOut, $adults, $children);
        // }

        $availableCombinations = [];
        $numberOfNights = (new Carbon($checkIn))->diffInDays($checkOut);

        // 2. Duyệt qua từng loại phòng để tìm các tổ hợp khả thi
        foreach ($roomTypes as $roomType) {
            // Kiểm tra số lượng phòng trống cho toàn bộ kỳ lưu trú
            $availableCount = $this->checkAvailability($roomType->id, $checkIn, $checkOut);
            if ($availableCount <= 0) {
                continue; // Bỏ qua nếu không có phòng trống
            }

            // Tính giá cơ bản cho toàn bộ kỳ lưu trú (giá cho 1 phòng)
            $basePrice = $this->calculateBaseRate($roomType->id, $checkIn, $checkOut);

            // Kiểm tra nếu giá không đủ cho tất cả các đêm
            if ($basePrice <= 0) {
                continue;
            }

            // Lấy các khuyến mãi hợp lệ
            $promotions = $this->getApplicablePromotions($roomType, $checkIn, $checkOut);

            // Lấy chính sách giá phòng
            $pricingPolicy = $this->getRoomPricingPolicy($roomType->id);

            // Tính số phòng tối thiểu cần thiết để chứa đủ khách
            // Tính toán người lớn thực tế (bao gồm trẻ em được tính như người lớn)
            $effectiveAdults = $adults + $childAgeAnalysis['children_as_adults'];
            $effectiveChildren = $childAgeAnalysis['free_children'] + $childAgeAnalysis['surcharge_children'];
            $totalGuests = $effectiveAdults + $effectiveChildren;

            // Sử dụng effective capacity từ pricing policy thay vì adult + child capacity
            // Vì child_capacity là dành cho trẻ em chứ không phải người lớn thêm
            $effectiveCapacityPerRoom = $this->calculateEffectiveCapacity($pricingPolicy, 1, $roomType);
            $minRoomsNeeded = ceil($totalGuests / $effectiveCapacityPerRoom);

            // Tính số phòng tối đa có thể hiển thị
            $maxRoomsToShow = min($availableCount, max($minRoomsNeeded, 5));

            // Bắt đầu từ số phòng tối thiểu cần thiết
            $startQuantity = max(1, $minRoomsNeeded);

            for ($quantity = $startQuantity; $quantity <= $maxRoomsToShow; $quantity++) {
                // Tính phụ thu cho quantity phòng này
                $additionalCharges = $this->calculateAdditionalCharges(
                    $effectiveAdults,
                    $childAgeAnalysis,
                    $quantity,
                    $pricingPolicy,
                    $numberOfNights,
                    $roomType
                );

                // Debug log pricing calculation
                \Log::info("🏨 Pricing Debug - Room: {$roomType->title}, Base Price: {$basePrice}, Quantity: {$quantity}, Effective Adults: {$effectiveAdults}, Additional Charges: " . json_encode($additionalCharges));

                // Tạo pricing breakdown theo structure mới
                $pricingBreakdown = $this->createPricingBreakdown(
                    $basePrice,
                    $quantity,
                    $numberOfNights,
                    $additionalCharges,
                    $childAgeAnalysis
                );

                // Debug log room configuration
                \Log::info("🏨 Room Debug - ID: {$roomType->id}, Name: {$roomType->title}, Adult Capacity: {$roomType->adult_capacity}, Extra Bed: " . ($roomType->is_extra_bed_available ? 'YES' : 'NO') . ", Adults: {$adults}, Children: {$children}");
                \Log::info("🏨 Pricing Policy - Room: {$roomType->title}, Base Occupancy: {$pricingPolicy->base_occupancy}, Additional Adult Price: {$pricingPolicy->additional_adult_price}");

                // Xây dựng chi tiết tổ hợp phòng với translation
                $combinationDetails = [
                    'room_type' => [
                        'id' => $roomType->id,
                        'name' => $roomType->getTranslation('title', $language, false) ?: $roomType->title,
                        'description' => $roomType->getTranslation('description', $language, false) ?: $roomType->description,
                        'amenities' => $roomType->getTranslation('amenities', $language, false) ?: $roomType->amenities,
                        'gallery' => $roomType->gallery_images,
                        // Additional localized fields
                        'area' => $roomType->getTranslation('area', $language, false) ?: $roomType->area,
                        'bed_type' => $roomType->getTranslation('bed_type', $language, false) ?: $roomType->bed_type,
                        'room_amenities' => $roomType->getTranslation('room_amenities', $language, false) ?: $roomType->room_amenities,
                        'bathroom_amenities' => $roomType->getTranslation('bathroom_amenities', $language, false) ?: $roomType->bathroom_amenities,
                        'view' => $roomType->getTranslation('view', $language, false) ?: $roomType->view,
                        'featured_image' => $roomType->featured_image,
                        'adult_capacity' => $roomType->adult_capacity,
                        'child_capacity' => $roomType->child_capacity,
                        'is_extra_bed_available' => $roomType->is_extra_bed_available ?? false,
                        'requires_extra_bed' => $adults > $roomType->adult_capacity && ($roomType->is_extra_bed_available ?? false)
                    ],
                    'quantity' => $quantity,
                    'available_rooms' => $availableCount,
                    'base_price_total' => $basePrice * $quantity,
                    'additional_adult_charges' => $additionalCharges['additional_adult_total'],
                    'child_surcharge_charges' => $additionalCharges['child_surcharge_total'],
                    'total_with_charges' => ($basePrice * $quantity) + $additionalCharges['total_additional'],
                    'pricing_breakdown' => $pricingBreakdown,
                    'additional_charges_detail' => $additionalCharges,
                    'children_ages' => $childrenAges,
                    'total_capacity' => $effectiveCapacityPerRoom * $quantity,
                    'effective_capacity' => $this->calculateEffectiveCapacity($pricingPolicy, $quantity, $roomType), // Thêm capacity thực tế
                    'can_accommodate_guests' => ($effectiveCapacityPerRoom * $quantity) >= $totalGuests,
                    'promotions' => $promotions->map(function ($promo) use ($basePrice, $quantity, $additionalCharges, $language) {
                        // Tính giá có thể áp dụng khuyến mãi: base + natural additional adults (KHÔNG bao gồm extra bed)
                        $promotionApplicablePrice = ($basePrice * $quantity) + $additionalCharges['natural_additional_total'];

                        return [
                            'details' => [
                                'id' => $promo->id,
                                'promotion_code' => $promo->promotion_code,
                                'name' => $promo->getTranslation('name', $language, false) ?: $promo->name,
                                'description' => $promo->getTranslation('description', $language, false) ?: $promo->description,
                                'type' => $promo->type,
                                'value_type' => $promo->value_type,
                                'value' => $promo->value,
                                'start_date' => $promo->start_date,
                                'end_date' => $promo->end_date,
                                'is_active' => $promo->is_active,
                                'min_stay' => $promo->min_stay,
                                'max_stay' => $promo->max_stay,
                            ],
                            'discounted_price_total' => $this->calculateDiscountedPrice($promotionApplicablePrice, $promo) + $additionalCharges['child_surcharge_total'],
                            'promotion_applicable_price' => $promotionApplicablePrice,
                            'children_surcharge_excluded' => $additionalCharges['child_surcharge_total'],
                        ];
                    }),
                ];

                // Tính effective capacity - chỉ cho phép thêm người nếu có additional_adult_price
                $effectiveCapacity = $this->calculateEffectiveCapacity($pricingPolicy, $quantity, $roomType);

                // Kiểm tra capacity và validation toàn diện
                if (!$this->validateRoomCapacity($roomType, $pricingPolicy, $quantity, $effectiveAdults, $childAgeAnalysis, $totalGuests)) {
                    continue; // Bỏ qua tổ hợp này vì không đáp ứng được yêu cầu capacity
                }

                // Tính giá tổng theo logic mới: base + natural additional adults có thể áp dụng khuyến mãi
                // Extra bed và children surcharge thì không áp dụng khuyến mãi
                $baseWithNaturalAdults = ($basePrice * $quantity) + $additionalCharges['natural_additional_total'];
                $nonPromotionAmount = $additionalCharges['extra_bed_additional_total'] + $additionalCharges['child_surcharge_total'];

                $availableCombinations[] = [
                    'total_price' => $baseWithNaturalAdults + $nonPromotionAmount,
                    'base_price' => $basePrice * $quantity,
                    'additional_charges' => $additionalCharges['total_additional'],
                    'promotion_applicable_amount' => $baseWithNaturalAdults,
                    'non_promotion_amount' => $nonPromotionAmount,
                    'combination_details' => [$combinationDetails],
                ];
            }
        }

        // Add mixed combinations for better flexibility
        $mixedCombinations = $this->generateMixedCombinations($roomTypes, $adults, $children, $childAgeAnalysis, $checkIn, $checkOut, $numberOfNights);
        $availableCombinations = array_merge($availableCombinations, $mixedCombinations);

        return $availableCombinations;
    }

    /**
     * Kiểm tra phòng trống xuyên suốt khoảng thời gian.
     */
    private function checkAvailability($roomTypeId, $checkIn, $checkOut)
    {
        \Log::info("🏨 Checking availability for room type {$roomTypeId} from {$checkIn} to {$checkOut}");
        $minAvailable = PHP_INT_MAX;

        $endDate = (new Carbon($checkOut))->subDay();
        $period = CarbonPeriod::create($checkIn, '1 day', $endDate);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $roomRate = RoomRate::where('roomtype_id', $roomTypeId)
                ->where('date', $dateStr)
                ->first();

            \Log::info("🏨 Date {$dateStr}: " . ($roomRate ? "Available: {$roomRate->available_rooms}, Rate: {$roomRate->rate}" : 'No rate record'));

            if (!$roomRate || $roomRate->is_available === false) {
                return 0;
            } else {
                $available = $roomRate->available_rooms;
            }
            if ($available < $minAvailable) {
                $minAvailable = $available;
            }
        }
        return $minAvailable;
    }

    /**
     * Tính tổng giá cơ bản cho toàn bộ khoảng thời gian.
     */
    private function calculateBaseRate($roomTypeId, $checkIn, $checkOut)
    {
        $endDate = (new Carbon($checkOut))->subDay();
        $period = CarbonPeriod::create($checkIn, '1 day', $endDate);

        $totalPrice = 0;
        $numberOfNights = 0;

        foreach ($period as $date) {
            $rate = RoomRate::where('roomtype_id', $roomTypeId)
                ->where('date', $date->toDateString())
                ->first();

            if (!$rate) {
                return 0; // Trả về 0 nếu thiếu giá của bất kỳ đêm nào
            }
            $totalPrice += $rate->price;
            $numberOfNights++;
        }

        return $totalPrice;
    }

    /**
     * Lấy danh sách khuyến mãi hợp lệ cho một loại phòng trong khoảng thời gian cụ thể.
     */
    private function getApplicablePromotions($roomType, $checkIn, $checkOut)
    {
        $promotions = $roomType->promotions()
            ->where('is_active', 1)
            ->where('start_date', '<=', $checkIn)
            ->where('end_date', '>=', $checkIn)
            ->get();

        $today = Carbon::now()->startOfDay();
        $checkInDate = (new Carbon($checkIn))->startOfDay();
        $numberOfNights = (new Carbon($checkIn))->diffInDays($checkOut);
        return $promotions->filter(function ($promo) use ($today, $checkInDate, $numberOfNights) {
            $isConditionMet = true;

            if ($promo->booking_days_in_advance !== null) {
                $daysBetween = $today->diffInDays($checkInDate, false);
                if ($promo->type === PromotionType::EARLY_BIRD && $daysBetween < $promo->booking_days_in_advance) {
                    $isConditionMet = false;
                } elseif ($promo->type === PromotionType::LAST_MINUTES && ($daysBetween > $promo->booking_days_in_advance || $daysBetween < 0)) {
                    $isConditionMet = false;
                }
            }

            if (!$isConditionMet) {
                return false;
            }

            if ($promo->min_stay !== null && $numberOfNights < $promo->min_stay) {
                $isConditionMet = false;
            }
            if ($promo->max_stay !== null && $numberOfNights > $promo->max_stay) {
                $isConditionMet = false;
            }

            return $isConditionMet;
        });
    }

    /**
     * Tính giá sau khuyến mãi.
     */
    private function calculateDiscountedPrice($basePrice, $promotion)
    {
        if ($promotion->value_type === 'fixed') {
            return max(0, $basePrice - $promotion->value);
        } elseif ($promotion->value_type === 'percentage') {
            return $basePrice * (1 - $promotion->value / 100);
        }
        return $basePrice;
    }

    /**
     * Return sample room combinations when real data is not available
     */
    private function getSampleRoomCombinations($hotelId, $checkIn, $checkOut, $adults, $children)
    {
        $numberOfNights = (new Carbon($checkIn))->diffInDays($checkOut);

        return [
            [
                'room_type' => [
                    'id' => 1,
                    'name' => 'Deluxe Double Room',
                    'description' => 'Spacious room with city view, perfect for couples',
                    'max_occupancy' => 2,
                    'adult_capacity' => 2,
                    'child_capacity' => 1,
                ],
                'available_count' => 5,
                'base_price' => 1200000, // 1.2M VND per night
                'total_price' => 1200000 * $numberOfNights,
                'currency' => 'VND',
                'nights' => $numberOfNights,
                'applicable_promotions' => []
            ],
            [
                'room_type' => [
                    'id' => 2,
                    'name' => 'Superior Twin Room',
                    'description' => 'Comfortable room with twin beds, great for friends or business travelers',
                    'max_occupancy' => 3,
                    'adult_capacity' => 3,
                    'child_capacity' => 1,
                ],
                'available_count' => 3,
                'base_price' => 1500000, // 1.5M VND per night
                'total_price' => 1500000 * $numberOfNights,
                'currency' => 'VND',
                'nights' => $numberOfNights,
                'applicable_promotions' => []
            ],
            [
                'room_type' => [
                    'id' => 3,
                    'name' => 'Family Suite',
                    'description' => 'Large suite with separate living area, perfect for families',
                    'max_occupancy' => 4,
                    'adult_capacity' => 4,
                    'child_capacity' => 2,
                ],
                'available_count' => 2,
                'base_price' => 2200000, // 2.2M VND per night
                'total_price' => 2200000 * $numberOfNights,
                'currency' => 'VND',
                'nights' => $numberOfNights,
                'applicable_promotions' => []
            ]
        ];
    }

    /**
     * Lấy chính sách độ tuổi trẻ em của khách sạn
     */
    private function getChildAgePolicy($hotelId)
    {
        return ChildAgePolicy::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->first() ?: $this->getDefaultChildAgePolicy();
    }

    /**
     * Lấy chính sách giá phòng
     */
    private function getRoomPricingPolicy($roomtypeId)
    {
        return RoomPricingPolicy::where('roomtype_id', $roomtypeId)
            ->where('is_active', true)
            ->first() ?: $this->getDefaultRoomPricingPolicy();
    }

    /**
     * Phân tích độ tuổi trẻ em
     */
    private function analyzeChildrenAges($childrenAges, $childAgePolicy)
    {
        $analysis = [
            'free_children' => 0,
            'surcharge_children' => 0,
            'children_as_adults' => 0,
            'free_ages' => [],
            'surcharge_ages' => [],
            'adult_ages' => []
        ];

        foreach ($childrenAges as $age) {
            if ($childAgePolicy->isChildFree($age)) {
                $analysis['free_children']++;
                $analysis['free_ages'][] = $age;
            } elseif ($childAgePolicy->isChildSurcharge($age)) {
                $analysis['surcharge_children']++;
                $analysis['surcharge_ages'][] = $age;
            } else {
                $analysis['children_as_adults']++;
                $analysis['adult_ages'][] = $age;
            }
        }

        return $analysis;
    }

    /**
     * Tính phụ thu cho người lớn thêm và trẻ em
     */
    private function calculateAdditionalCharges($effectiveAdults, $childAgeAnalysis, $quantity, $pricingPolicy, $numberOfNights, $roomType)
    {
        $baseOccupancy = $pricingPolicy->base_occupancy ?? 2;
        $additionalAdultPrice = $pricingPolicy->additional_adult_price ?? 0;
        $childSurchargePrice = $pricingPolicy->child_surcharge_price ?? 0;

        // Tính số người lớn thêm
        $totalBaseCapacity = $baseOccupancy * $quantity;
        $additionalAdults = max(0, $effectiveAdults - $totalBaseCapacity);

        // Kiểm tra giường phụ cho trẻ em > 6 tuổi
        $allowedSurchargeChildren = $childAgeAnalysis['surcharge_children'];
        $hasExtraBed = $roomType->is_extra_bed_available ?? false;

        // Nếu phòng không có giường phụ, trẻ em > 6 tuổi sẽ không được tính phụ thu (bị từ chối)
        if (!$hasExtraBed && $allowedSurchargeChildren > 0) {
            // Trả về lỗi hoặc đặt về 0 để bỏ qua tổ hợp này
            $allowedSurchargeChildren = 0;
        }

        // Debug logging
        \Log::info('Additional charges calculation:', [
            'effectiveAdults' => $effectiveAdults,
            'baseOccupancy' => $baseOccupancy,
            'quantity' => $quantity,
            'totalBaseCapacity' => $totalBaseCapacity,
            'additionalAdults' => $additionalAdults,
            'additionalAdultPrice' => $additionalAdultPrice,
            'childSurchargePrice' => $childSurchargePrice,
            'numberOfNights' => $numberOfNights,
            'hasExtraBed' => $hasExtraBed,
            'originalSurchargeChildren' => $childAgeAnalysis['surcharge_children'],
            'allowedSurchargeChildren' => $allowedSurchargeChildren
        ]);

        // Phân biệt additional adults trong natural capacity vs cần extra bed
        $roomAdultCapacity = $roomType->adult_capacity ?? 2;
        $naturalCapacityAdults = min($effectiveAdults, $roomAdultCapacity * $quantity);
        $extraBedAdults = max(0, $effectiveAdults - $naturalCapacityAdults);

        // Additional adults trong natural capacity (được áp dụng promotion)
        $naturalAdditionalAdults = max(0, $naturalCapacityAdults - $totalBaseCapacity);

        // Additional adults cần extra bed (KHÔNG được áp dụng promotion)
        $extraBedAdditionalAdults = $extraBedAdults;

        // Tính phụ thu
        $additionalAdultTotal = $additionalAdults * $additionalAdultPrice * $numberOfNights;
        $childSurchargeTotal = $allowedSurchargeChildren * $childSurchargePrice * $numberOfNights;

        // Tính phần được áp dụng promotion vs không được áp dụng
        $naturalAdditionalTotal = $naturalAdditionalAdults * $additionalAdultPrice * $numberOfNights;
        $extraBedAdditionalTotal = $extraBedAdditionalAdults * $additionalAdultPrice * $numberOfNights;

        return [
            'additional_adults' => $additionalAdults,
            'surcharge_children' => $allowedSurchargeChildren, // Sử dụng allowed thay vì original
            'original_surcharge_children' => $childAgeAnalysis['surcharge_children'], // Thêm thông tin gốc để debug
            'has_extra_bed' => $hasExtraBed,
            'additional_adult_price' => $additionalAdultPrice,
            'child_surcharge_price' => $childSurchargePrice,
            'additional_adult_total' => $additionalAdultTotal,
            'child_surcharge_total' => $childSurchargeTotal,
            'total_additional' => $additionalAdultTotal + $childSurchargeTotal,
            // Thêm thông tin phân biệt natural vs extra bed
            'natural_additional_adults' => $naturalAdditionalAdults,
            'extra_bed_additional_adults' => $extraBedAdditionalAdults,
            'natural_additional_total' => $naturalAdditionalTotal,
            'extra_bed_additional_total' => $extraBedAdditionalTotal,
        ];
    }

    /**
     * Chính sách độ tuổi trẻ em mặc định
     */
    private function getDefaultChildAgePolicy()
    {
        return (object) [
            'free_age_limit' => 6,
            'surcharge_age_limit' => 12,
            'isChildFree' => function ($age) {
                return $age < 6;
            },
            'isChildSurcharge' => function ($age) {
                return $age >= 6 && $age < 12;
            },
            'isChildAsAdult' => function ($age) {
                return $age >= 12;
            }
        ];
    }

    /**
     * Chính sách giá phòng mặc định
     */
    private function getDefaultRoomPricingPolicy()
    {
        return (object) [
            'base_occupancy' => 2,
            'additional_adult_price' => 0,
            'child_surcharge_price' => 0
        ];
    }

    /**
     * Tính capacity thực tế của phòng (bao gồm khả năng thêm người với phụ thu)
     */
    private function calculateEffectiveCapacity($pricingPolicy, $quantity, $roomType = null)
    {
        $baseOccupancy = $pricingPolicy->base_occupancy ?? 2;
        $additionalAdultPrice = $pricingPolicy->additional_adult_price ?? 0;

        // Base capacity: số người lớn cơ bản
        $effectiveCapacityPerRoom = $baseOccupancy;

        // Thêm capacity cho người lớn phụ thu (nếu có additional_adult_price)
        if ($additionalAdultPrice > 0) {
            $effectiveCapacityPerRoom += 2; // Cho phép thêm tối đa 2 người lớn/phòng
        }

        // Thêm capacity cho trẻ em (nếu có extra bed hoặc child capacity)
        if ($roomType) {
            $childCapacity = $roomType->child_capacity ?? 0;
            $hasExtraBed = $roomType->is_extra_bed_available ?? false;

            if ($hasExtraBed || $childCapacity > 0) {
                // Nếu có giường phụ hoặc child capacity, cho phép thêm trẻ em
                $effectiveCapacityPerRoom += max($childCapacity, 1); // Ít nhất 1 trẻ em
            }
        }

        return $effectiveCapacityPerRoom * $quantity;
    }

    /**
     * Kiểm tra toàn diện capacity của phòng với tất cả điều kiện
     */
    private function validateRoomCapacity($roomType, $pricingPolicy, $quantity, $effectiveAdults, $childAgeAnalysis, $totalGuests)
    {
        $baseOccupancy = $pricingPolicy->base_occupancy ?? 2;
        $additionalAdultPrice = $pricingPolicy->additional_adult_price ?? 0;
        $hasExtraBed = $roomType->is_extra_bed_available ?? false;
        $childCapacity = $roomType->child_capacity ?? 0;

        // 1. Tính capacity cơ bản
        $baseCapacity = $baseOccupancy * $quantity;
        $effectiveCapacity = $this->calculateEffectiveCapacity($pricingPolicy, $quantity, $roomType);

        // 2. Kiểm tra tổng số khách không vượt quá effective capacity
        if ($totalGuests > $effectiveCapacity) {
            \Log::info("❌ Total guests ({$totalGuests}) exceeds effective capacity ({$effectiveCapacity})");
            return false;
        }

        // 3. Kiểm tra người lớn không vượt quá khả năng cho phép
        $naturalAdultCapacity = ($roomType->adult_capacity ?? 2) * $quantity; // Khả năng natural của phòng

        if ($effectiveAdults > $naturalAdultCapacity) {
            // Nếu vượt quá natural capacity, cần extra bed hoặc additional pricing
            if ($additionalAdultPrice <= 0 && !$hasExtraBed) {
                \Log::info("❌ Adults ({$effectiveAdults}) exceed natural capacity ({$naturalAdultCapacity}) but no additional adult pricing and no extra bed");
                return false;
            }

            // Nếu có additional pricing hoặc extra bed, kiểm tra không vượt quá tối đa cho phép
            $extraAdultsPerRoom = $hasExtraBed ? 1 : ($additionalAdultPrice > 0 ? 2 : 0); // Extra bed = +1, additional pricing = +2
            $maxAdultsAllowed = $naturalAdultCapacity + ($extraAdultsPerRoom * $quantity);
            if ($effectiveAdults > $maxAdultsAllowed) {
                \Log::info("❌ Adults ({$effectiveAdults}) exceed maximum allowed ({$maxAdultsAllowed})");
                return false;
            }
        } else if ($effectiveAdults > $baseCapacity) {
            // Nếu vượt quá base capacity nhưng không vượt quá natural capacity
            // Chỉ cần check xem có cho phép thêm người mà không cần extra bed không
            // Ví dụ: Premium room capacity=3, base_occupancy=2, không có additional pricing → vẫn OK vì trong khả năng tự nhiên
            \Log::info("✅ Adults ({$effectiveAdults}) exceed base capacity ({$baseCapacity}) but within natural capacity ({$naturalAdultCapacity})");
        }

        // 4. Kiểm tra trẻ em cần phụ thu (6-12 tuổi) có giường phụ không
        if ($childAgeAnalysis['surcharge_children'] > 0) {
            if (!$hasExtraBed) {
                \Log::info("❌ Surcharge children ({$childAgeAnalysis['surcharge_children']}) need extra bed but room has no extra bed");
                return false;
            }
        }

        // 5. Kiểm tra trẻ em tính như người lớn (≥12 tuổi) - đã được tính vào effectiveAdults
        // Không cần check riêng vì đã được xử lý ở validation người lớn

        // 6. Kiểm tra child capacity cơ bản
        $totalChildren = $childAgeAnalysis['free_children'] + $childAgeAnalysis['surcharge_children'];
        if ($totalChildren > 0) {
            // Nếu có trẻ em nhưng không có child capacity và không có extra bed
            if ($childCapacity <= 0 && !$hasExtraBed) {
                \Log::info("❌ Has children ({$totalChildren}) but no child capacity and no extra bed");
                return false;
            }
        }

        \Log::info("✅ Room capacity validation passed", [
            'effectiveAdults' => $effectiveAdults,
            'totalChildren' => $totalChildren,
            'totalGuests' => $totalGuests,
            'baseCapacity' => $baseCapacity,
            'effectiveCapacity' => $effectiveCapacity,
            'hasExtraBed' => $hasExtraBed,
            'childCapacity' => $childCapacity
        ]);

        return true;
    }

    /**
     * Tạo pricing breakdown theo structure mới
     */
    private function createPricingBreakdown($basePrice, $quantity, $numberOfNights, $additionalCharges, $childAgeAnalysis)
    {
        $basePriceTotal = $basePrice * $quantity;
        $adultSurchargeTotal = $additionalCharges['additional_adult_total'];
        $childrenSurchargeTotal = $additionalCharges['child_surcharge_total'];

        // Phần có thể áp dụng khuyến mãi: base + natural additional adults (KHÔNG bao gồm extra bed)
        $promotionApplicableAmount = $basePriceTotal + $additionalCharges['natural_additional_total'];

        // Phần không áp dụng khuyến mãi: extra bed + children surcharge
        $nonPromotionAmount = $additionalCharges['extra_bed_additional_total'] + $childrenSurchargeTotal;

        return [
            'base_price' => $basePrice,
            'base_nights' => $numberOfNights,
            'base_total' => $basePriceTotal,
            'adult_surcharge_per_night' => $additionalCharges['additional_adult_price'] ?? 0,
            'adult_surcharge_nights' => $numberOfNights,
            'adult_surcharge_total' => $adultSurchargeTotal,
            'children_surcharge_per_night' => $additionalCharges['child_surcharge_price'] ?? 0,
            'children_surcharge_nights' => $numberOfNights,
            'children_surcharge_total' => $childrenSurchargeTotal,
            'promotion_applicable_amount' => $promotionApplicableAmount,
            'non_promotion_amount' => $nonPromotionAmount,
            'promotion_discount' => 0, // Sẽ được cập nhật khi có promotion
            'final_total' => $promotionApplicableAmount + $nonPromotionAmount,
            // Add breakdown for non-promotion amount
            'extra_bed_adult_surcharge_total' => $additionalCharges['extra_bed_additional_total'],
            'extra_bed_adult_count' => $additionalCharges['extra_bed_additional_adults'] ?? 0,
            'children_breakdown' => [
                'free_children' => $childAgeAnalysis['free_children'],
                'surcharge_children' => $childAgeAnalysis['surcharge_children'],
                'adult_rate_children' => $childAgeAnalysis['children_as_adults']
            ]
        ];
    }

    /**
     * Generate mixed room combinations for better guest distribution
     */
    private function generateMixedCombinations($roomTypes, $adults, $children, $childAgeAnalysis, $checkIn, $checkOut, $numberOfNights)
    {
        $mixedCombinations = [];

        // Only create mixed combinations if we have multiple room types and 3+ adults
        if ($roomTypes->count() < 2 || $adults < 3) {
            return $mixedCombinations;
        }

        // Find Family room (with extra bed) and Premium room
        $familyRoom = $roomTypes->where('is_extra_bed_available', true)->first();
        $premiumRoom = $roomTypes->where('is_extra_bed_available', false)->first();

        if (!$familyRoom || !$premiumRoom) {
            return $mixedCombinations;
        }

        // Create Family(3) + Premium(2) combination for 3 adults
        if ($adults == 3 && $children == 0) {
            try {
                // Family room: 3 adults (2 + 1 extra bed)
                $familyPricing = $this->getRoomPricingPolicy($familyRoom->id);
                $familyBasePrice = $this->calculateBaseRate($familyRoom->id, $checkIn, $checkOut);
                $familyCharges = $this->calculateAdditionalCharges(3, $childAgeAnalysis, 1, $familyPricing, $numberOfNights, $familyRoom);

                // Premium room: 2 adults (base capacity)
                $premiumPricing = $this->getRoomPricingPolicy($premiumRoom->id);
                $premiumBasePrice = $this->calculateBaseRate($premiumRoom->id, $checkIn, $checkOut);
                $premiumCharges = $this->calculateAdditionalCharges(2, ['free_children' => 0, 'surcharge_children' => 0, 'children_as_adults' => 0], 1, $premiumPricing, $numberOfNights, $premiumRoom);

                if ($familyBasePrice > 0 && $premiumBasePrice > 0) {
                    $familyDetail = [
                        'room_type' => [
                            'id' => $familyRoom->id,
                            'name' => $familyRoom->name,
                            'description' => $familyRoom->description,
                            'amenities' => $familyRoom->amenities,
                            'gallery' => $familyRoom->gallery_images,
                            'adult_capacity' => $familyRoom->adult_capacity,
                            'child_capacity' => $familyRoom->child_capacity,
                        ],
                        'quantity' => 1,
                        'available_rooms' => $this->checkAvailability($familyRoom->id, $checkIn, $checkOut),
                        'base_price_total' => $familyBasePrice,
                        'pricing_breakdown' => $this->createPricingBreakdown($familyBasePrice, 1, $numberOfNights, $familyCharges, $childAgeAnalysis),
                        'can_accommodate_guests' => true,
                        'total_capacity' => 3,
                        'effective_capacity' => 3,
                        'promotions' => $this->getApplicablePromotions($familyRoom, $checkIn, $checkOut)
                    ];

                    $premiumDetail = [
                        'room_type' => [
                            'id' => $premiumRoom->id,
                            'name' => $premiumRoom->name,
                            'description' => $premiumRoom->description,
                            'amenities' => $premiumRoom->amenities,
                            'gallery' => $premiumRoom->gallery_images,
                            'adult_capacity' => $premiumRoom->adult_capacity,
                            'child_capacity' => $premiumRoom->child_capacity,
                        ],
                        'quantity' => 1,
                        'available_rooms' => $this->checkAvailability($premiumRoom->id, $checkIn, $checkOut),
                        'base_price_total' => $premiumBasePrice,
                        'pricing_breakdown' => $this->createPricingBreakdown($premiumBasePrice, 1, $numberOfNights, $premiumCharges, ['free_children' => 0, 'surcharge_children' => 0, 'children_as_adults' => 0]),
                        'can_accommodate_guests' => true,
                        'total_capacity' => 2,
                        'effective_capacity' => 2,
                        'promotions' => $this->getApplicablePromotions($premiumRoom, $checkIn, $checkOut)
                    ];

                    $totalPrice = ($familyBasePrice + $familyCharges['total_additional']) + ($premiumBasePrice + $premiumCharges['total_additional']);
                    $totalPromotionApplicable = ($familyBasePrice + $familyCharges['natural_additional_total']) + ($premiumBasePrice + $premiumCharges['natural_additional_total']);
                    $totalNonPromotion = $familyCharges['extra_bed_additional_total'] + $premiumCharges['extra_bed_additional_total'];

                    $mixedCombinations[] = [
                        'total_price' => $totalPrice,
                        'base_price' => $familyBasePrice + $premiumBasePrice,
                        'additional_charges' => $familyCharges['total_additional'] + $premiumCharges['total_additional'],
                        'promotion_applicable_amount' => $totalPromotionApplicable,
                        'non_promotion_amount' => $totalNonPromotion,
                        'combination_details' => [$familyDetail, $premiumDetail],
                    ];
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error('Error generating mixed combination: ' . $e->getMessage());
            }
        }

        return $mixedCombinations;
    }
}
