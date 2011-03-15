jQuery.noConflict();
jQuery(document).ready(function(){ 
    jQuery("ul.sf-menu, ul.sf-menu-2").supersubs({ 
        minWidth:    14,                                // minimum width of sub-menus in em units 
        maxWidth:    27,                                // maximum width of sub-menus in em units 
        extraWidth:  1                                  // extra width can ensure lines don't sometimes turn over 
                                                        // due to slight rounding differences and font-family 
    }).superfish({ 
        delay:       400,                               // delay on mouseout 
        animation:   {opacity:'show',height:'show'},    // fade-in and slide-down animation 
        speed:       'fast',                            // faster animation speed 
        autoArrows:  false,                             // disable generation of arrow mark-up 
        dropShadows: false                              // disable drop shadows 
    }).children().find('li:first').css('border-top','0px').find('a').css('border-top','0px');
});
