/* copy content to mobile */
jQuery(document).ready(function ($) {
    //alert($.trim($('.nymain_mobile .mid_content').html()).length);
    if($('.nymain_mobile').css('display') == 'block' && $.trim($('.nymain_mobile .mid_content').html()).length == 0 && typeof pageInit === "function") {
        $('.nymain_mobile .mid_content').html($('.hatraotinner_content .mid_content').html());
        $('.hatraotinner_content .mid_content').html('');
        //pageInit();
    }
    $('.sorting .open-sort').click(function () {
       //if($(this).hasClass('down')){
           $(this).parents('.sorting').animate({top:'76px'},300,function () {
               $(this).find('.open-sort').hide();
           });
       /*}else {
           if ($(this).hasClass('up')) {
               $(this).parents('.sorting').animate({top: '37px'}, 300, function () {
                   $(this).find('.open-sort').removeClass('up').addClass('down');
               });
           }
       }*/
    });
    $('.sorting .close').click(function () {
        $(this).parents('.sorting').animate({top: '37px'}, 300, function () {
            $(this).find('.open-sort').show();
        });
    });
    $('#search_filter1').change(function() {
        $('#advanced_search_filter').val($(this).val());
        if ($('#page').attr('name') == 'pageNum') {
            $('#page').val(1);
            $('#search_filter_form')
                .find('input[type="submit"]')
                .click()
            ;
        }else{
            $('#search_filter_form')
            // change page number to 1 in url when switching filter
                .attr('action', $('#search_url_when_switching_filter').val())
                .find('input[type="submit"]')
                .click()
            ;
        }
    });
});

function sortByDistance(url){
    //advanced_search[filter]=distance;
    $('<form action="' + url + '" method="POST"><input type="hidden" name="advanced_search[filter]" value="distance"/></form>').appendTo($(document.body)).submit();
}

$(window).resize(function(){
    if($('.nymain_mobile').css('display') == 'block' && $.trim($('.nymain_mobile .mid_content').html()).length == 0 && typeof pageInit === "function") {
        $('.nymain_mobile .mid_content').html($('.hatraotinner_content .mid_content').html()).trigger('refresh');
        $('.hatraotinner_content .mid_content').html('');
        pageInit();
    }
    if($('.nymain_mobile').css('display') == 'none' && $.trim($('.hatraotinner_content .mid_content').html()).length == 0 && typeof pageInit === "function") {
        $('.hatraotinner_content .mid_content').html($('.nymain_mobile .mid_content').html()).trigger('refresh');
        $('.nymain_mobile .mid_content').html('');
        pageInit();
    }
});
/* end copy content to mobile */
//var popslide = '';
jQuery(document).ready(function ($) {
    $('.pop_con a').click(function(){
        arenaClick(this);
        return false;
    });
    popslide =  jQuery('.popup.pgal0 .popslider').lightSlider();
});

