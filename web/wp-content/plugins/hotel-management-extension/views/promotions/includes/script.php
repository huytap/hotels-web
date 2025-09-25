<script>
    jQuery(document).ready(function($) {
        // Define the HME.Promotion object if it's not already defined
        // HME.Promotion = HME.Promotion || {};

        // Function to handle the generation logic
        HME.Promotion.generateCode = function(callback) {
            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_generate_promotion_code',
                    nonce: hme_admin.nonce
                },
                success: function(response) {
                    // Check for the 'success' property in the response
                    if (response.success) {
                        callback(response.data.code);
                    } else {
                        // Handle API errors
                        HME.UI.showError(response.data || 'Không thể tạo mã khuyến mãi.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    HME.UI.showError('Lỗi AJAX: ' + textStatus);
                }
            });
        };

        $('#generate-code-btn').click(function() {
            const promotionCodeInput = $('#promotion_code'); // Use jQuery for consistency

            HME.Promotion.generateCode(function(code) {
                promotionCodeInput.val(code);
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const promotionTypeSelect = document.getElementById('type');
        const bookingDaysRow = document.getElementById('booking-days-row'); // Lấy hàng cần ẩn/hiện
        const bookingDaysInput = document.getElementById('booking_days_in_advance');
        const bookingDaysLabel = document.getElementById('booking-days-label');
        const bookingDaysDescription = bookingDaysInput.nextElementSibling;

        function toggleRequiredAndLabel() {
            const selectedType = promotionTypeSelect.value;

            if (selectedType === 'early_bird') {
                // Hiển thị hàng và đặt yêu cầu
                bookingDaysRow.style.display = 'table-row';
                bookingDaysInput.setAttribute('required', 'required');
                bookingDaysLabel.textContent = 'Số ngày đặt trước';
                bookingDaysDescription.textContent = 'Số ngày tối thiểu khách phải đặt trước so với ngày nhận phòng.';
            } else if (selectedType === 'last_minutes') {
                // Hiển thị hàng và đặt yêu cầu
                bookingDaysRow.style.display = 'table-row';
                bookingDaysInput.setAttribute('required', 'required');
                bookingDaysLabel.textContent = 'Số ngày tối đa trước ngày nhận phòng';
                bookingDaysDescription.textContent = 'Số ngày tối đa trước ngày nhận phòng để được hưởng khuyến mãi.';
            } else {
                // Ẩn hàng và xóa yêu cầu
                bookingDaysRow.style.display = 'none';
                bookingDaysInput.removeAttribute('required');
            }
        }

        // Gọi hàm khi trang tải để xử lý trạng thái ban đầu
        toggleRequiredAndLabel();

        // Thêm sự kiện lắng nghe khi giá trị của select thay đổi
        promotionTypeSelect.addEventListener('change', toggleRequiredAndLabel);
    });
</script>