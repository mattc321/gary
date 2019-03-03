
(function ($, Drupal) {

  Drupal.behaviors.garyForms = {
    attach: function (context, settings) {

      //Alert user of improper address formatting
      $('#edit-field-address-0-value', context).once('#edit-field-address-0-value').bind('focusout', function() {
        var msg = 'Remember to abbrieviate street suffixes and direction';
        var text2 = $('#edit-field-address-0-value').val().toLowerCase();

        //STRINGS TO TEST FOR IN ADDRESS FIELD
        var mylist = 'drive, avenue, street, boulevard, place, north, south, east, west';
        var things = mylist.split(',');
        for(var i = 0; i < things.length; i++) {
          if(text2.indexOf(things[i]) != -1) {
            $('#edit-field-address-0-value').setMessage(msg);
            return;
          } else if (text2.indexOf(things[i]) == -1) {
            $('#edit-field-address-0-value').setMessage('');
          }
        }
      });

      //prevent special chars
      $('#edit-field-address-0-value').cleanSpecialChars();
      $('#edit-field-city-0-value').cleanSpecialChars();
      $('#edit-field-state-0-value').cleanSpecialChars();
      $('#edit-field-zip-0-value').cleanSpecialChars();

      if ($('.page-node-type-projects').length > 0) {
        //Title of nodes are the addresses typically
        $('#edit-title-0-value').bind('focusout', function() {
          var myval = $('#edit-title-0-value').val()
          $('#edit-field-address-0-value').val(myval);
        });
      }
    }
  };

})(jQuery, Drupal);
