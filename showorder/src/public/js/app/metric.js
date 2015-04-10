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
* 数据管理
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

function Metric() {
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
		var _storeTypeMap =
		{
			1: 'GAUGE',
			2: 'COUNTER',
			3: 'DERIVE',
			4: 'ABSOLUTE'
		};
		$(document).ready(function() {
			__this.showListTable({
				id: 'metric_data',
				url: 'index.php?target=metric&action=get',
				columns: [
					{'data': function(obj) {
						var _html = '<div class="checker"><span class=""><input type="checkbox" class="checkboxes" value="' + obj.metric_id + '" /></span></div>';
						return _html;
					},   "orderable": false},
					{'data': 'name'},
					{'data': 'project_display_name'},
					{'data': 'display_name'},
					{'data': 'desc'},
					{'data': 'heartbeat'},
					{'data': 'steps'},
					{'data': 'start_time'},
					{'data': function (obj) {
						return _storeTypeMap[obj.store_type];
					}},
					{'data': function (obj) {
						return '<a class="btn red mini" href="javascript:;" action-data="' + obj.rra_type + '" onclick="' + __this.__thisName +'.showRra(this)">查看</a>';
					}}
				],
				editFn: __this.jumpMod,
				delFn: __this.doDel
			});

			$("#add_metric").on('click', function() {
				App.jumpPage('add_metric');
			});
		});
	}

	// }}}
	// {{{ function showRra()

	/**
	 * 显示 rra 规则
	 */
	this.showRra = function(obj)
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
			var _url = 'index.php?target=metric&action=getrra';
			var _data = 'rra_type=' + _actionData;
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
							_html.push('<td>运算公式:</td>');
							_html.push('<td>' + __this.__cfMap[dataRes.data[i].cf] + '</td>');
							_html.push('<td>数据总数:</td>');
							_html.push('<td>' + dataRes.data[i].rows + '</td>');
							_html.push('<td>合并基数:</td>');
							_html.push('<td>' + dataRes.data[i].steps + '</td>');
							_html.push('<td>数据有效因子:</td>');
							_html.push('<td>' + dataRes.data[i].xff + '</td>');
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

			__this._checkRra();
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
	// {{{ function _checkRra()

	/**
	 * 选择合并规则
	 */
	this._checkRra = function()
	{
		$("#select_rra").on('click', function() {
			var _checkeData = $("input[name='rra_type']").val().split(',');
			__this.callBoxy('rra', {
				checkType: 1, // 1: checkbox 2: radio
				checkData: _checkeData, // 选中的id
				width: '500px', // 选中的id
				buttons: [
					{
						title: '确定',
						fn: function(data) {
							var _rraIds = [];
							var _html = [];	
							_html.push('<div class="controls" id="rra_detail"><table class="table table-striped table-hover"><thead>');
							_html.push('<tr><th>计算规则</th><th>总记录数</th><th>合并行数</th><th>操作</th></tr>');
                            _html.push('<tbody>');
							$("#rra_boxy_wrapper .checkboxes").each(function() {
								var _checked = $(this).is(':checked');
								if (_checked) {
									_rraIds.push($(this).val());
									_html.push('<tr>');
									$(this).parent().parent().parent().siblings().each(function() {
										_html.push('<td>' + $(this).html() + '</td>');
									});
									_html.push('<td><span class="btn mini green-stripe" onclick="' + __this.__thisName + '.delRra(this)" id="' + $(this).val() + '">删除</span></td>')
									_html.push('</tr>');
								}
							});
							_html.push('</tbody></table></div>');
							
							$(".boxy-wrapper").remove();
							if (!_rraIds.length) {
								return;
							}

							$("#rra_detail").remove();
							$('#select_rra').parent().after(_html.join(''));
							$("input[name='rra_type']").val(_rraIds.join(','));
						}
					},
					{
						title: '关闭',
						fn: function() {
							$(".boxy-wrapper").remove();
						}
					},
				]
			});
		});
	}

	// }}}
	// {{{ function delRra()

	/**
	 * 删除所选 rra
	 */
	this.delRra = function(obj)
	{
		var _result = [];
		var _rraTypes = $("input[name='rra_type']").val();
		_rraTypes = _rraTypes.split(',');
		for (var i = 0, len = _rraTypes.length; i < len; i++) {
			if (obj.id != _rraTypes[i]) {
				_result.push(_rraTypes[i]);
			}
		}	
	
		$("input[name='rra_type']").val(_result.join(','));	
		$(obj).parent().parent().remove();
	}

	// }}}
	// }}}
}
