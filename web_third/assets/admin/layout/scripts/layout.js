let Layout = (function() {

    const sidebarToggle = function() {
        let SELECTED_FLAG = false; // 记录侧边导航栏的 list-group-item 是否有选中过
        sessionStorage.setItem('SELECTED_FLAG', false); // 存储在 sessionStorage 中供路由导航 navigateFn 使用

        /**
         * 监听sidebar的折叠和展开
         */
        $('#sidebar-toggle').on('click', function() {
            let flag = Boolean(sessionStorage.getItem('SELECTED_FLAG')); // sessionStorage中取出来的key是String类型，使用时转一下boolean
            // 给 page-sidebar-wrapper 添加/移除 sidebar-close
            $('.page-sidebar-wrapper').toggleClass('sidebar-close');

            // 展开 => 关闭
            if ($('.page-sidebar-wrapper').hasClass('sidebar-close')) {
                // 动态设置 sidebar-collapse 和 page-content-wrapper 
                $('.page-content-wrapper').css({
                    'width': 'calc(100% - 76px)',
                    'margin-left': '76px'
                });

                $('.sidebar-collapse').removeClass('hover-scroll-y');
                setTimeout(() => {
                    $('.sidebar-close .sidebar-collapse .page-sidebar .accordion-item-first .accordion-collapse-first.show').removeClass('show');
                }, 0);
            } else {
                // 动态设置 sidebar-collapse 和 page-content-wrapper 
                $('.page-content-wrapper').css({
                    'width': 'calc(100% - 232px)',
                    'margin-left': '232px'
                });

                $('.sidebar-collapse').addClass('hover-scroll-y');
                // 关闭 -> 展开
                let accorFirstBodyEle = $('.page-sidebar-wrapper:not(.sidebar-close) .sidebar-collapse .page-sidebar .accordion-item-first .accordion-collapse-first');

                // 选中过
                if (flag) {
                    let hasLightActive = accorFirstBodyEle.hasClass('light-active');
                    // active 和 light-active 共存场景，则是fold前谁展开，unfold后也谁展开
                    if (hasLightActive) {
                        let lightActivedAccor = $('.page-sidebar-wrapper:not(.sidebar-close) .sidebar-collapse .page-sidebar .accordion-item-first .accordion-collapse-first.light-active');

                        if (lightActivedAccor.hasClass('show')) {
                            lightActivedAccor.removeClass('show');
                            setTimeout(() => {
                                lightActivedAccor.addClass('show');
                            }, 400);
                        } else {
                            setTimeout(() => {
                                lightActivedAccor.addClass('show');
                            }, 400);
                        }
                    } else {
                        // 选中过有show, 找到加过 active 的accordion item, 加定时器模拟accordion过渡状态
                        let activedAccor = $('.page-sidebar-wrapper:not(.sidebar-close) .sidebar-collapse .page-sidebar .accordion-item-first .accordion-collapse-first.active');

                        if (accorFirstBodyEle.hasClass('show')) {
                            accorFirstBodyEle.removeClass('show');
                            setTimeout(() => {
                                activedAccor.addClass('show');
                            }, 400);
                        } else {
                            // 选中过没有show则加show, 找到加过 active 的accordion item, 并加定时器模拟accordion过渡状态
                            setTimeout(() => {
                                activedAccor.addClass('show');
                            }, 400);
                        }
                    }
                } else {
                    let lightActivedAccor = $('.page-sidebar-wrapper:not(.sidebar-close) .sidebar-collapse .page-sidebar .accordion-item-first .accordion-collapse-first.light-active');
                    // 没有选中但有show，找到加过 light-active 的accordion item, 并加定时器模拟accordion过渡状态
                    if (accorFirstBodyEle.hasClass('show')) {
                        accorFirstBodyEle.removeClass('show');
                        setTimeout(() => {
                            lightActivedAccor.addClass('show');
                        }, 400);
                    } else {
                        // 选中过没有show则加show, 找到加过 light-active 的accordion item, 并加定时器模拟accordion过渡状态
                        setTimeout(() => {
                            lightActivedAccor.addClass('show');
                        }, 400);
                    }
                }
            }
        });

        /**
         * 监听一级 accordion button 的点击
         */
        $('.page-sidebar .header-first__button').on('click', function(e) {
          if ($('.accordion-collapse.accordion-collapse-first').hasClass('light-active')) {
              $('.accordion-collapse.accordion-collapse-first').removeClass('light-active');
          }

          let accordionItemFirst = $(this).parents('.accordion-item-first');
          let activedAccordionItem = accordionItemFirst.find('.accordion-collapse-first.active');
          let lightActivedAccordionItem = accordionItemFirst.find('.header-first__button.collapsed');

          // 只在没有active过的 和 没有 show的accordion body 加light-active
          if (activedAccordionItem.length === 0 && lightActivedAccordionItem.length === 0) {
              accordionItemFirst.find('.accordion-collapse-first').addClass('light-active');
          }

        });

        /**
         * 监听一级list-group-item click
         */
        $('.page-sidebar .list-group-first__item').on('click', function() {
            if ($('.accordion-button.header-index__button').hasClass('selected')) {
                $('.accordion-button.header-index__button').removeClass('selected');
            }

            $('.list-group-second__item').removeClass('selected');

            if ($('.accordion-button.header-first__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-first').hasClass('active')) {
                $('.accordion-button.header-first__button').removeClass('active');
                $('.accordion-collapse.accordion-collapse-first').removeClass('active');
            }

            if ($('.accordion-button.header-second__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-second').hasClass('active')) {
                $('.accordion-button.header-second__button').removeClass('active');
                $('.accordion-collapse.accordion-collapse-second').removeClass('active');
            }

            let accordionItemFirst = $(this).parents('.accordion-item-first');

            // 移除 light-active
            if (accordionItemFirst.find('.accordion-collapse-first.light-active').length > 0) {
                accordionItemFirst.find('.accordion-collapse-first').removeClass('light-active');
            }

            accordionItemFirst.find('.header-first__button').addClass('active');
            accordionItemFirst.find('.accordion-collapse-first').addClass('active');


            if (!$(this).hasClass('selected')) {
                $('.list-group-first__item').removeClass('selected');
                $(this).addClass('selected');
                SELECTED_FLAG = true;
                sessionStorage.setItem('SELECTED_FLAG', true);
                // 如果导航栏是折叠状态，还要手动加上 show 和 去除 collapsed
                if ($('.page-sidebar-wrapper').hasClass('sidebar-close')) {
                    // 关闭上一次打开的accordion item
                    accordionItemFirst.find('.header-first__button').removeClass('collapsed');
                    accordionItemFirst.find('.accordion-collapse-first').addClass('show');
                }
            }
        });

        /**
         * 监听二级list-group-item 的点击
         */
        $('.page-sidebar .list-group-second__item').on('click', function() {
            if ($('.accordion-button.header-index__button').hasClass('selected')) {
                $('.accordion-button.header-index__button').removeClass('selected');
            }

            $('.list-group-first__item').removeClass('selected');

            if ($('.accordion-button.header-first__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-first').hasClass('active')) {
                $('.accordion-button.header-first__button').removeClass('active');
                $('.accordion-collapse.accordion-collapse-first').removeClass('active');
            }

            if ($('.accordion-button.header-second__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-second').hasClass('active')) {
                $('.accordion-button.header-second__button').removeClass('active');
                $('.accordion-collapse.accordion-collapse-second').removeClass('active');
            }

            let accordionItemFirst = $(this).parents('.accordion-item-first');

            // 移除 light-active
            if (accordionItemFirst.find('.accordion-collapse-second.light-active').length > 0) {
                accordionItemFirst.find('.accordion-collapse-second').removeClass('light-active');
            }

            accordionItemFirst.find('.header-first__button').addClass('active');
            accordionItemFirst.find('.accordion-collapse-first').addClass('active');
            accordionItemFirst.find('.header-second__button').addClass('active');
            accordionItemFirst.find('.accordion-collapse-second').addClass('active');

            if (!$(this).hasClass('selected')) {
                $('.list-group-second__item').removeClass('selected');
                $(this).addClass('selected');
                SELECTED_FLAG = true;
                sessionStorage.setItem('SELECTED_FLAG', true);
                // 如果导航栏是折叠状态，还要手动加上 show 和 去除 collapsed
                if ($('.page-sidebar-wrapper').hasClass('sidebar-close')) {
                    // 关闭上一次打开的accordion item
                    accordionItemFirst.find('.header-first__button').removeClass('collapsed');
                    accordionItemFirst.find('.accordion-collapse-first').addClass('show');
                }
            }
        })

        /**
         * 监听index-button click
         */
        $('.page-sidebar .accordion-button.header-index__button').on('click', function() {
            if (!$(this).hasClass('selected')) {
                $('.list-group-item').removeClass('selected');

                $('.accordion-button').each(function() {
                    // 移除上一个 header-index__button 的高亮色
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    }
                    // 移除上一个 menu-first-button 的高亮色
                    if (!$(this).hasClass('collapsed')) {
                        $(this).addClass('collapsed');
                    }
                });

                if ($('.accordion-button.header-first__button').hasClass('active')) {
                    $('.accordion-button.header-first__button').removeClass('active');
                }

                if ($('.accordion-collapse.accordion-collapse-first').hasClass('active')) {
                    $('.accordion-collapse.accordion-collapse-first').removeClass('active');
                }

                if ($('.accordion-collapse.accordion-collapse-first').hasClass('light-active')) {
                    $('.accordion-collapse.accordion-collapse-first').removeClass('light-active');
                }

                $('.accordion-collapse').each(function() {
                    if ($(this).hasClass('show')) {
                        $(this).removeClass('show');
                    }
                });

                $(this).addClass('selected');
            }
        });
    };

    /**
     * 处理侧边栏菜单点击事件
     */
    const handleSidebarMenu = function() {
        jQuery('.page-sidebar').on('click', '.accordion-item-first a', function(e) {
            e.preventDefault();

            let url = $(this).attr('href');
            if(PAGEROUTE[url] != undefined) {
                url = PAGEROUTE[url];
            }

            let routeName = this.name;
            let parentId = $(this).closest('.accordion-item-first').find('.header-first').attr('id');
            let pageContentBody = $('.page-content .page-content-body');
            let $pageContent = $('.page-content');

            // pageLoading
            $pageContent.block({
                message: '<div class="blockui-message"><span class="spinner-border spinner-primary"></span> Loading...</div>'
            });

            $.ajax({
                type: 'GET',
                cache: false,
                url: url,
                dataType: 'html',
                success: (res) => {
                    $('.admin-menu-wrapper__content .admin-menu-wrapper__content__item').removeClass('selected');
                    pageContentBody.html(res);
                    $pageContent.unblock();
                    
                    initCommonComponents(); // 初始化通用组件

                    History.pushState({ url: url, routeName: routeName }, '', `?${routeName}`);
                    ctlSidebar(routeName, parentId);
                },
                error: (error) => {
                    console.log(error, 'layout.js-error');
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                    $pageContent.unblock();
                }
            });
        });
    };

    /**
     * 初始化页面中的Tooltips
     */
    const initTooltips = function () {
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * 初始化页面中的Select2组件
     */
    const initSelect2 = () => {
        $('.select2').select2({
            minimumResultsForSearch: Infinity, // 取消下拉的搜索
            theme: 'bootstrap-5'
        });
    }

    /**
     * 初始化通用组件
     */
    const initCommonComponents = () => {

        initSelect2();

        initTooltips();
    }

    /**
     * 监听offcanvas蒙层click
     */
    const handleClickOffcanvasBackdrop = function() {
        $('body').on('click', 'div.offcanvas-backdrop', () => {
            let showOffcanvas = $('div.offcanvas.show');

            if (!showOffcanvas.length) {
                return;
            }

            if (showOffcanvas.attr('data-bs-backdrop') !== 'static') {
                return;
            }

            showOffcanvas.addClass('offcanvas-static');

            setTimeout(() => {
                showOffcanvas.removeClass('offcanvas-static');
            }, 300);
        });
    };

    const initListener = function(){
        // handle ajax link within main content
        jQuery('.page-content').on('click', '.ajaxify', function (e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let routeId = $(this).attr('route-id');
            let routeName = this.name;

            // 左侧菜单导航从ROUTE配置中获取url
            if(PAGEROUTE[url] != undefined) {
                url = PAGEROUTE[url];
            }

            let $pageContent = $('.page-content');
            let pageContentBody = $('.page-content .page-content-body');

            $pageContent.block({
                message: '<div class="blockui-message"><span class="spinner-border spinner-primary"></span> Loading...</div>'
            });

            $.ajax({
                type: 'GET',
                cache: false,
                url: url,
                dataType: 'html',
                success: function (res) {
                    pageContentBody.html(res);
                    $pageContent.unblock();
                    $('.page-header .admin-menu-wrapper__content__item.item-info').removeClass('selected');

                    History.pushState({ url: url, routeName: routeName || '' }, '', `?${routeName}`);
                    ctlSidebar(routeName, routeId);
                    initCommonComponents();
                },
                error: function () {
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                }
            });
        });

        // handle ajax link within header
        jQuery('.page-header').on('click', '.ajaxify', function (e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let routeId = $(this).attr('route-id');
            let routeName = this.name;

            // 左侧菜单导航从ROUTE配置中获取url
            if(PAGEROUTE[url] != undefined) {
                url = PAGEROUTE[url];
            }

            let $pageContent = $('.page-content');
            let pageContentBody = $('.page-content .page-content-body');
    
            $pageContent.block({
                message: '<div class="blockui-message"><span class="spinner-border spinner-primary"></span> Loading...</div>'
            });
            $.ajax({
                type: 'GET',
                cache: false,
                url: url,
                dataType: 'html',
                success: function (res) {
                    pageContentBody.html(res);
                    $pageContent.unblock();

                    History.pushState({ url: url, routeName: routeName || '' }, '', `?${routeName}`);
                    ctlSidebar(routeName, routeId);
                    initCommonComponents();
                },
                error: function () {
                    pageContentBody.html('<h4>Could not load the requested content.</h4>');
                }
            });
        });

        // init Tooltips
        initTooltips();

        handleClickOffcanvasBackdrop();

        // handle browser statechange
        window.addEventListener('popstate', function(event) {
            console.log(event.state, '状态变化');
        });
    }
    /**
     * 导航栏路由联动
     */
    const ctlSidebar = (routeName, accorItemHeadId = "parent_homepage") => {
        if (!routeName) {
            return;
        }
        
        // 移除上一次 header-index__button 的高亮样式
        if ($('.accordion-button.header-index__button').hasClass('selected')) {
            $('.accordion-button.header-index__button').removeClass('selected');
        }

        // 移除上一次 header-first__button 的active高亮样式
        if ($('.accordion-button.header-first__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-first').hasClass('active')) {
            $('.accordion-button.header-first__button').removeClass('active');
            $('.accordion-collapse.accordion-collapse-first').removeClass('show active');
            $('.accordion-button.header-first__button').addClass('collapsed');
        }

        // 移除上一次 header-second__button 的 active 样式
        if ($('.header-second__button').hasClass('active')) {
            $('.header-second__button').removeClass('active');
        }

        // 移除上一次 list-group-first__item 的高亮样式
        if ($('.list-group-first__item').hasClass('selected')) {
            $('.list-group-first__item').removeClass('selected');
        }

        // 移除上一次 list-group-second__item 的高亮样式
        if ($('.list-group-second__item').hasClass('selected')) {
            $('.list-group-second__item').removeClass('selected');
        }

        // 移除上一次 header-first__button 的 light-active 样式
        if ($('.accordion-collapse.accordion-collapse-first').hasClass('light-active')) {
            $('.accordion-collapse.accordion-collapse-first').removeClass('show light-active');
            $('.accordion-button.header-first__button').addClass('collapsed');
        }

        // 移除上一次 header-second__button 的 light-active 样式
        if ($('.accordion-collapse.accordion-collapse-second').hasClass('light-active')) {
            $('.accordion-collapse.accordion-collapse-second').removeClass('show light-active');
            $('.accordion-button.header-second__button').addClass('collapsed');
        }

        let accordionItemFirst = $(`#${accorItemHeadId}`).parents('.accordion-item-first');
        
        // 判断要跳转的目标页面是属于第一层级 还是 第二层级 还是 第三层级
        if (accordionItemFirst.hasClass('first-nav')) {
            // 加上active
            accordionItemFirst.find('.accordion-button.header-index__button').addClass('selected');

        } else {
            // 移除 当前accordion 的 light-active
            if (accordionItemFirst.find('.accordion-collapse-first.light-active').length > 0) {
                accordionItemFirst.find('.accordion-collapse-first').removeClass('light-active');
            }

            // 移除 当前accordion-second 的 light-active
            if (accordionItemFirst.find('.accordion-collapse-second.light-active').length > 0) {
                accordionItemFirst.find('.accordion-collapse-second').removeClass('light-active');
            }

            // 加上active
            accordionItemFirst.find('.header-first__button').addClass('active');
            accordionItemFirst.find('.accordion-collapse-first').addClass('show active');
            accordionItemFirst.find('.header-first__button').removeClass('collapsed');

            // 遍历找到与routeName对应的 list-group-text
            let accorItemFirstBodyId = `#child_${accorItemHeadId.split('parent_')[1]}`;
            
            // 首先检查是否是第二级菜单项被激活
            let foundMatch = false;
            
            // 遍历 menu-first-body 和 menu-second-body 找到name与routeName一致的list-group-item
            $(`${accorItemFirstBodyId} .list-group-item`).each(function() {
                if ($(this).attr('name') === routeName) {
                    $('.list-group-item').removeClass('selected');
                    $(this).addClass('selected');
                    
                    // 更新存储在 sessionStorage 中的 SELECTED_FLAG
                    sessionStorage.setItem('SELECTED_FLAG', true);

                    // 检查当前项是否为第二级菜单项(list-group-second__item)
                    if ($(this).hasClass('list-group-second__item')) {
                        // 为第二级菜单按钮添加active样式
                        let accordionItemSecond = $(this).closest('.accordion-item-second');
                        accordionItemSecond.find('.header-second__button').addClass('active');
                        
                        // 展开第二级菜单
                        accordionItemSecond.find('.accordion-collapse-second').addClass('show active');
                        accordionItemSecond.find('.header-second__button').removeClass('collapsed');
                    } 
                    // 如果是第一级菜单项(list-group-first__item)，确保第二级菜单关闭
                    else if ($(this).hasClass('list-group-first__item')) {
                        // 移除所有第二级菜单的active状态
                        accordionItemFirst.find('.header-second__button').removeClass('active');
                        accordionItemFirst.find('.accordion-collapse-second').removeClass('show active');
                        accordionItemFirst.find('.header-second__button').addClass('collapsed');
                    }
                    
                    // 如果导航栏是折叠状态，还要手动加上 show 和 去除 collapsed
                    if ($('.page-sidebar-wrapper').hasClass('sidebar-close')) {
                        // 关闭上一次打开的accordion item
                        accordionItemFirst.find('.header-first__button').removeClass('collapsed');
                        accordionItemFirst.find('.accordion-collapse-first').addClass('show');
                        
                        // 如果是第二级菜单项，同样处理第二级折叠状态
                        if ($(this).hasClass('list-group-second__item')) {
                            let accordionItemSecond = $(this).closest('.accordion-item-second');
                            accordionItemSecond.find('.header-second__button').removeClass('collapsed');
                            accordionItemSecond.find('.accordion-collapse-second').addClass('show');
                        }
                    }
                    
                    foundMatch = true;
                    return false; // 跳出循环
                }
            });
            
            // 如果没找到匹配项，但仍然需要确保第二级菜单的初始状态
            if (!foundMatch) {
                // 确保第二级菜单处于正确的初始状态
                accordionItemFirst.find('.header-second__button').removeClass('active');
                accordionItemFirst.find('.accordion-collapse-second').removeClass('show active');
                accordionItemFirst.find('.header-second__button').addClass('collapsed');
            }
        }
    }

    const handlePageHeader = function() {
        $('.admin-menu-wrapper__content__item.item-info').on('click', function() {
            if (!$(this).hasClass('selected')) {
                $('.admin-menu-wrapper__content__item.item-info').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        $('.page-header-nav__menu__alarm').on('mouseenter', function() {
            $('.page-header-nav__menu .page-header-nav__menu__alarm').css('border-color', '#AAB3C0');
            $('.page-header-nav__menu .page-header-nav__menu__alarm .alarm-img i').css('color', '#718096');
        });

        $('.page-header-nav__menu__alarm').on('mouseleave', function() {
            $('.page-header-nav__menu .page-header-nav__menu__alarm').css('border-color', '#E3E6EA');
            $('.page-header-nav__menu .page-header-nav__menu__alarm .alarm-img i').css('color', '#8D99AB');
        });

        $('.dropdown-menu.alarm-menu').on('mouseenter', function() {
            $('.page-header-nav__menu .page-header-nav__menu__alarm').css('border-color', '#AAB3C0');
            $('.page-header-nav__menu .page-header-nav__menu__alarm .alarm-img i').css('color', '#718096');
        });

        $('.dropdown-menu.alarm-menu').on('mouseleave', function() {
            $('.page-header-nav__menu .page-header-nav__menu__alarm').css('border-color', '#E3E6EA');
            $('.page-header-nav__menu .page-header-nav__menu__alarm .alarm-img i').css('color', '#8D99AB');
        });

        $('.dropdown-menu.admin-menu').on('mouseenter', function() {
            $('.admin-dropdown .admin-dropdown__label i').css('color', '#718096');
            $('.admin-dropdown .admin-dropdown__content').css('color', '#718096');

            if (!$('.dropdown-toggle.admin-dropdown').hasClass('active')) {
                $('.dropdown-toggle.admin-dropdown').addClass('active');
            }
        });

        $('.dropdown-menu.admin-menu').on('mouseleave', function() {
            $('.admin-dropdown .admin-dropdown__label i').css('color', '#8D99AB');
            $('.admin-dropdown .admin-dropdown__content').css('color', '#8D99AB');

            if ($('.dropdown-toggle.admin-dropdown').hasClass('active')) {
                $('.dropdown-toggle.admin-dropdown').removeClass('active');
            }
        });

        $('.page-header-nav__menu__admin').on('mouseenter', function() {
            $('.admin-dropdown .admin-dropdown__label i').css('color', '#718096');
            $('.admin-dropdown .admin-dropdown__content').css('color', '#718096');
        });

        $('.page-header-nav__menu__admin').on('mouseleave', function() {
            $('.admin-dropdown .admin-dropdown__label i').css('color', '#8D99AB');
            $('.admin-dropdown .admin-dropdown__content').css('color', '#8D99AB');
        });
    }

    return {
        initSidebar: function() {
            sidebarToggle();
            handleSidebarMenu();
            initListener();
        },

        initPageHeader: function() {
            handlePageHeader();
        },

        init: function() {
            this.initSidebar();
            this.initPageHeader()
        }
    };
  })();

  jQuery(document).ready(function() {
    Layout.init();
  });
