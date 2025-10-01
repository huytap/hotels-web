import React, { useState } from 'react';
import { User, Mail, Phone, Globe, MessageSquare, CreditCard } from 'lucide-react';
import type { Guest } from '../types/api';
import countriesData from '../data/countries.json';
import { useLocalizedText, useLanguage } from '../context/LanguageContext';
interface GuestFormProps {
  onSubmit: (guestData: Guest & { specialRequests: string }) => void;
  loading?: boolean;
}

const GuestForm: React.FC<GuestFormProps> = ({ onSubmit, loading = false }) => {
  const { t } = useLocalizedText();
  const { hotelConfig, currentLanguage } = useLanguage();

  // Format check-in/out times from hotel config - currently using hotel policy instead
  // const getCheckInOutInfo = () => {
  //   const defaultCheckIn = '14:00';
  //   const defaultCheckOut = '12:00';

  //   if (hotelConfig) {
  //     const checkIn = hotelConfig.check_in_time || defaultCheckIn;
  //     const checkOut = hotelConfig.check_out_time || defaultCheckOut;
  //     return t('guest.terms.dynamic_checkin_checkout', { checkIn, checkOut });
  //   }

  //   return t('guest.terms.dynamic_checkin_checkout', { checkIn: defaultCheckIn, checkOut: defaultCheckOut });
  // };

  // Get hotel policy from config
  const getHotelPolicy = () => {
    if (hotelConfig && hotelConfig.policy) {
      // If policy is an object with language keys
      if (typeof hotelConfig.policy === 'object' && hotelConfig.policy !== null) {
        return hotelConfig.policy[currentLanguage] || hotelConfig.policy.vi || hotelConfig.policy.en || '';
      }
      // If policy is a string
      if (typeof hotelConfig.policy === 'string') {
        return hotelConfig.policy;
      }
    }

    // Default fallback policy
    return `
      • ${t('guest.terms.dynamic_checkin_checkout', { checkIn: '14:00', checkOut: '12:00' })}
      • ${t('guest.terms.free_cancellation')}
      • ${t('guest.terms.prices_include')}
      • ${t('guest.terms.id_required')}
    `;
  };

  const [guestData, setGuestData] = useState<Guest & { specialRequests: string }>({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    nationality: 'VN',
    specialRequests: ''
  });

  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  const validateForm = (): boolean => {
    const newErrors: { [key: string]: string } = {};

    if (!guestData.first_name.trim()) {
      newErrors.first_name = t('validation.first_name_required');
    }

    if (!guestData.last_name.trim()) {
      newErrors.last_name = t('validation.last_name_required');
    }

    if (!guestData.email.trim()) {
      newErrors.email = t('validation.email_required');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(guestData.email)) {
      newErrors.email = t('validation.email_invalid');
    }

    if (!guestData.phone.trim()) {
      newErrors.phone = t('validation.phone_required');
    } else if (!/^[+]?[\d\s-()]{8,}$/.test(guestData.phone)) {
      newErrors.phone = t('validation.phone_invalid');
    }

    if (!guestData.nationality) {
      newErrors.nationality = t('validation.nationality_required');
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      onSubmit(guestData);
    }
  };

  const handleInputChange = (field: keyof typeof guestData, value: string) => {
    setGuestData(prev => ({
      ...prev,
      [field]: value
    }));

    // Clear error for this field
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };
  interface CountryOption {
    code: string; // Mã ISO của quốc gia (ví dụ: 'VN')
    name: string; // Tên quốc gia (ví dụ: 'Vietnam')
  }
  const countries: CountryOption[] = countriesData as CountryOption[];
  return (
    <div className="bg-white rounded-lg shadow-lg border p-6">
      <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <User className="w-6 h-6" />
        {t('guest.form_title')}
      </h2>

      <form onSubmit={handleSubmit} className="space-y-6 text-left">
        {/* Name Fields */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('guest.first_name')} <span className="text-red-500">{t('guest.required')}</span>
            </label>
            <input
              type="text"
              value={guestData.first_name}
              onChange={(e) => handleInputChange('first_name', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.first_name ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder={t('guest.first_name.placeholder')}
            />
            {errors.first_name && (
              <p className="text-red-500 text-xs mt-1">{errors.first_name}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('guest.last_name')} <span className="text-red-500">{t('guest.required')}</span>
            </label>
            <input
              type="text"
              value={guestData.last_name}
              onChange={(e) => handleInputChange('last_name', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.last_name ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder={t('guest.last_name.placeholder')}
            />
            {errors.last_name && (
              <p className="text-red-500 text-xs mt-1">{errors.last_name}</p>
            )}
          </div>
        </div>

        {/* Contact Fields */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Mail className="w-4 h-4 inline mr-1" />
              {t('guest.email')} <span className="text-red-500">{t('guest.required')}</span>
            </label>
            <input
              type="email"
              value={guestData.email}
              onChange={(e) => handleInputChange('email', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.email ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder={t('guest.email.placeholder')}
            />
            {errors.email && (
              <p className="text-red-500 text-xs mt-1">{errors.email}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Phone className="w-4 h-4 inline mr-1" />
              {t('guest.phone')} <span className="text-red-500">{t('guest.required')}</span>
            </label>
            <input
              type="tel"
              value={guestData.phone}
              onChange={(e) => handleInputChange('phone', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.phone ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder={t('guest.phone.placeholder')}
            />
            {errors.phone && (
              <p className="text-red-500 text-xs mt-1">{errors.phone}</p>
            )}
          </div>
        </div>

        {/* Nationality */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            <Globe className="w-4 h-4 inline mr-1" />
            {t('guest.nationality')} <span className="text-red-500">{t('guest.required')}</span>
          </label>
          <select
            value={guestData.nationality}
            onChange={(e) => handleInputChange('nationality', e.target.value)}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.nationality ? 'border-red-500' : 'border-gray-300'
              }`}
          >
            <option value="">{t('guest.nationality.placeholder')}</option>
            {countries.map((country) => (
              <option key={country.code} value={country.code}>
                {country.name}
              </option>
            ))}
          </select>
          {errors.nationality && (
            <p className="text-red-500 text-xs mt-1">{errors.nationality}</p>
          )}
        </div>

        {/* Special Requests */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            <MessageSquare className="w-4 h-4 inline mr-1" />
            {t('guest.special_requests')}
          </label>
          <textarea
            value={guestData.specialRequests}
            onChange={(e) => handleInputChange('specialRequests', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            rows={3}
            placeholder={t('guest.special_requests.placeholder')}
          />
          <p className="text-xs text-gray-500 mt-1">
            {t('guest.special_requests.note')}
          </p>
        </div>

        {/* Terms and Conditions */}
        <div className="bg-gray-50 p-4 rounded-lg">
          <h4 className="font-medium text-gray-800 mb-2">{t('guest.terms.title')}</h4>
          <div
            className="text-sm text-gray-600 whitespace-pre-line"
            dangerouslySetInnerHTML={{
              __html: getHotelPolicy().replace(/\n/g, '<br/>')
            }}
          />
        </div>

        {/* Submit Button */}
        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
        >
          {loading ? (
            <div className="flex items-center justify-center gap-2">
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {t('guest.booking_loading')}
            </div>
          ) : (
            <div className="flex items-center justify-center gap-2">
              <CreditCard className="w-4 h-4" />
              {t('guest.confirm_booking')}
            </div>
          )}
        </button>
      </form>
    </div>
  );
};

export default GuestForm;