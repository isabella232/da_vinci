/**
 * @file
 * Da Vinci Custom Code of the javascript behaviour for theme settings.
 */

'use strict';

(function ($) {

  /**
   * Initialize Select2 support.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.davinci_Select2 = {
    attach: function (context) {
      if (!$.fn.select2) {
        return;
      }

      $('select.js-davinci-select2').select2();

    }
  };

})(jQuery, Drupal);
