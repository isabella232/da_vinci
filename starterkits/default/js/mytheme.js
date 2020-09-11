/**
 * @file
 * My theme Custom Code of the javascript behaviour.
 */

'use strict';

(function ($) {
  Drupal.behaviors.mythemeTheme = {
    attach: function (context) {

      if($(".view-filters").children().length == 0){
        $(".view-filters").css('display','none');
      }

    }
  };
})(jQuery);
