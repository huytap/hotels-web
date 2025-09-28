import React, { useState, useEffect } from 'react';
import { Users, Wifi, Car, Coffee, Tag, Plus, Minus, Eye } from 'lucide-react';
import type { RoomAvailability, Promotion } from '../types/api';
import RoomGalleryPopup from './RoomGalleryPopup';

interface PromotionQuantity {
  promotionId: number;
  quantity: number;
}

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
}

interface RoomCardProps {
  roomAvailability: RoomAvailability;
  onAddRoom: (roomId: number, quantity: number, promotionId?: number) => void;
  selectedRooms: SelectedRoom[];
  selectedQuantity?: number;
  maxQuantity?: number;
}

const RoomCard: React.FC<RoomCardProps> = ({
  roomAvailability,
  onAddRoom,
  selectedRooms,
  selectedQuantity = 0,
  maxQuantity = 5
}) => {
  const { room, rate_per_night, available_inventory, applicable_promotions } = roomAvailability;
  const [baseQuantity, setBaseQuantity] = useState(selectedQuantity);
  const [promotionQuantities, setPromotionQuantities] = useState<PromotionQuantity[]>(
    applicable_promotions.map(promo => ({ promotionId: promo.id, quantity: 0 }))
  );
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);

  // Sync internal state with selectedRooms from parent
  useEffect(() => {
    const currentRoomId = roomAvailability.room_id || room.id;

    // Reset base quantity
    const baseRoomSelection = selectedRooms.find(r => r.roomId === currentRoomId && !r.promotionId);
    setBaseQuantity(baseRoomSelection?.quantity || 0);

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
  }, [selectedRooms, roomAvailability.room_id, room.id]);

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
      onAddRoom(roomId, newQuantity, undefined); // Base price, no promotion
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
      onAddRoom(roomId, newQuantity, promotionId);
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
  const cleanDescription = (desc) => {
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
  }> = ({ quantity, onQuantityChange, maxQuantity, label }) => (
    <div className="flex items-center gap-3">
      {label && <span className="text-sm font-medium text-gray-700">{label}</span>}
      <div className="flex items-center gap-2">
        <button
          onClick={() => onQuantityChange(quantity - 1)}
          disabled={quantity <= 0}
          className="w-8 h-8 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Minus className="w-4 h-4" />
        </button>
        <span className="w-8 text-center font-medium">{quantity}</span>
        <button
          onClick={() => onQuantityChange(quantity + 1)}
          disabled={quantity >= maxQuantity}
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
              <span className="text-sm font-medium">Xem gallery</span>
              {room.images && room.images.length > 1 && (
                <div className="text-xs mt-1">+{room.images.length - 1} ảnh khác</div>
              )}
            </div>
          </div>

          {applicable_promotions.length > 0 && (
            <div className="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold z-10">
              {applicable_promotions.length} Khuyến Mãi
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
              <span>Tối đa {room.capacity} người</span>
            </div>
            <span>Còn {available_inventory} phòng</span>
          </div>

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
              Chương Trình Khuyến Mãi
            </h4> */}
            <div className="space-y-3">
              {applicable_promotions.map((promotion) => {
                const promotionPrice = calculatePromotionPrice(promotion);
                const discountAmount = rate_per_night - promotionPrice;
                const promotionQuantity = promotionQuantities.find(pq => pq.promotionId === promotion.id)?.quantity || 0;

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
                            {rate_per_night.toLocaleString('vi-VN')} VND
                          </div>
                          <div className="text-lg font-bold text-green-600">
                            {promotionPrice.toLocaleString('vi-VN')} VND
                            <span className="text-xs text-gray-500 ml-1">/ đêm</span>
                          </div>
                        </div>
                        <div className="text-xs text-green-600 font-medium">
                          Tiết kiệm {discountAmount.toLocaleString('vi-VN')} VND ({promotion.type === 'percentage' ? `${promotion.value}%` : 'Giá cố định'})
                        </div>
                      </div>
                      <div className="flex-shrink-0">
                        <QuantitySelector
                          quantity={promotionQuantity}
                          onQuantityChange={(newQuantity) => handlePromotionQuantityChange(promotion.id, newQuantity)}
                          maxQuantity={Math.min(available_inventory, promotionQuantity + getRemainingInventory(promotion.id.toString()))}
                        />
                      </div>
                    </div>
                    {promotionQuantity > 0 && (
                      <div className="mt-3 text-right">
                        <div className="text-sm text-gray-500">Tổng cộng:</div>
                        <div className="text-lg font-bold text-green-600">
                          {(promotionPrice * promotionQuantity).toLocaleString('vi-VN')} VND
                        </div>
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
                    Không áp dụng khuyến mãi
                  </div>
                  <div className="text-lg font-bold text-blue-600">
                    {rate_per_night.toLocaleString('vi-VN')} VND
                    <span className="text-xs text-gray-500 ml-1">/ đêm</span>
                  </div>
                </div>
                <div className="flex-shrink-0">
                  <QuantitySelector
                    quantity={baseQuantity}
                    onQuantityChange={handleBaseQuantityChange}
                    maxQuantity={Math.min(available_inventory, baseQuantity + getRemainingInventory())}
                  />
                </div>
              </div>
              {baseQuantity > 0 && (
                <div className="mt-3 text-right">
                  <div className="text-sm text-gray-500">Tổng cộng:</div>
                  <div className="text-lg font-bold text-blue-600">
                    {(rate_per_night * baseQuantity).toLocaleString('vi-VN')} VND
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Total Summary */}
        {getTotalRoomsSelected() > 0 && (
          <div className="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-sm text-gray-600">Tổng phòng đã chọn:</div>
                <div className="text-lg font-semibold text-gray-800">{getTotalRoomsSelected()} phòng</div>
              </div>
              <div className="text-right">
                <div className="text-sm text-gray-600">Tổng tiền:</div>
                <div className="text-xl font-bold text-green-600">
                  {calculateTotalPrice().toLocaleString('vi-VN')} VND
                </div>
              </div>
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