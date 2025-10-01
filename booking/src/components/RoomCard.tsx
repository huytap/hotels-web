import React, { useState, useEffect } from 'react';
import { Users, Wifi, Car, Coffee, Tag, Plus, Minus, Eye } from 'lucide-react';
import type { RoomAvailability, Promotion, PricingBreakdown } from '../types/api';
import RoomGalleryPopup from './RoomGalleryPopup';
import { useLocalizedText } from '../context/LanguageContext';
import { useHotel } from '../context/HotelContext';

interface PromotionQuantity {
  promotionId: number;
  quantity: number;
}

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
  useExtraBed?: boolean;
}

interface RoomCardProps {
  roomAvailability: RoomAvailability;
  onAddRoom: (roomId: string, quantity: number, promotionId?: string, useExtraBed?: boolean) => void;
  selectedRooms: SelectedRoom[];
  selectedQuantity?: number;
  maxQuantity?: number;
  recommendedQuantity?: number;
  canAccommodateGuests?: boolean;
}

const RoomCard: React.FC<RoomCardProps> = ({
  roomAvailability,
  onAddRoom,
  selectedRooms,
  selectedQuantity = 0,
  maxQuantity = 5,
  recommendedQuantity = 1,
  canAccommodateGuests: _canAccommodateGuests = true
}) => {
  const { room, rate_per_night, available_inventory, applicable_promotions } = roomAvailability;
  const { bookingDetails } = useHotel();

  // Debug logging
  console.log('🏨 RoomCard - roomAvailability:', roomAvailability);
  console.log('🏨 RoomCard - pricing_breakdown:', roomAvailability.pricing_breakdown);
  const [baseQuantity, setBaseQuantity] = useState(selectedQuantity || 0);
  const [promotionQuantities, setPromotionQuantities] = useState<PromotionQuantity[]>(
    applicable_promotions.map(promo => ({ promotionId: promo.id, quantity: 0 }))
  );
  const [useExtraBed, setUseExtraBed] = useState(false);
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);
  const { t } = useLocalizedText();

  // Sync internal state with selectedRooms from parent
  useEffect(() => {
    const currentRoomId = roomAvailability.room_id || room.id;

    // Reset base quantity
    const baseRoomSelection = selectedRooms.find(r => r.roomId === currentRoomId && !r.promotionId);
    const initialQuantity = baseRoomSelection?.quantity || 0;
    setBaseQuantity(initialQuantity);

    // Auto-select recommended quantity if no selection exists
    // Disabled auto-selection to let users choose manually
    // if (!baseRoomSelection && recommendedQuantity > 0) {
    //   const roomId = roomAvailability.room_id || room.id;
    //   onAddRoom(roomId.toString(), recommendedQuantity, undefined);
    // }

    // Reset promotion quantities
    setPromotionQuantities(prev =>
      prev.map(pq => {
        const promotionSelection = selectedRooms.find(r =>
          r.roomId === currentRoomId && r.promotionId === pq.promotionId
        );
        return {
          ...pq,
          quantity: promotionSelection?.quantity || 0
        };
      })
    );
  }, [selectedRooms, roomAvailability.room_id, room.id, recommendedQuantity, onAddRoom]);

  // Update pricing when extra bed choice changes
  useEffect(() => {
    if (baseQuantity > 0) {
      const roomId = roomAvailability.room_id || room.id;
      onAddRoom(roomId.toString(), baseQuantity, undefined, useExtraBed);
    }
  }, [useExtraBed]); // Re-calculate when extra bed choice changes

  const getRemainingInventory = (excludePromotion?: number): number => {
    const currentTotal = baseQuantity + promotionQuantities
      .filter(pq => pq.promotionId !== excludePromotion)
      .reduce((sum, pq) => sum + pq.quantity, 0);

    console.log('🏨 Inventory check:', {
      available_inventory,
      maxQuantity,
      baseQuantity,
      promotionQuantities,
      currentTotal,
      remaining: available_inventory - currentTotal
    });

    return Math.max(0, available_inventory - currentTotal);
  };

  const handleBaseQuantityChange = (newQuantity: number) => {
    const maxAllowed = baseQuantity + getRemainingInventory();
    if (newQuantity >= 0 && newQuantity <= maxAllowed) {
      setBaseQuantity(newQuantity);
      // Use roomAvailability.room_id for consistency
      const roomId = roomAvailability.room_id || room.id;
      console.log('🏨 RoomCard: Adding room with ID:', roomId, 'room.id:', room.id, 'roomAvailability.room_id:', roomAvailability.room_id);
      onAddRoom(roomId.toString(), newQuantity, undefined, useExtraBed); // Base price, no promotion
    }
  };

  const handlePromotionQuantityChange = (promotionId: number, newQuantity: number) => {
    const currentPromotionQuantity = promotionQuantities.find(pq => pq.promotionId === promotionId)?.quantity || 0;
    const maxAllowed = currentPromotionQuantity + getRemainingInventory(promotionId);

    if (newQuantity >= 0 && newQuantity <= maxAllowed) {
      setPromotionQuantities(prev =>
        prev.map(pq =>
          pq.promotionId === promotionId
            ? { ...pq, quantity: newQuantity }
            : pq
        )
      );
      // Trigger callback for this specific promotion
      const roomId = roomAvailability.room_id || room.id;
      onAddRoom(roomId.toString(), newQuantity, promotionId.toString(), useExtraBed);
    }
  };

  const getPromotionDiscount = (promotion: Promotion, originalPrice: number): number => {
    if (promotion.type === 'percentage') {
      return originalPrice * (promotion.value / 100);
    } else if (promotion.type === 'fixed') {
      return promotion.value;
    }
    return 0;
  };

  const calculatePromotionPrice = (promotion: Promotion): number => {
    const discount = getPromotionDiscount(promotion, rate_per_night);
    return Math.max(0, rate_per_night - discount);
  };

  const getTotalRoomsSelected = (): number => {
    return baseQuantity + promotionQuantities.reduce((sum, pq) => sum + pq.quantity, 0);
  };
  const cleanDescription = (desc: string | undefined) => {
    if (!desc) return "";
    return desc.replace(/<!--[\s\S]*?-->/g, "").trim();
  };
  const calculateTotalPrice = (): number => {
    let total = baseQuantity * rate_per_night;

    promotionQuantities.forEach(pq => {
      if (pq.quantity > 0) {
        const promotion = applicable_promotions.find(p => p.id === pq.promotionId);
        if (promotion) {
          total += pq.quantity * calculatePromotionPrice(promotion);
        }
      }
    });

    return total;
  };

  // Quantity Selector Component
  const QuantitySelector: React.FC<{
    quantity: number;
    onQuantityChange: (newQuantity: number) => void;
    maxQuantity: number;
    label?: string;
    disabled?: boolean;
  }> = ({ quantity, onQuantityChange, maxQuantity, label, disabled = false }) => (
    <div className="flex items-center gap-3">
      {label && <span className="text-sm font-medium text-gray-700">{label}</span>}
      <div className="flex items-center gap-2">
        <button
          onClick={() => onQuantityChange(quantity - 1)}
          disabled={disabled || quantity <= 0}
          className="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Minus className="w-4 h-4" />
        </button>
        <span className={`w-8 text-center font-medium ${disabled ? 'text-gray-400' : ''}`}>{quantity}</span>
        <button
          onClick={() => onQuantityChange(quantity + 1)}
          disabled={disabled || quantity >= maxQuantity}
          className="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Plus className="w-4 h-4" />
        </button>
      </div>
    </div>
  );

  const amenityIcons: { [key: string]: React.ReactNode } = {
    'wifi': <Wifi className="w-4 h-4" />,
    'parking': <Car className="w-4 h-4" />,
    'breakfast': <Coffee className="w-4 h-4" />,
  };

  return (
    <div className="bg-white rounded-lg shadow-lg border overflow-hidden">
      {/* Room Image */}
      <div className="flex bg-white rounded-lg shadow-md overflow-hidden">
        <div className="w-2/5 relative h-64 group cursor-pointer" onClick={() => setIsGalleryOpen(true)}>
          <img
            src={room.images[0] || '/placeholder-room.jpg'}
            alt={room.name}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          />

          {/* Gallery Overlay */}
          <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center">
            <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
              <Eye size={32} className="mx-auto mb-2" />
              <span className="text-sm font-medium">{t('room.view_gallery')}</span>
              {room.images && room.images.length > 1 && (
                <div className="text-xs mt-1">{t('room.more_images', { count: room.images.length - 1 })}</div>
              )}
            </div>
          </div>

          {applicable_promotions.length > 0 && (
            <div className="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold z-10">
              {t('promotion.count', { count: applicable_promotions.length })}
            </div>
          )}
        </div>
        <div className="w-3/5 p-4 text-left">
          <h3
            className="text-xl font-bold text-gray-800 mb-2 hover:text-blue-600 cursor-pointer transition-colors duration-300"
            onClick={() => setIsGalleryOpen(true)}
          >
            {room.name}
          </h3>
          <div className="text-gray-600 text-sm mb-2">
            <div dangerouslySetInnerHTML={{ __html: cleanDescription(room.description) }} />
          </div>
          <div className="flex items-center gap-4 text-sm text-gray-500 mb-2">
            <div className="flex items-center gap-1">
              <Users className="w-4 h-4" />
              <span>{t('room.capacity', { count: room.capacity })}</span>
            </div>
            <span>{t('room.remaining', { count: available_inventory })}</span>
          </div>

          {/* Recommended quantity notice */}
          {recommendedQuantity > 1 && (
            <div className="mb-3 p-2 bg-blue-50 border border-blue-200 rounded-lg">
              <div className="flex items-center gap-2 text-blue-700">
                <div className="w-4 h-4 rounded-full bg-blue-500 flex items-center justify-center">
                  <div className="w-2 h-2 bg-white rounded-full"></div>
                </div>
                <span className="text-sm font-medium">
                  {t('room.recommended_rooms', { count: recommendedQuantity })}
                </span>
              </div>
            </div>
          )}

          {/* Extra bed selection - show when room has extra bed and exceeds base capacity */}
          {roomAvailability.requires_extra_bed && roomAvailability.room?.is_extra_bed_available && (
            <div className="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
              <div className="text-sm font-medium text-amber-800 mb-2">
                ⚠️ Số khách ({bookingDetails?.adults || 0}) vượt quá capacity phòng ({room.capacity} người)
              </div>

              <div className="flex items-start gap-3 mt-3">
                <input
                  type="checkbox"
                  id={`extra-bed-${room.id}`}
                  checked={useExtraBed}
                  onChange={(e) => {
                    setUseExtraBed(e.target.checked);
                    // Trigger re-calculation when user changes extra bed choice
                    if (baseQuantity > 0) {
                      const roomId = roomAvailability.room_id || room.id;
                      onAddRoom(roomId.toString(), baseQuantity, undefined, e.target.checked);
                    }
                  }}
                  className="mt-1 w-4 h-4 text-amber-600 border-amber-300 rounded focus:ring-amber-500"
                />
                <div className="flex-1">
                  <label htmlFor={`extra-bed-${room.id}`} className="text-sm font-medium text-amber-800 cursor-pointer">
                    ✓ Tôi chấp nhận sử dụng giường phụ
                  </label>
                  <div className="text-xs text-amber-600 mt-1">
                    {useExtraBed ? (
                      <div>
                        <div className="text-green-700 font-medium">
                          → Phòng có thể chứa đủ {bookingDetails?.adults || 0} khách
                        </div>
                        <div>
                          Phụ thu giường phụ: <strong>{(roomAvailability.pricing_breakdown?.extra_bed_adult_surcharge_total || 0).toLocaleString('vi-VN')} VND</strong>
                        </div>
                      </div>
                    ) : (
                      <div className="text-red-700">
                        → Phòng chỉ chứa được {room.capacity} người (thiếu {(bookingDetails?.adults || 0) - room.capacity} chỗ)
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {!useExtraBed && (
                <div className="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                  <strong>Lưu ý:</strong> Nếu không chọn giường phụ, phòng này không đủ chỗ cho số khách của bạn.
                </div>
              )}
            </div>
          )}

          {/* Show accommodation conflict warning */}
          {roomAvailability.requires_extra_bed && !roomAvailability.room?.is_extra_bed_available && (
            <div className="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
              <div className="text-sm font-medium text-red-800 mb-1">
                ❌ Phòng không phù hợp
              </div>
              <div className="text-xs text-red-600">
                Phòng này chỉ chứa được {room.capacity} người, không có giường phụ.
                Không thể chứa {roomAvailability.effective_capacity} khách như yêu cầu.
              </div>
            </div>
          )}

          {/* Amenities */}
          <div className="flex flex-wrap gap-2 mb-4">
            {room.amenities.map((amenity, index) => (
              <div
                key={index}
                className="flex items-center gap-1 text-xs bg-gray-100 px-2 py-1 rounded"
              >
                {amenityIcons[amenity.toLowerCase()] || <Tag className="w-3 h-3" />}
                <span className="capitalize">{amenity}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="p-6 text-left">
        {/* Promotion Options */}
        {applicable_promotions.length > 0 ? (
          <div className="mb-4">
            {/* <h4 className="font-semibold text-gray-800 mb-3 flex items-center gap-1">
              <Tag className="w-4 h-4" />
              {t('promotion.title')}
            </h4> */}
            <div className="space-y-3">
              {applicable_promotions.map((promotion) => {
                const promotionQuantity = promotionQuantities.find(pq => pq.promotionId === promotion.id)?.quantity || 0;

                // Calculate pricing based on user's extra bed choice
                let originalPrice, promotionPrice, discountAmount;

                if (roomAvailability.pricing_breakdown) {
                  // Base price calculation considering extra bed choice
                  let basePrice = roomAvailability.pricing_breakdown.promotion_applicable_amount;
                  let extraCharges = 0;

                  if (useExtraBed) {
                    // User chose extra bed - include extra bed surcharge
                    extraCharges = roomAvailability.pricing_breakdown.non_promotion_amount;
                  } else {
                    // User declined extra bed - only include children surcharge (if any)
                    extraCharges = roomAvailability.pricing_breakdown.children_surcharge_total || 0;
                  }

                  originalPrice = basePrice + extraCharges;

                  // Tính discount chỉ áp dụng cho promotion_applicable_amount
                  const promotionDiscount = promotion.type === 'percentage'
                    ? basePrice * (promotion.value / 100)
                    : promotion.value;

                  // Giá sau promotion: (basePrice - discount) + extraCharges
                  promotionPrice = (basePrice - promotionDiscount) + extraCharges;
                  discountAmount = promotionDiscount;
                } else {
                  // Fallback logic cũ
                  originalPrice = rate_per_night;
                  promotionPrice = calculatePromotionPrice(promotion);
                  discountAmount = originalPrice - promotionPrice;
                }

                return (
                  <div key={promotion.id} className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div className="flex items-center justify-between">
                      <div className="flex-1">
                        <div className="text-sm font-medium text-yellow-800 mb-1">{promotion.name}</div>
                        <div className="text-xs text-yellow-600 mb-2">
                          <div
                            dangerouslySetInnerHTML={{ __html: promotion.description }}
                          /></div>
                      </div>
                      <div className=" m-5">
                        <div className="gap-3">
                          <div className="text-sm text-gray-500 line-through">
                            {originalPrice.toLocaleString('vi-VN')} VND
                          </div>
                          <div className="text-lg font-bold text-green-600">
                            {promotionPrice.toLocaleString('vi-VN')} VND
                            <span className="text-xs text-gray-500 ml-1">/ tổng</span>
                          </div>
                        </div>
                        <div className="text-xs text-green-600 font-medium">
                          {t('promotion.savings', {
                            amount: discountAmount.toLocaleString('vi-VN'),
                            type: promotion.type === 'percentage' ? t('promotion.percentage', { value: promotion.value }) : t('promotion.fixed')
                          })}
                        </div>
                      </div>
                      <div className="flex-shrink-0">
                        <QuantitySelector
                          quantity={promotionQuantity}
                          onQuantityChange={(newQuantity) => handlePromotionQuantityChange(promotion.id, newQuantity)}
                          maxQuantity={Math.min(available_inventory, promotionQuantity + getRemainingInventory(promotion.id))}
                          disabled={roomAvailability.requires_extra_bed && !roomAvailability.room?.is_extra_bed_available}
                        />
                      </div>
                    </div>
                    {promotionQuantity > 0 && (
                      <div className="mt-3 text-right">
                        <div className="text-sm text-gray-500">Tổng cộng:</div>
                        <div className="text-lg font-bold text-green-600">
                          {(promotionPrice * promotionQuantity).toLocaleString('vi-VN')} VND
                        </div>
                        {roomAvailability.pricing_breakdown && roomAvailability.pricing_breakdown.non_promotion_amount > 0 && (
                          <div className="text-xs text-gray-500 mt-1">
                            {(() => {
                              const breakdown = roomAvailability.pricing_breakdown;
                              const extraBedAmount = (breakdown.extra_bed_adult_surcharge_total || 0) * promotionQuantity;
                              const childAmount = (breakdown.children_surcharge_total || 0) * promotionQuantity;

                              if (extraBedAmount > 0 && childAmount > 0) {
                                return `(Phụ thu người lớn giường phụ: ${extraBedAmount.toLocaleString('vi-VN')} VND + Phụ thu trẻ em: ${childAmount.toLocaleString('vi-VN')} VND không giảm giá)`;
                              } else if (extraBedAmount > 0) {
                                return `(Phụ thu người lớn giường phụ: ${extraBedAmount.toLocaleString('vi-VN')} VND không giảm giá)`;
                              } else if (childAmount > 0) {
                                return `(Phụ thu trẻ em: ${childAmount.toLocaleString('vi-VN')} VND không giảm giá)`;
                              }
                              return `(Phụ thu: ${(breakdown.non_promotion_amount * promotionQuantity).toLocaleString('vi-VN')} VND không giảm giá)`;
                            })()}
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        ) : (
          <div className="mb-4">
            {/* Base Price Option */}
            <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <div className="flex items-center justify-between">
                <div className="flex-1">
                  <h4 className="font-semibold text-blue-800 mb-1">Giá Gốc</h4>
                  <div className="text-sm text-blue-600 mb-2">
                    {t('promotion.no_promotion')}
                  </div>
                  <div className="text-lg font-bold text-blue-600">
                    {(() => {
                      if (roomAvailability.pricing_breakdown) {
                        let basePrice = roomAvailability.pricing_breakdown.promotion_applicable_amount;
                        let extraCharges = 0;

                        if (useExtraBed) {
                          // User chose extra bed - include extra bed surcharge
                          extraCharges = roomAvailability.pricing_breakdown.non_promotion_amount;
                        } else {
                          // User declined extra bed - only include children surcharge (if any)
                          extraCharges = roomAvailability.pricing_breakdown.children_surcharge_total || 0;
                        }

                        return (basePrice + extraCharges).toLocaleString('vi-VN');
                      } else {
                        return rate_per_night.toLocaleString('vi-VN');
                      }
                    })()} VND
                    <span className="text-xs text-gray-500 ml-1">/ tổng</span>
                  </div>
                </div>
                <div className="flex-shrink-0">
                  <QuantitySelector
                    quantity={baseQuantity}
                    onQuantityChange={handleBaseQuantityChange}
                    maxQuantity={Math.min(available_inventory, baseQuantity + getRemainingInventory())}
                    disabled={roomAvailability.requires_extra_bed && !roomAvailability.room?.is_extra_bed_available}
                  />
                </div>
              </div>
              {baseQuantity > 0 && (
                <div className="mt-3 text-right">
                  <div className="text-sm text-gray-500">Tổng cộng:</div>
                  <div className="text-lg font-bold text-blue-600">
                    {(() => {
                      if (roomAvailability.pricing_breakdown) {
                        let basePrice = roomAvailability.pricing_breakdown.promotion_applicable_amount;
                        let extraCharges = 0;

                        if (useExtraBed) {
                          // User chose extra bed - include extra bed surcharge
                          extraCharges = roomAvailability.pricing_breakdown.non_promotion_amount;
                        } else {
                          // User declined extra bed - only include children surcharge (if any)
                          extraCharges = roomAvailability.pricing_breakdown.children_surcharge_total || 0;
                        }

                        return ((basePrice + extraCharges) * baseQuantity).toLocaleString('vi-VN');
                      } else {
                        return (rate_per_night * baseQuantity).toLocaleString('vi-VN');
                      }
                    })()} VND
                  </div>
                  {roomAvailability.pricing_breakdown && (
                    <div className="text-xs text-gray-500 mt-1">
                      {(() => {
                        const breakdown = roomAvailability.pricing_breakdown;
                        if (useExtraBed && (breakdown.extra_bed_adult_surcharge_total || 0) > 0) {
                          const extraBedAmount = (breakdown.extra_bed_adult_surcharge_total || 0) * baseQuantity;
                          const childAmount = (breakdown.children_surcharge_total || 0) * baseQuantity;

                          if (childAmount > 0) {
                            return `(Bao gồm phụ thu người lớn giường phụ: ${extraBedAmount.toLocaleString('vi-VN')} VND + Phụ thu trẻ em: ${childAmount.toLocaleString('vi-VN')} VND)`;
                          } else {
                            return `(Bao gồm phụ thu người lớn giường phụ: ${extraBedAmount.toLocaleString('vi-VN')} VND)`;
                          }
                        } else if ((breakdown.children_surcharge_total || 0) > 0) {
                          const childAmount = (breakdown.children_surcharge_total || 0) * baseQuantity;
                          return `(Bao gồm phụ thu trẻ em: ${childAmount.toLocaleString('vi-VN')} VND)`;
                        }
                        return '';
                      })()}
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        )}

        {/* Inventory Status */}
        <div className="mt-3 space-y-2">
          {available_inventory <= 3 && (
            <div className="text-sm text-orange-600 font-medium">
              ⚠️ Chỉ còn {available_inventory} phòng!
            </div>
          )}

          {getTotalRoomsSelected() > 0 && (
            <div className="text-sm text-gray-600">
              Còn lại: {getRemainingInventory()} phòng có thể chọn
            </div>
          )}

          {getTotalRoomsSelected() >= available_inventory && (
            <div className="text-sm text-red-600 font-medium">
              🔒 Đã chọn tối đa số phòng có sẵn
            </div>
          )}
        </div>
      </div>

      {/* Room Gallery Popup */}
      <RoomGalleryPopup
        isOpen={isGalleryOpen}
        onClose={() => setIsGalleryOpen(false)}
        room={room}
        ratePerNight={rate_per_night}
      />
    </div>
  );
};

export default RoomCard;