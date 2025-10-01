<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     // Bypass authentication completely in development
    //     if (app()->environment('local')) {
    //         return $next($request);
    //     }

    //     // Lấy token từ header Authorization
    //     $authHeader = $request->header('Authorization');
    //     if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    //         return response()->json(['message' => 'Missing or invalid Authorization header.'], 401);
    //     }
    //     $providedToken = trim(substr($authHeader, 7));
    //     $wpId = $request->input('wp_id') ?? $request->json('wp_id') ?? $request->query('wp_id');
    //     if (empty($wpId)) {
    //         return response()->json(['message' => 'Missing wp_id parameter in request body.'], 400);
    //     }
    //     // Chuẩn bị URL để xác thực token với WordPress
    //     $wpApiUrl = config('services.wordpress.url') . config('services.wordpress.token_endpoint');
    //     try {
    //         // Gửi yêu cầu xác thực đến WordPress API
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $providedToken,
    //         ])->get($wpApiUrl, ['wp_id' => $wpId]);
    //         // Kiểm tra trạng thái phản hồi
    //         if ($response->successful()) {
    //             // Nếu phản hồi thành công, cho phép yêu cầu tiếp tục
    //             return $next($request);
    //         }

    //         // Nếu không thành công, trả về lỗi tương ứng
    //         if ($response->status() === 403) {
    //             return response()->json(['message' => 'Invalid or expired token.'], 403);
    //         }
    //         if ($response->status() === 401) {
    //             return response()->json(['message' => 'Unauthorized.'], 401);
    //         }

    //         // Xử lý các lỗi khác
    //         return response()->json(['message' => 'Authentication failed.'], $response->status());
    //     } catch (\Exception $e) {
    //         // Xử lý lỗi khi không thể kết nối đến WordPress
    //         return response()->json(['message' => 'Could not connect to authentication service.'], 503);
    //     }
    // }


    public function handle(Request $request, Closure $next): Response
    {
        // Bypass authentication in local
        if (app()->environment('local')) {
            return $next($request);
        }
        $authHeader = $request->header('Authorization');
        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            // Request từ WP -> xác thực token
            $providedToken = trim(substr($authHeader, 7));
            $wpId = $request->input('wp_id') ?? $request->json('wp_id') ?? $request->query('wp_id');

            if (empty($wpId)) {
                return response()->json(['message' => 'Missing wp_id parameter.'], 400);
            }

            $wpApiUrl = config('services.wordpress.url') . config('services.wordpress.token_endpoint');

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $providedToken,
                ])->get($wpApiUrl, ['wp_id' => $wpId]);

                if ($response->successful()) {
                    return $next($request);
                }

                if ($response->status() === 403) {
                    return response()->json(['message' => 'Invalid or expired token.'], 403);
                }
                if ($response->status() === 401) {
                    return response()->json(['message' => 'Unauthorized.'], 401);
                }

                return response()->json(['message' => 'Authentication failed.'], $response->status());
            } catch (\Exception $e) {
                return response()->json(['message' => 'Could not connect to authentication service.'], 503);
            }
        }

        // Nếu không có token -> coi là request từ React
        // Có thể check IP nếu muốn giới hạn chỉ server nội bộ
        $allowedIps = ['127.0.0.1', '::1']; // hoặc IP server React
        if (!in_array($request->ip(), $allowedIps)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
