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
* 计数器管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function CounterInfo() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members
	// }}}
	// {{{ functions
	// {{{ function init()
		
	/**
	 * 初始化  
	 */
	this.init = function() {
		$(document).ready(function() {
			__this.showListTable({
				id: 'counter_info_data',
				url: 'system/counterInfo/list',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'id'},	
					{'data': 'name'},	
					{'data': 'groupName'},	
					{'data': 'desc'},
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});

			$("#add_counter_info").on('click', function() {
				App.jumpPage('add_counter_info', false, false);
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
		    $("#projectId").chosen().change(function(e) {
                var projectId = $("#projectId option:selected").val()    
                __this.drawGroupList(projectId)
            })	
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
						required: true
						//input_name: true	
					},
                    groupId: {
                        required: true    
                    }
				},
				messages: {
					name: {
						required: '必须添加计数器名称.'	
					},	
					groupId: {
						required: '必须选择所属组.'	
					}	
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#add_form").valid()) {
					return;
				}
				var _formData =  $("#add_form").serialize();
				var _url = 'system/counterInfo/doadd';
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
								App.jumpPage('index_counter_info', false, false);	
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

            var groupId = $("#modGroupId").attr("action-data")
            var projectId = $("#projectId option:selected").val()    
            __this.drawGroupList(projectId, groupId)

			__this.validateForm({
				id: 'mod_form',
				rules: {
					name: {
						required: true	
					}
				},
				messages: {
					name: {
						required: '计数器组名称不能为空.'	
					}
				}
			});

			$("#form_submit").on('click', function() {
				if (!$("#mod_form").valid()) {
					return;	
				}
				var _formData =  $("#mod_form").serialize();
				var _url = 'system/counterInfo/domod';
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
								App.jumpPage('index_counter_info', false, false);	
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
			__this.dialogError('请选择要修改的计数器.');
			return;	
		}

		if (_tableChecked > 1) {
			__this.dialogError('只能修改一个计数器, 请勿多选.');
			return;	
		}

		var _infoId = $('input:checked').val();
		var _url = 'system/counterInfo/mod/' + _infoId;
		App.jumpPage(_url, true, false);
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _infoIds = [];
		$("#counter_info_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_infoIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_infoIds)) {
			var _infoIds = _infoIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的计数器.');	
			return;
		}

		var _url = 'system/counterInfo/dodel';
		var _data = 'ids=' + _infoIds;
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
                        App.jumpPage('index_counter_info', false, false);	
                    }, 1000);
                }
            }
		});
	}
	
	// }}}
    // {{{ function drawGroupList()

    /**
     * 绘制计数器组列表 
     */
    this.drawGroupList = function(projectId, groupId) {
		var _url = 'system/counterGroup/getListByProject/' + projectId;
		$.ajax ({
			type: "get",
			url : _url,
			dataType: "json",
			success: function (dataRes) {
				if (0 == dataRes.ret) {
                    var data = dataRes.data
                    var _html = []
                    _html.push('<select id="groupId" name="groupId" tabindex="-1" class="chosen span2" data-placeholder="选择所属组">')
                    _html.push('<option value=""></option>')
                    for(var i = 0; i < data.length; i++) {
                        _html.push('<option value="' + data[i].id + '"')
                        if (typeof(groupId) != "undefined" && groupId == data[i].id) {
                            _html.push(' selected >')    
                        } else {
                            _html.push('>')    
                        }
                        _html.push(data[i].name + '</option>')
                    }
                    _html.push('</select>')
                    if ($("#groupId")) {
                        $("#groupId").remove()    
                        $("#groupId_chzn").remove()    
                    }
                    $("#projectId").after(_html.join(''))
                    $("#groupId").chosen()
				}
			}
		});
    }

    // }}}
	// }}}
}
