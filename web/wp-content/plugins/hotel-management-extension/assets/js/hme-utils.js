/**
 * Hotel Management Extension - Shared JavaScript Utilities
 * Common functions used across multiple views
 */

window.HME_Utils = {
    /**
     * Format currency value
     * @param {number} amount - The amount to format
     * @returns {string} Formatted currency string
     */
    formatCurrency: function (amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },

    /**
     * Format date string to localized format
     * @param {string} dateString - Date string to format
     * @returns {string} Formatted date
     */
    formatDate: function (dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN');
    },

    /**
     * Format date and time string to localized format
     * @param {string} dateString - Date string to format
     * @returns {string} Formatted date and time
     */
    formatDateTime: function (dateString) {
        return new Date(dateString).toLocaleString('vi-VN');
    },

    /**
     * Show success notification
     * @param {string} message - Success message to display
     */
    showSuccess: function (message) {
        this.showNotice(message, 'success');
    },

    /**
     * Show error notification
     * @param {string} message - Error message to display
     */
    showError: function (message) {
        this.showNotice(message, 'error');
    },

    /**
     * Show notification with specified type
     * @param {string} message - Message to display
     * @param {string} type - Type of notice (success, error, warning, info)
     */
    showNotice: function (message, type) {
        const noticeClass = type === 'success' ? 'notice-success' :
            type === 'error' ? 'notice-error' :
                type === 'warning' ? 'notice-warning' : 'notice-info';

        const notice = jQuery(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);
        jQuery('.wrap h1').first().after(notice);

        // Auto dismiss after 5 seconds
        setTimeout(function () {
            notice.fadeOut();
        }, 5000);
    },

    /**
     * Show loading indicator
     */
    showLoading: function () {
        if (jQuery('.hme-loading').length === 0) {
            jQuery('body').append('<div class="hme-loading"><div class="loading-spinner"></div><div class="loading-text">Loading...</div></div>');
        }
    },

    /**
     * Hide loading indicator
     */
    hideLoading: function () {
        jQuery('.hme-loading').remove();
    },

    /**
     * Show field error
     * @param {jQuery} field - Field element
     * @param {string} message - Error message
     */
    showFieldError: function (field, message) {
        field.addClass('error');
        field.next('.field-error').remove();
        field.after(`<span class="field-error">${message}</span>`);
    },

    /**
     * Clear field error
     * @param {jQuery} field - Field element
     */
    clearFieldError: function (field) {
        field.removeClass('error');
        field.next('.field-error').remove();
    },

    /**
     * Validate email format
     * @param {string} email - Email to validate
     * @returns {boolean} True if valid email
     */
    isValidEmail: function (email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Calculate nights between two dates
     * @param {string} checkIn - Check-in date
     * @param {string} checkOut - Check-out date
     * @returns {number} Number of nights
     */
    calculateNights: function (checkIn, checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    },

    /**
     * Format discount value based on type
     * @param {string} type - Discount type (percentage, fixed, free_nights)
     * @param {number} value - Discount value
     * @returns {string} Formatted discount
     */
    formatDiscountValue: function (type, value) {
        switch (type) {
            case 'percentage':
                return value + '%';
            case 'fixed':
                return this.formatCurrency(value);
            case 'free_nights':
                return value + ' đêm miễn phí';
            default:
                return value;
        }
    },

    /**
     * Get status class for styling
     * @param {string} status - Status value
     * @returns {string} CSS class
     */
    getStatusClass: function (status) {
        const statusClasses = {
            'pending': 'status-pending',
            'confirmed': 'status-confirmed',
            'cancelled': 'status-cancelled',
            'completed': 'status-completed',
            'active': 'status-active',
            'inactive': 'status-inactive',
            'expired': 'status-expired'
        };
        return statusClasses[status] || 'status-default';
    },

    /**
     * Get status label for display
     * @param {string} status - Status value
     * @returns {string} Display label
     */
    getStatusLabel: function (status) {
        const statusLabels = {
            'pending': 'Chờ xác nhận',
            'confirmed': 'Đã xác nhận',
            'cancelled': 'Đã hủy',
            'completed': 'Hoàn thành',
            'active': 'Hoạt động',
            'inactive': 'Không hoạt động',
            'expired': 'Hết hạn'
        };
        return statusLabels[status] || status;
    },

    /**
     * Make API call with consistent error handling
     * @param {string} endpoint - API endpoint
     * @param {object} data - Request data
     * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
     * @returns {Promise} jQuery AJAX promise
     */
    apiCall: function (endpoint, data = {}, method = 'GET') {
        const requestData = {
            url: hme_admin.api_base_url + endpoint,
            method: method,
            headers: {
                'Authorization': 'Bearer ' + hme_admin.api_token,
                'Content-Type': 'application/json',
                'X-Hotel-ID': hme_admin.hotel_id
            }
        };

        if (method !== 'GET') {
            requestData.data = JSON.stringify(data);
        } else if (Object.keys(data).length > 0) {
            requestData.url += '?' + jQuery.param(data);
        }

        return jQuery.ajax(requestData);
    },

    /**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     * @returns {boolean} True if successful
     */
    copyToClipboard: function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
            return true;
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            return successful;
        }
    },

    /**
     * Debounce function for performance optimization
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    debounce: function (func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Initialize common UI components
     */
    initCommonUI: function () {
        // Add loading styles if not already present
        if (jQuery('#hme-loading-styles').length === 0) {
            jQuery('head').append(`
                <style id="hme-loading-styles">
                .hme-loading {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.8);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                }
                .loading-spinner {
                    width: 40px;
                    height: 40px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #0073aa;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                .loading-text {
                    margin-top: 10px;
                    font-size: 14px;
                    color: #666;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .field-error {
                    color: #d63638;
                    font-size: 13px;
                    display: block;
                    margin-top: 5px;
                }
                .error {
                    border-color: #d63638 !important;
                }
                </style>
            `);
        }

        // Make notice dismissible
        jQuery(document).on('click', '.notice-dismiss', function () {
            jQuery(this).parent().fadeOut();
        });
    }
};

// Initialize when DOM is ready
jQuery(document).ready(function () {
    HME_Utils.initCommonUI();
});