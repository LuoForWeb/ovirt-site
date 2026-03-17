var UIIdleTimeout = function () {
    return {

        //main function to initiate the module
        init: function () {
        	this.IDLETIMEOUTFLAG = true;		//超时检测标志
            // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
            var $countdown;

            $('body').append('<div class="modaltimeout fade" id="idle-timeout-dialog" data-backdrop="static"><div class="modal-dialog modal-small"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">' +
            		LANG.UI_TIMEOUT_TIPS_TITLE + '</h4></div><div class="modal-body"><p><i class="fa fa-warning"></i> ' +
            		LANG.UI_TIMEOUT_TIPS1 + ': <span id="idle-timeout-counter"></span> </p><p> ' +
            		LANG.UI_TIMEOUT_TIPS2 + '?</p></div><div class="modal-footer"><button id="idle-timeout-dialog-logout" type="button" class="btn btn-default">' +
            		LANG.UI_TIMEOUT_TIPS_NO + '</button><button id="idle-timeout-dialog-keepalive" type="button" class="btn btn-primary" data-dismiss="modal">' +
            		LANG.UI_TIMEOUT_TIPS_YES + '</button></div></div></div></div>');

            // start the idle timer plugin
            $.idleTimeout('#idle-timeout-dialog', '.modal-content button:last', {
                idleAfter: CONF.IDLETIMEOUT, // 5 seconds
//                timeout: 160, //30 seconds to timeout
                pollingInterval: 180, // 5 seconds
                keepAliveURL: '/keepalive.php',
                serverResponseEquals: 'OK',
                onTimeout: function(){
                	if(UIIdleTimeout.IDLETIMEOUTFLAG){
                        loginOut();
                	}
                },
                onIdle: function(){
                    $('#idle-timeout-dialog').modal('show');
                    $countdown = $('#idle-timeout-counter');
                    $('#idle-timeout-dialog-keepalive').on('click', function () {
                        $('#idle-timeout-dialog').modal('hide');
                    });

                    $('#idle-timeout-dialog-logout').on('click', function () {
                        $('#idle-timeout-dialog').modal('hide');
                        $.idleTimeout.options.onTimeout.call(this);
                    });
                },
                onCountdown: function(counter){
                    $countdown.html(counter); // update the counter
                }
            });
        },

        //停止超时检测
        stop: function(){
        	this.IDLETIMEOUTFLAG = false;
        },

        //重新开始超时检测
        start: function(){
        	$('#idle-timeout-dialog').remove();
        	this.init();
        }

    };

}();