jQuery(function ($) {
    var frame;
    $('#gu_add_images').on('click', function (e) {
        e.preventDefault();
        // If the media frame already exists, reopen it.
        if (frame) { frame.open(); return; }

        // Create a new media frame
        frame = wp.media({
            title: 'Chọn hoặc upload ảnh cho gallery',
            button: { text: 'Chọn ảnh' },
            library: { type: 'image' },
            multiple: true
        });

        frame.on('select', function () {
            var attachments = frame.state().get('selection').toArray();
            var preview = $('#gu_gallery_preview');
            // Append thumbs
            attachments.forEach(function (att) {
                var a = att.toJSON();
                // Create thumbnail html
                var thumb = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                // avoid duplicates
                if (preview.find('.gu-thumb[data-id="' + a.id + '"]').length) return;
                preview.append('<div class="gu-thumb" data-id="' + a.id + '"><img src="' + thumb + '" alt=""><button class="gu-remove">×</button></div>');
            });
            updateHiddenInputs();
        });

        frame.open();
    });

    // Remove image
    $('#gu_gallery_preview').on('click', '.gu-remove', function (e) {
        e.preventDefault();
        $(this).closest('.gu-thumb').remove();
        updateHiddenInputs();
    });

    // Build hidden inputs (gallery_images[])
    function updateHiddenInputs() {
        var ids = [];
        $('#gu_gallery_preview .gu-thumb').each(function () {
            ids.push($(this).data('id'));
        });
        // remove existing hidden inputs then add new ones
        var container = $('#gallery_images').closest('td');
        $('#gallery_images').remove();
        ids.forEach(function (id) {
            container.append('<input type="hidden" id="gallery_images" name="gallery_images[]" value="' + id + '">');
        });
    }

    // on page load, initialize hidden inputs (if editing)
    updateHiddenInputs();

});
