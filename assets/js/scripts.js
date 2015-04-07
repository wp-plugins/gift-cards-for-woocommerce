jQuery(document).ready( function($) {
	$("form.check_giftcard_balance").submit( function() {
		var data = {
			action:    'woocommerce_check_giftcard_balance',
            post_var:  $form.find('input[name=giftcard_code]').val()
		};
		// the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
	 	$.post(the_ajax_script.ajaxurl, data, function(response) {
	 		$( "#check_giftcard").hide();

			$( "#theBalance" ).append(response);
	 	});
	 	return false;
	});
});