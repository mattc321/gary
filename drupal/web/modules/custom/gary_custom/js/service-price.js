/**
 * @file
 * This file contains all Service Price funnctions
 */

  (function($, Drupal, drupalSettings) {
    Drupal.behaviors.garyServicePrices = {
      attach: function (context, settings) {


        //attach event listener to field - attached in form_alter
        $('.calculate-price', context).once(this.id).change(function () {
          $(this).servicePrice($(this).val());
        });

        //attach event listener to field - attached in form_alter
        $('.update-price', context).once(this.id).change(function () {
          let price_field = $('#edit-field-service-amount-0-value');
          setCalc(price_field.val());
        });


        //get the service price
         $.fn.servicePrice = function(value) {

          //exec an ajax response
          //  var endpoint = Drupal.url('modal/get-content');
          //  Drupal.ajax({ url: endpoint }).execute();


           //retrieve a string response
           var ajaxObject = Drupal.ajax({
             type: 'GET',
             url: '/service_price/' + value,
             success: function(responseData) {
                  setCalc(responseData);
              }
            });
           ajaxObject.execute();

           //now calculate

         };

         //set the calculated values
         function setCalc(price) {
           let price_field = $('#edit-field-service-amount-0-value');
           let qty_field = $('#edit-field-quantity-0-value');
           let line_total = $('#edit-field-line-total-0-value');

           //set the price field
           price_field.val(price);

           var qty = parseInt(qty_field.val());

           if (qty > 0) {
             line_total.val(parseInt(price) * qty);
           } else {
             line_total.val('');
           }

         }

      }
    };
  })(jQuery, Drupal, drupalSettings);




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
