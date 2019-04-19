/**
 * @file
 * Custom Scripts
 */
 (function($, Drupal, drupalSettings) {
   Drupal.behaviors.garyCustom = {
     attach: function (context, settings) {

        //switch the desired selectors
         $.fn.toggleHidden = function(selector_from, selector_to) {
           var $selector_from = $('.'+selector_from);
           var $selector_to = $('.'+selector_to);

           $selector_from.toggle('hidden');
           $selector_to.toggle('hidden');

         };



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
