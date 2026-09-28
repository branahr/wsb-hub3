(function ($) {
  "use strict";
  $(".wsb-color-field").wpColorPicker();

  var masks = {
    digits: function (value) {
      return value.replace(/\D/g, "");
    },
    upper: function (value) {
      return value.toUpperCase().replace(/[^A-Z]/g, "");
    },
    iban: function (value) {
      return value
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "")
        .slice(0, 21);
    },
  };
  $("[data-wsb-mask]").on("input", function () {
    var mask = masks[$(this).data("wsb-mask")];
    var value = mask ? mask(this.value) : this.value;
    if (value !== this.value) {
      this.value = value;
    }
  });

  var $purpose = $("#wsb_hub3_payment_purpose");
  if ($purpose.length && window.wsbHub3) {
    var $list = $('<datalist id="wsb-hub3-purpose-codes"></datalist>');
    $.each(wsbHub3.purposeCodes, function (code, label) {
      $list.append($("<option>").attr("value", code).text(label));
    });
    $list.insertAfter($purpose);
  }

  $("[data-wsb-counter][maxlength]").each(function () {
    var $field = $(this);
    var max = parseInt($field.attr("maxlength"), 10);
    var $counter = $(
      '<span class="wsb-hub3-counter" aria-live="polite"></span>',
    ).insertAfter($field);
    var update = function () {
      // Count code points, matching PHP's mb_strlen().
      var length = Array.from($field.val()).length;
      $counter
        .text(length + " / " + max)
        .toggleClass("wsb-hub3-counter-over", length > max);
    };
    $field.on("input", update);
    update();
  });

  var $model = $("#wsb_hub3_receiver_model");
  var $preview = $("#wsb-hub3-reference-preview");
  if ($model.length && $preview.length && window.wsbHub3) {
    var fields = {
      format: $("#wsb_hub3_receiver_reference"),
      date_format: $("#wsb_hub3_receiver_reference_date"),
      prefix: $("#wsb_hub3_receiver_reference_prefix"),
      sufix: $("#wsb_hub3_receiver_reference_sufix"),
    };
    var $referenceRows = $();
    $.each(fields, function (key, $field) {
      $referenceRows = $referenceRows.add($field.closest("tr"));
    });
    var timer;
    var request;

    var render = function (data) {
      $preview
        .empty()
        .toggleClass("wsb-hub3-preview-error", data.problems.length > 0);
      $preview.append($("<code>").text(data.reference));
      if (data.max) {
        $preview.append(
          $('<span class="wsb-hub3-counter"></span>')
            .text(data.length + " / " + data.max)
            .toggleClass("wsb-hub3-counter-over", data.length > data.max),
        );
      }
      $.each(data.problems, function (i, problem) {
        $preview.append(
          $('<span class="wsb-hub3-preview-problem"></span>').text(problem),
        );
      });
      $preview.append($('<span class="description"></span>').text(data.note));
    };

    var refresh = function () {
      $referenceRows.toggle($model.val() !== "99");
      clearTimeout(timer);
      timer = setTimeout(function () {
        if (request) {
          request.abort();
        }
        var data = {
          action: "wsb_hub3_reference_preview",
          _ajax_nonce: wsbHub3.nonce,
          model: $model.val(),
        };
        $.each(fields, function (key, $field) {
          data[key] = $field.val();
        });
        request = $.post(wsbHub3.ajaxUrl, data).done(function (response) {
          if (response && response.success) {
            render(response.data);
          }
        });
      }, 300);
    };

    $model.on("change", refresh);
    $.each(fields, function (key, $field) {
      $field.on("input change", refresh);
    });
    refresh();
  }
})(jQuery);
