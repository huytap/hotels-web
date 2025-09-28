<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Roomtype;
use Carbon\Carbon;
use App\Helpers\HotelHelper;

class SyncController extends Controller
{
    /**
     * Đồng bộ hóa thông tin khách sạn từ WordPress.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHotel(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $wpData = $request->json()->all();
        if (!isset($wpData['data']) || !is_array($wpData['data']) || empty($wpData['data'])) {
            return response()->json(['message' => 'Invalid data format or empty data.'], 400);
        }

        try {
            // Lấy WP_ID và thời gian cập nhật cuối cùng từ API
            $wpId = $wpData['wp_id'] ?? null;
            $wpUpdatedAt = $wpData['last_updated'] ?? null;
            // Danh sách các trường đa ngôn ngữ
            $translatableFields = [
                'name', 'address', 'policy', 'description', 'short_description',
                'amenities', 'facilities', 'services', 'nearby_attractions',
                'transportation', 'dining_options', 'room_features',
                'cancellation_policy', 'terms_conditions', 'special_notes'
            ];

            // Khởi tạo mảng dữ liệu đa ngôn ngữ
            $translatableData = [];
            foreach ($translatableFields as $key) {
                $translatableData[$key] = [];
            }

            // Dữ liệu chung (không đa ngôn ngữ) - lấy từ record đầu tiên
            $commonData = [];
            if (!empty($wpData['data'])) {
                $firstRecord = $wpData['data'][0];
                $commonData = [
                    'phone_number' => $firstRecord['phone_number'] ?? null,
                    'email_address' => $firstRecord['email_address'] ?? null,
                    'google_map' => $firstRecord['google_map'] ?? null,
                    'domain_name' => $firstRecord['domain_name'] ?? null,
                    'fax' => $firstRecord['fax'] ?? null,
                    'website' => $firstRecord['website'] ?? null,
                    'tax_code' => $firstRecord['tax_code'] ?? null,
                    'business_license' => $firstRecord['business_license'] ?? null,
                    'star_rating' => $firstRecord['star_rating'] ?? null,
                    'established_year' => $firstRecord['established_year'] ?? null,
                    'total_rooms' => $firstRecord['total_rooms'] ?? null,
                    'check_in_time' => $firstRecord['check_in_time'] ?? '14:00',
                    'check_out_time' => $firstRecord['check_out_time'] ?? '12:00',
                    'currency' => $firstRecord['currency'] ?? 'VND',
                    'timezone' => $firstRecord['timezone'] ?? 'Asia/Ho_Chi_Minh',
                ];
            }

            // Xử lý dữ liệu đa ngôn ngữ từ tất cả các record
            foreach ($wpData['data'] as $record) {
                $lang = $record['lang'] ?? 'vi';
                foreach ($translatableFields as $key) {
                    if (isset($record[$key])) {
                        $translatableData[$key][$lang] = $record[$key];
                    }
                }
            }

            // Tìm hoặc tạo mới bản ghi khách sạn
            $hotel = Hotel::firstOrNew(['wp_id' => $wpId]);

            // Gán dữ liệu đa ngôn ngữ
            foreach ($translatableData as $key => $translations) {
                if (!empty($translations)) {
                    $hotel->setTranslations($key, $translations);
                }
            }

            // Gán dữ liệu chung
            foreach ($commonData as $key => $value) {
                if ($value !== null) {
                    $hotel->$key = $value;
                }
            }

            // Gán các trường bắt buộc
            $hotel->wp_id = $wpId;

            // Gán thời gian cập nhật từ WordPress nếu có
            if ($wpUpdatedAt) {
                $hotel->wp_updated_at = Carbon::parse($wpUpdatedAt);
            } else {
                $hotel->wp_updated_at = now();
            }

            // Lưu bản ghi
            $hotel->save();

            return response()->json(['message' => 'Hotel information synchronized successfully.'], 200);
        } catch (\Exception $e) {
            // Ghi log lỗi để dễ dàng debug
            logger()->error('API Synchronization Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while processing the request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRooms(Request $request)
    {
        $wpData = $request->json()->all();
        if (!isset($wpData['data']) || !is_array($wpData['data']) || empty($wpData['data'])) {
            return response()->json(['message' => 'Invalid data format or empty data.'], 400);
        }
        $wpId = $wpData['wp_id'];
        $lastUpdated = $wpData['last_updated'];
        $translatableData = $wpData['data']['translatable'];
        $commonData = $wpData['data']['common'];
        $sync_id = $commonData['sync_id'];
        if (empty($sync_id)) {
            return response()->json(['message' => 'Missing sync_id.'], 400);
        }

        try {
            // Sử dụng updateOrCreate để tìm hoặc tạo bản ghi mới dựa trên sync_id
            $room = Roomtype::firstOrNew(['sync_id' => $sync_id]);

            // Gán dữ liệu đa ngôn ngữ
            foreach ($translatableData as $lang => $data) {
                $room->setTranslation('title', $lang, $data['title'] ?? null);
                $room->setTranslation('description', $lang, $data['content'] ?? null);
                $room->setTranslation('bed_type', $lang, $data['bed_type'] ?? null);
                $room->setTranslation('amenities', $lang, $data['amenities'] ?? null);
                $room->setTranslation('room_amenities', $lang, $data['room_amenities'] ?? null);
                $room->setTranslation('bathroom_amenities', $lang, $data['bathroom_amenities'] ?? null);
                $room->setTranslation('view', $lang, $data['view'] ?? null);
            }

            // Gán dữ liệu chung
            $hotelId = HotelHelper::getHotelIdByWpId($wpId);
            $room->hotel_id = $hotelId;
            $room->wp_id = $wpId;
            $room->sync_id = $sync_id;
            $room->gallery_images = $commonData['gallery_images'] ? json_encode($commonData['gallery_images']) : null;
            $room->featured_image = $commonData['featured_image'] ?? null;
            $room->area = $commonData['area'] ?? null;
            $room->adult_capacity = $commonData['adult'] ?? 0;
            $room->child_capacity = $commonData['children'] ?? 0;
            $room->is_extra_bed_available = $commonData['extrabed'] ?? 0;
            $room->last_updated = $lastUpdated;
            $room->save();

            return response()->json(['success' => true, 'message' => 'Room synchronized successfully.'], 200);
        } catch (\Exception $e) {
            logger()->error('Roomtype sync error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'An error occurred while processing the request.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
