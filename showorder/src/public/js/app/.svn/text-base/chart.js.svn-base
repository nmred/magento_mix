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
* 图表管理
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function Chart() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members

	/**
	 *  运算公式对应表 
	 */
	this.__cfMap = {
		1: '平均值',
		2: '最小值',
		3: '最大值',
		4: 'LAST'
	};

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
				id: 'chart_data',
				url: 'index.php?target=chart&action=get',
				columns: [ 
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.chart_id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'name'},	
					{'data': 'project_name'},	
					{'data': 'title'},	
					{'data': 'sub_title'},	
					{'data': 'y_title'},	
					{'data': 'unit'},	
					{'data': function (obj) {
						return '<a class="btn red mini" href="javascript:;" action-data="' + obj.chart_id + '" onclick="' + __this.__thisName +'.showDetail(this)">查看</a>';			
					}}	
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});
			
			$("#add_chart").on('click', function() {
				App.jumpPage('add_chart');
			});
		});
	}
	
	// }}}
	// {{{ function showDetail()
		
	/**
	 * 显示详细信息 
	 */
	this.showDetail = function(obj)
	{
		var _objTr = $(obj).parent().parent();
		var _detailTr = $("#detail_id");
		var _createTable = false;

		if (!_detailTr.length) {
			_createTable = true;	
		} else {
			if (_objTr.next().attr('id') == "detail_id") {
				_createTable = false;
				$(obj).html('查看');
			} else {
				_detailTr.prev().find('a').html('查看');
				_createTable = true;
			}
			_detailTr.remove();	
		}

		if (_createTable) {
			var _actionData = $(obj).attr('action-data');
			if (!_actionData) {
				return;	
			}
			var _url = 'index.php?target=chart&action=getdetail';
			var _data = 'chart_id=' + _actionData;
			$.ajax ({
				type: "post",
				url : _url,
				data: _data,
				dataType: "json",
				success: function (dataRes) {
					if (10000 != dataRes.code) {
						__this.alertError(dataRes.msg, 1000);
					} else {
						var _html = [];
						_html.push('<tr id="detail_id"><td class="details" colspan="10"><table class="details">');
						for (var i = 0, len = dataRes.data.length; i < len; i++) {
							_html.push('<tr>');
							_html.push('<td>数据项名称:</td>');
							_html.push('<td>' + dataRes.data[i].metric_name + '</td>');
							_html.push('<td>绘制图标类型:</td>');
							_html.push('<td>' + dataRes.data[i].display + '</td>');
							_html.push('<td>数据项显示名:</td>');
							_html.push('<td>' + dataRes.data[i].display_name + '</td>');
							_html.push('</tr>');
						}
						_html.push('</table></td></tr>');
						_objTr.after(_html.join(''));
						$(obj).html('关闭');
					}
				}
			});

		}

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
			$("#chart_metric").hide();

			// {{{ validateForm
			__this.validateForm({
				id: 'add_form',
				rules: {
					name: {
						required: true,
						input_name: true	
					},
					display_name: {
						required: true
					},
					project_id: {
						required: true
					},
					store_type: {
						required: true
					},
					steps: {
						required: true
					},
					heartbeat: {
						required: true,
						number: true	
					},
					rra_type: {
						required: true
					}
				},
				messages: {
					name: {
						required: '数据名称不能为空.'	
					},	
					display_name: {
						required: '数据显示名不能为空.'	
					},	
					project_id: {
						required: '数据所属项目不能为空.'	
					},	
					store_type: {
						required: '数据存储类型不能为空.'	
					},	
					steps: {
						required: '数据存储间隔不能为空.'	
					},	
					heartbeat: {
						required: '数据有效性不能为空.',	
						number: '数据有效性必须是数字.'
					},	
					rra_type: {
						required: '数据合并规则不能为空.'	
					}
				}
			});

			// }}}

			$("#project_id").select2();
			$("#store_type").select2();
			$("#project_id").change(function () {
				$("#chart_metric").hide();
				$("#chart_metric").show();
			});

			$("#form_submit").on('click', function() {
				if ($("#add_form").valid()) {
					var _formData =  $("#add_form").serialize();
					var _rra_type = $("input[name='rra_type']").val();
					_formData += '&rra_type=' + _rra_type;
					var _url = 'index.php?target=metric&action=doadd';
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
									App.jumpPage('index_metric');	
								}, 1000);
							}
						}
					});
				}
			});

			__this._addMetric();
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

			// {{{ validateForm
			__this.validateForm({
				id: 'mod_form',
				rules: {
					display_name: {
						required: true
					},
					project_id: {
						required: true
					},
					store_type: {
						required: true
					},
					steps: {
						required: true
					},
					heartbeat: {
						required: true,
						number: true	
					},
					rra_type: {
						required: true
					}
				},
				messages: {
					display_name: {
						required: '数据显示名不能为空.'	
					},	
					project_id: {
						required: '数据所属项目不能为空.'	
					},	
					store_type: {
						required: '数据存储类型不能为空.'	
					},	
					steps: {
						required: '数据存储间隔不能为空.'	
					},	
					heartbeat: {
						required: '数据有效性不能为空.',	
						number: '数据有效性必须是数字.'
					},	
					rra_type: {
						required: '数据合并规则不能为空.'	
					}
				}
			});

			// }}}

			$("#project_id").select2();
			$("#store_type").select2();

			$("#form_submit").on('click', function() {
				if ($("#mod_form").valid()) {
					var _formData =  $("#mod_form").serialize();
					var _rra_type = $("input[name='rra_type']").val();
					_formData += '&rra_type=' + _rra_type;
					var _url = 'index.php?target=metric&action=domod';
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
									App.jumpPage('index_metric');	
								}, 1000);
							}
						}
					});
				}
			});

			__this._checkRra();
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
			__this.dialogError('请选择要修改的数据.');
			return;	
		}

		if (_tableChecked > 1) {
			__this.dialogError('只能修改一个数据, 请勿多选.');
			return;	
		}

		var _metricId = $('input:checked').val();
		var _url = 'index.php?target=metric&action=mod&metric_id=' + _metricId;
		App.jumpPage(_url, true);
	}
	
	// }}}
	// {{{ function doDel()
		
	/**
	 * 跳到删除页面 
	 */
	this.doDel = function()
	{
		var _metricIds = [];
		$("#metric_data_wrapper .checkboxes").each(function() {
			var _checked = $(this).is(':checked');	
			if (_checked) {
				_metricIds.push($(this).val());	
			}
		});
		if (!$.isEmptyObject(_metricIds)) {
			var _metricIds = _metricIds.join(',');	
		} else {
			__this.dialogError('请选择要删除的数据.');	
			return;
		}

		var _url = 'index.php?target=metric&action=dodel';
		var _data = 'metric_id=' + _metricIds;
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
						App.jumpPage('index_metric');	
					}, 1000);
				}
			}
		});
	}
	
	// }}}
	// {{{ function _addMetric()
		
	/**
	 * 添加数据项 
	 */
	this._addMetric = function()
	{
		$("#add_metric").on('click', function() {
			var _html = [];
			_html.push('<div class="form span6">');
			_html.push('<form class="form-horizontal form-bordered form-row-stripped" id="add_metric_form" action="#" novalidate="novalidate">');
			_html.push('<div class="control-group">');
			_html.push('<label class="control-label">数据名称<span class="required">*</span></label>');
			_html.push('<div class="controls">');
			var _url = 'index.php?target=metric&action=getbyproject';
			$.ajax ({
				type: "post",
				url : _url,
				async: false,
				data: $("#add_form").serialize(),
				dataType: "json",
				success: function (dataRes) {
					if (10000 != dataRes.code) {
						__this.alertError(dataRes.msg, 1000);
					} else {
						_html.push('<select class="span2" tabindex="-1" data-placeholder="请选择一个数据" id="select_metric" name="metric_id">');	
						for(var i = 0, len = dataRes.data.length; i < len; i++) {
							_html.push('<option value="' + dataRes.data[i].metric_id + '">' + dataRes.data[i].display_name + '</option>');
						}
						_html.push('</select>');
					}
				}
			});
			_html.push('<span for="metric_id" class="help-inline"></span> </div></div>');
			_html.push('<div class="control-group"><label name="desc" class="control-label">显示数据类型</label>');
			_html.push('<div class="controls">');
			_html.push('<label class="checkbox line"><div class="checker"><span class="checked"><input type="checkbox" value="8" name="display"></span></div>最大值</label>');
			_html.push('<label class="checkbox line"><div class="checker"><span class="checked"><input type="checkbox" value="4" name="display"></span></div>平均值</label>');
			_html.push('<label class="checkbox line"><div class="checker"><span class="checked"><input type="checkbox" value="2" name="display"></span></div>最小值</label>');
			_html.push('<span for="metric_id" class="help-inline"></span> </div></div>');
			_html.push('<div class="form-actions"> <button class="btn blue" id="form_metric_submit" type="button">提交</button> <button class="btn" id="form_metric_reset" type="button">重置</button> </div></form></div>');
			var _boxy = new Boxy(_html.join(''), {title: '添加显示数据', top:0});	
			$("#select_metric").select2();
		});
	}
	
	// }}}
	// }}}
}
