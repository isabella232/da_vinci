/**
 * @file
 * In view.
 */

'use strict';

(function ($) {

  var $window = $(window);

  function check_if_in_view() {

    /** Usage.
    $('.example:in-viewport').addClass('in-view');
    $('.example:below-the-fold').removeClass('in-view');
    */

  }

  $window.on('scroll resize', check_if_in_view, 10);
  $window.trigger('scroll');

})(jQuery);
