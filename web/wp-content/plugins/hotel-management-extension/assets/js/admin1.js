jQuery(document).ready(function ($) {

    // Global object for managing state and functions
    const HME_Bookings = {
        currentPage: 1,
        perPage: 20,
        filters: {
            status: '',
            search: '',
            date_from: '',
            date_to: ''
        },
        blogId: $('.hme-table-wrapper').data('blog-id'),

        // Caching selectors
        $tableBody: $('.hme-table tbody'),
        $tableWrapper: $('.hme-table-wrapper'),
        $itemsCount: $('.hme-items-count'),
        $pagination: $('.hme-pagination'),
        $bulkActions: $('.hme-bulk-actions'),
        $search: $('.hme-search-input'),
        $statusFilter: $('select[name="status"]'),
        $dateFilter: $('select[name="date_filter"]'),
        $modal: $('#hme-booking-modal'),
        $form: $('#hme-booking-modal .hme-form'),
        $modalTitle: $('#hme-booking-modal .hme-modal-title'),
        $submitBtn: $('#hme-booking-modal button[type="submit"]'),

        // Language strings for easy management
        lang: {
            loadingData: 'Đang tải dữ liệu...',
            loadingError: 'Lỗi khi tải dữ liệu. Vui lòng thử lại.',
            noBookingsFound: 'Không tìm thấy đặt phòng nào.',
            nights: 'đêm',
            edit: 'Sửa',
            delete: 'Xóa',
            areYouSureDelete: 'Bạn có chắc chắn muốn xóa đặt phòng này?',
            deleteSuccess: 'Đặt phòng đã được xóa thành công.',
            deleteError: 'Lỗi khi xóa đặt phòng. Vui lòng thử lại.',
            selectActionWarning: 'Vui lòng chọn hành động và ít nhất một mục.',
            exportDeveloping: 'Tính năng xuất dữ liệu đang được phát triển.',
            processedSuccess: 'Đã xử lý thành công',
            items: 'mục.',
            bulkActionError: 'Có lỗi xảy ra với một số mục:',
            bulkActionFailed: 'Lỗi khi thực hiện hành động hàng loạt.',
            addNewBooking: 'Thêm đặt phòng mới',
            updateBooking: 'Cập nhật đặt phòng',
            createBookingBtn: 'Tạo đặt phòng',
            updateBookingBtn: 'Cập nhật',
            createSuccess: 'Đã tạo đặt phòng mới thành công.',
            updateSuccess: 'Đã cập nhật đặt phòng thành công.',
            saveError: 'Lỗi khi lưu đặt phòng. Vui lòng thử lại.',
            totalAmountNA: 'N/A'
        },

        // Helper to show/hide loading spinner
        toggleLoading: function (show) {
            $('.hme-spinner').toggle(show);
        },

        // Show toast notifications
        showToast: function (message, type = 'success') {
            const $alertContainer = $('#hme-alert-container');
            const alertClass = `hme-alert-${type}`;
            const alertHtml = `<div class="hme-alert ${alertClass}">${message}</div>`;
            $alertContainer.html(alertHtml).fadeIn();
            setTimeout(() => {
                $alertContainer.fadeOut().empty();
            }, 5000);
        },

        // Fetch and render bookings
        fetchBookings: function () {
            this.toggleLoading(true);
            this.$tableBody.html(`<tr><td colspan="10" class="hme-text-center hme-text-muted">${this.lang.loadingData}</td></tr>`);

            const data = {
                action: 'hme_ajax_get_bookings',
                nonce: hme_admin_vars.nonce,
                page: this.currentPage,
                per_page: this.perPage,
                status: this.filters.status,
                search: this.filters.search,
                date_from: this.filters.date_from,
                date_to: this.filters.date_to,
                blog_id: this.blogId,
            };

            $.post(hme_admin_vars.ajax_url, data)
                .done(response => {
                    this.toggleLoading(false);
                    if (response.success) {
                        this.renderBookings(response.data.data);
                        this.renderPagination(response.data);
                        this.$itemsCount.text(`(${response.data.total})`);
                        this.updateBulkActions();
                    } else {
                        this.$tableBody.html(`<tr><td colspan="10" class="hme-text-center hme-text-danger">${response.data}</td></tr>`);
                        this.showToast(response.data, 'error');
                        this.$itemsCount.text(`(0)`);
                    }
                })
                .fail(() => {
                    this.toggleLoading(false);
                    this.$tableBody.html(`<tr><td colspan="10" class="hme-text-center hme-text-danger">${this.lang.loadingError}</td></tr>`);
                    this.showToast(this.lang.loadingError, 'error');
                });
        },

        renderBookings: function (bookings) {
            this.$tableBody.empty();
            if (bookings.length === 0) {
                this.$tableBody.html(`<tr><td colspan="10" class="hme-text-center hme-text-muted">${this.lang.noBookingsFound}</td></tr>`);
                return;
            }

            const statusLabels = {
                'pending': 'Chờ xác nhận',
                'confirmed': 'Đã xác nhận',
                'cancelled': 'Đã hủy',
                'completed': 'Hoàn thành',
                'no_show': 'Không đến',
            };

            bookings.forEach(booking => {
                const checkInDate = new Date(booking.check_in);
                const checkOutDate = new Date(booking.check_out);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

                const row = `
                    <tr data-id="${booking.id}">
                        <td>
                            <div class="hme-checkbox">
                                <input type="checkbox" name="booking_ids[]" value="${booking.id}">
                                <label></label>
                            </div>
                        </td>
                        <td>#${booking.id}</td>
                        <td>
                            <strong>${booking.customer_name}</strong>
                            <div class="hme-sub-text">${booking.customer_email}</div>
                            <div class="hme-sub-text">${booking.customer_phone}</div>
                        </td>
                        <td>${booking.room_type.name || 'N/A'}</td>
                        <td>${this.formatDate(booking.check_in)}</td>
                        <td>${this.formatDate(booking.check_out)}</td>
                        <td>${nights} ${this.lang.nights}</td>
                        <td>${this.formatCurrency(booking.total_amount)}</td>
                        <td><span class="hme-status hme-status-${booking.status}">${statusLabels[booking.status] || booking.status}</span></td>
                        <td>
                            <div class="hme-actions">
                                <button class="hme-action-btn hme-edit-btn" data-id="${booking.id}" title="${this.lang.edit}">✏️</button>
                                <button class="hme-action-btn hme-delete-btn" data-id="${booking.id}" title="${this.lang.delete}">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `;
                this.$tableBody.append(row);
            });
        },

        renderPagination: function (data) {
            this.$pagination.empty();
            if (data.last_page > 1) {
                let html = '';
                const startPage = Math.max(1, data.current_page - 2);
                const endPage = Math.min(data.last_page, data.current_page + 2);

                if (data.current_page > 1) {
                    html += `<a href="#" class="hme-pagination-link" data-page="${data.current_page - 1}">«</a>`;
                }

                if (startPage > 1) {
                    html += `<a href="#" class="hme-pagination-link" data-page="1">1</a><span>...</span>`;
                }

                for (let i = startPage; i <= endPage; i++) {
                    const activeClass = (i === data.current_page) ? 'hme-pagination-link-active' : '';
                    html += `<a href="#" class="hme-pagination-link ${activeClass}" data-page="${i}">${i}</a>`;
                }

                if (endPage < data.last_page) {
                    html += `<span>...</span><a href="#" class="hme-pagination-link" data-page="${data.last_page}">${data.last_page}</a>`;
                }

                if (data.current_page < data.last_page) {
                    html += `<a href="#" class="hme-pagination-link" data-page="${data.current_page + 1}">»</a>`;
                }

                this.$pagination.html(html);
            }
        },

        // Calculate nights and update summary
        updateBookingSummary: function () {
            const checkIn = $('#booking-checkin').val();
            const checkOut = $('#booking-checkout').val();
            const nightsElement = $('#booking-nights');

            if (checkIn && checkOut) {
                const date1 = new Date(checkIn);
                const date2 = new Date(checkOut);
                const diffTime = Math.abs(date2 - date1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                nightsElement.text(diffDays);

                // Trigger total calculation
                this.calculateBookingTotal();
            } else {
                nightsElement.text('0');
            }
        },

        // Calculate booking total via AJAX
        calculateBookingTotal: function () {
            const formData = this.$form.serializeArray();
            let data = {};
            $(formData).each(function (index, obj) {
                data[obj.name] = obj.value;
            });

            data.action = 'hme_ajax_calculate_booking_total';
            data.nonce = hme_admin_vars.nonce;

            $.post(hme_admin_vars.ajax_url, data)
                .done(response => {
                    if (response.success) {
                        $('#booking-total').text(this.formatCurrency(response.data.total_amount));
                    } else {
                        $('#booking-total').text(this.lang.totalAmountNA);
                        this.showToast(response.data, 'error');
                    }
                });
        },

        // Get booking details and populate modal for editing
        editBooking: function (id) {
            this.toggleLoading(true);
            const data = {
                action: 'hme_ajax_get_booking_details',
                nonce: hme_admin_vars.nonce,
                booking_id: id,
                blog_id: this.blogId,
            };

            $.post(hme_admin_vars.ajax_url, data)
                .done(response => {
                    this.toggleLoading(false);
                    if (response.success) {
                        const booking = response.data;
                        this.$modalTitle.text(this.lang.updateBooking);
                        this.$submitBtn.text(this.lang.updateBookingBtn);
                        this.$form.data('action', 'update_booking');

                        $('#booking-id').val(booking.id);
                        $('#booking-customer-name').val(booking.customer_name);
                        $('#booking-customer-email').val(booking.customer_email);
                        $('#booking-customer-phone').val(booking.customer_phone);
                        $('#booking-room-id').val(booking.room_type_id);
                        $('#booking-checkin').val(booking.check_in.split(' ')[0]);
                        $('#booking-checkout').val(booking.check_out.split(' ')[0]);
                        $('#booking-adults').val(booking.guests);
                        $('#booking-children').val(booking.children || 0);
                        $('#booking-notes').val(booking.notes);

                        this.updateBookingSummary();
                        this.showModal(this.$modal);
                    } else {
                        this.showToast(response.data, 'error');
                    }
                })
                .fail(() => {
                    this.toggleLoading(false);
                    this.showToast(this.lang.loadingError, 'error');
                });
        },

        // Modal functions
        showModal: function ($modal) {
            $modal.addClass('hme-modal-open');
        },
        closeModal: function ($modal) {
            $modal.removeClass('hme-modal-open');
        },

        // Helper functions
        formatCurrency: function (amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        },
        formatDate: function (dateString) {
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return new Date(dateString).toLocaleDateString('vi-VN', options);
        },

        // Update bulk actions visibility
        updateBulkActions: function () {
            const checked = $('.hme-table tbody input[type="checkbox"]:checked').length;
            $('.hme-selected-count').text(checked);
            this.$bulkActions.toggleClass('hme-bulk-actions-active', checked > 0);
        },

        // Initialize all event listeners
        init: function () {
            // Initial fetch
            this.fetchBookings();

            // Handle filters
            this.$search.on('keypress', (e) => {
                if (e.which === 13) {
                    this.filters.search = this.$search.val();
                    this.currentPage = 1;
                    this.fetchBookings();
                }
            });

            this.$statusFilter.on('change', () => {
                this.filters.status = this.$statusFilter.val();
                this.currentPage = 1;
                this.fetchBookings();
            });

            this.$dateFilter.on('change', () => {
                const value = this.$dateFilter.val();
                const today = new Date();
                let dateFrom = '';
                let dateTo = '';

                switch (value) {
                    case 'today':
                        dateFrom = today.toISOString().split('T')[0];
                        dateTo = today.toISOString().split('T')[0];
                        break;
                    case 'tomorrow':
                        const tomorrow = new Date(today);
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        dateFrom = tomorrow.toISOString().split('T')[0];
                        dateTo = tomorrow.toISOString().split('T')[0];
                        break;
                    case 'this_week':
                        const day = today.getDay();
                        const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                        const firstDayOfWeek = new Date(today.setDate(diff));
                        const lastDayOfWeek = new Date(today.setDate(diff + 6));
                        dateFrom = firstDayOfWeek.toISOString().split('T')[0];
                        dateTo = lastDayOfWeek.toISOString().split('T')[0];
                        break;
                    case 'this_month':
                        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        dateFrom = firstDayOfMonth.toISOString().split('T')[0];
                        dateTo = lastDayOfMonth.toISOString().split('T')[0];
                        break;
                }

                this.filters.date_from = dateFrom;
                this.filters.date_to = dateTo;
                this.currentPage = 1;
                this.fetchBookings();
            });

            // Pagination click handler
            this.$pagination.on('click', '.hme-pagination-link', function (e) {
                e.preventDefault();
                HME_Bookings.currentPage = $(this).data('page');
                HME_Bookings.fetchBookings();
            });

            // Handle bulk actions
            $('.hme-bulk-apply').on('click', () => {
                const action = $('.hme-bulk-select').val();
                const bookingIds = $('.hme-table tbody input[type="checkbox"]:checked').map(function () {
                    return $(this).val();
                }).get();

                if (!action || bookingIds.length === 0) {
                    this.showToast(this.lang.selectActionWarning, 'warning');
                    return;
                }

                this.toggleLoading(true);

                if (action === 'export') {
                    this.showToast(this.lang.exportDeveloping, 'info');
                    this.toggleLoading(false);
                    return;
                }

                const data = {
                    action: 'hme_ajax_bulk_booking_actions',
                    nonce: hme_admin_vars.nonce,
                    bulk_action: action,
                    booking_ids: bookingIds
                };

                $.post(hme_admin_vars.ajax_url, data)
                    .done(response => {
                        this.toggleLoading(false);
                        if (response.success) {
                            this.showToast(`${this.lang.processedSuccess} ${response.data.processed} ${this.lang.items}`, 'success');
                            if (response.data.errors.length > 0) {
                                this.showToast(`${this.lang.bulkActionError} ` + response.data.errors.join(', '), 'error');
                            }
                            this.fetchBookings();
                        } else {
                            this.showToast(response.data, 'error');
                        }
                    })
                    .fail(() => {
                        this.toggleLoading(false);
                        this.showToast(this.lang.bulkActionFailed, 'error');
                    });
            });

            // Handle add new booking button
            $('[data-modal="#hme-booking-modal"]').on('click', (e) => {
                e.preventDefault();
                this.$modalTitle.text(this.lang.addNewBooking);
                this.$submitBtn.text(this.lang.createBookingBtn);
                this.$form[0].reset();
                this.$form.data('action', 'create_booking');
                this.showModal(this.$modal);
                $('#booking-nights').text('0');
                $('#booking-total').text('0 VNĐ');
            });

            // Handle table action buttons (edit/delete)
            this.$tableBody.on('click', '.hme-edit-btn', function (e) {
                e.preventDefault();
                const bookingId = $(this).data('id');
                HME_Bookings.editBooking(bookingId);
            });

            this.$tableBody.on('click', '.hme-delete-btn', function (e) {
                e.preventDefault();
                const bookingId = $(this).data('id');
                if (confirm(HME_Bookings.lang.areYouSureDelete)) {
                    HME_Bookings.toggleLoading(true);
                    const data = {
                        action: 'hme_ajax_delete_booking',
                        nonce: hme_admin_vars.nonce,
                        booking_id: bookingId,
                        blog_id: HME_Bookings.blogId
                    };
                    $.post(hme_admin_vars.ajax_url, data)
                        .done(response => {
                            HME_Bookings.toggleLoading(false);
                            if (response.success) {
                                HME_Bookings.showToast(HME_Bookings.lang.deleteSuccess, 'success');
                                HME_Bookings.fetchBookings();
                            } else {
                                HME_Bookings.showToast(response.data, 'error');
                            }
                        })
                        .fail(() => {
                            HME_Bookings.toggleLoading(false);
                            HME_Bookings.showToast(HME_Bookings.lang.deleteError, 'error');
                        });
                }
            });

            // Handle modal close buttons and backdrop
            $('.hme-modal-close').on('click', function () {
                HME_Bookings.closeModal($(this).closest('.hme-modal'));
            });

            $('.hme-modal').on('click', function (e) {
                if (e.target === this) {
                    HME_Bookings.closeModal($(this));
                }
            });

            // Handle form submission (create/update)
            this.$form.on('submit', function (e) {
                e.preventDefault();

                const formAction = $(this).data('action');
                let ajaxAction = 'hme_ajax_create_booking';

                if (formAction === 'update_booking') {
                    ajaxAction = 'hme_ajax_update_booking';
                }

                HME_Bookings.toggleLoading(true);

                const data = {
                    action: ajaxAction,
                    nonce: hme_admin_vars.nonce,
                    ...Object.fromEntries(new FormData(e.target)),
                    blog_id: HME_Bookings.blogId
                };

                $.post(hme_admin_vars.ajax_url, data)
                    .done(response => {
                        HME_Bookings.toggleLoading(false);
                        if (response.success) {
                            HME_Bookings.showToast(
                                formAction === 'create_booking' ? HME_Bookings.lang.createSuccess : HME_Bookings.lang.updateSuccess,
                                'success'
                            );
                            HME_Bookings.closeModal(HME_Bookings.$modal);
                            HME_Bookings.fetchBookings();
                        } else {
                            HME_Bookings.showToast(response.data, 'error');
                        }
                    })
                    .fail(() => {
                        HME_Bookings.toggleLoading(false);
                        HME_Bookings.showToast(HME_Bookings.lang.saveError, 'error');
                    });
            });

            // Handle date and guest change to update summary
            $('#booking-checkin, #booking-checkout, #booking-adults, #booking-children, #booking-room-id, #booking-promotion-code').on('change input', function () {
                HME_Bookings.updateBookingSummary();
            });

            // Handle bulk select all
            $('.hme-select-all').on('change', function () {
                $('.hme-table tbody input[type="checkbox"]').prop('checked', this.checked);
                HME_Bookings.updateBulkActions();
            });

            // Handle individual checkbox change
            this.$tableBody.on('change', 'input[type="checkbox"]', function () {
                HME_Bookings.updateBulkActions();
            });

            // Handle multisite selector
            $('#hotel-site-selector').on('change', function () {
                const blogId = $(this).val();
                window.location.href = `?page=hme-bookings&blog_id=${blogId}`;
            });
        }
    };

    HME_Bookings.init();
});