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
* 合并管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function Rra() {
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
				id: 'rra_data',
				url: 'index.php?target=rra&action=get',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.rra_id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'cf'},	
					{'data': 'rows'},	
					{'data': 'steps'},	
					{'data': 'xff'},	
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});
			
			$("#add_rra").on('click', function() {
				App.jumpPage('add_rra');
			});

			$("#del_rra").on('click', function() {
				__this.doDel();
			});
		});
	}
	
	// }}}
	// {{{ function initBoxy()
		
	/**
	 * 初始化  
	 */
	this.initBoxy = function()
	{
		$(document).ready(function() {
			__this.showListTable({
				id: 'rra_boxy',
				url: 'index.php?target=rra&action=get',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.rra_id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'cf'},	
					{'data': 'rows'},	
					{'data': 'steps'},	
					{'data': 'xff'},	
				]
			});

			$("#rra_boxy_close").on('click', function() {
				$(".boxy-wrapper").remove();	
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
					steps: {
						required: true,
						number: true	
					},
					rows: {
						required: true,
						number: true	
					},
					cf: {
						required: true,
						number: true	
					},
					xff: {
						xff_check: true	
					}
				},
				messages: {
					steps: {
						required: '必须添加合并行数.',	
						number: '请输入一个数字.'	
					},	
					rows: {
						required: '必须添加总记录数.',	
						number: '请输入一个数字.'	
					},	
					cf: {
						required: '必须选择计算规则.'	
					},	
					xff: {
						number: '请输入一个数字[0-1].'	
					}	
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#add_form").valid()) {
					return;
				}
				var _formData =  $("#add_form").serialize();
				var _url = 'index.php?target=rra&action=doadd';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData,
					dataType: "json",
					success: function (dataRes) {
						if (10000 != dataRes.code) {
							__this.alertError(dataRes.msg, 1000);
						} else {
							__this.alertSuccess(dataRes.msg, 1000);
							setTimeout(function() {
								App.jumpPage('index_rra');	
							}, 1000);
						}
					}
				});
			});
		});
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _rraIds = [];
		$("#rra_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_rraIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_rraIds)) {
			var _rraIds = _rraIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的合并规则.');	
			return;
		}

		var _url = 'index.php?target=rra&action=dodel';
		var _data = 'rra_id=' + _rraIds;
		$.ajax ({
			type: "post",
			url : _url,
			data: _data,
			dataType: "json",
			success: function (dataRes) {
				if (10000 != dataRes.code) {
					__this.alertError(dataRes.msg, 1000);
				} else {
					__this.alertSuccess(dataRes.msg, 1000);
					setTimeout(function() {
						App.jumpPage('index_rra');	
					}, 1000);
				}
			}
		});
	}
	
	// }}}
	// }}}
}
