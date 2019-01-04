/**
 * @file
 * Get fieldnames and append to select
 */
 (function($, Drupal, drupalSettings) {
   Drupal.behaviors.garyFields = {
     attach: function (context, settings) {

        $.fn.updateFieldsSelect = function(bundle, selector) {

          var bundle_element = $('.'+bundle+' option:selected').val();
          //retrieve a json response
          var ajaxObject = Drupal.ajax({
            type: 'GET',
            url: '/get_field_options/' + bundle_element,
            success: function(responseData) {
                 $('#'+selector).html(responseData);
             }
           });
          ajaxObject.execute();

        };

     }
   };
 })(jQuery, Drupal, drupalSettings);
