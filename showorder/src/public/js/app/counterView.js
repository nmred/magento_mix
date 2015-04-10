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
* 接口性能展示
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/

function CounterView() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members

    /**
     * 图标对象 
     */
    this.charts = {};

    /**
     *  绘图数据 
     */
    this.data = {};

    /**
     *  绘图结构 
     */
    this.structData = {};

    /**
     *  A/B测试类型结构 
     */
    this.testData = {};

    /**
     * 起始结束时间 
     */
    this.timeData = {'start': 0, 'end': 0};

    /**
     * 时间轴 
     */
    this.timeLine = {}

	// }}}
	// {{{ functions
	// {{{ function init()
		
	/**
	 * 初始化  
	 */
	this.init = function()
	{
		$(document).ready(function() {
            $(".chartdiv").each(function() {
                var chartId = $(this).attr('active_data')
                var idInfo = chartId.split('_')
                var options = {
                    "sourceId": idInfo[0],
                    "apiId": idInfo[1],
                }

                __this.getData(options)

                __this.initDateRange(options)
            })
		});
	}
	
	// }}}
    // {{{ function initDateRange

    /**
     * 初始化日期控件 
     */
    this.initDateRange = function(options) {
        var formID = "form_date_range_" + options.sourceId + '_' + options.apiId
        $('#' + formID).daterangepicker({
            ranges: {
                '今天': ['today', 'today'],
                '昨天': ['yesterday', 'yesterday'],
                '最近7天': [Date.today().add({
                        days: -6
                    }), 'today'],
                '最近29天': [Date.today().add({
                        days: -29
                    }), 'today'],
                '本月': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                '最近一个月': [Date.today().moveToFirstDayOfMonth().add({
                        months: -1
                    }), Date.today().moveToFirstDayOfMonth().add({
                        days: -1
                    })]
            },
            opens: (App.isRTL() ? 'left' : 'right'),
            format: 'MM/dd/yyyy',
            separator: ' to ',
            startDate: Date.today().add({
                days: -29
            }),
            endDate: Date.today(),
            minDate: '01/01/2012',
            maxDate: '12/31/2099',
            locale: {
                applyLabel: '确定',
                fromLabel: '从',
                toLabel: '到',
                customRangeLabel: '自定义范围',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        },

        function (start, end) {
            options.start = start.getTime();
            options.end   = end.getTime() + 86400 * 1000;
            __this.getData(options)
            $('#' + formID + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
        });

        $('#' + formID + ' span').html(Date.today().add({
            days: 0
        }).toString('yyyy-MM-dd') + ' - ' + Date.today().toString('yyyy-MM-dd'));   
    }

    // }}}
    // {{{ function _drawChart()

    this._drawChart = function(chartId, data) {
        __this.timeLine[chartId].sort()
        var timeLineTmp = __this.timeLine[chartId]
        var timeLine = (timeLineTmp[0] != undefined) ? [timeLineTmp[0]] : []
        for(var i = 1; i < timeLineTmp.length; i++) {
            if (timeLineTmp[i] != timeLineTmp[i -1]) {
                timeLine.push(timeLineTmp[i])    
            }    
        }

        var counters = data.counters
        var legend = [];
        var viewData = []
        if (counters.length ==0) {
            return    
        }
        for (var j = 0; j < counters.length; j++) {
            legend.push(counters[j].name)
            var item = []
            for (var i = 0; i < timeLine.length; i++) {
                if (counters[j].data == undefined || counters[j].data[timeLine[i]] == undefined) {
                    item.push(0)
                } else {
                    item.push(counters[j].data[timeLine[i]].value)    
                }
            }
            viewData.push({
                'name': counters[j].name,
                'type':'line',
                'symbol': 'none',
                'data': item
            }) 
        }
        
        var timeLineData = []
        for (var i = 0; i < timeLine.length; i++) {
            timeLineData.push(new Date(parseInt(timeLine[i]) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " "))  
        }

        // {{{ 响应时间表

        var option = {
            title : {
                text: data.name
            },
            tooltip : {
                trigger: 'axis',
                showDelay: 10             // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
            },
            legend: {
                data: legend
            },
            toolbox: {
                show : true,
                feature : {
                    mark : {show: true},
                    dataZoom : {show: true},
                    dataView : {show: true, readOnly: false},
                    magicType : {show: true, type: ['line', 'bar']},
                    restore : {show: true},
                    saveAsImage : {show: true}
                }
            },
            xAxis : [
                {
                    type : 'category',
                    boundaryGap : true,
                    data : timeLineData
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    scale:true,
                    splitNumber: 3,
                    splitArea : {show : true}
                }
            ],
            series : viewData 
        };

        // }}}

        var chartName = chartId + '_' + data.groupId
        if (document.getElementById(chartName) == null) {
            var _html = '<div id="' + chartName + '" style="height: 300px; background-color: transparent; cursor: default;"></div>'
            $("#master_" + chartId + " .chartdiv").append(_html)
        }
        __this.charts[chartName] = echarts.init(document.getElementById(chartName));
        __this.charts[chartName].setOption(option);

        setTimeout(function (){
            window.onresize = function () {
                __this.charts[chartName].resize();
            }
        },200)
    }

    // }}}
    // {{{ function getData()

    this.getData = function(options) {
        var _url = 'system/counterView/list/' + options.sourceId + '/' + options.apiId;
        if (options.start && options.end) {
            var _data = 'start=' + options.start + '&end=' + options.end
        } else {
            var _data = 'emptyoption'
        }
        var chartId = options.sourceId + '_' + options.apiId

        $.ajax ({
            type: "post",
            url : _url,
            data: _data,
            dataType: "json",
            success: function (dataRes) {
                if (__this.timeLine[chartId] != undefined) {
                    __this.timeLine[chartId] = []
                }
                if (__this.testData[chartId] != undefined) {
                    __this.testData[chartId] = {} 
                }
                __this.data[chartId] =  __this.formatData(dataRes.statData, chartId)
                __this.structData[chartId] = __this.formatStructData(dataRes.counterDetails)
                __this.timeData.start = dataRes.start
                __this.timeData.end   = dataRes.end
                __this.drawTestBtn(chartId)
                __this.showChart(chartId)    
            }
        });
    }

    // }}}
    // {{{ function formatData()

    /**
     * 格式化数据 
     */
    this.formatData = function(data, chartId) {
        var result = {}
        for (var i in data) {
            result[i] = __this.formatItemData(data[i], chartId)
        }

        return result
    }

    // }}}
    // {{{ function formatStructData()

    /**
     * 格式化图标结构数据 
     */
    this.formatStructData = function(data) {
        var result = {}
        for (var i = 0; i < data.length; i++) {
            if (typeof(result[data[i].groupId]) == "undefined") {
                result[data[i].groupId] = {}
                result[data[i].groupId].name = data[i].groupName
                if (typeof(result[data[i].groupId].counters) == "undefined") {
                    result[data[i].groupId].counters = []
                }
                result[data[i].groupId].counters.push(data[i])
            } else {
                result[data[i].groupId].counters.push(data[i])
            }    
        }

        return result
    }

    // }}}
    // {{{ function formatItemData()

    /**
     * 格式化数据 
     */
    this.formatItemData = function(data, chartId) {
        var combineData = {}
        var testData = {} 

        for (var i = 0; i < data.length; i++) {
            var item = {} 
            if (typeof(combineData[data[i].time]) != "undefined") {
                item.value   = combineData[data[i].time].value + data[i].value 
                item.request = combineData[data[i].time].request + data[i].request 
                combineData[data[i].time] = item
            } else {
                combineData[data[i].time] = data[i]
            }
            if (data[i].testId != "0") {
                if (typeof(testData[data[i].testId]) == "undefined") {
                    testData[data[i].testId] = {}     
                }
                testData[data[i].testId][data[i].time] = data[i] 

                // 计算testId
                if (typeof(__this.testData[chartId]) == "undefined") {
                    __this.testData[chartId] = {}
                    __this.testData[data[i].testId] = 1    
                }
                if (typeof(__this.testData[chartId][data[i].testId]) == "undefined") {
                    __this.testData[chartId][data[i].testId] = 1    
                }
            }
            if (typeof(__this.timeLine[chartId]) == "undefined") {
                __this.timeLine[chartId] = []
            }
            __this.timeLine[chartId].push(data[i].time)
        }

        return {'combineData': combineData, 'testData': testData}
    }

    // }}}
    // {{{ function drawTestBtn()

    /**
     * 绘制灰度测试按钮 
     */
    this.drawTestBtn = function(chartId) {
        var _html = []
        var id = 'master_' + chartId
        var testSuffix = '_btn_test' 

        _html.push('  <div class="btn-group" id="' + id + testSuffix + '">')
        _html.push('<a href="#" data-toggle="dropdown" class="btn dropdown-toggle">')
        _html.push('A/B测试: <span>全量业务</span><i class="icon-angle-down"></i>')
        _html.push('</a>')
        _html.push('<ul class="dropdown-menu">')
        for (var testId in __this.testData[chartId]) {
            _html.push('<li><a action-data="' + chartId + '" test_id="' + testId + '" onclick="' + __this.__thisName + '.showTestChart(this)">' + testId + '</a></li>')
        }
        _html.push('<li><a action-data="' + chartId + '" test_id="0" onclick="' + __this.__thisName + '.showTestChart(this)">全量业务</a></li>')
        _html.push('</ul>')

        if ($("#" + id + testSuffix)) {
            $("#" + id + testSuffix).remove()    
        }
        $("#" + id + " .portlet-body .btn-group").after(_html.join(''))
    }

    // }}}
    // {{{ function showTestChart()

    /**
     * 显示灰度测试图表  
     */
    this.showTestChart = function(obj) {
        var chartId  = $(obj).attr('action-data')
        var testId   = $(obj).attr('test_id')
        if (testId == 0) {
            __this.showChart(chartId)
        } else {
            __this.showChart(chartId, testId)
        }

        var aObj = $(obj).parent().parent().siblings("a")[0]
        var spanObj = $(aObj).children("span")[0]
        if (spanObj) {
            $(spanObj).text($(obj).text())    
        }
    }

    // }}}
    // {{{ function showChart()

    /**
     * 显示主图表  
     */
    this.showChart = function(chartId, testId) {
        if (typeof(__this.structData[chartId]) == "undefined") {
            __this.clearCharts(chartInfo.chartName)    
            return;
        }

        var structData = __this.structData[chartId]
        var data = []
        for(var groupId in structData) {
            var item = {} 
            item.name = structData[groupId].name
            var counters = structData[groupId].counters
            for (var i = 0; i < counters.length; i++) {
                if (counters[i].data == undefined) {
                    counters[i].data = []
                }
                if (testId == undefined) {
                    counters[i].data = __this.data[chartId][counters[i].counterId].combineData
                } else {
                    counters[i].data = __this.data[chartId][counters[i].counterId].testData[testId]
                }
            }
            item.counters = counters 
            item.groupId  = groupId 
            data.push(item)
        }
        for (var i = 0; i < data.length; i++) {
            __this._drawChart(chartId, data[i])    
        }
    }

    // }}}
    // {{{ function clearCharts()

    /**
     * 清理图表 
     */
    this.clearCharts = function(chartName) {
        if (typeof(__this.charts[chartName + '1']) != "undefined") {
            __this.charts[chartName + '1'].clear()   
            __this.charts[chartName + '2'].clear()   
            __this.charts[chartName + '3'].clear()   
            __this.charts[chartName + '1'].dispose()   
            __this.charts[chartName + '2'].dispose()   
            __this.charts[chartName + '3'].dispose()
        }
    }

    // }}}
    // }}}
}
