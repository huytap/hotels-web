/**
 * Hotel Management Extension - Admin JavaScript
 * File: assets/js/admin.js
 */

(function ($) {
    'use strict';

    // Global HME object
    window.HME = window.HME || {};

    /**
     * Utility Functions
     */
    HME.Utils = {

        /**
         * Format currency for display
         */
        formatCurrency: function (amount) {
            if (isNaN(amount) || amount === null || amount === undefined) {
                return '0 VNĐ';
            }
            return new Intl.NumberFormat('vi-VN').format(parseFloat(amount)) + ' VNĐ';
        },

        /**
         * Format date for display
         */
        formatDate: function (dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('vi-VN');
        },

        /**
         * Format datetime for display
         */
        formatDateTime: function (dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString('vi-VN');
        },

        /**
         * Calculate nights between two dates
         */
        calculateNights: function (checkIn, checkOut) {
            if (!checkIn || !checkOut) return 0;
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            return Math.max(0, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
        },

        /**
         * Validate email format
         */
        isValidEmail: function (email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        /**
         * Sanitize text input
         */
        sanitizeText: function (text) {
            return text.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        },

        /**
         * Generate random string
         */
        generateRandomString: function (length, charset) {
            let result = '';
            const characters = charset || 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            return result;
        },

        /**
         * Debounce function calls
         */
        debounce: function (func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Deep clone object
         */
        deepClone: function (obj) {
            return JSON.parse(JSON.stringify(obj));
        },

        /**
         * Check if element is in viewport
         */
        isInViewport: function (element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };

    /**
     * AJAX Helper
     */
    HME.Ajax = {

        /**
         * Make AJAX request to WordPress
         */
        request: function (action, data, options) {
            options = options || {};

            const defaultData = {
                action: action,
                nonce: hme_admin.nonce
            };

            const requestData = $.extend({}, defaultData, data);

            const ajaxOptions = {
                url: hme_admin.ajax_url,
                type: options.method || 'POST',
                data: requestData,
                dataType: 'json',
                timeout: options.timeout || 30000,

                beforeSend: function () {
                    if (options.beforeSend) {
                        options.beforeSend();
                    }
                    if (options.loadingElement) {
                        HME.UI.showLoading(options.loadingElement);
                    }
                },

                success: function (response) {
                    if (options.loadingElement) {
                        HME.UI.hideLoading(options.loadingElement);
                    }

                    if (response.success) {
                        if (options.success) {
                            options.success(response.data, response);
                        }
                    } else {
                        if (options.error) {
                            options.error(response.data || 'Unknown error', response);
                        } else {
                            HME.UI.showError(response.data || 'Request failed');
                        }
                    }
                },

                error: function (xhr, status, error) {
                    if (options.loadingElement) {
                        HME.UI.hideLoading(options.loadingElement);
                    }

                    let errorMessage = 'Connection error';
                    if (status === 'timeout') {
                        errorMessage = 'Request timeout';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Access denied';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Not found';
                    } else if (xhr.status >= 500) {
                        errorMessage = 'Server error';
                    }

                    if (options.error) {
                        options.error(errorMessage, xhr);
                    } else {
                        HME.UI.showError(errorMessage);
                    }
                },

                complete: function (xhr, status) {
                    if (options.complete) {
                        options.complete(xhr, status);
                    }
                }
            };

            return $.ajax(ajaxOptions);
        },

        /**
         * Convenience methods for different HTTP methods
         */
        get: function (action, data, options) {
            options = options || {};
            options.method = 'GET';
            return this.request(action, data, options);
        },

        post: function (action, data, options) {
            options = options || {};
            options.method = 'POST';
            return this.request(action, data, options);
        }
    };

    /**
     * UI Helper Functions
     */
    HME.UI = {

        /**
         * Show loading state
         */
        showLoading: function (element) {
            if (typeof element === 'string') {
                element = $(element);
            }

            if (element && element.length) {
                element.addClass('hme-loading-state');
                element.append('<div class="hme-loading-overlay"><div class="spinner is-active"></div></div>');
            } else {
                $('#hme-loading').show();
            }
        },

        /**
         * Hide loading state
         */
        hideLoading: function (element) {
            if (typeof element === 'string') {
                element = $(element);
            }

            if (element && element.length) {
                element.removeClass('hme-loading-state');
                element.find('.hme-loading-overlay').remove();
            } else {
                $('#hme-loading').hide();
            }
        },

        /**
         * Show success notice
         */
        showSuccess: function (message, options) {
            this.showNotice(message, 'success', options);
        },

        /**
         * Show error notice
         */
        showError: function (message, options) {
            this.showNotice(message, 'error', options);
        },

        /**
         * Show warning notice
         */
        showWarning: function (message, options) {
            this.showNotice(message, 'warning', options);
        },

        /**
         * Show info notice
         */
        showInfo: function (message, options) {
            this.showNotice(message, 'info', options);
        },

        /**
         * Show notice
         */
        showNotice: function (message, type, options) {
            options = $.extend({
                duration: 5000,
                dismissible: true,
                container: '.wrap h1',
                position: 'after'
            }, options);

            type = type || 'info';
            const noticeClass = 'notice-' + type;

            const notice = $(`
                <div class="notice ${noticeClass} hme-admin-notice ${options.dismissible ? 'is-dismissible' : ''}">
                    <p>${HME.Utils.sanitizeText(message)}</p>
                    ${options.dismissible ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : ''}
                </div>
            `);

            // Remove existing notices of the same type
            $(`.hme-admin-notice.${noticeClass}`).remove();

            // Insert notice
            if (options.position === 'before') {
                $(options.container).before(notice);
            } else {
                $(options.container).after(notice);
            }

            // Auto dismiss
            if (options.duration > 0) {
                setTimeout(() => {
                    notice.fadeOut(300, () => notice.remove());
                }, options.duration);
            }

            // Manual dismiss
            if (options.dismissible) {
                notice.find('.notice-dismiss').on('click', function () {
                    notice.fadeOut(300, () => notice.remove());
                });
            }

            // Scroll to notice if not in viewport
            if (!HME.Utils.isInViewport(notice[0])) {
                $('html, body').animate({
                    scrollTop: notice.offset().top - 50
                }, 300);
            }

            return notice;
        },

        /**
         * Show confirmation dialog
         */
        confirm: function (message, options) {
            options = $.extend({
                title: 'Confirm',
                confirmText: 'OK',
                cancelText: 'Cancel',
                type: 'warning'
            }, options);

            return new Promise((resolve) => {
                if (window.confirm(message)) {
                    resolve(true);
                } else {
                    resolve(false);
                }
            });
        },

        /**
         * Show modal
         */
        showModal: function (modalId, options) {
            options = $.extend({
                backdrop: true,
                keyboard: true,
                focus: true
            }, options);

            const $modal = $(modalId);
            if (!$modal.length) return;

            // Show modal
            $modal.show();

            // Focus management
            if (options.focus) {
                const firstInput = $modal.find('input, select, textarea, button').first();
                if (firstInput.length) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            }

            // Keyboard handling
            if (options.keyboard) {
                $(document).on('keydown.hme-modal', function (e) {
                    if (e.keyCode === 27) { // Escape key
                        HME.UI.hideModal(modalId);
                    }
                });
            }

            // Backdrop click
            if (options.backdrop) {
                $modal.on('click.hme-modal', function (e) {
                    if (e.target === this) {
                        HME.UI.hideModal(modalId);
                    }
                });
            }
        },

        /**
         * Hide modal
         */
        hideModal: function (modalId) {
            const $modal = $(modalId);
            if (!$modal.length) return;

            $modal.hide();
            $(document).off('keydown.hme-modal');
            $modal.off('click.hme-modal');
        },

        /**
         * Create skeleton loading placeholder
         */
        createSkeleton: function (config) {
            config = $.extend({
                lines: 3,
                height: '1em',
                spacing: '0.5em'
            }, config);

            let html = '<div class="hme-skeleton-container">';
            for (let i = 0; i < config.lines; i++) {
                const width = i === config.lines - 1 ? '60%' : '100%';
                html += `<div class="hme-skeleton hme-skeleton-text" style="height: ${config.height}; margin-bottom: ${config.spacing}; width: ${width};"></div>`;
            }
            html += '</div>';

            return $(html);
        },

        /**
         * Update pagination
         */
        updatePagination: function (data, containerId) {
            const container = $(containerId);
            if (!container.length || !data.last_page || data.last_page <= 1) {
                container.html('');
                return;
            }

            let html = '';
            const totalPages = data.last_page;
            const current = data.current_page;

            // Previous page
            if (current > 1) {
                html += `<a class="page-numbers" data-page="${current - 1}" href="#" aria-label="Previous page">‹</a>`;
            }

            // Page numbers
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(totalPages, current + 2);

            if (startPage > 1) {
                html += `<a class="page-numbers" data-page="1" href="#">1</a>`;
                if (startPage > 2) {
                    html += `<span class="page-numbers dots">…</span>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === current) {
                    html += `<span class="page-numbers current" aria-current="page">${i}</span>`;
                } else {
                    html += `<a class="page-numbers" data-page="${i}" href="#">${i}</a>`;
                }
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="page-numbers dots">…</span>`;
                }
                html += `<a class="page-numbers" data-page="${totalPages}" href="#">${totalPages}</a>`;
            }

            // Next page
            if (current < totalPages) {
                html += `<a class="page-numbers" data-page="${current + 1}" href="#" aria-label="Next page">›</a>`;
            }

            container.html(html);
        }
    };

    /**
     * Form Validation Helper
     */
    HME.Validator = {

        /**
         * Validation rules
         */
        rules: {
            required: function (value) {
                return value !== null && value !== undefined && String(value).trim() !== '';
            },

            email: function (value) {
                return !value || HME.Utils.isValidEmail(value);
            },

            min: function (value, min) {
                return !value || parseFloat(value) >= parseFloat(min);
            },

            max: function (value, max) {
                return !value || parseFloat(value) <= parseFloat(max);
            },

            minLength: function (value, length) {
                return !value || String(value).length >= parseInt(length);
            },

            maxLength: function (value, length) {
                return !value || String(value).length <= parseInt(length);
            },

            date: function (value) {
                return !value || !isNaN(Date.parse(value));
            },

            dateAfter: function (value, afterDate) {
                if (!value || !afterDate) return true;
                return new Date(value) > new Date(afterDate);
            },

            dateBefore: function (value, beforeDate) {
                if (!value || !beforeDate) return true;
                return new Date(value) < new Date(beforeDate);
            },

            number: function (value) {
                return !value || !isNaN(parseFloat(value));
            },

            integer: function (value) {
                return !value || (Number.isInteger(parseFloat(value)) && isFinite(value));
            },

            url: function (value) {
                if (!value) return true;
                try {
                    new URL(value);
                    return true;
                } catch (e) {
                    return false;
                }
            },

            phone: function (value) {
                return !value || /^[\+]?[0-9\s\-\(\)]{10,}$/.test(value);
            },

            alphanumeric: function (value) {
                return !value || /^[a-zA-Z0-9]+$/.test(value);
            },

            custom: function (value, validatorFn) {
                return validatorFn(value);
            }
        },

        /**
         * Validate single field
         */
        validateField: function (field, rules) {
            const $field = $(field);
            const value = $field.val();
            const errors = [];

            for (const rule in rules) {
                if (rules.hasOwnProperty(rule)) {
                    const ruleValue = rules[rule];
                    const ruleFn = this.rules[rule];

                    if (ruleFn && !ruleFn(value, ruleValue)) {
                        errors.push(this.getErrorMessage(rule, ruleValue, $field.attr('name')));
                    }
                }
            }

            return {
                isValid: errors.length === 0,
                errors: errors
            };
        },

        /**
         * Validate form
         */
        validateForm: function (form, validationRules) {
            const $form = $(form);
            const errors = {};
            let isFormValid = true;

            // Clear existing errors
            $form.find('.field-error').remove();
            $form.find('.error').removeClass('error');

            for (const fieldName in validationRules) {
                if (validationRules.hasOwnProperty(fieldName)) {
                    const $field = $form.find(`[name="${fieldName}"]`);
                    if (!$field.length) continue;

                    const result = this.validateField($field, validationRules[fieldName]);

                    if (!result.isValid) {
                        errors[fieldName] = result.errors;
                        isFormValid = false;

                        // Show field errors
                        this.showFieldError($field, result.errors[0]);
                    }
                }
            }

            return {
                isValid: isFormValid,
                errors: errors
            };
        },

        /**
         * Show field error
         */
        /**
         * Show field error
         */
        showFieldError: function ($field, message) {
            $field.addClass('error');

            // Remove existing error message
            $field.next('.field-error').remove();

            // Add new error message
            const $error = $(`<p class="field-error">${message}</p>`);
            $field.after($error);
        },

        /**
         * Clear field error
         */
        clearFieldError: function ($field) {
            $field.removeClass('error');
            $field.next('.field-error').remove();
        },

        /**
         * Get error message for rule
         */
        getErrorMessage: function (rule, ruleValue, fieldName) {
            const messages = {
                required: `${fieldName} is required`,
                email: `${fieldName} must be a valid email address`,
                min: `${fieldName} must be at least ${ruleValue}`,
                max: `${fieldName} must not exceed ${ruleValue}`,
                minLength: `${fieldName} must be at least ${ruleValue} characters`,
                maxLength: `${fieldName} must not exceed ${ruleValue} characters`,
                date: `${fieldName} must be a valid date`,
                dateAfter: `${fieldName} must be after ${ruleValue}`,
                dateBefore: `${fieldName} must be before ${ruleValue}`,
                number: `${fieldName} must be a valid number`,
                integer: `${fieldName} must be a whole number`,
                url: `${fieldName} must be a valid URL`,
                phone: `${fieldName} must be a valid phone number`,
                alphanumeric: `${fieldName} must contain only letters and numbers`
            };

            return messages[rule] || `${fieldName} is invalid`;
        }
    };

    /**
     * Data Table Helper
     */
    HME.DataTable = {

        /**
         * Initialize sortable table
         */
        initSortable: function (tableSelector, options) {
            options = $.extend({
                sortField: 'id',
                sortOrder: 'desc',
                onSort: null
            }, options);

            const $table = $(tableSelector);
            let currentSortField = options.sortField;
            let currentSortOrder = options.sortOrder;

            $table.find('.sortable').on('click', function (e) {
                e.preventDefault();

                const field = $(this).data('sort');

                if (currentSortField === field) {
                    currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortField = field;
                    currentSortOrder = 'asc';
                }

                // Update UI
                $table.find('.sortable .sorting-indicator').removeClass('asc desc');
                $(this).find('.sorting-indicator').addClass(currentSortOrder);

                // Trigger callback
                if (options.onSort) {
                    options.onSort(currentSortField, currentSortOrder);
                }
            });

            return {
                getSortField: () => currentSortField,
                getSortOrder: () => currentSortOrder
            };
        },

        /**
         * Handle bulk actions
         */
        initBulkActions: function (tableSelector, options) {
            options = $.extend({
                selectAllSelector: '.select-all',
                itemCheckboxSelector: 'tbody input[type="checkbox"]',
                bulkActionSelector: '.bulk-action-selector',
                bulkApplySelector: '.bulk-apply',
                onAction: null
            }, options);

            const $table = $(tableSelector);

            // Select all functionality
            $table.on('change', options.selectAllSelector, function () {
                const isChecked = $(this).prop('checked');
                $table.find(options.itemCheckboxSelector).prop('checked', isChecked);
            });

            // Individual checkbox change
            $table.on('change', options.itemCheckboxSelector, function () {
                const totalItems = $table.find(options.itemCheckboxSelector).length;
                const checkedItems = $table.find(options.itemCheckboxSelector + ':checked').length;

                $table.find(options.selectAllSelector).prop('checked', totalItems === checkedItems);
            });

            // Bulk action apply
            $(options.bulkApplySelector).on('click', function () {
                const action = $(options.bulkActionSelector).val();
                if (!action || action === '-1') {
                    HME.UI.showWarning('Please select an action');
                    return;
                }

                const selectedIds = [];
                $table.find(options.itemCheckboxSelector + ':checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    HME.UI.showWarning('Please select at least one item');
                    return;
                }

                if (options.onAction) {
                    options.onAction(action, selectedIds);
                }
            });
        },

        /**
         * Update table with new data
         */
        updateTable: function (tableSelector, data, options) {
            options = $.extend({
                emptyMessage: 'No data found',
                rowRenderer: null
            }, options);

            const $tbody = $(tableSelector + ' tbody');

            if (!data || data.length === 0) {
                const colspan = $(tableSelector + ' thead th').length;
                $tbody.html(`
                    <tr>
                        <td colspan="${colspan}" class="hme-no-results">
                            <div class="hme-empty-state">
                                <p>${options.emptyMessage}</p>
                            </div>
                        </td>
                    </tr>
                `);
                return;
            }

            let html = '';
            if (options.rowRenderer) {
                data.forEach(function (item, index) {
                    html += options.rowRenderer(item, index);
                });
            }

            $tbody.html(html);
        }
    };

    /**
     * Calendar Helper
     */
    HME.Calendar = {

        /**
         * Render calendar grid
         */
        render: function (containerId, data, options) {
            options = $.extend({
                month: new Date().getMonth(),
                year: new Date().getFullYear(),
                onDateClick: null,
                cellRenderer: null
            }, options);

            const $container = $(containerId);
            const firstDay = new Date(options.year, options.month, 1);
            const lastDay = new Date(options.year, options.month + 1, 0);
            const startDate = new Date(firstDay);

            // Start from Sunday
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            let html = `
                <table class="hme-calendar-table">
                    <thead>
                        <tr>
                            <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let currentDate = new Date(startDate);

            while (currentDate <= lastDay || currentDate.getDay() !== 0) {
                if (currentDate.getDay() === 0) {
                    html += '<tr>';
                }

                const dateStr = currentDate.toISOString().split('T')[0];
                const isCurrentMonth = currentDate.getMonth() === options.month;
                const dayData = data[dateStr] || {};

                let cellClass = 'hme-calendar-cell';
                let cellContent = `<div class="date-number">${currentDate.getDate()}</div>`;

                if (!isCurrentMonth) {
                    cellClass += ' other-month';
                } else if (options.cellRenderer) {
                    const rendered = options.cellRenderer(dayData, dateStr);
                    if (rendered.class) cellClass += ' ' + rendered.class;
                    if (rendered.content) cellContent += rendered.content;
                }

                html += `
                    <td class="${cellClass}" data-date="${dateStr}" ${dayData.tooltip ? `title="${dayData.tooltip}"` : ''}>
                        ${cellContent}
                    </td>
                `;

                if (currentDate.getDay() === 6) {
                    html += '</tr>';
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }

            html += '</tbody></table>';
            $container.html(html);

            // Attach click handlers
            if (options.onDateClick) {
                $container.find('.hme-calendar-cell').on('click', function () {
                    const date = $(this).data('date');
                    if (date && !$(this).hasClass('other-month')) {
                        options.onDateClick(date, data[date] || {});
                    }
                });
            }
        },

        /**
         * Navigate to previous month
         */
        previousMonth: function (currentMonth, currentYear) {
            if (currentMonth === 0) {
                return { month: 11, year: currentYear - 1 };
            } else {
                return { month: currentMonth - 1, year: currentYear };
            }
        },

        /**
         * Navigate to next month
         */
        nextMonth: function (currentMonth, currentYear) {
            if (currentMonth === 11) {
                return { month: 0, year: currentYear + 1 };
            } else {
                return { month: currentMonth + 1, year: currentYear };
            }
        },

        /**
         * Get month name
         */
        getMonthName: function (monthIndex) {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            return months[monthIndex];
        }
    };

    /**
     * Storage Helper (using sessionStorage for temporary data)
     */
    HME.Storage = {

        /**
         * Save data to session storage
         */
        set: function (key, data) {
            try {
                sessionStorage.setItem('hme_' + key, JSON.stringify(data));
                return true;
            } catch (e) {
                console.warn('Failed to save to session storage:', e);
                return false;
            }
        },

        /**
         * Get data from session storage
         */
        get: function (key, defaultValue) {
            try {
                const data = sessionStorage.getItem('hme_' + key);
                return data ? JSON.parse(data) : defaultValue;
            } catch (e) {
                console.warn('Failed to read from session storage:', e);
                return defaultValue;
            }
        },

        /**
         * Remove data from session storage
         */
        remove: function (key) {
            try {
                sessionStorage.removeItem('hme_' + key);
                return true;
            } catch (e) {
                console.warn('Failed to remove from session storage:', e);
                return false;
            }
        },

        /**
         * Clear all HME data from session storage
         */
        clear: function () {
            try {
                const keysToRemove = [];
                for (let i = 0; i < sessionStorage.length; i++) {
                    const key = sessionStorage.key(i);
                    if (key && key.startsWith('hme_')) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => sessionStorage.removeItem(key));
                return true;
            } catch (e) {
                console.warn('Failed to clear session storage:', e);
                return false;
            }
        }
    };

    /**
     * Export Helper
     */
    HME.Export = {

        /**
         * Export data as CSV
         */
        exportCSV: function (action, filters, filename) {
            filters = filters || {};
            filename = filename || 'export.csv';

            const form = $('<form>', {
                method: 'POST',
                action: hme_admin.ajax_url,
                target: '_blank'
            });

            form.append($('<input>', { type: 'hidden', name: 'action', value: action }));
            form.append($('<input>', { type: 'hidden', name: 'nonce', value: hme_admin.nonce }));
            form.append($('<input>', { type: 'hidden', name: 'format', value: 'csv' }));
            form.append($('<input>', { type: 'hidden', name: 'filename', value: filename }));

            // Add filter parameters
            for (const key in filters) {
                if (filters.hasOwnProperty(key) && filters[key] !== '') {
                    form.append($('<input>', { type: 'hidden', name: key, value: filters[key] }));
                }
            }

            form.appendTo('body').submit().remove();
        },

        /**
         * Download data as JSON
         */
        downloadJSON: function (data, filename) {
            filename = filename || 'data.json';

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    };

    /**
     * Booking Specific Functions
     */
    HME.Booking = {

        /**
         * Calculate booking total
         */
        calculateTotal: function (bookingData, callback) {
            HME.Ajax.post('hme_calculate_booking_total', bookingData, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to calculate total: ' + message);
                }
            });
        },

        /**
         * Validate promotion code
         */
        validatePromotion: function (promotionCode, bookingData, callback) {
            const data = $.extend({}, bookingData, { promotion_code: promotionCode });

            HME.Ajax.post('hme_validate_promotion_for_booking', data, {
                success: callback,
                error: function (message) {
                    callback({ valid: false, message: message });
                }
            });
        },

        /**
         * Get available room types
         */
        getAvailableRooms: function (dates, callback) {
            HME.Ajax.get('hme_get_available_room_types', dates, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to load available rooms: ' + message);
                }
            });
        }
    };

    /**
     * Room Rate Specific Functions
     */
    HME.RoomRate = {

        /**
         * Update single rate
         */
        updateRate: function (rateData, callback) {
            HME.Ajax.post('hme_update_room_rate', rateData, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to update rate: ' + message);
                }
            });
        },

        /**
         * Bulk update rates
         */
        bulkUpdate: function (bulkData, callback) {
            HME.Ajax.post('hme_bulk_update_rates', bulkData, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to bulk update rates: ' + message);
                }
            });
        },

        /**
         * Copy rates between periods
         */
        copyRates: function (copyData, callback) {
            HME.Ajax.post('hme_copy_rates', copyData, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to copy rates: ' + message);
                }
            });
        }
    };

    /**
     * Promotion Specific Functions
     */
    HME.Promotion = {

        /**
         * Generate promotion code
         */
        generateCode: function (options, callback) {
            HME.Ajax.post('hme_generate_promotion_code', options, {
                success: callback,
                error: function (message) {
                    HME.UI.showError('Failed to generate code: ' + message);
                }
            });
        },

        /**
         * Validate promotion code format
         */
        validateCodeFormat: function (code) {
            return /^[A-Z0-9_-]{3,20}$/.test(code);
        },

        /**
         * Check code uniqueness
         */
        checkUniqueness: function (code, callback) {
            HME.Ajax.get('hme_check_promotion_code', { code: code }, {
                success: function (data) {
                    callback(!data.exists);
                },
                error: function () {
                    callback(false);
                }
            });
        },
        create: function (formData, successCallback, errorCallback) {
            HME.Ajax.post('hme_create_promotion', formData, {
                success: successCallback,
                error: errorCallback
            });
        }
    };

    /**
     * Initialize Global Event Listeners
     */
    HME.init = function () {

        // Global modal handlers
        $(document).on('click', '.hme-modal-close', function () {
            $(this).closest('.hme-modal').hide();
        });

        // Global form validation on submit
        $(document).on('submit', '[data-hme-validate]', function (e) {
            const $form = $(this);
            const rules = $form.data('hme-validate');

            if (rules) {
                const result = HME.Validator.validateForm($form, rules);
                if (!result.isValid) {
                    e.preventDefault();
                    HME.UI.showError('Please correct the errors in the form');
                }
            }
        });

        // Auto-resize textareas
        $(document).on('input', 'textarea[data-auto-resize]', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Copy to clipboard functionality
        $(document).on('click', '[data-copy]', function () {
            const text = $(this).data('copy') || $($(this).data('copy-target')).val() || $(this).text();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    HME.UI.showSuccess('Copied to clipboard');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                HME.UI.showSuccess('Copied to clipboard');
            }
        });

        // Confirmation dialogs
        $(document).on('click', '[data-confirm]', function (e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-save functionality
        let autoSaveTimer = null;
        $(document).on('input', '[data-auto-save]', function () {
            clearTimeout(autoSaveTimer);
            const $field = $(this);
            const key = $field.data('auto-save');

            autoSaveTimer = setTimeout(function () {
                HME.Storage.set(key, $field.val());
            }, 1000);
        });

        // Restore auto-saved data
        $('[data-auto-save]').each(function () {
            const $field = $(this);
            const key = $field.data('auto-save');
            const savedValue = HME.Storage.get(key);

            if (savedValue && !$field.val()) {
                $field.val(savedValue);
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function (e) {
            // Ctrl/Cmd + S for save
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
                e.preventDefault();
                const $saveBtn = $('.button-primary[type="submit"]:visible').first();
                if ($saveBtn.length) {
                    $saveBtn.click();
                }
            }

            // Escape to close modals
            if (e.keyCode === 27) {
                $('.hme-modal:visible').hide();
            }
        });

        // Responsive table handling
        function makeTablesResponsive() {
            $('.wp-list-table').each(function () {
                const $table = $(this);
                const $wrapper = $table.parent();

                if ($table.outerWidth() > $wrapper.width()) {
                    $wrapper.css('overflow-x', 'auto');
                }
            });
        }

        $(window).on('resize', HME.Utils.debounce(makeTablesResponsive, 250));
        makeTablesResponsive();

        // Initialize tooltips if available
        if ($.fn.tooltip) {
            $('[title]').tooltip();
        }

        // Accessibility improvements
        $('[data-hme-role="button"]').attr('role', 'button').attr('tabindex', '0');

        $(document).on('keypress', '[data-hme-role="button"]', function (e) {
            if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                $(this).click();
            }
        });

        console.log('HME Admin JavaScript initialized');
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        HME.init();
    });

    // Expose HME to global scope for debugging
    if (window.console && window.console.log) {
        console.log('Hotel Management Extension admin scripts loaded');
    }

})(jQuery);