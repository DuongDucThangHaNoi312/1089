const $ = require('jquery');

require('bootstrap');

jQuery(document).ready(function() {

    /* https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
    function addhttp(url) {
        if (!/^(?:f|ht)tps?\:\/\//.test(url)) {
            url = "http://" + url;
        }
        return url;
    }

    function addCityDepartmentInJobTitle() {
        var cityValue = undefined;
        var departmentValue = undefined;
        $('select.data-city').each(function () {
            cityValue = $(this).find("option:selected").val();
        });

        $('select.data-department').each(function () {
            departmentValue = $(this).find("option:selected").val();
        });

        console.log(cityValue);
        console.log(departmentValue);

        if (cityValue !== undefined && departmentValue !== undefined) {
            var jobTitleDivisionId = $('select.job-title-division').attr('id');
            var jobTitleActionDivisionIdString = '#field_actions_' + jobTitleDivisionId + ' a';
            var jobTitleDivisionHref = $(jobTitleActionDivisionIdString).attr('href');
            $(jobTitleActionDivisionIdString).attr("href", jobTitleDivisionHref+'?city='+cityValue+'?department='+departmentValue);
        }

        if(cityValue !== undefined) {
            var jobTitleDepartmentId = $('select.job-title-department').attr('id');
            var jobTitleActionDepartmentIdString = '#field_actions_' + jobTitleDepartmentId + ' a';
            var jobTitleDepartmentHref = $(jobTitleActionDepartmentIdString).attr('href');
            $(jobTitleActionDepartmentIdString).attr("href", jobTitleDepartmentHref+'?city='+cityValue);
        }
    }

    addCityDepartmentInJobTitle();
    $("select.data-city").change(function () {
        addCityDepartmentInJobTitle();
    });

    var storeItems = {};
    // specific to main-navigation
    var mainNavData = '#main-navigation > .nav-tabs li a[data-toggle="tab"]';

    /* get sessionStorage for selectedTab */
    var tabObj = JSON.parse(sessionStorage.getItem('activeTab'));
    var href = sessionStorage.getItem('href');
    var isEmptyObject = $.isEmptyObject(tabObj);

    $(mainNavData).click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    /* Store data in sessionStorage for selectedTab */
    $(mainNavData).on("shown.bs.tab", function (e) {
        var id = $(e.target).data("nav-tab");
        store('mainNavData', id);
        sessionStorage.setItem('href', location.href);
    });


    if(tabObj !== null && isEmptyObject !== true && href === location.href) {
        if (tabObj['mainNavData'] !== null && tabObj['mainNavData'] !== 'undefined') {
            store('mainNavData', tabObj['mainNavData']);
            activeTab(tabObj['mainNavData']);
        }
    } else {
        var layer = 'mainNavData';
        storeFirstTab(layer, mainNavData);
    }


    function storeFirstTab(layerOfTab, listOfTabs){
        var firstElement = $(listOfTabs).first();
        var firstTabId = firstElement.data('nav-tab');
        store(layerOfTab, firstTabId);
        activeTab(firstTabId)
    }

    function store(storeTab, storeTabId) {
        storeItems[storeTab] = storeTabId;
        sessionStorage.setItem('activeTab', JSON.stringify(storeItems));
    }

    /* set activeTab */
    function activeTab(tab) {
        var selected = $('a[data-nav-tab="' + tab + '"]');
        selected.parent().siblings().removeClass('active');
        selected.parent().addClass('active');
        selected.tab('show');
        var tabReference = selected.attr('href');
        $(tabReference).siblings().removeClass('active in');
        $(tabReference).addClass('active in');
    }
});