jQuery.noConflict();
(function($) {
    'use strict';
    $(function() {
        $('#barcodediv').hide();
        $('#barcode_toggler').on('click', function () {
            $('.barcodediv').slideToggle();
        });

    });

})(jQuery);