/**
 * Bookings List Management JavaScript
 * File: assets/js/bookings-list.js
 * Version: 1.0.0
 */

(function ($) {
    'use strict';

    // Global variables
    let currentPage = 1;
    let currentFilters = {};
    let sortField = 'created_at';
    let sortOrder = 'desc';
    let selectedBookingId = null;

    // Initialize when DOM is ready
    $(document).ready(function () {
        initBookingsList();
        bindEvents();
        loadBookings();
    });

    /**
     * Initialize bookings list functionality
     */
    function initBookingsList() {
        // Set default date filters (optional)
        const today = new Date();
        const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());

        // Uncomment to set default date range
        // $('#booking-date-from').val(formatDateForInput(today));
        // $('#booking-date-to').val(formatDateForInput(nextMonth));
    }

    /**
     * Bind all event listeners
     */
    function bindEvents() {
        // Filter events
        $('#filter-bookings').on('click', handleFilterBookings);
        $('#clear-filters').on('click', handleClearFilters);
        $('#export-bookings').on('click', handleExportBookings);

        // Bulk actions
        $('#doaction').on('click', handleBulkAction);
        $('#cb-select-all-1').on('change', handleSelectAll);

        // Sorting
        $('.sortable').on('click', handleSort);

        // Pagination
        $(document).on('click', '.page-numbers', handlePagination);

        // Row actions
        $(document).on('click', '.view-booking', handleViewBooking);
        $(document).on('click', '.change-status', handleChangeStatus);
        $(document).on('click', '.delete-booking', handleDeleteBooking);

        // Modal events
        $('.hme-modal-close').on('click', closeModal);
        $(document).on('click', '.hme-modal', handleModalBackdropClick);

        // Form submissions
        $('#status-change-form').on('submit', handleStatusChangeSubmit);
        $('#new-status').on('change', handleStatusSelectChange);

        // Edit booking button
        $('#edit-booking-btn').on('click', handleEditBooking);

        // Keyboard shortcuts
        $(document).on('keydown', handleKeyboardShortcuts);

        // Auto-refresh every 5 minutes
        setInterval(function () {
            if (!$('.hme-modal:visible').length) {
                loadBookings(true); // Silent reload
            }
        }, 5 * 60 * 1000);
    }

    /**
     * Load bookings from server
     */
    function loadBookings(silent = false) {
        if (!silent) {
            showLoading();
        }

        const data = {
            action: 'hme_get_bookings',
            nonce: hme_admin.nonce,
            page: currentPage,
            per_page: 20,
            sort_field: sortField,
            sort_order: sortOrder,
            ...currentFilters
        };

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
                if (!silent) {
                    hideLoading();
                }

                if (response.success) {
                    displayBookings(response.data);
                } else {
                    showError('Failed to load bookings: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                if (!silent) {
                    hideLoading();
                }
                console.error('AJAX Error:', error);
                showError('Error connecting to server. Please try again.');
            }
        });
    }

    /**
     * Display bookings in table
     */
    function displayBookings(data) {
        let html = '';

        if (data.data && data.data.length > 0) {
            data.data.forEach(function (booking) {
                html += buildBookingRow(booking);
            });
        } else {
            html = buildEmptyStateRow();
        }

        $('#bookings-tbody').html(html);
        updatePagination(data);
        updateCounts(data.total || 0);
        updateSelectAllCheckbox();
        updateSortingUI();
    }

    /**
     * Build individual booking row HTML
     */
    function buildBookingRow(booking) {
        const nights = calculateNights(booking.check_in, booking.check_out);
        const promotionInfo = booking.promotion_code ?
            `<br><small class="promotion-discount">-${formatCurrency(booking.discount_amount)} (${booking.promotion_code})</small>` : '';

        return `
            <tr data-booking-id="${booking.id}" class="booking-row">
                <th scope="row" class="check-column">
                    <input type="checkbox" value="${booking.id}" class="booking-checkbox">
                </th>
                <td class="column-id">
                    <strong>#${booking.id}</strong>
                </td>
                <td class="column-customer">
                    <div class="customer-info">
                        <strong class="customer-name">${escapeHtml(booking.customer_name)}</strong>
                        <div class="customer-contact">
                            <a href="mailto:${escapeHtml(booking.customer_email)}" class="customer-email">
                                ${escapeHtml(booking.customer_email)}
                            </a>
                            <div class="customer-phone">${escapeHtml(booking.customer_phone)}</div>
                        </div>
                    </div>
                </td>
                <td class="column-room">
                    <span class="room-type">${escapeHtml(booking.room_type || 'N/A')}</span>
                </td>
                <td class="column-dates">
                    <div class="booking-dates">
                        <div><strong>In:</strong> ${formatDate(booking.check_in)}</div>
                        <div><strong>Out:</strong> ${formatDate(booking.check_out)}</div>
                        <small class="nights-count">${nights} night${nights !== 1 ? 's' : ''}</small>
                    </div>
                </td>
                <td class="column-guests">
                    <span class="guests-count">${booking.guests}</span>
                </td>
                <td class="column-amount">
                    <div class="amount-info">
                        <strong class="total-amount">${formatCurrency(booking.total_amount)}</strong>
                        ${promotionInfo}
                    </div>
                </td>
                <td class="column-status">
                    <span class="hme-status ${getStatusClass(booking.status)}" title="${getStatusLabel(booking.status)}">
                        ${getStatusLabel(booking.status)}
                    </span>
                </td>
                <td class="column-created">
                    <span class="created-date" title="${formatDateTime(booking.created_at)}">
                        ${formatRelativeDate(booking.created_at)}
                    </span>
                </td>
                <td class="column-actions">
                    <div class="row-actions visible">
                        <span class="view">
                            <a href="#" class="view-booking" data-id="${booking.id}" title="View Details">View</a> |
                        </span>
                        <span class="edit">
                            <a href="${getEditUrl(booking.id)}" title="Edit Booking">Edit</a> |
                        </span>
                        <span class="status">
                            <a href="#" class="change-status" data-id="${booking.id}" data-status="${booking.status}" title="Change Status">
                                Status
                            </a> |
                        </span>
                        <span class="delete">
                            <a href="#" class="delete-booking" data-id="${booking.id}" style="color: #d63638;" title="Delete Booking">
                                Delete
                            </a>
                        </span>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Build empty state row
     */
    function buildEmptyStateRow() {
        return `
            <tr>
                <td colspan="10" class="hme-no-results">
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <h3>No bookings found</h3>
                        <p>No bookings match your current criteria.</p>
                        <a href="${hme_admin.add_booking_url}" class="button button-primary">
                            <span class="dashicons dashicons-plus-alt"></span> Create New Booking
                        </a>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Event Handlers
     */
    function handleFilterBookings() {
        currentPage = 1;
        updateFilters();
        loadBookings();
    }

    function handleClearFilters() {
        $('#booking-status-filter, #booking-date-from, #booking-date-to, #booking-search').val('');
        currentFilters = {};
        currentPage = 1;
        loadBookings();
    }

    function handleExportBookings() {
        updateFilters();
        const params = new URLSearchParams({
            action: 'hme_export_bookings',
            nonce: hme_admin.nonce,
            ...currentFilters
        });

        // Create temporary link and trigger download
        const downloadUrl = `${hme_admin.ajax_url}?${params.toString()}`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `bookings-${formatDateForFilename(new Date())}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showSuccess('Export started. Download will begin shortly.');
    }

    function handleBulkAction() {
        const action = $('#bulk-action-selector-top').val();
        if (action === '-1') {
            showWarning('Please select an action');
            return;
        }

        const selectedIds = getSelectedBookingIds();
        if (selectedIds.length === 0) {
            showWarning('Please select at least one booking');
            return;
        }

        const actionLabels = {
            'confirm': 'confirm',
            'cancel': 'cancel',
            'delete': 'delete'
        };

        if (confirm(`Are you sure you want to ${actionLabels[action]} ${selectedIds.length} booking(s)?`)) {
            performBulkAction(action, selectedIds);
        }
    }

    function handleSelectAll() {
        const isChecked = $(this).prop('checked');
        $('.booking-checkbox').prop('checked', isChecked);
    }

    function handleSort() {
        const field = $(this).data('sort');
        if (sortField === field) {
            sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            sortField = field;
            sortOrder = 'asc';
        }

        loadBookings();
    }

    function handlePagination(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            loadBookings();
        }
    }

    function handleViewBooking() {
        const bookingId = $(this).data('id');
        showBookingDetails(bookingId);
    }

    function handleChangeStatus() {
        const bookingId = $(this).data('id');
        const currentStatus = $(this).data('status');
        showStatusChangeModal(bookingId, currentStatus);
    }

    function handleDeleteBooking() {
        const bookingId = $(this).data('id');
        const customerName = $(this).closest('tr').find('.customer-name').text();

        if (confirm(`Are you sure you want to delete booking #${bookingId} for ${customerName}? This action cannot be undone.`)) {
            deleteBooking(bookingId);
        }
    }

    function handleModalBackdropClick(e) {
        if (e.target === this) {
            closeModal();
        }
    }

    function handleStatusChangeSubmit(e) {
        e.preventDefault();
        updateBookingStatus();
    }

    function handleStatusSelectChange() {
        const selectedStatus = $(this).val();
        const reasonRow = $('#cancellation-reason-row');

        if (selectedStatus === 'cancelled') {
            reasonRow.show();
            $('#cancellation-reason').attr('required', true);
        } else {
            reasonRow.hide();
            $('#cancellation-reason').attr('required', false).val('');
        }
    }

    function handleEditBooking() {
        const bookingId = $(this).data('id');
        if (bookingId) {
            window.location.href = getEditUrl(bookingId);
        }
    }

    function handleKeyboardShortcuts(e) {
        // ESC key closes modals
        if (e.keyCode === 27) {
            closeModal();
        }

        // Ctrl/Cmd + N for new booking
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 78) {
            e.preventDefault();
            window.location.href = hme_admin.add_booking_url;
        }

        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
            e.preventDefault();
            $('#booking-search').focus();
        }
    }

    /**
     * Core Functions
     */
    function updateFilters() {
        currentFilters = {
            status: $('#booking-status-filter').val(),
            date_from: $('#booking-date-from').val(),
            date_to: $('#booking-date-to').val(),
            search: $('#booking-search').val()
        };

        // Remove empty filters
        Object.keys(currentFilters).forEach(key => {
            if (!currentFilters[key]) {
                delete currentFilters[key];
            }
        });
    }

    function showBookingDetails(bookingId) {
        selectedBookingId = bookingId;

        $('#booking-detail-content').html(`
            <div class="hme-loading">
                <div class="spinner is-active"></div>
                <p>Loading booking details...</p>
            </div>
        `);

        $('#booking-detail-modal').show();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_get_booking_details',
                nonce: hme_admin.nonce,
                booking_id: bookingId
            },
            success: function (response) {
                if (response.success) {
                    displayBookingDetails(response.data);
                } else {
                    $('#booking-detail-content').html(`
                        <div class="error-message">
                            <p>Error: ${response.data}</p>
                        </div>
                    `);
                }
            },
            error: function () {
                $('#booking-detail-content').html(`
                    <div class="error-message">
                        <p>Error loading booking details. Please try again.</p>
                    </div>
                `);
            }
        });
    }

    function displayBookingDetails(booking) {
        const nights = calculateNights(booking.check_in, booking.check_out);

        let promotionSection = '';
        if (booking.promotion_code) {
            promotionSection = `
                <div class="hme-detail-section">
                    <h3><span class="dashicons dashicons-tag"></span>Promotion Applied</h3>
                    <table class="hme-detail-table">
                        <tr>
                            <td><strong>Code:</strong></td>
                            <td><span class="promotion-code">${escapeHtml(booking.promotion_code)}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Discount:</strong></td>
                            <td><span class="discount-amount">-${formatCurrency(booking.discount_amount || 0)}</span></td>
                        </tr>
                    </table>
                </div>
            `;
        }

        let notesSection = '';
        if (booking.notes) {
            notesSection = `
                <div class="hme-detail-section">
                    <h3><span class="dashicons dashicons-edit"></span>Notes</h3>
                    <div class="notes-content">${escapeHtml(booking.notes).replace(/\n/g, '<br>')}</div>
                </div>
            `;
        }

        let requestsSection = '';
        if (booking.special_requests) {
            requestsSection = `
                <div class="hme-detail-section">
                    <h3><span class="dashicons dashicons-star-filled"></span>Special Requests</h3>
                    <div class="requests-content">${escapeHtml(booking.special_requests).replace(/\n/g, '<br>')}</div>
                </div>
            `;
        }

        const html = `
            <div class="hme-booking-details">
                <div class="hme-detail-section">
                    <h3><span class="dashicons dashicons-admin-users"></span>Customer Information</h3>
                    <table class="hme-detail-table">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>${escapeHtml(booking.customer_name)}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><a href="mailto:${escapeHtml(booking.customer_email)}">${escapeHtml(booking.customer_email)}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td><a href="tel:${escapeHtml(booking.customer_phone)}">${escapeHtml(booking.customer_phone)}</a></td>
                        </tr>
                    </table>
                </div>
                
                <div class="hme-detail-section">
                    <h3><span class="dashicons dashicons-calendar-alt"></span>Booking Information</h3>
                    <table class="hme-detail-table">
                        <tr>
                            <td><strong>Booking ID:</strong></td>
                            <td><span class="booking-id">#${booking.id}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Room Type:</strong></td>
                            <td>${escapeHtml(booking.room_type || 'N/A')}</td>
                        </tr>
                        <tr>
                            <td><strong>Check-in:</strong></td>
                            <td><strong>${formatDate(booking.check_in)}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Check-out:</strong></td>
                            <td><strong>${formatDate(booking.check_out)}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Duration:</strong></td>
                            <td>${nights} night${nights !== 1 ? 's' : ''}</td>
                        </tr>
                        <tr>
                            <td><strong>Guests:</strong></td>
                            <td>${booking.guests}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Amount:</strong></td>
                            <td><strong class="total-amount">${formatCurrency(booking.total_amount)}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="hme-status ${getStatusClass(booking.status)}">
                                    ${getStatusLabel(booking.status)}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>${formatDateTime(booking.created_at)}</td>
                        </tr>
                    </table>
                </div>
                
                ${promotionSection}
                ${notesSection}
                ${requestsSection}
            </div>
        `;

        $('#booking-detail-content').html(html);
        $('#edit-booking-btn').data('id', booking.id);
    }

    function showStatusChangeModal(bookingId, currentStatus) {
        selectedBookingId = bookingId;
        $('#status-booking-id').val(bookingId);
        $('#new-status').val(currentStatus);
        $('#cancellation-reason').val('');

        // Show/hide cancellation reason field
        if (currentStatus === 'cancelled') {
            $('#cancellation-reason-row').show();
        } else {
            $('#cancellation-reason-row').hide();
        }

        $('#status-change-modal').show();
    }

    function updateBookingStatus() {
        const bookingId = $('#status-booking-id').val();
        const status = $('#new-status').val();
        const reason = $('#cancellation-reason').val();

        if (status === 'cancelled' && !reason.trim()) {
            showError('Cancellation reason is required');
            return;
        }

        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_update_booking',
                nonce: hme_admin.nonce,
                booking_id: bookingId,
                update_type: 'status',
                status: status,
                cancellation_reason: reason
            },
            success: function (response) {
                hideLoading();

                if (response.success) {
                    closeModal();
                    showSuccess('Booking status updated successfully');
                    loadBookings();
                } else {
                    showError('Failed to update status: ' + response.data);
                }
            },
            error: function () {
                hideLoading();
                showError('Error updating booking status. Please try again.');
            }
        });
    }

    function deleteBooking(bookingId) {
        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_delete_booking',
                nonce: hme_admin.nonce,
                booking_id: bookingId
            },
            success: function (response) {
                hideLoading();

                if (response.success) {
                    showSuccess('Booking deleted successfully');
                    loadBookings();
                } else {
                    showError('Failed to delete booking: ' + response.data);
                }
            },
            error: function () {
                hideLoading();
                showError('Error deleting booking. Please try again.');
            }
        });
    }

    function performBulkAction(action, bookingIds) {
        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_bulk_booking_actions',
                nonce: hme_admin.nonce,
                bulk_action: action,
                booking_ids: bookingIds
            },
            success: function (response) {
                hideLoading();

                if (response.success) {
                    const result = response.data;
                    let message = `${action.charAt(0).toUpperCase() + action.slice(1)}ed ${result.processed} booking(s)`;

                    if (result.errors.length > 0) {
                        message += `. Errors: ${result.errors.join(', ')}`;
                        showWarning(message);
                    } else {
                        showSuccess(message);
                    }

                    loadBookings();
                    updateSelectAllCheckbox();
                } else {
                    showError('Bulk action failed: ' + response.data);
                }
            },
            error: function () {
                hideLoading();
                showError('Error performing bulk action. Please try again.');
            }
        });
    }

    /**
     * Utility Functions
     */
    function updatePagination(data) {
        if (!data.last_page || data.last_page <= 1) {
            $('#bookings-pagination').html('');
            return;
        }

        let paginationHtml = '';
        const totalPages = data.last_page;
        const current = data.current_page;

        // Previous page
        if (current > 1) {
            paginationHtml += `<a class="page-numbers" data-page="${current - 1}">‹</a>`;
        }

        // Page numbers
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(totalPages, current + 2);

        if (startPage > 1) {
            paginationHtml += `<a class="page-numbers" data-page="1">1</a>`;
            if (startPage > 2) {
                paginationHtml += `<span class="page-numbers dots">…</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === current) {
                paginationHtml += `<span class="page-numbers current">${i}</span>`;
            } else {
                paginationHtml += `<a class="page-numbers" data-page="${i}">${i}</a>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += `<span class="page-numbers dots">…</span>`;
            }
            paginationHtml += `<a class="page-numbers" data-page="${totalPages}">${totalPages}</a>`;
        }

        // Next page
        if (current < totalPages) {
            paginationHtml += `<a class="page-numbers" data-page="${current + 1}">›</a>`;
        }

        $('#bookings-pagination').html(paginationHtml);
    }

    function updateCounts(total) {
        const itemText = `${total} item${total !== 1 ? 's' : ''}`;
        $('#bookings-count, #bookings-count-bottom').text(itemText);
    }

    function updateSelectAllCheckbox() {
        $('#cb-select-all-1').prop('checked', false);
    }

    function updateSortingUI() {
        $('.sortable .sorting-indicator').removeClass('asc desc');
        $(`.sortable[data-sort="${sortField}"] .sorting-indicator`).addClass(sortOrder);
    }

    function getSelectedBookingIds() {
        const selectedIds = [];
        $('.booking-checkbox:checked').each(function () {
            selectedIds.push(parseInt($(this).val()));
        });
        return selectedIds;
    }

    function closeModal() {
        $('.hme-modal').hide();
        selectedBookingId = null;
    }

    function showLoading() {
        $('#hme-loading').show();
    }

    function hideLoading() {
        $('#hme-loading').hide();
    }

    // Formatting Functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatRelativeDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = now - date;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        const diffMinutes = Math.floor(diffTime / (1000 * 60));

        if (diffMinutes < 60) {
            return diffMinutes <= 1 ? 'just now' : `${diffMinutes}m ago`;
        } else if (diffHours < 24) {
            return `${diffHours}h ago`;
        } else if (diffDays < 7) {
            return `${diffDays}d ago`;
        } else {
            return formatDate(dateString);
        }
    }

    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }

    function formatDateForFilename(date) {
        return date.toISOString().split('T')[0];
    }

    function calculateNights(checkIn, checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    }

    function getStatusClass(status) {
        const classes = {
            pending: 'hme-status-pending',
            confirmed: 'hme-status-confirmed',
            cancelled: 'hme-status-cancelled',
            completed: 'hme-status-completed',
            no_show: 'hme-status-no-show'
        };
        return classes[status] || 'hme-status-unknown';
    }

    function getStatusLabel(status) {
        const labels = {
            pending: 'Chờ xác nhận',
            confirmed: 'Đã xác nhận',
            cancelled: 'Đã hủy',
            completed: 'Hoàn thành',
            no_show: 'Không đến'
        };
        return labels[status] || status;
    }

    function getEditUrl(bookingId) {
        return `${hme_admin.edit_booking_url}&id=${bookingId}`;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Notification Functions
    function showSuccess(message) {
        showNotice(message, 'success');
    }

    function showError(message) {
        showNotice(message, 'error');
    }

    function showWarning(message) {
        showNotice(message, 'warning');
    }

    function showInfo(message) {
        showNotice(message, 'info');
    }

    function showNotice(message, type = 'info') {
        const noticeClasses = {
            'success': 'notice-success',
            'error': 'notice-error',
            'warning': 'notice-warning',
            'info': 'notice-info'
        };

        const noticeClass = noticeClasses[type] || 'notice-info';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${escapeHtml(message)}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

        // Remove existing notices of the same type
        $(`.notice.${noticeClass}`).remove();

        // Insert after the page title
        $('.wrap h1').after(notice);

        // Auto dismiss after appropriate time
        const dismissTime = type === 'error' ? 10000 : 5000;
        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, dismissTime);

        // Manual dismiss handler
        notice.find('.notice-dismiss').on('click', function () {
            notice.fadeOut(() => notice.remove());
        });

        // Scroll to notice if page is scrolled
        if ($(window).scrollTop() > 100) {
            $('html, body').animate({
                scrollTop: notice.offset().top - 50
            }, 300);
        }
    }

    // Advanced booking validation functions
    function validateBookingDates(checkIn, checkOut) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);

        if (checkInDate < today) {
            return { valid: false, message: 'Check-in date cannot be in the past' };
        }

        if (checkOutDate <= checkInDate) {
            return { valid: false, message: 'Check-out date must be after check-in date' };
        }

        const maxAdvanceBooking = 365; // days
        const maxDate = new Date(today.getTime() + (maxAdvanceBooking * 24 * 60 * 60 * 1000));

        if (checkInDate > maxDate) {
            return { valid: false, message: `Bookings can only be made up to ${maxAdvanceBooking} days in advance` };
        }

        return { valid: true };
    }

    // Search and filter enhancement functions
    function highlightSearchTerms(text, searchTerm) {
        if (!searchTerm || !text) return text;

        const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\    function showError(message) {
        showNotice(message, ');
    }

    // Booking statistics functions
    function updateBookingStats() {
        // This could be called to update dashboard stats if needed
        const visibleBookings = $('#bookings-tbody tr:not(.hme-no-results)');
        const totalAmount = visibleBookings.toArray().reduce((sum, row) => {
            const amountText = $(row).find('.total-amount').text();
            const amount = parseFloat(amountText.replace(/[^\d.-]/g, ''));
            return sum + (isNaN(amount) ? 0 : amount);
        }, 0);

        // Could emit custom event for dashboard updates
        $(document).trigger('bookings:statsUpdated', {
            count: visibleBookings.length,
            totalAmount: totalAmount
        });
    }

    // Export to global scope for external access
    window.HME_BookingsList = {
        reload: loadBookings,
        showBooking: showBookingDetails,
        getCurrentFilters: () => currentFilters,
        getSelectedIds: getSelectedBookingIds
    };

    // Real-time updates via WebSocket or Server-Sent Events (if available)
    function initializeRealTimeUpdates() {
        // Check if EventSource is available and endpoint exists
        if (typeof EventSource !== 'undefined' && hme_admin.sse_endpoint) {
            const eventSource = new EventSource(hme_admin.sse_endpoint);

            eventSource.onmessage = function (event) {
                const data = JSON.parse(event.data);

                if (data.type === 'booking_updated' || data.type === 'booking_created') {
                    // Silently reload bookings if we're on the first page
                    if (currentPage === 1) {
                        loadBookings(true);
                    } else {
                        // Show notification about new updates
                        showInfo('New booking updates available. <a href="#" onclick="HME_BookingsList.reload()">Refresh</a> to see changes.');
                    }
                }
            };

            eventSource.onerror = function () {
                console.warn('Real-time updates connection lost');
            };

            // Cleanup on page unload
            $(window).on('beforeunload', function () {
                eventSource.close();
            });
        }
    }

    // Initialize real-time updates if available
    initializeRealTimeUpdates();

    // Performance optimization: debounced search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function () {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add debounced search functionality
    const debouncedSearch = debounce(function () {
        if ($('#booking-search').val() !== (currentFilters.search || '')) {
            handleFilterBookings();
        }
    }, 500);

    $('#booking-search').on('keyup', debouncedSearch);

    // Accessibility enhancements
    function enhanceAccessibility() {
        // Add ARIA labels
        $('.booking-checkbox').attr('aria-label', function () {
            const row = $(this).closest('tr');
            const customerName = row.find('.customer-name').text();
            const bookingId = row.find('.column-id strong').text();
            return `Select booking ${bookingId} for ${customerName}`;
        });

        // Add keyboard navigation for table rows
        $('#bookings-tbody tr').attr('tabindex', '0').on('keydown', function (e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
                e.preventDefault();
                $(this).find('.view-booking').click();
            }
        });

        // Screen reader announcements
        const announcer = $('<div>', {
            'aria-live': 'polite',
            'aria-atomic': 'true',
            'class': 'sr-only'
        }).appendTo('body');

        // Announce filter changes
        $(document).on('bookings:filtered', function (e, count) {
            announcer.text(`Showing ${count} bookings`);
        });
    }

    enhanceAccessibility();

    // Print functionality
    function initializePrintSupport() {
        // Add print button if needed
        if (hme_admin.show_print_button) {
            const printButton = $(`
                <button type="button" class="button" id="print-bookings">
                    <span class="dashicons dashicons-printer"></span> Print
                </button>
            `);

            $('.hme-filter-actions').append(printButton);

            printButton.on('click', function () {
                window.print();
            });
        }

        // Optimize table for printing
        $(window).on('beforeprint', function () {
            $('body').addClass('printing');
            $('.row-actions').hide();
        });

        $(window).on('afterprint', function () {
            $('body').removeClass('printing');
            $('.row-actions').show();
        });
    }

    initializePrintSupport();

})(jQuery);