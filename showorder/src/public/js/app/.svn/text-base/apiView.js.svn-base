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

function ApiView() {
	ModuleBase.call(this);
	var __this = this;

	// {{{ members

    /**
     * 图标对象 
     */
    this.charts = {};

    /**
     *  主接口绘图数据 
     */
    this.masterData = {};

    /**
     *  调用接口绘图数据 
     */
    this.apiData = {};


	// }}}
	// {{{ functions
	// {{{ function init()
		
	/**
	 * 初始化  
	 */
	this.init = function()
	{
		$(document).ready(function() {
			$("#add_api").on('click', function() {
				App.jumpPage('add_api');
			});

            $(".chartdiv").each(function() {
                var chartId = $(this).attr('active_data')
                var idInfo = chartId.split('_')
                var options = {
                    "sourceId": idInfo[0],
                    "apiId": idInfo[1],
                }

                __this.getData(options, true)

                __this.initDateRange(options, true)
            })
		});
	}
	
	// }}}
    // {{{ function initDateRange

    /**
     * 初始化日期控件 
     */
    this.initDateRange = function(options, isMaster) {
        if (isMaster) {
            var formID = "form_date_range_" + options.sourceId + '_' + options.apiId
        } else {
            var formID = "api_form_date_range_" + options.sourceId + '_' + options.masterId    
        }
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
            __this.getData(options, isMaster)
            $('#' + formID + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
        });

        $('#' + formID + ' span').html(Date.today().add({
            days: 0
        }).toString('yyyy-MM-dd') + ' - ' + Date.today().toString('yyyy-MM-dd'));   
    }

    // }}}
    // {{{ function _drawChart()

    this._drawChart = function(chartName, data) {
        var timeLine = [];
        var request = [];
        var haveData = [];
        var timeout = [];
        var requestTime = [];
        var requestTimeTitle = ['平均响应时间', '连接时间', 'LoopUp时间',
                                '99%响应时间', '98%响应时间', '97%响应时间',
                                '96%响应时间', '95%响应时间', '94%响应时间',
                                '93%响应时间', '92%响应时间', '91%响应时间',
                                '90%响应时间', '85%响应时间', '80%响应时间'];
        for (var j = 0; j < requestTimeTitle.length; j++) {
            requestTime[j] = [];
        }

        for (var i in data) {
            timeLine.push(new Date(parseInt(data[i].time) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " "))  
            request.push(data[i].request)
            haveData.push(data[i].haveData)
            timeout.push((data[i].timeout / data[i].request) * 100)
            requestTime.push()

            for (var j = 0; j < requestTimeTitle.length; j++) {
                requestTime[j].push(data[i].requestRange[j]) 
            }
        } 
        
        var requestTimeData = [];
        var legend = ['总请求量', '非空请求数据'];
        for (var j = 0; j < requestTimeTitle.length; j++) {
            requestTimeData.push({
                name: requestTimeTitle[j],
                type:'line',
                symbol: 'none',
                data: requestTime[j]
            }) 
            legend.push(requestTimeTitle[j])
        }

        legend.push('超时率');

        // 格式化主表数据
        var masterChartData = [];
        for(var i = 0; i < legend.length; i++) {
            if (i == 0) {
                masterChartData.push({name: legend[i], type: 'line', symbol: 'none', data: request})        
            }else if (i == 1) {
                masterChartData.push({name: legend[i], type: 'line', symbol: 'none', data: haveData})        
            } else {
                masterChartData.push({name: legend[i], type: 'line', symbol: 'none', data: []})        
            } 
        }

        // {{{ 主表

        var option = {
            title : {
                text: ''
            },
            tooltip : {
                trigger: 'axis',
                showDelay: 10,             // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
                formatter: function(obj) {
                    var displayData = obj.slice(0, 2)
                    var displayArr = [displayData[0].name]
                    for (var i = 0; i < displayData.length; i++) {
                        displayArr.push(displayData[i].seriesName + ":" + displayData[i].value) 
                    }
                    return displayArr.join("<br/>")
                }
            },
            legend: {
                data: legend,
                padding: [5, 5, 5, 1000]
            },
            toolbox: {
                show : true,
                feature : {
                    mark : {show: true},
                    dataZoom : {show: true},
                    magicType : {show: true, type: ['line', 'bar']},
                    restore : {show: true},
                    saveAsImage : {show: true}
                }
            },
            dataZoom : {
                y: 500,
                show : true,
                realtime: true,
                start : 0,
                end : 100
            },
            grid: {
                x: 80,
                y: 80,
                x2:20,
                y2:25
            },
            xAxis : [
                {
                    type : 'category',
                    boundaryGap : true,
                    axisTick: {onGap:false},
                    splitLine: {show:false},
                    data : timeLine
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    scale:true,
                    axisLabel: {
                      formatter: '{value}'
                    },
                    splitArea : {show : true}
                }
            ],
            series : masterChartData 
        };

        // }}}
        // {{{ 响应时间表

        var option2 = {
            tooltip : {
                trigger: 'axis',
                showDelay: 10             // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
            },
            legend: {
                y: -40,
                data: legend
            },
            toolbox: {
                y : -30,
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
            dataZoom : {
                show : true,
                realtime: true,
                start : 0,
                end : 100
            },
            grid: {
                x: 80,
                y: 5,
                x2:20,
                y2:40
            },
            xAxis : [
                {
                    type : 'category',
                    position:'top',
                    boundaryGap : true,
                    axisLabel:{show:false},
                    axisTick: {onGap:false},
                    splitLine: {show:false},
                    data : timeLine
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    scale:true,
                    splitNumber: 3,
                    axisLabel: {
                      formatter: '{value}ms'
                    },
                    splitArea : {show : true}
                }
            ],
            series : requestTimeData 
        };

        // }}}
        // {{{ 超时表

        option3 = {
            tooltip : {
                trigger: 'axis',
                showDelay: 10             // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
            },
            legend: {
                y : -40,
                data: legend
            },
            toolbox: {
                y : -30,
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
            dataZoom : {
                y:200,
                show : true,
                realtime: true,
                start : 0,
                end : 100
            },
            grid: {
                x: 80,
                y:5,
                x2:20,
                y2:30
            },
            xAxis : [
                {
                    type : 'category',
                    position:'bottom',
                    boundaryGap : true,
                    axisTick: {onGap:false},
                    splitLine: {show:false},
                    data : timeLine
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    scale:true,
                    splitNumber:3,
                    axisLabel: {
                      formatter: '{value}%'
                    },
                    splitArea : {show : true}
                }
            ],
            series : [
                {
                    name:'超时率',
                    type:'bar',
                    symbol: 'none',
                    data: timeout
                }
            ]
        };

        // }}}

        __this.charts[chartName + '1'] = echarts.init(document.getElementById(chartName + '1'));
        __this.charts[chartName + '1'].setOption(option);

        __this.charts[chartName + '2'] = echarts.init(document.getElementById(chartName + '2'));
        __this.charts[chartName + '2'].setOption(option2);

        __this.charts[chartName + '3'] = echarts.init(document.getElementById(chartName + '3'));
        __this.charts[chartName + '3'].setOption(option3);

        __this.charts[chartName + '1'].connect([__this.charts[chartName + '2'], __this.charts[chartName + '3']]);
        __this.charts[chartName + '2'].connect([__this.charts[chartName + '1'], __this.charts[chartName + '3']]);
        __this.charts[chartName + '3'].connect([__this.charts[chartName + '1'], __this.charts[chartName + '2']]);

        setTimeout(function (){
            window.onresize = function () {
                __this.charts[chartName + '1'].resize();
                __this.charts[chartName + '1'].resize();
                __this.charts[chartName + '1'].resize();
            }
        },200)
    }

    // }}}
    // {{{ function _drawApiDesc()

    this._drawApiDesc = function(chartId, data) {
        var id = 'api_desc_' + chartId;
        var _html = []
        for (i = 0; i < data.apiDesc.length; i++) {
            var actionData = data.apiDesc[i].apiId + '|' + data.start + '|' + data.end + '|' + chartId
            _html.push('<a class="icon-btn span2"  action-data="' + actionData + '" onclick="' + __this.__thisName +'.showApi(this)" style="margin-left:2.5%;width:17%">')
            _html.push('<i class="icon-cloud"></i>')
            _html.push('<div>' + data.apiDesc[i].apiName + '</div>')
            var timeout = data.apiDesc[i].timeout
            var timeoutCss = 'badge-success'
            if (timeout < 5) {
                timeoutCss = 'badge-success'
            } else if (timeout < 10) {
                timeoutCss = 'badge-info'    
            } else if (timeout < 20) {
                timeoutCss = 'badge-warnning'    
            } else {
                timeoutCss = 'badge-important'    
            }
            _html.push('<span class="badge  ' + timeoutCss + '">' + timeout + '</span>')
            _html.push('</a>') 
        }
        $("#" + id).html(_html.join(''))
    }

    // }}}
    // {{{ function showApi()

    this.showApi = function(obj) {
        var actionData = $(obj).attr('action-data').split('|')
        var idInfo = actionData[3].split('_')
        
        $("#master_" + actionData[3]).hide();
        $("#api_" + actionData[3]).show();
        $("#back_" + actionData[3]).on('click', function() {
            $("#master_" + actionData[3]).show();
            $("#api_" + actionData[3]).hide();
        });
        
        var apiTitle = $("#title_" + actionData[3]).text() + '---' + $(obj).children("div").html()
        $("#title_" + idInfo[0]).text(apiTitle)

        var options = {
            "sourceId": idInfo[0],
            "apiId": actionData[0],
            "masterId": idInfo[1],
            "start": actionData[1],
            "end": actionData[2]
        }
        
        __this.getData(options, false)
        __this.initDateRange(options, false)
    }

    // }}}
    // {{{ function getData()

    this.getData = function(options, isMaster) {
        if (isMaster) {
            var _url = 'system/apiView/list/' + options.sourceId + '/' + options.apiId;
            if (options.start && options.end) {
                var _data = 'start=' + options.start + '&end=' + options.end
            } else {
                var _data = 'emptyoption'
            }
            var chartId = options.sourceId + '_' + options.apiId
        } else {
            var _url = 'system/apiView/listApiDetail/' + options.sourceId + '/' + options.apiId + '/' + options.masterId + '/' + options.start + '/' + options.end;
            var _data = ''
            var chartId = options.sourceId + '_' + options.masterId
        }

        $.ajax ({
            type: "post",
            url : _url,
            data: _data,
            dataType: "json",
            success: function (dataRes) {
                if (isMaster) {
                    __this.masterData[chartId] = dataRes.masterData.length ? __this.formatData(dataRes.masterData) : {}
                    __this.masterData[chartId].apiDescData = dataRes.apiData.length ? __this.formatApiDescData(dataRes.apiData, dataRes.start, dataRes.end) : {}
                } else {
                    __this.apiData[chartId] = dataRes.length ? __this.formatData(dataRes) : {}
                }
                __this.drawTestBtn(chartId, isMaster)
                __this.showCombineChart(chartId, isMaster)    
            }
        });
    }

    // }}}
    // {{{ function formatData()

    /**
     * 格式化数据 
     */
    this.formatData = function(data) {
        var combineData = {};
        var testData = {};
        for (var i = 0; i < data.length; i++) {
            var currentItem = __this.formatItemData(data[i])
            var item = {};
            item.requestRange = [];
            if (typeof(combineData[currentItem.time]) == "undefined") {
                item = currentItem
            } else {
                item.time     = currentItem.time
                item.testId   = currentItem.testId
                item.request  = currentItem.request  + combineData[currentItem.time].request
                item.haveData = currentItem.haveData + combineData[currentItem.time].haveData
                item.timeout  = currentItem.timeout  + combineData[currentItem.time].timeout
                for (var j = 0; j < currentItem.requestRange.length; j++) {
                    item.requestRange[j] = (currentItem.requestRange[j] * currentItem.request 
                                                + combineData[currentItem.time].requestRange[j] * combineData[currentItem.time].request) / item.request
                }
            }
            if (typeof(testData[currentItem.testId]) == "undefined") {
                testData[currentItem.testId] = {}     
            }
            if (currentItem.testId != "0") {
                testData[currentItem.testId][currentItem.time] = currentItem 
            }
            combineData[currentItem.time] = item 
        }
        
        var data = {}
        data.combineData = combineData
        data.testData    = testData
        return data
    }

    // }}}
    // {{{ function formatItemData()

    /**
     * 格式化数据 
     */
    this.formatItemData = function(data) {
        var item = {};
        item.requestRange = [];
        var requestRange = data.requestRange.split(":")
        item.request  = data.request
        item.haveData = data.haveData
        item.timeout  = data.timeout
        item.time     = data.time
        item.testId   = data.testId
        item.requestRange[0] = data.requestAvg
        item.requestRange[1] = data.connectTime
        item.requestRange[2] = data.lookupTime
        for (var j = 0; j < requestRange.length; j++) {
            item.requestRange[j + 3] = requestRange[j] 
        }

        return item
    }

    // }}}
    // {{{ function formatApiDescData()

    /**
     * 格式化调用接口描述数据 
     */
    this.formatApiDescData = function(data, start, end) {
        var combineData = {};
        var testData = {};
        for (var i = 0; i < data.length; i++) {
            var key  = data[i].apiId
            if (typeof(combineData[key]) != "undefined") {
                if (combineData[key].timeout < data[i].timeout) {
                    combineData[key] = data[i]
                }
            } else {
                combineData[key] = data[i]   
            }

            if (typeof(testData[data[i].testId]) ==  "undefined") {
                testData[data[i].testId] = []
            }
            testData[data[i].testId].push(data[i])
        }
        
        var data = {}
        var combineResult = []
        for (var i in combineData) {
            combineResult.push(combineData[i])    
        }
        data.combineData = combineResult
        data.testData    = testData
        data.start = start
        data.end   = end
        return data
    }

    // }}}
    // {{{ function drawTestBtn()

    /**
     * 绘制灰度测试按钮 
     */
    this.drawTestBtn = function(chartId, isMaster) {
        var _html = []
        var id = (isMaster) ? ('master_' + chartId) : ('api_' + chartId)
        isMaster = isMaster ? 1 : 0
        var chartInfo  = __this.getChartInfo(chartId, isMaster)
        var testSuffix = '_btn_test' 

        if (typeof(chartInfo.data["combineData"]) != "undefined") {
            var testData = chartInfo.data.testData
            _html.push('  <div class="btn-group" id="' + id + testSuffix + '" is_master="'+ isMaster + '">')
            _html.push('<a href="#" data-toggle="dropdown" class="btn dropdown-toggle">')
            _html.push('A/B测试: <span>全量业务</span><i class="icon-angle-down"></i>')
            _html.push('</a>')
            _html.push('<ul class="dropdown-menu">')
            for (var testId in testData) {
                _html.push('<li><a action-data="' + chartId + '" test_id="' + testId + '" onclick="' + __this.__thisName + '.showTestChart(this)">' + testId + '</a></li>')
            }
            _html.push('<li><a action-data="' + chartId + '" test_id="0" onclick="' + __this.__thisName + '.showTestChart(this)">全量业务</a></li>')
            _html.push('</ul>')

            if ($("#" + id + testSuffix)) {
                $("#" + id + testSuffix).remove()    
            }
            $("#" + id + " .portlet-body .btn-group").after(_html.join(''))
        }
    }

    // }}}
    // {{{ function showTestChart()

    /**
     * 显示灰度测试图表  
     */
    this.showTestChart = function(obj) {
        var chartId  = $(obj).attr('action-data')
        var isMaster = $(obj).parent().parent().parent().attr("is_master")
        var testId   = $(obj).attr('test_id')
        var chartInfo = __this.getChartInfo(chartId, isMaster)
        var apiDescData = {}
        if (typeof(chartInfo.apiDescData.combineData) != "undefined") {
            if (testId == 0) { // 全量业务
                apiDescData.apiDesc = chartInfo.apiDescData.combineData
            } else {
                apiDescData.apiDesc = chartInfo.apiDescData.testData[testId] 
            }
            apiDescData.start = chartInfo.apiDescData.start
            apiDescData.end   = chartInfo.apiDescData.end
            __this._drawApiDesc(chartId, apiDescData)
        }
        if (typeof(chartInfo.data["combineData"]) != "undefined") {
            var data = []
            if (testId == 0) { // 全量业务
                data = chartInfo.data.combineData
            } else {
                data = chartInfo.data.testData[testId] 
            }
            __this._drawChart(chartInfo.chartName, data)
        } else {
            __this.clearCharts(chartInfo.chartName)
        }

        var aObj = $(obj).parent().parent().siblings("a")[0]
        var spanObj = $(aObj).children("span")[0]
        if (spanObj) {
            $(spanObj).text($(obj).text())    
        }
    }

    // }}}
    // {{{ function showCombineChart()

    /**
     * 显示主图表  
     */
    this.showCombineChart = function(chartId, isMaster) {
        var chartInfo = __this.getChartInfo(chartId, isMaster)
        var apiDescData = {} 
        if (typeof(chartInfo.apiDescData.combineData) != "undefined") {
            apiDescData.apiDesc = chartInfo.apiDescData.combineData
            apiDescData.start   = chartInfo.apiDescData.start
            apiDescData.end     = chartInfo.apiDescData.end
            __this._drawApiDesc(chartId, apiDescData)
        }

        if (typeof(chartInfo.data["combineData"]) != "undefined") {
            __this._drawChart(chartInfo.chartName, chartInfo.data.combineData)
        } else {
            __this.clearCharts(chartInfo.chartName)    
        }
    }

    // }}}
    // {{{ function getChartInfo()

    /**
     * 获取渲染表的结构  
     */
    this.getChartInfo = function(chartId, isMaster) {
        var info = {}
        var data = {}
        if (isMaster == 1) {
            info.chartName = 'master_chart_' + chartId + '_'
            data = __this.masterData
            info.apiDescData = __this.masterData[chartId].apiDescData
        } else {
            info.chartName = 'api_chart_' + chartId + '_'
            data = __this.apiData
            info.apiDescData = {} 
        }

        if (typeof(data[chartId]) != "undefined") {
            info.data = data[chartId]
        } else {
            info.data = {}    
        }
         

        return info
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
