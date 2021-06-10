// any CSS you require will output into a single css file (app.css in this case)
require('../css/global.scss');

const $ = require('jquery');
global.$ = global.jQuery = $;
const Noty = require('noty');
var Cleave = require('cleave.js');
require('cleave.js/dist/addons/cleave-phone.us');

require('slick-carousel/slick/slick.js');
require('slick-carousel/slick/slick.css');
require('slick-carousel/slick/slick-theme.css');

// // JS is equivalent to the normal "bootstrap" package
// // no need to set this to a variable, just require it
require('popper.js');
require('bootstrap');
require('bootstrap-datepicker');
require('select2');
require('ion-rangeslider');
require('select2/dist/css/select2.min.css');
require('noty/lib/noty.css');
require('select2-bootstrap4-theme/dist/select2-bootstrap4.min.css');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

require('../../public/bundles/tetranzselect2entity/js/select2entity.js');

require('../../public/bundles/sonataadmin/vendor/jqueryui/ui/jquery-ui');
require('../../public/bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css');

// Setup collection forms
var $removeButton = $('<a href="#" class="remove-item"><i class="fa fa-minus"></i></a>');

jQuery(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        "trigger": "hover",
        "delay": { "show": 250, "hide": 2000 }
    });
    $('[data-toggle="tooltip"]').tooltip();

    $('.modal.open').modal('show');

    function removePopover(selector) {
        selector.popover('dispose');
    }

    function initializePopover(selector) {
        selector.popover({
            "trigger": "hover",
            "delay": { "show": 250, "hide": 500 }
        });

        $('.modal.open').modal('show');
    }

    /* https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
    function addhttp(url) {
        if (!/^(?:f|ht)tps?\:\/\//.test(url)) {
            url = "http://" + url;
        }
        return url;
    }

    function addCityInJobTitle() {
        $('select.data-city').each(function () {
            // get data from job title city
            var citySelected = $(this).find("option:selected");
            var citySelectedValue = citySelected.val();
            // transfers over to url href of job title department in append form fields
            var jobTitleDeptId = $('select.job-title-department').attr('id');
            var jobTitleActionIdString = '#field_actions_' + jobTitleDeptId + ' a';
            var jobTitleHref = $(jobTitleActionIdString).attr('href');
            $(jobTitleActionIdString).attr("href", jobTitleHref+'?city='+citySelectedValue);
        });
    }
    addCityInJobTitle();
    $("select.data-city").change(function () {
        addCityInJobTitle();
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

    /* init range slider */
    $('.js-range-slider').ionRangeSlider({
        skin: "flat",
        type: "double",
        grid: true
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

    /* Create noty notification */
    function createNoty(type = 'error', message) {
        new Noty({
            layout: 'topCenter',
            theme: 'metroui',
            type: type,
            text: message,
            timeout: 2500,
        }).show();
    }

    /* Set the default theme to bootstrap4 for all Select2 */
    $.fn.select2.defaults.set( "theme", "bootstrap4" );

    function initializeSelect2s() {
        $('.select2box').each(function () {
            var placeholder = $(this).data('placeholder');

            $(this).select2({
                placeholder: placeholder,
                allowClear: true
            });
        });

        $('#homepage_job_search_state').select2({}).next().addClass('select2-single');
        $('#second_homepage_job_search_state').select2({}).next().addClass('select2-single');
        $('#homepage_job_search_county').select2({}).next().addClass('select2-single');
        $('#second_homepage_job_search_county').select2({}).next().addClass('select2-single');
        $('#homepage_link_search_state').select2({}).next().addClass('select2-single');
        $('#homepage_link_search_county').select2({}).next().addClass('select2-single');

        $('#job_seeker_account_information_interestedJobCategories').select2();
        $('#job_seeker_account_information_interestedJobTitleNames').select2();
        $('#job_seeker_account_information_interestedCounties').select2();

        $('#find_city_city').select2();
        $('#step_one_department').select2();
        $('#step_one_jobTitle').select2();
        $('#step_two_city').select2();
        $('#step_two_state').select2();
        $('#step_two_county').select2();
        $('#job_seeker_profile_interestedJobCategories').select2({
            maximumSelectionLength: 1
        });
        // $('#job_seeker_profile_interestedJobLevels').select2({
        //     maximumSelectionLength: 2
        // });
        $('#job_seeker_profile_interestedJobTitleNames').select2({
            maximumSelectionLength: 5
        });
        $('#job_seeker_profile_interestedCounties').select2({
            maximumSelectionLength: 5
        });
        $('select#agency_info_counties').select2();
        $('#search_filter_state').select2();
        $('#search_filter_counties').select2({"language": {"noResults": function() { return "Select a State First"}}});
        $('#search_filter_cities').select2({"language": {"noResults": function() { return "Select State or County First"}}});
        $('#search_filter_jobTitleNames').select2();
        $('.select2-state').select2();
        $('.select2-counties').select2({"language": {"noResults": function() { return "Select a State First"}}});
        $('.select2-cities').select2({"language": {"noResults": function() { return "Select State or County First"}}});
        $('.select2-jobTitleNames').select2();
        //$('#form_jobTitle').select2();
        $('#form_department').select2();
        citySearchSelect2($('#location_city'));
        citySearchSelect2($('#resume_job_seeker_city'));
        initializeSearchLocation();
        citySearchSelect2($('#settings_citiesToBlock'));
        // $('#resume_job_seeker_city').select2();
        $('#interest_profile_interestedJobTitleNames').select2();
        $('#interest_profile_interestedJobCategories').select2();
        $('#interest_profile_interestedJobLevels').select2();
        $('#interest_profile_interestedCounties').select2();
        $('#account_information_jobTitle').select2();
        $('#account_information_department').select2();
        $('#job_alert_setting_notificationPreferenceForSubmittedInterest').select2();
        $('#job_alert_setting_notificationPreferenceForJobsMatchingSavedSearchCriteria').select2();
        $('#job_title_jobTitleName').select2();
        $('#job_title_department').select2();
        $('#job_title_division').select2();
        $('#job_title_level').select2();
        $('#job_title_category').select2();
        $('#job_title_type').select2();
        $('.js-job-category').select2({
            allowClear: true,
            placeholder: "Select a Category"
        });
    }

    function initializeFormattedInputs() {
        if ($( ".cleave-phone" ).length) {
            new Cleave('.cleave-phone', {
                phone: true,
                phoneRegionCode: 'us',
                delimiter: '-'
            });
        }
    }

    // JS Date picker
    $('input[type=date]').each(function() {
        if (this.type != 'date') { /* use datepicker for older browsers that don't support html5 date type */
            $(this).datepicker({format: 'yyyy-mm-dd'});
        }
    });
    $('.js-datepicker').datepicker();
    initializeSelect2s();
    initializeFormattedInputs();

    /* Ajax Form general methods */
    function initializeAjaxForm(name, collections = []) {
        let main_identifier = "#" + name;
        if ($(main_identifier).length == 0) {
            return;
        }

        setInputsToElementsForForm(main_identifier);
        initializeSelect2s();
        initializeFormattedInputs();
        initializeCollectionAjaxForms(main_identifier, name, collections);

        $(main_identifier + ' .edit-btn, ' + main_identifier + ' .cancel-btn').click(function(){
            $(main_identifier + ' .ajax-form').toggle();
            $(main_identifier + ' .editable').toggle();
            $(main_identifier + ' .edit-btn').toggle();
            $(main_identifier + ' .title.cancel-btn').toggle();
        });

        $(main_identifier + ' .upload-btn:not(.js-click)').click(function(){
            $(main_identifier + ' > .ajax-form.file-upload > form .vich-image .custom-file-input').click();
        }).addClass('js-click');

        $(main_identifier + ' .delete-btn:not(.js-click)').click(function() {
            console.log($(main_identifier + ' > .ajax-form.file-upload > form .vich-image form-check-input'));
            $(main_identifier + ' > .ajax-form.file-upload > form .vich-image .form-check-input')[0].checked = true;
            $(main_identifier + ' > .ajax-form.file-upload > form').submit();
        }).addClass('js-click');

        $(main_identifier + ' > .ajax-form.file-upload > form .vich-image .custom-file-input').change(function(){
            $(main_identifier + ' > .ajax-form.file-upload > form').submit();
        });

        if (main_identifier === "#city-job-announcement-active-dates") {
            initializeJobAnnouncementHasNoEndDate();
        }

        $(main_identifier + ' .ajax-form:not(.file-upload) form').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data, status) {
                    if (data.form) {
                        setFormView(main_identifier, data.form);
                    } else {
                        setEditableRenderedView(main_identifier, data.display);
                    }

                    if (data.additional_displays && data.additional_displays.length !== 0) {
                        for (let key in data.additional_displays) {
                            if (!data.additional_displays.hasOwnProperty(key)) continue;
                            setRenderedView("#" + key, data.additional_displays[key]);
                        }
                    }
                    createNoty('success', data.message);
                    setInputsToElementsForForm(main_identifier);
                    if (main_identifier === "#city-job-announcement-active-dates") {
                        initializeJobAnnouncementHasNoEndDate();
                    }
                    initializeCollection();
                },
                error: function(xhr, desc, err) {
                    if (xhr.responseJSON && xhr.responseJSON.form) {
                        createNoty('error', xhr.responseJSON.message);
                        setFormView(main_identifier, xhr.responseJSON.form);
                    } else {
                        createNoty('error', xhr.statusText);
                    }
                }
            });
        });
    }

    function setFormView(main_identifier, data) {

        var collections = [];
        let name = main_identifier.replace('#', '');

        initializeCollectionAjaxForms(main_identifier, name, collections);
        initializeSelect2s();
        initializeFormattedInputs();

        $(main_identifier + " .ajax-form").replaceWith(data);
        $(main_identifier + ' .cancel-btn').click(function(){
            $(main_identifier + ' .ajax-form').hide();
            $(main_identifier + ' .editable').show();
            $(main_identifier + ' .edit-btn').show();
            $(main_identifier + ' .title.cancel-btn').hide();
        });

        $(main_identifier + ' .edit-btn').click(function() {
            $(main_identifier + ' .ajax-form').show();
            $(main_identifier + ' .editable').hide();
            $(main_identifier + ' .edit-btn').hide();
            $(main_identifier + ' .title.cancel-btn').show();
        });

        if (main_identifier === "#city-job-announcement-active-dates") {
            initializeJobAnnouncementHasNoEndDate();
        }

        $(main_identifier + ' .ajax-form:not(.file-upload) form').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data, status) {
                    if (data.form) {
                        setFormView(main_identifier, data.form);
                    } else {
                        setEditableRenderedView(main_identifier, data.display);
                    }

                    if (data.additional_displays && data.additional_displays.length !== 0) {
                        for (let key in data.additional_displays) {
                            if (!data.additional_displays.hasOwnProperty(key)) continue;
                            setRenderedView("#" + key, data.additional_displays[key]);
                        }
                    }
                    createNoty('success', data.message);
                    setInputsToElementsForForm(main_identifier);
                    if (main_identifier === "#city-job-announcement-active-dates") {
                        initializeJobAnnouncementHasNoEndDate();
                    }
                    initializeCollection();
                },
                error: function(xhr, desc, err) {
                    if (xhr.responseJSON.form) {
                        createNoty('error', xhr.responseJSON.message);
                        setFormView(main_identifier, xhr.responseJSON.form);
                        if (main_identifier === "#city-job-announcement-active-dates") {
                            initializeJobAnnouncementHasNoEndDate();
                        }
                    }
                }
            });
        });

        $(main_identifier + " .ajax-form").show();
        $(main_identifier + '.title.cancel-btn').show();
    }

    function setRenderedView(main_identifier, data) {
        $(main_identifier).html(data);
    }

    function initializeCollection()
    {
        if ($('.work-history-collection').length) {
            $('.work-history-collection').collection({
                allow_up: false,
                allow_down: false,
                add: '<div class="my-2"><button href="#" class="collection-add btn btn-outline-primary btn-sm add-work-history" title="Add element">Add a Work History</button></div>',
                elements_selector: 'div.item',
                elements_parent_selector: '#work_histories_workHistories',
                add_at_the_end: true,
            });
        }

        if ($('.license-certifications-collection').length) {
            $('.license-certifications-collection').collection({
                allow_up: false,
                allow_down: false,
                add: '<div class="my-2"><button href="#" class="collection-add btn btn-outline-primary btn-sm add-license-certification" title="Add element">Add a License/Certification</button></div>',
                elements_selector: 'div.item',
                elements_parent_selector: '#key_qualifications_licenseCertifications',
                add_at_the_end: true,
            });
        }

        if ($('.education-collection').length) {
            $('.education-collection').collection({
                allow_up: false,
                allow_down: false,
                add: '<div class="my-2"><button href="#" class="collection-add btn btn-outline-primary btn-sm add-education" title="Add element">Add an Education</button></div>',
                elements_selector: 'div.item',
                elements_parent_selector: '#key_qualifications_education',
                add_at_the_end: true,
            });
        }

        if ($('.census-population-collection').length) {
            $('.census-population-collection').collection({
                allow_up: false,
                allow_down: false,
                add: '<div class="my-2"><button href="#" class="collection-add btn btn-outline-primary btn-sm add-census-population" title="Add element">Add a Census Population</button></div>',
                elements_selector: 'div.item',
                elements_parent_selector: '#agency_info_censusPopulations',
                add_at_the_end: true,
            });
        }
    }
    initializeCollection();

    function setEditableRenderedView(main_identifier, data) {
        $(main_identifier + ' .edit-btn').show();
        $(main_identifier + '.title.cancel-btn').hide();
        $(main_identifier).html(data);
        $(main_identifier + ' .edit-btn, ' + main_identifier + ' .cancel-btn').click(function(){
            $(main_identifier + ' .ajax-form').toggle();
            $(main_identifier + ' .editable').toggle();
            $(main_identifier + ' .edit-btn').toggle();
            $(main_identifier + ' .title.cancel-btn').toggle();
        });

        var collections = [];
        let name = main_identifier.replace('#', '');

        initializeSelect2s();
        initializeFormattedInputs();
        initializeCollectionAjaxForms(main_identifier, name, collections);
        initializeJobAnnouncementHasNoEndDate();

        if (main_identifier === "#city-job-announcement-active-dates") {
            initializeJobAnnouncementHasNoEndDate();
        }

        $(main_identifier + ' .ajax-form:not(.file-upload) form').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data, status) {
                    if (data.form) {
                        setFormView(main_identifier, data.form);
                    } else {
                        setEditableRenderedView(main_identifier, data.display);
                    }

                    if (data.additional_displays && data.additional_displays.length !== 0) {
                        for (let key in data.additional_displays) {
                            if (!data.additional_displays.hasOwnProperty(key)) continue;
                            setRenderedView("#" + key, data.additional_displays[key]);
                        }
                    }
                    createNoty('success', data.message);
                    setInputsToElementsForForm(main_identifier);

                    if (main_identifier === "#city-job-announcement-active-dates") {
                        initializeJobAnnouncementHasNoEndDate();
                    }

                    initializeCollection();
                },
                error: function(xhr, desc, err) {
                    if (xhr.responseJSON.form) {
                        createNoty('error', xhr.responseJSON.message);
                        setFormView(main_identifier, xhr.responseJSON.form);
                        if (main_identifier === "#city-job-announcement-active-dates") {
                            initializeJobAnnouncementHasNoEndDate();
                        }
                    }
                }
            });
        });

        $(main_identifier + ' .ajax-form').hide();
        $(main_identifier + '.title.cancel-btn').hide();
        $(main_identifier + ' .editable').show();
    }

    function setInputsToElementsForForm(main_identifier) {
        $(main_identifier + ' .edit-btn').show();
        $(main_identifier + '.title.cancel-btn').hide();
        $(main_identifier + ' .ajax-form').hide();
        $(main_identifier + ' .editable').show();
    }

    function initializeCollectionAjaxForms(main_identifier, name, collections = []) {
        /* Collection Ajax Form */
        if (collections.length > 0) {
            for(var collection_identifier in collections) {
                var $identifier = $(main_identifier + ' ul.collection.allow-add.' + collections[collection_identifier]);
                if ($identifier.length > 0) {
                    var $collectionHolder = $identifier;

                    var $addButton = $('<button type="button" class="add-item '+ collections[collection_identifier] +' btn btn-light"><i class="fa fa-plus"></i></button>');
                    $addButton.addClass(name + '-' + collections[collection_identifier]);
                    $addButton.data('form_id', main_identifier);
                    $addButton.data('field_id', collections[collection_identifier]);
                    var $newLinkLi = $('<li></li>').append($addButton);

                    // add the "add a tag" anchor and li to the tags ul
                    $collectionHolder.append($newLinkLi);

                    // count the current form inputs we have (e.g. 2), use that as the new
                    // index when inserting a new item (e.g. 2)
                    $collectionHolder.data('index', $collectionHolder.find(':input').length);

                    $addButton.on('click', function(e) {
                        // add a new tag form (see next code block)
                        var form_id = $(this).data('form_id');
                        var field_id = $(this).data('field_id');
                        var $collectionHolder = $(form_id + ' ul.collection.allow-add.' + field_id);
                        var $newLinkLi = $(this).parent();
                        addCollectionForm($collectionHolder, $newLinkLi);
                    });
                }
            }
        } else {
            var $identifier = $(main_identifier + ' ul.collection.allow-add');
            if ($identifier.length > 0) {
                var $collectionHolder = $identifier;

                var $addButton = $('<button type="button" class="add-item btn btn-light"><i class="fa fa-plus"></i></button>');
                $addButton.addClass(name);
                var $newLinkLi = $('<li></li>').append($addButton);

                // add the "add a tag" anchor and li to the tags ul
                $collectionHolder.append($newLinkLi);

                // count the current form inputs we have (e.g. 2), use that as the new
                // index when inserting a new item (e.g. 2)
                $collectionHolder.data('index', $collectionHolder.find(':input').length);

                $addButton.on('click', function(e) {
                    // add a new tag form (see next code block)
                    addCollectionForm($collectionHolder, $newLinkLi);
                });
            }
        }
    }



    function addCollectionForm($collectionHolder, $newLinkLi) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');

        // get the new index
        var index = $collectionHolder.data('index');

        var newForm = prototype;
        // You need this only if you didn't set 'label' => false in your tags field in TaskType
        // Replace '__name__label__' in the prototype's HTML to
        // instead be a number based on how many items we have
        // newForm = newForm.replace(/__name__label__/g, index);

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        $newFormLi.append($removeButton);
        $newLinkLi.before($newFormLi);

        $('.remove-item').click(function(e) {
            e.preventDefault();

            $(this).parent().remove();

            return false;
        });
    }

    // City-Profile
    initializeAjaxForm('city-profile-about');
    initializeAjaxForm('city-profile-name');
    initializeAjaxForm('city-profile-contact-info');
    initializeAjaxForm('city-profile-agency-info');
    initializeAjaxForm('city-profile-city-links');
    initializeAjaxForm('city-profile-seal-image');
    initializeAjaxForm('city-profile-banner-image');

    //Job Announcements
    initializeAjaxForm('city-job-announcement-status');
    initializeAjaxForm('city-job-announcement-announcement');
    initializeAjaxForm('city-job-announcement-alert');
    initializeAjaxForm('city-job-announcement-location');
    initializeAjaxForm('city-job-announcement-application-deadline');
    initializeAjaxForm('city-job-announcement-closed-promotional');
    initializeAjaxForm('city-job-announcement-application-url');
    initializeAjaxForm('city-job-announcement-wage-salary');
    initializeAjaxForm('city-job-announcement-benefits');
    initializeAjaxForm('city-job-announcement-active-dates');

    // Resume
    initializeAjaxForm('job-seeker-resume-summary');
    initializeAjaxForm('job-seeker-resume-key-qualifications');
    initializeAjaxForm('job-seeker-resume-work-histories');
    initializeAjaxForm('job-seeker-resume-settings');
    initializeAjaxForm('job-seeker-resume-job-seeker');
    initializeAjaxForm('job-seeker-resume-interest-profile');

    // Improve filter field UX
    function initializeSearchFilter(selector) {
        var $this = selector;
        var form = $this.closest('form');
        var $state = form.find('.js-state').val();
        var $counties = form.find('.js-counties').val();
        var $cities = form.find('.js-cities').val();

        var isLinkSearch = false;
        if ($this.attr('id') == 'homepage_link_search_state') {
            isLinkSearch = true;
        }


        $.ajax({
            url: "/search/filter",
            type: "GET",
            dataType: "JSON",
            data: {
                state: $state,
                counties: $counties,
                cities: $cities,
                isLinkSearch: isLinkSearch
            },
            success: function (data) {
                /*** CIT-663 Filter JobTitle based on State, or County or City or All ***/
                if ($state) {
                    $('#homepage_job_search_jobTitleNames').data('req_params', { stateId: 'homepage_job_search[state]'});
                }
                if ($counties) {
                    $('#homepage_job_search_jobTitleNames').data('req_params', { countyId: 'homepage_job_search[county]'});
                }
                $('select#search_filter_jobTitleNames').find('option').remove();
                $.each(data, function (key, group) {
                    if (
                        (
                            $this.is('.js-state')
                            || ($this.is('.js-counties') && ['cities', 'jobTitleNames'].indexOf(key) != -1)
                            || ($this.is('.js-cities') && ['jobTitleNames'].indexOf(key) != -1)
                        )
                        && key != 'state'
                    ) {
                        var $select = $('select.js-' + key);

                        if(key === 'counties') {
                            $select = $this.closest('form').find($select);
                        }

                        // Remove current options
                        $select.find('option').each(function(item) {
                            if ($(this).attr('value')) {
                                $(this).remove();
                            }
                        });

                        // Add empty...
                        //$select.append('<option value></option>');

                        // Add value...
                        $.each(group, function (k, item) {
                            $select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });

                        // Select2
                        var options = {};
                        var placeholder = '';

                        if (placeholder) {
                            options['placeholder'] = placeholder;
                        }

                        $select.select2(options);
                    }
                });
            },
            error: function (err) {

            }
        });
    }
    // Improve filter field UX
    $('select.js-state, select.js-counties, select.js-cities').on('change', function () {
        initializeSearchFilter($(this));
    });

    // CIT-782: Filter field UX based State Default on Home Page.
    $('#homepage_job_search_state').each(function () {
        initializeSearchFilter($(this));
    });

    $('#homepage_link_search_state').each(function () {
        initializeSearchFilter($(this));
    });

    //CIT-470: In JobSeeker Resume, City Select2 did not paginate.
    function citySearchSelect2(thisSelect, url = null) {

        if ( ! url) {
            url = thisSelect.data('ajax--url');
        }

        var placeholder = thisSelect.attr('placeholder');

        thisSelect.select2({
            allowClear: true,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: data.items ? data.items.length == data.per_page : false
                        },
                    };
                },
                cache: true
            },
            placeholder: placeholder ? placeholder : 'Select an option',
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 3,
            templateResult: formatData,
            templateSelection: formatDataSelection
        });
    }

    function initializeSearchLocation() {
        $('select.city-county-state-select2').each(function() {
            var $this = $(this);
            var url = (typeof $this.data("city-registration") !== "undefined") ? "/search/location?city_registration=true" : "/search/location";
            citySearchSelect2($this, url);
        });
    }

    function formatData(repo) {
        if (repo.loading) {
            return repo.text;
        }

        if (repo.cityString) {
            var markup = "<span data-id='" + repo.id + "'>" + repo.cityString + "</span>";
        }
        else if (repo.name) {
            var markup = "<span data-id='" + repo.id + "'>" + repo.name + "</span>";
        }

        return markup;
    }

    function formatDataSelection(repo) {
        if (repo.cityString) {
            return repo.cityString;
        } else if (repo.name) {
            return repo.name;
        } else {
            return repo.text;
        }
    }
    //end CIT-470

    initializeSearchLocation();

    $('.job-detail-sliders').slick({
        loop: true,
        center: true,
        items: 1,
        margin: 30,
        autoplay: true,
        autoplaySpeed: 3000,
        dots:true,
        nav:true,
        arrows: false,
        adaptiveHeight: true,

    });

    $('.btn-homepage-job-search').click(function (e) {
        e.preventDefault();

        var form = $(this).closest('form');
        var jobTitles = form.find('.js-jobTitles').val();
        var state = form.find('.js-state').val();
        var county = form.find('.js-counties').val();

        var url = form.attr('action') + '?';

        if (jobTitles && jobTitles.length) {

            for (var i = 0; i < jobTitles.length; i++) {
                url += 'search_filter[jobTitleNames][]=' + jobTitles[i] + '&';
            }
        }

        if (state) {
            url += 'search_filter[state]=' + state + '&';
        }

        if (county) {
            url += 'search_filter[counties][]=' + county + '&';
        }

        url += '#announcement';

        window.location.href = encodeURI(url);
    });

    /** CIT-483: update the JobAnnouncement AssignedTo using an AJAX function */
    $('select.job-announcement-assignedto').change(function (e) {
        var url = $(this).data('url') + '/' + $(this).val();

        $.ajax({
            type: 'POST',
            url: url,
            success: function (data, status) {
                createNoty('success', data.message);
            },
            error: function (xhr, desc, err) {
                if (xhr.responseJSON && xhr.responseJSON.form) {
                    createNoty('error', xhr.responseJSON.message);
                } else {
                    createNoty('error', xhr.statusText);
                }
            }
        });
    });

    function initializeJobAnnouncementHasNoEndDate() {
        var hasNoEndDate        = $('input.ajax-ja-has-no-end-date');
        var hasNoEndDateDeadline = $('input.ajax-ja-deadline-has-no-end-date');
        var endDatesDescription = $('#active_dates_endDateDescription');
        var noEndDateDescription = $('input#active_dates_endDateDescription');

        var appDeadlineEndDatesDescription = $('#application_deadline_endDateDescription');
        if (hasNoEndDate.length) {

            hasNoEndDate.off('change').on('change', function() {
                if (hasNoEndDate.is(':checked')) {
                    $('.ajax-ja-ends-on').closest('div').css('display', 'none');
                    var form = $('.ajax-ja-ends-on').closest('form');
                    form.attr('novalidate', 'novalidate');
                    // Check it in Application Deadline
                    if (!hasNoEndDateDeadline.is(':checked')) {
                        hasNoEndDateDeadline.prop("checked", true).trigger('change');
                    };
                    $('#city-job-announcement-application-deadline .checkmark').addClass('complete');
                    $('#application-deadline-continuous').removeClass('d-none');
                    $('#application-deadline-date').addClass('d-none');
                    endDatesDescription.closest('div').css('display', 'block');
                    appDeadlineEndDatesDescription.closest('div').css('display', 'block');
                }
                else {
                    $('.ajax-ja-ends-on').closest('div').css('display', 'block');
                    var form = $('.ajax-ja-ends-on').closest('form');
                    form.attr('novalidate', '');
                    if ($("#application_deadline_applicationDeadline")[0].value == "") {
                        $('#city-job-announcement-application-deadline .checkmark').removeClass('complete');
                    }
                    if (hasNoEndDateDeadline.is(':checked')) {
                        hasNoEndDateDeadline.prop("checked", false).trigger('change');
                    }
                    $('#application-deadline-continuous').addClass('d-none');
                    $('#application-deadline-date').removeClass('d-none');
                    endDatesDescription.closest('div').css('display', 'none');
                    appDeadlineEndDatesDescription.closest('div').css('display', 'none');
                }
            }).trigger('change');
        }

        var formatDate = function(date) {
            return date.getDate() + "/" + date.getMonth() + "/" + date.getFullYear() + " " +  ('0' + date.getHours()).slice(-2) + ":" + ('0' + date.getMinutes()).slice(-2) + ":" + ('0' + date.getSeconds()).slice(-2) + ' ' + (date.getHours() < 12 ? 'AM' : 'PM');
        }

        hasNoEndDateDeadline.off('change').on('change', function() {
            if (hasNoEndDateDeadline.is(':checked')) {
                // Check hasNoEndDate
                if (!hasNoEndDate.is(':checked')) {
                    hasNoEndDate.prop( "checked", true ).trigger('change');
                }
                // Hide Date Input
                let target = 'application_deadline_applicationDeadline';
                $('label[for="' + target + '"]').hide();
                $("#application_deadline_applicationDeadline").addClass('d-none');
                // Display the value of endDateDescription
                $('#end-date-stored')[0].innerHTML = " " + noEndDateDescription.val();

            }
            else {
                $('.ajax-ja-ends-on').closest('div').css('display', 'block');
                // Uncheck hasNoEndDate
                if (hasNoEndDate.is(':checked')) {
                    hasNoEndDate.prop( "checked", false ).trigger('change');
                }
                // Show Date Input
                let target = 'application_deadline_applicationDeadline';
                $('label[for="' + target + '"]').show();
                $("#application_deadline_applicationDeadline").removeClass('d-none');

                // Show value of Date
                var timestamp = $(active_dates_endsOn).val();
                if (timestamp !== "") {
                    var date = new Date(timestamp);
                    $('#end-date-stored')[0].innerHTML = formatDate(date);
                } else {
                    $('#end-date-stored')[0].innerHTML = 'XX/XX/XXXX';
                }
            }
        }).trigger('change');

        if (noEndDateDescription.length) {
            noEndDateDescription.off('change').on('change', function(e) {
                $('#application-deadline-continuous')[0].innerHTML = e.target.value;
                $('#end-date-stored')[0].innerHTML = e.target.value;
            });
        }
    }


    $('.btn-job-title-view-summary').off('click').on('click', function() {
        var url = $(this).data('url');

        $.ajax({
            type: 'GET',
            url: url,
            success: function (data, status) {
                $('#submitted-interest-summary .modal-body').html(data);
                $('#submitted-interest-summary').modal('show');
            },
            error: function (xhr, desc, err) {
                if (xhr.responseJSON && xhr.responseJSON.form) {
                    createNoty('error', xhr.responseJSON.message);
                } else {
                    createNoty('error', xhr.statusText);
                }
            }
        });
    });

    if ($('a.nav-link#job-title-nav').length) {
        $('a.nav-link#job-title-nav, a.nav-link#job-announcement-nav').click(function() {
            location.hash = $(this).data('type');

            var action = $('.job-search-filter-section form').attr('action');
            action = action.split('#')[0] + '#' + $(this).data('type');
            $('.job-search-filter-section form').attr('action', action);
        });

        var hash = location.hash.replace('#','');
        $('a.nav-link#' + hash + '-nav').trigger('click');

    }

    // CIT-701 Filter JobTitles by Counties and Job Level //
    $('#job_seeker_profile_interestedCounties, #job_seeker_profile_interestedJobLevels').on('change', function () {
        var $counties  = $('#job_seeker_profile_interestedCounties').val();
        var $jobLevels = [];
        $('#job_seeker_profile_interestedJobLevels').find('input:checked').each(function () {
            $jobLevels.push($(this).val());
        });

        $('#job_seeker_profile_interestedJobTitleNames').find('option').remove();

        if ($counties.length > 0 && $jobLevels.length > 0) {
            $.ajax({
                url: "/filter/job-titles",
                type: "GET",
                dataType: "JSON",
                data: {
                    counties: $counties,
                    jobLevels: $jobLevels
                },
                success: function (data) {
                    $.each(data, function (id, name) {
                        $('#job_seeker_profile_interestedJobTitleNames').append('<option value="' + id + '">' + name + '</option>');
                    });
                },
                error: function (err) {

                }
            });
        }
    });

    // If there is Job Titles
    if ($('#job_seeker_profile_interestedJobCategoryNotGenerals').val() !== null && $('#job_seeker_profile_interestedJobCategoryNotGenerals').val() !== "") {
        $('.job-category > a').addClass('btn-danger');
        $('.job-titles > a').removeClass('btn-danger');
    } else {
        $('.job-category > a').removeClass('btn-danger');
        $('.job-titles > a').addClass('btn-danger');
    }

    // In Job Seeker Registration Step 3
    var $url = window.location.pathname;
    if ($url == '/registration/job-seeker/step/three') {
        $('.job-category > a').addClass('btn-danger');
        $('.job-titles > a').removeClass('btn-danger');
        $('.job-category-general .job-category').find('a.btn-primary').trigger('click');
        $('#job_seeker_profile_interestedJobCategoryNotGenerals').prop('required', 'required');
    }

    $('#job_seeker_profile_interestedJobCategoryNotGenerals, #job_seeker_profile_interestedJobCategoryGenerals').on('select2:selecting', function(e) {
        // Clear selection of JobTitle if Category chosen
        if ($('#job_seeker_profile_interestedJobTitleNames').val() !== []) {
            $('#job_seeker_profile_interestedJobTitleNames').val([]).trigger('change');
        }
    });

    $('#job_seeker_profile_interestedJobTitleNames').on('select2:selecting', function(e) {
        // Clear selection of Category if JobTitle chosen
        if ($('#job_seeker_profile_interestedJobCategoryNotGenerals').val() !== null) {
            $('#job_seeker_profile_interestedJobCategoryNotGenerals').val(null).trigger('change');
        }

        if ($('#job_seeker_profile_interestedJobCategoryGenerals').val() !== null) {
            $('#job_seeker_profile_interestedJobCategoryGenerals').val(null).trigger('change');
        }
    });

    $('label[for=job_seeker_profile_interestedJobTitleNames]').addClass('required');
    $('label[for=job_seeker_profile_interestedJobCategoryNotGenerals]').addClass('required');

    // Remove Required JobCategory and JobTitle.
    $('.job-category-general .btn-primary').on('click', function () {

        // JobTitle show
        $('#collapseJobTitle.collapse').on('show.bs.collapse', function () {
            $('#collapseCategory').collapse('hide');
            $('.job-category > a').removeClass('btn-danger');
            $('.job-titles > a').addClass('btn-danger');
            $('#job_seeker_profile_interestedJobCategoryNotGenerals').removeAttr('required');
            $('#job_seeker_profile_interestedJobTitleNames').prop('required', 'required');
        });

        // JobCategory show
        $('#collapseCategory.collapse').on('show.bs.collapse', function () {
            $('#collapseJobTitle').collapse('hide');
            $('.job-category > a').addClass('btn-danger');
            $('.job-titles > a').removeClass('btn-danger');
            $('#job_seeker_profile_interestedJobTitleNames').removeAttr('required');
            $('#job_seeker_profile_interestedJobCategoryNotGenerals').prop('required', 'required');
        });
    });

    // CIT-712: JobAlertSetting
    $('.getting-started-footer .btn-primary').on('click', function () {

        // Resume show
        $('.getting-started-section #getting-started').on('show.bs.collapse', function () {
            $('.getting-started-section #job-alert-setting').collapse('hide');
        });

        // Alert Setting show
        $('.getting-started-section #job-alert-setting').on('show.bs.collapse', function () {
            $('.getting-started-section #getting-started').collapse('hide');
        });

        $('.getting-started-section #getting-started').on('show.bs.collapse', function () {
            $('.btn-resume .btn-icon .fa-angle-down').css('display', 'none');
            $('.btn-resume .btn-icon .fa-angle-up').css('display', 'inline');
        });

        $('.getting-started-section #getting-started').on('hide.bs.collapse', function () {
            $('.btn-resume .btn-icon .fa-angle-up').css('display', 'none');
            $('.btn-resume .btn-icon .fa-angle-down').css('display', 'inline');
        });
    });

    if ($('.getting-started-section #getting-started').hasClass('show')) {
        $('.btn-resume .btn-icon .fa-angle-up').css('display', 'inline');
    } else {
        $('.btn-resume .btn-icon .fa-angle-down').css('display', 'inline');
    }

    // Drawer Saved Search in Job Search page
    $('.btn-more-job-categories').on('click', function () {
        $('#search_filter_jobCategories').toggleClass('show');
        $(this).toggleText('More', 'Hide');
    });

    // Toggle Text
    jQuery.fn.extend({
        toggleText: function (a, b){
            var that = this;
            if (that.text() != a && that.text() != b){
                that.text(a);
            }
            else
            if (that.text() == a){
                that.text(b);
            }
            else
            if (that.text() == b){
                that.text(a);
            }
            return this;
        }
    });

    // CIT-721: button on the "Default Search Criteria" that when clicked toggles the drawer open.
    $('.job-search-filter-section .link-edit-saved-search').on('click', function () {
        $('.btn-saved-search').trigger('click');
    });

    // CIT-764: The selection of job levels should be checkboxes, Maximum Selection Length: 2
    $('#job_seeker_profile_interestedJobLevels').on('change', function () {
        if ($(this).find('input:checked').length >= 2) {
            $(this).find('input:not(:checked)').prop('disabled', true);
        } else {
            $(this).find('input:not(:checked)').prop('disabled', false);
        }
    });

    if ($('#job_seeker_profile_interestedJobLevels').find('input:checked').length >= 2) {
        $('#job_seeker_profile_interestedJobLevels').find('input:not(:checked)').prop('disabled', true);
    }

    // CIT-748: In City Search, This form control has no LABEL and no programmatically determined name.
    $('label[for=search_filter_state]').on('click', function () {
        $('#search_filter_state').select2('open');
    });

    $('label[for=search_filter_counties]').on('click', function () {
       $('#search_filter_counties').select2('open');
    });

    $('label[for=search_filter_cities]').on('click', function () {
       $('#search_filter_cities').select2('open');
    });

    $('label[for=search_filter_jobTitleNames]').on('click', function () {
       $('#search_filter_jobTitleNames').select2('open');
    });


    // Save Search
    if ($('.save-search-now-btn').length) {
        $('.save-search-now-btn').off('click').on('click', function(e) {
            e.preventDefault();

            var searchName = $('.save-search-name').val();
            if (searchName) {
                // job search
                if ($('.main-job-search-btn').length) {
                    var searchBtn = $('.main-job-search-btn');
                }
                // city search
                else {
                    var searchBtn = $('.main-city-search-btn');
                }
                searchBtn.closest('form').find('.should-save-search').val(searchName);
                searchBtn.trigger('click');

            }
        });

        $('.should-save-search').val('');
    }

    $('.btn-close-save-search').off('click').on('click', function(e) {
        $('.btn-collapse-save-search-form').click();
    });

    // CIT-775: In Forgot Password mechanism, if the email doesn't exist return messages error.
    $('input.btn-reset-password').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $email = $btn.closest('form').find('#username').val();

        $.ajax({
            type: 'GET',
            dataType: "JSON",
            url: '/check-email-exist',
            data: {
                email: $email
            },
            success: function (data) {
                if (data.success) {
                    // email exist
                    $btn.closest('form').submit();
                } else {
                    // email doesn't exist
                    createNoty('error', 'The email address/username you have provided does not match an account in our system');
                }
            },
            error: function (err) {

            }
        });
    });

    // In JobSeeker Dashboard button Edit Profile link to Job Search and open Drawer Default Saved Search.
    if (window.location.pathname == '/job/search' && window.location.href.includes('#edit-profile')) {
        $('a.btn-saved-search').trigger('click');
    }

    function setAttrTooltip(selector) {
        selector.attr('data-content', 'Maximum number of categories is 15');
        selector.attr('data-toggle', 'popover');
        selector.attr('title', 'Categories selected is limited');
    }

    function removeAttrTooltip(selector) {
        selector.removeAttr('data-content');
        selector.removeAttr('data-toggle');
        selector.removeAttr('title');
    }

    function checkLimitOfCategoriesSelected(checkbox, parent) {
        if(parent.find('input[type="checkbox"]:checked').length >= 15) {
            checkbox.each(function () {
                if(! $(this).is(':checked')) {
                    setAttrTooltip($(this));
                    setAttrTooltip($(this).parent().find('label'));
                    initializePopover($(this));
                    initializePopover($(this).parent().find('label'));
                }
            });
            $(checkbox).on('click', function (e) {
                if($(this).is(':checked')) {
                    e.preventDefault();
                }
            });
        } else {
            removeAttrTooltip(checkbox);
            removeAttrTooltip(checkbox.parent().find('label'));
            removePopover(checkbox);
            removePopover(checkbox.parent().find('label'));
            $(checkbox).off('click');

            $(checkbox).closest('form').submit();
        }
    }

    if ($('.search-filter-job-categories').length) {
        var checkbox = $('.search-filter-job-categories .form-check input[type="checkbox"]');
        var parent   = $('.search-filter-job-categories');
        checkbox.change(function() {
            checkLimitOfCategoriesSelected(checkbox, parent);
        });
    }

    $('.btn-set-gdpr-cookie').on('click', function (e) {
        var cookie = document.cookie;
        document.cookie = 'gdpr_accepted=true;' + cookie;
        $('.check-gdpr-acceptance').hide();
    });

    function changeEndDateDescription(firstEndDateDescription, secondEndDateDescription) {
        firstEndDateDescription.on('keyup', function () {
            secondEndDateDescription.val($(this).val());
        });
    }

    changeEndDateDescription($('#active_dates_endDateDescription'), $('#application_deadline_endDateDescription'));
    changeEndDateDescription($('#application_deadline_endDateDescription'), $('#active_dates_endDateDescription'));

    function jobAnnouncementViewCounter() {
        $('.job-announcement-view-counter').on('click', function (e) {
            e.preventDefault();

            var $this = $(this);
            var jobAnnouncementId = $this.data('job-announcement-id');
            $.ajax({
                url: "/count/job-announcement-view",
                type: "POST",
                dataType: "JSON",
                data: {
                    'jobAnnouncementId': jobAnnouncementId
                },
                success: function (data) {
                    window.location.href = $this.attr('href');
                },
                error: function (err) {

                }
            });
        });
    }

    jobAnnouncementViewCounter();

    //CIT-931: Add drag and drop and hide on profile field to departments. Update in Admin / City User Front End
    if ($('#city-department-list').length) {
        $('#city-department-list').sortable({
            start: function (event, ui) {
            },
            stop: function (event, ui) {
                var departmentIds = [];
                var orderByNumbers = [];

                $(event.target).find('.city-department-row').each(function () {
                    departmentIds.push($(this).data('department-id'));
                    orderByNumbers.push($(this).data('order'));
                });

                orderByNumbers = orderByNumbers.sort(function (a, b) {
                    return a - b
                });

                $.ajax({
                    url: '/city/update/department/sortable',
                    type: 'POST',
                    dataType: "JSON",
                    data: {
                        'departmentIds': departmentIds,
                        'orderByNumbers': orderByNumbers,
                    },
                    success: function (data) {
                        // createNoty('success', data.message);
                    },
                    error: function (err) {

                    }
                });
            }
        });
        $("#city-department-list").disableSelection();
    }

    $('.city-link-type').on('click', function (e) {
        e.preventDefault();
        var btnLink = $(this);
        $.ajax({
            url: '/count/'+ btnLink.data('url-id') +'/city-link-type',
            type: 'POST',
            dataType: "JSON",
            success: function (data) {
                var newWindow = window.open(btnLink.attr('href'), '_blank');
                newWindow.focus();
            },
            error: function (err) {
                console.log(err);
            }
        });
    });

    //end CIT-931
});
