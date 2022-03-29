jQuery(document).ready(
    function ($) {

        $('.users .manage_user .addVerify').click(function () {
            verifyUser($(this).parents('tr').find('.userId').val(), $(this));
        });

        function verifyUser(id,elem) {

            $.ajax({
                url: '/user/verify/' + id,
                success: function (res) {
                    console.log(elem);
                    if (res) {
                        if(res == 'user-not-verify'){
                            alert('האימות בוטל');
                            elem.removeClass('disabled');
                            elem.attr('data-content', 'לאמת את המשתמש');
                        }else {
                            alert('המשתש אומת');
                            elem.addClass('disabled');
                            elem.attr('data-content', 'לבטל אומת של משתמש');
                        }
                        // console.log(elem)
                        // elem.removeAttr('href');
                        // elem.addClass('opacity-05');

                    }
                }
            });
        }

        if($('.ui.dropdown').length > 0) {
            $('.ui.dropdown')
                .dropdown()
            ;
        }
        if($('.ui.accordion').length > 0) {
            $('.ui.accordion')
                .accordion()
            ;
        }
        if($('.ui.checkbox').length > 0) {
            $('.ui .checkbox').checkbox();
        }
        $('#search button').click(function(){

            if($(this).hasClass('visible')){
                $(this).removeClass('visible');
                $(this).parent().css('margin-left','0px');
                $("#main_sidebar").animate({width:'toggle'},'25%');
            }
            else {
                $(this).addClass('visible');
                $(this).parent().css('margin-left','25%');
                $("#main_sidebar").animate({width:'toggle'},'25%');
            }

        });

        $('#main_sidebar .scroll')
            .css({"height": $(window).height() - $('#logo').height()})
            .perfectScrollbar()
        ;

        //$('#main_sidebar .scroll').css({"margin-right": "5px"});

        $('.internal_sidebar .scroll')
            .css({"height": $(window).height() - $('#header').height()})
            .perfectScrollbar()
        ;


        $('.ui.progress').hide();

        $('#sentMessage').click(function () {
            var text = tinymce.get('textMessage').getContent();
            if(text == '' || $('#reportId').val() == '') {
                if(text != '' && $('#reportId').val() == ''){
                    alert('Please select report');
                }
                return false;
            }
        });

        $('.message .close')
            .on('click', function() {
                $(this)
                    .closest('.message')
                    .transition('fade')
                ;
            });

        /******************** User Photos And Cloudinary *********************************************/
/*
        $.cloudinary.config({ cloud_name: 'greendate', api_key: '333193447586872'});
        $('.ui.progress').hide();


        $('table.users .user_photo').each(function(){

            var noPhoto = false;
            var photoName = $(this).find('input[type="hidden"]').val();

            if(photoName.length){
                var url = $.cloudinary.url(photoName, { width: 50, height: 50, crop: 'thumb', gravity: 'face', radius: 'max' })
            }
            else{
                var url = ( $(this).parents('tr').find('.user_props i').hasClass('male') )
                    ? $('#no_photo_male_url').val()
                    : $('#no_photo_female_url').val();
                noPhoto = true;
            }

            $(this).find('img').attr('src',url);

            if(noPhoto){
                $(this).find('img').addClass("ui circular image").css({"width":"50px", "height":"50px"});
            }

        });

        $('table.waiting_photos .photo').each(function(){
            var photoName = $(this).find('input[type="hidden"]').val();
            $(this).find('img').attr('src', $.cloudinary.url(photoName, { width: 414, height: 491, crop: 'fill'}));
        });
*/
        /************Articles Cloudinary************/

    /*    if($('#article_imageName').size() && $('#article_imageName').val().length > 0){
            var url = $.cloudinary.url($('#article_imageName').val(), { width: 300, height: 300, crop: 'fill' });
            $('.article_image').find('img').attr('src',url);
        }

        $('.cloudinary-fileupload').bind('fileuploadstart', function(e, data) {
            $('.ui.progress').show();
            $('.upload_photo button').addClass("loading");
        });

        $('.cloudinary-fileupload').bind('fileuploadprogress', function(e, data) {
            var value = Math.round((data.loaded * 100.0) / data.total);
            $('.ui.progress').progress({
                percent: value,
            });
            $('#upload_photo_label span').text(value);
        });

        $('.cloudinary-fileupload').bind('cloudinarydone', function(e, data) {
            //console.log(JSON.stringify(data));
            $('.upload_photo button').removeClass("loading");
            $('.ui.progress').hide();
            $('#article_imageName').val(data.result.public_id);
            var url = $.cloudinary.url(data.result.public_id, { width: 300, height: 300, crop: 'fill' });
            $('.article_image').find('img').attr('src',url);
            return true;
        });
*/


        /********************************************************************************************/


        $('.user_props .icon, .manage_user .icon, #logo, .users .actions button, .users .user_photo img').popup();
        $('.waiting_photos .card button, .waiting_photos .username, .waiting_photos .manage_photos, .waiting_photos .manage_user').popup();
        $('.waiting_photos .meta .icon').popup();

        $('#confirm_del').popup({
            on: 'click'
        });


        $('.messages .paging a').click(function(e){
            e.preventDefault();
            window.location.href = $(this).attr('href');
        });

        $('.users .paging a').click(function(e){
            e.preventDefault();
            var url = $(this).attr('href');

            $('#search_filter_form')
                .attr('action', url)
                .find('input[type="submit"]')
                .click()
            ;
        });


        $('.users .ui.checkbox.toggle').checkbox({
            onChecked: function(){
                setUserProperty('isActive', 1, $(this).parents('tr').find('.userId').val());
            },
            onUnchecked: function(){
                var userId = $(this).parents('tr').find('.userId').val();
                setUserProperty('isActive', 0, userId);

                var users = []
                users.push(userId);

                $('.small.modal.ban_users_reason')
                    .modal({
                        onApprove : function() {
                            saveBanUsersReason(users, $(this).find('textarea').val());
                        },
                        onHidden : function() {
                            $(this).find('textarea').val('');
                        }
                    })
                    //.modal('setting', 'transition', 'fade up')
                    .modal('show')
                ;
            },
        });


        //$('.sel_item .ui.checkbox').checkbox('attach events', '#sel_all'); // toggle

        //$('.sel_all').checkbox();

        $('#sel_all').click(function(){
            if($(this).find('input[type="checkbox"]').is(":checked")){
                $('.sel_item').find('.ui.checkbox').checkbox('set checked');
            }
            else{
                $('.sel_item').find('.ui.checkbox').checkbox('set unchecked');
            }
        });


        $('.sel_all').click(function(){
            var selected = $(this).siblings('input[type="hidden"]');
            if(selected.val() == 0){
                $('.sel_item').find('.ui.checkbox').checkbox('set checked');
                selected.val(1);
            }
            else{
                $('.sel_item').find('.ui.checkbox').checkbox('set unchecked');
                selected.val(0);
            }
        });


        $('.users .activated, .users .deactivated').click(function(){
            var users = [];
            var value = 1;
            var state = ":not(:checked)";

            if($(this).hasClass('deactivate')){
                value = 0;
                state = ":checked";
            }

            $('.sel_item .ui.checkbox').each(function(){
                if($(this).find('input[type="checkbox"]').is(":checked")){
                    var checkbox = $(this).parents('tr').find('.ui.checkbox.is_activated input[type="checkbox"]');
                    if(checkbox.is(state) && checkbox.is(":not(:disabled)")){
                        var userId = $(this).parents('tr').find('.userId').val();
                        console.log(userId);
                        checkbox.click();
                        setUserProperty('isActivated', value, userId);
                        users.push(userId);
                    }
                }
            });
        });

        $('.users .activate, .users .deactivate').click(function(){

            //$(this).addClass('loading');


            var users = [];
            var value = 1;
            var state = ":not(:checked)";

            if($(this).hasClass('deactivate')){
                value = 0;
                state = ":checked";
            }

            $('.sel_item .ui.checkbox').each(function(){
                if($(this).find('input[type="checkbox"]').is(":checked")){
                    var checkbox = $(this).parents('tr').find('.ui.checkbox.is_active input[type="checkbox"]');
                    if(checkbox.is(state) && checkbox.is(":not(:disabled)")){
                        var userId = $(this).parents('tr').find('.userId').val();
                        checkbox.click();
                        setUserProperty('isActive', value, userId);
                        users.push(userId);
                    }
                }
            });

            if(value == 0 && $('.sel_item .ui.checkbox input[type="checkbox"]').is(":checked")){
                $('.small.modal.ban_users_reason')
                    .modal({
                        onApprove : function() {
                            saveBanUsersReason(users, $(this).find('textarea').val());
                        },
                        onHidden : function() {
                            $(this).find('textarea').val('');
                        }
                    })
                    //.modal('setting', 'transition', 'fade up')
                    .modal('show')
                ;
            }


            //$(this).removeClass('loading');

        });

        $('.users .phoneActivate, .users .phoneDeactivate').click(function(){
            var value = ($(this).hasClass('phoneActivate')) ? 1 : 0;

            $('.sel_item .ui.checkbox').each(function(){
                if($(this).find('input[type="checkbox"]').is(":checked")){
                    setUserProperty('isActivated', value, $(this).parents('tr').find('.userId').val(), 'call');
                }
            });
        });

        $('.users .freeze, .users .unfreeze').click(function(){

            var value = ($(this).hasClass('freeze')) ? 1 : 0;

            $('.sel_item .ui.checkbox').each(function(){
                if($(this).find('input[type="checkbox"]').is(":checked")){
                    setUserProperty('isFrozen', value, $(this).parents('tr').find('.userId').val(), 'asterisk');
                }
            });

        });

        $('.users .flag, .users .unflag').click(function(){

            var value = ($(this).hasClass('flag')) ? 1 : 0;

            $('.sel_item .ui.checkbox').each(function(){
                if($(this).find('input[type="checkbox"]').is(":checked")){
                    setUserProperty('isFlagged', value, $(this).parents('tr').find('.userId').val(), 'flag');
                }
            });

        });


        $('.users .delete').click(function(){

            if($('.sel_item .ui.checkbox input[type="checkbox"]').is(":checked")){
                if( confirm('Delete selected users?') ){
                    $('.sel_item .ui.checkbox').each(function(){
                        if($(this).find('input[type="checkbox"]').is(":checked")){
                            deleteUser($(this).parents('tr').find('.userId').val());
                            $(this).parents('tr').remove();
                        }
                    });
                }
            }
        });


        $('.users .report').click(function(){

            $('.small.modal.create_report')
                .modal({
                    onApprove : function() {
                        createReport($(this));
                    },
                    onHidden : function() {
                        $(this).find('input[type="text"]').val('');
                        $(this).find('input[type="checkbox"]').attr('checked','');
                    }
                })
                //.modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .export').click(function(){

            $('.small.modal.export')
                .modal({
                    onApprove : function() {
                        exportToCSV($(this));
                    },
                    onHidden : function() {
                        $(this).find('input[type="text"]').val('');
                    }
                })
                //.modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .point').click(function(){

            $('.small.modal.give_point')
                .modal({
                    onApprove : function() {
                        givePoint(true);
                    },
                    onDeny : function(){
                        givePoint();
                    }
                })
                .modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .user_photo img').click(function(){
            getUserPhotosModal($(this).parents('tr').find('.userId').val());
        });

        $('.users .username').click(function(){
            viewProfile($(this).parents('tr').find('.userId').val());
        });

        $('.messages .profile').click(function(){
            viewProfile($(this).siblings('.userId').val());
        });

        $('.users .manage_user .edit').click(function(){
            getEditedProfile($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .send-msg').click(function(){
            getQuickSend($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .diamond').click(function(){
            getSubscr($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .sign.in').click(function(){
            logInAsUser($(this).parents('tr').find('.userId').val());
        });



        /*
        $( ".field .birthdayCalendar" ).datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '-90:-18',
            defaultDate:'-18y-m-d',
        });
        */


        $('.advanced_search .ui.checkbox.region').checkbox({
            onChecked: function(){
                addAreas($(this));
            },
            onUnchecked: function(){
                deleteAreas($(this));
            },
        });

        $('.advanced_search .calendar input').datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '-10:+1',
            defaultDate:'y-m-d',
            dateFormat: 'dd/mm/yy',
        });


        $('.advanced_search .period select').change(function(){
            var dateObj1 = $(this).parent().siblings('.date_from').find('input[type="text"]');
            var dateObj2 = $(this).parent().siblings('.date_to').find('input[type="text"]');
            setDatePeriods(dateObj1, dateObj2, $(this).val(), '%dd/%mm/%Y');
        });


        $('.waiting_photos .actions .approve, .waiting_photos .actions .delete').click(function(){

            var approve = true;

            if($(this).hasClass('delete')){
                if(!confirm('Delete photos?')){
                    return;
                }
                approve = false;
            }

            $('.sel_item .ui.checkbox').each(function(){
                var checkbox = $(this).find('input[type="checkbox"]');
                if(checkbox.is(":checked")){
                    var id = $(this).siblings('input[type="hidden"]').val();
                    approvePhoto(id, approve);
                }
            });

        });

        $('.waiting_photos .card .approve, .waiting_photos .card .delete').click(function(){

            var approve = true;

            if($(this).hasClass('delete')){
                if(!confirm('Delete photo?')){
                    return;
                }
                approve = false;
            }

            var id = $(this).parents('.card').find('.sel_item input[type="hidden"]').val();
            //alert($(this).parents('.card').find('.sel_item input[type="hidden"]').length);
            //alert(id);
            //return false;
            approvePhoto(id, approve);

        });

        $('.waiting_photos .username').click(function(){
            viewProfile($(this).parents('.card').find('.userId').val());
        });

        $('.waiting_photos .manage_photos').click(function(){
            getUserPhotosModal($(this).parents('.card').find('.userId').val());
        });

        $('.waiting_photos .manage_user').click(function(){
            getEditedProfile($(this).parents('.card').find('.userId').val());
        });

        $('.flagged_reports .item').click(function(){
            $(this).find('form').submit();
        });

        $('.reports a').click(function(e){
            e.preventDefault();
            $(this).siblings('form').submit();
        });

        $('.reports .ui.checkbox.toggle').checkbox({
            onChecked: function(){
                showReportOnMainPage(1, $(this).parents('tr').find('.report_id').val());
            },
            onUnchecked: function(){
                showReportOnMainPage(0, $(this).parents('tr').find('.report_id').val());
            },
        });

        $('.reports .delete').click(function(){
            var name = $(this).parents('tr').find('.report_name').text();
            if(confirm('Delete report?' + name + '?')){
                deleteReport($(this));
            }
        });

        $('.articles .delete').click(function(){
            var name = $(this).parents('tr').find('.article_name').text();
            if(confirm('Delete article - '+ name + '?')){
                deleteArticle($(this));
            }
        });

        $('.WordBlocked .delete, .EmailBlocked .delete, .PhoneBlocked .delete').click(function(){
            var name = $(this).parents('tr').find('.item_value').text();
            if(confirm('להסיר '+ name + '?')){
                deleteItem($(this));
            }
        });

        $('.pages .delete').click(function(){
            var name = $(this).parents('tr').find('.page_name').text();
            if(confirm('Delete page - '+ name + '?')){
                deletePage($(this));
            }
        });

        $('.coupons .delete').click(function(){
            var name = $(this).parents('tr').find('.page_name').text();
            if(confirm('Delete coupon - '+ name + '?')){
                deleteCoupon($(this));
            }
        });

        $('.articles .ui.checkbox.toggle').checkbox({
            onChecked: function(){
                setArticleProperty($(this), 1);
            },
            onUnchecked: function(){
                setArticleProperty($(this), 0);
            },
        });

        $('.pages .ui.checkbox.toggle').checkbox({
            onChecked: function(){
                setPageProperty($(this), 1);
            },
            onUnchecked: function(){
                setPageProperty($(this), 0);
            },
        });

        $('.coupons .ui.checkbox.toggle').checkbox({
            onChecked: function(){
                setCouponProperty($(this), 1);
            },
            onUnchecked: function(){
                setCouponProperty($(this), 0);
            },
        });

        $('#article_form input[type="file"]').change(function () {
            $(this).siblings('.file_path').text($(this).val());
        });


        $('.special.cards .image').dimmer({
            on: 'hover'
        });

        $('.edit_slide').click(function(){
            editSlide($(this).parents('.card').find('input[type="hidden"]').val());
        });

/*

        $('.slide').each(function(){
            var photoName = $(this).siblings('input[type="hidden"]').val();
            $(this).attr('src', $.cloudinary.url(photoName, { width: 300}));
        });
*/
        $('.headers .button').click(function(){
            $(this).parents('.headers').find('.button').removeClass('olive');
            $(this).addClass('olive');
            $(this).siblings('input[type="hidden"]').val($(this).text());
        });

        $('.region_or_zip_code .button').click(function(){
            $(this).parents('.region_or_zip_code').find('.button').removeClass('red');
            $(this).addClass('red');
            var value = $(this).find('input[type="hidden"]').val();

            if(value == 'region'){
                $('.region_block').removeClass('hidden');
                $('.zip_code_block').addClass('hidden');
                $('#admin_advanced_search_zipCodeSingle').val('');
            }
            else{
                $('.zip_code_block').removeClass('hidden');
                $('.region_block').addClass('hidden');
            }

        });

        $('.footerHeaders .save.icon').click(function(){
            saveFooterHeader($(this).parent());
        });


        $('#removeSelectedMessages').click(function(){
            var messagesIds = [];

            $('.messages .row :checked').each(function(){
                messagesIds.push($(this).val());
            });
            removeSelectedMessages(messagesIds);
        });

        $('.ui.borderless.menu.paging a.item').click(function (event) {
            event.preventDefault();
            if(!$(this).hasClass('active')){
                $('#search_filter_form').attr('action',$(this).attr('href')).submit();
            }
        });


        //var zipCodes = [];
/*

        $('<option value=""></option>').insertBefore($('#admin_advanced_search_zipCode').find('option').eq(0));
        $('#admin_advanced_search_zipCode').addClass('ui search selection dropdown');


        $('#admin_advanced_search_zipCode')
            .dropdown({
                onChange: function(value, text, $selectedItem) {
                    //zipCodes.push($selectedItem.attr('data-value'));
                    var val = $selectedItem.attr('data-value');
                    $('#admin_advanced_search_zipCode option').removeAttr("selected");
                    $('#admin_advanced_search_zipCode option[value="' + val + '"]').attr("selected", "selected");
                }
            })
        ;

        $('.zip_code_block .ui.dropdown .text').text('Start typing');

*/

        $('.rotate').click(function () {
            var id = $(this).parents('.card').find('.sel_item input[type="hidden"]').val();
            var rotate = ($(this).hasClass('left-side')) ? 90 : -90;
            var image = $(this).parents('.card').find('img');
            rotateImage(id,rotate,image);
        });

        faqInit();

        $('.bannerActive').click(function () {
            var id = $(this).parents('tr').find('.banner_id').val();
            $.ajax({
                url: '/admin/banner/' + id + '/activate',
            });
        });

        if($('#reportItemSearch').size() > 0){
            var reportUserOptions = {
                valueNames: [ 'item_value' ],
                searchClass: 'reportItemSearch',
                listClass: 'reportItemList',
            };

            var usersList = new List('reportItemSearch', reportUserOptions);
        }

        console.log($('#admin_advanced_search_gender_2').length);
        if($('#admin_advanced_search_gender_2').length > 0){
            $('#admin_advanced_search_gender_2').change(function () {
                console.log($(this).is(':checked'));
                if($(this).is(':checked') && !$('#admin_advanced_search_gender_1').is(':checked') && !$('#admin_advanced_search_gender_3').is(':checked')){
                    $('.age1, .she, .he').removeClass('hidden');
                }else{
                    $('.age1, .she, .he').addClass('hidden');
                }
            });
            $('#admin_advanced_search_gender_1,#admin_advanced_search_gender_3').change(function () {
                if($(this).is(':checked')){
                    $('.age1, .she, .he').addClass('hidden');
                }
            });
        }

        ajaxHelper();



    }
);

function deleteItem(thisObj){

    var id = thisObj.parents('tr').find('.item_id').val();
    var list = thisObj.parents('table').attr('list');
    jQuery.ajax({
        url: '/admin/content/lists/' + list + '/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });

}

function ajaxHelper() {
    if(jQuery('#ajaxHelper').length > 0){
        jQuery.ajax({
            url: '/admin/ajax/users/list',
            type: 'get',
            dataType: 'json',
            data: jQuery('#ajaxHelper').serialize(),
            error: function (response) {
                console.log("Error: " + response);
            },
            success: function (response) {
                console.log(response);
                jQuery('#count_results').html(response.count);
                if(response.reports.length > 0) {
                    for (var i in response.reports) {
                        var report = response.reports[i];
                        jQuery('.flagged_reports .item input[value="' + report.id + '"]').parents('.item').find('.ui.label').html(report.count);
                    }
                }
            }
        });
    }
}

var updateImg = 0;

function rotateImage(id,rotate,image){
    jQuery.ajax({
        url: '/admin/users/user/photo/rotate',
        type: 'Post',
        data: 'id=' + id + '&rotate=' + rotate,
        dataType: 'json',
        error: function (response) {
            console.log("Error: " + response);
        },
        success: function (response) {
            console.log(response);
            //var src = $(this).parents('.card').find('img').attr('src') + '?u='+updateImg;
            //alert(src);
            if(response.fullPhoto){
                response.fullPhoto = response.fullPhoto + '?u='+updateImg;
                updateImg++;

                image.attr('src',response.fullPhoto);
            }
        }
    });
}

function addAreas($checkbox){

    $checkbox.closest('form').find('input, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/search/advanced/helper',
        type: 'Post',
        data: 'regionId=' + $checkbox.val(),
        error: function(response){
            console.log("Error: " + response);
        },
        success: function(response){
            jQuery('.area.label').removeClass('hidden');
            jQuery('.areas').append(response);

            jQuery('.advanced_search .ui.checkbox.area').checkbox({
                onChecked: function(){
                    addZipCodes(jQuery(this));
                },
                onUnchecked: function(){
                    deleteZipCodes(jQuery(this));
                },
            });

            $checkbox.closest('form').find('input, select, textarea').prop( "disabled", false );

        }
    });
}

function addZipCodes($checkbox){

    $checkbox.closest('form').find('input, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/search/advanced/helper',
        type: 'Post',
        data: 'areaId=' + $checkbox.val(),
        error: function(response){
            console.log("Error: " + response);
        },
        success: function(response){
            jQuery('.zipCode.label').removeClass('hidden');
            jQuery('.zipCodes').append(response);
            $checkbox.closest('form').find('input, select, textarea').prop( "disabled", false );
        }
    });
}

function deleteAreas($checkbox){
    var id = $checkbox.val();
    jQuery('.area.parent_' + id).remove();
    jQuery('.zipCode.root_parent_' + id).remove();

    if(!jQuery('.areas .block').size()){
        jQuery('.area.label').addClass('hidden');
    }

    if(!jQuery('.zipCodes .block').size()){
        jQuery('.zipCode.label').addClass('hidden');
    }
}

function deleteZipCodes($checkbox){
    var id = $checkbox.val();
    jQuery('.zipCode.parent_' + id).remove();
    if(!jQuery('.zipCodes .block').size()){
        jQuery('.zipCode.label').addClass('hidden');
    }
}


function logInAsUser(id){
    window.location.href = '/admin/users/login/' + id;
}


function givePoint(giveToAll){

    var state = giveToAll ? 1 : 0;

    jQuery.ajax({
        url: '/admin/users/point/' + state,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });

}


function exportToCSV(exportWindow){

    var fileName = exportWindow.find('input[type="text"]').val().trim();

    if(!fileName){
        alert('שם הקובץ ריק');
        return;
    }

    jQuery('#search_filter_form')
        .attr('action', '/admin/users/export')
        .find('input[name="fileName"]')
        .val(fileName)
        .parents('form')
        .submit()
    ;
}


function deleteReport(thisObj){

    var id = thisObj.parents('tr').find('.report_id').val();

    jQuery.ajax({
        url: '/admin/users/reports/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });

}

function showReportOnMainPage(state, id){

    jQuery.ajax({
        url: '/admin/users/reports/' + id + '/show_on_main_page/' + state,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });

}

function createReport(createReportWindow){

    if(!createReportWindow.find('input[type="text"]').val().trim()){
        alert('הדוח לא נוצר. שם הדוח ריק');
        return;
    }

    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    var reportSettings = createReportWindow.find('form').serialize();
    var reportData = jQuery('#search_filter_form').serialize();

    jQuery.ajax({
        url: '/admin/users/reports/create',
        type: 'Post',
        data: reportSettings + '&' + reportData,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            location.reload(true);
        }
    });


}

function approvePhoto(id, approve){
    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    approve = approve ? 1 : 0;

    $('input[value="' + id + '"]').parents('.card').remove();

    $.ajax({
        url: '/admin/users/photos/waiting/' + id + '/approve/' + approve,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        },
        success: function(response){
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
            if($('.waiting_photos .card').length == 0){
                // $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
                $('.waiting_photos .actions').remove();
                setTimeout(function () {
                    window.location.href = '/admin/users/list';
                },1000);
            }
        }
    });
}


function viewProfile(id){
    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/admin/users/view/profile/' + id,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            $('#viewed_user_data').html(response).kfModal();
            $('#viewed_user_data .menu .item').tab();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}


function getSubscr(id){
    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: '/admin/users/user/' + id + '/subscription',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#subscr').html(response).kfModal();
            jQuery('#subscr .menu .item').tab();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function editor_init(id) {

    tinymce.init({

        selector: '#textMessage-'+id,

        theme: "modern",
        directionality: "rtl",

        menubar: false,

        plugins: [

            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",

            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",

            "save table contextmenu directionality emoticons template paste textcolor"

        ],

        //content_css: "css/content.css",

        toolbar: "insertfile undo redo | bold italic underline | styleselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | preview media fullpage | forecolor backcolor emoticons | link image | code | ltr rtl",

        style_formats: [

            {
                title: "Headers", items: [

                    {title: "Header 3", format: "h3"},

                    {title: "Header 4", format: "h4"},

                    {title: "Header 5", format: "h5"},

                    {title: "Header 6", format: "h6"}

                ]
            },

            {
                title: "Inline", items: [

                    {title: "Bold", icon: "bold", format: "bold"},

                    {title: "Italic", icon: "italic", format: "italic"},

                    {title: "Underline", icon: "underline", format: "underline"},

                    {title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},

                    {title: "Superscript", icon: "superscript", format: "superscript"},

                    {title: "Subscript", icon: "subscript", format: "subscript"},

                ]
            },

            {
                title: "Alignment", items: [

                    {title: "Left", icon: "alignleft", format: "alignleft"},

                    {title: "Center", icon: "aligncenter", format: "aligncenter"},

                    {title: "Right", icon: "alignright", format: "alignright"},

                    {title: "Justify", icon: "alignjustify", format: "alignjustify"}

                ]
            }

        ],

        cleanup: false,

        verify_html: false,

    });
}

function getQuickSend(id) {
    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    jQuery.ajax({
        url: '/admin/users/user/quick/send/' + id,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#edited_user_data').html(response).kfModal();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
            // editor_init(id);
        }
    });
}

function sendQuickMessage(form) {
    var id = form.find('.send_user_id').val();
    jQuery.ajax({
        url: '/admin/users/user/quick/send/' + id,
        type: 'Post',
        data: form.serialize(),
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#edited_user_data').html(response).kfModal();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
            // editor_init(id);
        }
    });
}

