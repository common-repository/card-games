/**
 * js file for cardgame plugin
 */
jQuery(document).ready(function(){
    jQuery('#cgwp_search_game').keyup(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13')
        {
            return;
        }
        var title = jQuery(this).val();
        var sort = jQuery('#cgwp_cats').val();
        var data = {
            action: 'cgwp_search_game',
            title: title,
            cat: sort,
            security: jQuery("#cgwp-search-ajax-nonce").val()
        }
        jQuery.post(ajaxurl, data, function(response){
            if(response == 'all')
            {
                location.reload();
            }
            jQuery('.cgwp_wrapper').html(response);
            jQuery('.cgwp_pageing').hide();
        });
    });
    jQuery('#cgwp_cats').on('change' , function(){
       var cat = jQuery(this).val();
        var title =  jQuery('#cgwp_search_game').val();
        var data = {
            action: 'cgwp_search_game',
            cat : cat,
            title : title,
            security: jQuery("#cgwp-search-ajax-nonce").val()
        }
        jQuery.post(ajaxurl, data , function(response){
            if(response == 'all')
            {
                location.reload();
            }
            jQuery('.cgwp_wrapper').html(response);
            jQuery('.cgwp_pageing').hide();
        })
    });
    jQuery('#cgwp_author_link_check').on('click' , function(){
        var author_link = (jQuery(this).is(':checked')) ? '1' : '0';
        var data = {
            action: 'cgwp_set_support_link_check',
            author_link : author_link,
            security: jQuery("#cgwp-search-ajax-nonce").val()
        }
        jQuery.post(ajaxurl, data , function(response){
            if(author_link == '1')
            {
                jQuery('.cgwp_author_link_confirm').show();
                jQuery('#cgwp-notice-support-view').hide();
            }
            else
            {
                jQuery('.cgwp_author_link_confirm').hide();
            }
        })
    })
});