<?php

/**
 * Booking Add Form View
 * File: views/booking-add.php
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Lấy room types từ API (cached)
$room_types = HME_Room_Rate_Manager::get_room_types();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-plus-alt"></span>
        Add New Booking
    </h1>
    <a href="<?php echo admin_url('admin.php?page=hotel-bookings'); ?>" class="page-title-action">
        <span class="dashicons dashicons-arrow-left-alt"></span> Back to Bookings
    </a>

    <!-- Progress Steps -->
    <div class="hme-progress-steps">
        <div class="hme-step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Customer Info</span>
        </div>
        <div class="hme-step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Room & Dates</span>
        </div>
        <div class="hme-step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Review & Confirm</span>
        </div>
    </div>

    <form id="add-booking-form" class="hme-booking-form">
        <?php wp_nonce_field('hme_admin_nonce', 'nonce'); ?>

        <!-- Step 1: Customer Information -->
        <div class="hme-form-step" id="step-1">
            <div class="hme-form-section">
                <h2><span class="dashicons dashicons-admin-users"></span> Customer Information</h2>

                <div class="hme-form-row">
                    <div class="hme-form-group">
                        <label for="customer_name" class="required">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" class="regular-text" required>
                        <p class="description">Customer's full name as it appears on ID</p>
                    </div>

                    <div class="hme-form-group">
                        <label for="customer_email" class="required">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" class="regular-text" required>
                        <p class="description">Valid email for booking confirmation</p>
                    </div>
                </div>

                <div class="hme-form-row">
                    <div class="hme-form-group">
                        <label for="customer_phone" class="required">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="regular-text" required>
                        <p class="description">Contact number for booking updates</p>
                    </div>

                    <div class="hme-form-group">
                        <label for="customer_nationality">Nationality</label>
                        <select id="customer_nationality" name="customer_nationality">
                            <option value="">Select nationality</option>
                            <option value="VN">Vietnam</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="AU">Australia</option>
                            <option value="JP">Japan</option>
                            <option value="KR">South Korea</option>
                            <option value="CN">China</option>
                            <option value="TH">Thailand</option>
                            <option value="SG">Singapore</option>
                            <option value="MY">Malaysia</option>
                        </select>
                    </div>
                </div>

                <div class="hme-form-actions">
                    <button type="button" class="button button-primary hme-next-step" data-next="2">
                        Next: Room & Dates <span class="dashicons dashicons-arrow-right-alt"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Room Selection and Dates -->
        <div class="hme-form-step" id="step-2" style="display: none;">
            <div class="hme-form-section">
                <h2><span class="dashicons dashicons-calendar-alt"></span> Room Selection & Dates</h2>

                <div class="hme-form-row">
                    <div class="hme-form-group">
                        <label for="check_in" class="required">Check-in Date *</label>
                        <input type="date" id="check_in" name="check_in" class="regular-text" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="hme-form-group">
                        <label for="check_out" class="required">Check-out Date *</label>
                        <input type="date" id="check_out" name="check_out" class="regular-text" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="hme-form-group">
                        <label for="guests" class="required">Number of Guests *</label>
                        <input type="number" id="guests" name="guests" min="1" max="10" class="small-text" required>
                        <p class="description">Total number of guests</p>
                    </div>
                </div>

                <div id="nights-display" class="hme-nights-info" style="display: none;">
                    <p><strong>Length of Stay:</strong> <span id="nights-count">0</span> night(s)</p>
                </div>

                <div class="hme-form-group">
                    <label for="room_type_id" class="required">Room Type *</label>
                    <div id="room-type-loading" style="display: none;">
                        <div class="spinner is-active"></div>
                        <span>Loading available rooms...</span>
                    </div>
                    <select id="room_type_id" name="room_type_id" class="regular-text" required style="display: none;">
                        <option value="">Select dates first to see available rooms</option>
                    </select>
                    <div id="room-type-details" class="hme-room-details" style="display: none;"></div>
                </div>

                <div class="hme-form-section">
                    <h3><span class="dashicons dashicons-tag"></span> Promotions & Discounts</h3>

                    <div class="hme-form-group">
                        <label for="promotion_code">Promotion Code</label>
                        <div class="hme-input-group">
                            <input type="text" id="promotion_code" name="promotion_code" class="regular-text" placeholder="Enter promotion code">
                            <button type="button" id="validate-promotion" class="button">Validate</button>
                        </div>
                        <div id="promotion-result" class="hme-promotion-result" style="display: none;"></div>
                    </div>
                </div>

                <div class="hme-form-section">
                    <h3><span class="dashicons dashicons-edit"></span> Additional Information</h3>

                    <div class="hme-form-group">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests" name="special_requests" rows="3" class="large-text" placeholder="Any special requests or preferences..."></textarea>
                    </div>

                    <div class="hme-form-group">
                        <label for="notes">Internal Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="large-text" placeholder="Internal notes (not visible to customer)..."></textarea>
                    </div>
                </div>

                <div class="hme-form-actions">
                    <button type="button" class="button hme-prev-step" data-prev="1">
                        <span class="dashicons dashicons-arrow-left-alt"></span> Back
                    </button>
                    <button type="button" class="button button-primary hme-next-step" data-next="3">
                        Review Booking <span class="dashicons dashicons-arrow-right-alt"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Review and Confirm -->
        <div class="hme-form-step" id="step-3" style="display: none;">
            <div class="hme-form-section">
                <h2><span class="dashicons dashicons-yes"></span> Review & Confirm Booking</h2>

                <div class="hme-booking-summary">
                    <div class="hme-summary-section">
                        <h3>Customer Details</h3>
                        <div class="hme-summary-content" id="customer-summary">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>

                    <div class="hme-summary-section">
                        <h3>Booking Details</h3>
                        <div class="hme-summary-content" id="booking-summary">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>

                    <div class="hme-summary-section">
                        <h3>Pricing Breakdown</h3>
                        <div class="hme-pricing-breakdown" id="pricing-breakdown">
                            <div class="spinner is-active" style="float: none; margin: 20px auto;"></div>
                            <p>Calculating total...</p>
                        </div>
                    </div>
                </div>

                <div class="hme-form-actions">
                    <button type="button" class="button hme-prev-step" data-prev="2">
                        <span class="dashicons dashicons-arrow-left-alt"></span> Back to Edit
                    </button>
                    <button type="submit" id="submit-booking" class="button button-primary button-large" disabled>
                        <span class="dashicons dashicons-yes"></span> Create Booking
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        let currentStep = 1;
        let bookingTotal = 0;
        let promotionDiscount = 0;
        let availableRoomTypes = [];

        // Initialize
        initializeDatePickers();

        // Step navigation
        $('.hme-next-step').on('click', function() {
            const nextStep = parseInt($(this).data('next'));
            if (validateCurrentStep()) {
                goToStep(nextStep);
            }
        });

        $('.hme-prev-step').on('click', function() {
            const prevStep = parseInt($(this).data('prev'));
            goToStep(prevStep);
        });

        // Date change handlers
        $('#check_in, #check_out').on('change', function() {
            updateNightsDisplay();
            loadAvailableRooms();
            clearPricingCalculation();
        });

        $('#guests').on('change', function() {
            loadAvailableRooms();
            clearPricingCalculation();
        });

        // Room type selection
        $('#room_type_id').on('change', function() {
            showRoomTypeDetails();
            clearPromotionResult();
            clearPricingCalculation();
        });

        // Promotion validation
        $('#validate-promotion').on('click', function() {
            validatePromotion();
        });

        $('#promotion_code').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                validatePromotion();
            }
        });

        // Form submission
        $('#add-booking-form').on('submit', function(e) {
            e.preventDefault();
            if (validateAllSteps()) {
                submitBooking();
            }
        });

        // Functions
        function initializeDatePickers() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            $('#check_in').attr('min', today.toISOString().split('T')[0]);
            $('#check_out').attr('min', tomorrow.toISOString().split('T')[0]);
        }

        function goToStep(step) {
            // Hide current step
            $('.hme-form-step').hide();
            $('.hme-step').removeClass('active completed');

            // Show new step
            $(`#step-${step}`).show();
            $(`.hme-step[data-step="${step}"]`).addClass('active');

            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                $(`.hme-step[data-step="${i}"]`).addClass('completed');
            }

            currentStep = step;

            // Special handling for step 3
            if (step === 3) {
                generateBookingSummary();
                calculateTotal();
            }
        }

        function validateCurrentStep() {
            switch (currentStep) {
                case 1:
                    return validateCustomerInfo();
                case 2:
                    return validateBookingInfo();
                case 3:
                    return true; // Review step, no validation needed
                default:
                    return true;
            }
        }

        function validateCustomerInfo() {
            const fields = ['customer_name', 'customer_email', 'customer_phone'];
            let isValid = true;

            fields.forEach(field => {
                const $field = $(`#${field}`);
                if (!$field.val().trim()) {
                    showFieldError($field, 'This field is required');
                    isValid = false;
                } else {
                    clearFieldError($field);
                }
            });

            // Email validation
            const email = $('#customer_email').val();
            if (email && !isValidEmail(email)) {
                showFieldError($('#customer_email'), 'Please enter a valid email address');
                isValid = false;
            }

            return isValid;
        }

        function validateBookingInfo() {
            const fields = ['check_in', 'check_out', 'guests', 'room_type_id'];
            let isValid = true;

            fields.forEach(field => {
                const $field = $(`#${field}`);
                if (!$field.val()) {
                    showFieldError($field, 'This field is required');
                    isValid = false;
                } else {
                    clearFieldError($field);
                }
            });

            // Date validation
            const checkIn = new Date($('#check_in').val());
            const checkOut = new Date($('#check_out').val());
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (checkIn < today) {
                showFieldError($('#check_in'), 'Check-in date cannot be in the past');
                isValid = false;
            }

            if (checkOut <= checkIn) {
                showFieldError($('#check_out'), 'Check-out date must be after check-in date');
                isValid = false;
            }

            return isValid;
        }

        function validateAllSteps() {
            return validateCustomerInfo() && validateBookingInfo();
        }

        function updateNightsDisplay() {
            const checkIn = $('#check_in').val();
            const checkOut = $('#check_out').val();

            if (checkIn && checkOut) {
                const nights = calculateNights(checkIn, checkOut);
                if (nights > 0) {
                    $('#nights-count').text(nights);
                    $('#nights-display').show();
                } else {
                    $('#nights-display').hide();
                }
            } else {
                $('#nights-display').hide();
            }
        }

        function loadAvailableRooms() {
            const checkIn = $('#check_in').val();
            const checkOut = $('#check_out').val();
            const guests = $('#guests').val();

            if (!checkIn || !checkOut || !guests) {
                return;
            }

            $('#room-type-loading').show();
            $('#room_type_id').hide();
            $('#room-type-details').hide();

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_get_available_room_types',
                    nonce: hme_admin.nonce,
                    check_in: checkIn,
                    check_out: checkOut,
                    guests: guests
                },
                success: function(response) {
                    $('#room-type-loading').hide();
                    $('#room_type_id').show();

                    if (response.success) {
                        populateRoomTypes(response.data);
                    } else {
                        showError('Failed to load available rooms: ' + response.data);
                        $('#room_type_id').html('<option value="">No rooms available</option>');
                    }
                },
                error: function() {
                    $('#room-type-loading').hide();
                    $('#room_type_id').show();
                    showError('Error loading available rooms');
                }
            });
        }

        function populateRoomTypes(roomTypes) {
            availableRoomTypes = roomTypes;
            let html = '<option value="">Select a room type</option>';

            roomTypes.forEach(function(room) {
                html += `<option value="${room.id}" data-price="${room.rate}" data-max-guests="${room.max_guests}">
                ${room.name} - ${formatCurrency(room.rate)}/night (Max ${room.max_guests} guests)
            </option>`;
            });

            $('#room_type_id').html(html);
        }

        function showRoomTypeDetails() {
            const selectedId = $('#room_type_id').val();
            if (!selectedId) {
                $('#room-type-details').hide();
                return;
            }

            const room = availableRoomTypes.find(r => r.id == selectedId);
            if (room) {
                const html = `
                <div class="hme-room-info">
                    <h4>${room.name}</h4>
                    <p><strong>Rate:</strong> ${formatCurrency(room.rate)} per night</p>
                    <p><strong>Max Guests:</strong> ${room.max_guests}</p>
                    <p><strong>Amenities:</strong> ${room.amenities || 'Standard amenities'}</p>
                    ${room.description ? `<p><strong>Description:</strong> ${room.description}</p>` : ''}
                </div>
            `;
                $('#room-type-details').html(html).show();
            }
        }

        function validatePromotion() {
            const promotionCode = $('#promotion_code').val().trim();
            if (!promotionCode) {
                showError('Please enter a promotion code');
                return;
            }

            const bookingData = {
                room_type_id: $('#room_type_id').val(),
                check_in: $('#check_in').val(),
                check_out: $('#check_out').val(),
                guests: $('#guests').val(),
                customer_email: $('#customer_email').val()
            };

            if (!bookingData.room_type_id || !bookingData.check_in || !bookingData.check_out) {
                showError('Please select room type and dates first');
                return;
            }

            $('#validate-promotion').prop('disabled', true).text('Validating...');

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_validate_promotion_for_booking',
                    nonce: hme_admin.nonce,
                    promotion_code: promotionCode,
                    ...bookingData
                },
                success: function(response) {
                    $('#validate-promotion').prop('disabled', false).text('Validate');

                    if (response.success) {
                        showPromotionResult(response.data, true);
                    } else {
                        showPromotionResult({
                            message: response.data
                        }, false);
                    }
                },
                error: function() {
                    $('#validate-promotion').prop('disabled', false).text('Validate');
                    showPromotionResult({
                        message: 'Error validating promotion code'
                    }, false);
                }
            });
        }

        function showPromotionResult(data, isValid) {
            const $result = $('#promotion-result');

            if (isValid) {
                promotionDiscount = data.discount_amount || 0;
                $result.html(`
                <div class="hme-promotion-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <strong>Promotion Applied!</strong>
                    <p>${data.title}</p>
                    <p>Discount: ${formatCurrency(promotionDiscount)}</p>
                </div>
            `).show();
            } else {
                promotionDiscount = 0;
                $result.html(`
                <div class="hme-promotion-error">
                    <span class="dashicons dashicons-dismiss"></span>
                    <strong>Invalid Promotion</strong>
                    <p>${data.message}</p>
                </div>
            `).show();
            }

            // Recalculate if we're on step 3
            if (currentStep === 3) {
                calculateTotal();
            }
        }

        function clearPromotionResult() {
            $('#promotion-result').hide();
            promotionDiscount = 0;
        }

        function generateBookingSummary() {
            // Customer summary
            const customerHtml = `
            <table class="hme-summary-table">
                <tr><td><strong>Name:</strong></td><td>${$('#customer_name').val()}</td></tr>
                <tr><td><strong>Email:</strong></td><td>${$('#customer_email').val()}</td></tr>
                <tr><td><strong>Phone:</strong></td><td>${$('#customer_phone').val()}</td></tr>
                ${$('#customer_nationality').val() ? `<tr><td><strong>Nationality:</strong></td><td>${$('#customer_nationality').val()}</td></tr>` : ''}
            </table>
        `;
            $('#customer-summary').html(customerHtml);

            // Booking summary
            const roomText = $('#room_type_id option:selected').text().split(' - ')[0];
            const nights = calculateNights($('#check_in').val(), $('#check_out').val());

            const bookingHtml = `
            <table class="hme-summary-table">
                <tr><td><strong>Room Type:</strong></td><td>${roomText}</td></tr>
                <tr><td><strong>Check-in:</strong></td><td>${formatDate($('#check_in').val())}</td></tr>
                <tr><td><strong>Check-out:</strong></td><td>${formatDate($('#check_out').val())}</td></tr>
                <tr><td><strong>Nights:</strong></td><td>${nights}</td></tr>
                <tr><td><strong>Guests:</strong></td><td>${$('#guests').val()}</td></tr>
                ${$('#special_requests').val() ? `<tr><td><strong>Special Requests:</strong></td><td>${$('#special_requests').val()}</td></tr>` : ''}
            </table>
        `;
            $('#booking-summary').html(bookingHtml);
        }

        function calculateTotal() {
            const bookingData = {
                room_type_id: $('#room_type_id').val(),
                check_in: $('#check_in').val(),
                check_out: $('#check_out').val(),
                guests: $('#guests').val(),
                promotion_code: $('#promotion_code').val()
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hme_calculate_booking_total',
                    nonce: hme_admin.nonce,
                    ...bookingData
                },
                success: function(response) {
                    if (response.success) {
                        displayPricingBreakdown(response.data);
                        $('#submit-booking').prop('disabled', false);
                    } else {
                        showPricingError(response.data);
                    }
                },
                error: function() {
                    showPricingError('Error calculating total');
                }
            });
        }

        function displayPricingBreakdown(pricing) {
            bookingTotal = pricing.total_amount || 0;

            let html = `
            <table class="hme-pricing-table">
                <tr>
                    <td>Room Rate (${pricing.nights} nights × ${formatCurrency(pricing.rate_per_night)})</td>
                    <td class="amount">${formatCurrency(pricing.subtotal)}</td>
                </tr>
        `;

            if (pricing.taxes && pricing.taxes > 0) {
                html += `
                <tr>
                    <td>Taxes & Fees</td>
                    <td class="amount">${formatCurrency(pricing.taxes)}</td>
                </tr>
            `;
            }

            if (pricing.discount_amount && pricing.discount_amount > 0) {
                html += `
                <tr class="discount">
                    <td>Discount (${pricing.promotion_code})</td>
                    <td class="amount">-${formatCurrency(pricing.discount_amount)}</td>
                </tr>
            `;
            }

            html += `
                <tr class="total">
                    <td><strong>Total Amount</strong></td>
                    <td class="amount"><strong>${formatCurrency(bookingTotal)}</strong></td>
                </tr>
            </table>
        `;

            $('#pricing-breakdown').html(html);
        }

        function showPricingError(message) {
            $('#pricing-breakdown').html(`
            <div class="hme-pricing-error">
                <span class="dashicons dashicons-warning"></span>
                <p>Error calculating total: ${message}</p>
            </div>
        `);
            $('#submit-booking').prop('disabled', true);
        }

        function clearPricingCalculation() {
            $('#pricing-breakdown').html(`
            <div class="spinner is-active" style="float: none; margin: 20px auto;"></div>
            <p>Calculating total...</p>
        `);
            $('#submit-booking').prop('disabled', true);
        }

        function submitBooking() {
            $('#submit-booking').prop('disabled', true).html(
                '<div class="spinner is-active" style="float: left; margin-right: 5px;"></div> Creating Booking...'
            );

            const formData = {
                action: 'hme_create_booking',
                nonce: hme_admin.nonce,
                customer_name: $('#customer_name').val(),
                customer_email: $('#customer_email').val(),
                customer_phone: $('#customer_phone').val(),
                customer_nationality: $('#customer_nationality').val(),
                room_type_id: $('#room_type_id').val(),
                check_in: $('#check_in').val(),
                check_out: $('#check_out').val(),
                guests: $('#guests').val(),
                promotion_code: $('#promotion_code').val(),
                special_requests: $('#special_requests').val(),
                notes: $('#notes').val()
            };

            $.ajax({
                url: hme_admin.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showSuccess('Booking created successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo admin_url('admin.php?page=hotel-bookings'); ?>';
                        }, 2000);
                    } else {
                        $('#submit-booking').prop('disabled', false).html(
                            '<span class="dashicons dashicons-yes"></span> Create Booking'
                        );
                        showError('Failed to create booking: ' + response.data);
                    }
                },
                error: function() {
                    $('#submit-booking').prop('disabled', false).html(
                        '<span class="dashicons dashicons-yes"></span> Create Booking'
                    );
                    showError('Error creating booking. Please try again.');
                }
            });
        }

        // Utility functions
        function calculateNights(checkIn, checkOut) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('vi-VN');
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showFieldError($field, message) {
            $field.addClass('error');
            let $error = $field.next('.field-error');
            if ($error.length === 0) {
                $error = $('<p class="field-error"></p>');
                $field.after($error);
            }
            $error.text(message);
        }

        function clearFieldError($field) {
            $field.removeClass('error');
            $field.next('.field-error').remove();
        }

        function showSuccess(message) {
            showNotice(message, 'success');
        }

        function showError(message) {
            showNotice(message, 'error');
        }

        function showNotice(message, type) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

            $('.wrap h1').after(notice);

            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);

            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(() => notice.remove());
            });
        }
    });
</script>