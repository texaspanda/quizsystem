(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.countdownTimer = {
    attach: function (context, settings) {
      var d1 = $.now();
      var d2 = $.now();
      var duration = drupalSettings.ttslogic.timer;
      var group_id = drupalSettings.ttslogic.group_id;
      if (duration != 0) {
        duration = parseInt(duration) * 60;
        if ($.cookie("custom_timer") === undefined) {
          $.cookie("custom_timer", d1);
        }
        else {
          d1 = $.cookie("custom_timer");
          d1 = parseInt(d1);
        }
        var to = d1 + (duration * 1000);

        if (to <= d2) {
          window.location.replace("https://" + document.domain + "/timer-save/" + group_id);
        }
        else {
          var time = new Date(to);
          var timer = '';
          $("#block-custom-sub-timerblock").countdown(time, function (event) {
            if (to <= d2) {
              window.location.replace("https://" + document.domain + "/timer-save/" + group_id);
            }
            timer = event.strftime('%M:%S');
            $(this).text(timer);
          });
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
