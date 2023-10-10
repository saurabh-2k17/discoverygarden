(function ($, Drupal) {
  Drupal.behaviors.uelCoreLightningSchedulerWrapper = {
    attach: function (context, settings) {
      // Check if scheduler exist and custom wrapper doesnot exist.
      if (($('.form-wrapper transitionset').length) && (!$('.form-wrapper .custom-wrapper-scheduler').length)) {
        // Add the custom wrapper for the scheduler.
        $('.form-wrapper transitionset').prepend('<div class="custom-wrapper-scheduler">' + Drupal.t('Schedule') + ':</div>');
      }
    }
  };
})(jQuery, Drupal);
