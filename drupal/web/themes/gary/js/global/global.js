
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
