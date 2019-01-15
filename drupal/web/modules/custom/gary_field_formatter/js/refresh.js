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
          let $view = $('.'+dom_id);
          if ($view.length == 0) {
            location.reload();
          } else {
            $view.trigger('RefreshView');
          }
        };

        //switch the desired view
         $.fn.switchView = function(vid_from, vid_to) {
           var $vid_from = $('.'+vid_from);
           var $vid_to = $('.'+vid_to);

           $vid_from.toggle('hidden');
           $vid_to.toggle('hidden');

         };

         //toggle an element
          $.fn.toggleElement = function(selector, property) {
            var $element = $(selector);
            $element.toggle(property);
          };

          //clear form values
           $.fn.clearValues = function(form_selector) {
             $(form_selector+" input:not(:hidden)").each(function(index){
                if ($(this).prop('type') != 'submit') {
                  $(this).val('');
                }
              });
           };


           //listener for add item button
           $('.add-pg-item', context).once(this.id).click(function () {
             formid = $(this).attr('data-id');
             let $form = $('#'+formid);
             $form.toggle('hidden');
           });


     }
   };
 })(jQuery, Drupal, drupalSettings);
