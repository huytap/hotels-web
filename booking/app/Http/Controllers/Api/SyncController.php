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
            // Xây dựng mảng dữ liệu dịch thuật từ dữ liệu API
            $translatableData = [];
            foreach (['name', 'address', 'phone', 'email', 'map'] as $key) {
                $translatableData[$key] = [];
            }

            foreach ($wpData['data'] as $hotel) {
                $lang = $hotel['lang'];
                foreach (['name', 'address', 'phone', 'email', 'map'] as $key) {
                    $translatableData[$key][$lang] = $hotel[$key];
                }
            }
            // 2. Tìm hoặc tạo mới bản ghi khách sạn
            $hotel = Hotel::firstOrNew(['wp_id' => $wpId]);
            // Gán dữ liệu có thể dịch
            foreach ($translatableData as $key => $translations) {
                $hotel->setTranslations($key, $translations);
            }
            $hotel->wp_id = $wpId;
            // Gán thời gian cập nhật từ WordPress nếu có
            if ($wpUpdatedAt) {
                $hotel->wp_updated_at = Carbon::parse($wpUpdatedAt);
            } else {
                $hotel->wp_updated_at = now();
            }

            // 3. Lưu bản ghi
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
