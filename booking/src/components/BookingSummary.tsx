import React, { useState, useEffect } from 'react';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';
import { Calendar, Users, Bed, Tag, CreditCard, X } from 'lucide-react';
import type { BookingDetails, RoomAvailability, TaxSettings } from '../types/api';
import { useLocalizedText } from '../context/LanguageContext';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
  useExtraBed?: boolean;
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
  buttonText,
  showButton = true
}) => {
  const { t } = useLocalizedText();
  const [taxSettings, setTaxSettings] = useState<TaxSettings | null>(null);

  // Load tax settings from localStorage
  useEffect(() => {
    try {
      const savedTaxSettings = localStorage.getItem('hotel_tax_settings');
      if (savedTaxSettings) {
        setTaxSettings(JSON.parse(savedTaxSettings));
      }
    } catch (error) {
      console.error('Error loading tax settings:', error);
    }
  }, []);
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
    try {
      console.log('🔍 Looking for room with ID:', roomId);
      console.log('🔍 Raw available rooms:', availableRooms);

      // The availableRooms might be a combination array, need to flatten it
      let allRooms: any[] = [];

      availableRooms.forEach((item: any, itemIndex: number) => {
        if (item.combination_details && Array.isArray(item.combination_details)) {
          // This is a combination object, extract rooms from combination_details
          item.combination_details.forEach((detail: any, detailIndex: number) => {
            const roomType = detail.room_type;
            if (roomType) {
              console.log(`🔍 Adding room from combination ${itemIndex}, detail ${detailIndex}:`, roomType.id, roomType.name);

              // Check if this room already exists in allRooms to avoid duplicates
              const existingRoom = allRooms.find(r => r.room_id === roomType.id);
              if (existingRoom) {
                console.log(`⚠️ Room ${roomType.id} already exists, skipping duplicate`);
                return;
              }

              allRooms.push({
                room_id: roomType.id,
                room: {
                  id: roomType.id,
                  name: roomType.name,
                  type: roomType.name,
                  description: roomType.description || '',
                  capacity: roomType.adult_capacity || 2,
                  amenities: roomType.amenities ? roomType.amenities.split(', ') : [],
                  images: roomType.gallery ? (typeof roomType.gallery === 'string' ? JSON.parse(roomType.gallery) : roomType.gallery) : [],
                  base_price: detail.base_price_total,
                  currency: 'VND'
                },
                available_inventory: detail.available_rooms,
                rate_per_night: detail.pricing_breakdown ? (detail.pricing_breakdown.final_total / detail.quantity) : (detail.base_price_total / detail.quantity),
                total_price: detail.pricing_breakdown ? (detail.pricing_breakdown.final_total / detail.quantity) : (detail.base_price_total / detail.quantity),
                pricing_breakdown: detail.pricing_breakdown ? {
                  ...detail.pricing_breakdown,
                  // Normalize pricing breakdown to per-room values
                  base_total: detail.pricing_breakdown.base_total / detail.quantity,
                  adult_surcharge_total: detail.pricing_breakdown.adult_surcharge_total / detail.quantity,
                  children_surcharge_total: detail.pricing_breakdown.children_surcharge_total / detail.quantity,
                  promotion_applicable_amount: detail.pricing_breakdown.promotion_applicable_amount / detail.quantity,
                  non_promotion_amount: detail.pricing_breakdown.non_promotion_amount / detail.quantity,
                  final_total: detail.pricing_breakdown.final_total / detail.quantity,
                  // Also normalize the new extra bed adult surcharge field
                  extra_bed_adult_surcharge_total: (detail.pricing_breakdown.extra_bed_adult_surcharge_total || 0) / detail.quantity,
                } : undefined, // Normalize pricing breakdown to per-room
                effective_capacity: detail.effective_capacity, // Thêm effective capacity từ API
                applicable_promotions: Object.values(detail.promotions || {}).map((promo: any) => {
                  // Handle both promotion structures: with .details and without
                  const promotionData = promo.details || promo;

                  // Safety check to ensure promotionData exists and has required fields
                  if (!promotionData || !promotionData.id) {
                    console.warn('Invalid promotion data:', promo);
                    return null;
                  }

                  return {
                    id: promotionData.id,
                    code: promotionData.promotion_code || '',
                    name: promotionData.name?.vi || promotionData.name?.en || promotionData.name || '',
                    description: promotionData.description?.vi || promotionData.description || '',
                    type: promotionData.value_type === 'percentage' ? 'percentage' as const : 'fixed' as const,
                    value: parseFloat(promotionData.value || '0'),
                    start_date: promotionData.start_date || '',
                    end_date: promotionData.end_date || '',
                    min_nights: promotionData.min_stay || 1,
                    applicable_room_types: [roomType.id],
                    is_active: !!promotionData.is_active,
                    max_uses: 999,
                    current_uses: 0
                  };
                }).filter(Boolean) // Remove null values
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
      const room = allRooms.find(r =>
        (r.room_id && r.room_id === roomId) ||
        (r.room && r.room.id === roomId)
      );
      console.log('🔍 Found room:', room);

      return room;
    } catch (error) {
      console.error('Error in getRoomDetails:', error);
      return null;
    }
  };

  const getPromotionDetails = (roomId: number, promotionId?: number) => {
    try {
      const roomAvailability = getRoomDetails(roomId);
      if (!roomAvailability || !promotionId) return null;
      return roomAvailability.applicable_promotions?.find((p: any) => p.id === promotionId) || null;
    } catch (error) {
      console.error('Error in getPromotionDetails:', error);
      return null;
    }
  };

  const calculateRoomPrice = (roomId: number, quantity: number, promotionId?: number, useExtraBed?: boolean): number => {
    console.log('🏷️ Calculating price for room:', roomId, 'quantity:', quantity, 'promotionId:', promotionId);

    const roomDetails = getRoomDetails(roomId);
    console.log('🏷️ Room details found:', roomDetails);

    if (!roomDetails) {
      console.log('❌ No room details found for roomId:', roomId);
      return 0;
    }

    // Sử dụng pricing breakdown nếu có, nếu không thì fallback về logic cũ
    if (roomDetails.pricing_breakdown) {
      console.log('🏷️ Using pricing breakdown:', roomDetails.pricing_breakdown);

      const promotion = getPromotionDetails(roomId, promotionId);
      let finalPrice = 0;

      // Calculate extra charges based on user's extra bed choice
      let extraCharges = 0;
      if (useExtraBed) {
        // User chose extra bed - include all non-promotion amounts
        extraCharges = roomDetails.pricing_breakdown.non_promotion_amount;
      } else {
        // User declined extra bed - only include children surcharge (if any)
        extraCharges = roomDetails.pricing_breakdown.children_surcharge_total || 0;
      }

      if (promotion) {
        // Với promotion: áp dụng giảm giá cho promotion_applicable_amount, cộng extraCharges
        const discountAmount = promotion.type === 'percentage'
          ? roomDetails.pricing_breakdown.promotion_applicable_amount * (promotion.value / 100)
          : promotion.value;

        finalPrice = (roomDetails.pricing_breakdown.promotion_applicable_amount - discountAmount) + extraCharges;
      } else {
        // Không có promotion: lấy base price + extraCharges
        finalPrice = roomDetails.pricing_breakdown.promotion_applicable_amount + extraCharges;
      }

      // In mixed combinations, finalPrice is already for 1 room for the entire stay
      // We should only multiply by quantity if user selects more than the API quantity
      const totalPrice = finalPrice * quantity;
      console.log('🏷️ Pricing breakdown calculation:', {
        roomId,
        promotionApplicableAmount: roomDetails.pricing_breakdown.promotion_applicable_amount,
        nonPromotionAmount: roomDetails.pricing_breakdown.non_promotion_amount,
        promotion,
        finalPrice,
        userQuantity: quantity,
        totalPrice,
        'pricing_breakdown.final_total': roomDetails.pricing_breakdown.final_total
      });

      return isNaN(totalPrice) ? 0 : totalPrice;
    }

    // Fallback to old logic if no pricing breakdown
    let pricePerNight = roomDetails.rate_per_night;
    console.log('🏷️ Base price per night (fallback):', pricePerNight);

    const promotion = getPromotionDetails(roomId, promotionId);
    if (promotion) {
      if (promotion.type === 'percentage') {
        pricePerNight = pricePerNight * (1 - promotion.value / 100);
      } else if (promotion.type === 'fixed') {
        pricePerNight = Math.max(0, pricePerNight - promotion.value);
      }
    }

    const nights = calculateNights();
    const totalPrice = pricePerNight * quantity * nights;

    return isNaN(totalPrice) ? 0 : totalPrice;
  };

  const calculateTotalPrice = (): number => {
    console.log('💰 Starting total calculation');
    console.log('💰 Selected rooms:', selectedRooms);
    console.log('💰 Available rooms count:', availableRooms.length);

    const total = selectedRooms.reduce((total, room) => {
      const roomPrice = calculateRoomPrice(room.roomId, room.quantity, room.promotionId, room.useExtraBed);
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

      // Sử dụng pricing breakdown nếu có để tính giá gốc (trước khuyến mãi)
      if (roomDetails.pricing_breakdown) {
        const originalPrice = roomDetails.pricing_breakdown.promotion_applicable_amount + roomDetails.pricing_breakdown.non_promotion_amount;
        return total + (originalPrice * room.quantity);
      }

      // Fallback về logic cũ
      return total + (roomDetails.rate_per_night * room.quantity * calculateNights());
    }, 0);
  };

  const totalPrice = calculateTotalPrice();
  const originalPrice = calculateOriginalPrice();
  const totalDiscount = originalPrice - totalPrice;
  const nights = calculateNights();

  // Calculate tax and service charge
  const calculateTaxAndFees = () => {
    if (!taxSettings) {
      return { vatAmount: 0, serviceChargeAmount: 0, grandTotal: totalPrice };
    }

    if (taxSettings.prices_include_tax) {
      // Giá đã bao gồm thuế - không tính thêm
      return { vatAmount: 0, serviceChargeAmount: 0, grandTotal: totalPrice };
    }

    // Giá chưa bao gồm thuế - tính thêm VAT và Service Charge
    const vatAmount = Math.round(totalPrice * taxSettings.vat_rate / 100);
    const subTotal = totalPrice + vatAmount;
    const serviceChargeAmount = Math.round(subTotal * taxSettings.service_charge_rate / 100);
    const grandTotal = subTotal + serviceChargeAmount;

    return { vatAmount, serviceChargeAmount, grandTotal };
  };

  const { vatAmount, serviceChargeAmount, grandTotal } = calculateTaxAndFees();

  // Calculate total capacity and validate
  const calculateTotalCapacity = (): { current: number; required: number; sufficient: boolean } => {
    var required = bookingDetails.adults;
    if (bookingDetails.children_ages?.length) {
      bookingDetails.children_ages.forEach(children_age => {
        if (children_age > 11) {
          required += 1;
        }
      })
    }
    let current = 0;
    selectedRooms.forEach(selectedRoom => {
      const roomDetails = getRoomDetails(selectedRoom.roomId);
      if (roomDetails && roomDetails.room) {
        // Determine capacity based on user's extra bed choice
        let roomCapacity: number;

        if (selectedRoom.useExtraBed && roomDetails.effective_capacity) {
          // User selected extra bed - use effective_capacity (includes extra bed)
          roomCapacity = roomDetails.effective_capacity;
        } else {
          // User did not select extra bed - use base capacity only
          roomCapacity = roomDetails.room.capacity || 2;
        }

        current += roomCapacity * selectedRoom.quantity;
      }
    });

    console.log('🏨 Capacity calculation:', {
      required,
      current,
      selectedRooms,
      sufficient: current >= required,
      roomDetails: selectedRooms.map(room => {
        const details = getRoomDetails(room.roomId);
        const capacity = room.useExtraBed && details?.effective_capacity
          ? details.effective_capacity
          : (details?.room?.capacity || 2);
        return {
          roomId: room.roomId,
          quantity: room.quantity,
          useExtraBed: room.useExtraBed,
          effective_capacity: details?.effective_capacity,
          base_capacity: details?.room?.capacity,
          actual_capacity_used: capacity,
          contribution: capacity * room.quantity
        };
      })
    });

    return {
      current,
      required,
      sufficient: current >= required
    };
  };

  const capacityInfo = calculateTotalCapacity();

  if (selectedRooms.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-lg border p-6 sticky top-4">
        <h3 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
          <CreditCard className="w-5 h-5" />
          {t('summary.title')}
        </h3>
        <div className="text-center py-8">
          <p className="text-gray-500 mb-4">{t('summary.no_rooms_selected')}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-lg border p-6 sticky top-4">
      <h3 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
        <CreditCard className="w-5 h-5" />
        {t('summary.title')}
      </h3>
      {/* Booking Details */}
      <div className="space-y-3 mb-6 pb-4 border-b">
        <div className="flex items-center gap-2 text-sm">
          <Calendar className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">{t('summary.checkin')}:</span>
          <span className="font-medium">
            {format(new Date(bookingDetails.check_in), 'dd/MM/yyyy', { locale: vi })}
          </span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Calendar className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">{t('summary.checkout')}:</span>
          <span className="font-medium">
            {format(new Date(bookingDetails.check_out), 'dd/MM/yyyy', { locale: vi })}
          </span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Bed className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">{t('summary.nights')}:</span>
          <span className="font-medium">{nights} {nights === 1 ? t('summary.night') : t('summary.nights')}</span>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <Users className="w-4 h-4 text-gray-500" />
          <span className="text-gray-600">{t('summary.guests')}:</span>
          <span className="font-medium">
            {t('summary.adults_count', { count: bookingDetails.adults })}
            {bookingDetails.children > 0 && `, ${t('summary.children_count', { count: bookingDetails.children })}`}
          </span>
        </div>
      </div>

      {/* Selected Rooms - Grouped by Room Type */}
      <div className="space-y-4 mb-6">
        <h4 className="font-semibold text-gray-700">{t('summary.selected_rooms')}:</h4>
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
                  const roomTotal = calculateRoomPrice(selectedRoom.roomId, selectedRoom.quantity, selectedRoom.promotionId, selectedRoom.useExtraBed);
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
                            <span className="text-sm text-gray-600">{t('summary.basic_price')}</span>
                          )}
                        </div>
                        <p className="text-xs text-gray-500">
                          {t('summary.rooms_nights', { rooms: selectedRoom.quantity, nights })}
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
                              {t('summary.savings')} {(originalRoomTotal - roomTotal).toLocaleString('vi-VN')} VND
                            </div>
                          )}
                        </div>
                        <button
                          onClick={() => onRemoveRoom(selectedRoom.roomId, selectedRoom.promotionId)}
                          className="flex items-center justify-center w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-full transition-colors"
                          title={t('summary.remove_room')}
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
              <span className="text-gray-600">{t('summary.original_price')}:</span>
              <span className="line-through text-gray-500">
                {originalPrice.toLocaleString('vi-VN')} VND
              </span>
            </div>
            <div className="flex justify-between text-sm text-green-600">
              <span>{t('summary.savings')}:</span>
              <span className="font-medium">
                -{totalDiscount.toLocaleString('vi-VN')} VND
              </span>
            </div>
          </>
        )}

        <div className="flex justify-between text-base">
          <span className="text-gray-700">
            {t('subtotal')}
            {taxSettings && !taxSettings.prices_include_tax && (
              <span className="text-xs text-gray-500"> ({t('before_tax', 'Before tax')})</span>
            )}:
          </span>
          <span className="font-semibold">{totalPrice.toLocaleString('vi-VN')} VND</span>
        </div>

        {/* Show VAT and Service Charge if prices don't include tax */}
        {taxSettings && !taxSettings.prices_include_tax && (vatAmount > 0 || serviceChargeAmount > 0) && (
          <>
            {vatAmount > 0 && (
              <div className="flex justify-between text-sm text-gray-600">
                <span>VAT ({taxSettings.vat_rate}%):</span>
                <span>{vatAmount.toLocaleString('vi-VN')} VND</span>
              </div>
            )}
            {serviceChargeAmount > 0 && (
              <div className="flex justify-between text-sm text-gray-600">
                <span>{t('service_charge', 'Service Charge')} ({taxSettings.service_charge_rate}%):</span>
                <span>{serviceChargeAmount.toLocaleString('vi-VN')} VND</span>
              </div>
            )}
          </>
        )}

        <div className="flex justify-between text-lg font-bold text-blue-600 pt-2 border-t">
          <span>{t('summary.total_amount')}:</span>
          <span>{grandTotal.toLocaleString('vi-VN')} VND</span>
        </div>

        {taxSettings && taxSettings.prices_include_tax && (
          <p className="text-xs text-gray-500 italic mt-1">
            {t('prices_include_tax_note', '* Prices include VAT and service charge')}
          </p>
        )}
      </div>

      {/* Capacity Status */}
      <div className={`p-3 rounded-lg mb-4 ${capacityInfo.sufficient ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
        {capacityInfo.sufficient ? (
          <div className="flex items-center gap-2 text-green-700">
            <div className="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center">
              <div className="w-2 h-2 bg-white rounded-full"></div>
            </div>
            <span className="text-sm font-medium">
              {t('summary.capacity_sufficient', { guests: capacityInfo.required })}
            </span>
          </div>
        ) : (
          <div className="flex items-center gap-2 text-red-700">
            <div className="w-4 h-4 rounded-full bg-red-500 flex items-center justify-center">
              <div className="w-1 h-1 bg-white rounded-full"></div>
            </div>
            <span className="text-sm font-medium">
              {t('summary.capacity_warning', { current: capacityInfo.current, required: capacityInfo.required })}
            </span>
          </div>
        )}
      </div>

      {/* Action Button */}
      {showButton && (
        <button
          onClick={onProceedToBooking}
          disabled={loading || selectedRooms.length === 0 || !capacityInfo.sufficient}
          className="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
        >
          {loading ? (
            <div className="flex items-center justify-center gap-2">
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {t('summary.processing')}
            </div>
          ) : (
            buttonText || t('summary.proceed_booking')
          )}
        </button>
      )}

      <p className="text-xs text-gray-500 mt-3 text-center">
        {t('summary.price_includes')}
      </p>
    </div>
  );
};

export default BookingSummary;