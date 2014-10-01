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
            if ($(input).attr('id') != 'fio' && $(input).val() == "") {
              var $item = $(input).closest('.item'),
                  error_box = $item.find('.error_msg');
              $item.addClass('error').find('.error_msg').html();
              error_box.html($(error_box).data('missing'));
              e.preventDefault();
            } else if ($(input).attr('id') == 'fio' && $(input).val().length > 20) {
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
Share = {
    /**
     * Показать пользователю дилог шаринга в сооветствии с опциями
     * Метод для использования в inline-js в ссылках
     * При блокировке всплывающего окна подставит нужный адрес и ползволит браузеру перейти по нему
     *
     * @example <a href="" onclick="return share.go(this)">like+</a>
     *
     * @param Object _element - элемент DOM, для которого
     * @param Object _options - опции, все необязательны
     */
    go: function(_element, _options) {
        var
            self = Share,
            options = $.extend(
                {
                    type:       'vk',    // тип соцсети
                    url:        location.href,  // какую ссылку шарим
                    count_url:  location.href,  // для какой ссылки крутим счётчик
                    title:      document.title, // заголовок шаринга
                    image:        '',             // картинка шаринга
                    text:       '',             // текст шаринга
                },
                $(_element).data(), // Если параметры заданы в data, то читаем их
                _options            // Параметры из вызова метода имеют наивысший приоритет
            );

        if (self.popup(link = self[options.type](options)) === null) {
            // Если не удалось открыть попап
            if ( $(_element).is('a') ) {
                // Если это <a>, то подставляем адрес и просим браузер продолжить переход по ссылке
                $(_element).prop('href', link);
                return true;
            }
            else {
                // Если это не <a>, то пытаемся перейти по адресу
                location.href = link;
                return false;
            }
        }
        else {
            // Попап успешно открыт, просим браузер не продолжать обработку
            return false;
        }
    },

    // ВКонтакте
    vk: function(_options) {
        var options = $.extend({
                url:    location.href,
                title:  document.title,
                image:  '',
                text:   '',
            }, _options);

        return 'http://vkontakte.ru/share.php?'
            + 'url='          + encodeURIComponent(options.url)
            + '&title='       + encodeURIComponent(options.title)
            + '&description=' + encodeURIComponent(options.text)
            + '&image='       + encodeURIComponent(options.image)
            + '&noparse=true';
    },

    // Одноклассники
    ok: function(_options) {
        var options = $.extend({
                url:    location.href,
                text:   '',
            }, _options);

        return 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1'
            + '&st.comments=' + encodeURIComponent(options.text)
            + '&st._surl='    + encodeURIComponent(options.url);
    },

    // Facebook
    fb: function(_options) {
        var options = $.extend({
                url:    location.href,
                title:  document.title,
                image:  '',
                text:   '',
            }, _options);

        return 'http://www.facebook.com/sharer.php?s=100'
            + '&p[title]='     + encodeURIComponent(options.title)
            + '&p[summary]='   + encodeURIComponent(options.text)
            + '&p[url]='       + encodeURIComponent(options.url)
            + '&p[images][0]=' + encodeURIComponent(options.image);
    },

    // Живой Журнал
    lj: function(_options) {
        var options = $.extend({
                url:    location.href,
                title:  document.title,
                text:   '',
            }, _options);

        return 'http://livejournal.com/update.bml?'
            + 'subject='        + encodeURIComponent(options.title)
            + '&event='         + encodeURIComponent(options.text + '<br/><a href="' + options.url + '">' + options.title + '</a>')
            + '&transform=1';
    },

    // Твиттер
    tw: function(_options) {
        var options = $.extend({
                url:        location.href,
                count_url:  location.href,
                title:      document.title,
            }, _options);

        return 'http://twitter.com/share?'
            + 'text='      + encodeURIComponent(options.title)
            + '&url='      + encodeURIComponent(options.url)
            + '&counturl=' + encodeURIComponent(options.count_url);
    },

    // Mail.Ru
    mr: function(_options) {
        var options = $.extend({
                url:    location.href,
                title:  document.title,
                image:  '',
                text:   '',
            }, _options);

        return 'http://connect.mail.ru/share?'
            + 'url='          + encodeURIComponent(options.url)
            + '&title='       + encodeURIComponent(options.title)
            + '&description=' + encodeURIComponent(options.text)
            + '&imageurl='    + encodeURIComponent(options.image);
    },

    // Открыть окно шаринга
    popup: function(url) {
        return window.open(url,'','toolbar=0,status=0,scrollbars=1,width=626,height=436');
    }
}

$(document).on('click', '.social_share', function(){
    Share.go(this);
	return false;
});