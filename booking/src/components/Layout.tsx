import React, { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { tenantDetection } from '../services/tenantDetection';
import { useHotel } from '../context/HotelContext';

interface LayoutProps {
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const location = useLocation();
  const { hotel } = useHotel();
  const [languageInfo, setLanguageInfo] = useState<any>(null);
  const [currentLanguage, setCurrentLanguage] = useState<string>('vi');
  const [hotelConfig, setHotelConfig] = useState<any>(null);
  const [themeData, setThemeData] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  // Get current step from URL
  const getCurrentStep = () => {
    const path = location.pathname;
    if (path === '/search' || path === '/') return 'search-rooms';
    if (path === '/rooms') return 'rooms'; // This page is now deprecated but keeping for compatibility
    if (path === '/guest') return 'guest';
    if (path === '/confirmation') return 'confirmation';
    return 'search-rooms';
  };

  // Load cached hotel information on component mount
  useEffect(() => {
    const loadCachedHotelData = () => {
      try {
        // Try to load from cache first
        const cachedConfig = tenantDetection.getCachedHotelConfig(currentLanguage);
        const cachedTheme = tenantDetection.getCachedTheme(currentLanguage);

        if (cachedConfig) {
          setHotelConfig(cachedConfig);
        }

        if (cachedTheme) {
          setThemeData(cachedTheme);
        }

        // If no cache, try to load fresh data
        if (!cachedConfig || !cachedTheme) {
          loadFreshHotelData();
        }
      } catch (error) {
        console.error('Error loading cached hotel data:', error);
      }
    };

    const loadFreshHotelData = async () => {
      try {
        const hotelData = await tenantDetection.getHotelData(currentLanguage);

        if (hotelData.config) {
          setHotelConfig(hotelData.config);
          if (hotelData.config.theme) {
            setThemeData(hotelData.config.theme);
          }
        }

        if (hotelData.language_info) {
          setLanguageInfo(hotelData.language_info);
        }
      } catch (error) {
        console.error('Error loading fresh hotel data:', error);
      }
    };

    loadCachedHotelData();
  }, [currentLanguage]);

  const handleLanguageChange = async (newLanguage: string) => {
    if (newLanguage === currentLanguage) return;

    try {
      setLoading(true);
      // Get hotel data with the new language
      const hotelData = await tenantDetection.getHotelData(newLanguage);

      if (hotelData.language_info) {
        setLanguageInfo(hotelData.language_info);
        setCurrentLanguage(hotelData.language_info.current_language || newLanguage);
      }

      // Update hotel configuration and theme data for new language
      if (hotelData.config) {
        setHotelConfig(hotelData.config);

        if (hotelData.config.theme) {
          setThemeData(hotelData.config.theme);
        }
      }
    } catch (error) {
      console.error('Error switching language:', error);
    } finally {
      setLoading(false);
    }
  };

  const currentStep = getCurrentStep();

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              {/* Logo from API or fallback */}
              {(themeData?.logo || hotel?.settings.theme.logo) && (
                <img
                  src={themeData?.logo || hotel?.settings.theme.logo}
                  alt={hotelConfig?.hotel_name || hotel?.name || 'Hotel Logo'}
                  className="h-10 w-auto"
                  onError={(e) => {
                    (e.target as HTMLImageElement).style.display = 'none';
                  }}
                />
              )}
              <div>
                <h1 className="text-2xl font-bold text-left" style={{ color: themeData?.primary_color || hotel?.settings.theme.primaryColor || '#3b82f6' }}>
                  {hotelConfig?.hotel_name || hotel?.name || 'Hệ Thống Đặt Phòng Khách Sạn'}
                </h1>
                {(hotelConfig?.address || hotel?.settings.contact.address) && (
                  <p className="text-sm text-gray-600">{hotelConfig?.address || hotel?.settings.contact.address}</p>
                )}
              </div>
            </div>

            <div className="flex items-center gap-4">
              {/* Language Selector */}
              {languageInfo && languageInfo.available_languages && languageInfo.available_languages.length > 1 && (
                <div className="flex items-center gap-2">
                  <span className="text-sm text-gray-600">Ngôn ngữ:</span>
                  <select
                    value={currentLanguage}
                    onChange={(e) => handleLanguageChange(e.target.value)}
                    className="text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    disabled={loading}
                  >
                    {languageInfo.available_languages.map((lang: string) => (
                      <option key={lang} value={lang}>
                        {lang.toUpperCase()}
                      </option>
                    ))}
                  </select>
                </div>
              )}
              <div>
                {/* Phone Contact */}
                {(hotelConfig?.contact?.phone || hotel?.settings.contact.phone) && (
                  <div className="text-right">
                    <p className="text-sm text-gray-600">Hotline</p>
                    <a
                      href={`tel:${hotelConfig?.contact?.phone || hotel?.settings.contact.phone}`}
                      className="text-lg font-semibold text-blue-600 hover:text-blue-800"
                    >
                      {hotelConfig?.contact?.phone || hotel?.settings.contact.phone}
                    </a>
                  </div>
                )}{/* Phone Contact */}
                {(hotelConfig?.contact?.email || hotel?.settings.contact.email) && (
                  <div className="text-right">
                    <a
                      href={`tel:${hotelConfig?.contact?.email || hotel?.settings.contact.email}`}
                      className="text-sm text-blue-600 hover:text-blue-800"
                    >
                      {hotelConfig?.contact?.email || hotel?.settings.contact.email}
                    </a>
                  </div>
                )}
              </div>
            </div>
          </div>
          <nav className="mt-2">
            <div className="flex items-center space-x-2 text-sm">
              <span className={`px-2 py-1 rounded ${currentStep === 'search-rooms' ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}`}>
                1. Tìm & Chọn Phòng
              </span>
              <span className="text-gray-300">→</span>
              <span className={`px-2 py-1 rounded ${currentStep === 'guest' ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}`}>
                2. Thông Tin
              </span>
              <span className="text-gray-300">→</span>
              <span className={`px-2 py-1 rounded ${currentStep === 'confirmation' ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}`}>
                3. Xác Nhận
              </span>
            </div>
          </nav>
        </div>
      </header>

      <main className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 py-6">
          {children}
        </div>
      </main>
    </div>
  );
};

export default Layout;