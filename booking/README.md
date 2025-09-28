# Hotel Booking Multi-Tenant System

Hệ thống đặt phòng khách sạn đa thuê bao (multi-tenant) tích hợp với WordPress Multisite.

## 🎯 Tính năng chính

### ✅ **Token-Based Hotel Detection (Ưu tiên cao nhất)**
- **WordPress API Token**: Tự động detect từ WP admin context
- **Meta Tags**: `wp-api-token`, `wp-hotel-id` từ WordPress
- **Environment Variables**: `VITE_API_TOKEN` cho development
- **Token Mapping**: Database mapping token → hotel_id
- **Base64 Encoded Tokens**: Support JSON payload

### ✅ **Multi-Tenant Architecture**
- Mỗi hotel có cấu hình riêng biệt
- API headers tự động theo hotel
- Branding & theming per hotel
- Database isolation per tenant

### ✅ **Booking System**
- **Multi-room booking**: Đặt nhiều phòng cùng lúc
- **Multiple promotions**: Nhiều khuyến mãi per phòng
- **4-step booking flow**: Search → Select → Guest Info → Confirm
- **Real-time pricing**: Tính giá tự động với promotions

## 🔧 New Simplified Detection Priority

```typescript
1. Domain → Auto WordPress API → Get Token (MAIN FLOW)
2. URL Parameters (?hotel_id=xxx) - for testing
3. WordPress Context - if embedded in WP admin
4. Local Storage Cache - cached from previous session
```

**🎯 Key Change: Domain-first → Auto get token from WordPress!**

## 📝 Token Formats Support

### 1. Direct Format
```javascript
'hotel_001_api_key_xyz123' → 'hotel_001'
'paradise_hotel_001_token' → 'hotel_001'
```

### 2. Token Mapping
```javascript
'abcd1234efgh5678' → 'hotel_001' // từ database
'ijkl9012mnop3456' → 'hotel_002'
```

### 3. Base64 Encoded
```javascript
// eyJob3RlbF9pZCI6ImhvdGVsXzAwMSJ9
// Decoded: {"hotel_id":"hotel_001","site_id":"001"}
```

## 🚀 Development Setup

### 1. Installation
```bash
cd booking
npm install
npm run dev
```

### 2. Environment Configuration
```bash
# .env
VITE_API_TOKEN=your_hotel_token_here
VITE_API_BASE_URL=http://localhost:8000/api
VITE_HOTEL_ID=hotel_001
```

### 3. Testing Different Hotels
```url
# Auto domain detection (MAIN WAY)
http://paradise.localhost:5175      → Auto get Paradise Hotel token
http://oceanview.localhost:5175     → Auto get Ocean View Resort token

# URL parameters (for testing)
http://localhost:5175?hotel_id=hotel_001
http://localhost:5175?hotel_id=hotel_002

# Current domain falls back to dev
http://localhost:5175               → Development Hotel
```

## 🏗️ WordPress Integration - SIMPLIFIED

### 1. WordPress Admin Setup (ONE-TIME per hotel)
```
WP Admin → Hotels → Add New Hotel:
┌─────────────────────────────────────────────────────┐
│ Hotel Name: [Paradise Hotel                ]        │
│ Domain:     [paradise.hotel.com            ]        │
│ ✓ Auto-generate API token                           │
│ [Save Hotel] ← CẤU HÌNH 1 LẦN DUY NHẤT!            │
└─────────────────────────────────────────────────────┘
```

### 2. Main REST API Endpoint (AUTO TOKEN)
```php
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com

Response:
{
  "success": true,
  "hotel_id": "hotel_001",
  "api_token": "hotel_001_auto_generated_xyz",  ← TỰ ĐỘNG
  "config": { /* hotel settings */ }
}
```

### 3. Frontend ZERO CONFIG!
```javascript
// Frontend tự động:
1. Detect domain → paradise.hotel.com
2. Call WP API → Get token + config
3. Cache token → localStorage
4. Ready for booking!
```

## 📡 API Integration

### Headers được gửi tự động:
```javascript
{
  'Authorization': 'Bearer hotel_specific_token',
  'X-Hotel-ID': 'hotel_001',
  'X-WP-Site-ID': '001',
  'X-Domain': 'hotel1.domain.com'
}
```

### API Endpoints:
```javascript
// Laravel API endpoints
/api/sync/hotel/find-rooms        // Tìm phòng trống
/api/sync/promotions              // Lấy khuyến mãi
/api/sync/bookings/calculate-total // Tính tổng giá
/api/sync/bookings                // CRUD booking
```

## 🎨 Hotel Configuration

Mỗi hotel có config riêng:

```typescript
interface HotelConfig {
  id: string;              // hotel_001
  name: string;            // "Paradise Hotel"
  apiToken: string;        // API token riêng
  apiBaseUrl: string;      // API endpoint
  wpSiteId: string;        // WordPress site ID
  settings: {
    currency: string;      // VND, USD
    theme: {
      primaryColor: string; // #e11d48
      logo: string;        // Logo URL
    };
    contact: {
      phone: string;       // Hotline
      email: string;       // Email support
      address: string;     // Địa chỉ
    };
    policies: {
      checkIn: string;     // 14:00
      checkOut: string;    // 12:00
      cancellation: string; // Policy text
    };
  };
}
```

## 🔄 Production Deployment

### 1. WordPress Plugin Setup
- Install hotel booking plugin
- Configure API tokens per site
- Setup multisite network mapping

### 2. Token Management
- Generate unique tokens per hotel
- Store token → hotel mapping in database
- Implement token refresh mechanism

### 3. Security
- HTTPS only for token transmission
- Token expiration and rotation
- Rate limiting per hotel

## 🧪 Testing

```bash
# Test different hotels via tokens
localStorage.setItem('wp_api_token', 'demo_token_001'); // Paradise
localStorage.setItem('wp_api_token', 'demo_token_002'); // Ocean View

# Test via URL
?api_token=demo_token_001
?hotel_id=hotel_001

# Test domain fallback
hotel1.domain.com → hotel_001
hotel2.domain.com → hotel_002
```

## 📂 Project Structure

```
booking/
├── src/
│   ├── components/          # React components
│   ├── context/            # Hotel context provider
│   ├── services/           # API & tenant detection
│   ├── types/              # TypeScript interfaces
│   └── utils/              # Token utilities
├── public/
│   └── demo-config.js      # Demo hotel configs
└── README.md
```

## 🎯 Key Benefits

1. **Flexible Token Detection**: Support nhiều format token
2. **WordPress Integration**: Seamless với WP Multisite
3. **Multi-Domain Support**: Không phụ thuộc domain
4. **Scalable Architecture**: Dễ mở rộng thêm hotel
5. **Developer Friendly**: Clear debugging & testing

---

**URL hiện tại**: http://localhost:5175

**Token-based detection đã ưu tiên cao nhất!** 🎉
