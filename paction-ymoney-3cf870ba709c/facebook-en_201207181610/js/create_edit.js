//Преобразует в строку в число
function toHumanNumber(num)
{
	return Number(num.replace(' ', '').replace(',', '.')); 
}

//Получаем количествово млн.
function mln(num)
{
	if(num%1000000>99999) {
                if((num/1000000).toFixed(1) >= 1000){
                        return mlrd(num);
                }
		return (num/1000000).toFixed(1);
	} else {
		return Math.round(num/1000000);
	}
}

//Получаем количествово млрд.
function mlrd(num)
{
	if(num%1000000000>99999999) {
		return (num/1000000000).toFixed(1);
	} else {
		return Math.round(num/1000000000);
	}
}

function setrightwreqC() 
{
	var cspb = toHumanNumber($('#current_sum').val())+curbal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+' billion</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	}
}

function setrightworeqC() 
{
	var cspb = toHumanNumber($('#current_sum').val())+curbal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion rub.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million rub.</span>');
	} else {
		$('.right .mb_collected').html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span>');
	}
}

function setrightWoReqWBalC() 
{
	var cspb = curbal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion rub.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million rub.</span>');
	} else {
		$('.right .mb_collected').html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span>');
	}
}

function setrightWithReqWoBalC() 
{
	var cspb = curbal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+' billion</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	}
}

function setrightwreq() 
{
	var cspb = toHumanNumber($('#current_sum').val())+bal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+' billion</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	}
}

function setrightworeq() 
{
	var cspb = toHumanNumber($('#current_sum').val())+bal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion rub.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million rub.</span>');
	} else {
		$('.right .mb_collected').html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span>');
	}
}

function setrightWoReqWBal() 
{
	var cspb = bal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span>billion rub.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million rub.</span>');
	} else {
		$('.right .mb_collected').html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span>');
	}
}

function setrightWithReqWoBal() 
{
	var cspb = bal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>billion rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+' billion</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> of <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>million rub.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mlrd(cspb)+'</span> <span> billion</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+cspb+'</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	} else {
		$('.right .mb_collected')
		.html('Collected <span class="s1">'+mln(cspb)+'</span> <span>million</span> of <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>rub.</span>');
	}
}

//Форматирует строку значения Всего
function formatMoney(newbal) {
    var newMoneyVal;
    if(newbal >= 1000000000) {
        newMoneyVal = mlrd(newbal) + ' billion ';
    } else if(newbal >= 1000000) {
        newMoneyVal = mln(newbal) + ' million ';
    } else {
        newMoneyVal = newbal;
    }
    
    return newMoneyVal;
}

function sumbalance()
{
    var newbal = bal;
//    if(!isNaN(toHumanNumber($('#current_sum').val())) && $('#cash_variants input').attr('value')==0) {
            newbal = toHumanNumber($('#current_sum').val())+bal;
//    } else if(!isNaN(toHumanNumber($('#current_sum').val())) && $('#cash_variants input').attr('value')!=0) {
//            newbal = toHumanNumber($('#current_sum').val());
//    }
//    if($('#cash_variants input').attr('value')!=0) {
//            $('.about_money td.inp p').first().html('rub.');
//    } else {
            $('.about_money td.inp p').first().html('rub. (a total of &ndash; <span>' + formatMoney(newbal) + ' rub.</span>)');
//    }
}

function updateRightBalance()
{
    if($('input[name="source"]').val()!='2' && $('input[name="source"]').val()!='1') {
        curbal = bal;
    }
    if(!isNaN(toHumanNumber($('#required_sum').val())) && !isNaN(toHumanNumber($('#current_sum').val()))) {
        if($('#required_sum').val()>0) {
            setrightwreqC();
        } else {
            setrightworeqC();
        }
    } else if(isNaN(toHumanNumber($('#required_sum').val())) && !isNaN(toHumanNumber($('#current_sum').val()))) {
        setrightworeqC();
    } else if(!isNaN(toHumanNumber($('#required_sum').val())) && isNaN(toHumanNumber($('#current_sum').val()))) {
        setrightWithReqWoBal();
    } else {
        setrightWoReqWBalC();
    }
}

function createUploader() {
    var uploader = new qq.FileUploader({
        element: $('#popup_tpl #file-uploader-demo1')[0],
        action: '/actions/loadimageajax',
        debug: false,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        onComplete: function(id, fileName, responseJSON) {
            data = JSON.parse(responseJSON.data);
            $('.p_pad #photos').prepend('<a class="selectphoto" pid="photo'+data[0].pid+'" href="#"><img src="'+data[0].src+'" /></a>');
            $('.listphoto #photos').prepend('<a class="selectphoto" pid="photo'+data[0].pid+'" href="#"><img src="'+data[0].src+'" /></a>');
            $('.p_pad a[pid="photo'+data[0].pid+'"]').click();
            $('.qq-upload-list').empty();
            FB.Canvas.setAutoGrow();
        }
    });
}

