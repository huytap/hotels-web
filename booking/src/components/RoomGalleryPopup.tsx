import React, { useState, useEffect } from 'react';
import { X, ChevronLeft, ChevronRight, Users, Bed, Maximize, Mountain, Sofa, Bath } from 'lucide-react';
import type { Room } from '../types/api';
import { useLocalizedText } from '../context/LanguageContext';

interface RoomGalleryPopupProps {
  isOpen: boolean;
  onClose: () => void;
  room: Room;
  ratePerNight: number;
}

const RoomGalleryPopup: React.FC<RoomGalleryPopupProps> = ({
  isOpen,
  onClose,
  room,
  ratePerNight: _ratePerNight
}) => {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [galleryImages, setGalleryImages] = useState<string[]>([]);
  const { t } = useLocalizedText();

  useEffect(() => {
    // Use gallery_images if available, otherwise fall back to images
    const images = room.gallery_images && room.gallery_images.length > 0
      ? room.gallery_images
      : room.images && room.images.length > 0
        ? room.images
        : [room.featured_image || '/placeholder-room.jpg'];

    setGalleryImages(images.filter(img => img)); // Remove null/undefined images
    setCurrentImageIndex(0);
  }, [room]);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (!isOpen) return;

      if (e.key === 'Escape') {
        onClose();
      } else if (e.key === 'ArrowLeft') {
        previousImage();
      } else if (e.key === 'ArrowRight') {
        nextImage();
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    // Prevent body scroll when popup is open
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'auto';
    }

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'auto';
    };
  }, [isOpen]);

  const nextImage = () => {
    setCurrentImageIndex((prev) =>
      prev === galleryImages.length - 1 ? 0 : prev + 1
    );
  };

  const previousImage = () => {
    setCurrentImageIndex((prev) =>
      prev === 0 ? galleryImages.length - 1 : prev - 1
    );
  };

  const goToImage = (index: number) => {
    setCurrentImageIndex(index);
  };

  if (!isOpen) return null;

  // Parse amenities from room data
  const getAmenityData = () => {
    return {
      main: room.amenities || [t('room.amenities.default.wifi'), t('room.amenities.default.tv'), t('room.amenities.default.ac')],
      room: room.room_amenities || [t('room.amenities.default.minibar'), t('room.amenities.default.safe'), t('room.amenities.default.desk')],
      bathroom: room.bathroom_amenities || [t('room.amenities.default.shower'), t('room.amenities.default.hairdryer'), t('room.amenities.default.toiletries')]
    };
  };

  const amenityData = getAmenityData();

  return (
    <div className="fixed inset-0 z-50 bg-black bg-opacity-80 backdrop-blur-sm">
      <div className="relative bg-white margin-auto max-w-7xl w-[95%] max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl"
        style={{ margin: '2.5vh auto' }}>

        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-6 z-10 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-80 transition-all duration-300"
        >
          <X size={24} />
        </button>

        <div className="flex h-[85vh]">
          {/* Gallery Section - 60% */}
          <div className="w-3/5 bg-gray-100 flex flex-col">
            {/* Main Image */}
            <div className="flex-1 relative overflow-hidden">
              <img
                src={galleryImages[currentImageIndex]}
                alt={`${room.name} - Hình ${currentImageIndex + 1}`}
                className="w-full h-full object-cover"
              />

              {/* Navigation Arrows */}
              {galleryImages.length > 1 && (
                <>
                  <button
                    onClick={previousImage}
                    className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-60 text-white rounded-full p-3 hover:bg-opacity-80 transition-all duration-300"
                  >
                    <ChevronLeft size={24} />
                  </button>
                  <button
                    onClick={nextImage}
                    className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-60 text-white rounded-full p-3 hover:bg-opacity-80 transition-all duration-300"
                  >
                    <ChevronRight size={24} />
                  </button>
                </>
              )}
            </div>

            {/* Thumbnail Gallery */}
            {galleryImages.length > 1 && (
              <div className="h-32 bg-white border-t border-gray-200 p-4">
                <div className="flex gap-3 overflow-x-auto">
                  {galleryImages.map((image, index) => (
                    <div
                      key={index}
                      onClick={() => goToImage(index)}
                      className={`relative flex-shrink-0 w-24 h-20 rounded-lg overflow-hidden cursor-pointer border-3 transition-all duration-300 ${index === currentImageIndex
                        ? 'border-blue-500 shadow-lg'
                        : 'border-transparent hover:border-gray-300'
                        }`}
                    >
                      <img
                        src={image}
                        alt={`Thumbnail ${index + 1}`}
                        className="w-full h-full object-cover"
                      />
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Info Section - 40% */}
          <div className="w-2/5 bg-white overflow-y-auto">
            <div className="p-8 text-left">
              {/* Header */}
              <div className="mb-6 pb-6 border-b-2 border-gray-100">
                <h2 className="text-2xl font-bold text-gray-800 mb-3">{room.name}</h2>
                {/* <div className="flex items-baseline gap-2">
                  <span className="text-2xl font-bold text-red-500">
                    {ratePerNight.toLocaleString('vi-VN')} VNĐ
                  </span>
                  <span className="text-gray-500">/đêm</span>
                </div> */}
              </div>
              <div className="space-y-6">
                <div>
                  <p className="text-gray-600 leading-relaxed">
                    {Array.isArray(amenityData.main) ? amenityData.main.join(', ') : amenityData.main}
                  </p>
                </div>
                {/* Basic Info */}
                <div className="space-y-4 mb-6">
                  <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <Bed className="text-blue-500" size={24} />
                    <div>
                      <div className="font-semibold text-gray-800">{t('room.bed_type')}</div>
                      <div className="text-gray-600">{room.bed_type || t('room.bed_type.default')}</div>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <Maximize className="text-blue-500" size={24} />
                    <div>
                      <div className="font-semibold text-gray-800">{t('room.area')}</div>
                      <div className="text-gray-600">{room.area || t('room.area.default')}</div>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <Users className="text-blue-500" size={24} />
                    <div>
                      <div className="font-semibold text-gray-800">{t('room.capacity_label')}</div>
                      <div className="text-gray-600">{t('room.capacity', { count: room.capacity })}</div>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <Mountain className="text-blue-500" size={24} />
                    <div>
                      <div className="font-semibold text-gray-800">{t('room.view')}</div>
                      <div className="text-gray-600">{room.view || t('room.view.default')}</div>
                    </div>
                  </div>
                </div>
              </div>
              {/* Description */}
              <div className="mb-6">
                <div
                  className="text-gray-600 leading-relaxed text-lg"
                  dangerouslySetInnerHTML={{
                    __html: room.description?.replace(/<!--[\s\S]*?-->/g, "").trim() || ''
                  }}
                />
              </div>

              {/* Amenities */}
              <div className="space-y-6">
                {/* Room Amenities */}
                <div>
                  <h4 className="flex items-center gap-2 text-lg font-semibold text-gray-800 mb-3">
                    <Sofa className="text-blue-500" size={20} />
                    {t('room.amenities.room')}
                  </h4>
                  <p className="text-gray-600 leading-relaxed">
                    {Array.isArray(amenityData.room) ? amenityData.room.join(', ') : amenityData.room}
                  </p>
                </div>

                {/* Bathroom Amenities */}
                <div>
                  <h4 className="flex items-center gap-2 text-lg font-semibold text-gray-800 mb-3">
                    <Bath className="text-blue-500" size={20} />
                    {t('room.amenities.bathroom')}
                  </h4>
                  <p className="text-gray-600 leading-relaxed">
                    {Array.isArray(amenityData.bathroom) ? amenityData.bathroom.join(', ') : amenityData.bathroom}
                  </p>
                </div>
              </div>

              {/* Action Buttons */}
              {/* <div className="flex gap-4 mt-8">
                <button className="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-300 flex items-center justify-center gap-2">
                  <Users size={20} />
                  Đặt phòng ngay
                </button>
                <button className="flex-1 border-2 border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                  <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                  </svg>
                  Liên hệ
                </button>
              </div> */}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RoomGalleryPopup;