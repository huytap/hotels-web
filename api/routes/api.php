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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::prefix('v1')->group(function () {
    Route::post('/hotel/find-rooms', [BookingController::class, 'getAvailableRoomCombinations']);
    Route::post('/bookings/confirm', [BookingController::class, 'store'])->middleware('throttle:booking');
    Route::get('/roomtypes', [HotelController::class, 'getRooms']);
    Route::get('/roomtypes/{id}', [HotelController::class, 'getRoomDetail']);
    //promotion
    Route::get('/promotions/check-code', [PromotionController::class, 'generateCode']);
    Route::middleware('auth.api_token')->group(function () {
        Route::put('/hotels', [SyncController::class, 'updateHotel']);
        Route::put('/rooms', [SyncController::class, 'updateRooms']);
        Route::put('/promotions/update-status', [PromotionController::class, 'updateStatus']);
        Route::apiResource('/promotions', PromotionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('/room-rates', RoomRateController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::put('/room-rates/batch-update', [RoomRateController::class, 'batchUpdate']);

        // Child Age Policy & Room Pricing Policy APIs
        Route::get('/child-age-policy', [BookingController::class, 'getChildAgePolicy']);
        Route::put('/child-age-policy', [BookingController::class, 'updateChildAgePolicy']);
        Route::get('/room-pricing-policies', [BookingController::class, 'getRoomPricingPolicies']);
        Route::put('/room-pricing-policies/{roomtypeId}', [BookingController::class, 'updateRoomPricingPolicy']);
        //booking
        Route::get('/dashboard/stats', [BookingController::class, 'getDashboardStats']);
        Route::prefix('/bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            Route::get('/{id}', [BookingController::class, 'show']);
            Route::put('/{id}/status', [BookingController::class, 'updateStatus']);
            Route::put('/{id}', [BookingController::class, 'update']);
            Route::delete('/{id}', [BookingController::class, 'destroy']);
            Route::get('/{id}/pricing', [BookingController::class, 'getPricing']);
            Route::post('/calculate-total', [BookingController::class, 'calculateTotal']);
        });
        // Rate Templates
        Route::prefix('/room-management')->group(function () {
            Route::prefix('/templates')->group(function () {
                Route::get('/', [RoomManagementController::class, 'getTemplates']);
                Route::post('/', [RoomManagementController::class, 'createTemplate']);
                Route::get('/{id}', [RoomManagementController::class, 'getTemplate']);
                Route::put('/{id}', [RoomManagementController::class, 'updateTemplate']);
                Route::delete('/{id}', [RoomManagementController::class, 'deleteTemplate']);
            });
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
            Route::post('/apply-template', [RoomManagementController::class, 'applyTemplate']);
        });
    });
});