function checkname() {
	var name = $('#name').val();

	$.ajax({
        url: checknameAjaxUrl,
        type: "GET",
        dataType: "json",
        data: {
            name: name,
            id: action_id
        },
        success: function (data) { 
            if(data == true) {
                $('#name').css("border", '1px solid #F00');
                goodname = false;
            } else {
                $('#name').css("border", '1px solid #BDC6D9');
                goodname = true;
            }
        }
	});
}

$(document).ready(function(){
    var formValidator = $('#actionform').validate({
        errorPlacement: function(error, element) {
            element.css("border", 'red 1px solid');
        },
        rules: {
            'video': {
                url: true
            },
            'current_sum': {
                number: true
            },
            'required_sum': {
                number: true
            },
            'required_sum_group': {
                number: true
            }
        }
    });
    
    $('#canсelbutton').click(function() {
        location.replace(previousUrl); 
    });
    
    /* add photo */
    $('.selectphoto').live('click', function(){
        if($(this).attr('class') == 'selectphoto selected') {
                $(this).removeClass('selected');
                $('.listphoto a[pid="'+$(this).attr('pid')+'"]').removeClass('selected');
                $('.photocontainer').find('#'+$(this).attr('pid').replace('photo', 'img')).remove();
        } else {
                $(this).addClass('selected');
                $('.listphoto a[pid="'+$(this).attr('pid')+'"]').addClass('selected');
                $('.photocontainer').append('<table id="'+$(this).attr('pid').replace('photo', 'img')+'"><tr><td><a href="#" class="del_photo"></a><input type="hidden" name="photo['+fileCounter+']" value="'+$(this).attr('pid').replace('photo', '')+'" /><img src="'+$(this).find('img').attr('src')+'" valign="middle"/></td></tr></table>');
            fileCounter++;
        }
        FB.Canvas.setAutoGrow();
    });
       
    $('.del_photo').live('click', function() {
        var table_id = $(this).siblings('input').val();
        $('.listphoto a[pid=photo'+table_id+']').removeClass('selected');
        $('table#img'+table_id).remove();
        FB.Canvas.setAutoGrow();
        return false;
    });
    
    var offset = 0;
    var first = true;
    $('.add_photo a').click(function() {
        $(this).fb_popup({
            content_container: '.listphoto',
            //content:'',
            title: 'Select photo',
            popup_id: 'popup_tpl',
            width: 760,
            height: 450,
            overflow: 'auto'
        });
        createUploader();
        if(first == true) {
            loadPhotos(offset);
        }
        first = false;

        return false;
    });

    function loadPhotos(offset) {
        params = {};
        params["offset"] = offset;
        if(action_id != 0) {
            params["action_id"] = action_id;
        }
        if(adminEdit == 1) {
            params["action_user"] = action_user;
        }
        $.ajax({
            url: getphotoAjaxUrl,
            type: "POST",
            dataType: "json",
            data: params,
            success: function (data) { 
                if(data.length > 0) {
                    $('.morephoto').remove();
                    jQuery.each(data, function(i, element){
                        var selected = '';
                        if(element['selected'] == 1) {
                            selected = ' selected';
                    	}
                        $('.p_pad #photos').append('<a class="selectphoto'+selected+'" pid="photo'+element['pid']+'" href="#"><img src="'+element['src']+'" /></a>');
                        $('.listphoto #photos').append('<a class="selectphoto'+selected+'" pid="photo'+element['pid']+'" href="#"><img src="'+element['src']+'" /></a>');
                    });
                    $('.p_pad').append('<div class="morephoto"><a href="#">More...</a></div>');
                    $('.listphoto').append('<div class="morephoto"><a href="#">More...</a></div>');
                    if(data.length<50) {
                        $('.morephoto').remove();
                    }
                    FB.Canvas.setAutoGrow();
                } else {
                    $('.morephoto').remove();
                }
            }                
        });            
    }

    $('.morephoto').live("click", function(){
        loadPhotos(offset);
        offset += 50;

        return false;
    });
    
    if($('#groupselect :selected').val() == 1) {
        $('.about_friends, .grouptr').slideToggle('slow');
        $('#group').val(1);
    }
    $('#groupselect').change(function(){
        $('.about_friends, .grouptr').slideToggle('slow');
        $('#group').val() == 0 ? $('#group').val(1) : $('#group').val(0);
        FB.Canvas.setAutoGrow();
    });
    
    $(".right .mini_block .mb_title div").empty().html($('#' + $('#who :selected').val()).html());
    $('#who').change(function(){
         cur = $('#who :selected').val();
         $(".right .mini_block .mb_title div").empty().html($('#' + cur).html());
    });
    FB.Canvas.setAutoGrow();
});

$(document).ajaxStart(function(){ 
    $('.morephoto').append('<span class="loading"></span>'); 
}).ajaxStop(function(){ 
    $('.morephoto span').remove();
});