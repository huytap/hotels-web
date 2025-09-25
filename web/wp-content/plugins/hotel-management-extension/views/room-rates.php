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
                <button type="button" class="button button-primary" id="load-calendar">
                    <span class="dashicons dashicons-calendar-alt"></span> Load Calendar
                </button>
                <button type="button" class="button" id="export-rates">
                    <span class="dashicons dashicons-download"></span> Export
                </button>
            </div>
        </div>

        <!-- View Controls -->
        <div class="hme-view-controls">
            <div class="hme-view-tabs">
                <button type="button" class="hme-tab-btn active" data-view="calendar">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    Calendar View
                </button>
                <button type="button" class="hme-tab-btn" data-view="list">
                    <span class="dashicons dashicons-list-view"></span>
                    List View
                </button>
                <button type="button" class="hme-tab-btn" data-view="templates">
                    <span class="dashicons dashicons-admin-page"></span>
                    Templates
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
                <h2 id="current-month">Current Month</h2>
                <button type="button" id="next-month" class="button">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>

            <div class="hme-calendar-legend">
                <div class="legend-item">
                    <div class="legend-color hme-closed"></div>
                    <span>Closed</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-low-availability"></div>
                    <span>Low Availability</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-no-availability"></div>
                    <span>No Availability</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-cta"></div>
                    <span>CTA</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-ctd"></div>
                    <span>CTD</span>
                </div>
            </div>
        </div>

        <div class="hme-calendar-container">
            <div id="calendar-container">
                <div class="hme-empty-state">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <p>Select a room type and click "Load Calendar" to view rates.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="hme-view-content" style="display: none;">
        <div id="list-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-list-view"></span>
                <p>List view functionality coming soon...</p>
            </div>
        </div>
    </div>

    <!-- Templates View -->
    <div id="templates-view" class="hme-view-content" style="display: none;">
        <div class="hme-templates-header">
            <h3>Rate Templates</h3>
            <button type="button" class="button button-primary" id="create-template-btn">
                <span class="dashicons dashicons-plus"></span>
                Create Template
            </button>
        </div>
        <div id="templates-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-admin-page"></span>
                <p>Loading templates...</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div id="quick-edit-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h3>Edit Room Rate</h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="quick-edit-form">
                <input type="hidden" id="edit-date">
                <table class="form-table">
                    <tr>
                        <th><label for="edit-rate">Rate (VNĐ):</label></th>
                        <td><input type="number" id="edit-rate" step="1000" min="0" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-available-rooms">Available Rooms:</label></th>
                        <td><input type="number" id="edit-available-rooms" min="0" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label>Status:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-is-closed">
                                Room is closed
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
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="bulk-update-form">
                <table class="form-table">
                    <tr>
                        <th><label for="bulk-room-type" class="required">Room Type:</label></th>
                        <td>
                            <select id="bulk-room-type" name="room_type_id" required>
                                <option value="">Select Room Type</option>
                                <?php foreach ($room_types as $room):
                                    $room_name = $room['title'][$current_lang]; ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-from" class="required">From Date:</label></th>
                        <td><input type="date" id="bulk-date-from" name="date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-to" class="required">To Date:</label></th>
                        <td><input type="date" id="bulk-date-to" name="date_to" required></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-rate">Rate (VNĐ):</label></th>
                        <td>
                            <input type="number" id="bulk-rate" name="rate" step="1000" min="0" class="regular-text">
                            <span class="description">Leave empty to keep current rates</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-available-rooms">Available Rooms:</label></th>
                        <td>
                            <input type="number" id="bulk-available-rooms" name="total_for_sale" min="0" class="small-text">
                            <span class="description">Leave empty to keep current availability</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Status:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-is-available">
                                Room is closed
                            </label>
                        </td>
                    </tr>
                    <!-- <tr>
                        <th><label>Apply to Days:</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Apply to specific weekdays</span></legend>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="0"> Sunday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="1"> Monday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="2"> Tuesday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="3"> Wednesday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="4"> Thursday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="5"> Friday</label><br>
                                <label><input type="checkbox" name="apply_to_weekdays[]" value="6"> Saturday</label><br>
                                <span class="description">Leave unchecked to apply to all days</span>
                            </fieldset>
                        </td>
                    </tr> -->
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close">Cancel</button>
            <button type="submit" form="bulk-update-form" id="update-rates" class="button button-primary">Update Rates</button>
        </div>
    </div>
</div>

<!-- Copy Rates Modal -->
<div id="copy-rates-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3>Copy Rates</h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="copy-rates-form">
                <h4>Source Period</h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-room-type" class="required">Room Type:</label></th>
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
                        <th><label for="copy-source-from" class="required">From Date:</label></th>
                        <td><input type="date" id="copy-source-from" name="source_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-source-to" class="required">To Date:</label></th>
                        <td><input type="date" id="copy-source-to" name="source_date_to" required></td>
                    </tr>
                </table>

                <h4>Target Period</h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-target-from" class="required">From Date:</label></th>
                        <td><input type="date" id="copy-target-from" name="target_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-target-to" class="required">To Date:</label></th>
                        <td><input type="date" id="copy-target-to" name="target_date_to" required></td>
                    </tr>
                </table>

                <h4>Copy Options</h4>
                <table class="form-table">
                    <tr>
                        <th><label>What to Copy:</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Copy options</span></legend>
                                <label><input type="checkbox" name="copy_rates" checked> Room Rates</label><br>
                                <label><input type="checkbox" name="copy_availability" checked> Availability</label><br>
                                <label><input type="checkbox" name="copy_restrictions" checked> Restrictions</label><br>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Overwrite:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="overwrite_existing">
                                Overwrite existing rates
                            </label>
                            <p class="description">If unchecked, only copy to dates without existing rates</p>
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

<!-- Template Modal -->
<div id="template-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3>Create New Template</h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="template-form">
                <input type="hidden" id="template-id" name="template_id">
                <table class="form-table">
                    <tr>
                        <th><label for="template-name" class="required">Template Name:</label></th>
                        <td>
                            <input type="text" id="template-name" name="template_name" class="regular-text" required>
                            <p class="description">Give your template a descriptive name</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-description">Description:</label></th>
                        <td>
                            <textarea id="template-description" name="template_description" class="large-text" rows="3"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-room-type" class="required">Room Type:</label></th>
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