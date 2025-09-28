export interface HotelConfig {
  id: string;
  name: string;
  domain: string;
  apiToken: string;
  apiBaseUrl: string;
  wpSiteId: string;
  settings: {
    currency: string;
    timezone: string;
    language: string;
    theme: {
      primaryColor: string;
      logo: string;
      favicon: string;
    };
    contact: {
      phone: string;
      email: string;
      address: string;
    };
    policies: {
      checkIn: string;
      checkOut: string;
      cancellation: string;
    };
  };
}

export interface TenantContext {
  hotel: HotelConfig | null;
  loading: boolean;
  error: string | null;
  setHotel: (hotel: HotelConfig) => void;
}

export interface DomainConfig {
  domain: string;
  hotelId: string;
  wpSiteUrl: string;
  apiEndpoint: string;
}