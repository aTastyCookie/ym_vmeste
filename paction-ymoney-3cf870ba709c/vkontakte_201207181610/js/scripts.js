function settime() {
    var d = new Date();
    var monthNamesShort = $( ".datepicker" ).datepicker( "option", "monthNamesShort" );
    var time1 = $('.date_start').val();
    
    var time2 = $('.date_end').val();
    new_time1 = (time1.substring(5,7) * 1) - 1;
    new_time1_day = time1.substring(8) * 1;
    new_time1_year = time1.substring(0,4) * 1;
    new_time2 = (time2.substring(5,7) * 1) - 1;
    new_time2_day = time2.substring(8) * 1;
    new_time2_year = time2.substring(0,4) * 1;
    if($('#signal').prop('checked')) {	
        $('.mb_timer').html('С ' + new_time1_day + ' ' + monthNamesShort[new_time1]);

    } else {
        /*$('#date2 .datepicker').attr('disabled', false);
        var end_date = $('#date2 .datepicker').val();
        $('.date_end').val(end_date);
        var start_date = $('#date1 .datepicker').val();
        $('.date_start').val(start_date);
        //alert($('.date_start').val());*/
        var sp = $('.mb_timer span').html();
        if(sp == null) {
            sp = '';
        }
        var year1 = (new_time1_year != d.getFullYear()) ? new_time1_year : '';
        var year2 = (new_time2_year != d.getFullYear() || year1 != '') ? new_time2_year : '';
        $('.mb_timer').html(new_time1_day + ' ' + monthNamesShort[new_time1] + ' ' + year1 + ' &mdash; ' + new_time2_day + ' ' + monthNamesShort[new_time2] + 
            ' ' + year2 + ' <span>' + sp + '</span>');

    }

    if( $('.date_end').val() == '0') {
        $('.mb_timer').html('С ' + new_time1_day + ' ' + monthNamesShort[new_time1]);
    }
	
    $('#signal').change(function() {
        if($(this).prop('checked')) {
            $('.mb_timer').html('С ' + new_time1_day + ' ' + monthNamesShort[new_time1]);
        } 
    });
	
}


function hideRight() {
    if($('#name').val().length==0 &&
        ($('#current_sum').val().replace(' ', '').replace(',', '.')==0 || $('#current_sum').val().length==0) &&
        ($('#required_sum').val().replace(' ', '').replace(',', '.')=='' || $('#required_sum').val().length==0)) {
        $('.right').hide('slow');
        return true;
    } else return false;
}

function showRight() {
    $('.right').show('slow');
}

/* SELECTS INIT */
$(function() {
    var sel_clicked=0;
    $('.select1').each(function(SI) {
        $(this).css('zIndex',1000-SI);
        $(this).click(function() {
            sel_clicked=this;
        });
        $('div a', this).click(function() {
            $('span',this.parentNode.parentNode).html($(this).html());
            $('input.settable',this.parentNode.parentNode).attr('value',$(this).attr('rel'));
            if($('#date1 span').size()>0) {
                var time1 = $('#date1 span').html();
                var time2 = $('#date2 span').html();
                $('.mb_timer').html(time1 + ' &mdash; ' + time2);
            }

            $(this.parentNode.parentNode).removeClass('sel_open');
            if($('#statform').size()==1) {
                $('#statform').submit();
            }
            return false;
        });
    });
    $('body').click(function() {
        $('.select1').removeClass('sel_open');
        if(sel_clicked!=0) $(sel_clicked).addClass('sel_open');
        sel_clicked=0;
    });
});

