if(!jQuery.ui) {
    jQl.loadjQdep(wbforms_ui_cdn);
}

jQuery('document').ready(function($)
{
    // append UI theme
    $("head").append($("<link rel='stylesheet' href='" + wbforms_ui_css.replace(/\%s/,wbforms_ui_theme) + "' type='text/css' media='screen' />"));
    // append image dropdown
    if($("select.fbimageselect").length)
    {
        $("head").append($("<link rel='stylesheet' href='" + WBLIB_URL + "/egg-0.5/egg.css' type='text/css' media='screen' />"));
        $("head").append($("<script src='" + WBLIB_URL + "/egg-0.5/egg.js' type='text/javascript'></script>"));
// *****************************************************************************
// TODO: Das gehört hier nicht hin!
// *****************************************************************************
        //$("select.fbimageselect option").each(function(){
        //    $(this).text('http://localhost/_projects/bcwa/media/flexRecord/cat_pics/'+$(this).val());
        //});
        $("select.fbimageselect").EggImageDropdown();
    }

    function wbforms_attach_select2() {
        $("select").not('.fbimageselect').select2({width:'250px'});
    }

    if(typeof select2 == 'undefined') {
        $("head").append($("<link rel='stylesheet' href='" + wbforms_sel_css + "' type='text/css' media='screen' />"));
        $.getScript(wbforms_sel_cdn,wbforms_attach_select2);
    }
    else
    {
        wbforms_attach_select2();
    }

    // add some styling to labels
    jQuery('div.fbform .fblabel').css('display','inline-block');
    jQuery('div.fbform .fblabel').css('width','250px');
    jQuery('div.fbform .fblabel').css('margin-right','10px');
    jQuery('div.fbform .fblabel').css('text-align','right');
    // notes and errors
    jQuery('div.fbform .fbnote').css('display','inline-block');
    jQuery('div.fbform .fbnote').css('margin-left','275px');
    jQuery('div.fbform .fberror').css('color','#f00');
    // add tooltips
    jQuery('form.ui-widget [title]').each( function() {
        if(jQuery(this).is('div')) {
            jQuery(this).append(
                '<span class="fbinfo ui-icon ui-icon-info" style="display:inline-block;vertical-align:top;width:20px;margin-left:5px;" title="' + jQuery(this).attr('title') + '">&nbsp;<\/span>'
            );
        }
        else {
            if(!jQuery(this).is('span')) {
                jQuery(this).after(
                    '<span class="fbinfo ui-icon ui-icon-info" style="display:inline-block;vertical-align:top;width:20px;margin-left:5px;" title="' + jQuery(this).attr('title') + '">&nbsp;<\/span>'
                );
            }
        }
    });
    jQuery('span.fbinfo').tooltip({
        position: {
            my: 'left-70 bottom-20',
            at: 'left+15 top',
            using: function(position, feedback) {
                 jQuery(this).css(position);
                 jQuery('<div>')
                 .addClass('arrow')
                 .addClass(feedback.vertical)
                 .addClass(feedback.horizontal)
                 .appendTo(this);
            }
        }
    });
    jQuery('span.fbrequired').tooltip({
        position: {
            my: 'center bottom-20',
            at: 'center top',
            using: function(position, feedback) {
                 jQuery(this).css(position);
                 jQuery('<div>')
                 .addClass('arrow')
                 .addClass(feedback.vertical)
                 .addClass(feedback.horizontal)
                 .appendTo(this);
            }
        }
    });
    // required
    jQuery('.fbrequired').addClass('ui-icon ui-icon-star');

});
