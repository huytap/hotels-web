<?php
// Lấy danh sách ngôn ngữ từ Polylang nếu đã kích hoạt, nếu không thì mặc định là 'vi' và 'en'
$languages = function_exists('pll_languages_list') ? pll_languages_list() : ['vi', 'en'];
$roomTypes = HME_Room_Rate_Manager::get_room_types();
$current_lang = get_locale();
?>

<div class="wrap">
    <h1>Cập Nhật Khuyến Mãi</h1>
    <form id="update-promotion-form" action="" method="post">
        <div class="hme-form-section">
            <h2 class="hme-form-title">Thông tin cơ bản</h2>
            <input type="hidden" name="id" id="promotion_id">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="promotion_code">Mã Khuyến Mãi</label></th>
                        <td>
                            <input readonly="readonly" type="text" name="promotion_code" id="promotion_code" class="regular-text" required>
                        </td>
                    </tr>
                    <?php foreach ($languages as $lang_code) : ?>
                        <tr>
                            <th scope="row"><label for="name_<?php echo esc_attr($lang_code); ?>">Tên KM (<?php echo esc_html(strtoupper($lang_code)); ?>)</label></th>
                            <td>
                                <input type="text" name="name[<?php echo esc_attr($lang_code); ?>]" id="name_<?php echo esc_attr($lang_code); ?>" class="regular-text" <?php echo ($lang_code == pll_current_language()) ? 'required' : ''; ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($languages as $lang_code) : ?>
                        <tr>
                            <th scope="row"><label for="description_<?php echo esc_attr($lang_code); ?>">Mô tả (<?php echo esc_html(strtoupper($lang_code)); ?>)</label></th>
                            <td>
                                <textarea name="description[<?php echo esc_attr($lang_code); ?>]" id="description_<?php echo esc_attr($lang_code); ?>" rows="4" cols="50"></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row"><label for="is_active">Trạng thái</label></th>
                        <td>
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked> Kích hoạt
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="hme-form-section">
            <h2 class="hme-form-title">Chi tiết giảm giá</h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="type">Loại Khuyến Mãi</label></th>
                        <td>
                            <select name="type" id="type">
                                <option value="early_bird">Đặt sớm (Early Bird)</option>
                                <option value="last_minutes">Phút chót (Last Minutes)</option>
                                <option value="other">Khác (Other)</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="booking-days-row">
                        <th scope="row"><label for="booking_days_in_advance" id="booking-days-label">Số ngày đặt trước</label></th>
                        <td>
                            <input type="number" name="booking_days_in_advance" id="booking_days_in_advance" class="regular-text" min="0">
                            <p class="description">Số ngày tối thiểu khách phải đặt trước so với ngày nhận phòng.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="value_type">Loại giá trị</label></th>
                        <td>
                            <select name="value_type" id="value_type">
                                <option value="percentage">Phần trăm (%)</option>
                                <option value="fixed">Số tiền cố định (VNĐ)</option>
                                <option value="free_nights">Đêm miễn phí</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="value">Giá trị</label></th>
                        <td>
                            <input type="number" name="value" id="value" class="regular-text" min="0" required>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="hme-form-section">
            <h2 class="hme-form-title">Phòng & Dịch vụ áp dụng</h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label>Phòng áp dụng</label></th>
                        <td>
                            <fieldset>
                                <?php foreach ($roomTypes as $roomType) : ?>
                                    <label>
                                        <input type="checkbox"
                                            name="roomtypes[]"
                                            value="<?php echo esc_attr($roomType['id']); ?>">
                                        <?php echo esc_html($roomType['title'][$current_lang] ?? $roomType['title']['en']); ?>
                                    </label><br>
                                <?php endforeach; ?>
                                <p class="description">Chọn một hoặc nhiều loại phòng áp dụng khuyến mãi.</p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="hme-form-section">
            <h2 class="hme-form-title">Thời gian & Hạn chế</h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="start_date">Ngày bắt đầu</label></th>
                        <td>
                            <input type="date" name="start_date" id="start_date" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="end_date">Ngày kết thúc</label></th>
                        <td>
                            <input type="date" name="end_date" id="end_date" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="min_stay">Số đêm ở tối thiểu</label></th>
                        <td>
                            <input type="number" name="min_stay" id="min_stay" class="regular-text" min="0">
                            <p class="description">Số đêm tối thiểu để áp dụng khuyến mãi.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="max_stay">Số đêm ở tối đa</label></th>
                        <td>
                            <input type="number" name="max_stay" id="max_stay" class="regular-text" min="0">
                            <p class="description">Số đêm tối đa để áp dụng khuyến mãi.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Thêm Khuyến Mãi">
        </p>
    </form>
