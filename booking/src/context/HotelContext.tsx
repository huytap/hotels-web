import React, { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import type { HotelConfig, TenantContext as TenantContextType } from '../types/tenant';
import { tenantDetection } from '../services/tenantDetection';
import { apiService } from '../services/api';

const HotelContext = createContext<TenantContextType | undefined>(undefined);

interface HotelProviderProps {
  children: ReactNode;
}

export const HotelProvider: React.FC<HotelProviderProps> = ({ children }) => {
  const [hotel, setHotel] = useState<HotelConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    initializeHotel();
  }, []);

  const initializeHotel = async () => {
    try {
      setLoading(true);
      setError(null);
      // Get hotel data following the simplified flow: domain → WP API → wp_id + token
      const hotelData = await tenantDetection.getHotelData();
      if (!hotelData.wp_id || !hotelData.token) {
        throw new Error('Không thể lấy thông tin khách sạn từ WordPress');
      }
      // Create simplified hotel config from WordPress data
      const hotelConfig: HotelConfig = {
        id: hotelData.wp_id,
        name: hotelData.config.hotel_name || 'Hotel',
        domain: window.location.hostname + (window.location.port ? ':' + window.location.port : ''),
        apiToken: hotelData.token,
        apiBaseUrl: hotelData.config.api_base_url || 'http://localhost:8000/api',
        wpSiteId: hotelData.wp_id,
        settings: {
          currency: hotelData.config.currency || 'VND',
          timezone: hotelData.config.timezone || 'Asia/Ho_Chi_Minh',
          language: hotelData.config.language || 'vi',
          theme: {
            primaryColor: hotelData.config.theme?.primary_color || '#e11d48',
            logo: hotelData.config.theme?.logo || '',
            favicon: hotelData.config.theme?.favicon || '/favicon.ico',
          },
          contact: {
            phone: hotelData.config.contact?.phone || '',
            email: hotelData.config.contact?.email || '',
            address: hotelData.config.contact?.address || '',
          },
          policies: {
            checkIn: hotelData.config.policies?.check_in || '14:00',
            checkOut: hotelData.config.policies?.check_out || '12:00',
            cancellation: hotelData.config.policies?.cancellation || 'Hủy miễn phí trước 24 giờ',
          },
        },
      };

      // Configure API service
      apiService.setHotelConfig(hotelConfig);
      // Set hotel state
      setHotel(hotelConfig);
    } catch (err) {
      console.error('Failed to initialize hotel:', err);
      setError(err instanceof Error ? err.message : 'Lỗi không xác định');
    } finally {
      setLoading(false);
    }
  };

  const contextValue: TenantContextType = {
    hotel,
    loading,
    error,
    setHotel: (newHotel: HotelConfig) => {
      setHotel(newHotel);
      apiService.setHotelConfig(newHotel);
    },
  };

  return (
    <HotelContext.Provider value={contextValue}>
      {children}
    </HotelContext.Provider>
  );
};

export const useHotel = (): TenantContextType => {
  const context = useContext(HotelContext);
  if (context === undefined) {
    throw new Error('useHotel must be used within a HotelProvider');
  }
  return context;
};

// Loading component for hotel initialization
export const HotelLoader: React.FC<{ children: ReactNode }> = ({ children }) => {
  const { hotel, loading, error } = useHotel();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <h2 className="text-xl font-semibold text-gray-700 mb-2">
            Đang tải cấu hình khách sạn...
          </h2>
          <p className="text-gray-500">Vui lòng đợi trong giây lát</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="text-center max-w-md mx-auto p-6">
          <div className="text-6xl mb-4">⚠️</div>
          <h2 className="text-xl font-semibold text-red-600 mb-4">
            Lỗi cấu hình
          </h2>
          <p className="text-gray-600 mb-4">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200"
          >
            Thử lại
          </button>
        </div>
      </div>
    );
  }

  if (!hotel) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="text-center">
          <div className="text-6xl mb-4">🏨</div>
          <h2 className="text-xl font-semibold text-gray-700 mb-2">
            Không tìm thấy khách sạn
          </h2>
          <p className="text-gray-500">Vui lòng kiểm tra cấu hình domain</p>
        </div>
      </div>
    );
  }

  return <>{children}</>;
};