function getAge(dateString) {
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function openPopup(id) {
    //e.preventDefault();
    id = parseInt(id);
    var addurl = '';
    if(id > 0){
        addurl = '/' + id;
    }
    jQuery.ajax({
        url: '/user/like' + addurl,
        type: "GET",
        dataType: "json",
        success: function (data) {
            if(data.photos == 0){
                window.location.href = data.url;
            }else {
                var html = '';
                var template = $('#arenaSlideTemplate').html();
                if (typeof data.online === 'object' && data.online.length > 0) {
                    for (var i in data.online) {
                        var user = data.online[i];
                        html += template;
                        html = html.replace('[ID]', user.id).replace('userID', user.id).replace('[USERNAME]', user.username).replace('[IMAGE]', user.image).replace('[AGE]', getAge(user.birthday)).replace('[AREA]', user.area);
                        //html += '<li class="slid" id="' + user.id + '"><div class="img"><div class="pop_title"><span>' + user.username + ',</span> 32, manhattan</div><div class="pop_img" style="background:url(' + user.image + ') no-repeat; background-size:cover;"></div></div></li>';
                    }
                }
                if (typeof data.other === 'object' && data.other.length > 0) {
                    for (i in data.other) {
                        var user = data.other[i];
                        html += template;
                        html = html.replace('[ID]', user.id).replace('userID', user.id).replace('[USERNAME]', user.username).replace('[IMAGE]', user.image).replace('[AGE]', getAge(user.birthday)).replace('[AREA]', user.area);
                        if (i === 200) {
                            break;
                        }
                    }
                }
                //alert(data);
                if(html == ''){
                    textAlert('No results');
                    return false;
                }
                jQuery('.popup.pgal0 .popslider').html(html);
                if (typeof popslide.destroy === 'function') {
                    popslide.destroy();
                }
                popslide = jQuery('.popup.pgal0 .popslider').lightSlider({
                    gallery: false,
                    item: 1,
                    freeMove: false,
                    thumbItem: 0,
                    slideMargin: 0,
                    speed: 3000,
                    pause: 9000,
                    mode: 'fade',
                    auto: false,
                    loop: true,
                    pager: false,
                    enableDrag: true,
                    enableTouch: true,
                    swipeThreshold: 40,
                    onSliderLoad: function () {
                        //alert(jQuery(window).height() * 0.7 - (jQuery('.pop_title').first().outerHeight() + jQuery('.pop_con').outerHeight()));
                        /*jQuery('.pop_img').css({'height': jQuery(window).height() * 0.8 - (jQuery('.pop_title').first().outerHeight() + jQuery('.pop_con').outerHeight()), 'margin': jQuery('.pop_title').first().outerHeight() + 'px 0px ' + jQuery('.pop_con').outerHeight() + 'px'});*/
                        jQuery('.pop_img').css({'height': jQuery(window).height() * 0.85});
                        jQuery('.imageslide').removeClass('cS-hidden');
                    }
                });

                jQuery('.popup.pgal0').fadeIn(200);
                popslide.resize();
                jQuery('body').addClass('ovh');
            }
        }
    });
}

function arenaClick(el){
    var alt = jQuery(el).find('img').attr('alt');
    if(alt !== 'redthumb'){
        var id = jQuery('.popup.pgal0 .popslider .slid.active').attr('id');
    }
    if(alt !== 'pmsg'){
        jQuery('.popup.pgal0 .popup_bot .lSAction .lSNext').click();
        //popslide.goToNextSlide();
    }
    if(alt == 'thumbgreen'){
            //alert(jQuery('.popup.pgal0 .popslider li').size());
            //return false;
        if(jQuery('.popup.pgal0 .popslider li').size() == 1) {
            jQuery('.popup.pgal0 .close').click();
            textAlert('No more results');
            //return false;
        }else {
            jQuery('.popup.pgal0 #' + id).remove();
            jQuery('.popup.pgal0 .popslider').trigger('refresh');

            popslide.refresh();
            popslide.resize();
        }
        sendLike(id);
    }
    if(alt == 'pmsg'){
        window.location.href = $('#dialogLink').val().replace('userID',id);
    }
}

function sendLike(id) {
    id = parseInt(id);

        if (id > 0) {
            jQuery.ajax({
                url: '/user/like/send/' + id,
                type: "POST",
                dataType: "json",
                success: function (data) {
                    console.log(data)
                    $('.like_click[userid="' + id + '"]').each(function () {
                        //if ($(this).hasClass('nyinner')) {
                            $(this).addClass('inList');
                        //} else {
                           // $(this).remove();
                        //}
                    });
                    //alert($('.popup.pgal0').css('display') != 'block' && $('.like_click[userid="' + id + '"]').first().size() > 0);
                    if ($('.popup.pgal0').css('display') != 'block'
                        && $('.like_click[userid="' + id + '"]').first().size() > 0 )
                    {
                        if($('#alert').size() == 0) {
                            $('body').prepend('<div class="ui modal small" id="alert"><i class="close icon"></i> <div class="header">Notification </div> <div class="content"> <p>'+ data.text +'</p> </div> <div class="actions"> <div class="ui button ok">OK</div> </div></div>');
                        }else{
                            $('#alert .content').html('<p>'+ data.text +'</p>');
                        }
                        $('#alert.ui.modal').modal({onApprove : function() { $('body').trigger('refresh'); }}).modal('show');
                        //alert('Your like was sent successfully.');
                    }
                }
            });
        }


}

function bingoCheck() {
    if(jQuery('#dialog').size() == 0) {
        jQuery.ajax({
            url: '/user/like/bingo/',
            type: "GET",
            dataType: "json",
            success: function (data) {
                if (data && $('#splashBingo').size() == 0) {
                    var template = jQuery('#splashBingoTemplate').html();
                    template = template.replace("[attrid]", 'id').replace("[USERNAME]", data.username).replace("[Photo1]", data.photo1).replace("[Photo2]", data.photo2).replace("%5BCONTACTID%5D", data.contact_id);
                    jQuery('body').prepend(template);
                    bingoShow(data.id);
                } else {
                    setTimeout(function () {
                        bingoCheck();
                    }, 10000);
                }
            }
        });
    }
}

function bingoShow(id){
    jQuery.ajax({
        url: '/user/like/bingo/show/' + id,
        type: "GET",
        dataType: "json",
        success: function (data) {

        }
    });
}

function closeBingo() {
    jQuery('#splashBingo').remove();
    bingoCheck();
}

function checkNewMessagesCount() {
    if($('#dialog').length == 0) {
        jQuery.ajax({
            url: '/user/newMessages',
            type: 'GET',
            dataType: "json",
            success: function (data) {
                //alert(data.newMessagesNumber)
                $('.inbox_count').html(data.newMessagesNumber);
                $('.arena_count').html(data.newNotificationsNumber);

                if (data.newMessagesNumber == 0) {
                    $('.inbox_count').hide();
                } else if (data.favoritesCount > 0) {
                    newFavoriteNotification(data.messages, data.newMessagesText);
                } else {
                    newMessagesNotification(data.messages, data.newMessagesText);
                    $('.inbox_count').show();
                }
                if (data.newNotificationsNumber == 0) {
                    $('.arena_count').hide();
                } else {
                    $('.arena_count').show();
                }
                $('.online_count').html(data.users_online);
                if (data.users_online == 0) {
                    $('.online_count').hide();
                } else {
                    $('.online_count').show();
                }
                $('.viewed_count').html(data.viewed);
                if (data.viewed == 0) {
                    $('.viewed_count').hide();
                } else {
                    $('.viewed_count').show();
                }
                $('.viewedMe_count').html(data.viewedMe);
                if (data.viewedMe == 0) {
                    $('.viewedMe_count').hide();
                } else {
                    $('.viewedMe_count').show();
                }
                $('.connected_count').html(data.connected);
                if (data.connected == 0) {
                    $('.connected_count').hide();
                } else {
                    $('.connected_count').show();
                }
                $('.connectedMe_count').html(data.connectedMe);
                if (data.connectedMe == 0) {
                    $('.connectedMe_count').hide();
                } else {
                    $('.connectedMe_count').show();
                }
                $('.favorited_count').html(data.favorited);
                if (data.favorited == 0) {
                    $('.favorited_count').hide();
                } else {
                    $('.favorited_count').show();
                }
                $('.favoritedMe_count').html(data.favoritedMe);
                if (data.favoritedMe == 0) {
                    $('.favoritedMe_count').hide();
                } else {
                    $('.favoritedMe_count').show();
                }
                $('.blacklisted_count').html(data.blacklisted);
                if (data.blacklisted == 0) {
                    $('.blacklisted_count').hide();
                } else {
                    $('.blacklisted_count').show();
                }

                $('.left_welcome').find('.inbox_count, .arena_count').show();
                setTimeout(function () {
                    checkNewMessagesCount();
                }, 10000);
            }
        });
    }
}

function notificationShow(text,el_sel) {
    //alert(typeof el_sel);
    if (typeof el_sel == 'undefined') {
        el_sel = 'body';
        $(el_sel).prepend('<div id="nitifShow"><div style="position: fixed;top: 107px;right: 10px;" class="ui compact message hidden">' + text + '</div></div>');
    } else {
        $(el_sel).after('<div id="nitifShow"><div class="ui compact message hidden">' + text + '</div></div>');
    }
    $('#nitifShow .ui.message').transition('fade right');
    setTimeout(function(){ $('#nitifShow .ui.message').transition('fade left').remove(); },5000);
}

function newFavoriteNotification(messages, text) {
    if($('.messNotifwrap').size() == 0){
        $('body').prepend('<div class="messNotifwrap"></div>');
    }
    //alert($('.messNotifwrap .message.messageNotif').size());
    if($('.messNotifwrap .message.messageNotif').size() == 0) {
        console.log(messages);
        for (var i in messages) {
            var mess = messages[i];
            var photo = mess.mainPhoto;
            if (photo == null) {
                photo = mess.noPhoto;
            }
            if(mess.id > 0) {
                Messenger.setMessageAsNotified(mess.id);
            }
            //mess.text = $.emoticons.replace(decodeURIComponent(mess.text));
            $('.messNotifwrap').prepend('<div id="mess_' + mess.id + '" class="ui compact message messageNotif hidden">' +
                '<i class="close icon"></i>' +
                '<div class="userPhoto" style="background-image: url(' + photo + ')"></div>' +
                '<div class="title">' + mess.username + '</div>' +
                text +
                '</div>');
            $('#mess_' + mess.id).on('click', function (e) {
                e.preventDefault();
                //alert($(e.target).hasClass('close'));
                if (!$(e.target).hasClass('close')) {
                    window.location.href = mess.chatLink;
                } else {
                    $(this).transition('fade down').remove();

                }
            }).transition('fade right');

            //alert(i);
            if (i == 2) {
                break;
            }
        }

        setTimeout(function () {
            $('.messNotifwrap .message.messageNotif').transition('fade down');
            setTimeout(function () {
                $('.messNotifwrap .message.messageNotif').remove();
            }, 900);
        }, 8000);
    }

}

function newMessagesNotification(messages, text) {
    if($('.messNotifwrap').size() == 0){
        $('body').prepend('<div class="messNotifwrap"></div>');
    }
    //alert($('.messNotifwrap .message.messageNotif').size());
    if($('.messNotifwrap .message.messageNotif').size() == 0) {
        for (var i in messages) {
            var mess = messages[i];
            var photo = mess.mainPhoto;
            if (photo == null) {
                photo = mess.noPhoto;
            }
            Messenger.setMessageAsNotified(mess.id);
            //mess.text = $.emoticons.replace(decodeURIComponent(mess.text));
            $('.messNotifwrap').prepend('<div id="mess_' + mess.id + '" class="ui compact message messageNotif hidden">' +
                '<i class="close icon"></i>' +
                '<div class="userPhoto" style="background-image: url(' + photo + ')"></div>' +
                '<div class="title">' + mess.username + '</div>' +
                text +
                '</div>');
            $('#mess_' + mess.id).on('click', function (e) {
                e.preventDefault();
                //alert($(e.target).hasClass('close'));
                if (!$(e.target).hasClass('close')) {
                    window.location.href = mess.chatLink;
                } else {
                    $(this).transition('fade down').remove();

                }
            }).transition('fade right');

            //alert(i);
            if (i == 2) {
                break;
            }
        }

        setTimeout(function () {
            $('.messNotifwrap .message.messageNotif').transition('fade down');
            setTimeout(function () {
                $('.messNotifwrap .message.messageNotif').remove();
            }, 900);
        }, 8000);
    }

}

jQuery(window).load(function () {
    //notificationShow('test');
    //if not dialog page
    if(jQuery('#dialog_mob').size() == 0) {
        bingoCheck();
    }
    checkNewMessagesCount();

    jQuery('.pop_img').css({'height': jQuery(window).height() - 220});
});

jQuery(document).ready(function () {

});
