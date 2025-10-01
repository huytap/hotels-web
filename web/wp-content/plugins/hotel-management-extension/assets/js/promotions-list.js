/**
 * Hotel Management Extension - Promotions List JavaScript
 */

(function ($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function () {
        initPromotionsList();
        bindEventHandlers();
        loadPromotions();
    });

    /**
     * Initialize promotions list
     */
    function initPromotionsList() {
        console.log('Promotions list initialized');
    }

    /**
     * Bind all event handlers
     */
    function bindEventHandlers() {
        // Filter button
        $('#filter-promotions').on('click', function (e) {
            e.preventDefault();
            loadPromotions();
        });

        // Clear filters button
        $('#clear-promotion-filters').on('click', function (e) {
            e.preventDefault();
            clearFilters();
        });

        // Export button
        $('#export-promotions').on('click', function (e) {
            e.preventDefault();
            exportPromotions();
        });

        // Bulk actions
        $('#do-promotion-action').on('click', function (e) {
            e.preventDefault();
            handleBulkAction();
        });

        // Select all checkbox
        $('#cb-select-all-promotions').on('change', function () {
            const isChecked = $(this).prop('checked');
            $('#promotions-tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // Search on enter
        $('#promotion-search').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                loadPromotions();
            }
        });

        // Dynamic event handlers for table rows
        $(document).on('click', '.delete-promotion', handleDeletePromotion);
        $(document).on('click', '.toggle-promotion-status', handleToggleStatus);
    }

    /**
     * Load promotions from API
     */
    function loadPromotions() {
        showLoading();

        const filters = {
            status: $('#promotion-status-filter').val(),
            type: $('#promotion-type-filter').val(),
            search: $('#promotion-search').val(),
            has_blackout: $('#has-blackout-filter').val(),
            weekdays_filter: $('#weekdays-filter').val()
        };

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_get_promotions',
                nonce: hme_admin.nonce,
                filters: filters
            },
            success: function (response) {
                hideLoading();
                if (response.success) {
                    renderPromotionsTable(response.data);
                } else {
                    showError(response.data || 'Failed to load promotions');
                }
            },
            error: function (xhr, status, error) {
                hideLoading();
                showError('Connection error: ' + error);
            }
        });
    }

    /**
     * Render promotions table
     */
    function renderPromotionsTable(data) {
        const $tbody = $('#promotions-tbody');

        if (!data || data.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="10" class="hme-no-results">
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-tag"></span>
                            <p>No promotions found.</p>
                        </div>
                    </td>
                </tr>
            `);
            $('#promotions-count').text('0 items');
            return;
        }

        let html = '';
        data.forEach(function (promo) {
            const statusClass = promo.is_active ? 'status-active' : 'status-inactive';
            const statusText = promo.is_active ? 'Active' : 'Inactive';

            html += `
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="promotion_ids[]" value="${promo.id}">
                    </th>
                    <td class="column-code"><strong>${promo.promotion_code || ''}</strong></td>
                    <td class="column-title">${promo.name || ''}</td>
                    <td class="column-type">${promo.type || ''}</td>
                    <td class="column-discount">${formatDiscount(promo)}</td>
                    <td class="column-dates">${formatDates(promo)}</td>
                    <td class="column-restrictions">${formatRestrictions(promo)}</td>
                    <td class="column-status"><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td class="column-created">${formatDate(promo.created_at)}</td>
                    <td class="column-actions">
                        <a href="${getEditUrl(promo.id)}" class="button button-small">Edit</a>
                        <button type="button" class="button button-small toggle-promotion-status" data-id="${promo.id}" data-status="${promo.is_active}">
                            ${promo.is_active ? 'Deactivate' : 'Activate'}
                        </button>
                        <button type="button" class="button button-small delete-promotion" data-id="${promo.id}">Delete</button>
                    </td>
                </tr>
            `;
        });

        $tbody.html(html);
        $('#promotions-count').text(data.length + ' items');
    }

    /**
     * Format discount display
     */
    function formatDiscount(promo) {
        if (promo.value_type === 'percentage') {
            return promo.value + '%';
        } else if (promo.value_type === 'fixed') {
            return new Intl.NumberFormat('vi-VN').format(promo.value) + ' VNĐ';
        } else if (promo.value_type === 'free_nights') {
            return promo.value + ' free nights';
        }
        return '-';
    }

    /**
     * Format dates display
     */
    function formatDates(promo) {
        if (promo.start_date && promo.end_date) {
            return formatDate(promo.start_date) + ' - ' + formatDate(promo.end_date);
        }
        return '-';
    }

    /**
     * Format restrictions display
     */
    function formatRestrictions(promo) {
        const restrictions = [];
        if (promo.min_stay) restrictions.push('Min ' + promo.min_stay + ' nights');
        if (promo.min_booking_amount) restrictions.push('Min ' + promo.min_booking_amount + ' VNĐ');
        return restrictions.length > 0 ? restrictions.join(', ') : '-';
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('vi-VN');
    }

    /**
     * Get edit URL
     */
    function getEditUrl(id) {
        return hme_admin.admin_url + 'admin.php?page=hotel-promotions&action=edit&id=' + id;
    }

    /**
     * Clear all filters
     */
    function clearFilters() {
        $('#promotion-status-filter').val('');
        $('#promotion-type-filter').val('');
        $('#promotion-search').val('');
        $('#has-blackout-filter').val('');
        $('#weekdays-filter').val('');
        loadPromotions();
    }

    /**
     * Export promotions to CSV
     */
    function exportPromotions() {
        const filters = {
            status: $('#promotion-status-filter').val(),
            type: $('#promotion-type-filter').val(),
            search: $('#promotion-search').val()
        };

        const form = $('<form>', {
            method: 'POST',
            action: hme_admin.ajax_url,
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: 'action', value: 'hme_export_promotions' }));
        form.append($('<input>', { type: 'hidden', name: 'nonce', value: hme_admin.nonce }));
        form.append($('<input>', { type: 'hidden', name: 'filters', value: JSON.stringify(filters) }));

        form.appendTo('body').submit().remove();
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction() {
        const action = $('#bulk-promotion-action-selector-top').val();
        if (!action || action === '-1') {
            showError('Please select an action');
            return;
        }

        const selectedIds = [];
        $('#promotions-tbody input[type="checkbox"]:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            showError('Please select at least one promotion');
            return;
        }

        if (!confirm('Are you sure you want to ' + action + ' ' + selectedIds.length + ' promotion(s)?')) {
            return;
        }

        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_bulk_promotion_action',
                nonce: hme_admin.nonce,
                bulk_action: action,
                promotion_ids: selectedIds
            },
            success: function (response) {
                hideLoading();
                if (response.success) {
                    showSuccess(response.data || 'Action completed successfully');
                    loadPromotions();
                } else {
                    showError(response.data || 'Action failed');
                }
            },
            error: function () {
                hideLoading();
                showError('Connection error');
            }
        });
    }

    /**
     * Handle delete promotion
     */
    function handleDeletePromotion() {
        const promotionId = $(this).data('id');

        if (!confirm('Are you sure you want to delete this promotion?')) {
            return;
        }

        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_delete_promotion',
                nonce: hme_admin.nonce,
                promotion_id: promotionId
            },
            success: function (response) {
                hideLoading();
                if (response.success) {
                    showSuccess('Promotion deleted successfully');
                    loadPromotions();
                } else {
                    showError(response.data || 'Failed to delete promotion');
                }
            },
            error: function () {
                hideLoading();
                showError('Connection error');
            }
        });
    }

    /**
     * Handle toggle promotion status
     */
    function handleToggleStatus() {
        const promotionId = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus ? 0 : 1;

        showLoading();

        $.ajax({
            url: hme_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'hme_toggle_promotion_status',
                nonce: hme_admin.nonce,
                promotion_id: promotionId,
                status: newStatus
            },
            success: function (response) {
                hideLoading();
                if (response.success) {
                    showSuccess('Status updated successfully');
                    loadPromotions();
                } else {
                    showError(response.data || 'Failed to update status');
                }
            },
            error: function () {
                hideLoading();
                showError('Connection error');
            }
        });
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        $('#hme-loading').show();
    }

    /**
     * Hide loading indicator
     */
    function hideLoading() {
        $('#hme-loading').hide();
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        if (typeof HME !== 'undefined' && HME.UI) {
            HME.UI.showSuccess(message);
        } else {
            alert(message);
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (typeof HME !== 'undefined' && HME.UI) {
            HME.UI.showError(message);
        } else {
            alert(message);
        }
    }

})(jQuery);