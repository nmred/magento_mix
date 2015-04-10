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
* 项目管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function Project() {
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
				id: 'project_data',
				url: 'system/project/list',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'name'},	
					{'data': 'display_name'},	
					{'data': 'group_name'},	
					{'data': 'description'},	
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});
			
			$("#add_project").on('click', function() {
				App.jumpPage('add_project');
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
					},
					displayName: {
						required: true	
					}
				},
				messages: {
					name: {
						required: '必须添加项目名称.'	
					},	
					displayName: {
						required: '必须添加项目显示名.'	
					}	
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#add_form").valid()) {
					return;
				}
				var _formData =  $("#add_form").serialize();
				var _url = 'system/project/doadd';
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
								App.jumpPage('index_project');	
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
			$("#form_reset").on('click', function () {
				$(":input").val('');	
			});	

			__this.validateForm({
				id: 'mod_form',
				rules: {
					displayName: {
						required: true	
					}
				},
				messages: {
					displayName: {
						required: '项目显示名不能为空.'	
					}	
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#mod_form").valid()) {
					return;	
				}
				var _formData =  $("#mod_form").serialize();
				var _url = 'system/project/domod';
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
								App.jumpPage('index_project');	
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
			__this.dialogError('请选择要修改的项目组.');
			return;	
		}

		if (_tableChecked > 1) {
			__this.dialogError('只能修改一个项目组, 请勿多选.');
			return;	
		}

		var _projectId = $('input:checked').val();
		var _url = 'system/project/mod/' + _projectId;
		App.jumpPage(_url, true);
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _projectIds = [];
		$("#project_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_projectIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_projectIds)) {
			var _projectIds = _projectIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的项目.');	
			return;
		}

		var _url = 'system/project/dodel';
		var _data = 'ids=' + _projectIds;
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
                        App.jumpPage('index_project');	
                    }, 1000);
                }
            }
		});
	}
	
	// }}}
	// }}}
}
