import React, { useEffect, useState } from 'react';
import { Navigate } from 'react-router-dom';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredData: string[]; // Array of localStorage keys that must exist
  redirectTo: string; // Where to redirect if validation fails
  customValidation?: () => boolean; // Optional custom validation function
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({
  children,
  requiredData,
  redirectTo,
  customValidation
}) => {
  const [isValidating, setIsValidating] = useState(true);
  const [isValid, setIsValid] = useState(false);

  useEffect(() => {
    const validateAccess = () => {
      try {
        // Check if all required data exists in localStorage
        for (const key of requiredData) {
          const data = localStorage.getItem(key);
          if (!data) {
            setIsValid(false);
            setIsValidating(false);
            return;
          }

          // For arrays, check if they have content
          if (key === 'selected_rooms') {
            const parsedData = JSON.parse(data);
            if (!Array.isArray(parsedData) || parsedData.length === 0) {
              setIsValid(false);
              setIsValidating(false);
              return;
            }
          }
        }

        // Run custom validation if provided
        if (customValidation && !customValidation()) {
          setIsValid(false);
          setIsValidating(false);
          return;
        }

        setIsValid(true);
        setIsValidating(false);
      } catch (error) {
        console.error('Error validating protected route:', error);
        setIsValid(false);
        setIsValidating(false);
      }
    };

    validateAccess();
  }, [requiredData, customValidation]);

  if (isValidating) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <div className="text-4xl mb-4">🔄</div>
          <h3 className="text-lg font-semibold text-gray-600">
            Đang kiểm tra quyền truy cập...
          </h3>
        </div>
      </div>
    );
  }

  if (!isValid) {
    return <Navigate to={redirectTo} replace />;
  }

  return <>{children}</>;
};

export default ProtectedRoute;