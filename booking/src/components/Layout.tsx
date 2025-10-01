import React from 'react';
import { useLocation, Link } from 'react-router-dom';
import { useHotel } from '../context/HotelContext';
import { useLanguage, useLocalizedText } from '../context/LanguageContext';

interface LayoutProps {
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const location = useLocation();
  const { hotel } = useHotel();
  const {
    currentLanguage,
    availableLanguages,
    hotelConfig,
    themeData,
    languageInfo,
    loading,
    changeLanguage
  } = useLanguage();
  const { t } = useLocalizedText();

  // Get current step from URL
  const getCurrentStep = () => {
    const path = location.pathname;
    if (path === '/search' || path === '/') return 'search-rooms';
    if (path === '/rooms') return 'rooms'; // This page is now deprecated but keeping for compatibility
    if (path === '/guest') return 'guest';
    if (path === '/confirmation') return 'confirmation';
    return 'search-rooms';
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
              {/* Track Booking Link */}
              <Link
                to="/track"
                className="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                {t('track_booking', 'Track Booking')}
              </Link>

              {/* Language Selector */}
              {availableLanguages && availableLanguages.length > 1 && (
                <div className="flex items-center gap-2">
                  <span className="text-sm text-gray-600">{t('booking.language')}:</span>
                  <select
                    value={currentLanguage}
                    onChange={(e) => changeLanguage(e.target.value)}
                    className="text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    disabled={loading}
                  >
                    {availableLanguages.map((lang: string) => (
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
                    <p className="text-sm text-gray-600">{t('booking.hotline')}</p>
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
                {t('booking.step1')}
              </span>
              <span className="text-gray-300">→</span>
              <span className={`px-2 py-1 rounded ${currentStep === 'guest' ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}`}>
                {t('booking.step2')}
              </span>
              <span className="text-gray-300">→</span>
              <span className={`px-2 py-1 rounded ${currentStep === 'confirmation' ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}`}>
                {t('booking.step3')}
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