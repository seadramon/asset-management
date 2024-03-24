/* ------------------------------------------------------------------------------
 *
 *  # Echarts - Line charts
 *
 *  Demo JS code for echarts_lines.html page
 *
 * ---------------------------------------------------------------------------- */


// Setup module
// ------------------------------

var EchartsLines = function() {


    //
    // Setup module components
    //

    // Line charts
    var _lineChartExamples = function() {
        if (typeof echarts == 'undefined') {
            console.warn('Warning - echarts.min.js is not loaded.');
            return;
        }

        // Define elements
        var line_basic_element = document.getElementById('line_basic');        


        //
        // Charts configuration
        //

        // Basic line chart
        if (line_basic_element) {

            // Initialize chart
            var line_basic = echarts.init(line_basic_element);


            //
            // Chart config
            //

            // Options
            line_basic.setOption({

                // Define colors
                // color: ['#EF5350', '#66BB6A'],

                // Global text styles
                textStyle: {
                    fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                    fontSize: 13
                },

                // Chart animation duration
                animationDuration: 750,

                // Setup grid
                grid: {
                    left: 0,
                    right: 40,
                    top: 35,
                    bottom: 0,
                    containLabel: true
                },

                // Add legend
                legend: {
                    data: ['Maximum', 'Minimum'],
                    itemHeight: 8,
                    itemGap: 20
                },

                // Add tooltip
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(0,0,0,0.75)',
                    padding: [10, 15],
                    textStyle: {
                        fontSize: 13,
                        fontFamily: 'Roboto, sans-serif'
                    }
                },

                // Horizontal axis
                xAxis: [{
                    type: 'category',
                    boundaryGap: false,
                    data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    axisLabel: {
                        color: '#333'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#999'
                        }
                    },
                    splitLine: {
                        lineStyle: {
                            color: ['#eee']
                        }
                    }
                }],

                // Vertical axis
                yAxis: [{
                    type: 'value',
                    axisLabel: {
                        formatter: '{value} °C',
                        color: '#333'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#999'
                        }
                    },
                    splitLine: {
                        lineStyle: {
                            color: ['#eee']
                        }
                    },
                    splitArea: {
                        show: true,
                        areaStyle: {
                            color: ['rgba(250,250,250,0.1)', 'rgba(0,0,0,0.01)']
                        }
                    }
                }],

                // Add series
                series: [
                    {
                        name: 'Maximum',
                        type: 'line',
                        data: [11, 11, 15, 13, 12, 13, 10],
                        smooth: true,
                        symbolSize: 7,
                        markLine: {
                            data: [{
                                type: 'average',
                                name: 'Average'
                            }]
                        },
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    },
                    {
                        name: 'Minimum',
                        type: 'line',
                        data: [1, -2, 2, 5, 3, 2, 0],
                        smooth: true,
                        symbolSize: 7,
                        markLine: {
                            data: [{
                                type: 'average',
                                name: 'Average'
                            }]
                        },
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    }
                ]
            });
        }

        // Stacked lines chart
        if (line_stacked_element) {

            // Initialize chart
            var line_stacked = echarts.init(line_stacked_element);


            //
            // Chart config
            //

            // Options
            line_stacked.setOption({

                // Global text styles
                textStyle: {
                    fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                    fontSize: 13
                },

                // Chart animation duration
                animationDuration: 750,

                // Setup grid
                grid: {
                    left: 0,
                    right: 20,
                    top: 35,
                    bottom: 0,
                    containLabel: true
                },

                // Add legend
                legend: {
                    data: ['Internet Explorer', 'Opera', 'Safari', 'Firefox', 'Chrome'],
                    itemHeight: 8,
                    itemGap: 20
                },

                // Add tooltip
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(0,0,0,0.75)',
                    padding: [10, 15],
                    textStyle: {
                        fontSize: 13,
                        fontFamily: 'Roboto, sans-serif'
                    }
                },

                // Horizontal axis
                xAxis: [{
                    type: 'category',
                    boundaryGap: false,
                    data: [
                        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                    ],
                    axisLabel: {
                        color: '#333'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#999'
                        }
                    },
                    splitLine: {
                        lineStyle: {
                            color: ['#eee']
                        }
                    }
                }],

                // Vertical axis
                yAxis: [{
                    type: 'value',
                    axisLabel: {
                        color: '#333'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#999'
                        }
                    },
                    splitLine: {
                        lineStyle: {
                            color: ['#eee']
                        }
                    },
                    splitArea: {
                        show: true,
                        areaStyle: {
                            color: ['rgba(250,250,250,0.1)', 'rgba(0,0,0,0.01)']
                        }
                    }
                }],

                // Add series
                series: [
                    {
                        name: 'Internet Explorer',
                        type: 'line',
                        stack: 'Total',
                        smooth: true,
                        symbolSize: 7,
                        data: [120, 132, 101, 134, 90, 230, 210],
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    },
                    {
                        name: 'Opera',
                        type: 'line',
                        stack: 'Total',
                        smooth: true,
                        symbolSize: 7,
                        data: [220, 182, 191, 234, 290, 330, 310],
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    },
                    {
                        name: 'Safari',
                        type: 'line',
                        stack: 'Total',
                        smooth: true,
                        symbolSize: 7,
                        data: [150, 232, 201, 154, 190, 330, 410],
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    },
                    {
                        name: 'Firefox',
                        type: 'line',
                        stack: 'Total',
                        smooth: true,
                        symbolSize: 7,
                        data: [320, 332, 301, 334, 390, 330, 320],
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    },
                    {
                        name: 'Chrome',
                        type: 'line',
                        stack: 'Total',
                        smooth: true,
                        symbolSize: 7,
                        data: [820, 932, 901, 934, 1290, 1330, 1320],
                        itemStyle: {
                            normal: {
                                borderWidth: 2
                            }
                        }
                    }
                ]
            });
        }

        // Inverted axes
        

        // Multiple lines
        

        // Display point values
        

        // Zoom option
        


        //
        // Resize charts
        //

        // Resize function
        var triggerChartResize = function() {
            line_basic_element && line_basic.resize();
            line_stacked_element && line_stacked.resize();
            line_inverted_axes_element && line_inverted_axes.resize();
            line_multiple_element && line_multiple.resize();
            line_values_element && line_values.resize();
            line_zoom_element && line_zoom.resize();
        };

        // On sidebar width change
        $(document).on('click', '.sidebar-control', function() {
            setTimeout(function () {
                triggerChartResize();
            }, 0);
        });

        // On window resize
        var resizeCharts;
        window.onresize = function () {
            clearTimeout(resizeCharts);
            resizeCharts = setTimeout(function () {
                triggerChartResize();
            }, 200);
        };
    };


    //
    // Return objects assigned to module
    //

    return {
        init: function() {
            _lineChartExamples();
        }
    }
}();


// Initialize module
// ------------------------------

document.addEventListener('DOMContentLoaded', function() {
    EchartsLines.init();
});
