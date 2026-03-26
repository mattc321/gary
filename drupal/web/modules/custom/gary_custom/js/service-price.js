/**
 * @file
 * This file contains all Service Price funnctions
 */

  (function($, Drupal, drupalSettings, once) {
    Drupal.behaviors.garyServicePrices = {
      attach: function (context, settings) {

        //attach event listener to field - attached in form_alter
        $(once('calculate-price', '.calculate-price', context)).change(function () {

          let subform = ($(this).closest('.paragraphs-subform').length > 0)
            ? $(this).closest('.paragraphs-subform')
            : $(this).closest('.inline-pg-form');

          if (subform.length >= 0 ) {
            $(this).getServicePrice($(this).val(), subform);
          }
        });

        //attach event listener to field - attached in form_alter
        $(once('update-price', '.update-price', context)).change(function () {

          let subform = ($(this).closest('.paragraphs-subform').length > 0)
            ? $(this).closest('.paragraphs-subform')
            : $(this).closest('.inline-pg-form');

          let price_field = subform.find('.service-price');
          setCalc(price_field.val(), subform);
        });


        //get the service price
         $.fn.getServicePrice = function(service_id, subform) {

           if (service_id.length < 1) {
             return;
           }

           //retrieve a string response
           $.ajax({
             type: 'GET',
             url: '/service_price/' + service_id,
             dataType: 'text',
              success: function(responseData) {
                  setCalc(responseData, subform);
              },
              error: function(xhr, status, error) {
                console.error('gary_custom getServicePrice failed', {
                  url: '/service_price/' + service_id,
                  serviceId: service_id,
                  status: status,
                  error: error,
                  httpStatus: xhr.status,
                  responseText: xhr.responseText
                });
              }
            });

           //now calculate

         };

         //set the calculated values
         function setCalc(price, subform) {
           let price_field = subform.find('.service-price');
           let qty_field = subform.find('.service-quantity');
           let line_total = subform.find('.service-line-total');

           //set the price field
           price_field.val(price);

           let qty = parseInt(qty_field.val());

           if (qty > 0) {
             line_total.val(parseInt(price) * qty);
           } else {
             line_total.val('');
           }

         }

      }
    };
  })(jQuery, Drupal, drupalSettings, once);




 /**
 *
 * Calculates extended prices of service line items on opportunities
 * Attached to the form controls in ec_app//ec_app_form_alter()
 */
 function updatePrice(pricekey) {
			//price edit-field-opportunity-services-und-2-field-service-amount-und-0-value
			priceid = document.getElementById('edit-field-opportunity-services-und-' + pricekey + '-field-service-amount-und-0-value').value;

			//qty field edit-field-opportunity-services-und-2-field-quantity-und-0-value
			qtyfield = 'edit-field-opportunity-services-und-' + pricekey + '-field-quantity-und-0-value';
			qty = parseInt(document.getElementById(qtyfield).value);

			//line total edit-field-opportunity-services-und-2-field-amount-und-0-value
			linetotal = 'edit-field-opportunity-services-und-' + pricekey + '-field-amount-und-0-value';
			if (qty > 0) {
				document.getElementById(linetotal).value = priceid * qty;
			} else {
				document.getElementById(linetotal).value = '';
			}

}
