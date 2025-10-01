import type { ApiResponse, RoomAvailability, Booking, BookingDetails, Guest } from '../types/api';
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

  async findAvailableRooms(searchParams: {
    check_in: string;
    check_out: string;
    adults: number;
    children: number;
    children_ages?: number[];
    rooms: number;
  }): Promise<ApiResponse<RoomAvailability[]>> {
    return this.makeRequest<RoomAvailability[]>('/v1/hotel/find-rooms', {
      method: 'POST',
      body: JSON.stringify(searchParams),
    });
  }

  // New method following exact flow: wp_id + token + search data → Laravel API → room list
  async searchRoomsWithWpData(
    wpId: string,
    //token: string,
    searchParams: {
      check_in: string;
      check_out: string;
      adults: number;
      children: number;
      children_ages?: number[];
      rooms: number;
    },
    language?: string
  ): Promise<ApiResponse<RoomAvailability[]>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}/v1/hotel/find-rooms`;

    const requestBody = {
      wp_id: wpId,
      language: language || 'vi',
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
    const url = `${config.apiBaseUrl}/v1/bookings/confirm`;

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
      if (response.status === 429) {
        const retryAfter = response.headers.get('Retry-After');
        alert(`Xin lỗi, bạn đã gửi quá nhiều yêu cầu đặt phòng. Vui lòng chờ ${retryAfter} giây để thử lại.`);
        throw new Error(`Rate limited. Please wait ${retryAfter} seconds.`); // Throw error instead of returning
      }
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

  /**
   * Track booking by booking number or email
   */
  async trackBooking(
    wpId: string,
    searchType: 'booking_number' | 'email',
    searchValue: string
  ): Promise<ApiResponse<Booking>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}/v1/bookings/track`;

    const requestBody = {
      wp_id: wpId,
      [searchType]: searchValue
    };

    const requestConfig: RequestInit = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(requestBody),
    };

    try {
      const response = await fetch(url, requestConfig);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Booking not found');
      }

      return data;
    } catch (error) {
      console.error('❌ Track booking error:', error);
      throw error;
    }
  }

  /**
   * Cancel booking
   */
  async cancelBooking(
    wpId: string,
    bookingId: number,
    reason: string
  ): Promise<ApiResponse<void>> {
    const config = this.getApiConfig();
    const url = `${config.apiBaseUrl}/v1/bookings/${bookingId}/cancel`;

    const requestBody = {
      wp_id: wpId,
      cancellation_reason: reason
    };

    const requestConfig: RequestInit = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(requestBody),
    };

    try {
      const response = await fetch(url, requestConfig);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to cancel booking');
      }

      return data;
    } catch (error) {
      console.error('❌ Cancel booking error:', error);
      throw error;
    }
  }
}

export const apiService = new ApiService();