// Demo configuration for testing multi-tenant functionality
// This would normally be provided by WordPress
window.hotelDemoConfig = {
  hotel_001: {
    hotel_name: "Khách Sạn Paradise",
    api_token: "demo_token_001",
    api_base_url: "http://localhost:8000/api",
    site_id: "001",
    currency: "VND",
    timezone: "Asia/Ho_Chi_Minh",
    language: "vi",
    theme: {
      primary_color: "#e11d48",
      logo: "/paradise-logo.png",
      favicon: "/paradise-favicon.ico"
    },
    contact: {
      phone: "+84 28 1234 5678",
      email: "info@paradise-hotel.com",
      address: "123 Đường Nguyễn Huệ, Quận 1, TP.HCM"
    },
    policies: {
      check_in: "14:00",
      check_out: "12:00",
      cancellation: "Hủy miễn phí trước 24 giờ"
    }
  },
  hotel_002: {
    hotel_name: "Resort Ocean View",
    api_token: "demo_token_002",
    api_base_url: "http://localhost:8000/api",
    site_id: "002",
    currency: "VND",
    timezone: "Asia/Ho_Chi_Minh",
    language: "vi",
    theme: {
      primary_color: "#0ea5e9",
      logo: "/ocean-logo.png",
      favicon: "/ocean-favicon.ico"
    },
    contact: {
      phone: "+84 236 3123 456",
      email: "booking@oceanview-resort.com",
      address: "456 Đường Biển, Nha Trang, Khánh Hòa"
    },
    policies: {
      check_in: "15:00",
      check_out: "11:00",
      cancellation: "Hủy miễn phí trước 48 giờ"
    }
  },
  hotel_dev: {
    hotel_name: "Hotel Development",
    api_token: "dev_token",
    api_base_url: "http://localhost:8000/api",
    site_id: "dev",
    currency: "VND",
    timezone: "Asia/Ho_Chi_Minh",
    language: "vi",
    theme: {
      primary_color: "#3b82f6",
      logo: null,
      favicon: null
    },
    contact: {
      phone: "+84 123 456 789",
      email: "dev@hotel.com",
      address: "Development Environment"
    },
    policies: {
      check_in: "14:00",
      check_out: "12:00",
      cancellation: "Hủy miễn phí trước 24 giờ"
    }
  }
};