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

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function index(Request $request)
    {
        // 1. Xác thực các tham số đầu vào
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:active,inactive',
            'search' => 'nullable|string',
            'type' => 'nullable|string',
            'wp_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Lấy hotel_id từ wp_id
        $hotel = Hotel::where('wp_id', $request->input('wp_id'))->first();
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found for the given wp_id.'], 404);
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
        return response()->json($promotions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $wpData = $request->json()->all();

        $validator = Validator::make($wpData, [
            'wp_id' => 'required|integer',
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpData['wp_id']);
        if (!$hotelId) {
            return response()->json(['error' => 'Invalid wp_id. Hotel not found.'], 404);
        }

        try {
            DB::beginTransaction();
            $wpData = $wpData['data'];
            dd($wpData);
            $promotion = Promotion::create([
                'hotel_id' => $hotelId,
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
                'min_stay' => $wpData['min_stay'] ?? null,
                'max_stay' => $wpData['max_stay'] ?? null
            ]);

            $promotion->roomTypes()->sync($wpData['roomtypes']);

            DB::commit();

            return response()->json(['message' => 'Promotion created successfully.', 'promotion' => $promotion->load('roomTypes')], 201);
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
        // Lấy wp_id từ request (ưu tiên từ query string hoặc header)
        $wpId = $request->input('wp_id');

        // Nếu không có wp_id, trả về lỗi
        if (is_null($wpId)) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        // Tìm hotel_id từ wp_id
        $hotelId = HotelHelper::getHotelIdByWpId($wpId);

        // Kiểm tra xem promotion có thuộc về khách sạn hợp lệ không
        if (!$hotelId || $promotion->hotel_id !== $hotelId) {
            return response()->json(['error' => 'Promotion not found or does not belong to this hotel.'], 404);
        }

        // Tải các mối quan hệ liên quan và trả về phản hồi
        return response()->json($promotion->load('roomtypes'));
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
        // Xác thực quyền sở hữu:
        $wpId = $request->input('wp_id');

        if (is_null($wpId)) {
            return response()->json(['error' => 'wp_id is required.'], 400);
        }

        $hotelId = HotelHelper::getHotelIdByWpId($wpId);

        // Kiểm tra xem khách sạn có hợp lệ và promotion có thuộc khách sạn đó không
        if (!$hotelId || $promotion->hotel_id !== $hotelId) {
            return response()->json(['error' => 'Promotion not found or does not belong to this hotel.'], 404);
        }

        // Xác thực dữ liệu đầu vào:
        $wpData = $request->json()->all();

        $validator = Validator::make($wpData, [
            'promotion_code' => ['required', 'string', Rule::unique('promotions')->ignore($promotion->id)],
            'name' => 'required|array',
            'description' => 'nullable|array',
            'type' => ['nullable', Rule::in(PromotionType::getValues())],
            'value_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'roomtypes' => 'required|array',
            'roomtypes.*' => 'required|exists:roomtypes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cập nhật dữ liệu:
        try {
            DB::beginTransaction();

            $promotion->update([
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
                'min_stay' => $wpData['min_stay'] ?? null,
                'max_stay' => $wpData['max_stay'] ?? null
            ]);

            // Cập nhật quan hệ nhiều-nhiều với bảng room_types
            $promotion->roomTypes()->sync($wpData['roomtypes']);

            DB::commit();

            return response()->json(['message' => 'Promotion updated successfully.', 'promotion' => $promotion->load('roomTypes')]);
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
        return response()->json(['code' => $code]);
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
