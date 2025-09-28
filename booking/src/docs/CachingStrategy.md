# Hotel Data Caching Strategy

## 🎯 Problem: Tránh gọi API mỗi lần tải trang

### **Current Flow (Optimized):**
```
1. Frontend load → Check cache first
2. If cache valid → Use cached wp_id + token
3. If cache expired/empty → Call WordPress → Get wp_id → Call Laravel API → Get token
4. Cache result for 24h
```

## 💾 Cache Structure trong localStorage

### **Cache Keys:**
```javascript
localStorage items per domain:
┌─────────────────────────────────────────────────────────────┐
│ wp_id_paradise.hotel.com      → "12345"                     │
│ token_paradise.hotel.com      → "eyJ0eXAiOiJKV1QiLCJhbGc"   │
│ config_paradise.hotel.com     → "{hotel_name: Paradise...}" │
│ cache_expiry_paradise.hotel.com → "1699123456789"           │
└─────────────────────────────────────────────────────────────┘
```

### **Cache Logic:**
```typescript
// Check cache validity
private isCacheValid(domain: string): boolean {
  const expiry = localStorage.getItem(`cache_expiry_${domain}`);
  const now = Date.now();
  const expiryTime = parseInt(expiry);

  return now < expiryTime; // Valid for 24 hours
}
```

## 🔄 Complete Flow với Cache

### **First Load (Cache empty):**
```
User → paradise.hotel.com
 ↓
Frontend: Check cache → Empty
 ↓
Call WP: /wp-json/hotel-info/v1/wp-id?domain=paradise.hotel.com
 ↓
WP Response: { success: true, wp_id: "12345" }
 ↓
Call Laravel: POST /api/auth/get-token { wp_id: "12345" }
 ↓
Laravel Response: { success: true, token: "xyz", hotel_config: {...} }
 ↓
Cache all data for 24h
 ↓
Ready for booking!
```

### **Subsequent Loads (Cache hit):**
```
User → paradise.hotel.com (reload/revisit)
 ↓
Frontend: Check cache → Found + Valid
 ↓
Use cached wp_id + token immediately
 ↓
Ready for booking! (NO API calls needed)
```

### **Cache Expired (24h later):**
```
User → paradise.hotel.com (after 24h)
 ↓
Frontend: Check cache → Found but Expired
 ↓
Clear old cache + Repeat first load flow
 ↓
Fresh wp_id + token + Cache for another 24h
```

## 🛠️ WordPress Plugin Requirements

### **Hotel Info Plugin endpoint cần implement:**

```php
// wp-content/plugins/hotel-info/hotel-info.php

add_action('rest_api_init', 'register_hotel_info_endpoints');

function register_hotel_info_endpoints() {
    register_rest_route('hotel-info/v1', '/wp-id', array(
        'methods' => 'GET',
        'callback' => 'get_wp_id_by_domain',
        'permission_callback' => '__return_true'
    ));
}

function get_wp_id_by_domain($request) {
    $domain = $request->get_param('domain');

    if (!$domain) {
        return new WP_Error('missing_domain', 'Domain parameter required', array('status' => 400));
    }

    // Logic để map domain → wp_id
    // Có thể từ database hoặc config
    $wp_id = get_wp_id_for_domain($domain);

    if (!$wp_id) {
        return new WP_Error('not_found', 'Hotel not found for domain', array('status' => 404));
    }

    return array(
        'success' => true,
        'wp_id' => $wp_id,
        'domain' => $domain
    );
}

function get_wp_id_for_domain($domain) {
    // Example mapping logic:
    $domain_mappings = array(
        'paradise.hotel.com' => '12345',
        'oceanview.hotel.com' => '67890',
        'mountain.hotel.com' => '11111'
    );

    return isset($domain_mappings[$domain]) ? $domain_mappings[$domain] : null;
}
```

## 🏗️ Laravel API Requirements

### **Auth endpoint cần implement:**

```php
// routes/api.php
Route::post('/auth/get-token', [AuthController::class, 'getTokenByWpId']);

// AuthController.php
public function getTokenByWpId(Request $request)
{
    $wpId = $request->input('wp_id');

    if (!$wpId) {
        return response()->json([
            'success' => false,
            'message' => 'wp_id is required'
        ], 400);
    }

    // Find hotel by wp_id
    $hotel = Hotel::where('wp_id', $wpId)->first();

    if (!$hotel) {
        return response()->json([
            'success' => false,
            'message' => 'Hotel not found for wp_id'
        ], 404);
    }

    // Generate or get existing token
    $token = $hotel->generateApiToken(); // hoặc existing token

    return response()->json([
        'success' => true,
        'token' => $token,
        'hotel_config' => [
            'hotel_id' => $hotel->id,
            'name' => $hotel->name,
            'currency' => $hotel->currency,
            // ... other config
        ]
    ]);
}
```

## 🎯 Cache Benefits

### **Performance:**
- ✅ **First load:** 2 API calls (WP + Laravel)
- ✅ **Subsequent loads:** 0 API calls (instant load)
- ✅ **24h validity:** Balance between performance và data freshness

### **User Experience:**
- ✅ **Fast page loads** after first visit
- ✅ **Offline-like experience** với cached data
- ✅ **Automatic refresh** when cache expires

### **API Efficiency:**
- ✅ **Reduced server load** (no repeated calls)
- ✅ **Better rate limiting** compliance
- ✅ **Network bandwidth savings**

## 🔧 Cache Management

### **Clear cache manually (for testing):**
```javascript
// Development tools
tenantDetection.clearCacheForDomain('paradise.hotel.com');

// Clear all hotel caches
localStorage.clear(); // Nuclear option
```

### **Auto cache refresh (future enhancement):**
```javascript
// Could implement background refresh
// when cache is close to expiry
if (timeUntilExpiry < 1_hour) {
    backgroundRefreshCache();
}
```

---

**Result: Dramatically faster subsequent page loads!** 🚀