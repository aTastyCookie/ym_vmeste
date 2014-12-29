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
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+' млрд</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	}
}

function setrightworeqC() 
{
	var cspb = toHumanNumber($('#current_sum').val())+curbal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд руб.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн руб.</span>');
	} else {
		$('.right .mb_collected').html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span>');
	}
}

function setrightWoReqWBalC() 
{
	var cspb = curbal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд руб.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн руб.</span>');
	} else {
		$('.right .mb_collected').html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span>');
	}
}

function setrightWithReqWoBalC() 
{
	var cspb = curbal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+' млрд</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<=999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	}
}

function setrightwreq() 
{
	var cspb = toHumanNumber($('#current_sum').val())+bal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+' млрд</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	}
}

function setrightworeq() 
{
	var cspb = toHumanNumber($('#current_sum').val())+bal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд руб.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн руб.</span>');
	} else {
		$('.right .mb_collected').html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span>');
	}
}

function setrightWoReqWBal() 
{
	var cspb = bal;
	if(cspb>=1000000 && cspb>=1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span>млрд руб.</span>');
	} else if (cspb>=1000000 && cspb<1000000000) {
		$('.right .mb_collected').html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн руб.</span>');
	} else {
		$('.right .mb_collected').html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span>');
	}
}

function setrightWithReqWoBal() 
{
	var cspb = bal;
	if($('#required_sum').val()>999999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} else if($('#required_sum').val()>999999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(toHumanNumber($('#required_sum').val())) + '</span> <span>млрд руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+' млрд</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()>999999 && cspb<1000000000 && cspb>=1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> из <span class="s2">'
    		+ mln(toHumanNumber($('#required_sum').val())) + '</span> <span>млн руб.</span>');
	} 

	else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb>=1000000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mlrd(cspb)+'</span> <span> млрд</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else if($('#required_sum').val()<=999999999 && $('#required_sum').val()<999999 && cspb<1000000000 && cspb<1000000) {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+cspb+'</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	} else {
		$('.right .mb_collected')
		.html('Собрано <span class="s1">'+mln(cspb)+'</span> <span>млн</span> из <span class="s2">'
    		+ toHumanNumber($('#required_sum').val()) + '</span> <span>руб.</span>');
	}
}

