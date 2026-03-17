;(function($, window, undefined) {    
    var $allDropdowns = $();
    $.fn.dropdownHover = function(options) {        
        $allDropdowns = $allDropdowns.add(this.parent());
       
        return this.each(function() {
            var $this = $(this).parent(),
                defaults = {
                    delay: 500,
                    instantlyCloseOthers: true,
                },
                data = {
                    delay: $(this).data('delay'),
                    instantlyCloseOthers: $(this).data('close-others'),
                },
                settings = $.extend(true, {}, defaults, options, data),
                timeout;

            $this.hover(function() {
                if(settings.instantlyCloseOthers === true){
                    $allDropdowns.removeClass('show').find(".dropdown-menu").removeClass('show');
                }

                window.clearTimeout(timeout);
                $(this).addClass('show').find(".dropdown-menu").addClass('show');
            }, function() {
                timeout = window.setTimeout(function() {
                    $this.removeClass('show').find(".dropdown-menu").removeClass('show');
                }, settings.delay);
            });
        });
    };

    $('[data-bs-hover="dropdown"]').dropdownHover();
})(jQuery, this);
