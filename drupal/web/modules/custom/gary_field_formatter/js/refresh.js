/**
 * @file
 * Refresh the view
 * dom_id string  any class within views children or a class on the view itself
 */
 (function($, Drupal, drupalSettings) {
   Drupal.behaviors.garyFieldFormatter = {
     attach: function (context, settings) {

       //refresh the desired view
        $.fn.refreshView = function(dom_id) {
          if($('.'+dom_id).length > 0) {
            var $target = $('.'+dom_id);
            $target.closest('.view').trigger('RefreshView');
          }
        };

        //switch the desired view
         $.fn.switchView = function(vid) {
           console.log(vid);
         };
     }
   };
 })(jQuery, Drupal, drupalSettings);
