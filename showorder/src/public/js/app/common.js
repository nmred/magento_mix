/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------

/**
+------------------------------------------------------------------------------
* 管理端基本库 :各个模块操作的基类
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

function ModuleBase()
{
	var __this = this;
	// {{{ members

	/**
	 * this 对象在外部的名字
	 *
	 * @type {String}
	 */
	this.__thisName = 'this';

	// }}}
	// {{{ functions
	// {{{ function setThisName()

	/**
	 * 设置this对象在外部的名字
	 *
	 * @param {String} thisName
	 * @return {Void}
	 */
	this.setThisName = function (thisName)
	{
		__this.__thisName = thisName;

		//这里可以放一些每个页面都要执行的逻辑
	}

	// }}}
	// {{{ function alertSuccess()

	/**
	 * 成功提示
	 *
	 * @param {String} message
	 * @return {Void}
	 */
	this.alertSuccess = function (message, timeOut)
	{
        if (typeof(timeOut) == "undefined") {
            timeOut = 2000    
        }
		var _html = [];
		if ($("#alert-success-id").length) {
			$("#alert-success-id").remove();
		}
		_html.push('<div id="alert-success-id" class="alert alert-success" style="position: absolute; width: 79%;z-index: 2;">');
		_html.push('<button data-dismiss="alert" class="close"></button>');
		_html.push('<strong>Success!</strong> ' + message);

		_html.push('</div>');
		$(".page-content-body").prepend(_html.join(''));
		setTimeout(function() {
			$("#alert-success-id").remove();
		}, timeOut);
	}

	// }}}
	// {{{ function alertError()

	/**
	 * 错误提示
	 *
	 * @param {String} message
	 * @return {Void}
	 */
	this.alertError = function (message, timeOut)
	{
        if (typeof(timeOut) == "undefined") {
            timeOut = 2000    
        }
		var _html = [];
		if ($("#alert-error-id").length) {
			$("#alert-error-id").remove();
		}
		_html.push('<div id="alert-error-id" class="alert alert-error" style="position: absolute; width: 79%;z-index: 2;">');
		_html.push('<button data-dismiss="alert" class="close"></button>');
		_html.push('<strong>Error!</strong> ' + message);

		_html.push('</div>');
		$(".page-content-body").prepend(_html.join(''));
		setTimeout(function() {
			$("#alert-error-id").remove();
		}, timeOut);
	}

	// }}}
	// {{{ function validateForm()

	/**
	 * 验证表单
	 *
	 * @param {Object} options
	 * @return {Void}
	 */
	this.validateForm = function (options)
	{
		// 验证函数出去官方默认的和去 app.js 的initHandle 中定义
		var _options = {
			id: null,
			rules: null,
			messages: null,
			submit: null
		};

		$.each(_options, function (key, val) {
			if ("undefined" != typeof options[key]) {
				_options[key] = options[key];	
			}
		});

        var _form = $('#' + _options.id);
        var _error = $('.alert-error', _form);
        var _success = $('.alert-success', _form);

        _form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-inline', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: _options.rules,
			messages: _options.messages,

            highlight: function (element) { 
                $(element).closest('.help-inline').removeClass('ok');
                $(element).closest('.control-group').removeClass('success').addClass('error');
            },

            unhighlight: function (element) {
                $(element).closest('.control-group').removeClass('error');
            },

            success: function (label) {
                label.addClass('valid').addClass('help-inline ok').closest('.control-group').removeClass('error').addClass('success');
            }
        });
	}

	// }}}
	// {{{ function callBoxy()

	/**
	 * 呼叫弹层
	 *
	 * @param {Object} options
	 * @return {Void}
	 */
	this.callBoxy = function (name, options)
	{
		var _mapAllow = ['rra'];
		var _columns = {
			rra: ['计算规则', '总记录数', '合并行数', '有效因子']
		};

		if (-1 == $.inArray(name, _mapAllow)) {
			return;
		}

		var _options = {
			title: '选择框',
			width: 'auto',
			checkType: 1,
			checkData: [],
			buttons: []
		};

		$.each(_options, function (key, val) {
			if ("undefined" != typeof options[key]) {
				_options[key] = options[key];	
			}
		});

		var _html = [];
		var _boxyTableId = name + '_boxy';
		var _boxyDivId = name + '_boxy_div';
		_html.push('<div class="row-fluid" style="width:' + _options.width + '" id="' + _boxyDivId + '"><div class="span12"><div class="portlet box blue"><div class="portlet-body">');
        _html.push('<table class="table table-striped table-hover table-bordered" id="' + _boxyTableId + '">');
        _html.push('<thead><tr>');
        _html.push('<th style="width:8px;">');
		if (_options.checkType == 1) {
			_html.push('<div class="checker"><span><input type="checkbox" class="group-checkable" data-set="#' + _boxyTableId + ' .checkboxes" /></span></div>');
		}
		_html.push('</th>');
		for (var i = 0, len = _columns[name].length; i < len; i++) {
			_html.push('<th>' + _columns[name][i] + '</th>')
		}
        _html.push('</tr></thead></table>');
		_html.push('</div></div></div></div>');
		$(".boxy-wrapper").remove();
		var _boxy = new Boxy(_html.join(''), {title:_options.title});

		// 动态获取数据
		__this.showListTable({
			id: _boxyTableId,
			url: 'index.php?target=' + name + '&action=get',
			columns: __this._getBoxyColumndef(name),
			checkData: _options.checkData
		});

		_html = [];
		if (_options.buttons.length > 0) {
			_html.push('<div class="portlet-title gray"> <div class="actions">');
			for (var i = 0, len = _options.buttons.length; i < len; i++) {
				var _btnId = _boxyTableId + '_btn_' + i;
				_html.push('<a action="" id="' + _btnId + '" class="btn green">');	
				_html.push('<i class="icon-pencil"></i>');
				_html.push(_options.buttons[i].title);
				_html.push('</a> ');
			}
			_html.push('</div></div>');	
		}	

		$("#" + _boxyDivId + " .portlet-body").after(_html.join(''));
		if (_options.buttons.length > 0) {
			for (var i = 0, len = _options.buttons.length; i < len; i++) {
				var _btnId = _boxyTableId + '_btn_' + i;
				$("#" + _btnId).on('click', _options.buttons[i].fn);
			}
		}	
	}

	// }}}
	// {{{ function _getBoxyColumndef()
		
	/**
	 * 获取 boxy 表定义 
	 */
	this._getBoxyColumndef = function(name)
	{
		var _columns = {
			rra:[
				{'data': function(obj) {
					var _html = '<div class="checker"><span class=""><input type="checkbox" id="check_id_' + obj.rra_id + '" class="checkboxes" value="' + obj.rra_id + '" /></span></div>';                        return _html;
				},   "orderable": false},
				{'data': 'cf'}, 
				{'data': 'rows'},   
				{'data': 'steps'},  
				{'data': 'xff'}
			]
		};

		return _columns[name];
	}
	
	// }}}
	// {{{ function dialogError()

	/**
	 * 错误弹框
	 *
	 * @param {String} message
	 * @return {Void}
	 */
	this.dialogError = function (message, timeOut)
	{
        if (typeof(timeOut) == "undefined") {
            timeOut = 2000    
        }
		var _html = [];
		_html.push('<div id="dialogError" class="modal hide fade in" tabindex="-1" role="dialog" aria-labelledby="dialogError" aria-hidden="false" style="display:block">');
		_html.push('<div class="modal-header">');
		_html.push('	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>');
		_html.push('	<h3 id="myModalLabel2">提示</h3>');
		_html.push('</div>');
		_html.push('<div class="modal-body">');
		_html.push('	<p>' + message + '</p>');
		_html.push('</div>');
		_html.push('<div class="modal-footer">');
		_html.push('	<button data-dismiss="modal" class="btn green">确定</button>');
		_html.push('</div>');
		_html.push('</div><div class="modal-backdrop fade in"></div>');

		$(".page-content-body").prepend(_html.join(''));
		setTimeout(function() {
			$("#dialogError").remove();
			$(".modal-backdrop").remove();
		}, timeOut);
	}

	// }}}
	// {{{ function showListTable()

	/**
	 * 显示列表
	 *
	 * @param {Object} options
	 * @return {Void}
	 */
	this.showListTable = function (options)
	{
		var _dataTable = {
			id: null,
			ordering: false,
			serverSide: true,	
			url: null,
			checkData: [],
			columns: null,
			editFn: null,
			delFn: null
		};

		$.each(_dataTable, function (key, val) {
			if ("undefined" != typeof options[key]) {
				_dataTable[key] = options[key];	
			}
		});

		$('#' + _dataTable.id).dataTable( {
			serverSide: _dataTable.serverSide,
			ordering: _dataTable.ordering,
			ajax: {
				url: _dataTable.url,
				type: 'POST'
			},
			columns: _dataTable.columns
		});

		$('#' + _dataTable.id + '_wrapper .dataTables_filter input').addClass("m-wrap small"); 
		$('#' + _dataTable.id + '_wrapper .dataTables_length select').addClass("m-wrap small");
		$('#' + _dataTable.id + '_wrapper .dataTables_length select').select2();
		
		// 预置已选中的行
		$('#' + _dataTable.id).on('draw.dt', function() {
			if (_dataTable.checkData.length > 0) {
				for (var i = 0, len = _dataTable.checkData.length; i < len; i++) {
					var _checkObj = $("#check_id_" + _dataTable.checkData[i]);
					_checkObj.attr("checked", true);
					_checkObj.parent().attr('class', "checked");
				}
			}
		});

		$('#' + _dataTable.id + '_wrapper .group-checkable').change(function () {
            var set = $(this).attr("data-set");
            var checked = $(this).is(":checked");
            $(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
					$(this).parent().attr('class', "checked");
                } else {
                    $(this).attr("checked", false);
					$(this).parent().attr('class', "");
                }
				__this._checkEditEvent(_dataTable.editFn);
				__this._checkDelEvent(_dataTable.delFn);
            });
            $.uniform.update(set);
        });

		$('#' + _dataTable.id + '_wrapper .checkboxes').live('change', function() {
            var checked = $(this).is(":checked");
            if (checked) {
                $(this).attr("checked", true);
				$(this).parent().attr('class', "checked");
            } else {
                $(this).attr("checked", false);
				$(this).parent().attr('class', "");
            }
			__this._checkEditEvent(_dataTable.editFn);
			__this._checkDelEvent(_dataTable.delFn);
		});
	}

	// }}}
	// {{{ function _checkEditEvent()
		
	/**
	 * 检查是否允许触发修改事件 
	 */
	this._checkEditEvent = function(func)
	{
		var _tableChecked = $('input:checked').length;
		var _modMenu = $("#mod_menu");
		if (_tableChecked != 1) {
			_modMenu.css('color', '#ccc');
			_modMenu.unbind('click');
		} else {
			_modMenu.unbind('click');
			_modMenu.on('click', func);
			_modMenu.css('color', '');
		}
	}
	
	// }}}
	// {{{ function _checkDelEvent()
		
	/**
	 * 检查是否允许触发删除事件 
	 */
	this._checkDelEvent = function(func)
	{
		var _tableChecked = $('td > div > span > input:checked').length;
		var _delMenu = $("#del_menu");
		if (_tableChecked == 0) {
			_delMenu.css('color', '#ccc');
			_delMenu.unbind('click');
		} else {
			_delMenu.unbind('click');
			_delMenu.on('click', func);
			_delMenu.css('color', '');
		}
	}
	
	// }}}
	// }}}
}

