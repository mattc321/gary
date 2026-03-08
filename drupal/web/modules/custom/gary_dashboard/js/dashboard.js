
(function ($, Drupal, drupalSettings, once) {

  Drupal.behaviors.garyForms = {
    attach: function (context, settings) {


      if(drupalSettings.path.isFront) {
        $(once('svg-logo', 'svg#logo')).toggleClass('animate-logo');
      }

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
        $(once('popup-filter-icon', '.popup-filter-icon', context)).click(function () {
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

})(jQuery, Drupal, drupalSettings, once);
