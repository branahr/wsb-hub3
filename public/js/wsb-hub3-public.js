jQuery.noConflict()
;(function ($) {
  'use strict'
  $(function () {
    $('#barcodediv').hide()
    $('#barcode_toggler').on('click', function () {
      $('.barcodediv').slideToggle()
    })
    $('#billing_company').on('blur', function () {
      $(document.body).trigger('update_checkout')
    })

    $(document.body).on('updated_checkout', function () {
      if ($('#payment_method_bacs').is(':checked')) {
        $('#_wsb_barcode_iban_field').show()
      } else {
        $('#_wsb_barcode_iban_field').hide()
      }
    })

    $('form.checkout').on(
      'change',
      'input[name^="payment_method"]',
      function () {
        $(document.body).trigger('update_checkout')
      }
    )
  })
})(jQuery)
