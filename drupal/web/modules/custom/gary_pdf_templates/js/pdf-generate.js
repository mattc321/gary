/**
 * @file
 * Get fieldnames and append to select
 */
(function($, Drupal) {
  $( document ).ready(function() {

    const generateLink = (url, $parent, fileName) => {
      $('<a />', {
        'href': url,
        'download': fileName,
        'text': "click"
      }).hide().appendTo("body")[0].click();
    };

    $('.generate-link').on('click', function(event) {
      event.preventDefault();
      event.stopPropagation();

      let $link = $(event.target).parent();

      $link.fadeOut('fast', function() {
        $link.after('<div class="generating">&nbsp;</div>');
      });

      let fileName = $link.attr('data-file-name') + '.pdf';

      let oReq = new XMLHttpRequest();
      oReq.open("GET", $link.attr('href'), true);
      oReq.responseType = "arraybuffer";

      oReq.onload = function(oEvent) {
        let arrayBuffer = oReq.response;

        // If you want to use the image in your DOM:
        let blob = new Blob([arrayBuffer], {type: "application/pdf"});
        let url = URL.createObjectURL(blob);
        generateLink(url, $link, fileName);
        $('.generating').remove();
        $link.fadeIn('fast');
      };

      oReq.send();

    });

  });
})(jQuery, Drupal);
