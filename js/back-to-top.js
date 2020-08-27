/**
 * @file
 * Back to top.
 */

'use strict';

(function (Drupal,$) {
  $(document).ready(function() {

    // Variables.
    var window = $(window),
        backToTop = $('.back-to-top'),
        windowHeight = window.height();

    // Events.

    $(window).on('scroll', function(){
      if ($(window).scrollTop() > windowHeight) {
        backToTop.addClass('show');
      } else {
        backToTop.removeClass('show');
      }
    });

    backToTop.click(function () {
      $('html, body').animate({
        scrollTop: 0
      }, 'slow');
    });
  });
})(Drupal, jQuery);