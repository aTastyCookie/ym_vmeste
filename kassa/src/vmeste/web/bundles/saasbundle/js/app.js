var Donation = (function (app, $) {


  function visual() {

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
            $('#payment_options input').next().children('label').attr('style', 'background-color:#fff');
            $('#payment_options input:checked').next().children('label').attr('style', 'background-color:#ffeba0');
          }

        })
        .on('change', '#times', function () {
          check_payment_options.call(this);
        })

        .on('click', 'a.delete_user', function(e) {
          e.preventDefault();
          if(confirm('Все транзакции, рекурренты, кампании, доноры и настройки, связанные с этим пользователем, будут удалены!')) {
            window.location = $(this).attr('href');
          } else {
            return false;
          }
        });

    check_payment_options.call($('#times'));
  }

  function check_payment_options() {
    var $self = $(this), po = $('#payment_options');
    po.removeClass('fixed_options has_value');
    po.find('input').each(function(){
       $(this).prop('checked', false)
    });
    if ($self.val() == '1') {
      $('#bc_option').trigger('click');
      po.addClass('fixed_options');
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
