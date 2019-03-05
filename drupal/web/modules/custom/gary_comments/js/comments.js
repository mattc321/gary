/**
 * @file
 * This file contains all Comment funnctions
 */

  (function($, Drupal, drupalSettings) {
    Drupal.behaviors.garyComments = {
      attach: function (context, settings) {

        //attach event listener to field - attached in form_alter
        $(document, context).once(document.id).ready(function () {
          $('.notifications').checkMessages();
        });

        window.setInterval(function(){
          $('.notifications').checkMessages();
        }, 5000);

        //get the message count
         $.fn.checkMessages = function() {
           $el = $(this);
           var ajaxObject = Drupal.ajax({
             type: 'GET',
             url: '/check-messages',
             success: function(responseData) {
                if (responseData > 0) {
                  $el.html(responseData);
                  $el.show();
                } else {
                  $el.hide();
                }
              }
            });
           ajaxObject.execute();
         };

      }
    };
  })(jQuery, Drupal, drupalSettings);
