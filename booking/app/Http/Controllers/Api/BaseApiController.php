<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

abstract class BaseApiController extends Controller
{
    protected function successResponse($data, $message = 'Success', $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function paginatedResponse($data, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ]);
    }

    protected function getHotelFromRequest(Request $request)
    {
        $wpId = $request->input('wp_id') ?? $request->json('wp_id');
        if (!$wpId) {
            return null;
        }

        return \App\Models\Hotel::where('wp_id', $wpId)->first();
    }

    protected function validateHotelAccess(Request $request)
    {
        $hotel = $this->getHotelFromRequest($request);

        if (!$hotel) {
            return $this->errorResponse('Hotel not found', 404);
        }
        if (!$hotel->is_active) {
            return $this->errorResponse('Hotel is inactive', 403);
        }

        return $hotel;
    }
}
