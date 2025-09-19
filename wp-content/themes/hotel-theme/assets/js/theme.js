jQuery(document).ready(function($) {
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });
    
    // Sticky header
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.site-header').addClass('scrolled');
        } else {
            $('.site-header').removeClass('scrolled');
        }
    });
    
    // Mobile menu toggle
    $('.mobile-menu-toggle').on('click', function() {
        $('.main-navigation').toggleClass('active');
        $(this).toggleClass('active');
    });
    
    // Booking bar optimization
    function optimizeBookingBar() {
        var $bookingBar = $('.booking-bar');
        var $heroSection = $('.hero-section');
        
        if ($bookingBar.length && $heroSection.length) {
            var heroHeight = $heroSection.outerHeight();
            var scrollTop = $(window).scrollTop();
            
            if (scrollTop > heroHeight - 100) {
                $bookingBar.addClass('fixed-top');
            } else {
                $bookingBar.removeClass('fixed-top');
            }
        }
    }
    
    $(window).scroll(optimizeBookingBar);
    
    // Hotel image lazy loading
    function lazyLoadImages() {
        $('.hotel-image[data-src]').each(function() {
            var $img = $(this);
            var src = $img.data('src');
            
            if (isElementInViewport($img[0])) {
                $img.attr('src', src).removeAttr('data-src');
                $img.addClass('loaded');
            }
        });
    }
    
    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    $(window).on('scroll resize', lazyLoadImages);
    lazyLoadImages(); // Initial load
    
    // Room card hover effects
    $('.hms-room-card').hover(
        function() {
            $(this).find('.hms-room-image').addClass('zoom');
        },
        function() {
            $(this).find('.hms-room-image').removeClass('zoom');
        }
    );
    
    // Amenity items animation
    function animateAmenities() {
        $('.hms-amenity-item').each(function(index) {
            var $item = $(this);
            if (isElementInViewport($item[0]) && !$item.hasClass('animated')) {
                setTimeout(function() {
                    $item.addClass('animated fadeInUp');
                }, index * 100);
            }
        });
    }
    
    $(window).on('scroll resize', animateAmenities);
    animateAmenities();
    
    // Form validation helpers
    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePhone(phone) {
        var re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
    
    // Enhanced booking form functionality
    $('.hms-booking-form input[type="email"]').blur(function() {
        var email = $(this).val();
        if (email && !validateEmail(email)) {
            $(this).addClass('error');
            showFieldError($(this), 'Please enter a valid email address');
        } else {
            $(this).removeClass('error');
            hideFieldError($(this));
        }
    });
    
    $('.hms-booking-form input[type="tel"]').blur(function() {
        var phone = $(this).val();
        if (phone && !validatePhone(phone)) {
            $(this).addClass('error');
            showFieldError($(this), 'Please enter a valid phone number');
        } else {
            $(this).removeClass('error');
            hideFieldError($(this));
        }
    });
    
    function showFieldError($field, message) {
        hideFieldError($field);
        $field.after('<div class="field-error">' + message + '</div>');
    }
    
    function hideFieldError($field) {
        $field.siblings('.field-error').remove();
    }
    
    // Newsletter signup (if implemented)
    $('.newsletter-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var email = $form.find('input[type="email"]').val();
        
        if (!validateEmail(email)) {
            alert('Please enter a valid email address');
            return;
        }
        
        // Here you would normally send the email to your newsletter service
        // For now, just show a success message
        $form.html('<div class="success-message">Thank you for subscribing!</div>');
    });
    
    // Initialize any additional features
    initializeTooltips();
    initializeDatePickers();
    
    function initializeTooltips() {
        $('[data-tooltip]').each(function() {
            var $element = $(this);
            var tooltip = $element.data('tooltip');
            
            $element.hover(
                function() {
                    $('body').append('<div class="tooltip">' + tooltip + '</div>');
                    var $tooltip = $('.tooltip');
                    var offset = $element.offset();
                    $tooltip.css({
                        top: offset.top - $tooltip.outerHeight() - 10,
                        left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                    });
                },
                function() {
                    $('.tooltip').remove();
                }
            );
        });
    }
    
    function initializeDatePickers() {
        // Basic date validation for booking forms
        $('input[type="date"]').on('change', function() {
            var selectedDate = new Date($(this).val());
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                $(this).addClass('error');
                showFieldError($(this), 'Please select a future date');
            } else {
                $(this).removeClass('error');
                hideFieldError($(this));
            }
        });
    }
    
    // Performance optimization: Debounce scroll events
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    var debouncedScroll = debounce(function() {
        optimizeBookingBar();
        lazyLoadImages();
        animateAmenities();
    }, 100);
    
    $(window).off('scroll').on('scroll', debouncedScroll);
});