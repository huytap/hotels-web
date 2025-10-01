import React, { useState } from 'react';
import { useLocalizedText } from '../context/LanguageContext';
import { useHotel } from '../context/HotelContext';
import { apiService } from '../services/api';
import type { Booking } from '../types/api';

const TrackingPage: React.FC = () => {
  const { t } = useLocalizedText();
  const { hotel } = useHotel();
  const [searchType, setSearchType] = useState<'booking_number' | 'email'>('booking_number');
  const [searchValue, setSearchValue] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [booking, setBooking] = useState<Booking | null>(null);
  const [showCancelConfirm, setShowCancelConfirm] = useState(false);
  const [cancelReason, setCancelReason] = useState('');

  const handleSearch = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!searchValue.trim()) {
      setError(t('please_enter_search', 'Please enter booking number or email'));
      return;
    }

    setLoading(true);
    setError(null);
    setBooking(null);

    try {
      if (!hotel?.wpSiteId) {
        setError(t('hotel_not_configured', 'Hotel configuration not found'));
        return;
      }

      const response = await apiService.trackBooking(hotel.wpSiteId, searchType, searchValue);

      if (response.success && response.data) {
        setBooking(response.data);
      } else {
        setError(t('booking_not_found', 'Booking not found. Please check your booking number or email.'));
      }
    } catch (err: any) {
      console.error('Track booking error:', err);
      setError(err.message || t('error_occurred', 'An error occurred. Please try again.'));
    } finally {
      setLoading(false);
    }
  };

  const handleCancelBooking = async () => {
    if (!booking || !cancelReason.trim()) {
      setError(t('cancel_reason_required', 'Please provide a reason for cancellation'));
      return;
    }

    setLoading(true);
    setError(null);

    try {
      if (!hotel?.wpSiteId) {
        setError(t('hotel_not_configured', 'Hotel configuration not found'));
        return;
      }

      await apiService.cancelBooking(hotel.wpSiteId, booking.id, cancelReason);

      // Refresh booking data
      setBooking({ ...booking, status: 'cancelled' });
      setShowCancelConfirm(false);
      setCancelReason('');

      alert(t('booking_cancelled', 'Booking has been cancelled successfully'));
    } catch (err: any) {
      console.error('Cancel booking error:', err);
      setError(err.message || t('cancel_error', 'Failed to cancel booking. Please contact support.'));
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadge = (status: string) => {
    const badges: Record<string, { bg: string; text: string; label: string }> = {
      pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: t('status_pending', 'Pending') },
      confirmed: { bg: 'bg-green-100', text: 'text-green-800', label: t('status_confirmed', 'Confirmed') },
      cancelled: { bg: 'bg-red-100', text: 'text-red-800', label: t('status_cancelled', 'Cancelled') },
      completed: { bg: 'bg-blue-100', text: 'text-blue-800', label: t('status_completed', 'Completed') },
    };

    const badge = badges[status] || badges.pending;

    return (
      <span className={`px-3 py-1 rounded-full text-sm font-medium ${badge.bg} ${badge.text}`}>
        {badge.label}
      </span>
    );
  };

  const canCancelBooking = (booking: Booking) => {
    return booking.status === 'pending' || booking.status === 'confirmed';
  };

  return (
    <div className="max-w-4xl mx-auto py-8 px-4">
      {/* Header */}
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          {t('track_booking', 'Track Your Booking')}
        </h1>
        <p className="text-gray-600">
          {t('track_description', 'Enter your booking number or email to view your reservation details')}
        </p>
      </div>

      {/* Search Form */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <form onSubmit={handleSearch}>
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('search_by', 'Search By')}
            </label>
            <div className="flex gap-4 mb-4">
              <label className="flex items-center cursor-pointer">
                <input
                  type="radio"
                  value="booking_number"
                  checked={searchType === 'booking_number'}
                  onChange={(e) => setSearchType(e.target.value as 'booking_number')}
                  className="w-4 h-4 text-blue-600"
                />
                <span className="ml-2 text-gray-700">
                  {t('booking_number', 'Booking Number')}
                </span>
              </label>
              <label className="flex items-center cursor-pointer">
                <input
                  type="radio"
                  value="email"
                  checked={searchType === 'email'}
                  onChange={(e) => setSearchType(e.target.value as 'email')}
                  className="w-4 h-4 text-blue-600"
                />
                <span className="ml-2 text-gray-700">
                  {t('email', 'Email Address')}
                </span>
              </label>
            </div>
          </div>

          <div className="mb-4">
            <input
              type={searchType === 'email' ? 'email' : 'text'}
              value={searchValue}
              onChange={(e) => setSearchValue(e.target.value)}
              placeholder={
                searchType === 'booking_number'
                  ? t('enter_booking_number', 'e.g., BK20251001123456')
                  : t('enter_email', 'e.g., example@email.com')
              }
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
          </div>

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
          >
            {loading ? t('searching', 'Searching...') : t('search', 'Search')}
          </button>
        </form>
      </div>

      {/* Booking Details */}
      {booking && (
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          {/* Header */}
          <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
            <div className="flex justify-between items-start">
              <div>
                <h2 className="text-2xl font-bold mb-2">
                  {t('booking', 'Booking')} #{booking.booking_number}
                </h2>
                <p className="text-blue-100">
                  {t('booked_on', 'Booked on')} {new Date(booking.created_at).toLocaleDateString('vi-VN')}
                </p>
              </div>
              <div>
                {getStatusBadge(booking.status)}
              </div>
            </div>
          </div>

          {/* Details */}
          <div className="p-6 space-y-6">
            {/* Guest Information */}
            <div>
              <h3 className="text-lg font-semibold text-gray-900 mb-3">
                {t('guest_info', 'Guest Information')}
              </h3>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">{t('name', 'Name')}:</span>
                  <p className="font-medium text-gray-900">
                    {booking.first_name} {booking.last_name}
                  </p>
                </div>
                <div>
                  <span className="text-gray-600">{t('email', 'Email')}:</span>
                  <p className="font-medium text-gray-900">{booking.email}</p>
                </div>
                <div>
                  <span className="text-gray-600">{t('phone', 'Phone')}:</span>
                  <p className="font-medium text-gray-900">{booking.phone_number}</p>
                </div>
                {booking.nationality && (
                  <div>
                    <span className="text-gray-600">{t('nationality', 'Nationality')}:</span>
                    <p className="font-medium text-gray-900">{booking.nationality}</p>
                  </div>
                )}
              </div>
            </div>

            {/* Booking Information */}
            <div className="border-t pt-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-3">
                {t('booking_info', 'Booking Information')}
              </h3>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">{t('check_in', 'Check-in')}:</span>
                  <p className="font-medium text-gray-900">
                    {new Date(booking.check_in).toLocaleDateString('vi-VN')}
                  </p>
                </div>
                <div>
                  <span className="text-gray-600">{t('check_out', 'Check-out')}:</span>
                  <p className="font-medium text-gray-900">
                    {new Date(booking.check_out).toLocaleDateString('vi-VN')}
                  </p>
                </div>
                <div>
                  <span className="text-gray-600">{t('nights', 'Nights')}:</span>
                  <p className="font-medium text-gray-900">{booking.nights}</p>
                </div>
                <div>
                  <span className="text-gray-600">{t('guests', 'Guests')}:</span>
                  <p className="font-medium text-gray-900">{booking.guests}</p>
                </div>
              </div>
            </div>

            {/* Room Details */}
            {booking.booking_details && booking.booking_details.length > 0 && (
              <div className="border-t pt-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-3">
                  {t('room_details', 'Room Details')}
                </h3>
                <div className="space-y-3">
                  {booking.booking_details.map((detail, index) => (
                    <div key={index} className="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                      <div>
                        <p className="font-medium text-gray-900">
                          {detail.roomtype?.name || 'Room'}
                        </p>
                        <p className="text-sm text-gray-600">
                          {detail.quantity} {detail.quantity > 1 ? t('rooms', 'rooms') : t('room', 'room')} × {detail.adults} {t('adults', 'adults')}
                          {detail.children > 0 && `, ${detail.children} ${t('children', 'children')}`}
                        </p>
                      </div>
                      <div className="text-right">
                        <p className="font-semibold text-gray-900">
                          {new Intl.NumberFormat('vi-VN').format(detail.sub_total)} VNĐ
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Payment Summary */}
            <div className="border-t pt-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-3">
                {t('payment_summary', 'Payment Summary')}
              </h3>
              <div className="space-y-2 text-sm">
                {booking.discount_amount > 0 && (
                  <div className="flex justify-between text-gray-600">
                    <span>{t('discount', 'Discount')}:</span>
                    <span className="text-green-600 font-medium">
                      -{new Intl.NumberFormat('vi-VN').format(booking.discount_amount)} VNĐ
                    </span>
                  </div>
                )}
                {booking.tax_amount > 0 && (
                  <div className="flex justify-between text-gray-600">
                    <span>{t('tax', 'Tax')}:</span>
                    <span>{new Intl.NumberFormat('vi-VN').format(booking.tax_amount)} VNĐ</span>
                  </div>
                )}
                <div className="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                  <span>{t('total', 'Total')}:</span>
                  <span>{new Intl.NumberFormat('vi-VN').format(booking.total_amount)} VNĐ</span>
                </div>
              </div>
            </div>

            {/* Notes */}
            {booking.notes && (
              <div className="border-t pt-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                  {t('notes', 'Notes')}
                </h3>
                <p className="text-gray-700 text-sm whitespace-pre-line">{booking.notes}</p>
              </div>
            )}

            {/* Actions */}
            {canCancelBooking(booking) && !showCancelConfirm && (
              <div className="border-t pt-6">
                <button
                  onClick={() => setShowCancelConfirm(true)}
                  className="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition-colors font-medium"
                >
                  {t('cancel_booking', 'Cancel This Booking')}
                </button>
              </div>
            )}

            {/* Cancel Confirmation */}
            {showCancelConfirm && (
              <div className="border-t pt-6">
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                  <h4 className="font-semibold text-red-900 mb-2">
                    {t('confirm_cancel', 'Confirm Cancellation')}
                  </h4>
                  <p className="text-sm text-red-700 mb-3">
                    {t('cancel_warning', 'Are you sure you want to cancel this booking? This action cannot be undone.')}
                  </p>
                  <textarea
                    value={cancelReason}
                    onChange={(e) => setCancelReason(e.target.value)}
                    placeholder={t('cancel_reason', 'Please provide a reason for cancellation...')}
                    rows={3}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
                    required
                  />
                  <div className="flex gap-3">
                    <button
                      onClick={handleCancelBooking}
                      disabled={loading || !cancelReason.trim()}
                      className="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors font-medium"
                    >
                      {loading ? t('cancelling', 'Cancelling...') : t('yes_cancel', 'Yes, Cancel Booking')}
                    </button>
                    <button
                      onClick={() => {
                        setShowCancelConfirm(false);
                        setCancelReason('');
                      }}
                      className="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                      {t('no_keep', 'No, Keep Booking')}
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default TrackingPage;
