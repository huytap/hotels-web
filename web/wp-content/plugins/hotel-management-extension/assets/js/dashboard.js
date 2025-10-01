/**
 * Hotel Management Extension - Dashboard JavaScript
 * Handles dashboard data loading and display
 */

(function ($) {
    'use strict';

    // Initialize dashboard when DOM is ready
    $(document).ready(function () {
        loadDashboardData();

        // Auto-refresh every 5 minutes
        setInterval(loadDashboardData, 5 * 60 * 1000);
    });

    /**
     * Load dashboard data from API
     */
    function loadDashboardData() {
        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_get_dashboard_stats',
                nonce: hme_admin.nonce
            },
            success: function (response) {
                if (response.success) {
                    updateDashboardStats(response.data);
                    $('.hme-dashboard-loading').fadeOut(300, function () {
                        $('.hme-dashboard-content').fadeIn(300);
                    });
                } else {
                    showError('Failed to load dashboard data: ' + (response.data || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                console.error('Dashboard AJAX Error:', error);
                showError('Error connecting to server. Please refresh the page.');
            }
        });
    }

    /**
     * Update dashboard statistics
     */
    function updateDashboardStats(data) {
        // Update stat cards with animation
        animateNumber($('#total-bookings .hme-stat-number'), data.total_bookings || 0);
        animateNumber($('#pending-bookings .hme-stat-number'), data.pending_bookings || 0);

        $('#available-rooms .hme-stat-number').text(
            (data.available_rooms || 0) + '/' + (data.total_rooms || 0)
        );

        animateNumber($('#active-promotions .hme-stat-number'), data.active_promotions || 0);

        $('#today-revenue .hme-stat-number').text(
            formatCurrency(data.today_revenue || 0) + ' VNĐ'
        );

        $('#month-revenue .hme-stat-number').text(
            formatCurrency(data.month_revenue || 0) + ' VNĐ'
        );

        // Update recent bookings
        updateRecentBookings(data.recent_bookings);

        // Update room availability
        updateRoomAvailability(data.room_availability);
    }

    /**
     * Update recent bookings list
     */
    function updateRecentBookings(bookings) {
        const $container = $('#recent-bookings');

        if (!bookings || bookings.length === 0) {
            $container.html('<p>No recent bookings</p>');
            return;
        }

        let html = '<ul>';
        bookings.forEach(function (booking) {
            const statusClass = getStatusClass(booking.status);
            const statusLabel = getStatusLabel(booking.status);

            html += `
                <li>
                    <div>
                        <strong>${escapeHtml(booking.customer_name)}</strong>
                        <span style="color: #6b7280;"> - ${escapeHtml(booking.room_type)}</span>
                    </div>
                    <span class="${statusClass}">${statusLabel}</span>
                </li>
            `;
        });
        html += '</ul>';

        $container.html(html);
    }

    /**
     * Update room availability display
     */
    function updateRoomAvailability(rooms) {
        const $container = $('#room-availability');

        if (!rooms || rooms.length === 0) {
            $container.html('<p>No room data available</p>');
            return;
        }

        let html = '';
        rooms.forEach(function (room) {
            const percentage = room.total > 0 ? Math.round((room.available / room.total) * 100) : 0;
            const colorClass = percentage > 50 ? 'green' : percentage > 20 ? 'orange' : 'red';

            html += `
                <div class="hme-room-item">
                    <strong>${escapeHtml(room.name)}</strong>
                    <div>
                        <span class="available">${room.available} available</span> /
                        <span class="total">${room.total} total</span>
                    </div>
                </div>
            `;
        });

        $container.html(html);
    }

    /**
     * Animate number counting
     */
    function animateNumber($element, targetValue) {
        const currentValue = parseInt($element.text()) || 0;
        const duration = 1000; // 1 second
        const steps = 30;
        const increment = (targetValue - currentValue) / steps;
        let current = currentValue;
        let step = 0;

        const timer = setInterval(function () {
            step++;
            current += increment;

            if (step >= steps) {
                current = targetValue;
                clearInterval(timer);
            }

            $element.text(Math.round(current));
        }, duration / steps);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    /**
     * Get status class
     */
    function getStatusClass(status) {
        return 'status-' + (status || 'unknown');
    }

    /**
     * Get status label
     */
    function getStatusLabel(status) {
        const labels = {
            'confirmed': 'Confirmed',
            'pending': 'Pending',
            'cancelled': 'Cancelled',
            'completed': 'Completed',
            'no_show': 'No Show'
        };
        return labels[status] || status;
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $loading = $('.hme-dashboard-loading');
        $loading.html(`
            <div class="notice notice-error">
                <p><strong>Error:</strong> ${escapeHtml(message)}</p>
                <p><button type="button" class="button" onclick="location.reload()">Reload Page</button></p>
            </div>
        `);
        $loading.show();
    }

})(jQuery);
