<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-money-alt"></span>
        Chính Sách Giá
    </h1>
    <p class="description">Quản lý chính sách giá cho trẻ em và người lớn thêm</p>

    <div class="hme-pricing-policies-container">
        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="#child-age-policy" class="nav-tab nav-tab-active" data-tab="child-age-policy">
                <span class="dashicons dashicons-groups"></span>
                Chính Sách Độ Tuổi Trẻ Em
            </a>
            <a href="#room-pricing-policy" class="nav-tab" data-tab="room-pricing-policy">
                <span class="dashicons dashicons-admin-home"></span>
                Chính Sách Giá Phòng
            </a>
            <a href="#tax-settings" class="nav-tab" data-tab="tax-settings">
                <span class="dashicons dashicons-money-alt"></span>
                Thuế & Phí Dịch Vụ
            </a>
        </h2>

        <!-- Loading indicator -->
        <div id="pricing-policies-loading" class="hme-loading" style="display: none;">
            <div class="hme-spinner"></div>
            <p>Đang tải...</p>
        </div>

        <!-- Child Age Policy Tab -->
        <div id="child-age-policy-tab" class="hme-tab-content tab-active">
            <div class="hme-card">
                <h3>
                    <span class="dashicons dashicons-groups"></span>
                    Chính Sách Độ Tuổi Trẻ Em
                </h3>
                <p class="description">
                    Thiết lập các giới hạn độ tuổi cho việc tính phí trẻ em. Trẻ em dưới "tuổi miễn phí" sẽ không tính phí,
                    từ "tuổi miễn phí" đến "tuổi phụ thu" sẽ tính phụ thu, trên "tuổi phụ thu" sẽ tính như người lớn.
                </p>

                <form id="child-age-policy-form">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="free_age_limit">Tuổi Miễn Phí</label>
                                </th>
                                <td>
                                    <input type="number" id="free_age_limit" name="free_age_limit"
                                        class="small-text" min="0" max="17" value="6" required>
                                    <p class="description">Trẻ em dưới độ tuổi này sẽ được miễn phí (ví dụ: dưới 6 tuổi)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="surcharge_age_limit">Tuổi Phụ Thu</label>
                                </th>
                                <td>
                                    <input type="number" id="surcharge_age_limit" name="surcharge_age_limit"
                                        class="small-text" min="0" max="17" value="12" required>
                                    <p class="description">Trẻ em từ tuổi miễn phí đến tuổi này sẽ bị tính phụ thu (ví dụ: từ 6-12 tuổi)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="free_description_vi">Mô Tả Miễn Phí (Tiếng Việt)</label>
                                </th>
                                <td>
                                    <textarea id="free_description_vi" name="free_description[vi]"
                                        rows="3" cols="50" class="large-text"></textarea>
                                    <p class="description">Mô tả chính sách miễn phí bằng tiếng Việt</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="free_description_en">Mô Tả Miễn Phí (English)</label>
                                </th>
                                <td>
                                    <textarea id="free_description_en" name="free_description[en]"
                                        rows="3" cols="50" class="large-text"></textarea>
                                    <p class="description">Free policy description in English</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="surcharge_description_vi">Mô Tả Phụ Thu (Tiếng Việt)</label>
                                </th>
                                <td>
                                    <textarea id="surcharge_description_vi" name="surcharge_description[vi]"
                                        rows="3" cols="50" class="large-text"></textarea>
                                    <p class="description">Mô tả chính sách phụ thu bằng tiếng Việt</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="surcharge_description_en">Mô Tả Phụ Thu (English)</label>
                                </th>
                                <td>
                                    <textarea id="surcharge_description_en" name="surcharge_description[en]"
                                        rows="3" cols="50" class="large-text"></textarea>
                                    <p class="description">Surcharge policy description in English</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="is_active">Trạng Thái</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        Kích hoạt chính sách này
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-yes"></span>
                            Lưu Chính Sách Độ Tuổi
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Room Pricing Policy Tab -->
        <div id="room-pricing-policy-tab" class="hme-tab-content">
            <div class="hme-card">
                <h3>
                    <span class="dashicons dashicons-admin-home"></span>
                    Chính Sách Giá Phòng
                </h3>
                <p class="description">
                    Thiết lập giá cơ bản và phụ thu cho từng loại phòng. Giá cơ bản thường tính cho 2 người lớn,
                    người lớn thêm và trẻ em phụ thu sẽ tính thêm phí.
                </p>

                <div id="room-pricing-table-container">
                    <table class="wp-list-table widefat fixed striped" id="room-pricing-table">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column">Loại Phòng</th>
                                <th scope="col" class="manage-column">Sức Chứa Cơ Bản</th>
                                <th scope="col" class="manage-column">Giá Người Lớn Thêm</th>
                                <th scope="col" class="manage-column">Giá Phụ Thu Trẻ Em</th>
                                <th scope="col" class="manage-column">Trạng Thái</th>
                                <th scope="col" class="manage-column">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="room-pricing-table-body">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="hme-spinner"></div>
                                    Đang tải danh sách phòng...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tax Settings Tab -->
        <div id="tax-settings-tab" class="hme-tab-content">
            <div class="hme-card">
                <h3>
                    <span class="dashicons dashicons-money-alt"></span>
                    Cấu Hình Thuế & Phí Dịch Vụ
                </h3>
                <p class="description">
                    Thiết lập tỷ lệ VAT (%) và phí dịch vụ (%) cho khách sạn. Chọn xem giá hiển thị đã bao gồm hay chưa bao gồm thuế và phí.
                </p>

                <form id="tax-settings-form">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="vat_rate">Tỷ Lệ VAT (%)</label>
                                </th>
                                <td>
                                    <input type="number" id="vat_rate" name="vat_rate"
                                        class="small-text" min="0" max="100" step="0.01" value="10.00" required>
                                    <span class="suffix">%</span>
                                    <p class="description">Thuế giá trị gia tăng (VAT) áp dụng cho giá phòng</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="service_charge_rate">Phí Dịch Vụ (%)</label>
                                </th>
                                <td>
                                    <input type="number" id="service_charge_rate" name="service_charge_rate"
                                        class="small-text" min="0" max="100" step="0.01" value="5.00" required>
                                    <span class="suffix">%</span>
                                    <p class="description">Phí dịch vụ (Service Charge) áp dụng cho giá phòng</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="prices_include_tax">Giá Hiển Thị</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="prices_include_tax" name="prices_include_tax" value="1">
                                        Giá đã bao gồm VAT và phí dịch vụ
                                    </label>
                                    <p class="description">
                                        <strong>Bỏ chọn:</strong> Giá hiển thị chưa bao gồm thuế và phí. Thuế & phí sẽ được tính thêm khi thanh toán.<br>
                                        <strong>Chọn:</strong> Giá hiển thị đã bao gồm thuế và phí. Không tính thêm khi thanh toán.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Tổng Tỷ Lệ</th>
                                <td>
                                    <p id="total_tax_rate" style="font-size: 14px; font-weight: bold; color: #2271b1;">
                                        Tổng: <span id="total_rate_display">15.00</span>%
                                    </p>
                                    <p class="description">
                                        Tổng phần trăm thuế và phí áp dụng = VAT (%) + Phí dịch vụ (%)
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-yes"></span>
                            Lưu Cấu Hình Thuế & Phí
                        </button>
                    </p>
                </form>
            </div>

            <!-- Example Calculation -->
            <div class="hme-card" style="background: #f0f6fc; border-color: #2271b1;">
                <h4 style="margin-top: 0; color: #2271b1;">
                    <span class="dashicons dashicons-info"></span>
                    Ví Dụ Tính Toán
                </h4>
                <table class="widefat" id="tax-example-table">
                    <tbody>
                        <tr>
                            <td><strong>Giá gốc phòng</strong></td>
                            <td class="text-right">1,000,000 VNĐ</td>
                        </tr>
                        <tr>
                            <td>VAT (<span class="vat-example">10</span>%)</td>
                            <td class="text-right"><span id="vat-amount-example">100,000</span> VNĐ</td>
                        </tr>
                        <tr>
                            <td>Phí dịch vụ (<span class="service-example">5</span>%)</td>
                            <td class="text-right"><span id="service-amount-example">50,000</span> VNĐ</td>
                        </tr>
                        <tr style="border-top: 2px solid #2271b1; font-weight: bold;">
                            <td>
                                <strong>Giá <span id="price-type-label">chưa bao gồm</span> thuế & phí</strong>
                            </td>
                            <td class="text-right">
                                <strong><span id="final-price-example">1,000,000</span> VNĐ</strong>
                            </td>
                        </tr>
                        <tr style="font-weight: bold; color: #2271b1;">
                            <td><strong>Khách phải trả</strong></td>
                            <td class="text-right">
                                <strong><span id="customer-pays-example">1,150,000</span> VNĐ</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Room Pricing Edit Modal -->
    <div id="room-pricing-modal" class="hme-modal" style="display: none;">
        <div class="hme-modal-content">
            <div class="hme-modal-header">
                <h3>Chỉnh Sửa Chính Sách Giá Phòng</h3>
                <span class="hme-modal-close">&times;</span>
            </div>
            <div class="hme-modal-body">
                <form id="room-pricing-form">
                    <input type="hidden" id="edit_roomtype_id" name="roomtype_id">

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="edit_roomtype_name">Loại Phòng</label>
                                </th>
                                <td>
                                    <strong id="edit_roomtype_name"></strong>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="edit_base_occupancy">Sức Chứa Cơ Bản</label>
                                </th>
                                <td>
                                    <input type="number" id="edit_base_occupancy" name="base_occupancy"
                                        class="small-text" min="1" max="10" value="2" required>
                                    <p class="description">Số người cơ bản được tính trong giá gốc (thường là 2 người)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="edit_additional_adult_price">Giá Người Lớn Thêm</label>
                                </th>
                                <td>
                                    <input type="number" id="edit_additional_adult_price" name="additional_adult_price"
                                        class="regular-text" min="0" step="1000" value="0" required>
                                    <span class="suffix">VNĐ/đêm</span>
                                    <p class="description">Giá thêm cho mỗi người lớn vượt quá sức chứa cơ bản</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="edit_child_surcharge_price">Giá Phụ Thu Trẻ Em</label>
                                </th>
                                <td>
                                    <input type="number" id="edit_child_surcharge_price" name="child_surcharge_price"
                                        class="regular-text" min="0" step="1000" value="0" required>
                                    <span class="suffix">VNĐ/đêm</span>
                                    <p class="description">Giá phụ thu cho mỗi trẻ em trong độ tuổi phụ thu</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="edit_is_active">Trạng Thái</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                                        Kích hoạt chính sách này
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="hme-modal-footer">
                <button type="button" class="button" onclick="closePricingModal()">Hủy</button>
                <button type="button" class="button button-primary" onclick="saveRoomPricing()">
                    <span class="dashicons dashicons-yes"></span>
                    Lưu Thay Đổi
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .hme-pricing-policies-container {
        margin-top: 20px;
    }

    .hme-tab-content {
        display: none;
        margin-top: 20px;
    }

    .hme-tab-content.tab-active {
        display: block;
    }

    .hme-card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .hme-card h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hme-loading {
        text-align: center;
        padding: 40px;
    }

    .hme-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #0073aa;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .hme-modal {
        position: fixed;
        z-index: 100000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .hme-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        border: 1px solid #888;
        border-radius: 4px;
        width: 80%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .hme-modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .hme-modal-header h3 {
        margin: 0;
    }

    .hme-modal-close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .hme-modal-close:hover,
    .hme-modal-close:focus {
        color: black;
    }

    .hme-modal-body {
        padding: 20px;
    }

    .hme-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: right;
    }

    .hme-modal-footer .button {
        margin-left: 10px;
    }

    .suffix {
        color: #666;
        font-style: italic;
        margin-left: 5px;
    }

    .text-center {
        text-align: center;
    }

    .status-active {
        color: #00a32a;
        font-weight: bold;
    }

    .status-inactive {
        color: #d63638;
        font-weight: bold;
    }

    .nav-tab {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Tab switching
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();

            // Remove active class from all tabs and content
            $('.nav-tab').removeClass('nav-tab-active');
            $('.hme-tab-content').removeClass('tab-active');

            // Add active class to clicked tab and corresponding content
            $(this).addClass('nav-tab-active');
            const tabId = $(this).data('tab') + '-tab';
            $('#' + tabId).addClass('tab-active');

            // Load data for the active tab
            if ($(this).data('tab') === 'child-age-policy') {
                loadChildAgePolicy();
            } else if ($(this).data('tab') === 'room-pricing-policy') {
                loadRoomPricingPolicies();
            } else if ($(this).data('tab') === 'tax-settings') {
                loadTaxSettings();
            }
        });

        // Initialize
        loadChildAgePolicy();

        // Child Age Policy Form Submit
        $('#child-age-policy-form').on('submit', function(e) {
            e.preventDefault();
            saveChildAgePolicy();
        });

        // Tax Settings Form Submit
        $('#tax-settings-form').on('submit', function(e) {
            e.preventDefault();
            saveTaxSettings();
        });

        // Update total rate and example when VAT or Service Charge changes
        $('#vat_rate, #service_charge_rate, #prices_include_tax').on('input change', function() {
            updateTaxExample();
        });

        function loadChildAgePolicy() {
            showLoading('child-age-policy');

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: 'child-age-policy',
                    method: 'GET'
                },
                success: function(response) {
                    hideLoading('child-age-policy');
                    if (response.success && response.data) {
                        populateChildAgeForm(response.data);
                    } else {
                        console.log('No existing child age policy found, using defaults');
                    }
                },
                error: function() {
                    hideLoading('child-age-policy');
                    showNotice('Không thể tải chính sách độ tuổi trẻ em', 'error');
                }
            });
        }

        function populateChildAgeForm(data) {
            $('#free_age_limit').val(data.free_age_limit || 6);
            $('#surcharge_age_limit').val(data.surcharge_age_limit || 12);
            $('#is_active').prop('checked', data.is_active !== false);

            // Handle multilingual descriptions
            if (data.free_description) {
                $('#free_description_vi').val(data.free_description.vi || '');
                $('#free_description_en').val(data.free_description.en || '');
            }

            if (data.surcharge_description) {
                $('#surcharge_description_vi').val(data.surcharge_description.vi || '');
                $('#surcharge_description_en').val(data.surcharge_description.en || '');
            }
        }

        function saveChildAgePolicy() {
            const formData = {
                free_age_limit: parseInt($('#free_age_limit').val()),
                surcharge_age_limit: parseInt($('#surcharge_age_limit').val()),
                is_active: +$('#is_active').is(':checked'),
                free_description: {
                    vi: $('#free_description_vi').val(),
                    en: $('#free_description_en').val()
                },
                surcharge_description: {
                    vi: $('#surcharge_description_vi').val(),
                    en: $('#surcharge_description_en').val()
                }
            };

            // Validate
            if (formData.surcharge_age_limit <= formData.free_age_limit) {
                showNotice('Tuổi phụ thu phải lớn hơn tuổi miễn phí', 'error');
                return;
            }

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: 'child-age-policy',
                    method: 'PUT',
                    data: formData
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Chính sách độ tuổi trẻ em đã được lưu thành công', 'success');
                    } else {
                        showNotice('Không thể lưu chính sách: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Lỗi kết nối máy chủ', 'error');
                }
            });
        }

        function loadRoomPricingPolicies() {
            showTableLoading();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: 'room-pricing-policies',
                    method: 'GET'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        populateRoomPricingTable(response.data);
                    } else {
                        showTableError('Không thể tải danh sách chính sách giá phòng');
                    }
                },
                error: function() {
                    showTableError('Lỗi kết nối máy chủ');
                }
            });
        }

        function populateRoomPricingTable(data) {
            const tbody = $('#room-pricing-table-body');
            tbody.empty();

            if (data.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>');
                return;
            }

            data.forEach(function(room) {
                const row = `
                <tr>
                    <td><strong>${room.roomtype_name}</strong></td>
                    <td>${room.base_occupancy} người</td>
                    <td>${formatCurrency(room.additional_adult_price)} VNĐ/đêm</td>
                    <td>${formatCurrency(room.child_surcharge_price)} VNĐ/đêm</td>
                    <td>
                        <span class="status-${room.is_active ? 'active' : 'inactive'}">
                            ${room.is_active ? 'Kích hoạt' : 'Tắt'}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="button button-small"
                                onclick="editRoomPricing(${room.roomtype_id}, '${room.roomtype_name}', ${room.base_occupancy}, ${room.additional_adult_price}, ${room.child_surcharge_price}, ${room.is_active})">
                            <span class="dashicons dashicons-edit"></span>
                            Chỉnh sửa
                        </button>
                    </td>
                </tr>
            `;
                tbody.append(row);
            });
        }

        function showTableLoading() {
            $('#room-pricing-table-body').html('<tr><td colspan="6" class="text-center"><div class="hme-spinner"></div> Đang tải...</td></tr>');
        }

        function showTableError(message) {
            $('#room-pricing-table-body').html(`<tr><td colspan="6" class="text-center" style="color: #d63638;">${message}</td></tr>`);
        }

        function showLoading(section) {
            $(`#${section}-loading`).show();
        }

        function hideLoading(section) {
            $(`#${section}-loading`).hide();
        }

        function showNotice(message, type = 'info') {
            const noticeClass = type === 'error' ? 'notice-error' : type === 'success' ? 'notice-success' : 'notice-info';
            const notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);

            $('.wrap h1').after(notice);

            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                notice.fadeOut(function() {
                    notice.remove();
                });
            }, 3000);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount || 0);
        }

        // Global functions for modal
        window.editRoomPricing = function(id, name, baseOccupancy, additionalPrice, childPrice, isActive) {
            $('#edit_roomtype_id').val(id);
            $('#edit_roomtype_name').text(name);
            $('#edit_base_occupancy').val(baseOccupancy);
            $('#edit_additional_adult_price').val(additionalPrice);
            $('#edit_child_surcharge_price').val(childPrice);
            $('#edit_is_active').prop('checked', isActive);

            $('#room-pricing-modal').show();
        };

        window.closePricingModal = function() {
            $('#room-pricing-modal').hide();
        };

        window.saveRoomPricing = function() {
            const roomtypeId = $('#edit_roomtype_id').val();
            const formData = {
                base_occupancy: parseInt($('#edit_base_occupancy').val()),
                additional_adult_price: parseFloat($('#edit_additional_adult_price').val()),
                child_surcharge_price: parseFloat($('#edit_child_surcharge_price').val()),
                is_active: +$('#edit_is_active').is(':checked')
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: `room-pricing-policies/${roomtypeId}`,
                    method: 'PUT',
                    data: formData
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Chính sách giá phòng đã được cập nhật', 'success');
                        closePricingModal();
                        loadRoomPricingPolicies(); // Reload table
                    } else {
                        showNotice('Không thể cập nhật: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Lỗi kết nối máy chủ', 'error');
                }
            });
        };

        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if (e.target.id === 'room-pricing-modal') {
                closePricingModal();
            }
        });

        // ============ TAX SETTINGS FUNCTIONS ============
        function loadTaxSettings() {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: 'hotels/tax-settings',
                    method: 'GET'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        populateTaxSettingsForm(response.data);
                    }
                },
                error: function() {
                    showNotice('Không thể tải cấu hình thuế', 'error');
                }
            });
        }

        function populateTaxSettingsForm(data) {
            $('#vat_rate').val(parseFloat(data.vat_rate || 10.00).toFixed(2));
            $('#service_charge_rate').val(parseFloat(data.service_charge_rate || 5.00).toFixed(2));
            $('#prices_include_tax').prop('checked', data.prices_include_tax || false);
            updateTaxExample();
        }

        function saveTaxSettings() {
            const formData = {
                vat_rate: parseFloat($('#vat_rate').val()),
                service_charge_rate: parseFloat($('#service_charge_rate').val()),
                prices_include_tax: $('#prices_include_tax').is(':checked')
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_api_call',
                    nonce: hme_admin.nonce,
                    endpoint: 'hotels/tax-settings',
                    method: 'PUT',
                    data: formData
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Cấu hình thuế & phí đã được lưu thành công', 'success');
                    } else {
                        showNotice('Không thể lưu cấu hình: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Lỗi kết nối máy chủ', 'error');
                }
            });
        }

        function updateTaxExample() {
            const vatRate = parseFloat($('#vat_rate').val()) || 0;
            const serviceRate = parseFloat($('#service_charge_rate').val()) || 0;
            const pricesIncludeTax = $('#prices_include_tax').is(':checked');
            const totalRate = vatRate + serviceRate;

            // Update total rate display
            $('#total_rate_display').text(totalRate.toFixed(2));

            // Update example table
            const basePrice = 1000000;
            const vatAmount = Math.round(basePrice * vatRate / 100);
            const serviceAmount = Math.round(basePrice * serviceRate / 100);
            const totalTax = vatAmount + serviceAmount;

            $('.vat-example').text(vatRate.toFixed(2));
            $('.service-example').text(serviceRate.toFixed(2));
            $('#vat-amount-example').text(formatCurrency(vatAmount));
            $('#service-amount-example').text(formatCurrency(serviceAmount));

            if (pricesIncludeTax) {
                // Giá hiển thị đã bao gồm thuế
                $('#price-type-label').text('đã bao gồm');
                $('#final-price-example').text(formatCurrency(basePrice + totalTax));
                $('#customer-pays-example').text(formatCurrency(basePrice + totalTax));
            } else {
                // Giá hiển thị chưa bao gồm thuế
                $('#price-type-label').text('chưa bao gồm');
                $('#final-price-example').text(formatCurrency(basePrice));
                $('#customer-pays-example').text(formatCurrency(basePrice + totalTax));
            }
        }
    });
</script>