/* SLIDER INIT */
$(function() {
    if($('.slider').hasClass('slider')) {
        $('.slider').each(function(SLI) {
            /* вычисляем количество страниц */
            var movable=this;
            var boxSize=$(movable).width(); /* размер содержащего бокса, заодно и шаг смещения при переключении страниц */
            var boxEl=$('li',movable).width();
            var elCont=Math.floor(boxSize/boxEl);
            pgCount=Math.ceil($('li',movable).length/elCont);//alert(pgCount);
            /* очищаем пагинатор */
            $('.slider_controller').eq(SLI).html('');
            /* добавляем страницы в нужном количестве */
            if(pgCount>1) {
                for(i=0;i<pgCount;i++) {
                    $('.slider_controller').eq(SLI).append('<a href="#"></a>');
                }
            }
            /* устанавливаем первую страницу текущей */
            var CEl=$('.slider_controller').eq(SLI);
            $('a',CEl).eq(0).addClass('curr');
            /* вешаем на пагинатор event для переключения страниц */
            $('a',CEl).each(function(CEI) {
                $(this).click(function() {
                    $('ul',movable).animate({
                        left:-(CEI*boxSize)
                        }, 720);
                    $('a',CEl).removeClass('curr');
                    $(this).addClass('curr');
                    return false;
                });
            });
        });
    }
});

/* Image|video gallery init */
$(function() {
    $('.sliding_img').fancybox();
    $('.sliding_video').click(function() {
        $.fancybox({
            'padding'		: 0,
            'autoScale'		: false,
            'transitionIn'	: 'none',
            'transitionOut'	: 'none',
            'title'		: this.title,
            'width'		: 680,
            'height'		: 495,
            'href'		: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
            'type'		: 'swf',
            'swf'		: {
                'wmode'                 : 'transparent',
                'allowfullscreen'	: 'true'
            }
        });
        return false;
    });
});

/* ADD SITE/PHOTO/VIDEO */
$(function() {
    /* add rows */
    $('.add_link a').click(function() {
        $('.add_tr').removeClass('add_tr_closed');
        $('#add_link').addClass('add_tr_closed');
        var hframe = $('.wrapper').height(); 
        VK.callMethod('resizeWindow', 770, hframe);
        return false;
    });
    
    $('.add_link2 a').live("click", function() {
        $('.add_tr2').removeClass('add_tr2_closed');
        $('#add_link2').addClass('add_tr2_closed');
        var hframe = $('.wrapper').height(); 
        VK.callMethod('resizeWindow', 770, hframe);
        return false;
    });
});
/* disable the second datepicker if checkbox is checked and set date value */

$(function() {
    var months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    $('.datepicker').not('#from, #to, #fromstat, #tostat').datepicker();
    var dates = $( "#from, #to" ).datepicker({
        //                        dateFormat: 'dd MM',
        onSelect: function( selectedDate ) {
            var option = this.id == "from" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),	
            date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );
            var datestamp = $.datepicker.parseDate('yy-mm-dd', selectedDate, instance.settings);
            var textdate = datestamp.getDate() + ' ' + months[datestamp.getMonth()];
//                                dateText = $.datepicker.formatDate(
//                                        'd MM',
//                                        new Date(selectedDate.selectedYear, selectedDate.selectedMonth, selectedDate.selectedDay),
//                                        {monthNames: month}
//                                );
            var otherid = (this.id == "from") ? "#to" : "#from";
            (this.id == "from") ? (date.setDate(date.getDate() + 1)) : (date.setDate(date.getDate() - 1));
            $(otherid).datepicker( "option", option, date );
            var otherclass = (this.id == "from") ? ".date_end" : ".date_start";
            if($(otherclass).val().length>0 && $(otherclass).val() != 0) {
                var otherdatestamp = $.datepicker.parseDate('yy-mm-dd', $(otherclass).val(), instance.settings);
                $(otherid).val((otherdatestamp.getDate()) + ' ' + months[otherdatestamp.getMonth()]);
            }
            if(this.id == "from") {
                $('.date_start').val(selectedDate);
            } else {
                $('.date_end').val(selectedDate);
            }
//                                inst.input.val(dateText);
            $(this).val(textdate);
            settime();
        }
    });
	
    var dates = $( "#fromstat, #tostat" ).datepicker({
        onSelect: function( selectedDate ) {
            var option = this.id == "fromstat" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
            var datestamp = $.datepicker.parseDate('yy-mm-dd', selectedDate, instance.settings);
            var textdate = (datestamp.getUTCDate()+1) + ' ' + months[datestamp.getMonth()];
            var otherid = (this.id == "fromstat") ? "#tostat" : "#fromstat";
            $(otherid).datepicker( "option", option, date );
            var otherclass = (this.id == "fromstat") ? "#stat_to" : "#stat_from";
            if($(otherclass).val().length>0) {
                var otherdatestamp = $.datepicker.parseDate('yy-mm-dd', $(otherclass).val(), instance.settings);
                $(otherid).val((otherdatestamp.getUTCDate()+1) + ' ' + months[otherdatestamp.getMonth()]);
            }
			
            if(this.id == "fromstat") {
                $('#stat_from').val(selectedDate);
            } else {
                $('#stat_to').val(selectedDate);
            }     
            $(this).val(textdate);
            $('#statform').submit();
        }
    });

    $('#signal').change(function() {
        if($(this).is(':checked')) {
            $(this).attr('checked', true);
            $('#date2 .datepicker').val('');
            $('.date_end').val('0');
            $('#date1 .datepicker').datepicker("option", "maxDate", null);
        } else {
            $(this).attr('checked', false);
            var end_date = $('#date2 .datepicker').val();
            $('.date_end').val(end_date);
        }
    });

