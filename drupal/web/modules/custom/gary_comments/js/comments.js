/**
 * @file
 * This file contains all Comment funnctions
 */

  (function($, Drupal, drupalSettings) {

    //initialize the messages container and attach a leave listener
    $('body').once('body').before('<div class="messages-popup-container"></div>');
    //go to the comment on message click
    $('.messages-popup-container').once('.messages-popup-container').on('click', '.message', function (event) {
      window.location = $(this).attr('rel');
    });
    $('.messages-popup-container').on('mouseout, mouseleave', function() {
        $('.message').each(function (index,element) {
          $(this).markRead();
        })
        $('.messages-popup').remove();

    });

    Drupal.behaviors.garyComments = {
      attach: function (context, settings) {

        //attach the messages popup to the menu item
        $(context).find('ul.menu').find('li a').each(function (index,element) {
          if ($(this).attr('href') == '/build-messages') {
            let li = $(this).parent('li');
            let ul = $(li).parent('ul');
            $(li).addClass('message-popup-activator');
            $('.message-popup-activator', context).once('.message-popup-activator').on('mouseover', function (event) {
              $('.messages-popup').remove();
              $(ul).buildMessages($(li).offset());
            });
          }
        })

        //override the message menu link click
        $('.message-popup-activator a').once('.message-popup-activator a').on('click', function (event) {
          event.preventDefault();
          window.location = '/user/'+drupalSettings.user.uid+'/#block-views-block-my-messages-block-1';
        });

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
           let $el = $('.notifications');
           let ajaxObject = Drupal.ajax({
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

         //get the message count
          $.fn.buildMessages = function(position) {
            $el = $(this);
            let ajaxObject = Drupal.ajax({
              type: 'GET',
              url: '/build-messages',
              success: function(response) {
                $('.messages-popup').remove();
                let messages = [];
                  for (let i = 0; i < response.length; i++) {
                    let dateObject = new Date(response[i].created[0].value);
                    const message = {
                      body: response[i].body[0].value,
                      nid: response[i].nid[0].value,
                      from: response[i].field_tag_user_by[0].image_url,
                      created: formatDate(dateObject),
                      content: response[i].field_tag_content_reference[0].url,
                      title: response[i].field_tag_content_reference[0].title,
                      comment_id: response[i].field_tag_comment_id[0].value,
                    };
                    messages.push(message);
                  }
                  $('.messages-popup-container').html(messagesObject.theme(position, messages));
               }
             });
            ajaxObject.execute();

          };
          $.fn.markRead = function() {
            let ajaxReadObject = Drupal.ajax({
              type: 'GET',
              url: '/mark-read/'+$(this).attr('message-id'),
              success: function(response) {
                console.log(response);
               }
             });
            ajaxReadObject.execute();

          };

          var formatDate = function(date) {
            return date.getMonth() + "-" + date.getDate() + "-" + date.getFullYear();
          }
          var messagesObject = {
            theme: function(position, messages) {
              var html = '<div class="messages-popup">';
                html += messagesObject.build_messages(messages);
                html += '</div>';
                messagesObject.place('.messages-popup-container', position);
              return html;

            },
            place: function(element, position) {
              $(element).css({
                 'position' : 'absolute',
                 'top' : position.top+'px',
                 'left' : position.left - 300 +'px',
                 'width' : '300px',
                 'height' : 'auto',
                 'z-index' : 999,
                 'transition' : '.5s'
              });
            },
            build_messages: function(messages) {
              let message_div = '';
              for (let i = 0; i < messages.length; i++) {
                message_div += '<div class="message" message-id="'+messages[i].nid+'" rel="'+messages[i].content+'#comment-'+messages[i].comment_id+'">';
                message_div += '<div class="message-from"><img class="user-pic-small" src="'+ messages[i].from+'"></div>';
                message_div += '<div class="message-created">'+ messages[i].created+'</div>';
                message_div += '<div class="message-body">'+ messages[i].body+'</div>';
                message_div += '<div class="message-content">'+ messages[i].title+'</div>';
                message_div += '</div>';
              }
              return message_div;
            }
          };

      }
    };
  })(jQuery, Drupal, drupalSettings);
