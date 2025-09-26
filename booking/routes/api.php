<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\RoomRateController;
use App\Http\Controllers\Api\RoomManagementController;
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
    Route::prefix('sync')->group(function () {
        Route::put('/hotels', [SyncController::class, 'updateHotel']);
        Route::put('/rooms', [SyncController::class, 'updateRooms']);
        //get list
        Route::get('/roomtypes', [HotelController::class, 'getRooms']);
        Route::get('/room-rates', [HotelController::class, 'getRoomRates']);
    });
    //promotion
    Route::prefix('sync/promotions')->group(function () {
        Route::get('/check-code', [PromotionController::class, 'generateCode']);
        Route::put('/update-status', [PromotionController::class, 'updateStatus']);
    });
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

    // Booking CRUD operations
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::put('/{id}', [BookingController::class, 'update']);
        Route::delete('/{id}', [BookingController::class, 'destroy']);
        Route::patch('/{id}/status', [BookingController::class, 'updateStatus']);
        Route::get('/{id}/pricing', [BookingController::class, 'getPricing']);
    });

    // Room Management - Combined API for Rates & Inventory
    Route::prefix('sync/room-management')->group(function () {
        // Calendar data (rates + inventory combined)
        Route::get('/calendar', [RoomManagementController::class, 'getCalendarData']);

        // Rate management
        Route::post('/rates', [RoomManagementController::class, 'updateRate']);
        Route::post('/rates/bulk', [RoomManagementController::class, 'bulkUpdateRates']);
        // Inventory management
        Route::post('/inventory', [RoomManagementController::class, 'updateInventory']);
        Route::post('/inventory/bulk', [RoomManagementController::class, 'bulkUpdateInventory']);
        // Bulk copy operations
        Route::post('/copy-all', [RoomManagementController::class, 'copyAll']);

        // Combined operations
        Route::post('/update-both', [RoomManagementController::class, 'updateBoth']);

        // Booking operations
        Route::post('/book', [RoomManagementController::class, 'bookRoom']);
        Route::post('/cancel', [RoomManagementController::class, 'cancelBooking']);

        // Statistics
        Route::get('/statistics', [RoomManagementController::class, 'getStatistics']);

        // Rate Templates
        Route::get('/templates', [RoomManagementController::class, 'getTemplates']);
        Route::post('/templates', [RoomManagementController::class, 'createTemplate']);
        Route::get('/templates/{id}', [RoomManagementController::class, 'getTemplate']);
        Route::put('/templates/{id}', [RoomManagementController::class, 'updateTemplate']);
        Route::delete('/templates/{id}', [RoomManagementController::class, 'deleteTemplate']);
        Route::post('/apply-template', [RoomManagementController::class, 'applyTemplate']);
    });
});
// GET /api/room-rates: Lấy danh sách giá phòng.
// POST /api/room-rates: Thêm giá phòng mới.
// PUT/PATCH /api/room-rates/{id}: Cập nhật giá phòng.
// DELETE /api/room-rates/{id}: Xóa giá phòng.
