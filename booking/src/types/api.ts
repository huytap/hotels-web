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
  is_extra_bed_available?: boolean;
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
  children_ages?: number[];
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
  roomId: number;
  quantity: number;
  promotionId?: number;
  rate_per_night: number;
  total_price: number;
  room_name: string;
}

export interface BookingDetail {
  id: number;
  roomtype_id: number;
  adults: number;
  children: number;
  quantity: number;
  price_per_night: number;
  sub_total: number;
  promotion_id?: number;
  roomtype?: {
    id: number;
    name: string;
  };
}

export interface Booking {
  id: number;
  booking_number: string;
  first_name: string;
  last_name: string;
  email: string;
  phone_number: string;
  nationality?: string;
  check_in: string;
  check_out: string;
  nights: number;
  guests: number;
  total_amount: number;
  discount_amount: number;
  tax_amount: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show';
  notes?: string;
  created_at: string;
  updated_at: string;
  booking_details?: BookingDetail[];
}

export interface PricingBreakdown {
  base_price: number;
  base_nights: number;
  base_total: number;
  adult_surcharge_per_night: number;
  adult_surcharge_nights: number;
  adult_surcharge_total: number;
  children_surcharge_per_night: number;
  children_surcharge_nights: number;
  children_surcharge_total: number;
  promotion_applicable_amount: number; // base + adult surcharge (có thể áp dụng khuyến mãi)
  non_promotion_amount: number; // extra bed adult + children surcharge (không áp dụng khuyến mãi)
  promotion_discount: number;
  final_total: number;
  // Breakdown of non_promotion_amount
  extra_bed_adult_surcharge_total: number;
  extra_bed_adult_count: number;
  children_breakdown?: {
    free_children: number;
    surcharge_children: number;
    adult_rate_children: number;
  };
}

export interface RoomAvailability {
  room_id: number;
  room: Room;
  available_inventory: number;
  rate_per_night: number;
  total_price: number;
  applicable_promotions: Promotion[];
  pricing_breakdown?: PricingBreakdown;
  effective_capacity?: number; // Capacity thực tế bao gồm khả năng thêm người với phụ thu
  is_extra_bed_available?: boolean;
  requires_extra_bed?: boolean; // Indicates if the current guest count needs extra bed
  extra_bed_options?: {
    without_extra_bed?: RoomPricingOption;
    with_extra_bed?: RoomPricingOption;
  };
}

export interface RoomPricingOption {
  can_accommodate: boolean;
  rate_per_night: number;
  total_price: number;
  pricing_breakdown?: PricingBreakdown;
  guest_accommodation_note?: string;
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

export interface TaxSettings {
  vat_rate: number;
  service_charge_rate: number;
  prices_include_tax: boolean;
}