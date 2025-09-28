import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { HotelProvider, HotelLoader } from './context/HotelContext';
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import SearchPage from './pages/SearchPage';
import RoomsPage from './pages/RoomsPage';
import GuestPage from './pages/GuestPage';
import ConfirmationPage from './pages/ConfirmationPage';
import './App.css';

const BookingApp: React.FC = () => {
  return (
    <Router>
      <Layout>
        <Routes>
          {/* Default route redirects to search */}
          <Route path="/" element={<Navigate to="/search" replace />} />

          {/* Search page - no protection needed */}
          <Route path="/search" element={<SearchPage />} />

          {/* Rooms page - requires booking details and available rooms */}
          <Route
            path="/rooms"
            element={
              <ProtectedRoute
                requiredData={['booking_details', 'available_rooms']}
                redirectTo="/search"
              >
                <RoomsPage />
              </ProtectedRoute>
            }
          />

          {/* Guest page - requires booking details and selected rooms */}
          <Route
            path="/guest"
            element={
              <ProtectedRoute
                requiredData={['booking_details', 'available_rooms', 'selected_rooms']}
                redirectTo="/rooms"
              >
                <GuestPage />
              </ProtectedRoute>
            }
          />

          {/* Confirmation page - requires booking result */}
          <Route
            path="/confirmation"
            element={
              <ProtectedRoute
                requiredData={['booking_result']}
                redirectTo="/search"
              >
                <ConfirmationPage />
              </ProtectedRoute>
            }
          />

          {/* Catch all route redirects to search */}
          <Route path="*" element={<Navigate to="/search" replace />} />
        </Routes>
      </Layout>
    </Router>
  );
};

function App() {
  return (
    <HotelProvider>
      <HotelLoader>
        <BookingApp />
      </HotelLoader>
    </HotelProvider>
  );
}

export default App;