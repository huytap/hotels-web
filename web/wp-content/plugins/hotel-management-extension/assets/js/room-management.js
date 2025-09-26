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
            copyAll: 'room-management/copy-all',
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
        $('#create-template-btn').on('click', showAddTemplateModal);

        // Modal close handlers
        $('.hme-modal-close').on('click', function () {
            $(this).closest('.hme-modal').hide();
        });

        // Form submissions - use off() to prevent double binding
        $('#quick-edit-form').off('submit').on('submit', handleQuickEdit);
        $('#bulk-update-form').off('submit').on('submit', handleBulkUpdate);
        $('#copy-rates-form').off('submit').on('submit', handleCopyAll);

        // Dynamic content handlers
        $(document).on('click', '.hme-calendar-cell', handleCalendarCellClick);
        $(document).on('click', '.edit-rate', handleEditRate);
        $(document).on('click', '.toggle-availability', handleToggleAvailability);

        // Template handlers
        $(document).on('click', '.edit-template', handleEditTemplate);
        $(document).on('click', '.apply-template', handleApplyTemplate);
        $(document).on('click', '.delete-template', handleDeleteTemplate);
        $('#template-form').on('submit', handleSaveTemplate);
        $('#apply-template-form').on('submit', handleApplyTemplateSubmit);

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
            'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
        ];

        // Update date filters to match current month
        const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);

        // Format dates for display
        const fromDate = firstDay.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        const toDate = lastDay.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        // Update header text with month and date range
        $('#current-month').text(`${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()} (${fromDate} - ${toDate})`);

        // Update date filters
        $('#date-from').val(formatDate(firstDay));
        $('#date-to').val(formatDate(lastDay));
    }

    /**
     * Load calendar data from API
     */
    function loadCalendarData() {
        const roomTypeId = $('#room-type-filter').val();
        if (!roomTypeId) {
            showError(hme_admin.strings.please_select_room_type);
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
                showError(hme_admin.strings.failed_to_load + ' calendar data');
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
            const isCurrentMonth = currentDate.getMonth() === currentMonth.getMonth();
            const dayData = calendarData[dateStr];
            console.log(calendarData)

            let cellClass = 'hme-calendar-cell';
            let cellContent = `<div class="date-number">${currentDate.getDate()}</div>`;

            if (!isCurrentMonth) {
                cellClass += ' other-month';
            } else if (dayData) {
                // Add status classes based on data
                if (!dayData.can_sell) cellClass += ' hme-no-availability';
                else if (dayData.available_rooms <= 3) cellClass += ' hme-low-availability';  // Fixed: use available_rooms instead of total_for_sale
                if (!dayData.is_available) cellClass += ' hme-closed';

                let restrictionsHtml = '';
                if (dayData.restrictions && dayData.restrictions.length > 0) {
                    const badges = dayData.restrictions.map(r => `<span class="restriction-mini ${r.toLowerCase()}">${r}</span>`).join('');
                    restrictionsHtml = `<div class="cell-restrictions">${badges}</div>`;
                }

                cellContent += `
                    <div class="rate-info">
                        <div class="rate">${formatCurrency(dayData.price)}</div>
                        <div class="availability">${dayData.available_rooms || 0} avail</div>
                    </div>
                    ${restrictionsHtml}
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
            showError(hme_admin.strings.please_select_room_type + ' first');
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
            $('#edit-is-closed').prop('checked', !dayData.is_available);  // Fixed: checkbox should be checked when room is CLOSED
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
            is_available: $('#edit-is-closed').prop('checked') ? 0 : 1  // Fixed: 1 = available, 0 = closed
        };

        apiCall(config.apiEndpoints.updateBoth, 'POST', data)
            .done(function () {
                hideModal('#quick-edit-modal');
                showSuccess(hme_admin.strings.rate_updated_successfully);
                loadCalendarData();
            })
            .fail(function () {
                showError(hme_admin.strings.failed_to_update);
            });
    }

    /**
     * Handle bulk update form submission
     */
    function handleBulkUpdate(e) {
        e.preventDefault();

        // Validate required fields
        const roomTypeId = $('#bulk-room-type').val();
        const dateFrom = $('#bulk-date-from').val();
        const dateTo = $('#bulk-date-to').val();

        if (!roomTypeId) {
            showError('Vui lòng chọn loại phòng');
            return;
        }

        if (!dateFrom || !dateTo) {
            showError('Vui lòng chọn khoảng ngày');
            return;
        }

        showLoading();

        // Get form values
        const rateValue = $('#bulk-rate').val();
        const availableRoomsValue = $('#bulk-available-rooms').val();
        const isClosedChecked = $('#edit-is-available').prop('checked');

        // Build data object - only include fields that have values
        const data = {
            roomtype_id: parseInt(roomTypeId),
            date: dateFrom,
            date_to: dateTo
        };

        // Only add price if user entered a value
        if (rateValue && rateValue.trim() !== '') {
            data.price = parseFloat(rateValue);
        }

        // Only add total_for_sale if user entered a value
        if (availableRoomsValue && availableRoomsValue.trim() !== '') {
            data.total_for_sale = parseInt(availableRoomsValue);
        }

        // Only add is_available if checkbox state was changed
        if (typeof isClosedChecked === 'boolean') {
            data.is_available = isClosedChecked ? 0 : 1; // 0 = closed, 1 = available
        }

        // Check if at least one field to update
        if (!data.price && !data.total_for_sale && data.is_available === undefined) {
            showError('Vui lòng nhập ít nhất một giá trị để cập nhật');
            hideLoading();
            return;
        }

        hideModal('#bulk-update-modal');

        apiCall('room-management/update-both', 'POST', data)
            .done(function (response) {
                const results = response.data || [];
                const successCount = Array.isArray(results) ? results.length : 0;
                showSuccess(`Đã cập nhật thành công ${successCount} bản ghi`);
                loadCalendarData();
            })
            .fail(function (xhr) {
                const errorMsg = xhr.responseJSON?.message || hme_admin.strings.bulk_update_failed;
                showError(`Cập nhật thất bại: ${errorMsg}`);
            })
            .always(hideLoading);
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
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
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
                showSuccess(isClosed ? hme_admin.strings.room_opened_successfully : hme_admin.strings.room_closed_successfully);
                loadCalendarData();
            })
            .fail(function () {
                showError(hme_admin.strings.failed_to_toggle_availability);
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
        showLoading('#templates-container');

        apiCall('room-management/templates', 'GET')
            .done(function (response) {
                console.log('Templates API response:', response); // Debug log

                // Handle different response structures
                let templatesData = null;
                if (response && response.data) {
                    templatesData = response.data;
                } else if (response && Array.isArray(response)) {
                    templatesData = response;
                } else {
                    console.warn('Unexpected templates response structure:', response);
                    templatesData = [];
                }

                renderTemplates(templatesData);
            })
            .fail(function (xhr, status, error) {
                console.error('Failed to load templates:', { xhr, status, error });

                let errorMessage = 'Không thể tải mẫu giá phòng';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (error) {
                    errorMessage = error;
                }

                $('#templates-container').html(`
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-warning"></span>
                        <p>${errorMessage}</p>
                        <button type="button" class="button" onclick="window.HMERoomManagement.loadTemplates()">
                            Thử lại
                        </button>
                    </div>
                `);
            })
            .always(() => hideLoading('#templates-container'));
    }

    function renderTemplates(templates) {
        console.log('renderTemplates called with:', templates); // Debug log

        let html = '';

        // Check if templates is valid
        if (!templates || !Array.isArray(templates) || templates.length === 0) {
            html = `
                <div class="hme-empty-state">
                    <span class="dashicons dashicons-admin-page"></span>
                    <p>Chưa có mẫu giá nào. Tạo mẫu đầu tiên của bạn!</p>
                </div>
            `;
        } else {
            html = '<div class="hme-templates-grid">';

            try {
                templates.forEach((template, index) => {
                    console.log(`Processing template ${index}:`, template); // Debug log

                    // Validate template object
                    if (!template || typeof template !== 'object') {
                        console.warn(`Invalid template at index ${index}:`, template);
                        return; // Skip this template
                    }

                    const templateId = template.id || '';
                    const templateName = template.name || 'Mẫu không tên';
                    const templateDescription = template.description || 'Không có mô tả';
                    const isActive = template.is_active ? 'active' : 'inactive';

                    // Safely get rates
                    const rates = template.rates || {};

                    html += `
                        <div class="hme-template-card ${isActive}" data-template-id="${templateId}">
                            <div class="template-header">
                                <h4>${safeEscapeHtml(templateName)}</h4>
                                <div class="template-actions">
                                    <button type="button" class="button button-small edit-template"
                                            data-template-id="${templateId}">
                                        Sửa
                                    </button>
                                    <button type="button" class="button button-small apply-template"
                                            data-template-id="${templateId}">
                                        Áp Dụng
                                    </button>
                                    <button type="button" class="button button-small delete-template"
                                            data-template-id="${templateId}">
                                        Xóa
                                    </button>
                                </div>
                            </div>
                            <div class="template-details">
                                <p class="template-description">${safeEscapeHtml(templateDescription)}</p>
                                <div class="template-rates">
                                    <span class="weekday-rates">
                                        T2: ${formatCurrency(rates.monday || 0)} -
                                        T3: ${formatCurrency(rates.tuesday || 0)} -
                                        T4: ${formatCurrency(rates.wednesday || 0)} -
                                        T5: ${formatCurrency(rates.thursday || 0)} -
                                        T6: ${formatCurrency(rates.friday || 0)} -
                                        T7: ${formatCurrency(rates.saturday || 0)} -
                                        CN: ${formatCurrency(rates.sunday || 0)}
                                    </span>
                                </div>
                                <div class="template-meta">
                                    <span class="min-stay">Lưu trú tối thiểu: ${template.min_stay || 1} đêm</span>
                                    <span class="max-stay">Lưu trú tối đa: ${template.max_stay || 30} đêm</span>
                                    <span class="status ${isActive}">${template.is_active ? 'Kích hoạt' : 'Không kích hoạt'}</span>
                                </div>
                                ${getRestrictionsHtml(template)}
                            </div>
                        </div>
                    `;
                });
            } catch (error) {
                console.error('Error rendering templates:', error);
                html = `
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-warning"></span>
                        <p>Lỗi khi hiển thị mẫu: ${error.message}</p>
                    </div>
                `;
            }

            html += '</div>';
        }

        $('#templates-container').html(html);
    }

    // Get restrictions HTML
    function getRestrictionsHtml(template) {
        const restrictions = [];

        if (template.close_to_arrival) {
            restrictions.push('<span class="restriction-badge cta">CTA</span>');
        }
        if (template.close_to_departure) {
            restrictions.push('<span class="restriction-badge ctd">CTD</span>');
        }
        if (template.is_closed) {
            restrictions.push('<span class="restriction-badge closed">CLOSED</span>');
        }

        if (restrictions.length === 0) {
            return '';
        }

        return `
            <div class="template-restrictions">
                <span class="restrictions-label">Hạn chế:</span>
                ${restrictions.join(' ')}
            </div>
        `;
    }

    // Safe HTML escape function
    function safeEscapeHtml(text) {
        if (!text || typeof text !== 'string') {
            return '';
        }

        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showAddTemplateModal() {
        // Reset form
        $('#template-form')[0].reset();
        $('#template-id').val('');
        $('#template-modal .hme-modal-header h3').text('Tạo Mẫu Mới');
        showModal('#template-modal');
    }

    function handleEditTemplate() {
        const templateId = $(this).data('template-id');

        apiCall('room-management/templates/' + templateId, 'GET')
            .done(function (response) {
                const template = response.data;
                populateTemplateForm(template);
                $('#template-modal .hme-modal-header h3').text('Chỉnh Sửa Mẫu');
                showModal('#template-modal');
            })
            .fail(function () {
                showError('Không thể tải thông tin mẫu');
            });
    }

    function populateTemplateForm(template) {
        $('#template-id').val(template.id);
        $('#template-name').val(template.name);
        $('#template-description').val(template.description);
        $('#template-room-type').val(template.roomtype_id);

        // Populate weekday rates
        if (template.rates) {
            Object.keys(template.rates).forEach(day => {
                $(`#rate-${day}`).val(template.rates[day]);
            });
        }

        $('#template-min-stay').val(template.min_stay);
        $('#template-max-stay').val(template.max_stay);
        $('#template-is-active').prop('checked', template.is_active);

        // Populate restrictions
        $('#template-close-to-arrival').prop('checked', template.close_to_arrival);
        $('#template-close-to-departure').prop('checked', template.close_to_departure);
        $('#template-is-closed').prop('checked', template.is_closed);
    }

    function handleSaveTemplate(e) {
        e.preventDefault();
        showLoading();

        const templateId = $('#template-id').val();
        const data = {
            id: templateId || null,
            name: $('#template-name').val(),
            description: $('#template-description').val(),
            roomtype_id: $('#template-room-type').val(),
            rates: {
                monday: parseFloat($('#rate-monday').val()) || 0,
                tuesday: parseFloat($('#rate-tuesday').val()) || 0,
                wednesday: parseFloat($('#rate-wednesday').val()) || 0,
                thursday: parseFloat($('#rate-thursday').val()) || 0,
                friday: parseFloat($('#rate-friday').val()) || 0,
                saturday: parseFloat($('#rate-saturday').val()) || 0,
                sunday: parseFloat($('#rate-sunday').val()) || 0
            },
            min_stay: parseInt($('#template-min-stay').val()) || 1,
            max_stay: parseInt($('#template-max-stay').val()) || 30,
            is_active: +$('#template-is-active').prop('checked'),
            // Restrictions
            close_to_arrival: +$('#template-close-to-arrival').prop('checked'),
            close_to_departure: +$('#template-close-to-departure').prop('checked'),
            is_closed: +$('#template-is-closed').prop('checked')
        };

        const endpoint = templateId ? `room-management/templates/${templateId}` : 'room-management/templates';
        const method = templateId ? 'PUT' : 'POST';

        apiCall(endpoint, method, data)
            .done(function () {
                hideModal('#template-modal');
                showSuccess(templateId ? 'Mẫu đã được cập nhật' : 'Mẫu mới đã được tạo');
                loadTemplates();
            })
            .fail(function () {
                showError('Không thể lưu mẫu');
            })
            .always(hideLoading);
    }

    function handleApplyTemplate() {
        const templateId = $(this).data('template-id');

        showLoading();

        // Get template details from API
        apiCall(`room-management/templates/${templateId}`, 'GET')
            .done(function (response) {
                const template = response.data;

                // Reset form and populate with template data
                $('#apply-template-form')[0].reset();
                $('#apply-template-id').val(templateId);
                $('#apply-template-name').text(template.name);

                // Set room type from template (read-only)
                $('#apply-room-type').val(template.roomtype_id);

                // Find room type name
                const roomType = roomTypes.find(r => r.id == template.roomtype_id);
                $('#apply-room-type-name').text(roomType ? roomType.name : 'Unknown');

                // Set default date range (current month)
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                $('#apply-date-from').val(formatDate(firstDay));
                $('#apply-date-to').val(formatDate(lastDay));

                hideLoading();
                showModal('#apply-template-modal');
            })
            .fail(function () {
                hideLoading();
                showError('Không thể tải thông tin mẫu');
            });
    }

    function handleApplyTemplateSubmit(e) {
        e.preventDefault();
        showLoading();

        const data = {
            template_id: parseInt($('#apply-template-id').val()),
            roomtype_id: parseInt($('#apply-room-type').val()),
            date_from: $('#apply-date-from').val(),
            date_to: $('#apply-date-to').val(),
            overwrite_existing: +$('#apply-overwrite').prop('checked')
        };

        // Validate required fields
        if (!data.template_id || !data.roomtype_id || !data.date_from || !data.date_to) {
            showError('Vui lòng điền đầy đủ thông tin bắt buộc');
            hideLoading();
            return;
        }

        apiCall('room-management/apply-template', 'POST', data)
            .done(function (response) {
                hideModal('#apply-template-modal');
                const count = response.data.applied_count || 0;
                showSuccess(`Đã áp dụng mẫu cho ${count} ngày`);
                loadCalendarData();
            })
            .fail(function (xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Không thể áp dụng mẫu';
                showError(`Áp dụng mẫu thất bại: ${errorMsg}`);
            })
            .always(hideLoading);
    }

    function handleDeleteTemplate() {
        const templateId = $(this).data('template-id');

        if (!confirm('Bạn có chắc chắn muốn xóa mẫu này?')) {
            return;
        }

        showLoading();

        apiCall(`room-management/templates/${templateId}`, 'DELETE')
            .done(function () {
                showSuccess('Mẫu đã được xóa');
                loadTemplates();
            })
            .fail(function () {
                showError('Không thể xóa mẫu');
            })
            .always(hideLoading);
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function handleCopyAll(e) {
        e.preventDefault();

        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);

        showLoading();

        const data = {
            roomtype_id: $('#copy-room-type').val(),
            source_date_from: $('#copy-source-from').val(),
            source_date_to: $('#copy-source-to').val(),
            target_date_from: $('#copy-target-from').val(),
            target_date_to: $('#copy-target-to').val(),
            copy_rates: +$('input[name="copy_rates"]').prop('checked'),
            copy_availability: +$('input[name="copy_availability"]').prop('checked'),
            copy_restrictions: +$('input[name="copy_restrictions"]').prop('checked'),
            overwrite_existing: +$('input[name="overwrite_existing"]').prop('checked')
        };

        // Validate required fields
        if (!data.roomtype_id || !data.source_date_from || !data.source_date_to ||
            !data.target_date_from || !data.target_date_to) {
            showError(__('Vui lòng điền đầy đủ thông tin bắt buộc', 'hotel'));
            $('#copy-rates-form').data('submitting', false);
            hideLoading();
            return;
        }

        // Validate at least one copy option is selected
        if (!data.copy_rates && !data.copy_availability && !data.copy_restrictions) {
            showError(__('Vui lòng chọn ít nhất một tùy chọn sao chép', 'hotel'));
            $('#copy-rates-form').data('submitting', false);
            hideLoading();
            return;
        }

        // Use single optimized API call instead of 3 separate calls
        const requestData = {
            roomtype_id: data.roomtype_id,
            source_start_date: data.source_date_from,
            source_end_date: data.source_date_to,
            target_start_date: data.target_date_from,
            target_end_date: data.target_date_to,
            copy_rates: data.copy_rates,
            copy_availability: data.copy_availability,
            copy_restrictions: data.copy_restrictions,
            overwrite_existing: data.overwrite_existing
        };

        apiCall(config.apiEndpoints.copyAll, 'POST', requestData)
            .done(function (response) {
                hideModal('#copy-rates-modal');
                const results = response.data;
                let message = 'Hoàn thành sao chép: ';
                let parts = [];

                if (results.rates_copied > 0) {
                    parts.push(`${results.rates_copied} giá phòng`);
                }
                if (results.inventory_copied > 0) {
                    parts.push(`${results.inventory_copied} tình trạng phòng`);
                }
                if (results.restrictions_copied > 0) {
                    parts.push(`${results.restrictions_copied} hạn chế`);
                }

                message += parts.length > 0 ? parts.join(', ') : 'không có dữ liệu nào được sao chép';
                showSuccess(message);
                loadCalendarData();
            })
            .fail(function (xhr) {
                const errorMsg = xhr.responseJSON?.message || hme_admin.strings.error;
                showError(`Sao chép thất bại: ${errorMsg}`);
            })
            .always(function () {
                // Reset submission flag
                $('#copy-rates-form').data('submitting', false);
                hideLoading();
            });
    }

    function handleEditRate() {
        const roomTypeId = $(this).data('room-type-id');
        const date = $(this).data('date');
        const rateInfo = $(this).data('rate-info');
        showQuickEditModal(roomTypeId, date, rateInfo);
    }

    function renderRatesList() {
        if (!calendarData || Object.keys(calendarData).length === 0) {
            $('#list-container').html(`
                <div class="hme-empty-state">
                    <span class="dashicons dashicons-list-view"></span>
                    <p>Vui lòng tải dữ liệu lịch trước khi xem danh sách</p>
                </div>
            `);
            return;
        }

        let html = `
            <div class="hme-rates-table-container">
                <div class="hme-table-header">
                    <h3>Danh Sách Giá Phòng</h3>
                    <div class="hme-table-filters">
                        <label>
                            <input type="checkbox" id="show-weekends-only" />
                            Chỉ hiển thị cuối tuần
                        </label>
                        <label>
                            <input type="checkbox" id="show-no-rates-only" />
                            Chỉ hiển thị ngày chưa có giá
                        </label>
                        <button type="button" class="button button-primary" id="bulk-edit-selected">
                            Sửa hàng loạt
                        </button>
                    </div>
                </div>
                <table class="hme-rates-table wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" id="select-all-dates">
                            </th>
                            <th class="column-date">Ngày</th>
                            <th class="column-weekday">Thứ</th>
                            <th class="column-rate">Giá (VNĐ)</th>
                            <th class="column-total-rooms">Tổng Phòng</th>
                            <th class="column-booked-rooms">Đã Đặt</th>
                            <th class="column-available-rooms">Còn Trống</th>
                            <th class="column-status">Trạng Thái</th>
                            <th class="column-actions">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        const sortedDates = Object.keys(calendarData).sort();
        const weekdayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        sortedDates.forEach(dateStr => {
            const data = calendarData[dateStr];
            const date = new Date(dateStr);
            const weekday = weekdayNames[date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;

            let statusClass = '';
            let statusText = 'Bình thường';

            if (!data.is_available) {
                statusClass = 'status-closed';
                statusText = 'Đóng';
            } else if (!data.can_sell) {
                statusClass = 'status-no-availability';
                statusText = 'Hết phòng';
            } else if (data.available_rooms <= 3) {
                statusClass = 'status-low-availability';
                statusText = 'Còn ít phòng';
            }

            html += `
                <tr class="rate-row ${isWeekend ? 'weekend' : 'weekday'} ${statusClass}"
                    data-date="${dateStr}"
                    data-has-rate="${data.has_rate ? '1' : '0'}">
                    <th class="check-column">
                        <input type="checkbox" class="date-checkbox" value="${dateStr}">
                    </th>
                    <td class="column-date">
                        <strong>${formatDisplayDate(dateStr)}</strong>
                    </td>
                    <td class="column-weekday ${isWeekend ? 'weekend' : ''}">${weekday}</td>
                    <td class="column-rate">
                        ${data.has_rate ? formatCurrency(data.price) : '<em>Chưa có giá</em>'}
                    </td>
                    <td class="column-total-rooms">${data.total_for_sale}</td>
                    <td class="column-booked-rooms">${data.booked_rooms}</td>
                    <td class="column-available-rooms">${data.available_rooms}</td>
                    <td class="column-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <button type="button" class="button button-small edit-rate"
                                    data-date="${dateStr}">
                                Sửa
                            </button>
                            <button type="button" class="button button-small toggle-availability"
                                    data-date="${dateStr}"
                                    data-is-closed="${!data.is_available}">
                                ${data.is_available ? 'Đóng' : 'Mở'}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        $('#list-container').html(html);

        // Add event handlers for list view
        setupListViewHandlers();
    }

    function setupListViewHandlers() {
        // Select all checkbox
        $('#select-all-dates').on('change', function () {
            const checked = $(this).prop('checked');
            $('.date-checkbox:visible').prop('checked', checked);
        });

        // Filtering functionality
        $('#show-weekends-only').on('change', function () {
            const showWeekendsOnly = $(this).prop('checked');
            if (showWeekendsOnly) {
                $('.rate-row.weekday').hide();
                $('.rate-row.weekend').show();
            } else {
                $('.rate-row').show();
            }
            updateNoRatesFilter();
        });

        $('#show-no-rates-only').on('change', updateNoRatesFilter);

        // Bulk edit selected
        $('#bulk-edit-selected').on('click', function () {
            const selectedDates = [];
            $('.date-checkbox:checked').each(function () {
                selectedDates.push($(this).val());
            });

            if (selectedDates.length === 0) {
                showError('Vui lòng chọn ít nhất một ngày');
                return;
            }

            // Populate bulk update modal with selected dates
            const firstDate = selectedDates[0];
            const lastDate = selectedDates[selectedDates.length - 1];
            $('#bulk-date-from').val(firstDate);
            $('#bulk-date-to').val(lastDate);
            showModal('#bulk-update-modal');
        });

        // Individual edit buttons
        $(document).on('click', '#list-container .edit-rate', function () {
            const date = $(this).data('date');
            const roomTypeId = $('#room-type-filter').val();
            const dayData = calendarData[date];

            if (roomTypeId && date) {
                showQuickEditModal(roomTypeId, date, dayData);
            } else {
                showError('Vui lòng chọn loại phòng trước');
            }
        });
    }

    function updateNoRatesFilter() {
        const showNoRatesOnly = $('#show-no-rates-only').prop('checked');
        if (showNoRatesOnly) {
            $('.rate-row[data-has-rate="1"]').hide();
            $('.rate-row[data-has-rate="0"]:visible').show();
        } else {
            const showWeekendsOnly = $('#show-weekends-only').prop('checked');
            if (!showWeekendsOnly) {
                $('.rate-row').show();
            }
        }
    }


    // Export for global access if needed
    window.HMERoomManagement = {
        loadCalendarData,
        switchView,
        formatCurrency,
        formatDate
    };

})(jQuery);