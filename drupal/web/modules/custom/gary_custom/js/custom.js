/**
 * @file
 * Custom Scripts
 */
 (function($, Drupal, drupalSettings, once) {

   var initialized;

   function addSwitchUnitMobileView() {
     if (!initialized) {
       initialized = true;
       $('.js-view-dom-id-project-units-block_3')
        .before(
          '<div class="switch-unit-view">'+
            '<span>Edit Mode</span>'+
          '</div>');
     }
   }


   Drupal.behaviors.garyCustom = {
     attach: function (context, settings) {

       // addSwitchUnitMobileView();

       // var unit_controller = new ScrollMagic.Controller();
       // var unit_scene = new ScrollMagic.Scene({triggerElement: "#trigger1", duration: 300})
       //   .setPin("#pin1")
       //   .addIndicators({name: "wtf1 (duration: 300)"}) // add indicators (requires plugin)
       //   .addTo(unit_controller);


        //switch the desired selectors
         $.fn.toggleHidden = function(selector_from, selector_to) {
           var $selector_from = $('.'+selector_from);
           var $selector_to = $('.'+selector_to);

           $selector_from.toggle('hidden');
           $selector_to.toggle('hidden');

         };

         const modifyTableDrag = function(){
           $("table[id^='draggable']" ).each( function( index, element ){
             let id = $(this).attr('id');
             $(this).parent().find('div.jelly').hide();
             const tableDrag = Drupal.tableDrag[id];
             tableDrag.onDrop = function($jelly) {
               $(this).parent().find('div.jelly').fadeIn();
             }.bind($(this));
           });
         };

        modifyTableDrag();

       //switch mobile view
         // $('.switch-unit-view').once('.switch-unit-view').click(function(e){
         //   $('.js-view-dom-id-project-units-block_3').toggle('hidden');
         //   $('#inline-pg-form-field-project-units').toggle('hidden');
         //   $('.units-mobile-edit-view').toggle('hidden');
         // });


         //palette selector dark palette night mode
         $(document).ready(function() {

           let color = $('a[palette-selector="change-palette"]').attr('current-palette');

           let checked = color == 'light' ? '' : 'checked';

           //light = unchecked / dark = checked
           let slider_html = '<label class="switch">';
             slider_html += '<input type="checkbox"'+checked+'>';
             slider_html += '<span class="slider round"></span>';
             slider_html += '</label>';

           $(once('a', 'a[palette-selector="change-palette"]')).append(slider_html);

         });

         $(once('palette-selector', 'a[palette-selector="change-palette"]')).click(function(e){
           setTimeout(function() {
             var ajaxObject = Drupal.ajax({
               type: 'GET',
               url: '/change-palette',
               success: function(response) {
                 location.reload();
               }
             });
             ajaxObject.execute();
           }, 200);
         });

     }
   };

  Drupal.behaviors.fieldInfoIcon = {
    attach: function (context, settings) {
      once('field-info-icon', '.form-item', context).forEach(function (item) {
        var desc = item.querySelector('.description');
        if (!desc || !desc.textContent.trim()) return;

        var text = desc.textContent.trim();

        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'field-info-toggle';
        toggle.setAttribute('aria-label', 'Field information');
        toggle.textContent = 'i';

        var tooltip = null;

        function showTooltip() {
          tooltip = document.createElement('div');
          tooltip.className = 'field-info-tooltip';
          tooltip.textContent = text;
          toggle.appendChild(tooltip);
        }

        function hideTooltip() {
          if (tooltip) {
            tooltip.remove();
            tooltip = null;
          }
        }

        toggle.addEventListener('click', function (e) {
          e.stopPropagation();
          if (tooltip) {
            hideTooltip();
          } else {
            showTooltip();
            document.addEventListener('click', hideTooltip, { once: true });
          }
        });

        var label = item.querySelector('label');
        if (label) {
          label.appendChild(toggle);
        } else {
          desc.parentNode.insertBefore(toggle, desc);
        }
      });
    }
  };

 })(jQuery, Drupal, drupalSettings, once);
