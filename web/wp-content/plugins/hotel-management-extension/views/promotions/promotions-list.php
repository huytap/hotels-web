<?php

/**
 * Promotions List View
 * File: views/promotions-list.php
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

$promotion_types = HME_Promotion_Manager::get_promotion_types();
$promotion_statuses = HME_Promotion_Manager::get_promotion_statuses();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-tag"></span>
        Promotion Management
    </h1>
    <a href="<?php echo admin_url('admin.php?page=hotel-promotions&action=add'); ?>" class="page-title-action">
        <span class="dashicons dashicons-plus-alt"></span> Add New Promotion
    </a>
    <!-- <button type="button" class="page-title-action" id="generate-code-btn">
        <span class="dashicons dashicons-randomize"></span> Generate Code
    </button> -->

    <!-- Loading Indicator -->
    <div id="hme-loading" class="hme-loading-overlay" style="display: none;">
        <div class="hme-loading-spinner">
            <div class="spinner is-active"></div>
            <p>Loading promotions...</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="hme-filters-section">
        <div class="hme-filters-row">
            <div class="hme-filter-group">
                <label for="promotion-status-filter">Status:</label>
                <select id="promotion-status-filter" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($promotion_statuses as $status => $label): ?>
                        <option value="<?php echo esc_attr($status); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hme-filter-group">
                <label for="promotion-type-filter">Type:</label>
                <select id="promotion-type-filter" name="type">
                    <option value="">All Types</option>
                    <?php foreach ($promotion_types as $type => $label): ?>
                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hme-filter-group">
                <label for="promotion-search">Search:</label>
                <input type="text" id="promotion-search" name="search" placeholder="Code, title..." class="regular-text">
            </div>

            <div class="hme-filter-actions">
                <button type="button" id="filter-promotions" class="button">
                    <span class="dashicons dashicons-search"></span> Filter
                </button>
                <button type="button" id="clear-promotion-filters" class="button">
                    <span class="dashicons dashicons-dismiss"></span> Clear
                </button>
                <button type="button" id="export-promotions" class="button">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-promotion-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select id="bulk-promotion-action-selector-top">
                <option value="-1">Bulk actions</option>
                <option value="activate">Activate Selected</option>
                <option value="deactivate">Deactivate Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <input type="submit" id="do-promotion-action" class="button action" value="Apply">
        </div>

        <div class="alignright">
            <span class="displaying-num" id="promotions-count">0 items</span>
        </div>
    </div>

    <!-- Promotions Table -->
    <table class="wp-list-table widefat fixed striped" id="promotions-table">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-promotions">Select All</label>
                    <input id="cb-select-all-promotions" type="checkbox">
                </td>
                <th scope="col" class="manage-column column-code sortable" data-sort="code">
                    <a><span>Code</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-title">Title</th>
                <th scope="col" class="manage-column column-type">Type</th>
                <th scope="col" class="manage-column column-discount">Discount</th>
                <th scope="col" class="manage-column column-dates sortable" data-sort="start_date">
                    <a><span>Valid Dates</span><span class="sorting-indicator"></span></a>
                </th>
                <!-- <th scope="col" class="manage-column column-usage">Usage</th> -->
                <th scope="col" class="manage-column column-status">Status</th>
                <th scope="col" class="manage-column column-created sortable" data-sort="created_at">
                    <a><span>Created</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" class="manage-column column-actions">Actions</th>
            </tr>
        </thead>

        <tbody id="promotions-tbody">
            <tr>
                <td colspan="10" class="hme-no-results">
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-tag"></span>
                        <p>No promotions found. <a href="<?php echo admin_url('admin.php?page=hotel-promotions&action=add'); ?>">Create your first promotion</a></p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages" id="promotions-pagination">
            <span class="displaying-num" id="promotions-count-bottom">0 items</span>
        </div>
    </div>
</div>

<!-- Promotion Detail Modal -->
<div id="promotion-detail-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h2><span class="dashicons dashicons-tag"></span> Promotion Details</h2>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body" id="promotion-detail-content">
            <div class="hme-loading">
                <div class="spinner is-active"></div>
                <p><?php _e('Loading promotion details...', 'hotel'); ?></p>
            </div>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close"><?php _e('Close', 'hotel'); ?></button>
            <button type="button" class="button button-primary" id="edit-promotion-btn"><?php _e('Edit Promotion', 'hotel'); ?></button>
            <button type="button" class="button" id="duplicate-promotion-btn"><?php _e('Duplicate', 'hotel'); ?></button>
            <!-- <button type="button" class="button" id="view-usage-btn">View Usage</button> -->
        </div>
    </div>
</div>

<!-- Usage History Modal -->
<div id="usage-history-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3>Promotion Usage History</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <div id="usage-history-content">
                <div class="hme-loading">
                    <div class="spinner is-active"></div>
                    <p>Loading usage history...</p>
                </div>
            </div>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Close</button>
            <button type="button" class="button" id="export-usage-history">Export Usage</button>
        </div>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div id="promotion-status-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-small">
        <div class="hme-modal-header">
            <h3>Change Promotion Status</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="promotion-status-form">
                <input type="hidden" id="status-promotion-id">
                <table class="form-table">
                    <tr>
                        <th><label for="promotion-is-active">Status:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="promotion-is-active" name="is_active">
                                Active
                            </label>
                            <p class="description">Inactive promotions cannot be used by customers.</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="promotion-status-form" class="button button-primary">Update Status</button>
        </div>
    </div>
</div>

<!-- Code Generator Modal -->
<div id="code-generator-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-small">
        <div class="hme-modal-header">
            <h3>Generate Promotion Code</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="code-generator-form">
                <table class="form-table">
                    <tr>
                        <th><label for="code-prefix">Prefix:</label></th>
                        <td>
                            <input type="text" id="code-prefix" name="prefix" class="regular-text" placeholder="e.g., SUMMER, VIP">
                            <p class="description">Optional prefix for the code</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="code-length">Length:</label></th>
                        <td>
                            <input type="number" id="code-length" name="length" min="4" max="20" value="8" class="small-text">
                            <p class="description">Total length including prefix</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="code-type">Character Type:</label></th>
                        <td>
                            <select id="code-type" name="type">
                                <option value="alphanumeric">Alphanumeric (A-Z, 0-9)</option>
                                <option value="alphabetic">Alphabetic (A-Z only)</option>
                                <option value="numeric">Numeric (0-9 only)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Generated Code:</label></th>
                        <td>
                            <div id="generated-code-display" class="hme-generated-code">
                                <input type="text" id="generated-code" readonly class="regular-text">
                                <button type="button" id="regenerate-code" class="button">Regenerate</button>
                                <button type="button" id="copy-code" class="button">Copy</button>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Close</button>
            <button type="button" id="use-generated-code" class="button button-primary">Use This Code</button>
        </div>
    </div>
</div>

<!-- Duplicate Promotion Modal -->
<div id="duplicate-promotion-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-small">
        <div class="hme-modal-header">
            <h3>Duplicate Promotion</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="duplicate-promotion-form">
                <input type="hidden" id="duplicate-source-id">
                <table class="form-table">
                    <tr>
                        <th><label for="duplicate-new-code" class="required">New Code *:</label></th>
                        <td>
                            <input type="text" id="duplicate-new-code" name="new_code" class="regular-text" required>
                            <p class="description">The new promotion code must be unique</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Source Promotion:</label></th>
                        <td>
                            <strong id="duplicate-source-title"></strong>
                            <p class="description">All settings will be copied from this promotion</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="duplicate-promotion-form" class="button button-primary">Create Duplicate</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let currentFilters = {};
        let sortField = 'created_at';
        let sortOrder = 'desc';
        let lang = '<?php echo get_locale() ?>';
        // Initialize
        loadPromotions();

        // Event Listeners
        $('#filter-promotions').on('click', function() {
            currentPage = 1;
            updateFilters();
            loadPromotions();
        });

        $('#clear-promotion-filters').on('click', function() {
            $('#promotion-status-filter, #promotion-type-filter, #promotion-search').val('');
            currentFilters = {};
            currentPage = 1;
            loadPromotions();
        });

        // Export promotions
        $('#export-promotions').on('click', function() {
            exportPromotions();
        });

        // Bulk actions
        $('#do-promotion-action').on('click', function() {
            const action = $('#bulk-promotion-action-selector-top').val();
            if (action === '-1') {
                alert('Please select an action');
                return;
            }

            const selectedIds = [];
            $('#promotions-tbody input[type="checkbox"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Please select at least one promotion');
                return;
            }

            if (confirm(`Are you sure you want to ${action} ${selectedIds.length} promotion(s)?`)) {
                bulkPromotionAction(action, selectedIds);
            }
        });

        // Select all checkbox
        $('#cb-select-all-promotions').on('change', function() {
            $('#promotions-tbody input[type="checkbox"]').prop('checked', $(this).prop('checked'));
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

            loadPromotions();
        });

        // Pagination click
        $(document).on('click', '.page-numbers', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== currentPage) {
                currentPage = page;
                loadPromotions();
            }
        });

        // View promotion details
        $(document).on('click', '.view-promotion', function() {
            const promotionId = $(this).data('id');
            showPromotionDetails(promotionId);
        });

        // Quick status change
        $(document).on('click', '.change-promotion-status', function() {
            const promotionId = $(this).data('id');
            const currentStatus = $(this).data('active') === '1';
            showPromotionStatusModal(promotionId, currentStatus);
        });

        // Delete promotion
        $(document).on('click', '.delete-promotion', function() {
            const promotionId = $(this).data('id');
            if (confirm('Are you sure you want to delete this promotion? This action cannot be undone.')) {
                deletePromotion(promotionId);
            }
        });

        // Duplicate promotion
        $(document).on('click', '.duplicate-promotion', function() {
            const promotionId = $(this).data('id');
            const promotionTitle = $(this).data('title');
            showDuplicateModal(promotionId, promotionTitle);
        });

        // Code generator
        $('#generate-code-btn').on('click', function() {
            showCodeGeneratorModal();
        });

        $('#regenerate-code, #code-prefix, #code-length, #code-type').on('change keyup', function() {
            generatePromotionCode();
        });

        $('#copy-code').on('click', function() {
            const code = $('#generated-code').val();
            if (code) {
                navigator.clipboard.writeText(code).then(function() {
                    showSuccess('Code copied to clipboard');
                });
            }
        });

        $('#use-generated-code').on('click', function() {
            const code = $('#generated-code').val();
            if (code) {
                // Redirect to add promotion page with pre-filled code
                window.location.href = `<?php echo admin_url('admin.php?page=hotel-promotions&action=add'); ?>&code=${encodeURIComponent(code)}`;
            }
        });

        // Modal events
        $('.hme-modal-close').on('click', function() {
            $(this).closest('.hme-modal').hide();
        });

        // Form submissions
        $('#promotion-status-form').on('submit', function(e) {
            e.preventDefault();
            updatePromotionStatus();
        });

        $('#duplicate-promotion-form').on('submit', function(e) {
            e.preventDefault();
            duplicatePromotion();
        });

        // View usage history
        $('#view-usage-btn').on('click', function() {
            const promotionId = $(this).data('id');
            showUsageHistory(promotionId);
        });

        // Functions
        function updateFilters() {
            currentFilters = {
                status: $('#promotion-status-filter').val(),
                type: $('#promotion-type-filter').val(),
                search: $('#promotion-search').val()
            };
        }

        function loadPromotions() {
            //showLoading();

            const data = {
                action: 'hme_get_promotions',
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
                    //hideLoading();
                    if (response.success) {
                        displayPromotions(response.data);
                    } else {
                        showError('Failed to load promotions: ' + response.data);
                    }
                },
                error: function() {
                    //hideLoading();
                    showError('Error connecting to server');
                }
            });
        }

        function displayPromotions(data) {
            let html = '';

            if (data.data && data.data.length > 0) {
                data.data.forEach(function(promotion) {
                    const formatted = formatPromotionForDisplay(promotion);
                    html += `
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" value="${promotion.id}">
                        </th>
                        <td class="column-code">
                            <strong>${promotion.promotion_code}</strong>
                            <div class="row-actions visible">
                                <span class="view">
                                    <a href="#" class="view-promotion" data-id="${promotion.id}">View</a> |
                                </span>
                                <span class="edit">
                                    <a href="${getEditUrl(promotion.id)}">Edit</a> |
                                </span>
                                <span class="duplicate">
                                    <a href="#" class="duplicate-promotion" data-id="${promotion.id}" data-title="${promotion.title}">Duplicate</a> |
                                </span>
                                <span class="status">
                                    <a href="#" class="change-promotion-status" data-id="${promotion.id}" data-active="${promotion.is_active ? '1' : '0'}">
                                        ${promotion.is_active ? 'Deactivate' : 'Activate'}
                                    </a> |
                                </span>
                                <span class="delete">
                                    <a href="#" class="delete-promotion" data-id="${promotion.id}" style="color: #d63638;">Delete</a>
                                </span>
                            </div>
                        </td>
                        <td class="column-title">
                            <strong>${promotion.name[lang]}</strong>
                            ${promotion.description ? `<br><small>${promotion.description[lang]}</small>` : ''}
                        </td>
                        <td class="column-type">${formatted.type}</td>
                        <td class="column-discount">
                            ${formatted.discount_formatted}
                        </td>
                        <td class="column-dates">
                            <strong>Start:</strong> ${formatted.start_date_formatted}<br>
                            <strong>End:</strong> ${formatted.end_date_formatted}
                        </td>
                        <td class="column-status">
                            <span class="hme-status ${formatted.status_class}">${formatted.status_label}</span>
                        </td>
                        <td class="column-created">
                            ${formatted.created_at_formatted}
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small view-promotion" data-id="${promotion.id}">
                                View
                            </button>
                        </td>
                    </tr>
                `;
                });
            } else {
                html = `
                <tr>
                    <td colspan="10" class="hme-no-results">
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-tag"></span>
                            <p>No promotions found matching your criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            }

            $('#promotions-tbody').html(html);

            // Update pagination
            updatePagination(data);

            // Update counts
            const total = data.total || 0;
            $('#promotions-count, #promotions-count-bottom').text(`${total} item${total !== 1 ? 's' : ''}`);

            // Uncheck select all
            $('#cb-select-all-promotions').prop('checked', false);
        }

        function formatPromotionForDisplay(promotion) {
            const status = calculatePromotionStatus(promotion);

            return {
                discount_formatted: formatDiscountValue(promotion.value_type, promotion.value),
                start_date_formatted: formatDate(promotion.start_date),
                end_date_formatted: formatDate(promotion.end_date),
                usage_count: promotion.used_count || 0,
                usage_remaining: promotion.usage_limit > 0 ? Math.max(0, promotion.usage_limit - (promotion.used_count || 0)) : '∞',
                status: status,
                status_label: getPromotionStatusLabel(status),
                status_class: getPromotionStatusClass(status),
                created_at_formatted: formatDateTime(promotion.created_at),
                type: loadPromotionType(promotion.type)
            };
        }

        function loadPromotionType(type) {
            const promotionTypes = {
                'early_bird': 'Early Bird',
                'last_minutes': 'Last Minutes',
                'others': 'Others' // Thêm một key cho giá trị mặc định
            };

            return promotionTypes[type] || promotionTypes['others'];
        }

        function calculatePromotionStatus(promotion) {
            const now = new Date();
            const startDate = new Date(promotion.start_date);
            const endDate = new Date(promotion.end_date);

            if (!promotion.is_active) {
                return 'inactive';
            }

            if (now < startDate) {
                return 'upcoming';
            }

            if (now > endDate) {
                return 'expired';
            }

            if (promotion.usage_limit > 0 && (promotion.used_count || 0) >= promotion.usage_limit) {
                return 'used_up';
            }

            return 'active';
        }

        function updatePagination(data) {
            if (!data.last_page || data.last_page <= 1) {
                $('#promotions-pagination').html('');
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

            $('#promotions-pagination').html(paginationHtml);
        }

        function showPromotionDetails(promotionId) {
            $('#promotion-detail-content').html(`
            <div class="hme-loading">
                <div class="spinner is-active"></div>
                <p>Loading promotion details...</p>
            </div>
        `);
            $('#promotion-detail-modal').show();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'GET',
                data: {
                    action: 'hme_get_promotion',
                    nonce: hme_admin.nonce,
                    promotion_id: promotionId
                },
                success: function(response) {
                    if (response.success) {
                        displayPromotionDetails(response.data.data);
                    } else {
                        $('#promotion-detail-content').html(`<p class="error">Error: ${response.data}</p>`);
                    }
                },
                error: function() {
                    $('#promotion-detail-content').html('<p class="error">Error loading promotion details</p>');
                }
            });
        }

        function getRoomTypeName(roomType, lang) {
            if (roomType.title && roomType.title[lang]) {
                return roomType.title[lang];
            }
            // Fallback to English or a default if the preferred language isn't available
            return roomType.name.en || 'N/A';
        }

        function getPromotionFieldByLang(promotion, field, lang) {
            if (promotion[field] && promotion[field][lang]) {
                return promotion[field][lang];
            }
            // Fallback to English or a default if the preferred language isn't available
            return promotion[field]['en'] || 'N/A';
        }

        function displayPromotionDetails(promotion) {
            const formatted = formatPromotionForDisplay(promotion);

            let restrictionsHtml = '';

            // Check if the roomtypes array exists and is not empty
            if (promotion.roomtypes && promotion.roomtypes.length > 0) {
                // Map the array of room type objects to an array of their names
                const roomTypeNames = promotion.roomtypes.map(roomType => {
                    // Assuming you have a way to determine the current language (e.g., 'vi' or 'en')
                    // This example uses 'vi' as the preferred language
                    return getRoomTypeName(roomType, lang);
                });

                restrictionsHtml += `<p><strong>Loại phòng áp dụng:</strong> ${roomTypeNames.join(', ')}</p>`;
            }
            // if (promotion.blackout_dates && promotion.blackout_dates.length > 0) {
            //     restrictionsHtml += `<p><strong>Blackout Dates:</strong> ${promotion.blackout_dates.join(', ')}</p>`;
            // }

            const html = `
            <div class="hme-promotion-details">
                <div class="hme-detail-section">
                    <h3>Basic Information</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Code:</strong></td><td>${promotion.promotion_code}</td></tr>
                        <tr><td><strong>Title:</strong></td><td>${getPromotionFieldByLang(promotion, 'name', lang)}</td></tr>
                        <tr><td><strong>Description:</strong></td><td>${getPromotionFieldByLang(promotion, 'description', lang) || 'No description'}</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="hme-status ${formatted.status_class}">${formatted.status_label}</span></td></tr>
                    </table>
                </div>
                
                <div class="hme-detail-section">
                    <h3>Discount Details</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Type:</strong></td><td>${loadPromotionType(promotion.type)}</td></tr>
                        <tr><td><strong>Discount:</strong></td><td>${formatted.discount_formatted}</td></tr>
                        <tr><td><strong>Min Nights:</strong></td><td>${promotion.min_stay || 1} night(s)</td></tr>
                        <tr><td><strong>Min Nights:</strong></td><td>${promotion.max_stay || 'N/A'} night(s)</td></tr>
                    </table>
                </div>
                
                <div class="hme-detail-section">
                    <h3>Validity</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Valid From:</strong></td><td>${formatted.start_date_formatted}</td></tr>
                        <tr><td><strong>Valid Until:</strong></td><td>${formatted.end_date_formatted}</td></tr>
                    </table>
                </div>
                
                ${restrictionsHtml ? `
                <div class="hme-detail-section">
                    <h3>Restrictions</h3>
                    ${restrictionsHtml}
                </div>
                ` : ''}
                
                <div class="hme-detail-section">
                    <h3>Additional Settings</h3>
                    <table class="hme-detail-table">
                        <tr><td><strong>Combinable:</strong></td><td>${promotion.is_combinable ? 'Yes' : 'No'}</td></tr>
                        <tr><td><strong>Advance Booking:</strong></td><td>${promotion.advance_booking_days || 0} days</td></tr>
                        <tr><td><strong>Created:</strong></td><td>${formatted.created_at_formatted}</td></tr>
                    </table>
                </div>
            </div>
        `;

            $('#promotion-detail-content').html(html);
            $('#edit-promotion-btn').data('id', promotion.id);
            $('#duplicate-promotion-btn').data('id', promotion.id).data('title', promotion.title);
            //('#view-usage-btn').data('id', promotion.id);
        }

        function showPromotionStatusModal(promotionId, currentStatus) {
            $('#status-promotion-id').val(promotionId);
            $('#promotion-is-active').prop('checked', currentStatus);
            $('#promotion-status-modal').show();
        }

        function updatePromotionStatus() {
            const promotionId = $('#status-promotion-id').val();
            const isActive = $('#promotion-is-active').prop('checked');

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_update_promotion',
                    nonce: hme_admin.nonce,
                    promotion_id: promotionId,
                    is_active: isActive
                },
                success: function(response) {
                    if (response.success) {
                        $('#promotion-status-modal').hide();
                        showSuccess('Promotion status updated successfully');
                        loadPromotions(); // Reload table
                    } else {
                        showError('Failed to update status: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error updating promotion status');
                }
            });
        }

        function deletePromotion(promotionId) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_delete_promotion',
                    nonce: hme_admin.nonce,
                    promotion_id: promotionId
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess('Promotion deleted successfully');
                        loadPromotions();
                    } else {
                        showError('Failed to delete promotion: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error deleting promotion');
                }
            });
        }

        function bulkPromotionAction(action, promotionIds) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_bulk_promotion_actions',
                    nonce: hme_admin.nonce,
                    bulk_action: action,
                    promotion_ids: promotionIds
                },
                success: function(response) {
                    if (response.success) {
                        const result = response.data;
                        let message = `${action.charAt(0).toUpperCase() + action.slice(1)}d ${result.processed} promotion(s)`;

                        if (result.errors.length > 0) {
                            message += `. Errors: ${result.errors.join(', ')}`;
                        }

                        showSuccess(message);
                        loadPromotions();
                    } else {
                        showError('Bulk action failed: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error performing bulk action');
                }
            });
        }

        function showCodeGeneratorModal() {
            $('#code-generator-modal').show();
            generatePromotionCode();
        }

        function generatePromotionCode() {
            const prefix = $('#code-prefix').val();
            const length = parseInt($('#code-length').val()) || 8;
            const type = $('#code-type').val();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_generate_promotion_code',
                    nonce: hme_admin.nonce,
                    prefix: prefix,
                    length: length,
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        $('#generated-code').val(response.data.code);
                    } else {
                        $('#generated-code').val('Error generating code');
                    }
                },
                error: function() {
                    $('#generated-code').val('Error generating code');
                }
            });
        }

        function showDuplicateModal(promotionId, promotionTitle) {
            $('#duplicate-source-id').val(promotionId);
            $('#duplicate-source-title').text(promotionTitle);
            $('#duplicate-new-code').val('');
            $('#duplicate-promotion-modal').show();
        }

        function duplicatePromotion() {
            const sourceId = $('#duplicate-source-id').val();
            const newCode = $('#duplicate-new-code').val();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_duplicate_promotion',
                    nonce: hme_admin.nonce,
                    promotion_id: sourceId,
                    new_code: newCode
                },
                success: function(response) {
                    if (response.success) {
                        $('#duplicate-promotion-modal').hide();
                        showSuccess('Promotion duplicated successfully');
                        loadPromotions();
                    } else {
                        showError('Failed to duplicate promotion: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error duplicating promotion');
                }
            });
        }

        function showUsageHistory(promotionId) {
            $('#usage-history-content').html(`
            <div class="hme-loading">
                <div class="spinner is-active"></div>
                <p>Loading usage history...</p>
            </div>
        `);
            $('#usage-history-modal').show();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_get_promotion_usage',
                    nonce: hme_admin.nonce,
                    promotion_id: promotionId,
                    page: 1,
                    per_page: 50
                },
                success: function(response) {
                    if (response.success && response.data.data.length > 0) {
                        let html = `
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Discount Amount</th>
                                    <th>Used At</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                        response.data.data.forEach(function(usage) {
                            html += `
                            <tr>
                                <td>#${usage.booking_id}</td>
                                <td>${usage.customer_name}<br><small>${usage.customer_email}</small></td>
                                <td>${formatCurrency(usage.discount_amount)}</td>
                                <td>${formatDateTime(usage.created_at)}</td>
                            </tr>
                        `;
                        });

                        html += '</tbody></table>';
                        $('#usage-history-content').html(html);
                    } else {
                        $('#usage-history-content').html('<p>No usage history found for this promotion.</p>');
                    }
                },
                error: function() {
                    $('#usage-history-content').html('<p>Error loading usage history</p>');
                }
            });
        }

        function exportPromotions() {
            const params = new URLSearchParams({
                action: 'hme_export_promotions',
                nonce: hme_admin.nonce,
                ...currentFilters
            });

            window.location.href = `${hme_admin.ajax_url}?${params.toString()}`;
        }

        // Utility functions
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('vi-VN');
        }

        function formatDateTime(dateString) {
            return new Date(dateString).toLocaleString('vi-VN');
        }

        function formatDiscountValue(type, value) {
            switch (type) {
                case 'percentage':
                    return value + '%';
                case 'fixed':
                    return formatCurrency(value);
                case 'free_nights':
                    return value + ' free night' + (value > 1 ? 's' : '');
                default:
                    return value;
            }
        }

        function getPromotionTypeLabel(type) {
            const types = {
                'percentage': 'Percentage Discount',
                'fixed': 'Fixed Amount Discount',
                'free_nights': 'Free Nights'
            };
            return types[type] || type;
        }

        function getPromotionStatusLabel(status) {
            const statuses = {
                0: 'Inactive',
                1: 'Active',
                2: 'Expired',
                3: 'Upcoming',
                4: 'Used Up'
            };
            return statuses[status] || status;
        }

        function getPromotionStatusClass(status) {
            const classes = {
                'inactive': 'hme-status-inactive',
                'active': 'hme-status-active',
                'expired': 'hme-status-expired',
                'upcoming': 'hme-status-upcoming',
                'used_up': 'hme-status-used-up'
            };
            return classes[status] || 'hme-status-unknown';
        }

        function getUsagePercentage(promotion) {
            if (!promotion.usage_limit || promotion.usage_limit === 0) {
                return 0;
            }
            return Math.min(100, ((promotion.used_count || 0) / promotion.usage_limit) * 100);
        }

        function getEditUrl(promotionId) {
            return `<?php echo admin_url('admin.php?page=hotel-promotions&action=edit'); ?>&id=${promotionId}`;
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

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);

            // Manual dismiss
            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(() => notice.remove());
            });
        }
    });
</script>