/***
Wrapper/Helper Class for datagrid based on jQuery Datatable Plugin
***/
var Datatable = function() {
    var tableOptions; // main options
    var dataTable; // datatable object
    var table; // actual table jquery object
    var tableContainer; // actual table container object
    var tableWrapper; // actual table wrapper jquery object
    var tableInitialized = false;
    var detailInitialized = false;
    var ajaxParams = {}; // set filter mode
    var the;

    var countSelectedRecords = function() {
        var selected = $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        var text = tableOptions.dataTable.language.metronicGroupActions;
        if(!text){
        	text = "";
        }
        if (selected > 0) {
            $('.table-group-actions > span', tableWrapper).text(text.replace("_TOTAL_", selected));
        } else {
            $('.table-group-actions > span', tableWrapper).text("");
        }
    };

    return {
    	
        //main function to initiate the module
        init: function(options) {

            if (!$().dataTable) {
                return;
            }
            the = this;

            // default settings
            options = $.extend(true, {
                src: "", // actual table  
                showDetail: false,	//show detail flag
                filterApplyAction: "filter",
                filterCancelAction: "filter_cancel",
                resetGroupActionInputOnSuccess: true,
                loadingMessage: 'Loading...',
                checkbox:true,
                dataTable: {
                    "dom": "<'row'<'col-md-12'B>><'table-scrollable't><'row table-page'<'col-md-2 col-sm-12'><'col-md-10 col-sm-12 page-right' pli>r>", // datatable layout
                    "pageLength": 10, // default records per page
                    "language": { // language settings
                        // metronic spesific
//                        "metronicGroupActions": "_TOTAL_ records selected:  ",
                        "metronicAjaxRequestGeneralError": LANG.UI_TOOLS_REQUEST_FAILURE,

                        // data tables spesific
                        "lengthMenu": LANG.UI_TOOLS_TABLE_LENGTH_MENU,
                        "info": LANG.UI_TOOLS_TABLE_INFO,
                        "infoEmpty": "",
                        "emptyTable": LANG.UI_TOOLS_NO_DATA,
                        "zeroRecords": LANG.UI_TOOLS_NO_DATA,
                        "paginate": {
                            "previous": LANG.UI_TOOLS_PRE_PAGE,
                            "next": LANG.UI_TOOLS_NEXT_PAGE,
                            "last": LANG.UI_TOOLS_LAST_PAGE,
                            "first": LANG.UI_TOOLS_FRIST_PAGE,
                            "page": LANG.UI_TOOLS_TOTAL1,
                            "pageOf": LANG.UI_TOOLS_TOTAL
                        }
                    },
                 // setup buttons extentension: http://datatables.net/extensions/buttons/
                    "lengthMenu": [
                       [10, 20, 50, 100, 150, 200],
                       [10, 20, 50, 100, 150, 200] // change per page values here
                    ],

                    "orderCellsTop": true,
                    "columnDefs": [{ // define columns sorting options(by default all columns are sortable extept the first checkbox column)
                        'orderable': false,
                        'targets': [0]
                    }],
                    "pagingType": "bootstrap_extended", // pagination type(bootstrap, bootstrap_full_number or bootstrap_extended)
                    "autoWidth": false, // disable fixed width and enable fluid table
                    "processing": false, // enable/disable display message box on record load
                    "serverSide": true, // enable/disable server side ajax loading
                    "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
                    "pageLength": 10, // default record count per page
                    "bRetrieve": true,
                    "showLoading":true,
//                    "sScrollY": 180,
//                    "bScrollInfinite": true,
                    "order": [
	                    [1, "asc"]
	                ],// set first column as a default sort by asc
                    "ajax": { // define ajax settings
                        "type": "POST", // request type
                        "timeout": 900000,
                        "url": CONF.AJAXPATH, // ajax source
                        "data": function(data) { // add request parameters before submit
                            $.each(ajaxParams, function(key, value) {
                                if("m" == key){
                                	data[key] = value;
                                }else if("f" == key){
                                	data[key] = value;
                                }else if("p" == key){
                                	data[key] = JSON.stringify(value);
                                }
                            });
                            if(tableOptions.dataTable.showLoading){
                            	Metronic.blockUI({
                                    message: tableOptions.loadingMessage,
                                    target: tableContainer,
                                    overlayColor: 'none',
                                    cenrerY: true,
                                    boxed: true
                                });
                            }
                            
                        },
                        "dataSrc": function(res) { // Manipulate the data returned from the server
                        	//移除提示框,修正刷新的时候又提示框但是提示框没有消失的bug
                        	$('.popover').remove();
                            if (res.customActionMessage) {
                                Metronic.alert({
                                    type: (res.customActionStatus == 'OK' ? 'success' : 'danger'),
                                    icon: (res.customActionStatus == 'OK' ? 'check' : 'warning'),
                                    message: res.customActionMessage,
                                    container: tableWrapper,
                                    place: 'prepend'
                                });
                            }

                            if (res.customActionStatus) {
                                if (tableOptions.resetGroupActionInputOnSuccess) {
                                    $('.table-group-action-input', tableWrapper).val("");
                                }
                            }

                            if ($('.group-checkable', table).size() === 1) {
                                $('.group-checkable', table).prop("checked", false);
                                $.uniform.update($('.group-checkable', table));
                            }

                            if (tableOptions.onSuccess) {
                                tableOptions.onSuccess.call(undefined, the);
                            }

                            Metronic.unblockUI(tableContainer);

                            return res.data;
                        },
                        "error": function() { // handle general connection errors
                            if (tableOptions.onError) {
                                tableOptions.onError.call(undefined, the);
                            }
                            
                            Metronic.alert({
                                type: 'danger',
                                icon: 'warning',
                                message: tableOptions.dataTable.language.metronicAjaxRequestGeneralError,
                                container: tableWrapper,
                                closeInSeconds: 5,
                                place: 'prepend'
                            });

                            Metronic.unblockUI(tableContainer);
                        }
                    },

                    "drawCallback": function(oSettings) { // run some code on table redraw
                        if (tableInitialized === false) { // check if table has been initialized
                            tableInitialized = true; // set table initialized
                            table.show(); // display table
                        }
                        Metronic.initUniform($('input[type="checkbox"]', table)); // reinitialize uniform checkboxes on each table reload
                        countSelectedRecords(); // reset selected records indicator
                        ctlDetail();
                        // callback for ajax data load
                        if (tableOptions.onDataLoad) {
                            tableOptions.onDataLoad.call(undefined, the);
                        }
                    }
                }
            }, options);
            var ctlDetail = function(){
            	if(!options.showDetail){
            		return;
            	}
            	var nCloneTh = document.createElement('th');
                nCloneTh.className = "table-checkbox";

                var nCloneTd = document.createElement('td');
                nCloneTd.innerHTML = '<span class="row-details row-details-close"></span>';

                table.find('thead tr').each(function () {
                	if(detailInitialized) return;
                    this.insertBefore(nCloneTh, this.childNodes[0]);
                });

                table.find('tbody tr').each(function () {
                	if(this.textContent != LANG.UI_TOOLS_NO_DATA){
                		this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
                	}else{
                		//如果没有数据,多一列
                		this.childNodes[0].colSpan = this.childNodes[0].colSpan + 1;
                	}
                });
                detailInitialized = true;
            };

            tableOptions = options;

            // create table's jquery object
            table = $(options.src);
            tableContainer = table.parents(".table-container");

            // apply the special class that used to restyle the default datatable
            var tmp = $.fn.dataTableExt.oStdClasses;

            $.fn.dataTableExt.oStdClasses.sWrapper = $.fn.dataTableExt.oStdClasses.sWrapper + " dataTables_extended_wrapper";
            $.fn.dataTableExt.oStdClasses.sFilterInput = "form-control input-small input-sm input-inline";
            $.fn.dataTableExt.oStdClasses.sLengthSelect = "form-control input-xsmall input-sm input-inline";

            // initialize a datatable
            dataTable = table.DataTable(options.dataTable);

            // revert back to default
            $.fn.dataTableExt.oStdClasses.sWrapper = tmp.sWrapper;
            $.fn.dataTableExt.oStdClasses.sFilterInput = tmp.sFilterInput;
            $.fn.dataTableExt.oStdClasses.sLengthSelect = tmp.sLengthSelect;

            // get table wrapper
            tableWrapper = table.parents('.dataTables_wrapper');

            // build table group actions panel
            if ($('.table-actions-wrapper', tableContainer).size() === 1) {
                $('.table-group-actions', tableWrapper).html($('.table-actions-wrapper', tableContainer).html()); // place the panel inside the wrapper
                $('.table-actions-wrapper', tableContainer).remove(); // remove the template container
            }
            // handle group checkboxes check/uncheck
            $('.group-checkable', table).change(function() {
            	if(!tableOptions.checkbox) return;
                var set = $('tbody > tr > td:nth-child(1) input[type="checkbox"]', table);
                if(tableOptions.showDetail){
                	set = $('tbody > tr > td:nth-child(2) input[type="checkbox"]', table);
                }
                var checked = $(this).is(":checked");
                $(set).each(function() {
                    $(this).prop("checked", checked);
                });
                $.uniform.update(set);
                countSelectedRecords();
            });
            $('tbody > tr > td:nth-child(1) input[type="checkbox"]', table).click(function(){
            	
            });
            
            
            // handle row's checkbox click
            table.on('change', 'tbody > tr > td:nth-child(1) input[type="checkbox"]', function() {
            	ctlCheckBox($(this).closest("tr").find("input"));
            });

            // handle filter submit button click
            table.on('click', '.filter-submit', function(e) {
                e.preventDefault();
                the.submitFilter();
            });

            // handle filter cancel button click
            table.on('click', '.filter-cancel', function(e) {
                e.preventDefault();
                the.resetFilter();
            });
            
            table.on('click', 'tbody > tr', function(){
            	var checkBox = $(this).find("input");
            	ctlCheckBox(checkBox);
            });
            
            var ctlCheckBox = function(checkBox){
//            	if(options.showDetail) return;
            	if(checkBox.length == 0) return;
            	var div = checkBox[0].parentNode.parentNode;
            	var flag = $(div).hasClass("hover");
            	if(flag){
            		return;
            	}
            	if(!tableOptions.checkbox){
            		var allcheckBox = checkBox.closest('tbody').find("input");
            		for(var i=0;i<allcheckBox.length;i++){
            			var span = allcheckBox[i].parentNode;
                	    $(span).prop("class", "");
            	        $(checkBox).prop("checked", false);
            		}
            	}
        	    if(checkBox.length > 0){
        	    	if($(checkBox[0]).is(":checked")){
        	    		var span = checkBox[0].parentNode;
                	    $(span).prop("class", "");
            	        $(checkBox).prop("checked", false);
        	    	}else{
        	    		var span = checkBox[0].parentNode;
                	    $(span).prop("class", "checked");
            	        $(checkBox).prop("checked", true);
        	    	}
        	    }
            };
        },


        getRefresh: function(data, callBack, resetPaging){
            this.addPageInfo2Param(data);
            if(resetPaging){
                dataTable.ajax.reload(callBack, true);
            }else{
                dataTable.ajax.reload(callBack, false);
            }
        },

        submitFilter: function() {
            the.setAjaxParam("action", tableOptions.filterApplyAction);
            // get all typeable inputs
            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all checkboxes
            $('input.form-filter[type="checkbox"]:checked', table).each(function() {
                the.addAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all radio buttons
            $('input.form-filter[type="radio"]:checked', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });

            dataTable.ajax.reload();
        },

        resetFilter: function() {
            $('textarea.form-filter, select.form-filter, input.form-filter', table).each(function() {
                $(this).val("");
            });
            $('input.form-filter[type="checkbox"]', table).each(function() {
                $(this).prop("checked", false);
            });
            the.clearAjaxParams();
            the.addAjaxParam("action", tableOptions.filterCancelAction);
            dataTable.ajax.reload();
        },

        getSelectedRowsCount: function() {
            return $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        },

        getSelectedRows: function() {
            var rows = [];
            $('tbody > tr > td:nth-child(1) .checker input[type="checkbox"]:checked', table).each(function() {
                rows.push($(this).val());
            });
            if(tableOptions.showDetail){
            	$('tbody > tr > td:nth-child(2) .checker input[type="checkbox"]:checked', table).each(function() {
                    rows.push($(this).val());
                });
            }
            return rows;
        },

        setAjaxParam: function(value) {
            ajaxParams = value;
        },
        
        addPageInfo2Param: function(data){
        	$.each(data, function(key, value) {
        		ajaxParams.p[key] = value;
            });
        	//TODO
//        	var start = dataTable.context[0]._iDisplayStart;
//        	var length = dataTable.context[0]._iDisplayLength;
//        	ajaxParams.p.start = start;
//        	ajaxParams.p.length = length;
        },
        
        clearAjaxParams: function(name, value) {
            ajaxParams = {};
        },

        getDataTable: function() {
            return dataTable;
        },

        getTableWrapper: function() {
            return tableWrapper;
        },

        gettableContainer: function() {
            return tableContainer;
        },

        getTable: function() {
            return table;
        }

    };

};