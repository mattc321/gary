
(function ($, Drupal) {

  Drupal.behaviors.garyFront = {
    attach: function (context, settings) {

      // TweenMax.to("#menu-wrap", .5, {y:0});
      $('.field--type-block-field').fadeIn();

      // var grid = new Muuri('.field--name-field-page-content', {dragEnabled: true});
      var hideController = new ScrollMagic.Controller();
      var hideScene = new ScrollMagic.Scene(
        {offset: 500})
            .setTween("#menu-wrap", .5, {y:-60})
            // .setClassToggle("#menu-wrap", "ani")
            .addTo(hideController);


      var controller = new ScrollMagic.Controller();
      var scene = new ScrollMagic.Scene(
        {triggerHook: "onEnter"})
            .setTween("#menu-wrap", .5, {y:0})
            // .setClassToggle("#menu-wrap", "ani")
            .addTo(controller);
   }
  };

})(jQuery, Drupal);
