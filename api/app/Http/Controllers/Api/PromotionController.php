<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\HotelHelper;
use App\Enums\PromotionType;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PromotionController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function index(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        // 1. Xác thực các tham số đầu vào
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:active,inactive',
            'search' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // 2. Xây dựng truy vấn
        $query = Promotion::where('hotel_id', $hotel->id);

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Tìm kiếm theo tên hoặc code
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Sử dụng JSON where để tìm kiếm trong các trường đa ngôn ngữ
                $q->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->vi', 'like', "%{$search}%")
                    ->orWhere('promotion_code', 'like', "%{$search}%");
            });
        }

        // Lọc theo loại khuyến mãi
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // 3. Thực hiện phân trang
        $perPage = $request->input('per_page', 50);
        $promotions = $query->paginate($perPage);

        // 4. Trả về kết quả
        return $this->successResponse($promotions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        $wpData = $request->json()->all();
        $validator = Validator::make($wpData, [
            'data' => 'required|array',
            'data.promotion_code' => 'required|string|unique:promotions,promotion_code',
            'data.name' => 'required|array',
            'data.description' => 'nullable|array',
            'data.type' => ['nullable', Rule::in(PromotionType::getValues())],
            'data.value_type' => 'required|in:percentage,fixed',
            'data.value' => 'required|numeric|min:0',
            'data.start_date' => 'required|date',
            'data.end_date' => 'required|date|after_or_equal:start_date',
            'data.is_active' => 'nullable|boolean',
            'data.roomtypes' => 'required|array',
            'data.roomtypes.*' => 'required|exists:roomtypes,id',
            // Blackout dates
            'data.blackout_start_date' => 'nullable|date',
            'data.blackout_end_date' => 'nullable|date|after_or_equal:data.blackout_start_date',
            // Valid weekdays
            'data.valid_monday' => 'nullable|boolean',
            'data.valid_tuesday' => 'nullable|boolean',
            'data.valid_wednesday' => 'nullable|boolean',
            'data.valid_thursday' => 'nullable|boolean',
            'data.valid_friday' => 'nullable|boolean',
            'data.valid_saturday' => 'nullable|boolean',
            'data.valid_sunday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        // if (!$hotelId) {
        //     return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        // }

        try {
            DB::beginTransaction();
            $wpData = $wpData['data'];
            //dd($wpData);
            $promotion = Promotion::create([
                'hotel_id' => $hotel->id,
                'promotion_code' => $wpData['promotion_code'],
                'name' => $wpData['name'],
                'description' => $wpData['description'] ?? null,
                'type' => $wpData['type'] ?? 'Others',
                'value_type' => $wpData['value_type'],
                'value' => $wpData['value'],
                'start_date' => $wpData['start_date'],
                'end_date' => $wpData['end_date'],
                'is_active' => $wpData['is_active'] ?? true,
                'booking_days_in_advance' => $wpData['booking_days_in_advance'] ?? null,
                'min_stay' => empty($wpData['min_stay']) ? null : $wpData['min_stay'],
                'max_stay' => empty($wpData['max_stay']) ? null : $wpData['max_stay'],
                // Blackout dates
                'blackout_start_date' => empty($wpData['blackout_start_date']) ? null : $wpData['blackout_start_date'],
                'blackout_end_date' => empty($wpData['blackout_end_date']) ? null : $wpData['blackout_end_date'],
                // Valid weekdays
                'valid_monday' => empty($wpData['valid_monday']) ? false : $wpData['valid_monday'],
                'valid_tuesday' => empty($wpData['valid_tuesday']) ? false : $wpData['valid_tuesday'],
                'valid_wednesday' => empty($wpData['valid_wednesday']) ? false : $wpData['valid_wednesday'],
                'valid_thursday' => empty($wpData['valid_thursday']) ? false : $wpData['valid_thursday'],
                'valid_friday' => empty($wpData['valid_friday']) ? false : $wpData['valid_friday'],
                'valid_saturday' => empty($wpData['valid_saturday']) ? false : $wpData['valid_saturday'],
                'valid_sunday' => empty($wpData['valid_sunday']) ? false : $wpData['valid_sunday'],
            ]);
            $promotion->roomtypes()->sync($wpData['roomtypes']);

            DB::commit();

            return $this->successResponse(['message' => 'Promotion created successfully.', 'promotion' => $promotion->load('roomtypes')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while creating the promotion.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Promotion $promotion)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        // Kiểm tra xem promotion có thuộc về khách sạn hợp lệ không
        if (!$hotel->id || $promotion->hotel_id !== $hotel->id) {
            return response()->json(['error' => 'Promotion not found or does not belong to this hotel.'], 404);
        }
        // Tải các mối quan hệ liên quan và trả về phản hồi
        return $this->successResponse($promotion->load('roomtypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Promotion $promotion)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        // Kiểm tra xem promotion có thuộc về khách sạn hợp lệ không
        if (!$hotel->id || $promotion->hotel_id !== $hotel->id) {
            return response()->json(['error' => 'Promotion not found or does not belong to this hotel.'], 404);
        }

        // Xác thực dữ liệu đầu vào:
        $wpData = $request->json()->all();
        //\Log::info($wpData);

        $validator = Validator::make($wpData, [
            //'promotion_code' => ['required', 'string', Rule::unique('promotions')->ignore($promotion->id)],
            'data' => 'required|array',
            'data.name' => 'required|array',
            'data.description' => 'nullable|array',
            'data.type' => ['nullable', Rule::in(PromotionType::getValues())],
            'data.value_type' => 'required|in:percentage,fixed',
            'data.value' => 'required|numeric|min:0',
            'data.start_date' => 'required|date',
            'data.end_date' => 'required|date|after_or_equal:start_date',
            'data.is_active' => 'nullable|boolean',
            'data.roomtypes' => 'required|array',
            'data.roomtypes.*' => 'required|exists:roomtypes,id',
            // Blackout dates
            'data.blackout_start_date' => 'nullable|date',
            'data.blackout_end_date' => 'nullable|date|after_or_equal:data.blackout_start_date',
            // Valid weekdays
            'data.valid_monday' => 'nullable|boolean',
            'data.valid_tuesday' => 'nullable|boolean',
            'data.valid_wednesday' => 'nullable|boolean',
            'data.valid_thursday' => 'nullable|boolean',
            'data.valid_friday' => 'nullable|boolean',
            'data.valid_saturday' => 'nullable|boolean',
            'data.valid_sunday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cập nhật dữ liệu:
        try {
            DB::beginTransaction();
            $wpData = $wpData['data'];

            $promotion->update([
                //'promotion_code' => $wpData['promotion_code'],
                'name' => $wpData['name'],
                'description' => $wpData['description'] ?? null,
                'type' => $wpData['type'] ?? 'Others',
                'value_type' => $wpData['value_type'],
                'value' => $wpData['value'],
                'start_date' => $wpData['start_date'],
                'end_date' => $wpData['end_date'],
                'is_active' => $wpData['is_active'] ?? true,
                'booking_days_in_advance' => $wpData['booking_days_in_advance'] ?? null,
                'min_stay' => empty($wpData['min_stay']) ? null : $wpData['min_stay'],
                'max_stay' => empty($wpData['max_stay']) ? null : $wpData['max_stay'],
                // Blackout dates
                'blackout_start_date' => empty($wpData['blackout_start_date']) ? null : $wpData['blackout_start_date'],
                'blackout_end_date' => empty($wpData['blackout_end_date']) ? null : $wpData['blackout_end_date'],
                // Valid weekdays
                'valid_monday' => empty($wpData['valid_monday']) ? false : $wpData['valid_monday'],
                'valid_tuesday' => empty($wpData['valid_tuesday']) ? false : $wpData['valid_tuesday'],
                'valid_wednesday' => empty($wpData['valid_wednesday']) ? false : $wpData['valid_wednesday'],
                'valid_thursday' => empty($wpData['valid_thursday']) ? false : $wpData['valid_thursday'],
                'valid_friday' => empty($wpData['valid_friday']) ? false : $wpData['valid_friday'],
                'valid_saturday' => empty($wpData['valid_saturday']) ? false : $wpData['valid_saturday'],
                'valid_sunday' => empty($wpData['valid_sunday']) ? false : $wpData['valid_sunday'],
            ]);

            // Cập nhật quan hệ nhiều-nhiều với bảng room_types
            $promotion->roomtypes()->sync($wpData['roomtypes']);

            DB::commit();

            return $this->successResponse(['message' => 'Promotion updated successfully.', 'promotion' => $promotion->load('roomtypes')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while updating the promotion.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json(['error' => 'Promotion not found or does not belong to this hotel.'], 404);
        }

        try {
            DB::beginTransaction();
            $promotion->delete();
            DB::commit();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while deleting the promotion.'], 500);
        }
    }
    public function updateStatus(Request $request)
    {
        $hotel = $this->validateHotelAccess($request);
        if ($hotel instanceof JsonResponse) {
            return $hotel;
        }
        // Xác thực dữ liệu đầu vào:
        $wpData = $request->json()->all();
        $validator = Validator::make($wpData, [
            'data' => 'required|array',
            'data.promotion_ids' => 'required|array',
            'data.promotion_ids.*' => 'required|integer|exists:promotions,id',
            'data.is_active' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cập nhật dữ liệu:
        try {
            DB::beginTransaction();
            $wpData = $wpData['data'];
            $is_active = $wpData['is_active'];

            $promotionIds = $wpData['promotion_ids'];
            // Find promotions that belong to the correct hotel and are in the provided list.
            // Using whereIn and the hotel_id ensures security and efficiency.
            $updatedCount = Promotion::whereIn('id', $promotionIds)
                ->where('hotel_id', $hotel->id)
                ->update(['is_active' => $is_active]);

            // Check if all requested promotions were updated.
            if ($updatedCount !== count($promotionIds)) {
                DB::rollBack();
                // Find IDs that were not updated (either not found or don't belong to the hotel)
                $foundPromotions = Promotion::whereIn('id', $promotionIds)
                    ->where('hotel_id', $hotel->id)
                    ->pluck('id')
                    ->toArray();
                $notUpdatedIds = array_diff($promotionIds, $foundPromotions);

                return response()->json([
                    'error' => 'Some promotions were not found or do not belong to this hotel.',
                    'not_updated_ids' => $notUpdatedIds,
                ], 404);
            }

            DB::commit();

            return $this->successResponse(['updated_count' => $updatedCount], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(['message' => $e->getMessage()], 500);
        }
    }
    public function generateCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prefix' => 'nullable|string|max:10',
            'length' => 'nullable|integer|min:4|max:20',
            'type' => 'nullable|in:alphanumeric,alphabetic,numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 400, $validator->errors());
        }

        $prefix = $request->get('prefix', '');
        $length = $request->get('length', 8);
        $type = $request->get('type', 'alphanumeric');

        $attempts = 0;
        $maxAttempts = 10;

        do {
            $code = $this->generateUniqueCode($prefix, $length, $type);
            $exists = Promotion::where('promotion_code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($exists) {
            // Fallback: add timestamp
            $code = $prefix . $this->generateRandomString($length - strlen($prefix) - 4, $type) . date('His');
        }
        return $this->successResponse(['code' => $code]);
    }
    /**
     * Generate unique promotion code
     */
    private function generateUniqueCode($prefix, $length, $type)
    {
        $remainingLength = $length - strlen($prefix);
        return $prefix . $this->generateRandomString($remainingLength, $type);
    }

    /**
     * Generate random string
     */
    private function generateRandomString($length, $type)
    {
        $characters = '';

        switch ($type) {
            case 'alphabetic':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alphanumeric':
            default:
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $result;
    }
}
