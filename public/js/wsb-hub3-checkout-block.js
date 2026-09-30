(function () {
  "use strict";
  var el = window.wp.element.createElement;
  var useEffect = window.wp.element.useEffect;
  var useSelect = window.wp.data.useSelect;
  var dispatch = window.wp.data.dispatch;
  var blocksData = window.wc.wcBlocksData;
  var settings = window.wc.wcSettings.getSetting("wsb-hub3_data", {});
  var accounts = settings.accounts || [];

  // With one account there is nothing to choose; the server saves it automatically.
  if (accounts.length < 2) {
    return;
  }

  var choose = function (iban) {
    dispatch(blocksData.CHECKOUT_STORE_KEY).setExtensionData("wsb-hub3", {
      iban: iban,
    });
  };

  var IbanChoice = function () {
    var state = useSelect(function (select) {
      var extensions = select(blocksData.CHECKOUT_STORE_KEY).getExtensionData();
      return {
        method: select(blocksData.PAYMENT_STORE_KEY).getActivePaymentMethod(),
        iban: (extensions["wsb-hub3"] || {}).iban,
      };
    }, []);

    useEffect(
      function () {
        if (!state.iban) {
          choose(accounts[0].iban);
        }
      },
      [state.iban],
    );

    if (state.method !== "bacs") {
      return null;
    }

    // WooCommerce's own title and radio control, so it looks like the shipping and payment options.
    return el(
      "div",
      { className: "wsb-hub3-iban-choice" },
      el(
        "h2",
        {
          className:
            "wc-block-components-title wc-block-components-checkout-step__title",
        },
        settings.label,
      ),
      el(window.wc.blocksComponents.RadioControl, {
        id: "wsb-hub3-iban",
        selected: state.iban || accounts[0].iban,
        onChange: choose,
        highlightChecked: true,
        options: accounts.map(function (account) {
          return {
            value: account.iban,
            label: account.name,
            description: account.ibanFormatted,
          };
        }),
      }),
    );
  };

  window.wc.blocksCheckout.registerCheckoutBlock({
    metadata: {
      name: "wsb-hub3/iban-choice",
      parent: ["woocommerce/checkout-payment-block"],
    },
    component: IbanChoice,
    // Rendered on existing checkout pages without editing them in the block editor.
    force: true,
  });
})();
