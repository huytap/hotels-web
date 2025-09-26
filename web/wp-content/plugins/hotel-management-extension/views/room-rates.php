<?php

/**
 * Room Rates Management View
 * File: views/room-rates.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
$current_lang = get_locale();
// Get room types from API
$room_types = HME_Room_Rate_Manager::get_room_types();
$weekdays = HME_Room_Rate_Manager::get_weekdays();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-home"></span>
        <?php _e('Quản Lý Giá Phòng', 'hotel'); ?>
    </h1>
    <button type="button" class="page-title-action" id="bulk-update-btn">
        <span class="dashicons dashicons-update"></span> <?php _e('Cập Nhật Hàng Loạt', 'hotel'); ?>
    </button>
    <button type="button" class="page-title-action" id="copy-rates-btn">
        <span class="dashicons dashicons-admin-page"></span> <?php _e('Sao Chép Giá', 'hotel'); ?>
    </button>

    <!-- Filters -->
    <div class="hme-filters-section">
        <div class="hme-filters-row">
            <div class="hme-filter-group">
                <label for="room-type-filter"><?php _e('Loại Phòng', 'hotel'); ?>:</label>
                <select id="room-type-filter">
                    <option value=""><?php _e('Tất Cả Loại Phòng', 'hotel'); ?></option>
                    <?php foreach ($room_types as $room):
                        $room_name = $room['title'][$current_lang]; ?>
                        <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hme-filter-group">
                <label for="date-from"><?php _e('Từ Ngày', 'hotel'); ?>:</label>
                <input type="date" id="date-from" value="<?php echo date('Y-m-01'); ?>">
            </div>

            <div class="hme-filter-group">
                <label for="date-to"><?php _e('Đến Ngày', 'hotel'); ?>:</label>
                <input type="date" id="date-to" value="<?php echo date('Y-m-t'); ?>">
            </div>

            <div class="hme-filter-actions">
                <button type="button" class="button button-primary" id="load-calendar">
                    <span class="dashicons dashicons-calendar-alt"></span> <?php _e('Tải Lịch', 'hotel'); ?>
                </button>
                <button type="button" class="button" id="export-rates">
                    <span class="dashicons dashicons-download"></span> <?php _e('Xuất Dữ Liệu', 'hotel'); ?>
                </button>
            </div>
        </div>

        <!-- View Controls -->
        <div class="hme-view-controls">
            <div class="hme-view-tabs">
                <button type="button" class="hme-tab-btn active" data-view="calendar">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php _e('Xem Lịch', 'hotel'); ?>
                </button>
                <button type="button" class="hme-tab-btn" data-view="list">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('Xem Danh Sách', 'hotel'); ?>
                </button>
                <button type="button" class="hme-tab-btn" data-view="templates">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php _e('Mẫu Giá', 'hotel'); ?>
                </button>
            </div>
        </div>
    </div>
    <!-- Loading Indicator -->
    <div id="hme-loading" class="hme-loading-overlay" style="display: none;">
        <div class="hme-loading-spinner">
            <div class="spinner is-active"></div>
            <p><?php _e('Đang tải giá phòng...', 'hotel'); ?></p>
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
                <div class="legend-item">
                    <div class="legend-color hme-closed"></div>
                    <span><?php _e('Đóng', 'hotel'); ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-low-availability"></div>
                    <span><?php _e('Còn Ít Phòng', 'hotel'); ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-no-availability"></div>
                    <span><?php _e('Hết Phòng', 'hotel'); ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-cta"></div>
                    <span><?php _e('Cấm Nhận Phòng', 'hotel'); ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-color hme-ctd"></div>
                    <span><?php _e('Cấm Trả Phòng', 'hotel'); ?></span>
                </div>
            </div>
        </div>

        <div class="hme-calendar-container">
            <div id="calendar-container">
                <div class="hme-empty-state">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <p><?php _e('Chọn loại phòng và nhấn "Tải Lịch" để xem giá phòng.', 'hotel'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="hme-view-content" style="display: none;">
        <div id="list-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-list-view"></span>
                <p><?php _e('Chức năng xem danh sách sẽ có sớm...', 'hotel'); ?></p>
            </div>
        </div>
    </div>

    <!-- Templates View -->
    <div id="templates-view" class="hme-view-content" style="display: none;">
        <div class="hme-templates-header">
            <h3><?php _e('Mẫu Giá Phòng', 'hotel'); ?></h3>
            <button type="button" class="button button-primary" id="create-template-btn">
                <span class="dashicons dashicons-plus"></span>
                <?php _e('Tạo Mẫu', 'hotel'); ?>
            </button>
        </div>
        <div id="templates-container">
            <div class="hme-empty-state">
                <span class="dashicons dashicons-admin-page"></span>
                <p><?php _e('Đang tải mẫu...', 'hotel'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div id="quick-edit-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h3><?php _e('Chỉnh Sửa Giá Phòng', 'hotel'); ?></h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="quick-edit-form">
                <input type="hidden" id="edit-date">
                <table class="form-table">
                    <tr>
                        <th><label for="edit-rate"><?php _e('Giá (VNĐ)', 'hotel'); ?>:</label></th>
                        <td><input type="number" id="edit-rate" step="1000" min="0" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-available-rooms"><?php _e('Số Phòng Có Sẵn', 'hotel'); ?>:</label></th>
                        <td><input type="number" id="edit-available-rooms" min="0" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Trạng Thái', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-is-closed">
                                <?php _e('Phòng đã đóng', 'hotel'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close"><?php _e('Hủy', 'hotel'); ?></button>
            <button type="submit" form="quick-edit-form" class="button button-primary"><?php _e('Cập Nhật Giá', 'hotel'); ?></button>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div id="bulk-update-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3><?php _e('Cập Nhật Hàng Loạt', 'hotel'); ?></h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="bulk-update-form">
                <table class="form-table">
                    <tr>
                        <th><label for="bulk-room-type" class="required"><?php _e('Loại Phòng', 'hotel'); ?>:</label></th>
                        <td>
                            <select id="bulk-room-type" name="room_type_id" required>
                                <option value=""><?php _e('Chọn Loại Phòng', 'hotel'); ?></option>
                                <?php foreach ($room_types as $room):
                                    $room_name = $room['title'][$current_lang]; ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-from" class="required"><?php _e('Từ Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="bulk-date-from" name="date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-date-to" class="required"><?php _e('Đến Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="bulk-date-to" name="date_to" required></td>
                    </tr>
                    <tr>
                        <th><label for="bulk-rate"><?php _e('Giá (VNĐ)', 'hotel'); ?>:</label></th>
                        <td>
                            <input type="number" id="bulk-rate" name="rate" step="1000" min="0" class="regular-text">
                            <span class="description"><?php _e('Để trống để giữ giá hiện tại', 'hotel'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-available-rooms"><?php _e('Số Phòng Có Sẵn', 'hotel'); ?>:</label></th>
                        <td>
                            <input type="number" id="bulk-available-rooms" name="total_for_sale" min="0" class="small-text">
                            <span class="description"><?php _e('Để trống để giữ số phòng hiện tại', 'hotel'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Trạng Thái', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-is-available">
                                <?php _e('Phòng đã đóng', 'hotel'); ?>
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
            <button type="button" class="button hme-modal-close"><?php _e('Hủy', 'hotel'); ?></button>
            <button type="submit" form="bulk-update-form" id="update-rates" class="button button-primary"><?php _e('Cập Nhật Giá', 'hotel'); ?></button>
        </div>
    </div>
</div>

<!-- Copy Rates Modal -->
<div id="copy-rates-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3><?php _e('Sao Chép Giá', 'hotel'); ?></h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="copy-rates-form">
                <h4><?php _e('Khoảng Thời Gian Nguồn', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-room-type" class="required"><?php _e('Loại Phòng', 'hotel'); ?>:</label></th>
                        <td>
                            <select id="copy-room-type" name="room_type_id" required>
                                <option value=""><?php _e('Chọn Loại Phòng', 'hotel'); ?></option>
                                <?php foreach ($room_types as $room):
                                    $room_name = $room['title'][$current_lang]; ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="copy-source-from" class="required"><?php _e('Từ Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="copy-source-from" name="source_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-source-to" class="required"><?php _e('Đến Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="copy-source-to" name="source_date_to" required></td>
                    </tr>
                </table>

                <h4><?php _e('Khoảng Thời Gian Đích', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="copy-target-from" class="required"><?php _e('Từ Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="copy-target-from" name="target_date_from" required></td>
                    </tr>
                    <tr>
                        <th><label for="copy-target-to" class="required"><?php _e('Đến Ngày', 'hotel'); ?>:</label></th>
                        <td><input type="date" id="copy-target-to" name="target_date_to" required></td>
                    </tr>
                </table>

                <h4><?php _e('Tùy Chọn Sao Chép', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Sao Chép Gì', 'hotel'); ?>:</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Tùy chọn sao chép', 'hotel'); ?></span></legend>
                                <label>
                                    <input type="checkbox" name="copy_rates" checked>
                                    <?php _e('Giá Phòng', 'hotel'); ?>
                                    <span class="description"><?php _e('(Sao chép giá và min/max stay)', 'hotel'); ?></span>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="copy_availability" checked>
                                    <?php _e('Tình Trạng Phòng', 'hotel'); ?>
                                    <span class="description"><?php _e('(Sao chép số phòng bán và trạng thái)', 'hotel'); ?></span>
                                </label><br>
                                <label>
                                    <input type="checkbox" name="copy_restrictions" checked>
                                    <?php _e('Hạn Chế Bán Hàng', 'hotel'); ?>
                                    <span class="description"><?php _e('(Sao chép CTA, CTD, CLOSED)', 'hotel'); ?></span>
                                </label><br>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Ghi Đè', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="overwrite_existing">
                                <?php _e('Ghi đè giá hiện tại', 'hotel'); ?>
                            </label>
                            <p class="description"><?php _e('Nếu không chọn, chỉ sao chép vào ngày chưa có giá', 'hotel'); ?></p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close"><?php _e('Hủy', 'hotel'); ?></button>
            <button type="submit" form="copy-rates-form" class="button button-primary"><?php _e('Sao Chép Giá', 'hotel'); ?></button>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div id="template-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content hme-modal-large">
        <div class="hme-modal-header">
            <h3><?php _e('Tạo Mẫu Mới', 'hotel'); ?></h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="template-form">
                <input type="hidden" id="template-id" name="template_id">
                <table class="form-table">
                    <tr>
                        <th><label for="template-name" class="required"><?php _e('Tên Mẫu', 'hotel'); ?>:</label></th>
                        <td>
                            <input type="text" id="template-name" name="template_name" class="regular-text" required>
                            <p class="description"><?php _e('Đặt tên mô tả cho mẫu của bạn', 'hotel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-description"><?php _e('Mô Tả', 'hotel'); ?>:</label></th>
                        <td>
                            <textarea id="template-description" name="template_description" class="large-text" rows="3"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-room-type" class="required"><?php _e('Loại Phòng', 'hotel'); ?>:</label></th>
                        <td>
                            <select id="template-room-type" name="roomtype_id" required>
                                <option value=""><?php _e('Chọn loại phòng', 'hotel'); ?></option>
                                <?php foreach ($room_types as $room):
                                    $room_name = $room['title'][$current_lang]; ?>
                                    <option value="<?php echo esc_attr($room['id']); ?>"><?php echo esc_html($room_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h4><?php _e('Giá Theo Ngày Trong Tuần', 'hotel'); ?></h4>
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

                <h4><?php _e('Quy Tắc Lưu Trú', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="template-min-stay"><?php _e('Số Đêm Tối Thiểu', 'hotel'); ?>:</label></th>
                        <td><input type="number" id="template-min-stay" name="min_stay" min="1" value="1" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="template-max-stay"><?php _e('Số Đêm Tối Đa', 'hotel'); ?>:</label></th>
                        <td><input type="number" id="template-max-stay" name="max_stay" min="1" value="30" class="small-text"></td>
                    </tr>
                </table>

                <h4><?php _e('Hạn Chế Bán Hàng', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="template-close-to-arrival"><?php _e('Hạn Chế Check-in', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="template-close-to-arrival" name="close_to_arrival">
                                <?php _e('Không cho phép check-in vào ngày này (CTA)', 'hotel'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-close-to-departure"><?php _e('Hạn Chế Check-out', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="template-close-to-departure" name="close_to_departure">
                                <?php _e('Không cho phép check-out vào ngày này (CTD)', 'hotel'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="template-is-closed"><?php _e('Đóng Bán', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="template-is-closed" name="is_closed">
                                <?php _e('Đóng bán hoàn toàn', 'hotel'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h4><?php _e('Trạng Thái', 'hotel'); ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="template-is-active"><?php _e('Kích Hoạt', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="template-is-active" name="is_active" checked>
                                <?php _e('Mẫu đang hoạt động', 'hotel'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close"><?php _e('Hủy', 'hotel'); ?></button>
            <button type="submit" form="template-form" class="button button-primary"><?php _e('Lưu Mẫu', 'hotel'); ?></button>
        </div>
    </div>
</div>

<script>
// Pass room types data to JavaScript
window.hmeRoomTypes = <?php echo json_encode(array_map(function($room) use ($current_lang) {
    return [
        'id' => $room['id'],
        'name' => $room['title'][$current_lang] ?? $room['title']['en'] ?? 'Unknown'
    ];
}, $room_types)); ?>;
</script>

<!-- Apply Template Modal -->
<div id="apply-template-modal" class="hme-modal" style="display: none;">
    <div class="hme-modal-content">
        <div class="hme-modal-header">
            <h3><?php _e('Áp Dụng Mẫu Giá', 'hotel'); ?></h3>
            <button type="button" class="hme-modal-close">&times;</button>
        </div>
        <div class="hme-modal-body">
            <form id="apply-template-form">
                <input type="hidden" id="apply-template-id" name="template_id">
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Tên Mẫu', 'hotel'); ?>:</label></th>
                        <td>
                            <strong id="apply-template-name"></strong>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Loại Phòng', 'hotel'); ?>:</label></th>
                        <td>
                            <strong id="apply-room-type-name"></strong>
                            <input type="hidden" id="apply-room-type" name="roomtype_id">
                            <p class="description"><?php _e('Loại phòng được lấy từ template', 'hotel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="apply-date-from" class="required"><?php _e('Từ Ngày', 'hotel'); ?>:</label></th>
                        <td>
                            <input type="date" id="apply-date-from" name="date_from" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="apply-date-to" class="required"><?php _e('Đến Ngày', 'hotel'); ?>:</label></th>
                        <td>
                            <input type="date" id="apply-date-to" name="date_to" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="apply-overwrite"><?php _e('Tùy Chọn', 'hotel'); ?>:</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="apply-overwrite" name="overwrite_existing" value="1">
                                <?php _e('Ghi đè lên giá hiện tại', 'hotel'); ?>
                            </label>
                            <p class="description"><?php _e('Nếu không chọn, chỉ áp dụng cho các ngày chưa có giá', 'hotel'); ?></p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="hme-modal-footer">
            <button type="button" class="button hme-modal-close"><?php _e('Hủy', 'hotel'); ?></button>
            <button type="submit" form="apply-template-form" class="button button-primary"><?php _e('Áp Dụng Mẫu', 'hotel'); ?></button>
        </div>
    </div>
</div>