$(document).ready(function(){
    var friendsarray = [];
    var login = false;
    var mephoto = '';
  
    $('.mb_shelp').live('click', function() {
        var id = $(this).attr('data-id');
        if(id) {
            for(var i = 0; i < actions.length; i++) {
               if(actions[i].id == id) {
                   app_picture = apphost + '/i/app_75_new.png';
                   action_link = appurl + '?app_data=' + actions[i].id;
                   action_id = actions[i].id;
                   action_name = actions[i].name;
                   action_description = actions[i].description
               }
            }
        }
        
        FB.getLoginStatus(function(response) {
                    
            if (response.authResponse) {
                //user is already logged in and connected
                FB.login(function(response) {
                    if (response.authResponse) {
                        FB.api('/me', function(info) {
                            $.ajax({
                                url: '/actions/saveuserajax',
                                type: "POST",
                                dataType: "json",
                                data: {
                                    user_id: info.id,
                                    action_id: action_id
                                },
                                success: function (data) { 
                                    
                                }
                            });
                        });
                        $(this).fb_popup({
                            content_container: '#shelp',
                            title: 'Publish',
                            popup_id: 'popup_tpl',
                            width: 460,
                            minheight: 110,
                            overflow: 'visible',
                            custom_btn: {
                                ok : '<a href="#" class="p_btn" onclick="sentMessages(); return false;" style="color: #fff; background: none repeat scroll 0 0 #6D84B4;">Publish on wall</a>',
                                canсel : '<a href="#" class="p_btn" onclick="popupClose(); return false;">Отмена</a>'
                            },
                            notshowok: true
                        });
                        
                        $('.p_pad').append('<div class="pop1"></div>');
                        FB.api('/me/picture', function(info) {
//                            var user_pic = "";
//                            if(info.data.url) {
//                                user_pic = info.data.url;
//                            } else {
//                                user_pic = info;
//                            }
                            $('.p_pad .pop1').append('<img src="' + info + '" width="50" height="50" alt="User photo" />');
                            $('.p_pad .pop1').append("<textarea rows='4' id='friendsmessage' style='width:350px; height:42px; float:right; resize: none;'>Help support a good cause!</textarea>");
                            $('.p_pad').append('<div class="pop2" style="padding: 7px 0px 0px 3px; display: inline-block;"><a href="#" style="margin-left:62px; text-decoration: none;">Add friends</a></div>');
                        });
                        
                        $('.p_pad a').live('click', function(){
                            FB.api('/me/friends', function(response) {
//                                var links = '';
                                var data = response["data"];
                                if(data.length > 0) {
                                    jQuery.each(data, function(i, element){
                                        element['value'] = element['id'];
                                    });
                                    $('.p_pad .pop2').empty();
                                    $('.p_pad .pop2').append('<div class="friends-div">Друзья:</div><input name="autocompleatebox" id="autocompleatebox" type="text" />');
                                    $(".p_pad .pop2 input").autoSuggest(data, {selectedItemProp: "name", searchObjProps: "name", startText: "", emptyText: "Нет совпадений", resultsHighlight: false});
                                }
                            });
                            
                        });
                    } else {
                        //user cancelled login or did not grant authorization
                    }
                }, {scope:'email,publish_stream,user_photos,manage_pages'});
            } else {
                //user is not connected to your app or logged out
                    FB.login(function(response) {
                        if (response.authResponse) {
                            FB.api('/me', function(info) {
                                $.ajax({
                                    url: '/actions/saveuserajax',
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        user_id: info.id
                                    },
                                    success: function (data) {
                                    }
                                });
                            });
                            
                            $(this).fb_popup({
                                content_container: '#shelp',
                                title: 'Publish',
                                popup_id: 'popup_tpl',
                                width: 460,
                                minheight: 110,
                                overflow: 'visible',
                                custom_btn: {
                                    ok : '<a href="#" class="p_btn" onclick="sentMessages(); return false;" style="background: none repeat scroll 0 0 #6D84B4; color: #fff;">Publish on wall</a>',
                                    canсel : '<a href="#" class="p_btn" onclick="popupClose(); return false;">Cancel</a>'
                                },
                                notshowok: true
                            });

                            $('.p_pad').append('<div class="pop1"></div>');
                            FB.api('/me/picture', function(info) {
//                                var user_pic = "";
//                                if(info.data.url) {
//                                    user_pic = info.data.url;
//                                } else {
//                                    user_pic = info;
//                                }
                                $('.p_pad .pop1').append('<img src="' + info + '" width="50" height="50" alt="User photo" />');
                                $('.p_pad .pop1').append("<textarea rows='4' id='friendsmessage' style='width:350px; height:42px; float:right'>Help support a good cause!</textarea>");
                                $('.p_pad').append('<div class="pop2" style="padding: 7px 0px 0px 3px; display: inline-block;"><a href="#" style="margin-left:62px; text-decoration: none;">Add friends</a></div>');
                            });

                            $('.p_pad a').live('click', function(){
                                FB.api('/me/friends', function(response) {
    //                                var links = '';
                                    var data = response["data"];
                                    if(data.length > 0) {
                                        jQuery.each(data, function(i, element){
                                            element['value'] = element['id'];
                                        });
                                        $('.p_pad .pop2').empty();
                                        $('.p_pad .pop2').append('<div class="friends-div">Друзья:</div><input name="autocompleatebox" id="autocompleatebox" type="text" />');
                                        $(".p_pad .pop2 input").autoSuggest(data, {selectedItemProp: "name", searchObjProps: "name", startText: "", emptyText: "Нет совпадений", resultsHighlight: false});
                                    }
                                });

                            });
                        } else {
                            //user cancelled login or did not grant authorization
                        }
                    }, {scope:'email,publish_stream,user_photos,manage_pages'});  	
                }
            });
        

        return false;
    });
    
//    $('#skip1').live("click", function() {
//        step2();
//        
//        return false;
//    });
    
//    $('#shelpok1').live("click", function() {
//        var body = 'Собирайте деньги';
//        confirm("<textarea rows='4' id='friendsmessage'></textarea>", function () {
//            $(".p_pad input:checked").each(function(){
//                obj = { 
//                    name: this.value,
//                    id: this.id
//                };
//                FB.api('/' + this.id + '/feed',
//                    'post',
//                    {message: $('#friendsmessage').val() != '' ? $('#friendsmessage').val() : body,
//                        name: action_name,
//                        link: action_link,
//                        caption: 'Акция по сбору средств',
//                        description: action_description,
//                        picture: app_picture,
//                        actions: [
//                            {name: 'Ссылка на акцию', link: action_link}
//                        ]
//                    },
//                    function(response) {
//                        if (!response || response.error) {
//                            alert('Возникла ошибка при отправлении поста. Адресат: ' + this.value);
//                        } else {
//                            step2();
//                        }
//                    }
//                );
//            });	
//        });
//        
//        return false;
//    });
    
//    $('#skip2').live("click", function() {
//        $('.close_x').click();
//        
//        return false;
//    });
    
//    $('#shelpok2').live("click", function() {
//        FB.api('/me/feed',
//            'post',
//            {message: $('#friendsmessage2').val(),
//                name: action_name,
//                link: action_link,
//                caption: 'Акция по сбору средств',
//                description: action_description,
//                picture: app_picture,
//                actions: [
//                    {name: 'Ссылка на акцию', link: action_link}
//                ]
//            },
//            function(response) {
//                if (!response || response.error) {
//                    alert('Возникла ошибка при отправлении поста.');
//                } else {
//                    $('.close_x').click();
//                }
//            }
//        );
//        
//        return false;
//    });
});

