define(
    [
        'jquery',
        'underscore',
        'mage/template'
    ],
    function ($, _, mageTemplate) {
        'use strict';

        return {
          
            build: function (formData) {
                console.log(formData);
                console.log(2222);

                var formTmpl = mageTemplate('<form action="<%= data.action %>" id="network_payment_form"' +
                    ' method="POST" hidden enctype="application/x-www-form-urlencoded">' +
                            '<input value=\'<%= data.fields %>\' name="requestParameter" type="hidden">' +
                    '</form>');

                return $(formTmpl({
                    data: {
                        action: formData.action,
                        fields: formData.fields
                    }
                })).appendTo($('[data-container="body"]'));
            }

        };
    }
);