</div>
<?php
include HME_PLUGIN_PATH . 'views/promotions/includes/script.php';
?>
<script>
    jQuery(document).ready(function($) {
        loadPromotion();

        function loadPromotion() {
            //showLoading();
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            const promotionId = params.get('id');

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
                        displayPromotion(response.data.data);
                    } else {
                        $('#promotion-detail-content').html(`<p class="error">Error: ${response.data}</p>`);
                    }
                },
                error: function() {
                    $('#promotion-detail-content').html('<p class="error">Error loading promotion details</p>');
                }
            });
        }

        function displayPromotion(promotion) {
            if (!promotion || typeof promotion !== 'object') {
                console.error('Invalid promotion data provided.');
                return;
            }

            const form = document.getElementById('update-promotion-form');
            if (!form) {
                console.error('Form not found.');
                return;
            }

            // Gán giá trị cho các trường
            const promotionIdInput = form.querySelector('#promotion_id');
            if (promotionIdInput) {
                promotionIdInput.value = promotion.id;
            }

            const promoCodeInput = form.querySelector('#promotion_code');
            if (promoCodeInput) {
                promoCodeInput.value = promotion.promotion_code;
            }

            // Điền dữ liệu đa ngôn ngữ một cách an toàn
            for (const lang in promotion.name) {
                const nameField = form.querySelector(`input[name="name[${lang}]"]`);
                if (nameField) {
                    nameField.value = promotion.name[lang];
                }
            }

            for (const lang in promotion.description) {
                const descriptionField = form.querySelector(`textarea[name="description[${lang}]"]`);
                if (descriptionField) {
                    descriptionField.value = promotion.description[lang];
                }
            }

            // Điền các trường khác
            const typeSelect = form.querySelector('#type');
            if (typeSelect) {
                typeSelect.value = promotion.type;
            }

            const valueTypeSelect = form.querySelector('#value_type');
            if (valueTypeSelect) {
                valueTypeSelect.value = promotion.value_type;
            }

            const valueInput = form.querySelector('#value');
            if (valueInput) {
                valueInput.value = promotion.value;
            }

            const startDateInput = form.querySelector('#start_date');
            if (startDateInput) {
                startDateInput.value = promotion.start_date;
            }

            const endDateInput = form.querySelector('#end_date');
            if (endDateInput) {
                endDateInput.value = promotion.end_date;
            }

            const minStayInput = form.querySelector('#min_stay');
            if (minStayInput) {
                minStayInput.value = promotion.min_stay;
            }

            const maxStayInput = form.querySelector('#max_stay');
            if (maxStayInput) {
                maxStayInput.value = promotion.max_stay;
            }

            const bookingDaysInput = form.querySelector('#booking_days_in_advance');
            if (bookingDaysInput) {
                bookingDaysInput.value = promotion.booking_days_in_advance;
            }

            // Xử lý checkbox is_active
            const isActiveCheckbox = form.querySelector('#is_active');
            if (isActiveCheckbox) {
                isActiveCheckbox.checked = promotion.is_active === 1;
            }

            // Xử lý các checkbox roomtypes
            if (promotion.roomtypes && Array.isArray(promotion.roomtypes)) {
                const allRoomtypes = form.querySelectorAll('input[name="roomtypes[]"]');
                allRoomtypes.forEach(checkbox => {
                    const isChecked = promotion.roomtypes.some(rt => rt.id == checkbox.value);
                    checkbox.checked = isChecked;
                });
            }

            // Cập nhật tiêu đề và nút submit
            document.querySelector('.wrap h1').textContent = 'Cập Nhật Khuyến Mãi';
            document.querySelector('input[type="submit"]').value = 'Cập Nhật Khuyến Mãi';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('update-promotion-form');
        form.addEventListener('submit', function(event) {
            // Ngăn chặn việc gửi form mặc định
            event.preventDefault();
            // Gọi hàm xử lý AJAX
            ajax_update_promotion();
        });
        /**
         * Hàm xử lý AJAX để tạo một khuyến mãi mới.
         */
        function ajax_update_promotion() {
            //showLoading();
            const form = document.getElementById('update-promotion-form');
            const formData = new FormData(form);

            // Lấy dữ liệu đa ngôn ngữ từ form
            const names = {};
            const descriptions = {};
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('name[')) {
                    const lang = key.match(/\[(.*?)\]/)[1];
                    names[lang] = value;
                } else if (key.startsWith('description[')) {
                    const lang = key.match(/\[(.*?)\]/)[1];
                    descriptions[lang] = value;
                }
            }
            const selectedRoomtypes = [];
            document.querySelectorAll('input[name="roomtypes[]"]:checked').forEach(checkbox => {
                selectedRoomtypes.push(checkbox.value);
            });
            // Tạo đối tượng dữ liệu để gửi đi
            const promotionData = {
                action: 'hme_update_promotion',
                nonce: hme_admin.nonce,
                name: names,
                description: descriptions,
                type: formData.get('type'),
                value_type: formData.get('value_type'),
                value: formData.get('value'),
                start_date: formData.get('start_date'),
                end_date: formData.get('end_date'),
                min_stay: formData.get('min_stay') || null,
                max_stay: formData.get('max_stay') || null,
                booking_days_in_advance: formData.get('booking_days_in_advance') || null,
                is_active: +formData.get('is_active'),
                roomtypes: selectedRoomtypes,
                id: formData.get('id')
            };

            // Gửi dữ liệu bằng jQuery.ajax hoặc Fetch API
            // Ví dụ sử dụng jQuery.ajax()
            jQuery.ajax({
                url: hme_admin.ajax_url,
                method: 'POST',
                data: promotionData,
                success: function(response) {
                    //hideLoading();
                    // Xử lý khi API thành công
                    alert('Promotion updated successfully');
                    // Có thể chuyển hướng người dùng hoặc làm mới trang
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    //hideLoading();
                    // Xử lý khi có lỗi
                    console.error('Error creating promotion:', textStatus, errorThrown);
                    alert('Lỗi: Không thể tạo khuyến mãi.');
                }
            });
        }
    });
</script>