import React, { useState } from 'react';
import { format, addDays } from 'date-fns';
import { Calendar, Users, Search } from 'lucide-react';
import type { BookingDetails } from '../types/api';
import { useLocalizedText } from '../context/LanguageContext';
import ChildrenAgeSelector from './ChildrenAgeSelector';

interface BookingSearchProps {
  onSearch: (searchParams: BookingDetails) => void;
  loading?: boolean;
  initialValues?: BookingDetails;
}

const BookingSearch: React.FC<BookingSearchProps> = ({ onSearch, loading = false, initialValues }) => {
  const { t } = useLocalizedText();
  const [searchParams, setSearchParams] = useState<BookingDetails>(
    initialValues || {
      check_in: format(new Date(), 'yyyy-MM-dd'),
      check_out: format(addDays(new Date(), 1), 'yyyy-MM-dd'),
      adults: 2,
      children: 0,
      children_ages: [],
      rooms: 1,
    }
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Validate children ages if children > 0
    if (searchParams.children > 0) {
      if (!searchParams.children_ages || searchParams.children_ages.length !== searchParams.children) {
        alert(t('search.child_age_required'));
        return;
      }

      // Check if all ages are selected
      const hasEmptyAges = searchParams.children_ages.some(age => age === undefined || age === null);
      if (hasEmptyAges) {
        alert(t('search.child_age_required'));
        return;
      }
    }

    onSearch(searchParams);
  };

  const handleInputChange = (field: keyof BookingDetails, value: string | number) => {
    setSearchParams(prev => {
      const updated = {
        ...prev,
        [field]: value,
      };

      // Auto update check-out date when check-in changes
      if (field === 'check_in' && typeof value === 'string') {
        const checkInDate = new Date(value);
        const currentCheckOutDate = new Date(prev.check_out);

        // If check-out is before or same as new check-in, set check-out to check-in + 1 day
        if (currentCheckOutDate <= checkInDate) {
          updated.check_out = format(addDays(checkInDate, 1), 'yyyy-MM-dd');
        }
      }

      // Reset children ages when children count changes
      if (field === 'children') {
        updated.children_ages = [];
      }

      return updated;
    });
  };

  const handleChildrenAgesChange = (ages: number[]) => {
    setSearchParams(prev => ({
      ...prev,
      children_ages: ages,
    }));
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-lg border">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end text-left">
          {/* Check-in Date */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Calendar className="w-4 h-4 inline mr-1" />
              {t('search.checkin_date')}
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
              {t('search.checkout_date')}
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
              {t('search.adults')}
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
              {t('search.children')}
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
                  {t('search.searching')}
                </div>
              ) : (
                <div className="flex items-center justify-center gap-2">
                  <Search className="w-4 h-4" />
                  {t('search.search_rooms')}
                </div>
              )}
            </button>
          </div>
        </div>

        {/* Children Age Selector */}
        <ChildrenAgeSelector
          childrenCount={searchParams.children}
          childrenAges={searchParams.children_ages || []}
          onChildrenAgesChange={handleChildrenAgesChange}
        />
      </form>
    </div>

  );
};

export default BookingSearch;