jQuery(document).ready(function ($) {
    $('.btn-view-room').on('click', function () {
        const roomId = $(this).data('room-id');
        const modal = $('#roomModal');

        // Show modal
        modal.modal('show');

        // Hiển thị spinner
        modal.find('.modal-content').html(`
            <div class="modal-body text-center p-4">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Đang tải...</span>
                </div>
            </div>
        `);

        // AJAX load chi tiết phòng
        $.get(hotelRoomAjax.ajax_url, { action: 'load_room_detail', room_id: roomId, lang: hotelRoomAjax.current_lang }, function (response) {
            if (response.success) {
                modal.find('.modal-content').html(response.data.html);
            } else {
                modal.find('.modal-content').html('<div class="modal-body"><p>' + (response.data.message || 'Lỗi tải dữ liệu') + '</p></div>');
            }
        });
    });
});
