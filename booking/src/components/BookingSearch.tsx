import React, { useState } from 'react';
import { format, addDays } from 'date-fns';
import { Calendar, Users, Search } from 'lucide-react';
import type { BookingDetails } from '../types/api';

interface BookingSearchProps {
  onSearch: (searchParams: BookingDetails) => void;
  loading?: boolean;
}

const BookingSearch: React.FC<BookingSearchProps> = ({ onSearch, loading = false }) => {
  const [searchParams, setSearchParams] = useState<BookingDetails>({
    check_in: format(new Date(), 'yyyy-MM-dd'),
    check_out: format(addDays(new Date(), 1), 'yyyy-MM-dd'),
    adults: 2,
    children: 0,
    rooms: 1,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(searchParams);
  };

  const handleInputChange = (field: keyof BookingDetails, value: string | number) => {
    setSearchParams(prev => ({
      ...prev,
      [field]: value,
    }));
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-lg border">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
          {/* Check-in Date */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Calendar className="w-4 h-4 inline mr-1" />
              Ngày Nhận Phòng
            </label>
            <input
              type="date"
              value={searchParams.check_in}
              onChange={(e) => handleInputChange("check_in", e.target.value)}
              min={format(new Date(), "yyyy-MM-dd")}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          {/* Check-out Date */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Calendar className="w-4 h-4 inline mr-1" />
              Ngày Trả Phòng
            </label>
            <input
              type="date"
              value={searchParams.check_out}
              onChange={(e) => handleInputChange("check_out", e.target.value)}
              min={searchParams.check_in}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          {/* Adults */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Users className="w-4 h-4 inline mr-1" />
              Người Lớn
            </label>
            <select
              value={searchParams.adults}
              onChange={(e) =>
                handleInputChange("adults", parseInt(e.target.value))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {[1, 2, 3, 4, 5, 6].map((num) => (
                <option key={num} value={num}>
                  {num}
                </option>
              ))}
            </select>
          </div>

          {/* Children */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Trẻ Em
            </label>
            <select
              value={searchParams.children}
              onChange={(e) =>
                handleInputChange("children", parseInt(e.target.value))
              }
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {[0, 1, 2, 3, 4].map((num) => (
                <option key={num} value={num}>
                  {num}
                </option>
              ))}
            </select>
          </div>

          {/* Submit button */}
          <div>
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
            >
              {loading ? (
                <div className="flex items-center justify-center gap-2">
                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                  Đang Tìm Kiếm...
                </div>
              ) : (
                <div className="flex items-center justify-center gap-2">
                  <Search className="w-4 h-4" />
                  Tìm Phòng Trống
                </div>
              )}
            </button>
          </div>
        </div>
      </form>
    </div>

  );
};

export default BookingSearch;