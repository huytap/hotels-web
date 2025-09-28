import React from 'react';
import type { RoomAvailability } from '../types/api';
import RoomCard from './RoomCard';

interface SelectedRoom {
  roomId: number;
  quantity: number;
  promotionId?: number;
}

interface RoomListProps {
  rooms: (RoomAvailability | any)[];
  selectedRooms: SelectedRoom[];
  onRoomSelectionChange: (roomId: string, quantity: number, promotionId?: string) => void;
  loading?: boolean;
}

const RoomList: React.FC<RoomListProps> = ({
  rooms,
  selectedRooms,
  onRoomSelectionChange,
  loading = false
}) => {
  console.log('🏨 RoomList received:', {
    rooms,
    roomsLength: rooms.length,
    selectedRooms,
    selectedRoomsLength: selectedRooms.length
  });
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
          Không tìm thấy phòng phù hợp
        </h3>
        <p className="text-gray-500">
          Vui lòng thử thay đổi ngày hoặc số lượng khách để tìm phòng khác.
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {rooms.flatMap((roomData, index) => {
        // Transform Laravel API response to expected format
        if (roomData.combination_details && roomData.combination_details.length > 0) {
          return roomData.combination_details.map((detail: any, detailIndex: number) => {
            const roomType = detail.room_type;
            const roomKey = `room-${roomType.id}-${index}-${detailIndex}`;
            // Transform to RoomAvailability format
            const transformedRoom: RoomAvailability = {
              room_id: roomType.id,
              room: {
                id: roomType.id,
                name: roomType.name,
                type: roomType.name,
                description: roomType.description || '',
                capacity: 2, // Default capacity
                amenities: roomType.amenities ? roomType.amenities.split(', ') : [],
                images: roomType.gallery ? (typeof roomType.gallery === 'string' ? JSON.parse(roomType.gallery) : roomType.gallery) : [],
                base_price: detail.base_price_total,
                currency: 'VND'
              },
              available_inventory: detail.available_rooms,
              rate_per_night: detail.base_price_total,
              total_price: roomData.total_price,
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
                applicable_room_types: [roomType.id.toString()],
                is_active: !!promo.details.is_active,
                max_uses: 999,
                current_uses: 0
              }))
            };

            console.log('🔄 Transformed room:', transformedRoom);

            return (
              <RoomCard
                key={roomKey}
                roomAvailability={transformedRoom}
                onAddRoom={onRoomSelectionChange}
                selectedRooms={selectedRooms}
                selectedQuantity={getSelectedQuantity(transformedRoom.room_id)}
                maxQuantity={detail.available_rooms}
              />
            );
          });
        }

        // Fallback for unexpected format
        return [
          <div key={`room-error-${index}`} className="p-4 border border-red-200 rounded-lg bg-red-50">
            <p className="text-red-600">⚠️ Unexpected room data format</p>
            <pre className="text-xs">{JSON.stringify(roomData, null, 2)}</pre>
          </div>
        ];
      })}
    </div>
  );
};

export default RoomList;