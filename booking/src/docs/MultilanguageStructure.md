# Hotel Multi-language Structure - Updated Plugin

## 🎯 Yêu Cầu Đã Được Cập Nhật

✅ **Chỉ có tên khách sạn, địa chỉ là theo ngôn ngữ**
✅ **Số điện thoại, tên miền, địa chỉ email KHÔNG theo ngôn ngữ**

## 📊 Database Structure Mới

### **Table 1: wp_hotels** (Thông tin không đổi)
```sql
CREATE TABLE wp_hotels (
  id INT PRIMARY KEY AUTO_INCREMENT,
  hotel_id VARCHAR(50) UNIQUE NOT NULL,      -- hotel_001
  domain VARCHAR(255) UNIQUE NOT NULL,       -- paradise.hotel.com (KHÔNG đổi)
  api_token VARCHAR(255) UNIQUE NOT NULL,    -- hotel_001_abc123xyz
  api_base_url VARCHAR(255) NOT NULL,        -- https://api.hotel.com/api

  -- KHÔNG theo ngôn ngữ
  currency VARCHAR(10) DEFAULT 'VND',
  timezone VARCHAR(50) DEFAULT 'Asia/Ho_Chi_Minh',
  primary_color VARCHAR(20) DEFAULT '#3b82f6',
  logo_url TEXT,
  phone VARCHAR(50),                         -- +84 28 1234 5678
  email VARCHAR(255),                        -- info@paradise.hotel.com
  check_in_time VARCHAR(10) DEFAULT '14:00',
  check_out_time VARCHAR(10) DEFAULT '12:00',

  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Table 2: wp_hotels_i18n** (Thông tin đa ngôn ngữ)
```sql
CREATE TABLE wp_hotels_i18n (
  id INT PRIMARY KEY AUTO_INCREMENT,
  hotel_id VARCHAR(50) NOT NULL,             -- hotel_001
  language VARCHAR(10) NOT NULL,             -- 'vi', 'en', 'ko', 'ja'

  -- THEO ngôn ngữ
  hotel_name VARCHAR(255) NOT NULL,          -- Tên khách sạn
  address TEXT,                              -- Địa chỉ theo ngôn ngữ
  description TEXT,                          -- Mô tả
  cancellation_policy TEXT,                  -- Chính sách hủy

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (hotel_id) REFERENCES wp_hotels(hotel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_hotel_lang (hotel_id, language)
);
```

## 🔧 WordPress API Updated

### **API Call với Language Support:**
```
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=vi
```

### **Response Structure:**
```json
{
  "success": true,
  "wp_id": "hotel_001",
  "api_token": "hotel_001_abc123xyz",
  "config": {
    // THEO ngôn ngữ
    "hotel_name": "Khách Sạn Paradise",
    "address": "123 Đường Biển, Đà Nẵng, Việt Nam",
    "description": "Khách sạn sang trọng bên bờ biển với view tuyệt đẹp",
    "cancellation_policy": "Hủy miễn phí trước 24 giờ",

    // KHÔNG theo ngôn ngữ
    "domain": "paradise.hotel.com",
    "currency": "VND",
    "timezone": "Asia/Ho_Chi_Minh",
    "language": "vi",
    "theme": {
      "primary_color": "#e11d48",
      "logo": "https://paradise.hotel.com/logo.png"
    },
    "contact": {
      "phone": "+84 28 1234 5678",        // KHÔNG đổi
      "email": "info@paradise.hotel.com", // KHÔNG đổi
      "address": "123 Đường Biển, Đà Nẵng, Việt Nam" // THEO ngôn ngữ
    },
    "policies": {
      "check_in": "14:00",              // KHÔNG đổi
      "check_out": "12:00",             // KHÔNG đổi
      "cancellation": "Hủy miễn phí trước 24 giờ"  // THEO ngôn ngữ
    },
    "api_base_url": "https://api.hotel.com/api"
  }
}
```

## 📝 PHP Plugin Functions Updated

### **Main Function:**
```php
function get_hotel_config_by_domain($request) {
    global $wpdb;

    $domain = $request->get_param('domain');
    $language = $request->get_param('language') ?: 'vi'; // Default Vietnamese

    // Get main hotel data (không theo ngôn ngữ)
    $hotel = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hotels WHERE domain = %s AND status = 'active'",
        $domain
    ));

    // Get multilingual data (theo ngôn ngữ)
    $hotel_i18n = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hotels_i18n WHERE hotel_id = %s AND language = %s",
        $hotel->hotel_id,
        $language
    ));

    return array(
        'success' => true,
        'wp_id' => $hotel->hotel_id,
        'api_token' => $hotel->api_token,
        'config' => format_hotel_config_multilingual($hotel, $hotel_i18n, $language)
    );
}
```

## 🚀 Usage Examples

### **Vietnamese:**
```
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=vi
→ "hotel_name": "Khách Sạn Paradise"
→ "address": "123 Đường Biển, Đà Nẵng, Việt Nam"
→ "phone": "+84 28 1234 5678" (KHÔNG đổi)
```

### **English:**
```
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=en
→ "hotel_name": "Paradise Hotel"
→ "address": "123 Beach Road, Da Nang, Vietnam"
→ "phone": "+84 28 1234 5678" (KHÔNG đổi)
```

### **Korean:**
```
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=ko
→ "hotel_name": "파라다이스 호텔"
→ "address": "123 비치 로드, 다낭, 베트남"
→ "phone": "+84 28 1234 5678" (KHÔNG đổi)
```

## ✅ Key Benefits

1. **📱 Phone & Email**: Không đổi theo ngôn ngữ - dễ liên lạc
2. **🌐 Domain**: Không đổi - consistent URL structure
3. **🏨 Hotel Name**: Theo ngôn ngữ - UX tốt hơn
4. **📍 Address**: Theo ngôn ngữ - người dùng hiểu rõ hơn
5. **💻 Easy Integration**: Frontend chỉ cần thêm ?language=xx

---

**Cấu trúc này đáp ứng chính xác yêu cầu: chỉ tên khách sạn và địa chỉ theo ngôn ngữ!** ✅