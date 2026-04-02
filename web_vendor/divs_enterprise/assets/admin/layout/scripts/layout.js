/**
Core script to handle the entire theme and core functions
**/
var Layout = function () {

    var layoutImgPath = 'admin/layout/img/';

    var layoutCssPath = 'admin/layout/css/';

    var resBreakpointMd = Metronic.getResponsiveBreakpoint('md');

    //* BEGIN:CORE HANDLERS *//
    // this function handles responsive layout on screen size resize or mobile device rotate.

    // Set proper height for sidebar and content. The content and sidebar height must be synced always.
    var handleSidebarAndContentHeight = function () {
        var content = $('.page-content');
        var sidebar = $('.page-sidebar');
        var body = $('body');
        var height;

        if (body.hasClass("page-footer-fixed") === true && body.hasClass("page-sidebar-fixed") === false) {
            var available_height = Metronic.getViewPort().height - $('.page-footer').outerHeight() - $('.page-header').outerHeight();
            if (content.height() < available_height) {
                content.attr('style', 'min-height:' + available_height + 'px');
            }
        } else {
            if (body.hasClass('page-sidebar-fixed')) {
                height = _calculateFixedSidebarViewportHeight();
                if (body.hasClass('page-footer-fixed') === false) {
                    height = height - $('.page-footer').outerHeight();
                }
            } else {
                var headerHeight = $('.page-header').outerHeight();
                var footerHeight = $('.page-footer').outerHeight();

                if (Metronic.getViewPort().width < resBreakpointMd) {
                    height = Metronic.getViewPort().height - headerHeight - footerHeight;
                } else {
                    height = sidebar.height() + 20;
                }

                if ((height + headerHeight + footerHeight) <= Metronic.getViewPort().height) {
                    height = Metronic.getViewPort().height - headerHeight - footerHeight;
                }
            }
            content.attr('style', 'min-height:' + height + 'px');
        }
    };

    // Handle sidebar menu links
    var handleSidebarMenuActiveLink = function(mode, el) {
        var url = location.hash.toLowerCase();    

        var menu = $('.page-sidebar-menu');

        if (mode === 'click' || mode === 'set') {
            el = $(el);
        } else if (mode === 'match') {
            menu.find("li > a").each(function() {
                var path = $(this).attr("href").toLowerCase();       
                // url match condition         
                if (path.length > 1 && url.substr(1, path.length - 1) == path.substr(1)) {
                    el = $(this);
                    return; 
                }
            });
        }

        if (!el || el.size() == 0) {
            return;
        }

        if (el.attr('href').toLowerCase() === 'javascript:;' || el.attr('href').toLowerCase() === '#') {
            return;
        }        

        var slideSpeed = parseInt(menu.data("slide-speed"));
        var keepExpand = menu.data("keep-expanded");

        // disable active states
        menu.find('li.active').removeClass('active');
        menu.find('li > a > .selected').remove();

        if (menu.hasClass('page-sidebar-menu-hover-submenu') === false) {
            menu.find('li.open').each(function(){
                if ($(this).children('.sub-menu').size() === 0) {
                    $(this).removeClass('open');
                    $(this).find('> a > .arrow.open').removeClass('open');
                }                             
            }); 
        } else {
             menu.find('li.open').removeClass('open');
        }

        el.parents('li').each(function () {
            $(this).addClass('active');
            $(this).find('> a > span.arrow').addClass('open');

            if ($(this).parent('ul.page-sidebar-menu').size() === 1) {
                $(this).find('> a').append('<span class="selected"></span>');
            }
            
            if ($(this).children('ul.sub-menu').size() === 1) {
                $(this).addClass('open');
            }
        });

        if (mode === 'click') {
            if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
                $('.page-header .responsive-toggler').click();
            }
        }
    };
    
    // Handle page header menu links
    var handlePageHeaderMenuActiveLinks = function(){
    	// jQuery('.hor-menu .dropdown-menu').on('click', 'li > a', function (e) {
    	//     debugger;
    	// 	//If the index page, window reload
        //     if('survey' == this.name){
        //     	window.location.reload();
        //     	return;
        //     }
        //     //如果是禁用选项,不处理
        //     if($(this).hasClass("disableda")){
        //     	return true;
        //     }
    	// 	var current = $(this).closest('.mega-menu-dropdown');
    	// 	$(current).removeClass('open');
    	// 	CTLSIDEBAR(this.name);
    	// 	if($(current).hasClass('active')){
    	// 		//如果已经选中
    	// 		return;
    	// 	}else{
        //         debugger;
    	// 		//如果当前选中的是其他
    	// 		$(current).siblings(".active").removeClass('active');
    	// 		$(current).addClass('active');
    	// 		//控制左边树
    	// 		var treeLi = $('.page-sidebar-menu').children();
    	// 		$(treeLi).addClass('displaynone');
    	// 		$(treeLi[0]).removeClass('displaynone');
    	// 		for(var i=1; i<treeLi.length; i++){
    	// 			// 忽略第一个收起展开项
    	// 			if(current.get(0).dataset.name == treeLi[i].dataset.name){
    	// 				$(treeLi[i]).removeClass('displaynone');
    	// 			}
    	// 		}
    	// 	}
    	// });



        $('.hor-menu .dropdown-submenu').on('mouseenter', function() {
            $(this).children('.dropdown-menu').show();
        });

        $('.hor-menu .dropdown-submenu').on('mouseleave', function() {
            $(this).children('.dropdown-menu').hide();
        });
        $('.hor-menu .nav.navbar-nav li a').on('click', function(event) {
            // 如果被点击的菜单项有子菜单，那么不做任何处理
            if ($(this).next('ul').length > 0) {
                return;
            }

            // 移除所有菜单项的 "active" 类
            $('.nav.navbar-nav li').removeClass('active');

            // 给被点击的菜单项的最高级父菜单项添加 "active" 类
            $(this).parents('.menu-dropdown').last().addClass('active');
        });
    	
    }

    // Handle sidebar menu
    var handleSidebarMenu = function () {
        // handle sidebar link click
        jQuery('.page-sidebar').on('click', 'li > a', function (e) {
            var hasSubMenu = $(this).next().hasClass('sub-menu');

            if (Metronic.getViewPort().width >= resBreakpointMd && $(this).parents('.page-sidebar-menu-hover-submenu').size() === 1) { // exit of hover sidebar menu
                return;
            }

            if (hasSubMenu === false) {
                if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
                    $('.page-header .responsive-toggler').click();
                }
                return;
            }

            if ($(this).next().hasClass('sub-menu always-open')) {
                return;
            }

            var parent = $(this).parent().parent();
            var the = $(this);
            var menu = $('.page-sidebar-menu');
            var sub = jQuery(this).next();

            var autoScroll = menu.data("auto-scroll");
            var slideSpeed = parseInt(menu.data("slide-speed"));
            var keepExpand = menu.data("keep-expanded");

            if (keepExpand !== true) {
                parent.children('li.open').children('a').children('.arrow').removeClass('open');
                parent.children('li.open').children('.sub-menu:not(.always-open)').slideUp(slideSpeed);
                parent.children('li.open').removeClass('open');
            }

            var slideOffeset = -200;

            if (sub.is(":visible")) {
                jQuery('.arrow', jQuery(this)).removeClass("open");
                jQuery(this).parent().removeClass("open");
                sub.slideUp(slideSpeed, function () {
                    if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            menu.slimScroll({
                                'scrollTo': (the.position()).top
                            });
                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    handleSidebarAndContentHeight();
                });
            } else if (hasSubMenu) {
                jQuery('.arrow', jQuery(this)).addClass("open");
                jQuery(this).parent().addClass("open");
                sub.slideDown(slideSpeed, function () {
                    if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            menu.slimScroll({
                                'scrollTo': (the.position()).top
                            });
                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    handleSidebarAndContentHeight();
                });
            }

            e.preventDefault();
        });
        
        // handle ajax links within sidebar menu
        jQuery('.page-sidebar').on('click', ' li > a.ajaxify', function (e) {
            e.preventDefault();
            Metronic.scrollTop();

            var url = $(this).attr("href");
            var urlMark = "?" + $(this).attr("name");
            var menuContainer = jQuery('.page-sidebar ul');
            var pageContent = $('.page-content');
            var pageContentBody = $('.page-content .page-content-body');
            //If the index page, window reload
            if('?homepage' == urlMark){
            	window.location.reload();
            	return;
            }

            // 移除所有已展开的二级菜单样式
            menuContainer.find('.level1.open').removeClass('open');
            menuContainer.find('.level1 .arrow').removeClass('open');
            menuContainer.find('.level1 .sub-menu').hide();

            // 移除所有激活的菜单项
            menuContainer.children('li.active').removeClass('active');
            menuContainer.children('arrow.open').removeClass('open');

            let flag = $(this).parent().attr("class") === 'level2';
            $(this).parents('li').each(function () {
                $(this).addClass('active');
                if ($(this).parent('ul.page-sidebar-menu').size() === 1) {
                    $(this).find('> a > span.arrow').addClass('open');

                    if (flag) { // 点的是二级菜单
                        $(this).find('.arrow').addClass('open');
                        $(this).find('.sub-menu').show();
                    }
                }
            });

            if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
                $('.page-header .responsive-toggler').click();
            }
			
			// 销毁echarts
            if (typeof disposeEcharts === 'function') {
                disposeEcharts();
            }

//            Metronic.startPageLoading();
            TimerManager.clear();
            var the = $(this);
            
            if(		'dbtbackup' == $(this).attr("name") || 
            		'dbtrecovery' == $(this).attr("name") || 
            		'dbtdata' == $(this).attr("name") || 
            		'dbtagent' == $(this).attr("name") || 
            		'dbtstorage' == $(this).attr("name") || 
            		'dbtlog' == $(this).attr("name")){
            	var html = '<iframe style="margin: -10px -20px; width: 102.8%; height: 102.8%;" src="' + url + '" id="iframepage" width="100%" height="100%" frameborder="0" scrolling="yes" onLoad=""></iframe>';
            	pageContentBody.html(html);
            }else{
            	$.ajax({
                    type: "GET",
                    cache: false,
                    url: url,
                    dataType: "html",
                    success: function (res) {
//                        if (the.parents('li.open').size() === 0) {
//                            $('.page-sidebar-menu > li.open > a').click();
//                        }

//                        Metronic.stopPageLoading();
                        pageContentBody.html(res);
                        Layout.fixContentHeight(); // fix content height
                        Metronic.initAjax(); // initialize core stuff
                        CURRENT_URL = url;
                        History.pushState({url:url}, CONF.SYSTEMNAME, urlMark);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        Metronic.stopPageLoading();
                        pageContentBody.html('<h4>Could not load the requested content.</h4>');
                    }
                });
            }
        });

        // handle ajax link within main content
        jQuery('.page-content').on('click', '.ajaxify', function (e) {
            e.preventDefault();
            Metronic.scrollTop();

            var url = $(this).attr("href");
            var urlMark = "?" + $(this).attr("name");
            var pageContent = $('.page-content');
            var pageContentBody = $('.page-content .page-content-body');
			
			CTLSIDEBAR(this.name);

//            Metronic.startPageLoading();

            if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
                $('.page-header .responsive-toggler').click();
            }
			// 销毁echarts
            if (typeof disposeEcharts === 'function') {
                disposeEcharts();
            }
			
            TimerManager.clear();
            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function (res) {
//                    Metronic.stopPageLoading();
                    pageContentBody.html(res);
                    Layout.fixContentHeight(); // fix content height
                    Metronic.initAjax(); // initialize core stuff
                    CURRENT_URL = url;
                    History.pushState({url:url}, CONF.SYSTEMNAME, urlMark);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                    Metronic.stopPageLoading();
                }
            });
        });
        
        // handle ajax link within header
        jQuery('.page-header').on('click', '.ajaxify', function (e) {
            e.preventDefault();
            Metronic.scrollTop();

            //如果是禁用选项,不处理
            if($(this).hasClass("disableda")){
            	return true;
            }

            var url = $(this).attr("href");
            var urlMark = "?" + $(this).attr("name");
            var pageContent = $('.page-content');
            var pageContentBody = $('.page-content .page-content-body');

//            Metronic.startPageLoading();

            if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
                $('.page-header .responsive-toggler').click();
            }
			// 销毁echarts
            if (typeof disposeEcharts === 'function') {
                disposeEcharts();
            }
            TimerManager.clear();
            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function (res) {
//                    Metronic.stopPageLoading();
                    pageContentBody.html(res);
                    Layout.fixContentHeight(); // fix content height
                    Metronic.initAjax(); // initialize core stuff
                    CURRENT_URL = url;
                    History.pushState({url:url}, CONF.SYSTEMNAME, urlMark);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                    Metronic.stopPageLoading();
                }
            });
        });
        
        //history bind
        History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        	var State = History.getState(); // Note: We are using History.getState() instead of event.state
        	var url = State.data.url;
        	var pageContentBody = $('.page-content .page-content-body');
        	if('./content/platform/databackup_center.php' == url){
            	window.location.reload();
            	return;
            }
        	if(CURRENT_URL == url) return;	//fix page repeated loading
        	$.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function (res) {
//                    Metronic.stopPageLoading();
                    pageContentBody.html(res);
                    Layout.fixContentHeight(); // fix content height
                    Metronic.initAjax(); // initialize core stuff
                    CURRENT_URL = url;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                    Metronic.stopPageLoading();
                }
            });
        });
     
        // handle sidebar hover effect        
        handleFixedSidebarHoverEffect();

        // handle the search bar close
        $('.page-sidebar').on('click', '.sidebar-search .remove', function (e) {
            e.preventDefault();
            $('.sidebar-search').removeClass("open");
        });

        // handle the search query submit on enter press
        $('.page-sidebar .sidebar-search').on('keypress', 'input.form-control', function (e) {
            if (e.which == 13) {
                $('.sidebar-search').submit();
                return false; //<---- Add this line
            }
        });

        // handle the search submit(for sidebar search and responsive mode of the header search)
        $('.sidebar-search .submit').on('click', function (e) {
            e.preventDefault();
            if ($('body').hasClass("page-sidebar-closed")) {
                if ($('.sidebar-search').hasClass('open') === false) {
                    if ($('.page-sidebar-fixed').size() === 1) {
                        $('.page-sidebar .sidebar-toggler').click(); //trigger sidebar toggle button
                    }
                    $('.sidebar-search').addClass("open");
                } else {
                    $('.sidebar-search').submit();
                }
            } else {
                $('.sidebar-search').submit();
            }
        });

        // handle close on body click
        if ($('.sidebar-search').size() !== 0) {
            $('.sidebar-search .input-group').on('click', function(e){
                e.stopPropagation();
            });

            $('body').on('click', function() {
                if ($('.sidebar-search').hasClass('open')) {
                    $('.sidebar-search').removeClass("open");
                }
            });
        }
    };

    // Helper function to calculate sidebar height for fixed sidebar layout.
    var _calculateFixedSidebarViewportHeight = function () {
        var sidebarHeight = Metronic.getViewPort().height - $('.page-header').outerHeight();
        if ($('body').hasClass("page-footer-fixed")) {
            sidebarHeight = sidebarHeight - $('.page-footer').outerHeight();
        }

        return sidebarHeight;
    };

    // Handles fixed sidebar
    var handleFixedSidebar = function () {
        var menu = $('.page-sidebar-menu');

        Metronic.destroySlimScroll(menu);

        if ($('.page-sidebar-fixed').size() === 0) {
            handleSidebarAndContentHeight();
            return;
        }

        if (Metronic.getViewPort().width >= resBreakpointMd) {
            menu.attr("data-height", _calculateFixedSidebarViewportHeight());
            Metronic.initSlimScroll(menu);
            handleSidebarAndContentHeight();
        }
    };

    // Handles sidebar toggler to close/hide the sidebar.
    var handleFixedSidebarHoverEffect = function () {
        var body = $('body');
        if (body.hasClass('page-sidebar-fixed')) {
            $('.page-sidebar').on('mouseenter', function () {
                if (body.hasClass('page-sidebar-closed')) {
                    $(this).find('.page-sidebar-menu').removeClass('page-sidebar-menu-closed');
                }
            }).on('mouseleave', function () {
                if (body.hasClass('page-sidebar-closed')) {
                    $(this).find('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
                }
            });
        }
    };

    // Hanles sidebar toggler
    var handleSidebarToggler = function () {
        var body = $('body');
        if ($.cookie && $.cookie('sidebar_closed') === '1' && Metronic.getViewPort().width >= resBreakpointMd) {
//    	if ($.cookie && Metronic.getViewPort().width >= resBreakpointMd) {
            $('body').addClass('page-sidebar-closed');
            $('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
            $(".scopyright").hide();
        }

        // handle sidebar show/hide
        $('body').on('click', '.sidebar-toggler', function (e) {
            var sidebar = $('.page-sidebar');
            var sidebarMenu = $('.page-sidebar-menu');
            $(".sidebar-search", sidebar).removeClass("open");

            if (sidebarMenu.hasClass("page-sidebar-menu-closed")) {
            	//展开
            	$(".scopyright").show();
                body.removeClass("page-sidebar-closed");
                sidebarMenu.removeClass("page-sidebar-menu-closed");
                if ($.cookie) {
                    $.cookie('sidebar_closed', '0');
                }
            } else {
            	//收起
            	$(".scopyright").hide();
                body.addClass("page-sidebar-closed");
                sidebarMenu.addClass("page-sidebar-menu-closed");
                if (body.hasClass("page-sidebar-fixed")) {
                    sidebarMenu.trigger("mouseleave");
                }
                if ($.cookie) {
                    $.cookie('sidebar_closed', '1');
                }
            }

            $(window).trigger('resize');
        });
    };

    // Handles the horizontal menu
    var handleHorizontalMenu = function () {
        //handle tab click
        $('.page-header').on('click', '.hor-menu a[data-toggle="tab"]', function (e) {
            e.preventDefault();
            var nav = $(".hor-menu .nav");
            var active_link = nav.find('li.current');
            $('li.active', active_link).removeClass("active");
            $('.selected', active_link).remove();
            var new_link = $(this).parents('li').last();
            new_link.addClass("current");
            new_link.find("a:first").append('<span class="selected"></span>');
        });

        // handle search box expand/collapse        
        $('.page-header').on('click', '.search-form', function (e) {
            $(this).addClass("open");
            $(this).find('.form-control').focus();

            $('.page-header .search-form .form-control').on('blur', function (e) {
                $(this).closest('.search-form').removeClass("open");
                $(this).unbind("blur");
            });
        });

        // handle hor menu search form on enter press
        $('.page-header').on('keypress', '.hor-menu .search-form .form-control', function (e) {
            if (e.which == 13) {
                $(this).closest('.search-form').submit();
                return false;
            }
        });

        // handle header search button click
        $('.page-header').on('mousedown', '.search-form.open .submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.search-form').submit();
        });

        // handle hover dropdown menu for desktop devices only
        $('[data-hover="megamenu-dropdown"]').not('.hover-initialized').each(function() {   
            $(this).dropdownHover(); 
            $(this).addClass('hover-initialized'); 
        });
        
        $(document).on('click', '.mega-menu-dropdown .dropdown-menu', function (e) {
            e.stopPropagation();
        });
        
        handlePageHeaderMenuActiveLinks();
    };

    // Handles Bootstrap Tabs.
    var handleTabs = function () {
        // fix content height on tab click
        $('body').on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
            handleSidebarAndContentHeight();
        });
    };

    // Handles the go to top button at the footer
    var handleGoTop = function () {
        var offset = 300;
        var duration = 500;

        if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {  // ios supported
            $(window).bind("touchend touchcancel touchleave", function(e){
               if ($(this).scrollTop() > offset) {
                    $('.scroll-to-top').fadeIn(duration);
                } else {
                    $('.scroll-to-top').fadeOut(duration);
                }
            });
        } else {  // general 
            $(window).scroll(function() {
                if ($(this).scrollTop() > offset) {
                    $('.scroll-to-top').fadeIn(duration);
                } else {
                    $('.scroll-to-top').fadeOut(duration);
                }
            });
        }
        
        $('.scroll-to-top').click(function(e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: 0}, duration);
            return false;
        });
    };

    // Hanlde 100% height elements(block, portlet, etc)
    var handle100HeightContent = function () {

        var target = $('.full-height-content');
        var height;

        height = Metronic.getViewPort().height -
            $('.page-header').outerHeight(true) -
            $('.page-footer').outerHeight(true) -
            $('.page-title').outerHeight(true) -
            $('.page-bar').outerHeight(true);

        if (target.hasClass('portlet')) {
            var portletBody = target.find('.portlet-body');
            
            if (Metronic.getViewPort().width < resBreakpointMd) {
                Metronic.destroySlimScroll(portletBody.find('.full-height-content-body')); // destroy slimscroll 
                return;
            }

            height = height -
                target.find('.portlet-title').outerHeight(true) -
                parseInt(target.find('.portlet-body').css('padding-top')) -
                parseInt(target.find('.portlet-body').css('padding-bottom')) - 2;

            if (target.hasClass("full-height-content-scrollable")) {
                height = height - 35;
                portletBody.find('.full-height-content-body').css('height', height);
                Metronic.initSlimScroll(portletBody.find('.full-height-content-body'));
            } else {
                portletBody.css('min-height', height);
            }
        } else {
            if (Metronic.getViewPort().width < resBreakpointMd) {
                Metronic.destroySlimScroll(target.find('.full-height-content-body')); // destroy slimscroll 
                return;
            }

            if (target.hasClass("full-height-content-scrollable")) {
                height = height - 35;
                target.find('.full-height-content-body').css('height', height);
                Metronic.initSlimScroll(target.find('.full-height-content-body'));
            } else {
                target.css('min-height', height);
            }
        }
    };
    //* END:CORE HANDLERS *//

    return {
        // Main init methods to initialize the layout
        //IMPORTANT!!!: Do not modify the core handlers call order.

        initHeader: function() {
            handleHorizontalMenu(); // handles horizontal menu    
        },

        setSidebarMenuActiveLink: function(mode, el) {
            handleSidebarMenuActiveLink(mode, el);
        },

        initSidebar: function() {
            //layout handlers
            handleFixedSidebar(); // handles fixed sidebar menu
            handleSidebarMenu(); // handles main menu
            handleSidebarToggler(); // handles sidebar hide/show

            if (Metronic.isAngularJsApp()) {      
                handleSidebarMenuActiveLink('match'); // init sidebar active links 
            }

            Metronic.addResizeHandler(handleFixedSidebar); // reinitialize fixed sidebar on window resize
        },

        initContent: function() {
            handle100HeightContent(); // handles 100% height elements(block, portlet, etc)
            handleTabs(); // handle bootstrah tabs

            Metronic.addResizeHandler(handleSidebarAndContentHeight); // recalculate sidebar & content height on window resize
            Metronic.addResizeHandler(handle100HeightContent); // reinitialize content height on window resize 
        },

        initFooter: function() {
            handleGoTop(); //handles scroll to top functionality in the footer
        },

        init: function () {            
            this.initHeader();
            this.initSidebar();
            this.initContent();
            this.initFooter();
        },

        //public function to fix the sidebar and content height accordingly
        fixContentHeight: function () {
            handleSidebarAndContentHeight();
        },

        initFixedSidebarHoverEffect: function() {
            handleFixedSidebarHoverEffect();
        },

        initFixedSidebar: function() {
            handleFixedSidebar();
        },

        getLayoutImgPath: function () {
            return Metronic.getAssetsPath() + layoutImgPath;
        },

        getLayoutCssPath: function () {
            return Metronic.getAssetsPath() + layoutCssPath;
        }
    };

}();