function pageFocus(clicked) {
    if (clicked.attr('data-action') == 'show') {
        clicked.attr('data-action', 'hide');

		var title = clicked.attr('data-showJumboUKM');
		if (title == 'true') {
			jQuery('#ukm_page_jumbo_header').hide();
			jQuery('#ukm_page_jumbo_header_temp').show();
		}

        jQuery('#ukm_page_content').hide();
        jQuery('#ukm_page_pre_content').html( jQuery( clicked.attr('data-toggle') ).html() ).slideDown();

		jQuery('#ukm_page_jumbo_content').hide();
		jQuery('#ukm_page_jumbo_temp').html( clicked.attr('data-toggletitle') ).show();
		
		jQuery('#ukm_page_post_content').show();
		jQuery('#pageDeFocus').attr('data-clicker', clicked.attr('id') ).html( clicked.attr('data-toggleclose') );
    } else {
        clicked.attr('data-action', 'show');

		jQuery('#ukm_page_jumbo_temp').html('').hide();
		jQuery('#ukm_page_jumbo_content').show();

		jQuery('#ukm_page_pre_content').slideUp( 400, function(){jQuery('#ukm_page_content').show();} );
		jQuery('#ukm_page_post_content').hide();
		
		jQuery('#ukm_page_jumbo_header_temp').hide();
		jQuery('#ukm_page_jumbo_header').show();
    }
}

jQuery(document).on('click','#lokalmonstringer_toggle', function(){
	pageFocus( jQuery(this) );
});

jQuery(document).on('click','#show_kontaktpersoner,#show_main_mobile_menu', function(){
	pageFocus( jQuery(this) );
});
