/**
 * @file
 * My theme Custom Code of the javascript behaviour.
 */

'use strict';

(function ($) {
  Drupal.behaviors.inputs = {
    attach: function (context) {

      // Functions.

      // Function add class to element.
      function addClass(element,nameClass){
        element.parent('.form-item').addClass(nameClass);
      }

      // Function remove class to element.
      function removeClass(element,nameClass){
        element.parent('.form-item').removeClass(nameClass);
      }

      // add the "float-label" class when input got focus.
      $('input').focusin(function () {
        addClass($(this),'float-label');
        addClass($(this),'focus');
      });

      // Remove the "float-label" class when input lost focus.
      $('input').focusout(function () {
        if($(this).val() && $(this).val().length <= 0){
          removeClass($(this),'float-label');
        }
        removeClass($(this),'focus');
      });

      // If input isn't empty.
      $('input,select').each(function( index ) {
        if($(this).val() && $(this).val().length > 0){
          addClass($(this),'float-label');
        }else{
          removeClass($(this),'float-label');
        }
      });

      // If input change of length.
      $('input,select').on('change', function (){
        if($(this).val() && $(this).val().length > 0){
          addClass($(this),'float-label');
        }else{
          removeClass($(this),'float-label');
        }
      });

      // Select multiple.
      $('select[multiple=multiple]').parent('.js-form-type-select').addClass('select-multiple');

      // If input type checkbox.
      $('input[type="checkbox"]').once('Add span').after('<span class="check"></span>');

      // display none for the div when it exists bulk-operations-delete.
      $('#edit-views-bulk-operations-delete-entity').parent().css('display','none');
    }
  };
})(jQuery);
