import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const ConfirmationPage: React.FC = () => {
  const navigate = useNavigate();
  const [bookingResult, setBookingResult] = useState<any>(null);

  // Load booking result and validate access
  useEffect(() => {
    const validateAccess = () => {
      try {
        // Check if we have booking result
        const savedBookingResult = localStorage.getItem('booking_result');
        if (!savedBookingResult) {
          console.log('❌ No booking result found, redirecting to search');
          navigate('/search');
          return;
        }

        const resultData = JSON.parse(savedBookingResult);
        setBookingResult(resultData);

        console.log('✅ Confirmation page access validated successfully');
      } catch (error) {
        console.error('Error validating confirmation page access:', error);
        navigate('/search');
      }
    };

    validateAccess();
  }, [navigate]);

  const handleNewBooking = () => {
    // Clear all booking data
    localStorage.removeItem('booking_details');
    localStorage.removeItem('available_rooms');
    localStorage.removeItem('selected_rooms');
    localStorage.removeItem('booking_result');
    localStorage.removeItem('current_step');

    // Navigate to search
    navigate('/search');
  };

  if (!bookingResult) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <div className="text-6xl mb-4">✅</div>
          <h3 className="text-xl font-semibold text-gray-600 mb-2">
            Đang tải thông tin xác nhận...
          </h3>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto">
      <div className="bg-white rounded-lg shadow-lg border p-6 text-center">
        <div className="text-6xl mb-4">🎉</div>
        <h2 className="text-2xl font-bold text-green-600 mb-4">
          Đặt Phòng Thành Công!
        </h2>
        <div className="text-lg text-gray-700 mb-4">
          <p className="mb-2">
            <strong>Mã đặt phòng:</strong> {bookingResult.booking_id || bookingResult.id || 'N/A'}
          </p>
          <p className="mb-2">
            <strong>Tổng tiền:</strong> {bookingResult.total_amount?.toLocaleString('vi-VN') || 'N/A'} VND
          </p>
          {bookingResult.guest_name && (
            <p className="mb-2">
              <strong>Khách hàng:</strong> {bookingResult.guest_name}
            </p>
          )}
          {bookingResult.check_in && bookingResult.check_out && (
            <p className="mb-2">
              <strong>Thời gian:</strong> {bookingResult.check_in} - {bookingResult.check_out}
            </p>
          )}
        </div>
        <p className="text-sm text-gray-600 mt-2">
          Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn.
        </p>
      </div>

      <div className="mt-6 text-center">
        <button
          onClick={handleNewBooking}
          className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200"
        >
          Đặt Phòng Mới
        </button>
      </div>
    </div>
  );
};

export default ConfirmationPage;