import type { ApiResponse, Room, RoomAvailability, Promotion, Booking, BookingCalculation, BookingDetails, Guest } from '../types/api';
import type { HotelConfig } from '../types/tenant';

class ApiService {
  private hotelConfig: HotelConfig | null = null;

  public setHotelConfig(config: HotelConfig) {
    this.hotelConfig = config;
  }

  private getApiConfig() {
    if (!this.hotelConfig) {
      throw new Error('Hotel configuration not set. Please configure hotel first.');
    }
    return this.hotelConfig;
  }

  private async makeRequest<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}${endpoint}`;
    // Get token từ localStorage (auto-retrieved từ domain)
    // const autoToken = localStorage.getItem('hotel_api_token') || config.apiToken;
    // console.log(autoToken)
    const requestConfig: RequestInit = {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        //'Authorization': `Bearer ${autoToken}`,
        //'X-Hotel-ID': config.id,
        //'X-WP-Site-ID': config.wpSiteId,
        //'X-Domain': config.domain,
        //'X-Auto-Token': 'true', // Flag để backend biết đây là auto token
        ...options.headers,
      },
    };

    try {
      const response = await fetch(url, requestConfig);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'API request failed');
      }

      return data;
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  // Room APIs
  async getRoomTypes(): Promise<ApiResponse<Room[]>> {
    return this.makeRequest<Room[]>('/sync/roomtypes');
  }

  async findAvailableRooms(searchParams: {
    check_in: string;
    check_out: string;
    adults: number;
    children: number;
    rooms: number;
  }): Promise<ApiResponse<RoomAvailability[]>> {
    return this.makeRequest<RoomAvailability[]>('/sync/hotel/find-rooms', {
      method: 'POST',
      body: JSON.stringify(searchParams),
    });
  }

  // New method following exact flow: wp_id + token + search data → Laravel API → room list
  async searchRoomsWithWpData(
    wpId: string,
    token: string,
    searchParams: {
      check_in: string;
      check_out: string;
      adults: number;
      children: number;
      rooms: number;
    }
  ): Promise<ApiResponse<RoomAvailability[]>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}/sync/hotel/find-rooms`;

    const requestBody = {
      wp_id: wpId,
      data: searchParams
    };

    const requestConfig: RequestInit = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        //'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(requestBody),
    };

    try {
      const response = await fetch(url, requestConfig);
      console.log('📡 Laravel Response status:', response.status, response.statusText);

      const data = await response.json();
      console.log('📦 Laravel Response data:', data);

      if (!response.ok) {
        throw new Error(data.message || 'API request failed');
      }

      // Validate room data structure
      if (data.success && Array.isArray(data.data)) {
        console.log('🏨 Room items structure check:');
        data.data.forEach((room: any, index: number) => {
          console.log(`Room ${index}:`, {
            room_id: room.room_id,
            id: room.id,
            hasRoomProperty: !!room.room,
            roomProperty: room.room
          });
        });
      }

      return data;
    } catch (error) {
      console.error('❌ Room search API error:', error);
      throw error;
    }
  }

  // Promotion APIs
  async getPromotions(filters?: {
    room_type?: string;
    check_in?: string;
    check_out?: string;
    is_active?: boolean;
  }): Promise<ApiResponse<Promotion[]>> {
    const queryParams = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) {
          queryParams.append(key, value.toString());
        }
      });
    }

    const endpoint = `/sync/promotions${queryParams.toString() ? '?' + queryParams.toString() : ''}`;
    return this.makeRequest<Promotion[]>(endpoint);
  }

  async validatePromotionCode(code: string, bookingDetails: BookingDetails): Promise<ApiResponse<Promotion>> {
    return this.makeRequest<Promotion>('/sync/promotions/check-code', {
      method: 'POST',
      body: JSON.stringify({ code, ...bookingDetails }),
    });
  }

  // Booking APIs
  async calculateBookingTotal(bookingData: {
    rooms: { room_id: string; quantity: number }[];
    booking_details: BookingDetails;
    promotion_codes?: string[];
  }): Promise<ApiResponse<BookingCalculation>> {
    return this.makeRequest<BookingCalculation>('/sync/bookings/calculate-total', {
      method: 'POST',
      body: JSON.stringify(bookingData),
    });
  }

  async createBooking(bookingData: {
    guest: Guest;
    booking_details: BookingDetails;
    rooms: { room_id: string; quantity: number; promotion_id?: string }[];
    special_requests?: string;
  }): Promise<ApiResponse<Booking>> {
    return this.makeRequest<Booking>('/sync/bookings', {
      method: 'POST',
      body: JSON.stringify(bookingData),
    });
  }

  async getBooking(id: string): Promise<ApiResponse<Booking>> {
    return this.makeRequest<Booking>(`/sync/bookings/${id}`);
  }

  async updateBookingStatus(id: string, status: string): Promise<ApiResponse<Booking>> {
    return this.makeRequest<Booking>(`/sync/bookings/${id}/status`, {
      method: 'PATCH',
      body: JSON.stringify({ status }),
    });
  }

  // Room Management APIs
  async getRoomRates(params: {
    room_type?: string;
    date_from?: string;
    date_to?: string;
  }): Promise<ApiResponse<any>> {
    const queryParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value) queryParams.append(key, value);
    });

    return this.makeRequest(`/room-rates?${queryParams.toString()}`);
  }

  async getCalendarData(params: {
    room_type: string;
    date_from: string;
    date_to: string;
  }): Promise<ApiResponse<any>> {
    const queryParams = new URLSearchParams(params);
    return this.makeRequest(`/sync/room-management/calendar?${queryParams.toString()}`);
  }

  // Submit booking with wp_id + token approach
  async submitBooking(
    wpId: string,
    //token: string,
    bookingData: {
      booking_details: BookingDetails;
      selected_rooms: { roomId: number; quantity: number; promotionId?: number }[];
      guest_info: Guest;
    }
  ): Promise<ApiResponse<Booking>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}/sync/bookings`;

    const requestBody = {
      wp_id: wpId,
      data: {
        guest: bookingData.guest_info,
        booking_details: bookingData.booking_details,
        rooms: bookingData.selected_rooms.map(room => ({
          room_id: room.roomId.toString(),
          quantity: room.quantity,
          promotion_id: room.promotionId?.toString()
        }))
      }
    };

    const requestConfig: RequestInit = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        //'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(requestBody),
    };

    try {
      const response = await fetch(url, requestConfig);
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Booking submission failed');
      }

      return data;
    } catch (error) {
      console.error('❌ Booking submission error:', error);
      throw error;
    }
  }
}

export const apiService = new ApiService();