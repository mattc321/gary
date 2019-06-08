/**
 * @file
 * Custom Scripts
 */
 (function($, Drupal, drupalSettings) {

   var initialized;

   function addSwitchUnitMobileView() {
     if (!initialized) {
       initialized = true;
       $('.js-view-dom-id-project-units-block_3')
        .before(
          '<div class="switch-unit-view">'+
            '<span>Edit Mode</span>'+
          '</div>');
     }
   }


   Drupal.behaviors.garyCustom = {
     attach: function (context, settings) {

       // addSwitchUnitMobileView();

       // var unit_controller = new ScrollMagic.Controller();
       // var unit_scene = new ScrollMagic.Scene({triggerElement: "#trigger1", duration: 300})
       //   .setPin("#pin1")
       //   .addIndicators({name: "wtf1 (duration: 300)"}) // add indicators (requires plugin)
       //   .addTo(unit_controller);


        //switch the desired selectors
         $.fn.toggleHidden = function(selector_from, selector_to) {
           var $selector_from = $('.'+selector_from);
           var $selector_to = $('.'+selector_to);

           $selector_from.toggle('hidden');
           $selector_to.toggle('hidden');

         };

         //switch mobile view
         // $('.switch-unit-view').once('.switch-unit-view').click(function(e){
         //   $('.js-view-dom-id-project-units-block_3').toggle('hidden');
         //   $('#inline-pg-form-field-project-units').toggle('hidden');
         //   $('.units-mobile-edit-view').toggle('hidden');
         // });


         //palette selector dark palette night mode
         $(document).ready(function() {

           let color = $('a[palette-selector="change-palette"]').attr('current-palette');

           let checked = color == 'light' ? '' : 'checked';

           //light = unchecked / dark = checked
           let slider_html = '<label class="switch">';
             slider_html += '<input type="checkbox"'+checked+'>';
             slider_html += '<span class="slider round"></span>';
             slider_html += '</label>';

           $('a[palette-selector="change-palette"]').once('a').append(slider_html);

         });

         $('a[palette-selector="change-palette"]').once('a[palette-selector="change-palette"]').click(function(e){
           setTimeout(function() {
             var ajaxObject = Drupal.ajax({
               type: 'GET',
               url: '/change-palette',
               success: function(response) {
                 location.reload();
               }
             });
             ajaxObject.execute();
           }, 200);
         });

     }
   };
 })(jQuery, Drupal, drupalSettings);
