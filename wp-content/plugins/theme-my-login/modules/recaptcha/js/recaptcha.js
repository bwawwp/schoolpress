(function($) {
	$(document)
		.on('ready', function() {
			if ( $('#recaptcha').length ) {
				Recaptcha.create(tmlRecaptcha.publickey, 'recaptcha', {
					theme: tmlRecaptcha.theme
				});
			}
		})
		.on('show', '#tml_ajax_window', function() {
			if ( $(this).find('#recaptcha').length ) {
				Recaptcha.create(tmlRecaptcha.publickey, $(this).find('#recaptcha').get(0), {
					theme: tmlRecaptcha.theme
				});
			}
		})
		.on('hide', '#tml_ajax_window', function() {
			Recaptcha.destroy();

			if ( $('#recaptcha').length ) {
				Recaptcha.create(tmlRecaptcha.publickey, 'recaptcha', {
					theme: tmlRecaptcha.theme
				});
			}
		});
})(jQuery);
