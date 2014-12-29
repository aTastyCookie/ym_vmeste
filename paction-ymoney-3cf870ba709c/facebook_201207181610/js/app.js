var Myapp = (function (app, $) {

    function fallbacks() {

        if (window.PIE) {
            $('.ie-pie').each(function () {
                PIE.attach(this);
            });
        }

        if ($('body').hasClass('ie8')) {
            $('#font-css')[0].href = $('#font-css')[0].href;
        }

    }

    function visual() {
        var dir, vel;
        app.scroll_actions = false;

        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {
            return split(term).pop();
        }

        $("#myTags").tagit({
            autocomplete: {
              source: function (request, response) {
      				$.getJSON("http://ymfbru.robimgut.com/rubric/get/", { key: extractLast(request.term)}, function(data){ response(data);});	
      			},
              delay: 0,
              minLength: 2,
              appendTo: '.search .control',
              position: {of: '.search .control', my: 'left top+3' }
            }
        });
        
        $('.action_items .item .q > i').on('click', function () {
            $(this).siblings('.tooltip').show();
        });

        $('.action_items .item').on('hover', function () {
        }, function () {
            $(this).find('.tooltip').hide();
        });

        $('.actions .panel .filter a').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('.main_blocks .close').on('click', function (e) {
            e.preventDefault();
            $(this).parent().hide();
        });

        var page = 1;
        var search = false;
        if($('.search input').val()!="Поиск акций") {
            search = $('.search input').val();
        }
        $('#show_more').on('click', function (e) {
            e.preventDefault();
            app.scroll_actions = true;
            $('.action_items .section').animate({top: '-520px'}, 'slow');
            
            $.ajax({
                url: pageAjaxUrl, 
                type: "GET",
                dataType: "json",
                data: {
                    page: page,
                    search: search,
                    selected: "pages",
                    next: true
                },
                success: function (data) { 
                	alert(data);
                }
            });
        });

        $('.action_items .section').on('mousewheel', function (event, delta) {
            var $this = $(this);
            if (app.scroll_actions == true) {
                if (delta < 0) {
                    if ($this.css('top').replace(/\D/g, '') <= $this.height() - 770) {
                        $this.css('top', '+=' + delta * 20);
                        return false;
                    }
                }
                else {
                    if ($this.css('top') >= '0') {
                        $this.css('top', '0px');
                    }
                    else {
                        $this.css('top', '+=' + delta * 20);
                        return false;
                    }
                }
            }
        });

    }

    app.init = function () {
        fallbacks();
        visual();
    }

    // Call the init handler on document ready. Put the js logic in the function above
    $(function () {
        app.init();
    });

    return app;
}(Myapp || {}, jQuery));
