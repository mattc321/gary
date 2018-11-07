(function ($, _, Backbone, Drupal) {
  let $body = $('body');
  Drupal.gary.MobileMenuView = Backbone.View.extend({
    initialize: function() {
      this.$menuWrap = $('#menu-wrap');
      this.$nav = this.$menuWrap.find('nav#block-gary-main-menu');

      this.menuExpanded = false;
      this.showingMenuWrap = true;
      this.$logo = this.$menuWrap.find('#block-gary-branding');

      $('#mobile-toggle').on('click', (e) => {
        e.preventDefault();
        this.toggleMenu();
      });

      this.model.on('change:breakpoint', this.onBreakpointChange, this);
      this.onBreakpointChange();
    },

    onBreakpointChange: function () {
      if(this.model.get('breakpoint') <= Drupal.gary.breakpoints.medium) {
        this.model.on('change:scrollTop', this.onScroll, this);
      } else {
        // Get rid of any manual changes we have made
        this.$menuWrap.attr('style', '');

        this.model.off('change:scrollTop', this.onScroll);
      }
    },

    onScroll: function (e) {
      let top = this.model.get('scrollTop');
      let dir = this.model.get('scrollDir');
      if(top < 100) {
        this.showMenuWrap();
      } else if(dir === 'up') {
        this.showMenuWrap();
      } else if(dir === 'down') {
        this.hideMenuWrap();
      }
    },

    toggleMenu: function() {
      if(this.menuExpanded) {
        this.$menuWrap.removeClass('expanded');
        TweenLite.to(this.$nav, .5, {top: '-100vh', ease: Power4.easeOut, onComplete: () => {
          this.menuExpanded = false;
          this.unfreeze();
        }});
      } else {
        this.$menuWrap.addClass('expanded');
        this.$nav.show();
        TweenLite.to(this.$nav, .5, {top: 0, ease: Power4.easeOut, onComplete: () => {
          this.menuExpanded = true;
          this.freeze();
        }});
      }
    },

    showMenuWrap: function() {
      if(this.showingMenuWrap) {
        return;
      }
      this.showingMenuWrap = true;

      TweenLite.to(this.$menuWrap, 1, { top: 0, ease: Power4.easeOut });
    },

    hideMenuWrap: function () {
      if(this.menuExpanded || !this.showingMenuWrap) {
        return;
      }
      this.showingMenuWrap = false;

      TweenLite.to(this.$menuWrap, .5, { top: -140, ease: Power2.easeIn });
    },
    freeze: function() {
      $body.addClass('freeze');
    },
    unfreeze: function () {
      $body.removeClass('freeze');
    }
  });

}(jQuery, _, Backbone, Drupal));
