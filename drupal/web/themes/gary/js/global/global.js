
(function ($, Drupal) {
  Drupal.gary = {
    breakpoints: {
      medium: 850,
      large: 1100,
    }
  };

  $.fn.bounceIn = function() {
    TweenMax.fromTo(this, .5, {scale: .9, opacity: 0}, {scale: 1, opacity: 1});
  }

  //gsap fade functions
  $.fn.garyFadeOut = function(sec = 2) {
    TweenMax.fromTo(this, sec, {opacity: 1, x: -50}, {opacity: 0, x: 0});
  }
  $.fn.garyFadeIn = function(sec = 2) {
    TweenMax.fromTo(this, sec, {opacity: 0, x: 50}, {opacity: 1, x: 0});
  }

  //set or remove a message
  $.fn.setMessage = function(message) {
      console.log('++'+message.length);
      if (message.length == 0) {
        $('#alert-msg').remove();
        return;
      }
      this.after('<div id="alert-msg">'+message+'</div>');
      return;
  }

  //PREVENT SPECIAL CHARACTERS
  $.fn.cleanSpecialChars = function() {
    this.bind('keypress', function (event) {
      var regex = new RegExp("^[a-z A-Z0-9]+$");
      var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
      if (!regex.test(key)) {
        event.preventDefault();
        return false;
      }
    });
  }

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
