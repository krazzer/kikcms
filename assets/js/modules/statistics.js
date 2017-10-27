var Statistics = Class.extend({
    ATTR_INTERVAL: 'data-interval',

    CLASS_BTN_DEFAULT: 'btn-default',
    CLASS_BTN_PRIMARY: 'btn-primary',
    CLASS_FAILED: 'failed',
    CLASS_GLOW: 'glow',
    CLASS_LOADING: 'loading',
    CLASS_START: 'start',

    COLOR_PINK: '#3669c9',
    COLOR_BLUE: '#df137a',

    STR_FETCHING_NEW_DATA: KikCMS.translations['statistics.fetchingNewData'],
    STR_FETCHING_FAILED: KikCMS.translations['statistics.fetchingFailed'],
    STR_FETCH_NEW_DATA: KikCMS.translations['statistics.fetchNewData'],
    STR_VISITORS: KikCMS.translations['statistics.visitors'],

    URL_GET_VISITORS: '/cms/stats/getVisitors',
    URL_UPDATE_STATISTICS: '/cms/stats/update',

    $controls: null,
    $buttonRefresh: null,
    $buttonRefreshLbl: null,
    $settingsInput: null,
    $intervalInputs: null,
    $intervalButtons: null,
    $fieldStart: null,
    $fieldEnd: null,
    $rangeInputs: null,
    $visitors: null,

    $tableOverviewBody: null,

    settings: null,

    /**
     * Get the current user selected interval value
     * @return string
     */
    getInterval: function () {
        return this.$intervalButtons.filter('.' + this.CLASS_BTN_PRIMARY).attr(this.ATTR_INTERVAL);
    },

    /**
     * Initialize the Statistics component
     */
    init: function () {
        if(typeof google == 'undefined'){
            return;
        }

        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(this.renderChart.bind(this));

        this.initElements();

        this.settings = JSON.parse(this.$settingsInput.val());

        this.$rangeInputs.each(this.initRangeInput.bind(this));
        this.$intervalButtons.click(this.onIntervalButtonClick.bind(this));
        this.$buttonRefresh.click(this.onRefreshClick.bind(this));
    },

    /**
     * Initialize elements used
     */
    initElements: function () {
        this.$visitors = $('#visitors');
        this.$controls = $('.controls');

        this.$settingsInput   = this.$controls.find('input[name=settings]');
        this.$buttonRefresh   = this.$controls.find('.refresh');
        this.$rangeInputs     = this.$controls.find('.dateRange input[type=date]');
        this.$intervalButtons = this.$controls.find('.interval button');
        this.$fieldStart      = this.$controls.find('.start');
        this.$fieldEnd        = this.$controls.find('.end');

        this.$tableOverviewBody = $('#tab-overview').find('table tbody');

        this.$buttonRefreshLbl = this.$buttonRefresh.find('.lbl');
    },

    /**
     * Initialize the range input fields
     *
     * @param index
     * @param field
     */
    initRangeInput: function (index, field) {
        var $field = $(field);

        var datepickerSettings = {
            format: this.settings.dateFormat,
            minDate: this.settings.minDate,
            maxDate: this.settings.maxDate,
            useCurrent: false
        };

        if ($field.hasClass(this.CLASS_START)) {
            datepickerSettings.defaultDate = this.settings.startDate;
        }

        $field.datetimepicker(datepickerSettings);
        $field.on('dp.change', this.renderChart.bind(this));
    },

    /**
     * Action after the refresh button fades out
     */
    onButtonRefreshFadeOut: function () {
        this.$buttonRefresh.addClass(this.CLASS_LOADING);
        this.$buttonRefresh.addClass(this.CLASS_BTN_DEFAULT);

        this.$buttonRefresh.removeClass(this.CLASS_BTN_PRIMARY);
        this.$buttonRefresh.removeClass(this.CLASS_GLOW);

        this.$buttonRefreshLbl.text(this.STR_FETCHING_NEW_DATA);
    },

    /**
     * Action after new data from google is fetched
     *
     * @param response
     */
    onDataUpdate: function (response) {
        if (!response.success) {
            this.$buttonRefresh.removeClass(this.CLASS_LOADING);
            this.$buttonRefreshLbl.text(this.STR_FETCHING_FAILED);
            return;
        }

        this.$buttonRefresh.removeClass(this.CLASS_LOADING);
        this.$buttonRefresh.removeClass(this.CLASS_BTN_DEFAULT);

        this.$buttonRefresh.addClass(this.CLASS_BTN_PRIMARY);
        this.$buttonRefresh.addClass(this.CLASS_GLOW);

        this.$buttonRefreshLbl.text(this.STR_FETCH_NEW_DATA);

        this.$rangeInputs.each(function() {
            $(this).data("DateTimePicker").maxDate(moment(response.maxDate));
        });
    },

    /**
     * Action when clicked on either interval buttons
     *
     * @param event
     */
    onIntervalButtonClick: function (event) {
        var $intervalButton = $(event.target);
        this.$intervalButtons.removeClass(this.CLASS_BTN_PRIMARY);
        this.$intervalButtons.addClass(this.CLASS_BTN_DEFAULT);

        $intervalButton.addClass(this.CLASS_BTN_PRIMARY);
        $intervalButton.removeClass(this.CLASS_BTN_DEFAULT);

        this.renderChart();
    },

    /**
     * Action when clicked on the data refresh button
     *
     * @returns bool
     */
    onRefreshClick: function () {
        if (this.$buttonRefresh.hasClass(this.CLASS_LOADING)) {
            return false;
        }

        if (this.$buttonRefresh.hasClass(this.CLASS_FAILED)) {
            this.updateAnalyticsData();
            return false;
        }

        this.renderChart();

        this.$buttonRefresh.fadeOut(this.onButtonRefreshFadeOut.bind(this));
    },

    /**
     * @param overviewData
     */
    populateOverviewTable: function (overviewData) {
        var tableBodyHtml = '';

        $.each(overviewData, function (key, value) {
            tableBodyHtml += '<tr><td>' + key + ':</td><td>' + value + '</td></tr>';
        });

        this.$tableOverviewBody.html(tableBodyHtml);
    },

    /**
     * @param type
     * @param visitorData
     */
    populateVisitorDataTable: function (type, visitorData) {
        var tableBodyHtml = '';

        $.each(visitorData, function (key, value) {
            tableBodyHtml +=
                '<tr>' +
                '<td><span>' + value.value + '</span></td>' +
                '<td>' + value.visits + '</td>' +
                '<td>' +
                '<span class="percentage" style="width: ' + (value.percentage * 5) + 'px;"></span>' +
                value.percentage + '%' +
                '</td>' +
                '</tr>';
        });

        $('#tab-' + type).find('table tbody').html(tableBodyHtml);
    },

    /**
     * Renders the chart
     */
    renderChart: function () {
        var params = {
            interval: this.getInterval(),
            start: this.$fieldStart.val(),
            end: this.$fieldEnd.val()
        };

        KikCMS.action(this.URL_GET_VISITORS, params, this.renderChartWithData.bind(this));
    },

    /**
     * Actually render the chart with the given data
     *
     * @param jsonData
     */
    renderChartWithData: function (jsonData) {
        var options = {
            title: this.STR_VISITORS,
            legend: {position: 'bottom'},
            colors: [this.COLOR_PINK, this.COLOR_BLUE],
            chartArea: {'width': '80%'}
        };

        var data  = new google.visualization.DataTable(jsonData.visitorsData);
        var chart = new google.visualization.AreaChart(this.$visitors[0]);

        this.populateOverviewTable(jsonData.overviewData);
        $.each(jsonData.visitorData, this.populateVisitorDataTable.bind(this));

        chart.draw(data, options);

        if (jsonData.requiresUpdate) {
            this.updateAnalyticsData();
        } else {
            this.$buttonRefresh.fadeOut();
        }
    },

    /**
     * Update the chart action from google. It uses it's own loader so KikCMS.action is not used here
     */
    updateAnalyticsData: function () {
        this.$buttonRefresh.fadeIn();

        $.ajax({
            url: this.URL_UPDATE_STATISTICS,
            success: this.onDataUpdate.bind(this),
            dataType: 'json'
        });
    }
});

var statistics = new Statistics();

$(function () {
    statistics.init();
});
