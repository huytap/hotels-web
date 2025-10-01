import React from 'react';
import { useLocalizedText } from '../context/LanguageContext';

interface ChildrenAgeSelectorProps {
  childrenCount: number;
  childrenAges: number[];
  onChildrenAgesChange: (ages: number[]) => void;
}

const ChildrenAgeSelector: React.FC<ChildrenAgeSelectorProps> = ({
  childrenCount,
  childrenAges,
  onChildrenAgesChange,
}) => {
  const { t } = useLocalizedText();

  // Create age options from 0 to 17
  const ageOptions = Array.from({ length: 18 }, (_, i) => i);

  const handleAgeChange = (index: number, age: number) => {
    const newAges = [...childrenAges];
    newAges[index] = age;
    onChildrenAgesChange(newAges);
  };

  // Don't render if no children
  if (childrenCount === 0) {
    return null;
  }

  return (
    <div className="mt-4 space-y-3 text-left">
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-8 gap-3">
        {Array.from({ length: childrenCount }, (_, index) => (
          <div key={index}>
            <label className="block text-xs text-gray-600 mb-1">
              {t('search.child_age_placeholder').replace('{index}', (index + 1).toString())}
            </label>
            <select
              value={childrenAges[index] || ''}
              onChange={(e) => handleAgeChange(index, parseInt(e.target.value))}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              required
            >
              <option value="">{t('search.child_age_placeholder').replace('{index}', (index + 1).toString())}</option>
              {ageOptions.map((age) => (
                <option key={age} value={age}>
                  {age} {age === 1 ? 'tuổi' : 'tuổi'}
                </option>
              ))}
            </select>
          </div>
        ))}
      </div>
      {childrenAges.length < childrenCount && (
        <p className="text-sm text-red-600 mt-2">
          {t('search.child_age_required')}
        </p>
      )}
    </div>
  );
};

export default ChildrenAgeSelector;