class TenantDetectionService {
  private static instance: TenantDetectionService;
  private wpApiUrl: string = import.meta.env.VITE_WP_API_URL;; // WordPress API URL
  private cacheExpiry = 24 * 60 * 60 * 1000; // 24 hours

  private constructor() { }

  public static getInstance(): TenantDetectionService {
    if (!TenantDetectionService.instance) {
      TenantDetectionService.instance = new TenantDetectionService();
    }
    return TenantDetectionService.instance;
  }

  /**
   * Main method: Get hotel data following the exact flow
   * 1. Access localhost:5173 → Call WP API → Get wp_id + token
   * 2. Cache the result to avoid repeated calls
   * 3. Support language detection from WordPress admin
   */
  async getHotelData(requestedLanguage?: string): Promise<{ wp_id: string; token: string; config: any; language_info?: any }> {
    //const domain = this.getCurrentDomain();
    const domain = 'pinkboutiquehotel.local';

    // Create cache key with language for language-specific caching
    const cacheKey = requestedLanguage ? `${domain}_${requestedLanguage}` : domain;

    // Check cache first
    const cached = this.getCachedData(cacheKey);
    if (cached) {
      return cached;
    }

    // Call WordPress API to get wp_id + token + language info
    try {
      const wpData = await this.callWordPressAPI(domain, requestedLanguage);
      // Cache the result with language-specific key
      this.cacheData(cacheKey, wpData);
      return wpData;
    } catch (error) {
      throw new Error(`WordPress API call failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  /**
   * Call WordPress API to get wp_id + token + language info
   */
  private async callWordPressAPI(domain: string, requestedLanguage?: string): Promise<{ wp_id: string; token: string; config: any; language_info?: any }> {
    let url = `${this.wpApiUrl}/hotel-info/v1/domain-config?domain=${domain}`;

    // Add language parameter if specified
    if (requestedLanguage) {
      url += `&language=${requestedLanguage}`;
    }
    const response = await fetch(url);
    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`WordPress API failed: ${response.status} - ${errorText}`);
    }

    const data = await response.json();
    // Check if response has the expected structure
    if (!data || typeof data !== 'object') {
      throw new Error('Invalid API response format');
    }

    // Handle different response formats
    if (data.success === false) {
      throw new Error(`API error: ${data.message || 'Unknown error'}`);
    }

    // Extract data - handle different possible structures
    const wp_id = data.wp_id || data.wpId || data.hotel_id;
    const token = data.api_token || data.token || data.apiToken;
    const config = data.config || {};
    const language_info = data.language_info || {};

    if (!wp_id || !token) {
      console.error('❌ Missing required fields:', { wp_id, token, data });
      throw new Error('API response missing wp_id or token');
    }

    console.log('🌐 Language info received:', language_info);

    return {
      wp_id,
      token,
      config,
      language_info
    };
  }

  /**
   * Get current domain (simplified for development)
   * Currently unused but kept for future use
   */
  // private getCurrentDomain(): string {
  //   if (typeof window === 'undefined') return 'localhost:5173';

  //   const hostname = window.location.hostname;
  //   const port = window.location.port;

  //   // For development
  //   if (hostname === 'localhost' || hostname === '127.0.0.1') {
  //     return `${hostname}:${port}`;
  //   }

  //   // For production domains
  //   return hostname;
  // }

  /**
   * Get cached data if valid (supports language-specific caching)
   */
  private getCachedData(cacheKey: string): { wp_id: string; token: string; config: any; language_info?: any } | null {
    try {
      const cached = localStorage.getItem(`hotel_data_${cacheKey}`);
      const expiry = localStorage.getItem(`hotel_expiry_${cacheKey}`);

      if (!cached || !expiry) return null;

      const now = Date.now();
      const expiryTime = parseInt(expiry);

      if (now > expiryTime) {
        // Cache expired, clear it
        this.clearCache(cacheKey);
        return null;
      }

      return JSON.parse(cached);
    } catch (error) {
      console.error('Error reading cache:', error);
      return null;
    }
  }

  /**
   * Cache hotel data for 24 hours (supports language-specific caching)
   * Expanded to cache full hotel information including logo, theme, contact info
   */
  private cacheData(cacheKey: string, data: { wp_id: string; token: string; config: any; language_info?: any }): void {
    try {
      const expiry = Date.now() + this.cacheExpiry;

      // Cache full hotel data
      localStorage.setItem(`hotel_data_${cacheKey}`, JSON.stringify(data));
      localStorage.setItem(`hotel_expiry_${cacheKey}`, expiry.toString());

      // Cache individual components for quick access
      if (data.config) {
        localStorage.setItem(`hotel_config_${cacheKey}`, JSON.stringify(data.config));

        // Cache theme info (logo, colors)
        if (data.config.theme) {
          localStorage.setItem(`hotel_theme_${cacheKey}`, JSON.stringify(data.config.theme));
        }

        // Cache contact info
        if (data.config.contact) {
          localStorage.setItem(`hotel_contact_${cacheKey}`, JSON.stringify(data.config.contact));
        }
      }

      console.log(`Cached complete hotel data for ${cacheKey} until ${new Date(expiry)}`);
    } catch (error) {
      console.error('Error caching data:', error);
    }
  }

  /**
   * Get cached hotel config by language
   */
  public getCachedHotelConfig(language: string = 'vi'): any {
    const domain = 'pinkboutiquehotel.local';
    const cacheKey = `${domain}_${language}`;

    try {
      const cached = localStorage.getItem(`hotel_config_${cacheKey}`);
      const expiry = localStorage.getItem(`hotel_expiry_${cacheKey}`);

      if (!cached || !expiry) return null;

      const now = Date.now();
      const expiryTime = parseInt(expiry);

      if (now > expiryTime) {
        return null;
      }

      return JSON.parse(cached);
    } catch (error) {
      console.error('Error reading hotel config cache:', error);
      return null;
    }
  }

  /**
   * Get cached theme data (logo, colors)
   */
  public getCachedTheme(language: string = 'vi'): any {
    const domain = 'pinkboutiquehotel.local';
    const cacheKey = `${domain}_${language}`;

    try {
      const cached = localStorage.getItem(`hotel_theme_${cacheKey}`);
      const expiry = localStorage.getItem(`hotel_expiry_${cacheKey}`);

      if (!cached || !expiry) return null;

      const now = Date.now();
      const expiryTime = parseInt(expiry);

      if (now > expiryTime) {
        return null;
      }

      return JSON.parse(cached);
    } catch (error) {
      console.error('Error reading theme cache:', error);
      return null;
    }
  }

  /**
   * Clear cache for specific cache key
   */
  private clearCache(cacheKey: string): void {
    localStorage.removeItem(`hotel_data_${cacheKey}`);
    localStorage.removeItem(`hotel_expiry_${cacheKey}`);
    localStorage.removeItem(`hotel_config_${cacheKey}`);
    localStorage.removeItem(`hotel_theme_${cacheKey}`);
    localStorage.removeItem(`hotel_contact_${cacheKey}`);
  }
  /**
   * Clear all cached data (for development)
   */
  public clearAllCache(): void {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
      if (key.startsWith('hotel_data_') || key.startsWith('hotel_expiry_')) {
        localStorage.removeItem(key);
      }
    });
    console.log('All hotel cache cleared');
  }
}

export const tenantDetection = TenantDetectionService.getInstance();