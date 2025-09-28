import React from 'react';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';
import { Calendar, Users, Bed, Tag, CreditCard, X } from 'lucide-react';
import type { BookingDetails, RoomAvailability } from '../types/api';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
}

interface BookingSummaryProps {
  bookingDetails: BookingDetails;
  selectedRooms: SelectedRoom[];
  availableRooms: RoomAvailability[];
  onProceedToBooking: () => void;
  onRemoveRoom: (roomId: number, promotionId?: number) => void;
  loading?: boolean;
  buttonText?: string;
  showButton?: boolean;
}

const BookingSummary: React.FC<BookingSummaryProps> = ({
  bookingDetails,
  selectedRooms,
  availableRooms,
  onProceedToBooking,
  onRemoveRoom,
  loading = false,
  buttonText = 'Tiếp Tục Đặt Phòng',
  showButton = true
}) => {
  console.log('📋 BookingSummary render:', {
    selectedRooms,
    selectedRoomsLength: selectedRooms.length,
    availableRooms,
    availableRoomsLength: availableRooms.length,
    bookingDetails
  });
  const calculateNights = (): number => {
    try {
      const checkIn = new Date(bookingDetails.check_in);
      const checkOut = new Date(bookingDetails.check_out);

      if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
        console.log('⚠️ Invalid dates in booking details:', bookingDetails);
        return 1; // Default to 1 night
      }

      const nights = Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
      console.log('📅 Calculated nights:', {
        checkIn: bookingDetails.check_in,
        checkOut: bookingDetails.check_out,
        nights
      });

      return nights > 0 ? nights : 1; // Ensure at least 1 night
    } catch (error) {
      console.error('Error calculating nights:', error);
      return 1;
    }
  };

  const getRoomDetails = (roomId: number) => {
    console.log('🔍 Looking for room with ID:', roomId);
    console.log('🔍 Raw available rooms:', availableRooms);

    // The availableRooms might be a combination array, need to flatten it
    let allRooms: any[] = [];

    availableRooms.forEach((item: any) => {
      if (item.combination_details && Array.isArray(item.combination_details)) {
        // This is a combination object, extract rooms from combination_details
        item.combination_details.forEach((detail: any) => {
          const roomType = detail.room_type;
          if (roomType) {
            allRooms.push({
              room_id: roomType.id,
              room: {
                id: roomType.id,
                name: roomType.name,
                type: roomType.name,
                description: roomType.description || '',
                capacity: 2,
                amenities: roomType.amenities ? roomType.amenities.split(', ') : [],
                images: roomType.gallery ? (typeof roomType.gallery === 'string' ? JSON.parse(roomType.gallery) : roomType.gallery) : [],
                base_price: detail.base_price_total,
                currency: 'VND'
              },
              available_inventory: detail.available_rooms,
              rate_per_night: detail.base_price_total,
              total_price: item.total_price,
              applicable_promotions: Object.values(detail.promotions || {}).map((promo: any) => ({
                id: promo.details.id,
                code: promo.details.promotion_code,
                name: promo.details.name?.vi || promo.details.name?.en || promo.details.name || '',
                description: promo.details.description?.vi || promo.details.description || '',
                type: promo.details.value_type === 'percentage' ? 'percentage' as const : 'fixed' as const,
                value: parseFloat(promo.details.value),
                start_date: promo.details.start_date,
                end_date: promo.details.end_date,
                min_nights: promo.details.min_stay || 1,
                applicable_room_types: [roomType.id],
                is_active: !!promo.details.is_active,
                max_uses: 999,
                current_uses: 0
              }))
            });
          }
        });
      } else if (item.room_id) {
        // This is already a proper room object
        allRooms.push(item);
      }
    });

    console.log('🔍 Flattened rooms:', allRooms);

    // Now find the room
    const room = allRooms.find(r => r.room_id === roomId || r.room?.id === roomId);
    console.log('🔍 Found room:', room);

    return room;
  };

  const getPromotionDetails = (roomId: number, promotionId?: number) => {
    const roomAvailability = getRoomDetails(roomId);
    if (!roomAvailability || !promotionId) return null;
    return roomAvailability.applicable_promotions.find(p => p.id === promotionId);
  };

  const calculateRoomPrice = (roomId: number, quantity: number, promotionId?: number): number => {
    console.log('🏷️ Calculating price for room:', roomId, 'quantity:', quantity, 'promotionId:', promotionId);

    const roomDetails = getRoomDetails(roomId);
    console.log('🏷️ Room details found:', roomDetails);

    if (!roomDetails) {
      console.log('❌ No room details found for roomId:', roomId);
      return 0;
    }

    let pricePerNight = roomDetails.rate_per_night;
    console.log('🏷️ Base price per night:', pricePerNight);

    const promotion = getPromotionDetails(roomId, promotionId);
    console.log('🏷️ Promotion details:', promotion);

    if (promotion) {
      if (promotion.type === 'percentage') {
        pricePerNight = pricePerNight * (1 - promotion.value / 100);
      } else if (promotion.type === 'fixed') {
        pricePerNight = Math.max(0, pricePerNight - promotion.value);
      }
      console.log('🏷️ Price after promotion:', pricePerNight);
    }

    const nights = calculateNights();

    // Ensure all values are numbers
    const safePricePerNight = typeof pricePerNight === 'number' ? pricePerNight : 0;
    const safeQuantity = typeof quantity === 'number' ? quantity : 0;
    const safeNights = typeof nights === 'number' ? nights : 0;

    const totalPrice = safePricePerNight * safeQuantity * safeNights;
    console.log('🏷️ Final calculation:', {
      pricePerNight: safePricePerNight,
      quantity: safeQuantity,
      nights: safeNights,
      totalPrice,
      isValidNumber: !isNaN(totalPrice)
    });

    return isNaN(totalPrice) ? 0 : totalPrice;
  };

  const calculateTotalPrice = (): number => {
    console.log('💰 Starting total calculation');
    console.log('💰 Selected rooms:', selectedRooms);
    console.log('💰 Available rooms count:', availableRooms.length);

    const total = selectedRooms.reduce((total, room) => {
      const roomPrice = calculateRoomPrice(room.roomId, room.quantity, room.promotionId);
      console.log('💰 Room price calculation:', {
        roomId: room.roomId,
        quantity: room.quantity,
        promotionId: room.promotionId,
        roomPrice,
        totalSoFar: total + roomPrice
      });
      return total + roomPrice;
    }, 0);
    console.log('💰 Final total price:', total);
    return total;
  };

  const calculateOriginalPrice = (): number => {
    return selectedRooms.reduce((total, room) => {
      const roomDetails = getRoomDetails(room.roomId);
      if (!roomDetails) return total;
      return total + (roomDetails.rate_per_night * room.quantity * calculateNights());
    }, 0);
  };

  const totalPrice = calculateTotalPrice();
  const originalPrice = calculateOriginalPrice();
  const totalDiscount = originalPrice - totalPrice;
  const nights = calculateNights();

  if (selectedRooms.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-lg border p-6 sticky top-4">
        <h3 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
          <CreditCard className="w-5 h-5" />
          Tóm Tắt Đặt Phòng
        </h3>
        <div className="text-center py-8">
          <p className="text-gray-500 mb-4">Chưa có phòng nào được chọn</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-lg border p-6 sticky top-4">
      <h3 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
        <CreditCard className="w-5 h-5" />
        Tóm Tắt Đặt Phòng
      </h3>
      {/* Booking Details */}
      <div className="space-y-3 mb-6 pb-4 border-b">
        <div className="flex items-center gap-2 text-sm">
          <Calendar className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">Nhận phòng:</span>
          <span className="font-medium">
            {format(new Date(bookingDetails.check_in), 'dd/MM/yyyy', { locale: vi })}
          </span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Calendar className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">Trả phòng:</span>
          <span className="font-medium">
            {format(new Date(bookingDetails.check_out), 'dd/MM/yyyy', { locale: vi })}
          </span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Bed className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">Số đêm:</span>
          <span className="font-medium">{nights} đêm</span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Users className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">Khách:</span>
          <span className="font-medium">
            {bookingDetails.adults} người lớn
            {bookingDetails.children > 0 && `, ${bookingDetails.children} trẻ em`}
          </span>
        </div>
      </div>

      {/* Selected Rooms - Grouped by Room Type */}
      <div className="space-y-4 mb-6">
        <h4 className="font-semibold text-gray-700">Phòng Đã Chọn:</h4>
        {(() => {
          // Group selected rooms by room type
          const groupedRooms = selectedRooms.reduce((groups, selectedRoom) => {
            const roomDetails = getRoomDetails(selectedRoom.roomId);
            console.log('🏨 Grouping room:', selectedRoom.roomId, 'details:', roomDetails);

            if (!roomDetails) {
              console.log('❌ No room details for grouping:', selectedRoom.roomId);
              return groups;
            }

            const roomName = roomDetails.room?.name || `Room ${selectedRoom.roomId}`;
            if (!groups[roomName]) {
              groups[roomName] = [];
            }
            groups[roomName].push(selectedRoom);
            return groups;
          }, {} as Record<string, typeof selectedRooms>);

          return Object.entries(groupedRooms).map(([roomName, roomSelections]) => (
            <div key={roomName} className="p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
              <h5 className="font-semibold text-gray-800 mb-3">{roomName}</h5>
              <div className="space-y-2">
                {roomSelections.map((selectedRoom, index) => {
                  const roomDetails = getRoomDetails(selectedRoom.roomId);
                  const promotion = getPromotionDetails(selectedRoom.roomId, selectedRoom.promotionId);
                  const roomTotal = calculateRoomPrice(selectedRoom.roomId, selectedRoom.quantity, selectedRoom.promotionId);
                  const originalRoomTotal = roomDetails ? roomDetails.rate_per_night * selectedRoom.quantity * nights : 0;

                  return (
                    <div key={index} className="flex justify-between items-center py-2 px-3 bg-white rounded border">
                      <div className="flex-1">
                        <div className="flex items-center gap-2">
                          {promotion ? (
                            <>
                              <Tag className="w-4 h-4 text-green-600" />
                              <span className="text-sm font-medium text-green-700">{promotion.name}</span>
                            </>
                          ) : (
                            <span className="text-sm text-gray-600">Giá cơ bản</span>
                          )}
                        </div>
                        <p className="text-xs text-gray-500">
                          {selectedRoom.quantity} phòng × {nights} đêm
                        </p>
                      </div>
                      <div className="flex items-center gap-3">
                        <div className="text-right">
                          {promotion && (
                            <div className="text-xs text-gray-500 line-through">
                              {originalRoomTotal.toLocaleString('vi-VN')} VND
                            </div>
                          )}
                          <div className="font-medium text-blue-600">
                            {roomTotal.toLocaleString('vi-VN')} VND
                          </div>
                          {promotion && (
                            <div className="text-xs text-green-600">
                              Tiết kiệm {(originalRoomTotal - roomTotal).toLocaleString('vi-VN')} VND
                            </div>
                          )}
                        </div>
                        <button
                          onClick={() => onRemoveRoom(selectedRoom.roomId, selectedRoom.promotionId)}
                          className="flex items-center justify-center w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-full transition-colors"
                          title="Xóa phòng này"
                        >
                          <X className="w-3 h-3" />
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          ));
        })()}
      </div>

      {/* Price Summary */}
      <div className="space-y-2 mb-6 pb-4 border-b">
        {totalDiscount > 0 && (
          <>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Tổng giá gốc:</span>
              <span className="line-through text-gray-500">
                {originalPrice.toLocaleString('vi-VN')} VND
              </span>
            </div>
            <div className="flex justify-between text-sm text-green-600">
              <span>Tiết kiệm:</span>
              <span className="font-medium">
                -{totalDiscount.toLocaleString('vi-VN')} VND
              </span>
            </div>
          </>
        )}
        <div className="flex justify-between text-lg font-bold text-blue-600">
          <span>Tổng tiền:</span>
          <span>{totalPrice.toLocaleString('vi-VN')} VND</span>
        </div>
      </div>

      {/* Action Button */}
      {showButton && (
        <button
          onClick={onProceedToBooking}
          disabled={loading || selectedRooms.length === 0}
          className="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
        >
          {loading ? (
            <div className="flex items-center justify-center gap-2">
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              Đang Xử Lý...
            </div>
          ) : (
            buttonText
          )}
        </button>
      )}

      <p className="text-xs text-gray-500 mt-3 text-center">
        * Giá đã bao gồm thuế và phí dịch vụ
      </p>
    </div>
  );
};

export default BookingSummary;