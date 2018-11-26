/**
 * @file
 * Append handler for adding a field collection row
 */
 (function($, Drupal, drupalSettings) {
   Drupal.behaviors.garyFieldFormatter = {
     attach: function (context, settings) {
        $.fn.refreshView = function(domdom) {
          console.log('wtf');
          var dom_id = drupalSettings.dom_id;
          console.log(dom_id);
          if($('.'+domdom).length > 0) {
            var $target = $('.'+domdom);
            $target.trigger('RefreshView');
          }
        };
     }
   };
 })(jQuery, Drupal, drupalSettings);
