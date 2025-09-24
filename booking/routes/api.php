<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\RoomInventoryController;
use App\Http\Controllers\Api\RoomRateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth.api_token'])->group(function () {
    Route::put('/sync/hotels', [SyncController::class, 'updateHotel']);
    Route::put('/sync/rooms', [SyncController::class, 'updateRooms']);
    //get list
    Route::get('/sync/room-types', [HotelController::class, 'getRooms']);
    Route::get('/sync/room-rates', [HotelController::class, 'getRoomRates']);
    //promotion
    Route::get('/sync/promotions/check-code', [PromotionController::class, 'generateCode']);
    Route::apiResource('/sync/promotions', PromotionController::class);
    // Route::apiResource('room-rates', RoomRateController::class);
    Route::get('room-rates', [RoomRateController::class, 'index']);
    Route::get('room-rates/{roomRate}', [RoomRateController::class, 'show']);
    Route::delete('room-rates/{roomRate}', [RoomRateController::class, 'destroy']);
    // Route tùy chỉnh cho việc tạo mới (nhiều bản ghi)
    Route::post('room-rates', [RoomRateController::class, 'store']);
    // Route tùy chỉnh cho việc cập nhật (nhiều bản ghi)
    Route::put('room-rates/batch-update', [RoomRateController::class, 'batchUpdate']);
    //booking
    Route::get('/sync/dashboard/stats', [BookingController::class, 'getDashboardStats']);
    Route::post('/hotel/find-rooms', [BookingController::class, 'getAvailableRoomCombinations']);
    //inventory
    Route::get('/room-inventories', [RoomInventoryController::class, 'index']);
    Route::post('/room-inventories', [RoomInventoryController::class, 'storeOrUpdate']);
});
// GET /api/room-rates: Lấy danh sách giá phòng.
// POST /api/room-rates: Thêm giá phòng mới.
// PUT/PATCH /api/room-rates/{id}: Cập nhật giá phòng.
// DELETE /api/room-rates/{id}: Xóa giá phòng.
