import React, { useState } from 'react';
import { User, Mail, Phone, Globe, MessageSquare, CreditCard } from 'lucide-react';
import type { Guest } from '../types/api';
import countriesData from '../data/countries.json';
interface GuestFormProps {
  onSubmit: (guestData: Guest & { specialRequests: string }) => void;
  loading?: boolean;
}

const GuestForm: React.FC<GuestFormProps> = ({ onSubmit, loading = false }) => {
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
      newErrors.first_name = 'Vui lòng nhập tên';
    }

    if (!guestData.last_name.trim()) {
      newErrors.last_name = 'Vui lòng nhập họ';
    }

    if (!guestData.email.trim()) {
      newErrors.email = 'Vui lòng nhập email';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(guestData.email)) {
      newErrors.email = 'Email không hợp lệ';
    }

    if (!guestData.phone.trim()) {
      newErrors.phone = 'Vui lòng nhập số điện thoại';
    } else if (!/^[+]?[\d\s-()]{8,}$/.test(guestData.phone)) {
      newErrors.phone = 'Số điện thoại không hợp lệ';
    }

    if (!guestData.nationality) {
      newErrors.nationality = 'Vui lòng chọn quốc tịch';
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
        Thông Tin Khách Hàng
      </h2>

      <form onSubmit={handleSubmit} className="space-y-6 text-left">
        {/* Name Fields */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Tên <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={guestData.first_name}
              onChange={(e) => handleInputChange('first_name', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.first_name ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder="Nhập tên của bạn"
            />
            {errors.first_name && (
              <p className="text-red-500 text-xs mt-1">{errors.first_name}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Họ <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={guestData.last_name}
              onChange={(e) => handleInputChange('last_name', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.last_name ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder="Nhập họ của bạn"
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
              Email <span className="text-red-500">*</span>
            </label>
            <input
              type="email"
              value={guestData.email}
              onChange={(e) => handleInputChange('email', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.email ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder="example@email.com"
            />
            {errors.email && (
              <p className="text-red-500 text-xs mt-1">{errors.email}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Phone className="w-4 h-4 inline mr-1" />
              Số Điện Thoại <span className="text-red-500">*</span>
            </label>
            <input
              type="tel"
              value={guestData.phone}
              onChange={(e) => handleInputChange('phone', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.phone ? 'border-red-500' : 'border-gray-300'
                }`}
              placeholder="+84 123 456 789"
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
            Quốc Tịch <span className="text-red-500">*</span>
          </label>
          <select
            value={guestData.nationality}
            onChange={(e) => handleInputChange('nationality', e.target.value)}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.nationality ? 'border-red-500' : 'border-gray-300'
              }`}
          >
            <option value="">Chọn quốc tịch</option>
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
            Yêu Cầu Đặc Biệt (Tùy Chọn)
          </label>
          <textarea
            value={guestData.specialRequests}
            onChange={(e) => handleInputChange('specialRequests', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            rows={3}
            placeholder="Ví dụ: Phòng view biển, giường đôi, không hút thuốc..."
          />
          <p className="text-xs text-gray-500 mt-1">
            Khách sạn sẽ cố gắng đáp ứng yêu cầu của bạn tùy theo tình hình thực tế.
          </p>
        </div>

        {/* Terms and Conditions */}
        <div className="bg-gray-50 p-4 rounded-lg">
          <h4 className="font-medium text-gray-800 mb-2">Điều Khoản Đặt Phòng:</h4>
          <ul className="text-sm text-gray-600 space-y-1">
            <li>• Check-in: 14:00 | Check-out: 12:00</li>
            <li>• Hủy miễn phí trước 24 giờ</li>
            <li>• Giá đã bao gồm thuế và phí dịch vụ</li>
            <li>• Vui lòng mang theo CMND/Passport khi check-in</li>
          </ul>
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
              Đang Đặt Phòng...
            </div>
          ) : (
            <div className="flex items-center justify-center gap-2">
              <CreditCard className="w-4 h-4" />
              Xác Nhận Đặt Phòng
            </div>
          )}
        </button>
      </form>
    </div>
  );
};

export default GuestForm;