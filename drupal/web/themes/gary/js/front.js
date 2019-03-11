
(function ($, Drupal) {

  Drupal.behaviors.garyFront = {
    attach: function (context, settings) {

      //dashboard fade in
      var delayInterval = 0;
      $(context).find('.homebox-column').once('.homebox-column').each(function (index,value) {
        setTimeout(function(){ $(value).bounceIn(); }, delayInterval);
        delayInterval += 100;
      })


      //user menu handler
      var expanded = 0;
      $('.right-menu').click( function(){
        let nav = '#'+$(this).attr('nav-id');
        if (expanded == 0) {
          $(nav).toggleClass('user-nav-expanded').garyFadeIn(.3);
          expanded = 1;
        } else {
          $(nav).toggleClass('user-nav-expanded');
          expanded = 0;
        }
      });
      let nav = '#block-gary-account-menu, #block-addcontent';
      $(nav).mouseleave(function(){
        if (expanded == 1) {
          $(this).toggleClass('user-nav-expanded');
          expanded = 0;
        }
      });

      //mobile main menu handler

      // $('#mobile-toggle').click( function(){
      //   $('#menu-wrap').toggleClass('expanded');
      //   $('#block-gary-main-menu').show();
      //   TweenLite.to('#block-gary-main-menu', .5, {top: 0, ease: Power4.easeOut, onComplete: () => {
      //     console.log(1);
      //   }});
      //   // TweenMax.to("#block-gary-main-menu", .5, {transform:'translateY(100vh)'});
      // });


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
