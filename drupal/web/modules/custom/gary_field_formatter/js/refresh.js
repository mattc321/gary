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
          //no domid is null just reload the page
          if (! dom_id) {
            location.reload();
          } else {
            let $view = $('.'+dom_id);
            if ($view.length === 0) {
              location.reload();
            }
            $view.once('.'+dom_id).trigger('RefreshView');
          }
        };

       $.fn.refreshTotalAmount = function(amount) {
         let amount_el = $('.field--name-field-amount .field__item');

         if (amount_el.length > 0) {
           amount_el.fadeOut('slow');
           amount_el.attr('content', amount);
           amount_el.html('$'+amount);
           amount_el.fadeIn('slow');
         }
       };

        //refresh the desired view
         $.fn.refreshAndHide = function(dom_id, switch_id) {
           //no domid is null just reload the page
           let $view = $('.'+dom_id);
           let $switch_view = $('.'+switch_id);
           $view.once('.'+dom_id).trigger('RefreshView');
           $switch_view.once('.'+dom_id).trigger('RefreshView');
           $switch_view.toggle('hidden');
         };

        //reload and jump
         $.fn.reloadJump = function(vid_from, vid_to) {
           //no domid is null just reload the page
           location.reload();
           // let $view = $('.'+vid_from);
           // let $viewto = $('.'+vid_to);
           // $view.trigger('RefreshView');
           // $viewto.trigger('RefreshView');
           // $view.toggle('hidden');
           //we'd prefer to refresh the view with ajax
           //but the form isnt being rebuilt correctly. just
           //have to refresh the whole page for now.

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
             $(form_selector+" input:not(:hidden)").each(function(){
                if ($(this).prop('type') != 'submit') {
                  $(this).val('');
                }
              });
              //clear out select2 selects
             $(form_selector).find('.select2-hidden-accessible').each(function(){
                 $(this).val(null).trigger('change');
             });

           };

         //notify an assignee from route in gary_custom
          $.fn.notifyAssignee = function(tid) {
            var ajaxObject = Drupal.ajax({
              type: 'GET',
              url: '/notify/' + tid,
              success: function(responseData) {
                   //success
               }
             });
            ajaxObject.execute();
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