/*$('#date1 .datepicker').change(function() {
        var start_date = $(this).val();
        $('.date_start').val(start_date);
        alert($(this).val());
    });*/
//    $('#date3 .datepicker').change(function() {
//        var ymfromdate = $(this).val();
//        var datestamp = $.datepicker.parseDate('yy-mm-dd', ymfromdate);
//		var textdate = (datestamp.getUTCDate()+1) + ' ' + months[datestamp.getMonth()];
//        $('.ymfromdate').val(ymfromdate);
//        $(this).val(textdate);
//    });
/*$('#date2 .datepicker').change(function() {
       var end_date = $(this).val();
       $('.date_end').val(end_date);
       //alert($('.date_end').val());
    });
    $('#datestat1 .datepicker').change(function() {
        var stat_from = $(this).val();
        $('#stat_from').val(stat_from);
        $('#statform').submit();
    });
    $('#datestat2 .datepicker').change(function() {
        var stat_to = $(this).val();
        $('#stat_to').val(stat_to);
        $('#statform').submit();
    });*/
    
});


/* ABOUT MONEY */

$(function() {
    /* select variant */
    var bal = $('.about_money td.inp span').html()*1;
    $('#cash_variants li').each(function(CVI) {
        if($(this).hasClass('curr') && CVI==0) {
            if($('#required_sum').val()>0) {
                $('.right .mb_collected').html('Собрано <span class="s1">'+(($('#current_sum').val()*1)+bal)+'</span> из <span class="s2">' + ($('#required_sum').val()*1) + '</span> <span>руб.</span>');
            } else {
                $('.right .mb_collected').html('Собрано <span class="s1">'+(($('#current_sum').val()*1)+bal)+'</span> <span>руб.</span>');
            }
        }
        $(this).click(function() {
            $('#cash_variants li').removeClass('curr');
            $(this).addClass('curr');
            $('#cash_variants input').attr('value',CVI);
            /* show/hide hidden rows */
            if(CVI==0||CVI==2) {
                $('.mbegin').addClass('tr_closed');
            }
            if(CVI==1) {
                $('.mbegin').removeClass('tr_closed');
            }
            $('.cv_info').removeClass('cv_show');
            $('.cv_info').eq(CVI).addClass('cv_show');
            if(CVI==0) {
                if($('#required_sum').val()>0) {
                    $('.right .mb_collected').html('Собрано <span class="s1">'+(($('#current_sum').val()*1)+bal)+'</span> из <span class="s2">' + ($('#required_sum').val()*1) + '</span> <span>руб.</span>');
                } else {
                    $('.right .mb_collected').html('Собрано <span class="s1">'+(($('#current_sum').val()*1)+bal)+'</span> <span>руб.</span>');
                }
            }
        });
    });
});

$(function() {
    $('#action_is_closed a').click(function(){
        $('#action_is_closed').remove();
    });
});