//Форматирует строку значения Всего
function formatMoney(newbal) {
    var newMoneyVal;
    if(newbal >= 1000000000) {
        newMoneyVal = mlrd(newbal) + ' млрд. ';
    } else if(newbal >= 1000000) {
        newMoneyVal = mln(newbal) + ' млн. ';
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
//        newbal = toHumanNumber($('#current_sum').val());
//    }
//    if($('#cash_variants input').attr('value')!=0) {
//        $('.about_money td.inp p').first().html('руб.');
//    } else {
        $('.about_money td.inp p').first().html('руб. (всего &ndash; <span>' + formatMoney(newbal) + ' руб.</span>)');
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
    var upload_url = '';
    VK.api("photos.getAlbums", {uid: uid}, function(data) {        
        aid = 0;
        for (var i in data.response) {
            if(data.response[i].title == 'Собирайте деньги')
                aid = data.response[i].aid;
        }
        if(aid == 0) {
            VK.api("photos.createAlbum", {title:"Собирайте деньги", privacy:0}, function(data) { 
                aid = data.response.aid;
                // получаем url для закачки изображения
                VK.api("photos.getUploadServer", {aid: aid}, function(data) {
                    if(data.response.upload_url) {
                        $("#urlh").val(data.response.upload_url);
                        uploader.setParams({
                            url: data.response.upload_url
                        });
                        $("#aid").val(aid);
                    }
                });
            });
        } else {
            VK.api("photos.getUploadServer", {aid: aid}, function(data) {
                if(data.response.upload_url) {
                    $("#urlh").val(data.response.upload_url);
                    upload_url = data.response.upload_url;
                    uploader.setParams({
                        url: data.response.upload_url
                    });
                    $("#aid").val(aid);
                }
            });
        }
    });
    var uploader = new qq.FileUploader({
        element: $('#popup_tpl #file-uploader-demo1')[0],
        action: '/actions/loadimageajax',
        debug: false,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        params: {
//            url: $("#urlh").val()
            url: upload_url
            
        },
        onComplete: function(id, fileName, responseJSON) {
            data = jQuery.parseJSON(responseJSON.data);
            
            VK.api("photos.save", {aid: data.aid, hash: data.hash,
                server: data.server, photos_list: data.photos_list},
                function(responseJSON) {
                    data = responseJSON.response;
                    $('.p_pad #photos').prepend('<a class="selectphoto" pid="photo'+data[0].pid+'" href="#"><img src="'+data[0].src+'" /></a>');
                    $('.listphoto #photos').prepend('<a class="selectphoto" pid="photo'+data[0].pid+'" href="#"><img src="'+data[0].src+'" /></a>');
                    $('.p_pad a[pid="photo'+data[0].pid+'"]').click();  
                }
            );
            
            $('.qq-upload-list').empty();
        }
    });
}

function checkname()
{
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

function addTableRow(jQtable, str){
    jQtable.each(function(){
        var $table = $(this);
        // Number of td's in the last table row
        var n = $('tr:last td', this).length;
        var tds = '<tr>';
            tds += str;
        tds += '</tr>';
        if($('tbody', this).length > 0){
            $('tbody', this).append(tds);
        }else {
            $(this).append(tds);
        }
    });
}

$(document).ready(function(){
    VK.api("groups.get", {uid: current_user.Uid, extended: 1, filter: (adminEdit == 1) ? '' : 'admin'}, function(data) {  
	
        if(typeof data.response != 'undefined' && data.response.length > 1) {
            if(adminEdit != 1) {
                console.log(current_user);
                $.ajax({
                    url: '/actions/savepagesajax',
                    type: "POST",
                    dataType: "json",
                    data: data,
                    success: function (data) { 

                    }                
                });            
                var selected = false;
                str = '<tr id="add_link2"><td></td><td class="add_link2"><a href="#">Изменить автора акции</a></td></tr>';
                str += '<tr class="add_tr2 add_tr2_closed"><td>От кого публиковать:</td><td class="inp">';
                str += '<select name="who" id="who">'
                str += '<option value="' + ((current_user.Uid !== undefined) ? current_user.Uid : uid) + '">' + current_user.Username + '</option>';
                for (var i in data.response) {
                    if(i > 0) {
                        str += '<option value="' + data.response[i].gid + '"' + (data.response[i].gid == page_id ? ' selected' : '') + '>' + data.response[i].name + '</option>';
                        selected = true;
                    }
                }
                str += '</select></td></tr>';
    //            addTableRow($('#edit_table'), str);
                $('#edit_table').append(str);
                if(selected == true) {
                    $('.add_tr2').removeClass('add_tr2_closed');
                    $('#add_link2').addClass('add_tr2_closed');
                }
                var hframe = $('.wrapper').height(); 
                VK.callMethod('resizeWindow', 770, hframe);

                str = '<div id="' + ((current_user.Uid !== undefined) ? current_user.Uid : uid) + '"><a href="' + current_user.Userurl + '" target="_blank">';		
                str += current_user.Username.length <= 18 ? current_user.Username
                    : current_user.Firstname.substr(0,1) + '. ' + current_user.Lastname;
                str += '</a></div>';
                for (var i in data.response) {
                    if(i > 0) {
                        str += '<div id="' + data.response[i].gid + '"><a href="http://vk.com/club' + data.response[i].gid + '" target="_blank">';		
                        str += data.response[i].name.length <= 50 ? data.response[i].name : (data.response[i].name.substr(0,49) + '...');
                        str += '</a></div>';
                    }
                }
                $('#tmp_div').html(str);
            }
        }
        if($('#who').length) {
            $(".right .mini_block .mb_title div").html($('#' + $('#who :selected').val()).html());
        }
        
    });
    
//    VK.api("photos.getAlbums", {uid: uid}, function(data) {        
//        aid = 0;
//        for (var i in data.response) {
//            if(data.response[i].title == 'Собирайте деньги')
//                aid = data.response[i].aid;
//        }
//        if(aid == 0) {
//            VK.api("photos.createAlbum", {title:"Собирайте деньги", privacy:0}, function(data) { 
//                aid = data.response.aid;
//            });
//        }
//        // получаем url для закачки изображения
//        VK.api("photos.getUploadServer", {aid: aid}, function(data) {
//            if(data.response.upload_url) {
//                $("#urlh").val(data.response.upload_url);
//            }
//        });
//        $("#aid").val(aid);
//    });
    
    $('#canselbutton').click(function() {
//        history.back();
        location.replace(previousUrl); 
    });
    
    var offset = 0;
    var loadP = true;
    $('.add_photo > a').click(function() {
        $(this).fb_popup({
            content_container: '.listphoto',
            //content:'',
            title: 'Выберите фото',
            popup_id: 'popup_tpl',
            width: 760,
            height: 450,
            overflow: 'auto'
        });
        createUploader();
        if(loadP) {
            loadPhotos(offset);
        }
        loadP = false;

        return false;
    });

    function loadPhotos(offset) {
        params = {};
        params["offset"] = offset;
        if(action_id != 0) {
            params["action_id"] = action_id;
        }
        if(adminEdit == true) {
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
                    $('.p_pad').append('<div class="morephoto"><a href="#">Еще...</a></div>');
                    $('.listphoto').append('<div class="morephoto"><a href="#">Еще...</a></div>');
                    if(data.length<50) {
                        $('.morephoto').remove();
                    }
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
        //VK.callMethod('resizeWindow', 770, $('.wrapper').height());
    }
    $('#groupselect').change(function(){
        $('.about_friends, .grouptr').fadeToggle('slow', function(){
            if($('#groupselect').val() == 0)
                VK.callMethod('resizeWindow', 770, $('.wrapper').height());
        });
        $('#group').val() == 0 ? $('#group').val(1) : $('#group').val(0);
        setTimeout(function() {VK.callMethod('resizeWindow', 770, $('.wrapper').height());}, 70);
    });
    
    $('#who').live("change", function(){        
        cur = $('#who :selected').val();
        $(".right .mini_block .mb_title div").empty().html($('#' + cur).html());
        $("#actionnameh").val($('#who :selected').html());         
    });
    
    $('.qq-upload-button input').live("click", function(){        
//        VK.api("photos.getAlbums", {uid: uid}, function(data) {        
//            aid = 0;
//            for (var i in data.response) {
//                if(data.response[i].title == 'Собирайте деньги')
//                    aid = data.response[i].aid;
//            }
//            if(aid == 0) {
//                VK.api("photos.createAlbum", {title:"Собирайте деньги", privacy:0}, function(data) { 
//                    aid = data.response.aid;
//                    // получаем url для закачки изображения
//                    VK.api("photos.getUploadServer", {aid: aid}, function(data) {
//                        if(data.response.upload_url) {
//                            $("#urlh").val(data.response.upload_url);
//                            uploader.setParams({
//                                url: data.response.upload_url
//                            });
//                            $("#aid").val(aid);
//                        }
//                    });
//                });
//            } else {
//                VK.api("photos.getUploadServer", {aid: aid}, function(data) {
//                    if(data.response.upload_url) {
//                        $("#urlh").val(data.response.upload_url);
//                        uploader.setParams({
//                            url: data.response.upload_url
//                        });
//                        $("#aid").val(aid);
//                    }
//                });
//            }
//        });         
    });      
    
    VK.callMethod('resizeWindow', 770, $('.wrapper').height());
});

$(document).ajaxStart(function(){
    $('.morephoto').append('<span class="loading"></span>');
}).ajaxStop(function(){
    $('.morephoto span').remove();
});