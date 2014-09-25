var Donation = (function (app, $) {

  function visual() {
    var $text_inputs = $("#payment_form").find('input[type="text"]');
    $(document)
        .on('click', '#add_comment', function (e) {
          e.preventDefault();
          $('.user_form .fields.comment').show(400);
          $(this).hide('80');
          $('#remove_comment').show('80');
        })
        .on('click', '#remove_comment', function (e) {
          e.preventDefault();
          $('.user_form .fields.comment').hide(400);
          $(this).hide('80');
          $('#add_comment').show('80');
        })
        .on('change', '#payment_options input', function () {
          if ($('#payment_options input:checked').length) {
            $('#payment_options').addClass('has_value');
          }
        })
        .on('change', '#times', function () {
          check_payment_options.call(this);
          check_times();
        })
        .on('keydown', '#payment_form input[type="text"]', function () {
          $(this).closest('.item').removeClass('error');
        })
        .on('submit', '#payment_form', function (e) {
          check_email(e);

          $text_inputs.each(function (i, input) {
            if ($(input).val() == "") {
              var $item = $(input).closest('.item'),
                  error_box = $item.find('.error_msg');
              $item.addClass('error').find('.error_msg').html();
              error_box.html($(error_box).data('missing'));
              e.preventDefault();
            }
          });
        })
        .on('focusout', '#times', function () {
          $('.times_box').removeClass('monthly')
        });

    function check_email(e) {
      var r = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if (!r.test($('#email').val())) {
        $('#email').closest('.item').addClass('error');
        var error_box = $('#email').closest('.item').find('.error_msg');
        error_box.html($(error_box).data('invalid'));
        e.preventDefault();
      } else {
        $('#email').closest('.item').removeClass('error');
      }
    }

    check_payment_options.call($('#times'));
  }

  function check_payment_options() {
    var $self = $(this), po = $('#payment_options');
    po.removeClass('fixed_options has_value');
    po.find('input').each(function () {
      $(this).prop('checked', false)
    });
    if ($self.val() == '1') {
      po.addClass('fixed_options');
      $("#monthly_type_icon").show();
      $("#all_payments").slideUp('slow',function(){
        $('#bc_option').trigger('click');
      });
    }
    else{
      $('#payment_options input').prop('checked', false);
      $("#all_payments").slideDown();
      $("#monthly_type_icon").hide();

    }

  }

  function check_times() {
    if ($('#times').val() == '1') {
      $('.times_box').addClass('monthly')
    }
    else {
      $('.times_box').removeClass('monthly')
    }
  }

  app.init = function () {
    visual();
  };

  $(function () {
    app.init();
  });

  return app;
}(Donation || {}, jQuery));