function getEditedProfile(id){

    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: '/admin/users/edit/profile/' + id,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#edited_user_data').html(response).kfModal();
            jQuery('#edited_user_data .menu .item').tab();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}


function getUserPhotosModal(id){
    //jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    jQuery('#user_photos').find('.photos').html('');
    jQuery('#user_photos').kfModal();
    getUserPhotos(id);
}


function getUserPhotos(id){

    jQuery('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: '/admin/users/user/' + id + '/photos',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#user_photos').find('.photos').html(response);
            jQuery('#user_photos_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });

}


function savePhotoData(data, userId){

    jQuery('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    var mainPhotoAlreadyExists = jQuery('#mainPhotoAlreadyExists').val();

    jQuery.ajax({
        url: '/admin/users/user/' + userId + '/photos/photo/data',
        type: 'Post',
        data: 'name=' + data.result.public_id + '&mainPhotoAlreadyExists=' + mainPhotoAlreadyExists,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(){
            getUserPhotos(userId);
        }
    });
}

function setPhotoProperty(property, value, id){

    console.log(property, value, id);

    jQuery.ajax({
        url: '/admin/users/user/photos/' + id + '/' + property + '/' + value,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            //jQuery('#user_photos').find('.photos').html(response);
            //jQuery('#user_photos_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });

}

function deletePhoto(id, node){

    jQuery('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    var thisIsMainPhoto = node.find('.main_photo input').is(":checked");
    node.remove();

    if(thisIsMainPhoto){
        jQuery('.photo').eq(0).find('.main_photo').click();
    }

    jQuery.ajax({
        url: '/admin/users/user/photos/' + id + '/delete',
        type: 'Post',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(otherPhotoId){
            if(otherPhotoId > 0){
                var radiobox = jQuery('.photos .main_photo').find('input[value="' + otherPhotoId + '"]');
                radiobox.parents('.photos .main_photo').checkbox('attach events', radiobox, 'check');
                radiobox.click();
            }
            if(!jQuery('.photos .photo').size()){
                jQuery('#mainPhotoAlreadyExists').val(0);
            }

            jQuery('#user_photos_dimmer').addClass("disabled").find('.loader').addClass("disabled");

        }
    });
}

function saveProfile(id, form, tab){

    if (( typeof(form[0].checkValidity) == "function" ) && !form[0].checkValidity()) {
        return;
    }

    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/users/edit/profile/' + id + '/' + tab,
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#edited_user_data')
                .html(response)
                .find('.menu .item')
                .tab()
            ;
        }
    });
}

function setUserProperty(property, value, userId, icon){
    jQuery.ajax({
        url: '/admin/user/' + userId + '/'+ property +'/' + value,
        type: 'Post',
        error: function(response){
            console.log("Error:" + response);
        },
        success: function(response){
            console.log(response);
            if(icon){
                var item = jQuery('.users input[value="' + userId + '"]').parents('tr').find('.user_props i.icon.' + icon);
                if(icon == 'call'){
                    if (value == 1) {
                        item.removeClass('purple').addClass('green').attr('data-content','יש אקטיבציה בטלפון');
                    }else{
                        item.removeClass('green').addClass('purple').attr('data-content','אין אקטיבציה בטלפון');
                    }
                }else {
                    if (value == 1) {
                        item.removeClass('hidden');
                    }
                    else {
                        item.addClass('hidden');
                    }
                }
            }
        }
    });

}

function deleteUser(userId){
    jQuery.ajax({
        url: '/admin/user/' + userId + '/delete',
        type: 'Post',
        error: function(response){
            console.log("Error:" + response);
        },
        success: function(response){
            console.log(response);
            //alert('בוצע');
        }
    });

}

function saveBanUsersReason(users, reason){

    if(!reason.trim().length || !users.length){
        return;
    }

    jQuery.ajax({
        url: '/admin/users/save/ban/reason',
        type: 'Post',
        data: 'users=' + users + '&reason=' + reason,
        error: function(response){
            console.log("Error:" + response);
        },
        success: function(response){
            console.log(response);
        }
    });

}


function setArticleProperty(thisObj, value){

    var id = thisObj.parents('tr').find('.article_id').val();
    var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';

    jQuery.ajax({
        url: '/admin/magazine/article/' + id + '/'+ property +'/' + value,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });

}



function deleteArticle(thisObj){

    var id = thisObj.parents('tr').find('.article_id').val();

    jQuery.ajax({
        url: '/admin/magazine/article/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });

}


function  setCouponProperty(thisObj, value) {
    var id = thisObj.parents('tr').find('.coupon_id').val();
    var property = 'isActive';

    jQuery.ajax({
        url: '/admin/content/coupon/' + id + '/'+ property +'/' + value,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });
}


function setPageProperty(thisObj, value){

    var id = thisObj.parents('tr').find('.page_id').val();
    //var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';
    var property = 'isActive';

    jQuery.ajax({
        url: '/admin/content/page/' + id + '/'+ property +'/' + value,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });

}



function deleteCoupon(thisObj) {
    var id = thisObj.parents('tr').find('.coupon_id').val();

    jQuery.ajax({
        url: '/admin/content/coupon/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });
}



function deletePage(thisObj){

    var id = thisObj.parents('tr').find('.page_id').val();

    jQuery.ajax({
        url: '/admin/content/page/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });

}


function editSlide(id){
    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: '/admin/content/slide/' + id,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#slide').html(response).kfModal();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveSlide(id, form){

    if (( typeof(form[0].checkValidity) == "function" ) && !form[0].checkValidity()) {
        //return;
    }

    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/content/slide/' + id,
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

            jQuery('#slide').html(response);

            jQuery('.slides input[value="' + id + '"]')
                .parents('.card')
                .find('a.edit_slide')
                .text(jQuery('#slide_name').val())
            ;

            jQuery('.slides input[value="' + id + '"]')
                .parents('.card')
                .find('.slide')
                .attr('src', jQuery('.slide_image img').attr('src'))
            ;
        }
    });
}


function updateHomePageBlock(form, id){

    var data = form.serialize();
    form.siblings('div').find('button').addClass('loading');
    form.find('input, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/content/homepage/block/' + id,
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            form.siblings('div').find('button').removeClass('loading');
            form.find('input, select, textarea').prop( "disabled", false );
        }
    });
}


function updatePageSeo(form){

    var data = form.serialize();
    form.siblings('div').find('button').addClass('loading');
    form.find('input, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/content/pages/seo',
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            form.siblings('div').find('button').removeClass('loading');
            form.find('input, textarea').prop( "disabled", false );
        }
    });
}


function saveFooterHeader(wrapper){

    console.log(1);
    var name = wrapper.find('input[type="text"]').val();
    var id = wrapper.find('input[type="hidden"]').val();
    wrapper.find('i').addClass('loading');
    wrapper.find('input').prop( "disabled", true );

    jQuery.ajax({
        url: '/admin/content/footer/header/' + id,
        type: 'Post',
        data: 'name=' + name,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            wrapper.find('i').removeClass('loading');
            wrapper.find('input').prop( "disabled", false );
        }
    });
}


