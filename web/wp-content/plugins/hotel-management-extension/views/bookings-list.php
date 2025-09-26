<?php

/**
 * Bookings List View
 * File: views/bookings-list.php
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

$booking_statuses = HME_Booking_Manager::get_booking_statuses();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-calendar-alt"></span>
        Booking Management
    </h1>
    <a href="<?php echo admin_url('admin.php?page=hotel-bookings&action=add'); ?>" class="page-title-action">
        <span class="dashicons dashicons-plus-alt"></span> Add New Booking
    </a>

    <!-- Loading Indicator -->
    <div id="hme-loading" class="hme-loading-overlay" style="display: none;">
        <div class="hme-loading-spinner">
            <div class="spinner is-active"></div>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="hme-filters-section">
        <div class="hme-filters-row">
            <div class="hme-filter-group">
                <label for="booking-status-filter">Status:</label>
                <select id="booking-status-filter" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($booking_statuses as $status => $label): ?>
                        <option value="<?php echo esc_attr($status); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hme-filter-group">
                <label for="booking-date-from">Check-in From:</label>
                <input type="date" id="booking-date-from" name="date_from">
            </div>

            <div class="hme-filter-group">
                <label for="booking-date-to">Check-in To:</label>
                <input type="date" id="booking-date-to" name="date_to">
            </div>

            <div class="hme-filter-group">
                <label for="booking-search">Search:</label>
                <input type="text" id="booking-search" name="search" placeholder="Name, email, phone..." class="regular-text">
            </div>

            <div class="hme-filter-actions">
                <button type="button" id="filter-bookings" class="button">
                    <span class="dashicons dashicons-search"></span> Filter
                </button>
                <button type="button" id="clear-filters" class="button">
                    <span class="dashicons dashicons-dismiss"></span> Clear
                </button>
                <button type="button" id="export-bookings" class="button">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select id="bulk-action-selector-top">
                <option value="-1">Bulk actions</option>
                <option value="confirm">Confirm Selected</option>
                <option value="cancel">Cancel Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <input type="submit" id="doaction" class="button action" value="Apply">
        </div>

        <div class="alignright">
            <span class="displaying-num" id="bookings-count">0 items</span>
        </div>
    </div>

    <!-- Bookings Table -->
    <table class="wp-list-table widefat fixed striped" id="bookings-table">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th scope="col" class="manage-column column-id sortable" data-sort="id">
                    <a><span>ID</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-customer">Customer</th>
                <th scope="col" class="manage-column column-room">Room Type</th>
                <th scope="col" class="manage-column column-dates sortable" data-sort="check_in">
                    <a><span>Dates</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-guests">Guests</th>
                <th scope="col" class="manage-column column-amount sortable" data-sort="total_amount">
                    <a><span>Amount</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-status">Status</th>
                <th scope="col" class="manage-column column-created sortable" data-sort="created_at">
                    <a><span>Created</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-actions">Actions</th>
            </tr>
        </thead>

        <tbody id="bookings-tbody">
            <tr>
                <td colspan="10" class="hme-no-results">
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <p>No bookings found. <a href="<?php echo admin_url('admin.php?page=hotel-bookings&action=add'); ?>">Create your first booking</a></p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages" id="bookings-pagination">
            <span class="displaying-num" id="bookings-count-bottom">0 items</span>
        </div>
    </div>
</div>

<!-- Booking Detail Modal -->
<div id="booking-detail-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h2><span class="dashicons dashicons-calendar-alt"></span> Booking Details</h2>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body" id="booking-detail-content">
            <div class="hme-loading">
                <div class="spinner is-active"></div>
                <p>Loading booking details...</p>
            </div>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Close</button>
            <button type="button" class="button button-primary" id="edit-booking-btn">Edit Booking</button>
        </div>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div id="status-change-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-small">
        <div class="hme-modal-header">
            <h3>Change Booking Status</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="status-change-form">
                <input type="hidden" id="status-booking-id">
                <table class="form-table">
                    <tr>
                        <th><label for="new-status">New Status:</label></th>
                        <td>
                            <select id="new-status" name="status" required>
                                <?php foreach ($booking_statuses as $status => $label): ?>
                                    <option value="<?php echo esc_attr($status); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="cancellation-reason-row" style="display: none;">
                        <th><label for="cancellation-reason">Reason:</label></th>
                        <td>
                            <textarea id="cancellation-reason" name="cancellation_reason" rows="3" class="large-text" placeholder="Reason for cancellation..."></textarea>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="status-change-form" class="button button-primary">Update Status</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let currentFilters = {};
        let sortField = 'created_at';
        let sortOrder = 'desc';

        // Initialize
        loadBookings();

        // Event Listeners
        $('#filter-bookings').on('click', function() {
            currentPage = 1;
            updateFilters();
            loadBookings();
        });

        $('#clear-filters').on('click', function() {
            $('#booking-status-filter, #booking-date-from, #booking-date-to, #booking-search').val('');
            currentFilters = {};
            currentPage = 1;
            loadBookings();
        });

        // Export bookings
        $('#export-bookings').on('click', function() {
            exportBookings();
        });

        // Bulk actions
        $('#doaction').on('click', function() {
            const action = $('#bulk-action-selector-top').val();
            if (action === '-1') {
                alert('Please select an action');
                return;
            }

            const selectedIds = [];
            $('#bookings-tbody input[type="checkbox"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Please select at least one booking');
                return;
            }

            if (confirm(`Are you sure you want to ${action} ${selectedIds.length} booking(s)?`)) {
                bulkAction(action, selectedIds);
            }
        });

        // Select all checkbox
        $('#cb-select-all-1').on('change', function() {
            $('#bookings-tbody input[type="checkbox"]').prop('checked', $(this).prop('checked'));
        });

        // Sortable columns
        $('.sortable').on('click', function() {
            const field = $(this).data('sort');
            if (sortField === field) {
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortOrder = 'asc';
            }

            // Update UI
            $('.sortable .sorting-indicator').removeClass('asc desc');
            $(this).find('.sorting-indicator').addClass(sortOrder);

            loadBookings();
        });

        // Pagination click
        $(document).on('click', '.page-numbers', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== currentPage) {
                currentPage = page;
                loadBookings();
            }
        });

        // View booking details
        $(document).on('click', '.view-booking', function() {
            const bookingId = $(this).data('id');
            showBookingDetails(bookingId);
        });

        // Quick status change
        $(document).on('click', '.change-status', function() {
            const bookingId = $(this).data('id');
            const currentStatus = $(this).data('status');
            showStatusChangeModal(bookingId, currentStatus);
        });

        // Delete booking
        $(document).on('click', '.delete-booking', function() {
            const bookingId = $(this).data('id');
            if (confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
                deleteBooking(bookingId);
            }
        });

        // Modal events
        $('.hme-modal-close').on('click', function() {
            $(this).closest('.hme-modal').hide();
        });

        // Status change form
        $('#status-change-form').on('submit', function(e) {
            e.preventDefault();
            updateBookingStatus();
        });

        $('#new-status').on('change', function() {
            if ($(this).val() === 'cancelled') {
                $('#cancellation-reason-row').show();
            } else {
                $('#cancellation-reason-row').hide();
            }
        });

        // Functions
        function updateFilters() {
            currentFilters = {
                status: $('#booking-status-filter').val(),
                date_from: $('#booking-date-from').val(),
                date_to: $('#booking-date-to').val(),
                search: $('#booking-search').val()
            };
        }

        function loadBookings() {
            showLoading();

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
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        displayBookings(response.data);
                    } else {
                        showError('Failed to load bookings: ' + response.data);
                    }
                },
                error: function() {
                    hideLoading();
                    showError('Error connecting to server');
                }
            });
        }

        function displayBookings(data) {
            let html = '';

            if (data.data && data.data.length > 0) {
                data.data.forEach(function(booking) {
                    const formatted = formatBookingRow(booking);
                    html += `
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" value="${booking.id}">
                        </th>
                        <td class="column-id"><strong>#${booking.id}</strong></td>
                        <td class="column-customer">
                            <strong>${booking.customer_name}</strong><br>
                            <small><a href="mailto:${booking.customer_email}">${booking.customer_email}</a></small><br>
                            <small>${booking.customer_phone}</small>
                        </td>
                        <td class="column-room">${booking.room_type || 'N/A'}</td>
                        <td class="column-dates">
                            <strong>In:</strong> ${formatted.check_in_formatted}<br>
                            <strong>Out:</strong> ${formatted.check_out_formatted}<br>
                            <small>${booking.nights || 0} night(s)</small>
                        </td>
                        <td class="column-guests">${booking.guests}</td>
                        <td class="column-amount">
                            <strong>${formatted.total_amount_formatted}</strong>
                            ${booking.discount_amount > 0 ? `<br><small>-${formatCurrency(booking.discount_amount)} (${booking.promotion_code})</small>` : ''}
                        </td>
                        <td class="column-status">
                            <span class="hme-status ${formatted.status_class}">${formatted.status_label}</span>
                        </td>
                        <td class="column-created">
                            ${formatted.created_at_formatted}
                        </td>
                        <td class="column-actions">
                            <div class="row-actions">
                                <span class="view">
                                    <a href="#" class="view-booking" data-id="${booking.id}">View</a> |
                                </span>
                                <span class="edit">
                                    <a href="${getEditUrl(booking.id)}">Edit</a> |
                                </span>
                                <span class="status">
                                    <a href="#" class="change-status" data-id="${booking.id}" data-status="${booking.status}">Status</a> |
                                </span>
                                <span class="delete">
                                    <a href="#" class="delete-booking" data-id="${booking.id}" style="color: #d63638;">Delete</a>
                                </span>
                            </div>
                        </td>
                    </tr>
                `;
                });
            } else {
                html = `
                <tr>
                    <td colspan="10" class="hme-no-results">
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <p>No bookings found matching your criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            }

            $('#bookings-tbody').html(html);

            // Update pagination
            updatePagination(data);

            // Update counts
            const total = data.total || 0;
            $('#bookings-count, #bookings-count-bottom').text(`${total} item${total !== 1 ? 's' : ''}`);

            // Uncheck select all
            $('#cb-select-all-1').prop('checked', false);
        }

        function formatBookingRow(booking) {
            return {
                check_in_formatted: formatDate(booking.check_in),
                check_out_formatted: formatDate(booking.check_out),
                total_amount_formatted: formatCurrency(booking.total_amount),
                status_class: getStatusClass(booking.status),
                status_label: getStatusLabel(booking.status),
                created_at_formatted: formatDateTime(booking.created_at)
            };
        }

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

        function showBookingDetails(bookingId) {
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
                success: function(response) {
                    if (response.success) {
                        displayBookingDetails(response.data);
                    } else {
                        $('#booking-detail-content').html(`<p class="error">Error: ${response.data}</p>`);
                    }
                },
                error: function() {
                    $('#booking-detail-content').html('<p class="error">Error loading booking details</p>');
                }
            });
        }

        function displayBookingDetails(booking) {
            const html = `
            <div class="hme-booking-details">
                <div class="hme-detail-section">
                    <h3>Customer Information</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Name:</strong></td><td>${booking.customer_name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td><a href="mailto:${booking.customer_email}">${booking.customer_email}</a></td></tr>
                        <tr><td><strong>Phone:</strong></td><td>${booking.customer_phone}</td></tr>
                    </table>
                </div>
                
                <div class="hme-detail-section">
                    <h3>Booking Information</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Room Type:</strong></td><td>${booking.room_type || 'N/A'}</td></tr>
                        <tr><td><strong>Check-in:</strong></td><td>${formatDate(booking.check_in)}</td></tr>
                        <tr><td><strong>Check-out:</strong></td><td>${formatDate(booking.check_out)}</td></tr>
                        <tr><td><strong>Guests:</strong></td><td>${booking.guests}</td></tr>
                        <tr><td><strong>Total Amount:</strong></td><td><strong>${formatCurrency(booking.total_amount)}</strong></td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="hme-status ${getStatusClass(booking.status)}">${getStatusLabel(booking.status)}</span></td></tr>
                        <tr><td><strong>Created:</strong></td><td>${formatDateTime(booking.created_at)}</td></tr>
                    </table>
                </div>
                
                ${booking.promotion_code ? `
                <div class="hme-detail-section">
                    <h3>Promotion</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Code:</strong></td><td>${booking.promotion_code}</td></tr>
                        <tr><td><strong>Discount:</strong></td><td>-${formatCurrency(booking.discount_amount || 0)}</td></tr>
                    </table>
                </div>
                ` : ''}
                
                ${booking.notes ? `
                <div class="hme-detail-section">
                    <h3>Notes</h3>
                    <p>${booking.notes}</p>
                </div>
                ` : ''}
                
                ${booking.special_requests ? `
                <div class="hme-detail-section">
                    <h3>Special Requests</h3>
                    <p>${booking.special_requests}</p>
                </div>
                ` : ''}
            </div>
        `;

            $('#booking-detail-content').html(html);
            $('#edit-booking-btn').data('id', booking.id);
        }

        function showStatusChangeModal(bookingId, currentStatus) {
            $('#status-booking-id').val(bookingId);
            $('#new-status').val(currentStatus);
            $('#cancellation-reason').val('');

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
                success: function(response) {
                    if (response.success) {
                        $('#status-change-modal').hide();
                        showSuccess('Booking status updated successfully');
                        loadBookings(); // Reload table
                    } else {
                        showError('Failed to update status: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error updating booking status');
                }
            });
        }

        function deleteBooking(bookingId) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_delete_booking',
                    nonce: hme_admin.nonce,
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess('Booking deleted successfully');
                        loadBookings();
                    } else {
                        showError('Failed to delete booking: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error deleting booking');
                }
            });
        }

        function bulkAction(action, bookingIds) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_bulk_booking_actions',
                    nonce: hme_admin.nonce,
                    bulk_action: action,
                    booking_ids: bookingIds
                },
                success: function(response) {
                    if (response.success) {
                        const result = response.data;
                        let message = `${action.charAt(0).toUpperCase() + action.slice(1)}ed ${result.processed} booking(s)`;

                        if (result.errors.length > 0) {
                            message += `. Errors: ${result.errors.join(', ')}`;
                        }

                        showSuccess(message);
                        loadBookings();
                    } else {
                        showError('Bulk action failed: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error performing bulk action');
                }
            });
        }

        function exportBookings() {
            const params = new URLSearchParams({
                action: 'hme_export_bookings',
                nonce: hme_admin.nonce,
                ...currentFilters
            });

            window.location.href = `${hme_admin.ajax_url}?${params.toString()}`;
        }

        // Utility functions
        // Use shared utility functions from HME_Utils
        function formatCurrency(amount) {
            return HME_Utils.formatCurrency(amount);
        }

        function formatDate(dateString) {
            return HME_Utils.formatDate(dateString);
        }

        function formatDateTime(dateString) {
            return HME_Utils.formatDateTime(dateString);
        }

        function getStatusClass(status) {
            return HME_Utils.getStatusClass(status);
        }

        function getStatusLabel(status) {
            return HME_Utils.getStatusLabel(status);
        }

        function getEditUrl(bookingId) {
            return `<?php echo admin_url('admin.php?page=hotel-bookings&action=edit'); ?>&id=${bookingId}`;
        }

        function showLoading() {
            HME_Utils.showLoading();
        }

        function hideLoading() {
            HME_Utils.hideLoading();
        }

        function showSuccess(message) {
            HME_Utils.showSuccess(message);
        }

        function showError(message) {
            HME_Utils.showError(message);
        }

        function showNotice(message, type) {
            HME_Utils.showNotice(message, type);
        }
    });
</script>