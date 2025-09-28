import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import GuestForm from '../components/GuestForm';
import BookingSummary from '../components/BookingSummary';
import type { BookingDetails, Guest, RoomAvailability } from '../types/api';
import { apiService } from '../services/api';
import { tenantDetection } from '../services/tenantDetection';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
}

const GuestPage: React.FC = () => {
  const navigate = useNavigate();
  const [bookingDetails, setBookingDetails] = useState<BookingDetails | null>(null);
  const [selectedRooms, setSelectedRooms] = useState<SelectedRoom[]>([]);
  const [availableRooms, setAvailableRooms] = useState<RoomAvailability[]>([]);
  const [loading, setLoading] = useState(false);

  // Load data from localStorage and validate access
  useEffect(() => {
    const validateAccess = () => {
      try {
        // Check if we have booking details
        const savedBookingDetails = localStorage.getItem('booking_details');
        if (!savedBookingDetails) {
          navigate('/search');
          return;
        }

        // Check if we have selected rooms
        const savedSelectedRooms = localStorage.getItem('selected_rooms');
        if (!savedSelectedRooms) {
          navigate('/rooms');
          return;
        }

        const selectedData = JSON.parse(savedSelectedRooms);
        if (selectedData.length === 0) {
          navigate('/search');
          return;
        }

        // Load available rooms data
        const savedAvailableRooms = localStorage.getItem('available_rooms');
        if (!savedAvailableRooms) {
          navigate('/search');
          return;
        }

        // Load data
        const bookingData = JSON.parse(savedBookingDetails);
        const availableRoomsData = JSON.parse(savedAvailableRooms);

        setBookingDetails(bookingData);
        setSelectedRooms(selectedData);
        setAvailableRooms(availableRoomsData);
      } catch (error) {
        console.error('Error validating guest page access:', error);
        navigate('/search');
      }
    };

    validateAccess();
  }, [navigate]);

  const handleGuestSubmit = async (guestInfo: Guest) => {
    if (!bookingDetails || selectedRooms.length === 0) {
      console.error('Missing booking data for submission');
      return;
    }

    setLoading(true);

    try {
      // Get hotel data for submission
      const hotelData = await tenantDetection.getHotelData();

      if (!hotelData.wp_id || !hotelData.token) {
        throw new Error('Không thể lấy thông tin khách sạn');
      }

      // Submit booking
      const response = await apiService.submitBooking(
        hotelData.wp_id,
        {
          booking_details: bookingDetails,
          selected_rooms: selectedRooms,
          guest_info: guestInfo,
        }
      );

      if (response.success) {
        // Save booking result
        localStorage.setItem('booking_result', JSON.stringify(response.data));
        localStorage.setItem('current_step', 'confirmation');

        // Navigate to confirmation
        navigate('/confirmation');
      } else {
        throw new Error(response.message || 'Không thể hoàn tất đặt phòng');
      }
    } catch (error) {
      console.error('Booking submission error:', error);
      alert('Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại.');
    } finally {
      setLoading(false);
    }
  };

  const handleBackToRooms = () => {
    navigate('/search');
  };

  const handleRemoveRoom = (roomId: number, promotionId?: number) => {
    setSelectedRooms(prev => {
      const newSelectedRooms = prev.filter(r => !(r.roomId === roomId && r.promotionId === promotionId));
      // Save to localStorage
      try {
        localStorage.setItem('selected_rooms', JSON.stringify(newSelectedRooms));
      } catch (error) {
        console.error('Error saving after removal:', error);
      }

      // If no rooms left, redirect to search
      if (newSelectedRooms.length === 0) {
        navigate('/search');
      }

      return newSelectedRooms;
    });
  };

  const handleProceedToBooking = () => {
  };

  if (!bookingDetails) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <div className="text-6xl mb-4">👤</div>
          <h3 className="text-xl font-semibold text-gray-600 mb-2">
            Đang tải dữ liệu...
          </h3>
        </div>
      </div>
    );
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={handleBackToRooms}
          className="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          ← Quay lại chọn phòng
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Guest Form */}
        <div className="lg:col-span-2">
          <GuestForm onSubmit={handleGuestSubmit} loading={loading} />
        </div>

        {/* Booking Summary */}
        <div className="lg:col-span-1">
          {bookingDetails && (
            <BookingSummary
              bookingDetails={bookingDetails}
              selectedRooms={selectedRooms}
              availableRooms={availableRooms}
              onProceedToBooking={handleProceedToBooking}
              onRemoveRoom={handleRemoveRoom}
              loading={loading}
              showButton={false}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default GuestPage;