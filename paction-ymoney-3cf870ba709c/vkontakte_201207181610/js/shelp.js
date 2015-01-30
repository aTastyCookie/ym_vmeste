$(document).ready(function(){
    var friendsarray = [];
    
    if(social_users.length > 0) {
        VK.api("users.get", {
            uids: social_users,
            fields: ' uid, first_name, last_name, nickname, photo, photo_medium, photo_big'
        }, function(data) {
            jQuery.each(data.response, function(i, element){
                $('#social_pics').append('<img src="'+element.photo+'" width="30" height="30" style="padding-right:5px;" title="'+element.first_name+' '+element.last_name+'"/>');
            });            
        });
    }
    
    $('.mb_shelp').live('click', function() {
        var id = $(this).attr('data-id');
        if(id) {
            for(var i = 0; i < actions.length; i++) {
               if(actions[i].id == id) {
                   action_id = actions[i].id;
                   link = 'https://vk.com/app' + appid + '?#app_data=' + action_id;
               }
            }
        }
        $.ajax({
            url: '/actions/saveuserajax',
            type: "POST",
            dataType: "json",
            data: {
                user_id: user_id,
                action_id: action_id
            },
            success: function (data) { 

            }
        });
        $(this).fb_popup({
            content_container: '#shelp',
            title: 'Рассказать',
            popup_id: 'popup_tpl',
            width: 410,
            minheight: 100,
            overflow: 'visible',
            custom_btn: {
                ok : '<a href="#" class="p_btn" onclick="sentMessages(); return false;" style="background: none repeat scroll 0 0 #597DA3; color: #fff; font-weight: normal; font-size: 11px;">Отправить на стену</a>',
                cansel : '<a href="#" class="p_btn" onclick="popupClose(); return false;" style="font-weight: normal; font-size: 11px;">Отмена</a>'
            },
            notshowok: true
        });
        
        $('.p_pad').append('<div class="pop1"></div>');
        VK.api("users.get", {
            uids: user_id,
            fields: ' uid, first_name, last_name, nickname, photo, photo_medium, photo_big'
        }, function(data) {
            $('.p_pad .pop1').append('<img src="' + data.response[0].photo + '" width="50" height="50" alt="User photo" />');
            $('.p_pad .pop1').append("<textarea rows='4' id='friendsmessage' style='width:320px; height:42px; float:right; resize: none;'>Друзья, поддержите хорошее дело!</textarea>");
            $('.p_pad').append('<div class="pop2" style="padding: 8px 0px 0px; display: inline-block; width: 100%;"><a href="#" style="margin-left:60px; text-decoration: none; font-size: 11px;">Добавить друзей</a></div>');            
        });
        
        $('.p_pad a').live('click', function(){
            VK.api('friends.get',{
                fields: 'uid, first_name, last_name'
            },function(data) {
                if (data.response) { 
                    if(data.response.length > 0) {
                        jQuery.each(data.response, function(i, element){
                            element['value'] = element['uid'];
                            element['name'] = element['first_name'] + ' ' + element['last_name'];
                        });
                        $('.p_pad .pop2').empty();
                        $('.p_pad .pop2').append('<div class="friends-div">Друзья:</div><input name="autocompleatebox" id="autocompleatebox" type="text" />');
                        $(".p_pad .pop2 input").autoSuggest(data.response, {selectedItemProp: "name", searchObjProps: "name", startText: "", emptyText: "Нет совпадений", resultsHighlight: false});
                    }
                }
            });
        });
                            
        return false;
    });
});

function recPost(arr) {
    if(arr.length > 0) {
        tmp = arr.pop();
        VK.api("wall.post", {
            message: $('#friendsmessage').val() != '' ? $('#friendsmessage').val() : 'Друзья, поддержите хорошее дело!',
            owner_id: tmp,
            attachment: '' + link
        }, function(data) {
            recPost(arr);                
        });
    } else {
    }
}

function popupClose(){
    $('.close_x').click();
}

function sentMessages(){
    VK.api("wall.post", {
        message: $('#friendsmessage').val() != '' ? $('#friendsmessage').val() : 'Друзья, поддержите хорошее дело!',
        attachment: '' + link
    }, function(data) {
        var number_of_LI = jQuery('ul.as-selections li').size();
            if(number_of_LI > 1) {
                arr = $('input.as-values').val().slice(0, -1).split(',');
                if((number_of_LI - arr.length) == 1){
                    var sorted_arr = arr.sort();
                    var results = [].concat(sorted_arr);
                    for (var i = 0; i < arr.length - 1; i++) {
                        if (sorted_arr[i + 1] == sorted_arr[i]) {
                            results.splice(i, 1);
                        }
                    }
                } else if((number_of_LI - arr.length) == 0) {
                    results = [].concat(arr.splice(0, 1));
                }
                friends = new Array();
                for (var i = 0; i < results.length; i++) {
                    friends.push(results[i]);
                }
                recPost(friends);
            }
            $('.close_x').click();
    });
}