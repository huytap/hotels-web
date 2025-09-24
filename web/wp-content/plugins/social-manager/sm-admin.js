jQuery(function ($) {
    function resetForm() {
        $('#sm-post-id').val('');
        $('#sm-title').val('');
        $('#sm-content').val('');
        $('#sm-platform').val('facebook');
        $('#sm-url').val('');
        $('#sm-submit').text('Lưu bài');
    }

    $('#sm-reset').on('click', function () { resetForm(); });

    $('#sm-form').on('submit', function (e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        data.push({ name: 'nonce', value: SM_Ajax.nonce });
        $.post(SM_Ajax.ajax_url, data, function (res) {
            if (res.success) {
                alert(res.data.message || 'OK');
                location.reload();
            } else {
                alert(res.data && res.data.message ? res.data.message : 'Lỗi');
            }
        }, 'json');
    });

    // edit button
    $('#sm-list').on('click', '.sm-edit', function () {
        var tr = $(this).closest('tr');
        var post_id = tr.data('id');
        $.get(SM_Ajax.ajax_url, { action: 'sm_edit', post_id: post_id, nonce: SM_Ajax.nonce }, function (res) {
            if (res.success) {
                $('#sm-post-id').val(res.data.ID);
                $('#sm-title').val(res.data.title);
                $('#sm-content').val(res.data.content);
                $('#sm-platform').val(res.data.platform);
                $('#sm-url').val(res.data.url);
                $('#sm-submit').text('Cập nhật');
                $('html,body').animate({ scrollTop: 0 }, 300);
            } else {
                alert('Không lấy được dữ liệu');
            }
        }, 'json');
    });

    // delete button
    $('#sm-list').on('click', '.sm-delete', function () {
        if (!confirm('Bạn có chắc muốn xóa?')) return;
        var tr = $(this).closest('tr');
        var post_id = tr.data('id');
        $.post(SM_Ajax.ajax_url, { action: 'sm_delete', post_id: post_id, nonce: SM_Ajax.nonce }, function (res) {
            if (res.success) {
                alert(res.data.message || 'Đã xóa');
                location.reload();
            } else {
                alert(res.data && res.data.message ? res.data.message : 'Lỗi xóa');
            }
        }, 'json');
    });

});
