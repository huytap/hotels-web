<?php
// Lấy danh sách ngôn ngữ từ Polylang nếu đã kích hoạt, nếu không thì mặc định là 'vi' và 'en'
$languages = function_exists('pll_languages_list') ? pll_languages_list() : ['vi', 'en'];
$current_lang = get_locale();
$roomTypes = HME_Room_Rate_Manager::get_room_types();
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
                                <?php
                                $content   = ''; // sẽ được điền từ AJAX khi load dữ liệu
                                $editor_id = 'description_' . sanitize_key($lang_code); // dùng sanitize_key cho id

                                wp_editor(
                                    $content,
                                    $editor_id,
                                    [
                                        'textarea_name' => "description[{$lang_code}]", // name field
                                        'textarea_rows' => 10,
                                        'media_buttons' => true,   // Hiện nút Add Media
                                        'teeny'         => false,  // true = toolbar đơn giản
                                        'quicktags'     => true,   // bật tab Text
                                    ]
                                );
                                ?>
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
                                        <?php echo esc_html($roomType['title']); ?>
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

        <div class="hme-form-section">
            <h2 class="hme-form-title">Blackout & Hạn chế ngày</h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="blackout_start_date">Blackout từ ngày</label></th>
                        <td>
                            <input type="date" name="blackout_start_date" id="blackout_start_date" class="regular-text">
                            <p class="description">Ngày bắt đầu không áp dụng khuyến mãi (tùy chọn).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="blackout_end_date">Blackout đến ngày</label></th>
                        <td>
                            <input type="date" name="blackout_end_date" id="blackout_end_date" class="regular-text">
                            <p class="description">Ngày kết thúc không áp dụng khuyến mãi (tùy chọn).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Ngày trong tuần áp dụng</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">Chọn ngày trong tuần</legend>
                                <label><input type="checkbox" name="valid_monday" value="1"> Thứ 2</label><br>
                                <label><input type="checkbox" name="valid_tuesday" value="1"> Thứ 3</label><br>
                                <label><input type="checkbox" name="valid_wednesday" value="1"> Thứ 4</label><br>
                                <label><input type="checkbox" name="valid_thursday" value="1"> Thứ 5</label><br>
                                <label><input type="checkbox" name="valid_friday" value="1"> Thứ 6</label><br>
                                <label><input type="checkbox" name="valid_saturday" value="1"> Thứ 7</label><br>
                                <label><input type="checkbox" name="valid_sunday" value="1"> Chủ nhật</label><br>
                                <p class="description">Khuyến mãi chỉ áp dụng vào những ngày được chọn.</p>
                                <button type="button" id="select-all-days" class="button">Chọn tất cả</button>
                                <button type="button" id="select-weekdays-only" class="button">Chỉ ngày thường</button>
                                <button type="button" id="select-weekend-only" class="button">Chỉ cuối tuần</button>
                            </fieldset>
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
    // Lấy danh sách ngôn ngữ từ PHP
    const availableLanguages = <?php echo json_encode($languages); ?>;

    // Tạo mapping giữa language code và editor ID để đảm bảo khớp với PHP
    const editorIdMapping = {};
    <?php foreach ($languages as $lang_code) : ?>
        editorIdMapping['<?php echo esc_js($lang_code); ?>'] = '<?php echo esc_js('description_' . sanitize_key($lang_code)); ?>';
    <?php endforeach; ?>
    jQuery(document).ready(function($) {
        loadPromotion();

        function loadPromotion() {
            //showLoading();
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            const promotionId = params.get('id');

            // Đợi TinyMCE editors được khởi tạo
            function waitForTinyMCE(callback) {
                if (typeof tinymce !== 'undefined') {
                    // Kiểm tra xem tất cả editors đã được khởi tạo chưa
                    const allEditorsReady = availableLanguages.every(lang => {
                        const editorId = editorIdMapping[lang];
                        return tinymce.get(editorId) !== null;
                    });

                    if (allEditorsReady) {
                        callback();
                    } else {
                        setTimeout(() => waitForTinyMCE(callback), 100);
                    }
                } else {
                    setTimeout(() => waitForTinyMCE(callback), 100);
                }
            }

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
                        waitForTinyMCE(() => {
                            displayPromotion(response.data.data);
                        });
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
                const editorId = editorIdMapping[lang];
                console.log(`Setting description for ${lang}, editor ID: ${editorId}, content:`, promotion.description[lang]);

                // Kiểm tra xem TinyMCE editor có tồn tại không
                if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                    // Nếu editor đã được khởi tạo, set content
                    tinymce.get(editorId).setContent(promotion.description[lang] || '');
                    console.log(`Set content via TinyMCE for ${lang}`);
                } else {
                    // Fallback cho textarea nếu editor chưa được khởi tạo
                    const descriptionField = form.querySelector(`textarea[name="description[${lang}]"]`);
                    if (descriptionField) {
                        descriptionField.value = promotion.description[lang] || '';
                        console.log(`Set content via textarea for ${lang}`);
                    } else {
                        console.error(`No editor or textarea found for ${lang}, editor ID: ${editorId}`);
                    }
                }
            }

            // Điền các trường khác
            const bookingDaysRow = document.getElementById('booking-days-row');
            const bookingDaysInput = document.getElementById('booking_days_in_advance');
            const typeSelect = form.querySelector('#type');
            if (typeSelect) {
                typeSelect.value = promotion.type;
                if (typeSelect.value == 'other') {
                    bookingDaysRow.style.display = 'none';
                    bookingDaysInput.removeAttribute('required');
                }
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

            // Blackout dates
            const blackoutStartInput = form.querySelector('#blackout_start_date');
            if (blackoutStartInput) {
                blackoutStartInput.value = promotion.blackout_start_date || '';
            }

            const blackoutEndInput = form.querySelector('#blackout_end_date');
            if (blackoutEndInput) {
                blackoutEndInput.value = promotion.blackout_end_date || '';
            }

            // Valid weekdays checkboxes
            const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            weekdays.forEach(day => {
                const checkbox = form.querySelector(`input[name="valid_${day}"]`);
                if (checkbox) {
                    checkbox.checked = promotion[`valid_${day}`] === true || promotion[`valid_${day}`] === 1;
                }
            });

            // Cập nhật tiêu đề và nút submit
            document.querySelector('.wrap h1').textContent = 'Cập Nhật Khuyến Mãi';
            document.querySelector('input[type="submit"]').value = 'Cập Nhật Khuyến Mãi';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('update-promotion-form');

        // Weekday selection buttons
        document.getElementById('select-all-days').addEventListener('click', function() {
            document.querySelectorAll('input[name^="valid_"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        document.getElementById('select-weekdays-only').addEventListener('click', function() {
            document.querySelectorAll('input[name^="valid_"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            // Monday to Friday
            document.querySelector('input[name="valid_monday"]').checked = true;
            document.querySelector('input[name="valid_tuesday"]').checked = true;
            document.querySelector('input[name="valid_wednesday"]').checked = true;
            document.querySelector('input[name="valid_thursday"]').checked = true;
            document.querySelector('input[name="valid_friday"]').checked = true;
        });

        document.getElementById('select-weekend-only').addEventListener('click', function() {
            document.querySelectorAll('input[name^="valid_"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            // Saturday and Sunday
            document.querySelector('input[name="valid_saturday"]').checked = true;
            document.querySelector('input[name="valid_sunday"]').checked = true;
        });

        // Blackout date validation
        document.getElementById('blackout_start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('blackout_end_date');
            if (startDate) {
                endDateInput.min = startDate;
            }
        });

        document.getElementById('blackout_end_date').addEventListener('change', function() {
            const endDate = this.value;
            const startDateInput = document.getElementById('blackout_start_date');
            if (endDate) {
                startDateInput.max = endDate;
            }
        });

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

            // Đồng bộ nội dung từ TinyMCE editors trước khi submit
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            const form = document.getElementById('update-promotion-form');
            const formData = new FormData(form);

            // Lấy dữ liệu đa ngôn ngữ từ form
            const names = {};
            const descriptions = {};

            // Lấy dữ liệu từ form data cho names
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('name[')) {
                    const lang = key.match(/\[(.*?)\]/)[1];
                    names[lang] = value;
                }
            }

            // Lấy dữ liệu từ TinyMCE editors cho descriptions
            availableLanguages.forEach(lang => {
                const editorId = editorIdMapping[lang];
                console.log(`Getting description for ${lang}, editor ID: ${editorId}`);

                if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                    descriptions[lang] = tinymce.get(editorId).getContent();
                    console.log(`Got content via TinyMCE for ${lang}:`, descriptions[lang]);
                } else {
                    // Fallback cho textarea
                    const textarea = form.querySelector(`textarea[name="description[${lang}]"]`);
                    if (textarea) {
                        descriptions[lang] = textarea.value;
                        console.log(`Got content via textarea for ${lang}:`, descriptions[lang]);
                    } else {
                        console.error(`No editor or textarea found for ${lang}, editor ID: ${editorId}`);
                    }
                }
            });

            const selectedRoomtypes = [];
            document.querySelectorAll('input[name="roomtypes[]"]:checked').forEach(checkbox => {
                selectedRoomtypes.push(checkbox.value);
            });

            // Validation: Yêu cầu chọn ít nhất 1 room type
            if (selectedRoomtypes.length === 0) {
                alert('Vui lòng chọn ít nhất một loại phòng để áp dụng khuyến mãi.');
                return;
            }
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
                id: formData.get('id'),
                // Blackout dates
                blackout_start_date: formData.get('blackout_start_date') || null,
                blackout_end_date: formData.get('blackout_end_date') || null,
                // Valid weekdays
                valid_monday: formData.get('valid_monday') ? 1 : 0,
                valid_tuesday: formData.get('valid_tuesday') ? 1 : 0,
                valid_wednesday: formData.get('valid_wednesday') ? 1 : 0,
                valid_thursday: formData.get('valid_thursday') ? 1 : 0,
                valid_friday: formData.get('valid_friday') ? 1 : 0,
                valid_saturday: formData.get('valid_saturday') ? 1 : 0,
                valid_sunday: formData.get('valid_sunday') ? 1 : 0
            };

            // Debug: Log dữ liệu trước khi gửi
            console.log('Promotion Data being sent:', {
                action: promotionData.action,
                names: promotionData.name,
                descriptions: promotionData.description,
                id: promotionData.id
            });

            // Gửi dữ liệu bằng jQuery.ajax hoặc Fetch API
            // Ví dụ sử dụng jQuery.ajax()
            jQuery.ajax({
                url: hme_admin.ajax_url,
                method: 'POST',
                data: promotionData,
                success: function(response) {
                    //hideLoading();
                    console.log('Promotion updated - Full response:', response);

                    if (response.success) {
                        alert('Khuyến mãi đã được cập nhật thành công!');
                        // Có thể chuyển hướng người dùng hoặc làm mới trang
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + (response.data || 'Không thể cập nhật khuyến mãi'));
                        console.error('API Error:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    //hideLoading();
                    console.error('AJAX Error updating promotion:', {
                        status: jqXHR.status,
                        statusText: jqXHR.statusText,
                        responseText: jqXHR.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });

                    let errorMessage = 'Lỗi: Không thể cập nhật khuyến mãi.';
                    if (jqXHR.responseText) {
                        try {
                            const errorResponse = JSON.parse(jqXHR.responseText);
                            errorMessage = 'Lỗi: ' + (errorResponse.data || errorResponse.message || errorMessage);
                        } catch (e) {
                            errorMessage += ' (' + jqXHR.status + ' ' + jqXHR.statusText + ')';
                        }
                    }
                    alert(errorMessage);
                }
            });
        }
    });
</script>