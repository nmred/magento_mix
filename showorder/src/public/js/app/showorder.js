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
* 晒单管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function ShowOrder() {
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
				'id': 'showorder_data',
				'url' : 'showorder/dolist',
				'columns': [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'title'},	
					{'data': 'comment'},	
				],
				'editFn': __this.jumpMod,
				'delFn' : __this.doDel
			});
			
			$("#add_showorder").on('click', function() {
				console.info("dede")
				App.jumpPage('add_showorder', false, false);
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

			$('.basic-toggle-button').toggleButtons();
			var um = UM.getEditor('description');
			FormFileUpload.init();

			__this.validateForm({
				id: 'add_form',
				rules: {
					title: {
						required: true,
					}
				},
				messages: {
					name: {
						required: '必须添加标题.'	
					}
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#add_form").valid()) {
					return;
				}

				var imgUrl = []
				var imgdata = $(".fancybox-button");
				for (var i = 0; i < imgdata.length; i++) {
					imgUrl.push(imgdata[i].href);	
				}
				var _formData =  $("#add_form").serialize();
				var _url = 'showorder/doadd';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData + '&img=' + imgUrl.join('::') + "&description=" + encodeURIComponent(um.getContent()),
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.status) {
							__this.alertError(dataRes.msg, 1000);
						} else {
							__this.alertSuccess(dataRes.data.success, 1000);
							setTimeout(function() {
								App.jumpPage('showorder_list', false, false);	
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

			var um = UM.getEditor('description');
			$('.basic-toggle-button').toggleButtons();
			FormFileUpload.init();
			__this.validateForm({
				id: 'mod_form',
				rules: {
					title: {
						required: true,
					}
				},
				messages: {
					name: {
						required: '必须添加标题.'	
					}
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#mod_form").valid()) {
					return;	
				}
				var imgUrl = []
				var imgdata = $(".fancybox-button");
				for (var i = 0; i < imgdata.length; i++) {
					imgUrl.push(imgdata[i].href);	
				}
				var _formData =  $("#mod_form").serialize();
				var _url = 'showorder/domod';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData + '&img=' + imgUrl.join('::') + "&description=" + encodeURIComponent(um.getContent()),
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.status) {
							__this.alertError(dataRes.msg, 1000);
						} else {
							__this.alertSuccess(dataRes.data.success, 1000);
							setTimeout(function() {
								App.jumpPage('showorder_list', false, false);	
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

		var _groupId = $('input:checked').val();
		var _url = 'showorder/mod?id=' + _groupId;
		App.jumpPage(_url, true, false);
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _groupIds = [];
		$("#showorder_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_groupIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_groupIds)) {
			var _groupIds = _groupIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的晒单.');	
			return;
		}

		var _url = 'showorder/dodel';
		var _data = 'ids=' + _groupIds;
		$.ajax ({
			type: "post",
			url : _url,
			data: _data,
			dataType: "json",
            success: function (dataRes) {
                if (0 != dataRes.status) {
                    __this.alertError(dataRes.msg, 1000);
                } else {
                    __this.alertSuccess(dataRes.data.success, 1000);
                    setTimeout(function() {
                        App.jumpPage('showorder_list', false, false);	
                    }, 1000);
                }
            }
		});
	}
	
	// }}}
	// }}}
}
