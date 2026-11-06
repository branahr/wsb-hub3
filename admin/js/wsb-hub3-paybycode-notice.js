(function ($) {
  "use strict";

  function prepareNotice() {
    var $notice = $("#wsb-hub3-paybycode-notice");
    if (!$notice.length || $notice.find(".notice-dismiss").length) {
      return;
    }
    var $form = $notice.find("form");
    var label = $form.find("button").text();
    var $close = $('<button type="button" class="notice-dismiss"></button>')
      .attr("aria-label", label)
      .appendTo($notice.addClass("is-dismissible"));

    $form.on("submit", function (event) {
      event.preventDefault();
      var $buttons = $notice.find("button").prop("disabled", true);
      $notice.find(".wsb-hub3-notice-error").prop("hidden", true);
      $.post(wsbHub3Notice.ajaxUrl, $form.serialize())
        .done(function (response) {
          if (response.success) {
            $notice.remove();
          } else {
            $notice.find(".wsb-hub3-notice-error").prop("hidden", false);
          }
        })
        .fail(function () {
          $notice.find(".wsb-hub3-notice-error").prop("hidden", false);
        })
        .always(function () {
          $buttons.prop("disabled", false);
        });
    });
    $close.on("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      $form.trigger("submit");
    });
  }

  function refreshNotice() {
    $.get(wsbHub3Notice.ajaxUrl, {
      action: "wsb_hub3_paybycode_notice",
      nonce: wsbHub3Notice.nonce,
    }).done(function (response) {
      if (response.success && response.data.html && !$("#wsb-hub3-paybycode-notice").length) {
        $(".wrap").first().prepend(response.data.html);
        prepareNotice();
      }
    });
  }

  $(function () {
    prepareNotice();
    var params = new URLSearchParams(window.location.search);
    var plugins = (params.get("plugins") || params.get("plugin") || "").split(",");
    if (/\/update\.php$/.test(window.location.pathname) && plugins.indexOf(wsbHub3Notice.plugin) !== -1) {
      refreshNotice();
    }
  });
  $(document).on("wp-plugin-update-success", function (event, response) {
    if (response && response.plugin === wsbHub3Notice.plugin) {
      refreshNotice();
    }
  });
})(jQuery);
