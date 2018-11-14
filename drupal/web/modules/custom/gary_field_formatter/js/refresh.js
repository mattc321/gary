/**
 * @file
 * Append handler for adding a field collection row
 */
 (function($, Drupal, drupalSettings) {
   Drupal.behaviors.garyFieldFormatter = {
     attach: function (context, settings) {
        $.fn.refreshView = function() {
          var dom_id = drupalSettings.dom_id;
          if($('.'+dom_id).length > 0) {
            var $target = $('.'+dom_id);
            $target.trigger('RefreshView');
          }
        };
     }
   };
 })(jQuery, Drupal, drupalSettings);
