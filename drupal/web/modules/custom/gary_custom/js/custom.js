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
     }
   };
 })(jQuery, Drupal, drupalSettings);