function faqCategory(id){

    var url = id ? '/admin/content/faq/category/' + id : '/admin/content/faq/category';

    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: url,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#faq_cat').html(response).kfModal();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveFaqCategory(id){

    var form = jQuery('#faq_cat_form');

    if (( typeof(form[0].checkValidity) == "function" ) && !form[0].checkValidity()) {
        return;
    }

    var url = id ? '/admin/content/faq/category/' + id : '/admin/content/faq/category';
    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, checkbox').prop( "disabled", true );

    jQuery.ajax({
        url: url,
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('.ui.segment.faq').html(response);
            faqInit();
            form.find('button').removeClass('loading');
            form.find('input, checkbox').prop( "disabled", false );
            jQuery.kfModal.close();
            //updateFaqSection();
        }
    });
}

function updateFaqSection(){
    jQuery.ajax({
        url: '/admin/content/faq/section/update',
        type: 'Post',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });
}



function faq(id){

    var url = id ? '/admin/content/faq/' + id : '/admin/content/faq';

    jQuery('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    jQuery.ajax({
        url: url,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('#faq').html(response).kfModal();
            jQuery('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveFaq(id){

    var form = jQuery('#faq_form');

    if (( typeof(form[0].checkValidity) == "function" ) && !form[0].checkValidity()) {
        return;
    }

    var url = id ? '/admin/content/faq/' + id : '/admin/content/faq';
    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, checkbox, select, textarea').prop( "disabled", true );

    jQuery.ajax({
        url: url,
        type: 'Post',
        data: data,
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            jQuery('.ui.segment.faq').html(response);
            faqInit();
            form.find('button').removeClass('loading');
            form.find('input, checkbox, select, textarea').prop( "disabled", false );
            jQuery.kfModal.close();
            //updateFaqSection();
        }
    });
}


function setFaqCategoryProperty(thisObj, value){

    var id = thisObj.parents('tr').find('.category_id').val();
    //var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';
    var property = 'isActive';

    jQuery.ajax({
        url: '/admin/content/faq/category/' + id + '/'+ property +'/' + value,
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){

        }
    });

}

