const $ = require('jquery');
require('select2');

jQuery(document).ready(function() {
// Improve filter field UX
    var searchFilterForm = $('form[name="account_information"]');
    if (searchFilterForm.length > 0) {
        searchFilterForm.find('.js-department').on('change', function () {
            var $this = $(this);
            var $department = $('.js-department').val();

            $.ajax({
                url: "/search/job-titles",
                type: "GET",
                dataType: "JSON",
                data: {
                    department: $department,
                },
                success: function (data) {
                    $.each(data, function (key, group) {
                        if (
                            (
                                $this.is('.js-department')
                            )
                            && key !== 'department'
                        ) {
                            var $select = $('#account_information_' + key);

                            // Remove current options
                            $select.html('');

                            // Add empty...
                            $select.append('<option value></option>');

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
        });
    }
});