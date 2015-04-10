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
* 绘制图表
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function ChartView() {
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
			for (var i in wbChartData) {
				cardTypes = wbChartData[i].card_type;
				for (var j = 0; j < cardTypes.length; j++) {
					$("#" + cardTypes[j].card_id).bind("click", function () {
						var _cardId = $(this).attr('id');
						$("#chart_" + _cardId).attr('class', 'tab-pane active');
						var _siblingObjs = $("#chart_" + _cardId).siblings();
						for(var i = 0; i < _siblingObjs.length; i++) {
							$(_siblingObjs[i]).attr('class', 'tab-pane');
						}
						__this.getChart(_cardId);
					});
					if (j == 0) {
						__this.getChart(cardTypes[j].card_id);
					}
				}
			}
		});	
	}
	
	// }}}
	// {{{ function getChart()

    /**
     * 获取一个图标
     *
     * @param {String} cardId
     * @return {Void}  
     */
	this.getChart = function (cardId)
	{
		var _params = cardId.split('_');
		var _chartId = _params[1];
		var _cardTypeId = _params[2];
		var _cardInfo = wbChartData[_chartId].card_type[_cardTypeId];
		var _chartParams = wbChartData[_chartId].metrics;
		var _typeMap = {
			2:'平均值',
			4:'最小值',
			8:'最大值',
			16:'最后值'
		};

		var _chartTypeMap = {
			1:'line',
			2:'area',
		};

		var _metricNames = [];
		var _displays = [];
		for (var _metricName in _chartParams) {
			_metricNames.push(_metricName);	
			_displays.push(_chartParams[_metricName].display);
		}
		_metricNames = _metricNames.join(',');
		_displays = _displays.join(',');

		var _url = '?target=charts&action=fetch&project_name=' + wbChartData[_chartId].project_name + '&metric_name=' + _metricNames + '&interval=' + _cardInfo.interval + '&total=' + _cardInfo.total + '&display=' + _displays + '&default=0.1';
		$.ajax ({
			type: "post",
			url : _url,
			dataType: "json",
			async: false,
			success: function (dataRes) {
				if (10000 != dataRes.code) {
					__this.drawNoDataChart(cardId);
					__this.alertError(dataRes.msg, 10000);
				} else {
					var _data = [];
					for (var _metricName in dataRes.data) {
						for (var _type in dataRes.data[_metricName]) {
							var _item = {};
							var _tmpData = dataRes.data[_metricName][_type];
							var i = 0;
							var _valueData = [];
							for (var _time in _tmpData) {
								_valueData.push(_tmpData[_time]);
								if (i <= 1) {
									_item.pointStart = _time * 1000;
								}
								i++;
							}	
							_item.pointInterval = _cardInfo.interval * 1000;
							_item.data = _valueData;
							_item.type = _chartTypeMap[_chartParams[_metricName].type]; 
							_item.name = _chartParams[_metricName].display_name + ' ' + _typeMap[_type];
							_data.push(_item);
						}
					}
					__this.drawChart(_data, cardId);
				}
			}
		});
	}

	// }}}
	// {{{ function drawChart()


    /**
     * 绘制一个图表
     *
     * @param {Array} data
     * @param {String} cardId
     * @return {Void}  
     */
	this.drawChart = function (data, cardId)
	{
		var _params = cardId.split('_');
		var _chartId = _params[1];
		var _cardTypeId = _params[2];
		var _cardInfo = wbChartData[_chartId].card_type[_cardTypeId];
		var _chartParams = wbChartData[_chartId].metrics;
	   	Highcharts.setOptions({
			global: {
				useUTC: false,
			}
		});	
		
		var _chartInfo = wbChartData[_chartId];
        $('#chart_' + cardId).highcharts({
            title: {
                text: _chartInfo.title,
                x: -20 //center
            },
            subtitle: {
                text: _chartInfo.sub_title,
                x: -20
            },
			xAxis: {
                type: 'datetime',
				dateTimeLabelFormats: {second: '%H:%M:%S'},
				labels: {
                	step:  1
            	},
            },
            yAxis: {
                title: {
                    text: _chartInfo.y_title 
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: _chartInfo.unit 
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: data 
        });
	}

	// }}}
	// {{{ function drawNoDataChart()


    /**
     * 绘制一个没有数据的图表
     *
     * @param {String} cardId
     * @return {Void}  
     */
	this.drawNoDataChart = function (cardId)
	{
		var _params = cardId.split('_');
		var _chartId = _params[1];
		var _cardTypeId = _params[2];
		var _cardInfo = wbChartData[_chartId].card_type[_cardTypeId];
		var _chartParams = wbChartData[_chartId].metrics;
		
		var _chartInfo = wbChartData[_chartId];
        $('#chart_' + cardId).highcharts({
            title: {
                text: _chartInfo.title,
                x: -20 //center
            },
            subtitle: {
                text: _chartInfo.sub_title,
                x: -20
            },
			xAxis: {
                type: 'datetime',
				dateTimeLabelFormats: {second: '%H:%M:%S'},
				labels: {
                	step:  1
            	},
            },
            yAxis: {
                title: {
                    text: _chartInfo.y_title 
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
			series: [{
				type: 'line',
				name: 'no data',  
				data: []     
			}],        
			lang: {
				noData: "获取数据失败"
			},
			noData: {
				style: {    
					fontWeight: 'bold',     
					fontSize: '15px',
					color: '#303030'        
				}
			}
        });
	}

	// }}}
	// }}}
}
