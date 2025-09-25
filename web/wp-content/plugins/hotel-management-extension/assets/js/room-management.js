/**
 * Hotel Management Extension - Room Management JavaScript
 * Enhanced Room Rates & Inventory Management
 */

(function ($) {
    'use strict';

    // Global variables
    let currentView = 'calendar';
    let currentMonth = new Date();
    let calendarData = {};
    let roomTypes = [];

    // Configuration
    const config = {
        dateFormat: 'YYYY-MM-DD',
        currency: 'VND',
        apiEndpoints: {
            calendar: 'room-management/calendar',
            updateBoth: 'room-management/update-both',
            bulkRates: 'room-management/rates/bulk',
            bulkInventory: 'room-management/inventory/bulk',
            copyRates: 'room-management/rates/copy',
            statistics: 'room-management/statistics'
        }
    };

    /**
     * Main initialization
     */
    $(document).ready(function () {
        initializePlugin();
        bindEventHandlers();
        loadInitialData();
    });

    /**
     * Initialize the plugin
     */
    function initializePlugin() {
        updateCalendarHeader();
        switchView('calendar');

        // Load room types from global variable if available
        if (typeof window.hmeRoomTypes !== 'undefined') {
            roomTypes = window.hmeRoomTypes;
        }
    }

    /**
     * Bind all event handlers
     */
    function bindEventHandlers() {
        // View switching
        $('.hme-tab-btn').on('click', handleViewSwitch);

        // Calendar navigation
        $('#prev-month').on('click', navigatePreviousMonth);
        $('#next-month').on('click', navigateNextMonth);

        // Data loading
        $('#load-calendar').on('click', loadCalendarData);
        $('#export-rates').on('click', exportRates);

        // Modal triggers
        $('#bulk-update-btn').on('click', () => showModal('#bulk-update-modal'));
        $('#copy-rates-btn').on('click', () => showModal('#copy-rates-modal'));
        $('#add-template-btn').on('click', showAddTemplateModal);

        // Modal close handlers
        $('.hme-modal-close').on('click', function () {
            $(this).closest('.hme-modal').hide();
        });

        // Form submissions
        $('#quick-edit-form').on('submit', handleQuickEdit);
        $('#bulk-update-form').on('submit', handleBulkUpdate);
        $('#copy-rates-form').on('submit', handleCopyRates);

        // Dynamic content handlers
        $(document).on('click', '.hme-calendar-cell', handleCalendarCellClick);
        $(document).on('click', '.edit-rate', handleEditRate);
        $(document).on('click', '.toggle-availability', handleToggleAvailability);

        // Form validation
        $('input[name="rate_update_type"]').on('change', handleRateUpdateTypeChange);
    }

    /**
     * Load initial data
     */
    function loadInitialData() {
        // Auto-load calendar if room type is selected
        if ($('#room-type-filter').val()) {
            loadCalendarData();
        }
    }

    /**
     * View switching handler
     */
    function handleViewSwitch() {
        const view = $(this).data('view');
        switchView(view);
    }

    /**
     * Switch between different views
     */
    function switchView(view) {
        $('.hme-tab-btn').removeClass('active');
        $(`.hme-tab-btn[data-view="${view}"]`).addClass('active');

        $('.hme-view-content').hide();
        $(`#${view}-view`).show();

        currentView = view;

        // Load data for specific views
        switch (view) {
            case 'templates':
                loadTemplates();
                break;
            case 'list':
                if (calendarData && Object.keys(calendarData).length > 0) {
                    renderRatesList();
                }
                break;
        }
    }

    /**
     * Calendar navigation handlers
     */
    function navigatePreviousMonth() {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        updateCalendarHeader();
        loadCalendarData();
    }

    function navigateNextMonth() {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        updateCalendarHeader();
        loadCalendarData();
    }

    /**
     * Update calendar header with current month
     */
    function updateCalendarHeader() {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $('#current-month').text(`${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`);

        // Update date filters to match current month
        const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);

        $('#date-from').val(formatDate(firstDay));
        $('#date-to').val(formatDate(lastDay));
    }

    /**
     * Load calendar data from API
     */
    function loadCalendarData() {
        const roomTypeId = $('#room-type-filter').val();
        if (!roomTypeId) {
            showError('Please select a room type');
            return;
        }

        showLoading();

        const params = {
            roomtype_id: roomTypeId,
            start_date: $('#date-from').val(),
            end_date: $('#date-to').val()
        };

        apiCall(config.apiEndpoints.calendar, 'GET', params)
            .done(function (response) {
                calendarData = response.data;
                renderCalendar();
            })
            .fail(function () {
                showError('Failed to load calendar data');
            })
            .always(hideLoading);
    }

    /**
     * Render calendar with data
     */
    function renderCalendar() {
        const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        let html = `
            <table class="hme-calendar-table">
                <thead>
                    <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let currentDate = new Date(startDate);
        while (currentDate <= lastDay || currentDate.getDay() !== 0) {
            if (currentDate.getDay() === 0) {
                html += '<tr>';
            }

            const dateStr = formatDate(currentDate);
            console.log(dateStr)
            const isCurrentMonth = currentDate.getMonth() === currentMonth.getMonth();
            const dayData = calendarData[dateStr];

            let cellClass = 'hme-calendar-cell';
            let cellContent = `<div class="date-number">${currentDate.getDate()}</div>`;

            if (!isCurrentMonth) {
                cellClass += ' other-month';
            } else if (dayData) {
                // Add status classes based on data
                if (!dayData.can_sell) cellClass += ' hme-no-availability';
                else if (dayData.total_for_sale <= 3) cellClass += ' hme-low-availability';
                if (!dayData.is_available) cellClass += ' hme-closed';

                cellContent += `
                    <div class="rate-info">
                        <div class="rate">${formatCurrency(dayData.price)}</div>
                        <div class="availability">${dayData.total_for_sale} avail</div>
                    </div>
                `;
            } else {
                cellContent += '<div class="no-data">No data</div>';
            }

            html += `
                <td class="${cellClass}"
                    data-date="${dateStr}"
                    data-day-data='${JSON.stringify(dayData || {})}'>
                    ${cellContent}
                </td>
            `;

            if (currentDate.getDay() === 6) {
                html += '</tr>';
            }

            currentDate.setDate(currentDate.getDate() + 1);
        }

        html += '</tbody></table>';
        $('#calendar-container').html(html);
    }

    /**
     * Handle calendar cell click
     */
    function handleCalendarCellClick() {
        const date = $(this).data('date');
        const dayData = $(this).data('day-data');
        const roomTypeId = $('#room-type-filter').val();

        if (date && roomTypeId) {
            showQuickEditModal(roomTypeId, date, dayData);
        } else {
            showError('Please select a room type first');
        }
    }

    /**
     * Show quick edit modal
     */
    function showQuickEditModal(roomTypeId, date, dayData) {
        $('#edit-room-type-id').val(roomTypeId);
        $('#edit-date').val(date);
        $('#edit-date-display').text(formatDisplayDate(date));

        // Find room type name
        const roomType = roomTypes.find(r => r.id == roomTypeId);
        $('#edit-room-type-display').text(roomType ? roomType.name : 'Unknown');

        // Fill current values
        if (dayData && Object.keys(dayData).length > 0) {
            $('#edit-rate').val(dayData.price || 0);
            $('#edit-available-rooms').val(dayData.total_for_sale || 0);
            $('#edit-is-closed').prop('checked', dayData.is_available);
        } else {
            // Reset form for new entry
            $('#edit-rate').val('');
            $('#edit-available-rooms').val('');
            $('#edit-is-closed').prop('checked', false);
        }

        showModal('#quick-edit-modal');
    }

    /**
     * Handle quick edit form submission
     */
    function handleQuickEdit(e) {
        e.preventDefault();

        const data = {
            roomtype_id: $('#room-type-filter').val(),
            date: $('#edit-date').val(),
            price: parseFloat($('#edit-rate').val()) || 0,
            total_for_sale: parseInt($('#edit-available-rooms').val()) || 0,
            is_available: +$('#edit-is-closed').prop('checked')
        };

        apiCall(config.apiEndpoints.updateBoth, 'POST', data)
            .done(function () {
                hideModal('#quick-edit-modal');
                showSuccess('Rate and inventory updated successfully');
                loadCalendarData();
            })
            .fail(function () {
                showError('Failed to update rate and inventory');
            });
    }

    /**
     * Handle bulk update form submission
     */
    function handleBulkUpdate(e) {
        e.preventDefault();
        showLoading();
        const data = {
            roomtype_id: $('#bulk-room-type').val(),
            date_to: $('#bulk-date-to').val(),
            date: $("#bulk-date-from").val(),
            price: parseFloat($('#bulk-rate').val()) || 0,
            total_for_sale: parseInt($('#bulk-available-rooms').val()) || 0,
            is_available: +$('#edit-is-available').prop('checked')
        };
        // Determine which API to call
        let endpoint;
        //if (data.price && (data.total_for_sale >= 0 || data.is_available !== undefined)) {
        // Need to update both - call multiple APIs
        //bulkUpdateBoth(data);
        // return;
        endpoint = config.apiEndpoints.updateBoth;
        // } else if (data.price) {
        //     endpoint = config.apiEndpoints.bulkRates;
        // } else {
        //     endpoint = config.apiEndpoints.bulkInventory;
        // }

        hideModal('#bulk-update-modal');
        apiCall(endpoint, 'POST', data)
            .done(function (response) {
                const count = response.data.updated_count || 0;
                showSuccess(`Successfully updated ${count} records`);
                loadCalendarData();
            })
            .fail(function () {
                showError('Bulk update failed');
            }).always(hideLoading);
    }

    /**
     * Handle bulk update for both rates and inventory
     */
    function bulkUpdateBoth(data) {
        const requests = [];

        // Prepare rate data
        if (data.price) {
            const rateData = {
                hotel_id: data.hotel_id,
                roomtype_id: data.roomtype_id,
                start_date: data.start_date,
                end_date: data.end_date,
                price: data.price
            };
            if (data.weekdays) rateData.weekdays = data.weekdays;
            requests.push(apiCall(config.apiEndpoints.bulkRates, 'POST', rateData));
        }

        // Prepare inventory data
        if (data.total_for_sale >= 0 || data.is_available !== undefined) {
            const inventoryData = {
                hotel_id: data.hotel_id,
                roomtype_id: data.roomtype_id,
                start_date: data.start_date,
                end_date: data.end_date
            };
            if (data.total_for_sale >= 0) inventoryData.total_for_sale = data.total_for_sale;
            if (data.is_available !== undefined) inventoryData.is_available = data.is_available;
            if (data.weekdays) inventoryData.weekdays = data.weekdays;
            requests.push(apiCall(config.apiEndpoints.bulkInventory, 'POST', inventoryData));
        }

        $.when.apply($, requests)
            .done(function () {
                hideModal('#bulk-update-modal');
                showSuccess('Bulk update completed successfully');
                loadCalendarData();
            })
            .fail(function () {
                showError('Bulk update failed');
            });
    }

    /**
     * API call wrapper
     */
    function apiCall(endpoint, method, data) {
        const ajaxData = {
            action: 'hme_api_call',
            nonce: hme_admin.nonce,
            endpoint: endpoint,
            method: method,
            data: data
        };

        return $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: ajaxData,
            dataType: 'json'
        });
    }

    /**
     * Utility functions
     */
    function getWpId() {
        return parseInt(hme_admin.blog_id || '1');
    }

    function formatDate(date) {
        //return date.toISOString().split('T')[0];
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDisplayDate(dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN');
    }

    function formatCurrency(amount) {
        if (!amount) return '0 VND';
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VND';
    }

    function getWeekdayNumber(dayName) {
        const days = {
            'sunday': 0, 'monday': 1, 'tuesday': 2, 'wednesday': 3,
            'thursday': 4, 'friday': 5, 'saturday': 6
        };
        return days[dayName.toLowerCase()] || 0;
    }

    function showModal(selector) {
        $(selector).show();
    }

    function hideModal(selector) {
        $(selector).hide();
    }

    function showLoading() {
        $('#hme-loading').show();
    }

    function hideLoading() {
        $('#hme-loading').hide();
    }

    function showSuccess(message) {
        showNotice(message, 'success');
    }

    function showError(message) {
        showNotice(message, 'error');
    }

    function showNotice(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

        $('.wrap h1').after(notice);

        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, 5000);

        notice.find('.notice-dismiss').on('click', function () {
            notice.fadeOut(() => notice.remove());
        });
    }

    // Additional handlers
    function handleRateUpdateTypeChange() {
        const type = $(this).val();
        $('#bulk-set-rate').toggle(type === 'set').prop('required', type === 'set');
        $('#bulk-adjust-type, #bulk-adjust-value').toggle(type === 'adjust').prop('required', type === 'adjust');
    }

    function handleToggleAvailability() {
        // Implementation for quick availability toggle
        const roomTypeId = $(this).data('room-type-id');
        const date = $(this).data('date');
        const isClosed = $(this).hasClass('closed');

        const data = {
            wp_id: getWpId(),
            roomtype_id: roomTypeId,
            date: date,
            is_available: isClosed
        };

        apiCall('room-management/inventory', 'POST', data)
            .done(function () {
                showSuccess(`Room ${isClosed ? 'opened' : 'closed'} successfully`);
                loadCalendarData();
            })
            .fail(function () {
                showError('Failed to toggle availability');
            });
    }

    function exportRates() {
        const params = new URLSearchParams({
            action: 'hme_export_rates',
            nonce: hme_admin.nonce,
            roomtype_id: $('#room-type-filter').val(),
            start_date: $('#date-from').val(),
            end_date: $('#date-to').val()
        });

        window.location.href = `${hme_admin.ajax_url}?${params.toString()}`;
    }

    function loadTemplates() {
        // Template loading implementation
        $('#templates-container').html(`
            <div class="hme-empty-state">
                <span class="dashicons dashicons-admin-settings"></span>
                <p>Rate templates feature coming soon...</p>
            </div>
        `);
    }

    function showAddTemplateModal() {
        showModal('#template-modal');
    }

    function handleCopyRates(e) {
        e.preventDefault();
        showError('Copy rates feature coming soon...');
    }

    function handleEditRate() {
        const roomTypeId = $(this).data('room-type-id');
        const date = $(this).data('date');
        const rateInfo = $(this).data('rate-info');
        showQuickEditModal(roomTypeId, date, rateInfo);
    }

    function renderRatesList() {
        // Convert calendar data to list format
        const ratesList = Object.entries(calendarData).map(([date, data]) => ({
            date,
            ...data
        }));

        let html = '';

        if (ratesList.length > 0) {
            ratesList.forEach(function (rate) {
                html += `
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" value="${rate.date}">
                        </th>
                        <td>${formatDisplayDate(rate.date)}</td>
                        <td>Current Room Type</td>
                        <td>${formatCurrency(rate.price)}</td>
                        <td>${rate.total_for_sale || 0}</td>
                        <td>1 - 30</td>
                        <td>${rate.is_available ? 'Open' : 'Closed'}</td>
                        <td>
                            <button type="button" class="button button-small edit-rate"
                                    data-room-type-id="${$('#room-type-filter').val()}"
                                    data-date="${rate.date}"
                                    data-rate-info='${JSON.stringify(rate)}'>
                                Edit
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = `
                <tr>
                    <td colspan="8">
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-admin-home"></span>
                            <p>No rate data found for selected criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        $('#rates-tbody').html(html);
    }

    // Export for global access if needed
    window.HMERoomManagement = {
        loadCalendarData,
        switchView,
        formatCurrency,
        formatDate
    };

})(jQuery);