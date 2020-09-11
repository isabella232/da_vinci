/**
 * @file
 * My theme Custom Code of the javascript behaviour.
 */

'use strict';

(function ($) {
  Drupal.behaviors.home = {
    attach: function (context) {

      //Variables.
      var loginName = $('.user-login-form .js-form-type-textfield input'),
          loginPassword = $('.user-login-form .js-form-type-password input'),
          inputs = $('.user-login-form .js-form-item input');


      // Functions.
      function deleteClasses(element){

        // Input.
        var brothers = element.parents('.js-form-item').siblings('.js-form-item');

        // Brother.
        brothers.each(function() {
          if($(this).find('label').hasClass('label-float') && $(this).find('input').val()<=0){
            $(this).find('label').removeClass('label-float');
          }
        });
      }

      function addClasses(element){

        // If input has text.
        if(element.val().length > 0){
            element.parent('.wrapper-input').siblings('label').addClass('label-float');
        }else{
          element.parent('.wrapper-input').siblings('label').removeClass('label-float');
        }
      }

      // Add icons on inputs.
      inputs.each(function() {
        $(this).wrapAll('<div class="wrapper-input" />');
        if($(this).attr('name')=='pass'){
          $(this).after('<span class="icon-hidden"></span>');
        }
      });

      // Add element after inputs.
      inputs.after('<span class="bar"/>');

      // First load.
      addClasses(loginName);
      addClasses(loginPassword);

      // When the inputs undergo changes.
      loginName.on('change', function (){
        addClasses(loginName);
      });

      loginPassword.on('change', function (){
        addClasses(loginPassword);
      });

      loginName.focus(function() {
        deleteClasses(loginName);
        loginName.parent('.wrapper-input').siblings('label').addClass('label-float');
      });

      loginPassword.focus(function() {
        deleteClasses(loginPassword);
        loginPassword.parent('.wrapper-input').siblings('label').addClass('label-float');
      });

      // Show or hidden password.
      $('.icon-hidden').click(function () {
        if($('#edit-pass').attr('type') == 'password'){
          $('#edit-pass').prop('type', 'text');
        }else{
          $('#edit-pass').prop('type', 'password');
        }
        $('.icon-hidden').toggleClass('show');
      });
    }
  };
})(jQuery);