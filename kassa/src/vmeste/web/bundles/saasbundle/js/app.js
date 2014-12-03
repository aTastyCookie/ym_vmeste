var Promo = (function (app, $) {

  function visual() {
    var $root = $('html, body'),
        sticky_navigation_offset_top = $('#menu_block').offset().top + 185;

    $(document)
        .on('click', '.menu_block a, .get_kassa a, a.down_arrow', function (e) {
          e.preventDefault();
          var href = $.attr(this, 'href');
          $root.animate({
            scrollTop: $(href).offset().top
          }, 500, function () {
            window.location.hash = href;
          });
        })
        .on('click', '.info_tip .info_icon', function () {
          $(this).siblings('.tip_box').show();
        })
        .on('click', '.info_tip .close', function (e) {
          e.preventDefault();
          $(this).closest('.tip_box').hide();
        })
        .on('click', function (e) {
          if ($(e.target).closest('.info_tip').length == 0) {
            $('.info_tip .tip_box').hide();
          }
        })
        .on('submit', '#form', function (e) {

          var error = false;
          var r = /^[\w\.\d-_]+@[\w\.\d-_]+\.\w{2,4}$/i;
          if (!r.test($('#email').val())) {
            $('#email_error').show();
            error = true;
          } else {
            $('#email_error').hide();
          }

          r = /^[\d\+\-\(\)]+$/i;
          if (!r.test($('#phone').val())) {
            $('#phone_error').show();
            error = true;
          } else {
            $('#phone_error').hide();
          }

          if (error) return false;

        })
        .on("keydown", '#email', function (e) {
          $('#email_error').hide();
        })
        .on("keydown", '#phone', function (e) {
          $('#phone_error').hide();
        })
        .on('focus', '#form input, #form select', function(){
          $('.submit_box.done').removeClass('done');
        });

    $(window).on('scroll', function () {
      var scroll_top = $(this).scrollTop();
      sticky_navigation(scroll_top);
      hide_menu_btn(scroll_top);
    });

    function sticky_navigation(scroll_top) {
      $('#menu_block').toggleClass('stick', scroll_top > sticky_navigation_offset_top)
    }

    function hide_menu_btn(scroll_top) {
      if (scroll_top >= $("#kassa_section").offset().top || scroll_top + $(window).height() == $(document).height()) {
        $('#get_kassa_btn').hide();
      }
      else {
        $('#get_kassa_btn').show();
      }
    }

    sticky_navigation($(window).scrollTop());
    hide_menu_btn($(window).scrollTop());

  }

  // run our function on load

  app.init = function () {
    visual();
  };

  $(function () {
    app.init();
  });

  return app;
}(Promo || {}, $));
