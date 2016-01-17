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
* NewReview 管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function NewReview() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members
	// }}}
	// {{{ functions
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
			var um = UM.getEditor('context');
			FormFileUpload.init();

			__this.validateForm({
				id: 'add_form',
				rules: {
					context: {
						required: true,
					},
					username: {
						required: true,
					}
				},
				messages: {
					context: {
						required: '必须添加评论信息.'	
					},
					username: {
						required: '必须添加用户名.'	
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
				var _url = 'newreview/doadd';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData + '&img=' + imgUrl.join('::') + "&context=" + encodeURIComponent(um.getContent()),
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.status) {
							__this.alertError(dataRes.msg, 1000);
						} else {
							__this.alertSuccess(dataRes.data.success, 1000);
							setTimeout(function() {
								var url = '/newreview/reviewlist?product_id=' + $("#product_id").val() + '&entity_id=' + $("#entity_id").val();
								App.jumpPage(url, true);
							}, 1000);
						}
					}
				});
			});
		});
	}
	
	// }}}
	// {{{ function init()
		
	/**
	 * 初始化  
	 */
	this.init = function()
	{
		$(document).ready(function() {
			__this.showListTable({
				id: 'newreview_data',
				url: '/newreview/dolist',
				columns: [ 
					{'data': 'entity_id'},
					{'data': 'value'},	
					{'data': function(obj) {
						var _html = '<td><a rel="' + obj.product_id + '" entity_id="' + obj.entity_id + '" href="javascript:void(0)" style="width:60px;" class="btn mini red-stripe">添加评论</a></td>';
						return _html;
					}, "orderable": false},
					{'data': function(obj) {
						var _html = '<td><a rel="' + obj.product_id + '" entity_id="' + obj.entity_id + '" href="javascript:void(0)" style="width:60px;" class="btn mini green-stripe">评论列表</a></td>';
						return _html;
					}, "orderable": false},
				],
			});
			
			$("#newreview_data a.green-stripe").die().live('click', function() {
                var url = '/newreview/reviewlist?product_id=' + $(this).attr('rel') + '&entity_id=' + $(this).attr('entity_id');
				App.jumpPage(url, true);
			});
			
			$("#newreview_data a.red-stripe").die().live('click', function() {
                var url = '/newreview/add?product_id=' + $(this).attr('rel') + '&entity_id=' + $(this).attr('entity_id');
				App.jumpPage(url, true);
			});
		});
	}
	
	// }}}
	// {{{ function initList()
		
	/**
	 * 初始化列表页 
	 */
	this.initList = function()
	{
		$(document).ready(function() {
			__this.showListTable({
				id: 'newreviewlist_data',
				url: '/newreview/doreviewlist?product_id=' + $('#product_id').val(),
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.review_id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'entity_id'},	
					{'data': 'product_id'},	
					{'data': 'context'},	
					{'data': 'username'},	
					{'data': 'create_time'},	
				],
				'editFn': __this.jumpMod,
				'delFn' : __this.doDel
			});
			$("#add_review").on('click', function() {
				App.jumpPage('add_review', false, false);
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
		console.info("debug");
		var _tableChecked = $('input:checked').length;
		if (_tableChecked == 0) {
			__this.dialogError('请选择要修改的评论.');
			return;	
		}

		if (_tableChecked > 1) {
			__this.dialogError('只能修改一个评论, 请勿多选.');
			return;	
		}

		var _groupId = $('input:checked').val();
		var _url = 'newreview/mod?review_id=' + _groupId;
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
		$("#newreviewlist_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_groupIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_groupIds)) {
			var _groupIds = _groupIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的评论.');	
			return;
		}

		var _url = 'newreview/dodel';
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
						var url = '/newreview/reviewlist?product_id=' + $("#product_id").val() + '&entity_id=' + $("#entity_id").val();
						App.jumpPage(url, true);
                    }, 1000);
                }
            }
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

			var um = UM.getEditor('context');
			$('.basic-toggle-button').toggleButtons();
			FormFileUpload.init();
			__this.validateForm({
				id: 'mod_form',
				rules: {
					context: {
						required: true,
					},
					username: {
						required: true,
					}
				},
				messages: {
					context: {
						required: '必须添加评论信息.'	
					},
					username: {
						required: '必须添加用户名.'	
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
					var thumpic = $($(imgdata[i]).find('img')[0]).attr('src');
					imgUrl.push(imgdata[i].href + '|' + thumpic);	
				}
				var _formData =  $("#mod_form").serialize();
				var _url = 'newreview/domod';
				$.ajax ({
					type: "post",
					url : _url,
					data: _formData + '&img=' + imgUrl.join('::') + "&context=" + encodeURIComponent(um.getContent()),
					dataType: "json",
					success: function (dataRes) {
						if (0 != dataRes.status) {
							__this.alertError(dataRes.msg, 1000);
						} else {
							__this.alertSuccess(dataRes.data.success, 1000);
							setTimeout(function() {
								var url = '/newreview/reviewlist?product_id=' + $("#product_id").val() + '&entity_id=' + $("#entity_id").val();
								App.jumpPage(url, true);
							}, 1000);
						}
					}
				});
			});

		});
	}
	
	// }}}
	// }}}
}
