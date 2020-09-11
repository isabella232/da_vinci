/**
 * @file
 * My theme Custom Code of the javascript behaviour.
 */

'use strict';

(function ($) {
  Drupal.behaviors.collapseMenu = {
    attach: function (context) {

      // Variables.

      var menuAdministrationUser = $('.menu-administration-user-content'),
          collapse = $('.sidebar-menu-collapse-parent a');

      // Functions.

      if ($(window).width() <= 768) {
        menuAdministrationUser = $('.menu-administration-user-content').addClass('collapse');
      }else{
        menuAdministrationUser = $('.menu-administration-user-content').removeClass('collapse');
      }

      $(window).resize(function() {
        if ($(window).width() <= 768) {
          menuAdministrationUser = $('.menu-administration-user-content').addClass('collapse');
        }else{
          menuAdministrationUser = $('.menu-administration-user-content').removeClass('collapse');
        }
      });

      collapse.click(function() {
        menuAdministrationUser.toggleClass('collapse');
      });

    }
  };
})(jQuery);