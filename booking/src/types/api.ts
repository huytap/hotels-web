export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
}

export interface Room {
  id: number;
  name: string;
  type: string;
  description: string;
  capacity: number;
  amenities: string[];
  images: string[];
  base_price: number;
  currency: string;
  // Extended fields for popup
  area?: string;
  bed_type?: string;
  room_amenities?: string[];
  bathroom_amenities?: string[];
  view?: string;
  gallery_images?: string[];
  featured_image?: string;
}

export interface RoomRate {
  id: number;
  room_id: number;
  date: string;
  rate: number;
  inventory: number;
  min_stay: number;
  is_available: boolean;
}

export interface Promotion {
  id: number;
  code: string;
  name: string;
  description: string;
  type: 'percentage' | 'fixed' | 'free_night';
  value: number;
  start_date: string;
  end_date: string;
  min_nights: number;
  applicable_room_types: string[];
  is_active: boolean;
  max_uses: number;
  current_uses: number;
}

export interface BookingDetails {
  check_in: string;
  check_out: string;
  adults: number;
  children: number;
  rooms: number;
}

export interface Guest {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  nationality: string;
}

export interface BookingRoom {
  room_id: number;
  room_name: string;
  room_type: string;
  rate_per_night: number;
  total_nights: number;
  subtotal: number;
  promotion_id?: string;
  promotion_discount?: number;
}

export interface Booking {
  id: number;
  guest: Guest;
  booking_details: BookingDetails;
  rooms: BookingRoom[];
  total_price: number;
  discount_amount: number;
  final_price: number;
  status: 'pending' | 'confirmed' | 'cancelled';
  special_requests?: string;
  created_at: string;
}

export interface RoomAvailability {
  room_id: number;
  room: Room;
  available_inventory: number;
  rate_per_night: number;
  total_price: number;
  applicable_promotions: Promotion[];
}

// Laravel API Response format
export interface LaravelRoomResponse {
  total_price: number;
  combination_details: CombinationDetail[];
}

export interface CombinationDetail {
  room_type: {
    id: number;
    name: string;
    description: string;
    amenities: string;
    gallery: string;
  };
  quantity: number;
  available_rooms: number;
  base_price_total: number;
  promotions: { [key: string]: PromotionDetail };
}
export interface Combination {
  total_price: number;
  combination_details: CombinationDetail[];
}
export interface PromotionDetail {
  details: {
    id: number;
    promotion_code: string;
    name: { vi?: string; en?: string };
    description: { vi?: string };
    value_type: string;
    value: string;
    start_date: string;
    end_date: string;
    min_stay: number;
    is_active: number;
  };
  discounted_price_total: number;
}

export interface BookingCalculation {
  subtotal: number;
  promotions_applied: {
    promotion: Promotion;
    discount_amount: number;
  }[];
  total_discount: number;
  final_total: number;
  rooms: BookingRoom[];
}