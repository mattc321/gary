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
            $target.trigger('RefreshView');
          }
        };

        //switch the desired view
         $.fn.switchView = function(vid_from, vid_to) {
           var $vid_from = $('.'+vid_from);
           var $vid_to = $('.'+vid_to);

           $vid_from.toggle('hidden');
           $vid_to.toggle('hidden');

         };
     }
   };
 })(jQuery, Drupal, drupalSettings);
