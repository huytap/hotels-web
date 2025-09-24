<?php

/**
 * Room Rates Management View
 * File: views/room-rates.php
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}
$current_lang = get_locale();
// Lấy room types từ API
$room_types = HME_Room_Rate_Manager::get_room_types();
$weekdays = HME_Room_Rate_Manager::get_weekdays();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-home"></span>
        Room Rate Management
    </h1>
    <button type="button" class="page-title-action" id="bulk-update-btn">
        <span class="dashicons dashicons-update"></span> Bulk Update
    </button>
    <button type="button" class="page-title-action" id="copy-rates-btn">
        <span class="dashicons dashicons-admin-page"></span> Copy Rates
    </button>

    <!-- Filters -->
    <div class="hme-filters-section">
        <div class="hme-filters-row">
            <div class="hme-filter-group">
                <label for="room-type-filter"><?php _e("Room Type", "hotel"); ?>:</label>
                <select id="room-type-filter">
                    <option value=""><?php _e("All Room Types", "hotel"); ?></option>
                    <?php foreach ($room_types as $room):
                        $room_name = $room['title'][$current_lang]; ?>
                        <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hme-filter-group">
                <label for="date-from"><?php _e("From Date", "hotel"); ?>:</label>
                <input type="date" id="date-from" value="<?php echo date('Y-m-01'); ?>">
            </div>

            <div class="hme-filter-group">
                <label for="date-to"><?php _e("To Date", "hotel"); ?>:</label>
                <input type="date" id="date-to" value="<?php echo date('Y-m-t'); ?>">
            </div>

            <div class="hme-filter-actions">
                <button type="button" id="load-calendar" class="button button-primary">
                    <span class="dashicons dashicons-calendar-alt"></span> <?php _e("Load Calendar", "hotel"); ?>
                </button>
                <button type="button" id="export-rates" class="button">
                    <span class="dashicons dashicons-download"></span> <?php _e("Export CSV", "hotel"); ?>
                </button>
            </div>
        </div>

        <div class="hme-view-controls">
            <div class="hme-view-tabs">
                <button type="button" class="hme-tab-btn active" data-view="calendar">
                    <span class="dashicons dashicons-calendar-alt"></span> <?php _e("Calendar View", "hotel"); ?>
                </button>
                <button type="button" class="hme-tab-btn" data-view="list">
                    <span class="dashicons dashicons-list-view"></span> <?php _e("List View", "hotel"); ?>
                </button>
                <button type="button" class="hme-tab-btn" data-view="templates">
                    <span class="dashicons dashicons-admin-settings"></span> <?php _e("Rate Templates", "hotel"); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="hme-loading" class="hme-loading-overlay" style="display: none;">
        <div class="hme-loading-spinner">
            <div class="spinner is-active"></div>
            <p>Loading room rates...</p>
        </div>
    </div>

    <!-- Calendar View -->
    <div id="calendar-view" class="hme-view-content">
        <div class="hme-calendar-header">
            <div class="hme-calendar-navigation">
                <button type="button" id="prev-month" class="button">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h2 id="current-month"></h2>
                <button type="button" id="next-month" class="button">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
            <div class="hme-calendar-legend">
                <span class="legend-item">
                    <span class="legend-color hme-closed"></span> Closed
                </span>
                <span class="legend-item">
                    <span class="legend-color hme-low-availability"></span> Low Availability
                </span>
                <span class="legend-item">
                    <span class="legend-color hme-no-availability"></span> No Availability
                </span>
                <span class="legend-item">
                    <span class="legend-color hme-cta"></span> CTA
                </span>
                <span class="legend-item">
                    <span class="legend-color hme-ctd"></span> CTD
                </span>
            </div>
        </div>

        <div id="calendar-container" class="hme-calendar-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-calendar-alt"></span>
                <p>Select room type and date range, then click "Load Calendar"</p>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="hme-view-content" style="display: none;">
        <table class="wp-list-table widefat fixed striped" id="rates-table">
            <thead>
                <tr>
                    <th class="manage-column column-cb check-column">
                        <input type="checkbox" id="select-all-rates">
                    </th>
                    <th>Date</th>
                    <th>Room Type</th>
                    <th>Rate</th>
                    <th>Available</th>
                    <th>Min Stay</th>
                    <th>Restrictions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rates-tbody">
                <tr>
                    <td colspan="8">
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-admin-home"></span>
                            <p>No room rates data. Use the filters above to load rates.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Templates View -->
    <div id="templates-view" class="hme-view-content" style="display: none;">
        <div class="hme-templates-header">
            <button type="button" id="add-template-btn" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span> Add New Template
            </button>
        </div>

        <div id="templates-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-admin-settings"></span>
                <p>Loading rate templates...</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div id="quick-edit-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h3>Quick Edit Rate</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="quick-edit-form">
                <input type="hidden" id="edit-room-type-id">
                <input type="hidden" id="edit-date">

                <table class="form-table">
                    <tr>
                        <th><label>Date:</label></th>
                        <td><strong id="edit-date-display"></strong></td>
                    </tr>
                    <tr>
                        <th><label>Room Type:</label></th>
                        <td><strong id="edit-room-type-display"></strong></td>
                    </tr>
                    <tr>
                        <th><label for="edit-rate">Rate (VNĐ):</label></th>
                        <td>
                            <input type="number" id="edit-rate" name="rate" step="1000" min="0" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-available-rooms">Available Rooms:</label></th>
                        <td>
                            <input type="number" id="edit-available-rooms" name="available_rooms" min="0" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-min-stay">Min Stay (nights):</label></th>
                        <td>
                            <input type="number" id="edit-min-stay" name="min_stay" min="1" value="1" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-max-stay">Max Stay (nights):</label></th>
                        <td>
                            <input type="number" id="edit-max-stay" name="max_stay" min="1" value="30" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label>Restrictions:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-is-closed" name="is_closed">
                                Close for sale
                            </label><br>
                            <label>
                                <input type="checkbox" id="edit-close-to-arrival" name="close_to_arrival">
                                Close to arrival (CTA)
                            </label><br>
                            <label>
                                <input type="checkbox" id="edit-close-to-departure" name="close_to_departure">
                                Close to departure (CTD)
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="quick-edit-form" class="button button-primary">Update Rate</button>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div id="bulk-update-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3>Bulk Update Rates</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="bulk-update-form">
                <table class="form-table">
                    <tr>
                        <th><label for="bulk-room-type" class="required">Room Type *:</label></th>
                        <td>
                            <select id="bulk-room-type" name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                <?php foreach ($room_types as $room): ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-from" class="required">From Date *:</label></th>
                        <td><input type="date" id="bulk-date-from" name="date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-to" class="required">To Date *:</label></th>
                        <td><input type="date" id="bulk-date-to" name="date_to" required></td>
                    </tr>
                    <tr>
                        <th><label>Apply to Days:</label></th>
                        <td>
                            <?php foreach ($weekdays as $day => $label): ?>
                                <label style="margin-right: 15px;">
                                    <input type="checkbox" name="apply_to_weekdays[]" value="<?php echo $day; ?>" checked>
                                    <?php echo $label; ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Rate Update:</label></th>
                        <td>
                            <label>
                                <input type="radio" name="rate_update_type" value="set" checked>
                                Set rate to: <input type="number" id="bulk-set-rate" step="1000" min="0" class="regular-text">
                            </label><br>
                            <label>
                                <input type="radio" name="rate_update_type" value="adjust">
                                Adjust rate by:
                                <select id="bulk-adjust-type">
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                                <input type="number" id="bulk-adjust-value" step="0.01" class="regular-text">
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-min-stay">Min Stay:</label></th>
                        <td><input type="number" id="bulk-min-stay" name="min_stay" min="1" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-max-stay">Max Stay:</label></th>
                        <td><input type="number" id="bulk-max-stay" name="max_stay" min="1" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-available-rooms">Available Rooms:</label></th>
                        <td><input type="number" id="bulk-available-rooms" name="available_rooms" min="0" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label>Restrictions:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="bulk-is-closed" name="is_closed">
                                Close for sale
                            </label><br>
                            <label>
                                <input type="checkbox" id="bulk-close-to-arrival" name="close_to_arrival">
                                Close to arrival (CTA)
                            </label><br>
                            <label>
                                <input type="checkbox" id="bulk-close-to-departure" name="close_to_departure">
                                Close to departure (CTD)
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="bulk-update-form" class="button button-primary">Apply Bulk Update</button>
        </div>
    </div>
</div>

<!-- Copy Rates Modal -->
<div id="copy-rates-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h3>Copy Rates</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="copy-rates-form">
                <h4>Source Period</h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-room-type" class="required">Room Type *:</label></th>
                        <td>
                            <select id="copy-room-type" name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                <?php foreach ($room_types as $room): ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="copy-source-from">From Date *:</label></th>
                        <td><input type="date" id="copy-source-from" name="source_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-source-to">To Date *:</label></th>
                        <td><input type="date" id="copy-source-to" name="source_date_to" required></td>
                    </tr>
                </table>

                <h4>Target Period</h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-target-from">From Date *:</label></th>
                        <td><input type="date" id="copy-target-from" name="target_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-target-to">To Date *:</label></th>
                        <td><input type="date" id="copy-target-to" name="target_date_to" required></td>
                    </tr>
                </table>

                <h4>Copy Options</h4>
                <table class="form-table">
                    <tr>
                        <th><label>What to Copy:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="copy_rates" checked>
                                Room Rates
                            </label><br>
                            <label>
                                <input type="checkbox" name="copy_availability" checked>
                                Availability
                            </label><br>
                            <label>
                                <input type="checkbox" name="copy_restrictions" checked>
                                Restrictions (Min/Max Stay, CTA/CTD)
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Overwrite:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="overwrite_existing">
                                Overwrite existing data
                            </label>
                            <p class="description">If unchecked, will only copy to dates that don't have existing data.</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="copy-rates-form" class="button button-primary">Copy Rates</button>
        </div>
    </div>
</div>

<!-- Rate Template Modal -->
<div id="template-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3 id="template-modal-title">Add Rate Template</h3>
            <span class="hme-modal-close">&times;</span>
        </div>
        <div class="hme-modal-body">
            <form id="template-form">
                <input type="hidden" id="template-id" name="template_id">

                <table class="form-table">
                    <tr>
                        <th><label for="template-name" class="required">Template Name *:</label></th>
                        <td><input type="text" id="template-name" name="template_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="template-description">Description:</label></th>
                        <td><textarea id="template-description" name="template_description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="template-room-type" class="required">Room Type *:</label></th>
                        <td>
                            <select id="template-room-type" name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                <?php foreach ($room_types as $room): ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h4>Weekly Rates</h4>
                <table class="form-table">
                    <?php foreach ($weekdays as $day => $label): ?>
                        <tr>
                            <th><label for="rate-<?php echo $day; ?>"><?php echo $label; ?>:</label></th>
                            <td>
                                <input type="number" id="rate-<?php echo $day; ?>" name="rate_<?php echo $day; ?>" step="1000" min="0" class="regular-text">
                                <span class="description">VNĐ</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <table class="form-table">
                    <tr>
                        <th><label for="template-min-stay">Min Stay:</label></th>
                        <td><input type="number" id="template-min-stay" name="min_stay" min="1" value="1" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="template-max-stay">Max Stay:</label></th>
                        <td><input type="number" id="template-max-stay" name="max_stay" min="1" value="30" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label>Status:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="template-is-active" name="is_active" checked>
                                Active
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="template-form" class="button button-primary">Save Template</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        let currentView = 'calendar';
        let currentMonth = new Date();
        let calendarData = {};

        // Initialize
        initializeView();

        // View switching
        $('.hme-tab-btn').on('click', function() {
            const view = $(this).data('view');
            switchView(view);
        });

        // Calendar navigation
        $('#prev-month').on('click', function() {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            updateCalendarHeader();
            loadCalendarData();
        });

        $('#next-month').on('click', function() {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            updateCalendarHeader();
            loadCalendarData();
        });

        // Load calendar button
        $('#load-calendar').on('click', function() {
            if (currentView === 'calendar') {
                loadCalendarData();
            } else if (currentView === 'list') {
                loadListData();
            }
        });

        // Modal triggers
        $('#bulk-update-btn').on('click', function() {
            $('#bulk-update-modal').show();
        });

        $('#copy-rates-btn').on('click', function() {
            $('#copy-rates-modal').show();
        });

        $('#add-template-btn').on('click', function() {
            resetTemplateForm();
            $('#template-modal-title').text('Add Rate Template');
            $('#template-modal').show();
        });

        // Modal close
        $('.hme-modal-close').on('click', function() {
            $(this).closest('.hme-modal').hide();
        });

        // Form submissions
        $('#quick-edit-form').on('submit', function(e) {
            e.preventDefault();
            updateSingleRate();
        });

        $('#bulk-update-form').on('submit', function(e) {
            e.preventDefault();
            bulkUpdateRates();
        });

        $('#copy-rates-form').on('submit', function(e) {
            e.preventDefault();
            copyRates();
        });

        $('#template-form').on('submit', function(e) {
            e.preventDefault();
            saveTemplate();
        });

        // Calendar cell interactions
        $(document).on('click', '.hme-calendar-cell', function() {
            const date = $(this).data('date');
            const roomTypeId = $('#room-type-filter').val();

            if (date && roomTypeId) {
                showQuickEditModal(roomTypeId, date, $(this).data('rate-info'));
            } else {
                alert('Please select a room type first');
            }
        });

        // Availability toggle
        $(document).on('click', '.toggle-availability', function(e) {
            e.stopPropagation();
            const roomTypeId = $(this).data('room-type-id');
            const date = $(this).data('date');
            const isClosed = $(this).hasClass('closed');

            toggleRoomAvailability(roomTypeId, date, !isClosed);
        });

        // Export rates
        $('#export-rates').on('click', function() {
            exportRates();
        });

        // Functions
        function initializeView() {
            updateCalendarHeader();
            loadTemplates();
        }

        function switchView(view) {
            $('.hme-tab-btn').removeClass('active');
            $(`.hme-tab-btn[data-view="${view}"]`).addClass('active');

            $('.hme-view-content').hide();
            $(`#${view}-view`).show();

            currentView = view;

            if (view === 'templates') {
                loadTemplates();
            }
        }

        function updateCalendarHeader() {
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            $('#current-month').text(`${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`);

            // Update date filters to match current month
            const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
            const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);

            $('#date-from').val(firstDay.toISOString().split('T')[0]);
            $('#date-to').val(lastDay.toISOString().split('T')[0]);
        }

        function loadCalendarData() {
            const roomTypeId = $('#room-type-filter').val();
            if (!roomTypeId) {
                alert('Please select a room type');
                return;
            }

            showLoading();

            const data = {
                action: 'hme_get_room_rates',
                nonce: hme_admin.nonce,
                room_type_id: roomTypeId,
                date_from: $('#date-from').val(),
                date_to: $('#date-to').val()
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        calendarData = response.data;
                        renderCalendar();
                    } else {
                        showError('Failed to load calendar data: ' + response.data);
                    }
                },
                error: function() {
                    hideLoading();
                    showError('Error loading calendar data');
                }
            });
        }

        function renderCalendar() {
            const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
            const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay()); // Start from Sunday

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

                const dateStr = currentDate.toISOString().split('T')[0];
                const isCurrentMonth = currentDate.getMonth() === currentMonth.getMonth();
                const rateInfo = calendarData[dateStr];

                let cellClass = 'hme-calendar-cell';
                let cellContent = `<div class="date-number">${currentDate.getDate()}</div>`;

                if (!isCurrentMonth) {
                    cellClass += ' other-month';
                } else if (rateInfo) {
                    cellClass += ` ${rateInfo.css_classes}`;
                    cellContent += `
                    <div class="rate-info">
                        <div class="rate">${rateInfo.rate_formatted}</div>
                        <div class="availability">${rateInfo.available_rooms} avail</div>
                    </div>
                `;
                } else {
                    cellContent += '<div class="no-data">No data</div>';
                }

                html += `
                <td class="${cellClass}" data-date="${dateStr}" data-rate-info='${JSON.stringify(rateInfo || {})}' title="${rateInfo?.tooltip || ''}">
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

        function loadListData() {
            showLoading();

            const data = {
                action: 'hme_get_room_rates',
                nonce: hme_admin.nonce,
                room_type_id: $('#room-type-filter').val(),
                date_from: $('#date-from').val(),
                date_to: $('#date-to').val()
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        renderRatesList(response.data);
                    } else {
                        showError('Failed to load rates list: ' + response.data);
                    }
                },
                error: function() {
                    hideLoading();
                    showError('Error loading rates list');
                }
            });
        }

        function renderRatesList(rates) {
            let html = '';

            if (rates && rates.length > 0) {
                rates.forEach(function(rate) {
                    const restrictions = [];
                    if (rate.is_closed) restrictions.push('CLOSED');
                    if (rate.close_to_arrival) restrictions.push('CTA');
                    if (rate.close_to_departure) restrictions.push('CTD');

                    html += `
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" value="${rate.id}">
                        </th>
                        <td>${formatDate(rate.date)}</td>
                        <td>${rate.room_type_name}</td>
                        <td>${formatCurrency(rate.rate)}</td>
                        <td>${rate.available_rooms}</td>
                        <td>${rate.min_stay} - ${rate.max_stay}</td>
                        <td>
                            ${restrictions.length > 0 ? `<span class="restrictions">${restrictions.join(', ')}</span>` : '-'}
                        </td>
                        <td>
                            <button type="button" class="button button-small edit-rate" 
                                    data-room-type-id="${rate.room_type_id}" 
                                    data-date="${rate.date}"
                                    data-rate-info='${JSON.stringify(rate)}'>
                                Edit
                            </button>
                            <button type="button" class="button button-small toggle-availability ${rate.is_closed ? 'closed' : ''}"
                                    data-room-type-id="${rate.room_type_id}"
                                    data-date="${rate.date}">
                                ${rate.is_closed ? 'Open' : 'Close'}
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

        function loadTemplates() {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_get_rate_templates',
                    nonce: hme_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        renderTemplates(response.data);
                    } else {
                        $('#templates-container').html(`
                        <div class="hme-empty-state">
                            <span class="dashicons dashicons-warning"></span>
                            <p>Error loading templates: ${response.data}</p>
                        </div>
                    `);
                    }
                },
                error: function() {
                    $('#templates-container').html(`
                    <div class="hme-empty-state">
                        <span class="dashicons dashicons-warning"></span>
                        <p>Error loading templates</p>
                    </div>
                `);
                }
            });
        }

        function renderTemplates(templates) {
            let html = '';

            if (templates && templates.length > 0) {
                html = '<div class="hme-templates-grid">';

                templates.forEach(function(template) {
                    const statusClass = template.is_active ? 'active' : 'inactive';

                    html += `
                    <div class="hme-template-card ${statusClass}">
                        <div class="template-header">
                            <h4>${template.name}</h4>
                            <span class="template-status">${template.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                        <div class="template-body">
                            <p><strong>Room Type:</strong> ${template.room_type_name}</p>
                            <p><strong>Description:</strong> ${template.description || 'No description'}</p>
                            <div class="template-rates">
                                <small>Weekly Rates:</small>
                                <div class="rates-preview">
                                    <span>Mon: ${formatCurrency(template.rates.monday)}</span>
                                    <span>Tue: ${formatCurrency(template.rates.tuesday)}</span>
                                    <span>Wed: ${formatCurrency(template.rates.wednesday)}</span>
                                    <span>Thu: ${formatCurrency(template.rates.thursday)}</span>
                                    <span>Fri: ${formatCurrency(template.rates.friday)}</span>
                                    <span>Sat: ${formatCurrency(template.rates.saturday)}</span>
                                    <span>Sun: ${formatCurrency(template.rates.sunday)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="template-actions">
                            <button type="button" class="button button-small edit-template" data-template-id="${template.id}">
                                Edit
                            </button>
                            <button type="button" class="button button-small apply-template" data-template-id="${template.id}">
                                Apply
                            </button>
                            <button type="button" class="button button-small delete-template" data-template-id="${template.id}">
                                Delete
                            </button>
                        </div>
                    </div>
                `;
                });

                html += '</div>';
            } else {
                html = `
                <div class="hme-empty-state">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <p>No rate templates found. <a href="#" id="create-first-template">Create your first template</a></p>
                </div>
            `;
            }

            $('#templates-container').html(html);
        }

        function showQuickEditModal(roomTypeId, date, rateInfo) {
            $('#edit-room-type-id').val(roomTypeId);
            $('#edit-date').val(date);
            $('#edit-date-display').text(formatDate(date));

            // Find room type name
            const roomType = <?php echo json_encode($room_types); ?>.find(r => r.id == roomTypeId);
            $('#edit-room-type-display').text(roomType ? roomType.name : 'Unknown');

            // Fill current values
            if (rateInfo && Object.keys(rateInfo).length > 0) {
                $('#edit-rate').val(rateInfo.rate || 0);
                $('#edit-available-rooms').val(rateInfo.available_rooms || 0);
                $('#edit-min-stay').val(rateInfo.min_stay || 1);
                $('#edit-max-stay').val(rateInfo.max_stay || 30);
                $('#edit-is-closed').prop('checked', rateInfo.is_closed || false);
                $('#edit-close-to-arrival').prop('checked', rateInfo.close_to_arrival || false);
                $('#edit-close-to-departure').prop('checked', rateInfo.close_to_departure || false);
            } else {
                // Reset form for new entry
                $('#edit-rate').val('');
                $('#edit-available-rooms').val('');
                $('#edit-min-stay').val(1);
                $('#edit-max-stay').val(30);
                $('#edit-is-closed').prop('checked', false);
                $('#edit-close-to-arrival').prop('checked', false);
                $('#edit-close-to-departure').prop('checked', false);
            }

            $('#quick-edit-modal').show();
        }

        function updateSingleRate() {
            const data = {
                action: 'hme_update_room_rate',
                nonce: hme_admin.nonce,
                room_type_id: $('#edit-room-type-id').val(),
                date: $('#edit-date').val(),
                rate: $('#edit-rate').val(),
                available_rooms: $('#edit-available-rooms').val(),
                min_stay: $('#edit-min-stay').val(),
                max_stay: $('#edit-max-stay').val(),
                is_closed: $('#edit-is-closed').prop('checked'),
                close_to_arrival: $('#edit-close-to-arrival').prop('checked'),
                close_to_departure: $('#edit-close-to-departure').prop('checked')
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#quick-edit-modal').hide();
                        showSuccess('Rate updated successfully');

                        // Reload current view
                        if (currentView === 'calendar') {
                            loadCalendarData();
                        } else {
                            loadListData();
                        }
                    } else {
                        showError('Failed to update rate: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error updating rate');
                }
            });
        }

        function bulkUpdateRates() {
            const formData = new FormData($('#bulk-update-form')[0]);
            const data = {
                action: 'hme_bulk_update_rates',
                nonce: hme_admin.nonce
            };

            // Add form data
            for (let [key, value] of formData.entries()) {
                if (key === 'apply_to_weekdays[]') {
                    if (!data.apply_to_weekdays) data.apply_to_weekdays = [];
                    data.apply_to_weekdays.push(value);
                } else {
                    data[key] = value;
                }
            }

            // Handle rate update type
            const rateUpdateType = $('input[name="rate_update_type"]:checked').val();
            if (rateUpdateType === 'set') {
                data.rate = $('#bulk-set-rate').val();
            } else if (rateUpdateType === 'adjust') {
                data.rate_adjustment_type = $('#bulk-adjust-type').val();
                data.rate_adjustment_value = $('#bulk-adjust-value').val();
            }

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#bulk-update-modal').hide();
                        showSuccess(`Successfully updated ${response.data.updated_count || 0} rate(s)`);

                        // Reload current view
                        if (currentView === 'calendar') {
                            loadCalendarData();
                        } else {
                            loadListData();
                        }
                    } else {
                        showError('Bulk update failed: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error performing bulk update');
                }
            });
        }

        function copyRates() {
            const formData = new FormData($('#copy-rates-form')[0]);
            const data = {
                action: 'hme_copy_rates',
                nonce: hme_admin.nonce
            };

            // Add form data
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#copy-rates-modal').hide();
                        showSuccess(`Successfully copied ${response.data.copied_count || 0} rate(s)`);

                        // Reload current view
                        if (currentView === 'calendar') {
                            loadCalendarData();
                        } else {
                            loadListData();
                        }
                    } else {
                        showError('Copy rates failed: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error copying rates');
                }
            });
        }

        function saveTemplate() {
            const formData = new FormData($('#template-form')[0]);
            const data = {
                action: 'hme_save_rate_template',
                nonce: hme_admin.nonce
            };

            // Add form data
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#template-modal').hide();
                        showSuccess('Template saved successfully');
                        loadTemplates();
                    } else {
                        showError('Failed to save template: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error saving template');
                }
            });
        }

        function resetTemplateForm() {
            $('#template-form')[0].reset();
            $('#template-id').val('');
            $('#template-is-active').prop('checked', true);
        }

        function toggleRoomAvailability(roomTypeId, date, isClosed) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_toggle_room_availability',
                    nonce: hme_admin.nonce,
                    room_type_id: roomTypeId,
                    date: date,
                    is_closed: isClosed
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess(`Room ${isClosed ? 'closed' : 'opened'} successfully`);

                        // Reload current view
                        if (currentView === 'calendar') {
                            loadCalendarData();
                        } else {
                            loadListData();
                        }
                    } else {
                        showError('Failed to toggle availability: ' + response.data);
                    }
                },
                error: function() {
                    showError('Error toggling availability');
                }
            });
        }

        function exportRates() {
            const params = new URLSearchParams({
                action: 'hme_export_rates',
                nonce: hme_admin.nonce,
                room_type_id: $('#room-type-filter').val(),
                date_from: $('#date-from').val(),
                date_to: $('#date-to').val()
            });

            window.location.href = `${hme_admin.ajax_url}?${params.toString()}`;
        }

        // Event handlers for dynamic content
        $(document).on('click', '.edit-rate', function() {
            const roomTypeId = $(this).data('room-type-id');
            const date = $(this).data('date');
            const rateInfo = $(this).data('rate-info');
            showQuickEditModal(roomTypeId, date, rateInfo);
        });

        $(document).on('click', '.edit-template', function() {
            const templateId = $(this).data('template-id');
            // Load template data and show edit modal
            // Implementation depends on your API structure
            resetTemplateForm();
            $('#template-modal-title').text('Edit Rate Template');
            $('#template-id').val(templateId);
            $('#template-modal').show();
        });

        $(document).on('click', '.delete-template', function() {
            const templateId = $(this).data('template-id');
            if (confirm('Are you sure you want to delete this template?')) {
                // Delete template
                // Implementation depends on your API structure
            }
        });

        $(document).on('click', '#create-first-template', function(e) {
            e.preventDefault();
            $('#add-template-btn').click();
        });

        // Utility functions
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('vi-VN');
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

            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(() => notice.remove());
            });
        }
    });
</script>