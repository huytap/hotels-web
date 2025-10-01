import React from 'react';
import type { RoomAvailability } from '../types/api';
import RoomCard from './RoomCard';
import { useLocalizedText } from '../context/LanguageContext';
import { useHotel } from '../context/HotelContext';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
  useExtraBed?: boolean;
}

interface RoomListProps {
  rooms: (RoomAvailability | any)[];
  selectedRooms: SelectedRoom[];
  onRoomSelectionChange: (roomId: string, quantity: number, promotionId?: string, useExtraBed?: boolean) => void;
  loading?: boolean;
}

const RoomList: React.FC<RoomListProps> = ({
  rooms,
  selectedRooms,
  onRoomSelectionChange,
  loading = false
}) => {
  const { t } = useLocalizedText();
  const { bookingDetails } = useHotel();
  console.log('🏨 RoomList received:', {
    rooms,
    roomsLength: rooms.length,
    selectedRooms,
    selectedRoomsLength: selectedRooms.length
  });

  // Debug first room structure
  if (rooms.length > 0) {
    console.log('🏨 First room structure:', rooms[0]);
    console.log('🏨 Has combination_details?', !!rooms[0].combination_details);
    console.log('🏨 Has room property?', !!rooms[0].room);
  }
  const getSelectedQuantity = (roomId: string): number => {
    const numericRoomId = parseInt(roomId, 10);
    const selectedRoom = selectedRooms.find(r => r.roomId === numericRoomId);
    return selectedRoom?.quantity || 0;
  };

  if (loading) {
    return (
      <div className="space-y-6">
        {[1, 2, 3].map((i) => (
          <div key={i} className="bg-white rounded-lg shadow-lg border p-6 animate-pulse">
            <div className="h-48 bg-gray-300 rounded mb-4"></div>
            <div className="space-y-3">
              <div className="h-6 bg-gray-300 rounded w-3/4"></div>
              <div className="h-4 bg-gray-300 rounded w-full"></div>
              <div className="h-4 bg-gray-300 rounded w-2/3"></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  if (rooms.length === 0) {
    return (
      <div className="text-center py-12">
        <div className="text-6xl mb-4">🏨</div>
        <h3 className="text-xl font-semibold text-gray-600 mb-2">
          {t('rooms.no_rooms_found')}
        </h3>
        <p className="text-gray-500">
          {t('rooms.no_rooms_description')}
        </p>
      </div>
    );
  }

  // Group rooms by room type to avoid duplicates
  const groupedRooms = new Map();

  rooms.forEach((roomData) => {
    if (roomData.combination_details && roomData.combination_details.length > 0) {
      roomData.combination_details.forEach((detail: any) => {
        const roomType = detail.room_type;
        const roomTypeId = roomType.id;

        // Skip if no rooms available
        if (!detail.available_rooms || detail.available_rooms <= 0) {
          console.log(`🏨 RoomList: Skipping room type ${roomTypeId} (${roomType.name}) - No rooms available`);
          return;
        }

        if (!groupedRooms.has(roomTypeId)) {
          const normalizedBasePrice = detail.base_price_total / detail.quantity;
          console.log(`🏨 RoomList: Adding room type ${roomTypeId} (${roomType.name}):`, {
            quantity: detail.quantity,
            available_rooms: detail.available_rooms,
            base_price_total: detail.base_price_total,
            normalized_base_price: normalizedBasePrice,
            adults_from_context: bookingDetails?.adults
          });

          // Use the first occurrence of this room type as the base data
          groupedRooms.set(roomTypeId, {
            roomType,
            detail,
            maxQuantity: detail.available_rooms,
            basePrice: normalizedBasePrice,
            promotions: detail.promotions,
            canAccommodateGuests: detail.can_accommodate_guests,
            totalCapacity: detail.total_capacity,
            effectiveCapacity: detail.effective_capacity, // Thêm effective capacity
            minQuantityRecommended: detail.quantity // Quantity from API is the minimum recommended
          });
        } else {
          console.log(`🏨 RoomList: Skipping duplicate room type ${roomTypeId} with quantity ${detail.quantity}`);
        }
      });
    }
  });

  return (
    <div className="space-y-6">
      {Array.from(groupedRooms.entries()).map(([roomTypeId, roomInfo]) => {
        const { roomType, maxQuantity, basePrice, promotions, canAccommodateGuests, minQuantityRecommended, effectiveCapacity } = roomInfo;
        const roomKey = `room-${roomTypeId}`;

        // Debug logging
        console.log('🏨 Room debug:', {
          roomTypeId,
          roomType: {
            name: roomType.name,
            adult_capacity: roomType.adult_capacity,
            is_extra_bed_available: roomType.is_extra_bed_available
          },
          bookingAdults: bookingDetails?.adults,
          requiresExtraBed: (bookingDetails?.adults || 0) > (roomType.adult_capacity || 2)
        });

        // Transform to RoomAvailability format
        const transformedRoom: RoomAvailability = {
          room_id: roomType.id,
          room: {
            id: roomType.id,
            name: roomType.name,
            type: roomType.name,
            description: roomType.description || '',
            capacity: roomType.adult_capacity || 2, // Use actual capacity from API
            amenities: roomType.amenities ? roomType.amenities.split(', ') : [],
            images: roomType.gallery ? (typeof roomType.gallery === 'string' ? JSON.parse(roomType.gallery) : roomType.gallery) : [],
            base_price: basePrice,
            currency: 'VND',
            is_extra_bed_available: roomType.is_extra_bed_available || false
          },
          available_inventory: maxQuantity,
          rate_per_night: basePrice,
          total_price: basePrice,
          pricing_breakdown: roomInfo.detail.pricing_breakdown ? {
            ...roomInfo.detail.pricing_breakdown,
            // Chia các giá trị cho quantity để có giá per room
            base_total: roomInfo.detail.pricing_breakdown.base_total / roomInfo.detail.quantity,
            adult_surcharge_total: roomInfo.detail.pricing_breakdown.adult_surcharge_total / roomInfo.detail.quantity,
            children_surcharge_total: roomInfo.detail.pricing_breakdown.children_surcharge_total / roomInfo.detail.quantity,
            promotion_applicable_amount: roomInfo.detail.pricing_breakdown.promotion_applicable_amount / roomInfo.detail.quantity,
            non_promotion_amount: roomInfo.detail.pricing_breakdown.non_promotion_amount / roomInfo.detail.quantity,
            final_total: roomInfo.detail.pricing_breakdown.final_total / roomInfo.detail.quantity,
            // Also normalize the new extra bed adult surcharge field
            extra_bed_adult_surcharge_total: (roomInfo.detail.pricing_breakdown.extra_bed_adult_surcharge_total || 0) / roomInfo.detail.quantity,
          } : undefined, // Pricing breakdown per room!
          effective_capacity: effectiveCapacity, // Thêm effective capacity!
          is_extra_bed_available: roomType.is_extra_bed_available || false,
          requires_extra_bed: roomType.requires_extra_bed || false,
          applicable_promotions: Object.values(promotions || {}).map((promo: any) => {
            const details = promo.details || promo; // Fallback in case structure changes
            return {
              id: details.id || 0,
              code: details.promotion_code || details.code || '',
              name: details.name?.vi || details.name?.en || details.name || '',
              description: details.description?.vi || details.description || '',
              type: details.value_type === 'percentage' ? 'percentage' as const : 'fixed' as const,
              value: parseFloat(details.value || '0'),
              start_date: details.start_date || '',
              end_date: details.end_date || '',
              min_nights: details.min_stay || 1,
              applicable_room_types: [roomType.id.toString()],
              is_active: !!details.is_active,
              max_uses: 999,
              current_uses: 0
            };
          })
        };

        console.log('🔄 Transformed room:', transformedRoom);
        console.log('🔄 Original detail pricing_breakdown:', roomInfo.detail.pricing_breakdown);
        console.log('🔄 Additional charges detail:', roomInfo.detail.additional_charges_detail);

        return (
          <RoomCard
            key={roomKey}
            roomAvailability={transformedRoom}
            onAddRoom={onRoomSelectionChange}
            selectedRooms={selectedRooms}
            selectedQuantity={getSelectedQuantity(transformedRoom.room_id.toString())}
            maxQuantity={maxQuantity}
            recommendedQuantity={minQuantityRecommended}
            canAccommodateGuests={canAccommodateGuests}
          />
        );
      })}

      {groupedRooms.size === 0 && rooms.length > 0 && (
        <div className="p-4 border border-red-200 rounded-lg bg-red-50">
          <p className="text-red-600">⚠️ {t('rooms.unexpected_data')}</p>
          <pre className="text-xs">{JSON.stringify(rooms, null, 2)}</pre>
        </div>
      )}
    </div>
  );
};

export default RoomList;