function deleteFaqCategory(thisObj){

    var id = thisObj.parents('tr').find('.category_id').val();

    jQuery.ajax({
        url: '/admin/content/faq/category/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
            jQuery('.faq_cat_' + id).remove();
        }
    });
}


function deleteFaq(thisObj){

    var id = thisObj.parents('tr').find('.faq_id').val();

    jQuery.ajax({
        url: '/admin/content/faq/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
        }
    });

}

function faqInit(){
    jQuery('.faq .menu .item').tab();

    jQuery('.ui.accordion')
        .accordion()
    ;

    jQuery('.add_faq_cat').click(function(){
        faqCategory();
    });

    jQuery('.edit_faq_cat').click(function(){
        faqCategory(jQuery(this).siblings('input[type="hidden"]').val());
    });

    jQuery('.add_faq').click(function(){
        faq();
    });

    jQuery('.edit_faq').click(function(){
        //var idArr = jQuery(this).attr('id').split("_");
        //faq(idArr[1]);
        faq(jQuery(this).siblings('input[type="hidden"]').val());
    });

    jQuery('table.faq_categories .ui.checkbox.toggle').checkbox({
        onChecked: function(){
            setFaqCategoryProperty(jQuery(this), 1);
        },
        onUnchecked: function(){
            setFaqCategoryProperty(jQuery(this), 0);
        },
    });

    jQuery('table.faq_categories .delete').click(function(){
        var name = jQuery(this).parents('tr').find('.category_name').text();
        if(confirm('Delete category - '+ name + '?')){
            deleteFaqCategory(jQuery(this));
        }
    });

    jQuery('table.faq .delete').click(function(){
        var name = jQuery(this).parents('tr').find('.faq_name').text();
        if(confirm('Delete question - '+ name + '?')){
            deleteFaq(jQuery(this));
        }
    });

    jQuery('table.banners .delete').click(function(){
        var name = jQuery(this).parents('tr').find('.banner_id').text();
        console.log(name);
        if(confirm('להסיר באנר?')){
            deleteBanner(jQuery(this));
        }
    });
}

