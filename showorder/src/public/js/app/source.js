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
* 数据源管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function Source() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members
	// }}}
	// {{{ functions
	// {{{ function init()
		
	/**
	 * 初始化  
	 */
	this.init = function()
	{
		$(document).ready(function() {
			__this.showListTable({
				id: 'source_data',
				url: 'system/source/list',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.id + '" /></span></div>';
						return _html;
					},   "orderable": false},
                    {'data': 'id'},	
					{'data': 'name'},	
					{'data': 'description'},	
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});
			
			$("#add_source").on('click', function() {
				App.jumpPage('add_source');
			});
		});
	}
	
	// }}}
	// {{{ function initAdd()
		
	/**
	 * 初始化添加页面 
	 */
	this.initAdd = function()
	{
		$(document).ready(function() {
		    $("#projectId").chosen()	
			$("#form_reset").on('click', function () {
				$(":input").val('');	
			});

			$('.select2_category').select2({	
				placeholder: "Select an option",
				allowClear: true
			});

			__this.validateForm({
				id: 'add_form',
				rules: {
					name: {
						required: true,
						input_name: true	
					}
				},
				messages: {
					name: {
						required: '必须添加数据源名称.'	
					}	
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#add_form").valid()) {
					return;
				}
				var _formData =  $("#add_form").serialize();
				var _url = 'system/source/doadd';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData,
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.ret) {
							__this.alertError(dataRes.errors.join("<br>"), 1000);
						} else {
							__this.alertSuccess(dataRes.msg, 1000);
							setTimeout(function() {
								App.jumpPage('index_source');	
							}, 1000);
						}
					}
				});
			});
		});
	}
	
	// }}}
	// {{{ function initMod()
		
	/**
	 * 初始化修改页面 
	 */
	this.initMod = function()
	{
		$(document).ready(function() {
		    $("#projectId").chosen()	
			$("#form_reset").on('click', function () {
				$(":input").val('');	
			});	

			__this.validateForm({
				id: 'mod_form',
				rules: {
					name: {
						required: true	
					}
				},
				messages: {
					name: {
						required: '数据源名称不能为空.'	
					}
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#mod_form").valid()) {
					return;	
				}
				var _formData =  $("#mod_form").serialize();
				var _url = 'system/source/domod';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData,
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.ret) {
							__this.alertError(dataRes.errors.join("<br>"), 1000);
						} else {
							__this.alertSuccess(dataRes.msg, 1000);
							setTimeout(function() {
								App.jumpPage('index_source');	
							}, 1000);
						}
					}
				});
			});
		});
	}
	
	// }}}
	// {{{ function jumpMod()
		
	/**
	 * 跳到修改页面 
	 */
	this.jumpMod = function()
	{
		var _tableChecked = $('input:checked').length;
		if (_tableChecked == 0) {
			__this.dialogError('请选择要修改的数据源.');
			return;	
		}

		if (_tableChecked > 1) {
			__this.dialogError('只能修改一个数据源, 请勿多选.');
			return;	
		}

		var _sourceId = $('input:checked').val();
		var _url = 'system/source/mod/' + _sourceId;
		App.jumpPage(_url, true);
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _sourceIds = [];
		$("#source_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_sourceIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_sourceIds)) {
			var _sourceIds = _sourceIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的数据源.');	
			return;
		}

		var _url = 'system/source/dodel';
		var _data = 'ids=' + _sourceIds;
		$.ajax ({
			type: "post",
			url : _url,
			data: _data,
			dataType: "json",
            success: function (dataRes) {
                if (0 != dataRes.ret) {
                    __this.alertError(dataRes.errors.join("<br>"), 1000);
                } else {
                    __this.alertSuccess(dataRes.msg, 1000);
                    setTimeout(function() {
                        App.jumpPage('index_source');	
                    }, 1000);
                }
            }
		});
	}
	
	// }}}
	// }}}
}
