
(function ($, Drupal) {

  Drupal.behaviors.garyForms = {
    attach: function (context, settings) {

      //login screen
      $(".user-login-container").garyFadeIn();

      //update close date after intake date chosen
      //gives user a projected End Date
      $('#edit-field-intake-date-0-value-date', context).once('#edit-field-intake-date-0-value-date').bind('focusout', function() {
        var tt = $('#edit-field-intake-date-0-value-date').val();
        if(tt == '') {
          tt = Date();
        }
        var date = new Date(tt);
        var newdate = new Date(date);

        newdate.setDate(newdate.getDate() + 450);
        var dd = newdate.getDate();
        if (dd < 10) {
          dd = '0'+dd;
        }
        var mm = newdate.getMonth() + 1;
        if (mm < 10) {
          mm = '0'+mm;
        }
        var y = newdate.getFullYear();

        var someFormattedDate = y + '-' + mm + '-' + dd;
        $('#edit-field-date-closed-0-value-date').val(someFormattedDate);
      });


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
