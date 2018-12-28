
(function ($, Drupal) {
  Drupal.gary = {
    breakpoints: {
      medium: 850,
      large: 1100,
    }
  };

  $(document).ready(function() {
    Drupal.gary.appState = new Drupal.gary.AppStateModel({
      scrollTop: $(window).scrollTop(),
    });

    Drupal.gary.mobileMenu = new Drupal.gary.MobileMenuView({
      model: Drupal.gary.appState,
    });

    //init qtip implementation
    // Grab all elements with the class "hasTooltip"
    $('.tooltip').each(function() { // Notice the .each() loop, discussed below
        $(this).qtip({
            content: {
                text: $(this).children('.tooltip-content').html()
            },
            position: {
                target: 'mouse', // Position it where the click was...
                adjust: { mouse: false } // ...but don't follow the mouse
            } // Use the "div" element next to this for the content
        });
    });


    // Drupal.gary.globalView = new Drupal.gary.GlobalView({
    //   model: Drupal.gary.appState,
    // });
    //
    // Drupal.gary.modal = new Drupal.gary.ModalView({
    //   model: Drupal.gary.appState,
    //   el: '#gary-modal',
    // });

  });

})(jQuery, Drupal);
