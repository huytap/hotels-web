import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import RoomList from '../components/RoomList';
import BookingSummary from '../components/BookingSummary';
import type { BookingDetails, RoomAvailability } from '../types/api';

interface SelectedRoom {
  roomId: string;
  quantity: number;
  promotionId?: string;
}

const RoomsPage: React.FC = () => {
  const navigate = useNavigate();
  const [bookingDetails, setBookingDetails] = useState<BookingDetails | null>(null);
  const [availableRooms, setAvailableRooms] = useState<RoomAvailability[]>([]);
  const [selectedRooms, setSelectedRooms] = useState<SelectedRoom[]>([]);
  const [loading, setLoading] = useState(false);

  // Load data from localStorage on component mount
  useEffect(() => {
    const loadData = () => {
      try {
        // Load booking details
        const savedBookingDetails = localStorage.getItem('booking_details');
        if (!savedBookingDetails) {
          console.log('❌ No booking details found, redirecting to search');
          navigate('/search');
          return;
        }

        const bookingData = JSON.parse(savedBookingDetails);
        setBookingDetails(bookingData);

        // Load available rooms
        const savedAvailableRooms = localStorage.getItem('available_rooms');
        if (!savedAvailableRooms) {
          console.log('❌ No room data found, redirecting to search');
          navigate('/search');
          return;
        }

        const roomsData = JSON.parse(savedAvailableRooms);
        setAvailableRooms(roomsData);

        // Load selected rooms if any
        const savedSelectedRooms = localStorage.getItem('selected_rooms');
        if (savedSelectedRooms) {
          const selectedData = JSON.parse(savedSelectedRooms);
          setSelectedRooms(selectedData);
        }

        console.log('✅ Rooms page data loaded successfully');
      } catch (error) {
        console.error('Error loading rooms page data:', error);
        navigate('/search');
      }
    };

    loadData();
  }, [navigate]);

  // Save selected rooms to localStorage
  const saveSelectedRooms = (rooms: SelectedRoom[]) => {
    try {
      localStorage.setItem('selected_rooms', JSON.stringify(rooms));
      localStorage.setItem('current_step', 'rooms');
    } catch (error) {
      console.error('Error saving selected rooms:', error);
    }
  };

  const handleRoomSelectionChange = (roomId: string, quantity: number, promotionId?: string, useExtraBed?: boolean) => {
    setSelectedRooms(prev => {
      const filtered = prev.filter(r => !(r.roomId === roomId && r.promotionId === promotionId));
      const newSelectedRooms = quantity > 0
        ? [...filtered, { roomId, quantity, promotionId, useExtraBed }]
        : filtered;

      // Save to localStorage
      saveSelectedRooms(newSelectedRooms);

      return newSelectedRooms;
    });
  };

  const handleProceedToGuest = () => {
    if (selectedRooms.length === 0) {
      alert('Vui lòng chọn ít nhất một phòng trước khi tiếp tục.');
      return;
    }

    // Save current state
    localStorage.setItem('current_step', 'guest');
    navigate('/guest');
  };

  const handleBackToSearch = () => {
    navigate('/search');
  };

  if (!bookingDetails) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <div className="text-6xl mb-4">🔍</div>
          <h3 className="text-xl font-semibold text-gray-600 mb-2">
            Đang tải dữ liệu...
          </h3>
        </div>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div className="lg:col-span-2">
        <div className="mb-4">
          <button
            onClick={handleBackToSearch}
            className="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            ← Thay đổi tìm kiếm
          </button>
        </div>

        <div className="mb-4">
          <h2 className="text-xl font-bold text-gray-800">Kết Quả Tìm Kiếm</h2>
          <p className="text-sm text-gray-600">
            {availableRooms.length} phòng từ {bookingDetails.check_in} đến {bookingDetails.check_out}
          </p>
        </div>

        <RoomList
          rooms={availableRooms}
          selectedRooms={selectedRooms}
          onRoomSelectionChange={handleRoomSelectionChange}
          loading={loading}
        />
      </div>

      <div className="lg:col-span-1">
        <BookingSummary
          bookingDetails={bookingDetails}
          selectedRooms={selectedRooms}
          availableRooms={availableRooms}
          onProceedToBooking={handleProceedToGuest}
          loading={loading}
        />
      </div>
    </div>
  );
};

export default RoomsPage;