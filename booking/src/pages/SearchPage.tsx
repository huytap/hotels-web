import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { format, addDays } from 'date-fns';
import BookingSearch from '../components/BookingSearch';
import RoomList from '../components/RoomList';
import BookingSummary from '../components/BookingSummary';
import type { BookingDetails, RoomAvailability } from '../types/api';
import { apiService } from '../services/api';
import { tenantDetection } from '../services/tenantDetection';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
}

const SearchPage: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [autoSearchTriggered, setAutoSearchTriggered] = useState(false);
  const [bookingDetails, setBookingDetails] = useState<BookingDetails | null>(null);
  const [availableRooms, setAvailableRooms] = useState<RoomAvailability[]>([]);
  const [selectedRooms, setSelectedRooms] = useState<SelectedRoom[]>([]);

  // Create dummy data for testing
  const createDummyData = () => {
    console.log('🧪 Creating dummy data for testing...');

    const dummyRooms: RoomAvailability[] = [
      {
        room_id: 'room-001',
        room: {
          id: 'room-001',
          name: 'Phòng Deluxe',
          type: 'deluxe',
          description: 'Phòng cao cấp với view biển',
          capacity: 2,
          amenities: ['WiFi', 'TV', 'AC'],
          images: [],
          base_price: 1500000,
          currency: 'VND'
        },
        available_inventory: 5,
        rate_per_night: 1500000,
        total_price: 1500000,
        applicable_promotions: [
          {
            id: 'promo-001',
            code: 'SUMMER20',
            name: 'Khuyến mãi mùa hè',
            description: 'Giảm 20% cho mùa hè',
            type: 'percentage',
            value: 20,
            start_date: '2025-06-01',
            end_date: '2025-08-31',
            min_nights: 1,
            applicable_room_types: ['deluxe'],
            is_active: true,
            max_uses: 100,
            current_uses: 10
          }
        ]
      }
    ];

    const dummyBookingDetails = {
      check_in: '2025-09-28',
      check_out: '2025-09-30',
      adults: 2,
      children: 0,
      rooms: 1
    };

    setAvailableRooms(dummyRooms);
    setBookingDetails(dummyBookingDetails);

    console.log('🧪 Dummy data created:', { dummyRooms, dummyBookingDetails });
  };

  // Get default booking details for auto-load
  const getDefaultBookingDetails = (): BookingDetails => {
    return {
      check_in: format(new Date(), 'yyyy-MM-dd'),
      check_out: format(addDays(new Date(), 1), 'yyyy-MM-dd'),
      adults: 2,
      children: 0,
      rooms: 1,
    };
  };

  // Save booking details to localStorage
  const saveBookingState = (details: BookingDetails) => {
    try {
      localStorage.setItem('booking_details', JSON.stringify(details));
      localStorage.setItem('current_step', 'search');
    } catch (error) {
      console.error('Error saving booking state:', error);
    }
  };

  // Load booking state from localStorage
  useEffect(() => {
    const loadBookingState = () => {
      try {
        const savedBookingDetails = localStorage.getItem('booking_details');
        const savedAvailableRooms = localStorage.getItem('available_rooms');
        const savedSelectedRooms = localStorage.getItem('selected_rooms');

        console.log('💾 SearchPage: Loading from localStorage:', {
          savedBookingDetails: savedBookingDetails ? 'exists' : 'null',
          savedAvailableRooms: savedAvailableRooms ? 'exists' : 'null',
          savedSelectedRooms: savedSelectedRooms ? 'exists' : 'null'
        });

        if (savedBookingDetails) {
          const bookingData = JSON.parse(savedBookingDetails);
          setBookingDetails(bookingData);
          console.log('📋 Restored booking details:', bookingData);
        }

        if (savedAvailableRooms) {
          const roomsData = JSON.parse(savedAvailableRooms);
          setAvailableRooms(roomsData);
          console.log('🏨 Restored available rooms:', roomsData.length, roomsData);
        }

        if (savedSelectedRooms) {
          const selectedData = JSON.parse(savedSelectedRooms);
          setSelectedRooms(selectedData);
          console.log('✅ Restored selected rooms:', selectedData);
        }

        // If we have existing data, don't auto-search
        if (savedBookingDetails && savedAvailableRooms) {
          setAutoSearchTriggered(true); // Prevent auto-search
        }
      } catch (error) {
        console.error('Error loading booking state:', error);
      }
    };

    loadBookingState();
  }, []);

  // Auto-load with default form data on fresh visits
  useEffect(() => {
    const triggerAutoSearch = async () => {
      // Check if we have existing booking data
      const existingData = localStorage.getItem('booking_details');

      if (!autoSearchTriggered && !existingData) {
        console.log('🚀 Auto-loading with default form data...');
        setAutoSearchTriggered(true);

        const defaultBookingDetails = getDefaultBookingDetails();
        console.log('📋 Using default booking details:', defaultBookingDetails);

        // Try real API first, fallback to dummy data
        try {
          await handleSearch(defaultBookingDetails);
        } catch (error) {
          console.log('❌ API search failed, using dummy data:', error);
          createDummyData();
        }
      }
    };

    triggerAutoSearch();
  }, [autoSearchTriggered]);

  const handleSearch = async (searchParams: BookingDetails) => {
    setLoading(true);
    setError(null);

    try {
      // Step 1: Get wp_id + token + language info from WordPress API
      console.log('Step 1: Getting wp_id + token + language info from WordPress...');
      const hotelData = await tenantDetection.getHotelData();

      if (!hotelData.wp_id || !hotelData.token) {
        throw new Error('Không thể lấy thông tin khách sạn');
      }

      console.log('Step 2: Sending search data to Laravel API...', {
        wp_id: hotelData.wp_id,
        searchParams,
        language: hotelData.config?.language || 'vi'
      });

      // Step 2: Send wp_id + token + search data to Laravel API
      const response = await apiService.searchRoomsWithWpData(
        hotelData.wp_id,
        hotelData.token,
        {
          check_in: searchParams.check_in,
          check_out: searchParams.check_out,
          adults: searchParams.adults,
          children: searchParams.children,
          rooms: searchParams.rooms,
        }
      );

      if (response.success) {
        console.log('Step 3: Got room list from Laravel API:', response.data);

        // Update state instead of navigating
        setBookingDetails(searchParams);
        setAvailableRooms(response.data);
        setSelectedRooms([]);

        // Save booking details and room data
        saveBookingState(searchParams);
        localStorage.setItem('available_rooms', JSON.stringify(response.data));
        localStorage.setItem('selected_rooms', JSON.stringify([]));
      } else {
        setError(response.message || 'Không thể tìm phòng. Vui lòng thử lại.');
      }
    } catch (err) {
      console.error('Search error:', err);
      setError('Có lỗi xảy ra khi tìm phòng. Vui lòng thử lại.');
    } finally {
      setLoading(false);
    }
  };

  const handleRoomSelectionChange = (roomId: string, quantity: number, promotionId?: string) => {
    console.log('🏨 SearchPage: Room selection change:', { roomId, quantity, promotionId });

    // Convert roomId to number and promotionId to number if exists
    const numericRoomId = parseInt(roomId, 10);
    const numericPromotionId = promotionId ? parseInt(promotionId, 10) : undefined;

    setSelectedRooms(prev => {
      const filtered = prev.filter(r => !(r.roomId === numericRoomId && r.promotionId === numericPromotionId));
      const newSelectedRooms = quantity > 0
        ? [...filtered, { roomId: numericRoomId, quantity, promotionId: numericPromotionId }]
        : filtered;

      console.log('🏨 SearchPage: New selected rooms:', newSelectedRooms);

      // Save to localStorage
      try {
        localStorage.setItem('selected_rooms', JSON.stringify(newSelectedRooms));
        console.log('💾 SearchPage: Saved to localStorage:', newSelectedRooms);
      } catch (error) {
        console.error('Error saving selected rooms:', error);
      }

      return newSelectedRooms;
    });
  };

  const handleRemoveRoom = (roomId: number, promotionId?: number) => {
    console.log('🗑️ SearchPage: Removing room:', { roomId, promotionId });

    setSelectedRooms(prev => {
      const newSelectedRooms = prev.filter(r => !(r.roomId === roomId && r.promotionId === promotionId));

      console.log('🗑️ SearchPage: After removal:', newSelectedRooms);

      // Save to localStorage
      try {
        localStorage.setItem('selected_rooms', JSON.stringify(newSelectedRooms));
        console.log('💾 SearchPage: Saved after removal:', newSelectedRooms);
      } catch (error) {
        console.error('Error saving after removal:', error);
      }

      return newSelectedRooms;
    });
  };

  const handleProceedToGuest = () => {
    if (selectedRooms.length === 0) {
      alert('Vui lòng chọn ít nhất một phòng trước khi tiếp tục.');
      return;
    }

    // Navigate to guest page
    navigate('/guest');
  };

  return (
    <div>
      {/* Search Form */}
      <div className="formSearch">
        <div className="mb-6">
          <BookingSearch onSearch={handleSearch} loading={loading} />
        </div>
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          {/* Error Message */}
          {error && (
            <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
              <p className="text-red-600">{error}</p>
            </div>
          )}

          {/* Debug Controls */}
          {/* <div className="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 className="text-sm font-medium text-yellow-800 mb-2">Debug Controls</h3>
            <div className="flex gap-2">
              <button
                onClick={createDummyData}
                className="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
              >
                Create Dummy Data
              </button>
              <button
                onClick={() => {
                  console.log('🧪 Current state:', { bookingDetails, availableRooms, selectedRooms });
                  console.log('💾 localStorage:', {
                    booking_details: localStorage.getItem('booking_details'),
                    available_rooms: localStorage.getItem('available_rooms'),
                    selected_rooms: localStorage.getItem('selected_rooms')
                  });
                }}
                className="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700"
              >
                Log Current State
              </button>
              <button
                onClick={() => {
                  if (availableRooms.length > 0) {
                    const testRoom = availableRooms[0];
                    const testRoomId = testRoom.room_id || testRoom.room?.id || (testRoom as any).id;
                    console.log('🧪 Testing room selection with room:', testRoomId);
                    handleRoomSelectionChange(testRoomId, 1, undefined);
                  }
                }}
                className="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700"
              >
                Test Select Room
              </button>
            </div>
          </div> */}

          {/* Room List */}
          {bookingDetails && availableRooms.length > 0 && (
            <>
              <RoomList
                rooms={availableRooms}
                selectedRooms={selectedRooms}
                onRoomSelectionChange={handleRoomSelectionChange}
                loading={loading}
              />
            </>
          )}
        </div>

        {/* Booking Summary */}
        <div className="lg:col-span-1">
          {bookingDetails && (
            <BookingSummary
              bookingDetails={bookingDetails}
              selectedRooms={selectedRooms}
              availableRooms={availableRooms}
              onProceedToBooking={handleProceedToGuest}
              onRemoveRoom={handleRemoveRoom}
              loading={loading}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchPage;