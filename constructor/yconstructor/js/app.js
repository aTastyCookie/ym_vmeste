var Konstructor = (function (app, $) {

  function visual() {
//    $('body, html').animate({scrollTop: '0px'}, 100);
    var scrollr,
        $w = $(window),
        $d = $(document),
        $_page_form = $('#page_form'),
        $_image_box = $('#image_box'),
        $_image_file = $('#image_file'),
        $_page_data = $('#page_data'),
        $_page_theme = $('#page_theme');


    var opts = {
      lines: 13, // The number of lines to draw
      length: 20, // The length of each line
      width: 10, // The line thickness
      radius: 30, // The radius of the inner circle
      corners: 1, // Corner roundness (0..1)
      rotate: 0, // The rotation offset
      direction: 1, // 1: clockwise, -1: counterclockwise
      color: '#fff', // #rgb or #rrggbb or array of colors
      speed: 1, // Rounds per second
      trail: 60, // Afterglow percentage
      shadow: false, // Whether to render a shadow
      hwaccel: false, // Whether to use hardware acceleration
      className: 'spinner', // The CSS class to assign to the spinner
      zIndex: 2e9, // The z-index (defaults to 2000000000)
      top: '50%', // Top position relative to parent
      left: '50%' // Left position relative to parent
    };
    var target = document.getElementById('preloader');
    var spinner = new Spinner(opts).spin(target);


    $w
        .on('load', function () {
          $('body').removeClass('before_load');

          scrollr = skrollr.init({
//      forceHeight: false,
            smoothScrolling: false
          });
        })
        .on('scroll', function () {
        });

    $d
        .on('change', '#page_theme input', function () {
          $this = $(this);
          $_page_form.attr('class', 'page_form');
          $_page_theme.find('.input_field').attr('class', 'input_field');

          if ($(this).prop('checked') === true) {
            $_page_form.addClass($this.closest('label').attr('class') + " styled");
          }
          $_page_theme.attr('class', 'page_theme');

        })
        .on('change', '#image_file', function () {
          var _this = this;
          $_page_data.attr('class', 'page_data');

          $.when(readURL(_this)).done(function (img) {
            $_page_data.hide().show(0);
            if (!$_page_theme.find('input:checked').length) {
              $_page_theme.addClass('show_tip');
            }

            if (img.width / img.height > 1.7) {
              $_page_data.addClass('narrow');
            }
            $_page_data.addClass('has_image');
            if ($_image_box.find('img').length) {
              $_image_box.find('img').replaceWith(img);
            }
            else {
              $_image_box.find('.image').html(img);
            }
          });

        })
        .on('click', '#image_box .remove_icon', function (e) {
          $_image_file.val('');
          $_image_box.find('img').remove();
          $_page_data.attr('class', 'page_data');
        })
      // tooltips, erros
        .on('click', '.payment_form .input_field input', function () {
          $(this).closest('.input_field').addClass('show_tip');
        })
        .on('click', function (e) {
          if ($(e.target).closest('.input_field').length === 0) {
            $('.input_field').removeClass('show_tip');
          }
        })
        .on('blur', 'input, textarea', function (e) {
          $('.input_field').removeClass('show_tip');
        })
        .on('keydown', 'input, textarea', function (e) {
          $(this).closest('.input_field').removeClass('error');
        })
      // page scrolls
        .on('click', '.screen1 .make_page', function (e) {
          e.preventDefault();
          if ($d.scrollTop() < 800) {
            $('html, body').scrollTop(800);
          }
          scroll_to($('.screen4').offset().top);
        })
        .on('click', '.section_arrow', function (e) {
          if ($d.scrollTop() < 800) {
            $('html, body').scrollTop(800);
          }
          scroll_to($(this).closest("div [class^='screen']").offset().top);
        })
      // form validations
        .on('submit', '#main_form', function (e) {
          var valid = true;
          $(this).find('.input_field').removeClass('error');
          $(this).find('.input_field input, .input_field textarea').not('#amount').each(function (i, item) {
            var $item = $(item);
            if (!$item.val().replace(/\s/g, '').length) {
              $item.closest('.input_field').addClass('error');
              valid = false;
            }
          });

          // amount validations
          var $amount = $(this).find('#amount');
          if (+$amount.val() > 15000) {
            $amount.closest('.input_field').addClass('error');
            valid = false;
          }

          // theme validations
          if (!$_page_theme.find('input:checked').length) {
            $_page_theme.find('.input_field').addClass('error');
            valid = false;
          }

          // email validation
          if (!validateEmail($('#email_input').val())) {
            $('#email_input').closest('.input_field').addClass('error');
            valid = false;
          }

          // agree validation
          if(!$(this).find('#field_agree').is(":checked")) {
              return false;
          }

          if (!valid) {
            var error_offset = $('.input_field.error').eq(0).offset().top;
            if (error_offset < $w.scrollTop()) {
              scroll_to(error_offset);
            }
            return false;
          }
        });

      var $pageUrl = $('#page_url');
      var pageUrl = $pageUrl.val();

      if (pageUrl) {
          // crazy fix for inputmask
          $pageUrl.val('n' + pageUrl);
      }

      $pageUrl.inputmask({
          mask: "y\\asobe.ru/n\\a/q*{*}",
          showMaskOnHover: false,
          placeholder: '',
          definitions: {
              '*': {
                  validator: "[0-9A-Za-z_]",
                  cardinality: 1,
                  casing: "lower"
              },
              'q': {
                  validator: "[A-Za-z]",
                  cardinality: 1,
                  casing: "lower"
              }
          }
      });

    allow_numbers_only('#amount');

    preload([
      'img/backgrounds/Artboard1.jpg',
      'img/backgrounds/Artboard2.jpg',
      'img/backgrounds/Artboard3.jpg',
      'img/backgrounds/Artboard4.jpg',
      'img/backgrounds/Artboard5.jpg',
      'img/backgrounds/Artboard6.jpg',
      'img/mask/mask1.png',
      'img/mask/mask2.png',
      'img/mask/mask3.png',
      'img/mask/mask4.png',
      'img/mask/mask5.png',
      'img/mask/mask6.png'
    ]);

    // functions

    function scroll_to(offset) {
      $('html,body').animate({scrollTop: offset}, '600');
    }

    function validateEmail(email) {
      var emailReg = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
      var valid = emailReg.test(email);

      if (!valid) {
        return false;
      } else {
        return true;
      }
    }

    function readURL(input) {
      var d = new $.Deferred();
      if (input.files && input.files[0]) {
        var reader = new FileReader(),
            img = new Image();

        reader.onload = function (e) {
          img.src = e.target.result;
          img.onload = function () {
            d.resolve(img);
          };
        };

        reader.readAsDataURL(input.files[0]);
        return d.promise();
      }
    }

    function allow_numbers_only(selector) {
      $(document).on('keydown', selector, function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 27, 13, 110]) !== -1 ||
          // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
          // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
          // let it happen, don't do anything
          return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
          e.preventDefault();
        }
      });
    }

    function preload(arrayOfImages) {
      $(arrayOfImages).each(function () {
          var img = new Image();
          img.src = this;
          img.onload = function(){
              var d = $('<div/>');
              d.css('background-image', 'url("/' + img.src + '")');
              $('#preload').append(d);
          };
      });
    }

  }

  app.init = function () {
    visual();
  };

  $(function () {
    app.init();
  });

  return app;
}(Konstructor || {}, $));
