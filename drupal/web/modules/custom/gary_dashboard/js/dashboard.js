
(function ($, Drupal) {

  Drupal.behaviors.garyForms = {
    attach: function (context, settings) {

      if ($('.popup-filter-icon').length > 0) {
        //hide any filters if the form isnt there
        $(context).find('.popup-filter-icon').each(function (index,element) {
          var sel = '#'+($(this).attr('popup-selector'));
          let formcheck = $(sel);
          if (formcheck.length <= 0) {
            $(this).toggle('hidden');
          }
        })

        //click listener
        $(context).find('.popup-filter-icon').once('.popup-filter-icon').click(function () {
          var selector = '#'+($(this).attr('popup-selector'));
          let form = $(selector);
          if (form.length > 0) {
            form.toggle('hidden');
            $(this).toggleClass('filter-expanded');
          }
        })
      }
//end
    }
  };

})(jQuery, Drupal);
