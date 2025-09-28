import React, { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { tenantDetection } from '../services/tenantDetection';

interface LanguageContextType {
  currentLanguage: string;
  availableLanguages: string[];
  hotelConfig: any;
  themeData: any;
  languageInfo: any;
  loading: boolean;
  changeLanguage: (language: string) => Promise<void>;
  getLocalizedText: (key: string, fallback?: string) => string;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

interface LanguageProviderProps {
  children: ReactNode;
}

// Static text translations
const staticTexts = {
  vi: {
    'booking.step1': '1. Tìm & Chọn Phòng',
    'booking.step2': '2. Thông Tin',
    'booking.step3': '3. Xác Nhận',
    'booking.language': 'Ngôn ngữ',
    'booking.hotline': 'Hotline',
    'room.capacity': 'Tối đa {count} người',
    'room.remaining': 'Còn {count} phòng',
    'room.view_gallery': 'Xem gallery',
    'room.more_images': '+{count} ảnh khác',
    'room.amenities.main': 'Tiện ích chính',
    'room.amenities.room': 'Tiện ích phòng',
    'room.amenities.bathroom': 'Tiện ích phòng tắm',
    'room.book_now': 'Đặt phòng ngay',
    'room.contact': 'Liên hệ',
    'room.bed_type': 'Loại giường',
    'room.area': 'Diện tích',
    'room.capacity_label': 'Sức chứa',
    'room.view': 'View',
    'room.price_per_night': '/đêm'
  },
  en: {
    'booking.step1': '1. Search & Select Rooms',
    'booking.step2': '2. Guest Info',
    'booking.step3': '3. Confirmation',
    'booking.language': 'Language',
    'booking.hotline': 'Hotline',
    'room.capacity': 'Up to {count} guests',
    'room.remaining': '{count} rooms available',
    'room.view_gallery': 'View gallery',
    'room.more_images': '+{count} more photos',
    'room.amenities.main': 'Main Amenities',
    'room.amenities.room': 'Room Amenities',
    'room.amenities.bathroom': 'Bathroom Amenities',
    'room.book_now': 'Book Now',
    'room.contact': 'Contact',
    'room.bed_type': 'Bed Type',
    'room.area': 'Area',
    'room.capacity_label': 'Capacity',
    'room.view': 'View',
    'room.price_per_night': '/night'
  },
  ko: {
    'booking.step1': '1. 객실 검색 및 선택',
    'booking.step2': '2. 고객 정보',
    'booking.step3': '3. 확인',
    'booking.language': '언어',
    'booking.hotline': '핫라인',
    'room.capacity': '최대 {count}명',
    'room.remaining': '{count}개 객실 이용 가능',
    'room.view_gallery': '갤러리 보기',
    'room.more_images': '+{count}장 더 보기',
    'room.amenities.main': '주요 편의시설',
    'room.amenities.room': '객실 편의시설',
    'room.amenities.bathroom': '욕실 편의시설',
    'room.book_now': '지금 예약',
    'room.contact': '문의하기',
    'room.bed_type': '침대 유형',
    'room.area': '면적',
    'room.capacity_label': '수용 인원',
    'room.view': '전망',
    'room.price_per_night': '/박'
  },
  ja: {
    'booking.step1': '1. 部屋を検索・選択',
    'booking.step2': '2. ゲスト情報',
    'booking.step3': '3. 確認',
    'booking.language': '言語',
    'booking.hotline': 'ホットライン',
    'room.capacity': '最大{count}名',
    'room.remaining': '{count}室利用可能',
    'room.view_gallery': 'ギャラリーを見る',
    'room.more_images': '+{count}枚の写真',
    'room.amenities.main': 'メインアメニティ',
    'room.amenities.room': 'ルームアメニティ',
    'room.amenities.bathroom': 'バスルームアメニティ',
    'room.book_now': '今すぐ予約',
    'room.contact': 'お問い合わせ',
    'room.bed_type': 'ベッドタイプ',
    'room.area': '面積',
    'room.capacity_label': '定員',
    'room.view': 'ビュー',
    'room.price_per_night': '/泊'
  }
};

export const LanguageProvider: React.FC<LanguageProviderProps> = ({ children }) => {
  const [currentLanguage, setCurrentLanguage] = useState<string>('vi');
  const [availableLanguages, setAvailableLanguages] = useState<string[]>(['vi']);
  const [hotelConfig, setHotelConfig] = useState<any>(null);
  const [themeData, setThemeData] = useState<any>(null);
  const [languageInfo, setLanguageInfo] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  // Initialize language on mount
  useEffect(() => {
    initializeLanguage();
  }, []);

  const initializeLanguage = async () => {
    try {
      setLoading(true);
      console.log('🌐 Initializing language context...');

      // Try to load cached data first
      const cachedConfig = tenantDetection.getCachedHotelConfig(currentLanguage);
      const cachedTheme = tenantDetection.getCachedTheme(currentLanguage);

      if (cachedConfig) {
        setHotelConfig(cachedConfig);
        console.log('🏨 Loaded cached hotel config:', cachedConfig);
      }

      if (cachedTheme) {
        setThemeData(cachedTheme);
        console.log('🎨 Loaded cached theme:', cachedTheme);
      }

      // Load fresh data to get language info
      const hotelData = await tenantDetection.getHotelData(currentLanguage);

      if (hotelData.config) {
        setHotelConfig(hotelData.config);
        if (hotelData.config.theme) {
          setThemeData(hotelData.config.theme);
        }
      }

      if (hotelData.language_info) {
        setLanguageInfo(hotelData.language_info);
        setAvailableLanguages(hotelData.language_info.available_languages || ['vi']);
        setCurrentLanguage(hotelData.language_info.current_language || 'vi');
      }

      console.log('🌐 Language context initialized successfully');
    } catch (error) {
      console.error('❌ Error initializing language context:', error);
    } finally {
      setLoading(false);
    }
  };

  const changeLanguage = async (newLanguage: string) => {
    if (newLanguage === currentLanguage || loading) return;

    try {
      setLoading(true);
      console.log(`🌐 Changing language from ${currentLanguage} to ${newLanguage}`);

      // Get hotel data with the new language
      const hotelData = await tenantDetection.getHotelData(newLanguage);

      if (hotelData.language_info) {
        setLanguageInfo(hotelData.language_info);
        setCurrentLanguage(hotelData.language_info.current_language || newLanguage);
        console.log('✅ Language switched successfully:', hotelData.language_info);
      }

      // Update hotel configuration and theme data for new language
      if (hotelData.config) {
        setHotelConfig(hotelData.config);

        if (hotelData.config.theme) {
          setThemeData(hotelData.config.theme);
          console.log('🎨 Theme updated for new language:', hotelData.config.theme);
        }
      }

      // Trigger a re-render for all components
      console.log('🔄 Language context updated, triggering re-render...');

    } catch (error) {
      console.error('❌ Error switching language:', error);
      // Revert to previous language on error
      setCurrentLanguage(currentLanguage);
    } finally {
      setLoading(false);
    }
  };

  const getLocalizedText = (key: string, fallback?: string) => {
    const texts = staticTexts[currentLanguage as keyof typeof staticTexts] || staticTexts.vi;
    const text = texts[key as keyof typeof texts] || fallback || key;

    return text;
  };

  const contextValue: LanguageContextType = {
    currentLanguage,
    availableLanguages,
    hotelConfig,
    themeData,
    languageInfo,
    loading,
    changeLanguage,
    getLocalizedText
  };

  return (
    <LanguageContext.Provider value={contextValue}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = (): LanguageContextType => {
  const context = useContext(LanguageContext);
  if (context === undefined) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

// Hook để format text với placeholder
export const useLocalizedText = () => {
  const { getLocalizedText } = useLanguage();

  const t = (key: string, params?: Record<string, any>, fallback?: string) => {
    let text = getLocalizedText(key, fallback);

    // Replace placeholders like {count} with actual values
    if (params) {
      Object.entries(params).forEach(([paramKey, value]) => {
        text = text.replace(new RegExp(`\\{${paramKey}\\}`, 'g'), String(value));
      });
    }

    return text;
  };

  return { t };
};