//function step2() {
//    $('.p_pad').html('<p><strong>Шаг 2.</strong> Опубликовать сообщение себе на стену</p>');
//    $('.p_pad').append("<textarea rows='4' id='friendsmessage2'></textarea>");
//        
//    links = '<p><a href="#" id="skip2">Пропустить шаг 2</a><a href="#" id="shelpok2">Опубликовать у себя на стене</a></p>';
//    $('.p_pad').append(links);
//}

function popupClose(){
    $('.close_x').click();
}

function sentMessages(){
    FB.api('/me/feed',
        'post',
        {message: $('#friendsmessage').val(),
            name: action_name,
            link: action_link,
            caption: 'Collection',
            description: action_description,
            picture: app_picture,
            actions: [
                {name: 'Link to collection', link: action_link}
            ]
        },
        function(response) {
            if (!response || response.error) {
                alert('Возникла ошибка при отправлении поста.');
                $('.close_x').click();
            } else {
//                friends = $('#autocompleatebox').val().split(',');
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
                    for (var i = 0; i < results.length; i++) {
    //                    obj = { 
    //                        name: results[i].value,
    //                        id: results[i].id
    //                    };
                        FB.api('/' + results[i] + '/feed',
                            'post',
                            {message: $('#friendsmessage').val() != '' ? $('#friendsmessage').val() : body,
                                name: action_name,
                                link: action_link,
                                caption: 'Collection',
                                description: action_description,
                                picture: app_picture
    //                            actions: [
    //                                {name: 'Ссылка на акцию', link: action_link}
    //                            ]
                            },
                            function(response) {
                                if (!response || response.error) {
                                    alert('Возникла ошибка при отправлении поста.');
                                } else {
    //                                step2();
                                }
                            }
                        );
                    }
                }
                $('.close_x').click();
            }
        }
    );
}

//function confirm(message, callback) {
//    $('#confirm').modal({
//        closeHTML: "<a href='#' title='Close' class='modal-close'>x</a>",
//        position: ["30%",],
//        overlayId: 'confirm-overlay',
//        containerId: 'confirm-container', 
//        onShow: function (dialog) {
//            var modal = this;
//
//            $('.message', dialog.data[0]).append(message);
//
//            // if the user clicks "yes"
//            $('.yes', dialog.data[0]).click(function () {
//                // call the callback
//                if ($.isFunction(callback)) {
//                        callback.apply();
//                }
//                // close the dialog
//                modal.close(); // or $.modal.close();
//            });
//        }
//    });
//}