/**
 * @file
 * This file contains all Comment funnctions
 */

  (function($, Drupal, drupalSettings) {

//     events: function(start, end, timezone, callback) {
// console.log(drupalSettings.path.baseUrl);
//       $.ajax({
//         url: drupalSettings.path.baseUrl + 'rest/calendar/'+start.add(10, 'days').format('YYYYMM'),
//         type: 'GET',
//         error: function() {
//           console.log('Error loading calendar events.');
//         },
//         success: (response) => {
//           let events = [];
//           for (let i = 0; i < response.length; i++) {
//             const event = {
//               id: response[i].nid[0].value,
//               title: response[i].title[0].value,
//               start: response[i].field_date_range[0].value+'+00:00', // Defines it as UTC
//               end: response[i].field_date_range[0].end_value+'+00:00', // Defines it as UTC,
//               description: response[i].body[0] ? response[i].body[0].summary ? response[i].body[0].summary : response[i].body[0].value : '',
//               url: `${drupalSettings.path.baseUrl}node/${response[i].nid[0].value}`,
//               className: [],
//             };
//             for(let type of response[i].field_event_type) {
//               event.className.push('term-'+type.target_id);
//             }
//             events.push(event);
//           }
//
//           callback(events);
//         }
//       });
//     }

    Drupal.behaviors.garyComments = {
      attach: function (context, settings) {

        //attach event listener to field - attached in form_alter
        $(document, context).once(document.id).ready(function () {
          $('.notifications').checkMessages();
        });

        window.setInterval(function(){
          $('.notifications').checkMessages();
        }, 5000);

        //message div clickable
        $(".message-item-clickable").once(".message-item-clickable").click(function() {
          let link = $(this).find(".message-follow > a");
          let link_follow = link.attr("href") + "#node-projects-field-comments";
          window.location = link_follow;
          return false;
        });

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
