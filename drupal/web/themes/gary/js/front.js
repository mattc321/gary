
(function ($, Drupal) {
  Drupal.behaviors.garyFront = {
    attach: function (context, settings) {

      //dashboard fade in
      $('.dashboard-block').staggerBounceIn('.dashboard-block');

      //logo
      // $('.site-logo').bounceIn();

      //user menu handler
      $('.right-menu').once('.right-menu').click( function(){
        let nav = '#'+$(this).attr('nav-id');
        if ($(nav).hasClass('user-nav-expanded')) {
          $(nav).removeClass('user-nav-expanded');
        } else {
          $(nav).toggleClass('user-nav-expanded').garyFadeIn(.3);
        }

      });

      let nav = '#block-gary-account-menu, #block-addcontent';
      $('#block-gary-account-menu, #block-addcontent').on('mouseout, mouseleave', function() {
        if ($('.message:hover').length > 0) {
          return;
        }
        if ($(this).hasClass('user-nav-expanded')) {
          $('.messages-popup').remove();
          $(this).toggleClass('user-nav-expanded');
        }
      });

      TweenMax.to("#menu-wrap", .5, {y:0});
      $('.field--type-block-field').fadeIn();


      var hide_tween = TweenMax.to("#menu-wrap", .5, {
        transform: 'translate(0, -60px)'
      });

      var hideController = new ScrollMagic.Controller();
      var hideScene = new ScrollMagic.Scene(
        {offset: 500})
            .setTween(hide_tween)
            // .setClassToggle("#menu-wrap", "ani")
            .addTo(hideController);


      var controller = new ScrollMagic.Controller();
      var scene = new ScrollMagic.Scene(
        {triggerHook: "onEnter"})
            .setTween("#menu-wrap", .5, {y:0})
            // .setClassToggle("#menu-wrap", "ani")
            .addTo(controller);

      // var scale_tween = TweenMax.to("#block-gary-page-title", .3, {
      //   transform: 'translate(40px, -35px) scale(.75)'
      // });

      var titleController = new ScrollMagic.Controller();
      var titleScene = new ScrollMagic.Scene({
        triggerHook: .1,
        triggerElement: '#block-gary-page-title'
        })
            // .setPin('#block-gary-page-title')
            // .setTween(scale_tween)
            // .addIndicators(true)
            // .setTween("#block-gary-page-title", .1, {transform: 'scale(.75)'})
            // .setTween(hide_tween)
            // .setClassToggle("#block-gary-page-title", "sticky-header")
            // .addTo(titleController);
   }
  };

})(jQuery, Drupal);