function deleteBanner(thisObj){

    var id = thisObj.parents('tr').find('.banner_id').val();

    jQuery.ajax({
        url: '/admin/content/banner/' + id + '/delete',
        type: 'Get',
        error: function(response){
            console.log("Error:" + JSON.stringify(response));
        },
        success: function(response){
            thisObj.parents('tr').remove();
            //  jQuery('.faq_cat_' + id).remove();
        }
    });
}


function removeSelectedMessages(messagesIds){

    if(!messagesIds.length){
        alert('Please choose messages');
        return;
    }

    if(!confirm('Delete?')){
        return;
    }


    jQuery.ajax({
        url: '/admin/messenger/messages/delete',
        data: 'messagesIds=' + messagesIds,
        type: 'Post',
        success: function(){

            jQuery('.messages .row :checked').each(function(){
                jQuery(this).parents('tr').remove();
            });

            alert('Messages have been deleted');

        },
        error: function(response){
            jQuery('body').html(response.responseText);
        }
    });

}




















//D4D old website function







function setDatePeriods(objRef_l, objRef_h, period, dtformat) {
    var d = new Date(),
        dStr_l = "",
        dStr_h = "",
        m;
    switch (period) {
        case "today":
            dStr_l = dStr_h = createdStr();
            break;
        case "week_t":
            d.setDate(d.getDate() - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_t":
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "quarter_t":
        case "3":
            m = d.getMonth();
            if (m <= 2) {
                d.setMonth(0)
            } else if (m <= 5) {
                d.setMonth(3)
            } else if (m <= 8) {
                d.setMonth(6)
            } else {
                d.setMonth(9)
            };
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_t":
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        case "week_n":
            d.setDate(d.getDate() + 7 - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_n":
            d.setMonth(d.getMonth() + 1);
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case 1:
        case 3:
        case 6:
        case 12:
            if(dtformat != '%Y-%mm-%dd'){
                var dateArr = objRef_l.val().split("-");
                var dateStrVal = '';
                if(dtformat.split("-")[2] == '%Y'){
                    dateStrVal += dateArr[2] + '-';
                }else if(dtformat.split("-")[1] == '%Y'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%Y'){
                    dateStrVal += dateArr[0] + '-';
                }
                if(dtformat.split("-")[1] == '%mm'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%mm'){
                    dateStrVal += dateArr[0] + '-';
                }else if(dtformat.split("-")[2] == '%mm'){
                    dateStrVal += dateArr[2] + '-';
                }
                if(dtformat.split("-")[1] == '%dd'){
                    dateStrVal += dateArr[1];
                }else if(dtformat.split("-")[0] == '%dd'){
                    dateStrVal += dateArr[0];
                }else if(dtformat.split("-")[2] == '%dd'){
                    dateStrVal += dateArr[2];
                }

                d = new Date(dateStrVal);
            }else {
                d = new Date(objRef_l.val());
            }
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + period);
            dStr_h = createdStr();
            break;
        case 0:
            if(dtformat != '%Y-%mm-%dd'){
                var dateArr = objRef_l.val().split("-");
                var dateStrVal = '';
                if(dtformat.split("-")[2] == '%Y'){
                    dateStrVal += dateArr[2] + '-';
                }else if(dtformat.split("-")[1] == '%Y'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%Y'){
                    dateStrVal += dateArr[0] + '-';
                }
                if(dtformat.split("-")[1] == '%mm'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%mm'){
                    dateStrVal += dateArr[0] + '-';
                }else if(dtformat.split("-")[2] == '%mm'){
                    dateStrVal += dateArr[2] + '-';
                }
                if(dtformat.split("-")[1] == '%dd'){
                    dateStrVal += dateArr[1];
                }else if(dtformat.split("-")[0] == '%dd'){
                    dateStrVal += dateArr[0];
                }else if(dtformat.split("-")[2] == '%dd'){
                    dateStrVal += dateArr[2];
                }
                d = new Date(dateStrVal);
            }else {
                d = new Date(objRef_l.val());
            }
            dStr_l = createdStr();
            dStr_h = createdStr();
            break;
        case "2weeks":
            if(dtformat != '%Y-%mm-%dd'){
                var dateArr = objRef_l.val().split("-");
                var dateStrVal = '';
                if(dtformat.split("-")[2] == '%Y'){
                    dateStrVal += dateArr[2] + '-';
                }else if(dtformat.split("-")[1] == '%Y'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%Y'){
                    dateStrVal += dateArr[0] + '-';
                }
                if(dtformat.split("-")[1] == '%mm'){
                    dateStrVal += dateArr[1] + '-';
                }else if(dtformat.split("-")[0] == '%mm'){
                    dateStrVal += dateArr[0] + '-';
                }else if(dtformat.split("-")[2] == '%mm'){
                    dateStrVal += dateArr[2] + '-';
                }
                if(dtformat.split("-")[1] == '%dd'){
                    dateStrVal += dateArr[1];
                }else if(dtformat.split("-")[0] == '%dd'){
                    dateStrVal += dateArr[0];
                }else if(dtformat.split("-")[2] == '%dd'){
                    dateStrVal += dateArr[2];
                }
                d = new Date(dateStrVal);
            }else {
                d = new Date(objRef_l.val());
            }
            dStr_l = createdStr();
            d.setDate(d.getDate() + 14);
            dStr_h = createdStr();
            break;
        case "quarter_n":
            m = d.getMonth();
            if (m <= 2) {
                d.setMonth(3)
            } else if (m <= 5) {
                d.setMonth(6)
            } else if (m <= 8) {
                d.setMonth(9)
            } else {
                d.setMonth(12)
            };
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_n":
            d.setFullYear(d.getFullYear() + 1);
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        case "week_p":
            d.setDate(d.getDate() - 7 - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_p":
            d.setMonth(d.getMonth() - 1);
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "quarter_p":
            m = d.getMonth();
            if (m <= 2) {
                d.setFullYear(d.getFullYear() - 1);
                d.setMonth(9)
            } else if (m <= 5) {
                d.setMonth(0)
            } else if (m <= 8) {
                d.setMonth(3)
            } else {
                d.setMonth(6)
            };
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_p":
            d.setFullYear(d.getFullYear() - 1);
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        default:
            return;
            break;
    };

/*
     objRef_l.value = dStr_l;
     objRef_h.value = dStr_h;

*/
    //modified


    objRef_l.val(dStr_l);
    objRef_h.val(dStr_h);


    function setMonthLastDate() {
        m = d.getMonth();
        if (m == 0 || m == 2 || m == 4 || m == 6 || m == 7 || m == 9 || m == 11) {
            d.setDate(31)
        } else if (m == 3 || m == 5 || m == 8 || m == 10) {
            d.setDate(30)
        } else if (d.getFullYear() % 4 == 0) {
            d.setDate(29)
        } else {
            d.setDate(28)
        }
    }

    function createdStr() {
        if ("undefined" != typeof (dtformat)) {
            if(console && console.log) {
                console.log('in')
            }
            return formatDate_ami( d, dtformat );
        }
        return ((d.getMonth() + 1) < 10 ? "0" + (d.getMonth() + 1) : (d.getMonth() + 1)) + "/" + (d.getDate() < 10 ? "0" + d.getDate() : d.getDate()) + "/" + d.getFullYear()
    }

    function formatDate_ami( dt, s ) {
        if( !(dt instanceof Date) ) {
            throw "Date object expected";
        }
        s = s.toString();

        console.log(s);
        var day = dt.getDate();
        if(day < 10) day = '0' + day;
        var month = dt.getMonth()+1;
        if(month < 10) month = '0' + month;
        s = s.replace(/%dd/g, day);
        s = s.replace(/%mm/g, month);
        s = s.replace(/%d/g, dt.getDate());
        s = s.replace(/%m/g, dt.getMonth()+1);
        s = s.replace(/%Y/g, dt.getFullYear());
        s = s.replace(/%H/g, dt.getHours());
        s = s.replace(/%i/g, dt.getMinutes());
        s = s.replace(/%s/g, dt.getSeconds());
        s = s.replace(/%f/g, dt.getMilliseconds());
        return s;
    }
}
