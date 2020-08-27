/**
 * @file
 * Sliding panel.
 */

'use strict';

(function (Drupal, $) {
  $(document).ready(function () {

    // Fade into menu.
    $('#menu-svg').on('click touchstart', function (e) {
      buttonChange();
      $('.sliding-panel-content').toggleClass('is-visible');
      $('body').toggleClass('frozen-body');
      e.preventDefault();
    });

    $(window).resize(function () {
      if ($(this).width() > 1024) {
        if ($('.sliding-panel-content').hasClass('is-visible')) {
          $('.sliding-panel-content').removeClass('is-visible');
          $('body').removeClass('frozen-body');
          buttonChange();
        }
      }
    });

    function throwComplete() {
      isThrowing = false;
      menu_svg_init.removeEventListener('loopComplete', throwComplete);
    }

    function buttonChange() {
      if (menu_svg_x) {
        menu_svg_init.playSegments([26, 39], true);
        menu_svg_x = false
      } else {
        menu_svg_x = true;
        menu_svg_init.playSegments([0, 14], true)
      }
    }

  });
})(Drupal, jQuery);
