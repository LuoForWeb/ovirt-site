/*
 * @Author: ChengJiaFu
 * @Date: 2026-02-09 17:59:36
 * @Description: 搜索输入框组件
 * @version: 1.0
 */
(function($) {
    $.fn.initSearchInput = function(options) {
        const defaults = {
            placeholder: 'Search...',
            onSearch: function() {},
            onClear: function() {}
        };

        const settings = $.extend({}, defaults, options);

        return this.each(function() {
            const $container = $(this);

            // --- HTML Generation ---
            $container.addClass('toolbar-search-wrapper').empty(); // Add base class and clear content

            const $input = $('<input class="form-control form-control-sm search" type="text">');
            const $clearBtn = $('<span class="toolbar-search-wrapper__clear"><i class="viconfont vicon-zujianshanchu"></i></span>');
            const $searchBtn = $('<button class="btn btn-outline-primary toolbar-search-wrapper__btn"><i class="viconfont vicon-gaojisousuo1"></i></button>');

            $container.append($input).append($clearBtn).append($searchBtn);

            // --- Initialization ---
            $input.attr('placeholder', settings.placeholder);
            $clearBtn.css('display', 'none');

            // --- Event Handlers ---

            // Show/hide clear button based on input
            $input.on('input keyup', function() {
                if ($(this).val().length > 0) {
                    $clearBtn.css({'display': 'inline-flex', 'align-items': 'center'});
                } else {
                    $clearBtn.css('display', 'none');
                }
            });

            // Handle clear button click
            $clearBtn.on('click', function(e) {
                e.preventDefault();
                $input.val('').focus();
                $(this).css('display', 'none');
                if (typeof settings.onClear === 'function') {
                    settings.onClear.call($container);
                }
            });

            // Handle search action (button click or Enter key)
            const performSearch = () => {
                const value = $input.val().trim();
                if (typeof settings.onSearch === 'function') {
                    settings.onSearch.call($container, value);
                }
            };

            $searchBtn.on('click', function(e) {
                e.preventDefault();
                performSearch();
            });

            $input.on('keyup', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    performSearch();
                }
            });

            // --- Public Methods ---
            $container.data('searchInput', {
                getValue: function() {
                    return $input.val();
                },
                setValue: function(value) {
                    $input.val(value).trigger('input');
                },
                clear: function() {
                    $clearBtn.trigger('click');
                },
                search: function() {
                    performSearch();
                }
            });
        });
    }
})(jQuery);