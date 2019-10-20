
(function ($, Drupal) {
  Drupal.gary = {
    breakpoints: {
      medium: 850,
      large: 1100,
    }
  };

  var delayInterval = 0;
  $.fn.staggerBounceIn = function(selector) {
    $(this).once(selector).each(function (index,value) {
      setTimeout(function(){ $(value).bounceIn(2); }, delayInterval);
      delayInterval += 100;
    })
  }

  $.fn.stagger = function(i = 100) {
    setTimeout(function(){ $(this).bounceIn(5); }, delayInterval);
    delayInterval += i;
  }

  $.fn.bounceIn = function(sec = .5) {
    TweenMax.fromTo(this, sec, {scale: .9, opacity: 0}, {scale: 1, opacity: 1});
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

  //submit the form if ctrl S clicked
  $(window).keypress(function(event) {
    if (!(event.which == 5 && event.ctrlKey)) {
      return true;
    }
    let quickedita = $('li.quickedit a');
    event.preventDefault();
    quickedita.once('.quick-edit').trigger('click');
    return false;
  });

  //make clickable containers for mobile
  $(".item-clickable").once(".item-clickable").click(function() {
    let link = $(this).find(".item-follow > a").attr('href');
    window.location = link;
    return false;
  });

  $(document).ready(function() {

    var controller = new ScrollMagic.Controller();
    new ScrollMagic.Scene(
      {triggerHook: "onEnter"})
      .setTween("#menu-wrap", .5, {y:0})
      // .setClassToggle("#menu-wrap", "ani")
      .addTo(controller);

    Drupal.gary.appState = new Drupal.gary.AppStateModel({
      scrollTop: $(window).scrollTop(),
    });

    Drupal.gary.mobileMenu = new Drupal.gary.MobileMenuView({
      model: Drupal.gary.appState,
    });

    //init qtip implementation
    $('.tooltip').each(function() {
      let content = $(this).children('.tooltip-content');
      let title = $(this).children('.tooltip-title');
        $(this).qtip({
            content: {
                text: $(content).html(),
                title: $(title).html()
            },
            style: {
                classes: 'qtip-bootstrap qtip-shadow'
            },
            position: {
                target: 'mouse',
                adjust: { mouse: false }
            }
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
