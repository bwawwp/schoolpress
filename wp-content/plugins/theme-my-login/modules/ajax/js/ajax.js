var tmlAjax;

(function($) {
	tmlAjax = {
		overlay : $('<div id="tml_ajax_overlay" />'),

		window : $('<div id="tml_ajax_window" />'),

		content : $('<div id="tml_ajax_content" />'),

		init : function() {
			$('li.tml_ajax_link a, a.tml_ajax_link')
				.attr('href', function(i, href) {
					if (href.indexOf('?') === -1)
						return href + '?ajax=1';
					else
						return href + '&ajax=1';
				})
				.click(function(e) {
					e.preventDefault();
					this.blur();
					tmlAjax.process(this.href, 'GET');
				});

			$(window).resize(function() {
				tmlAjax.reposition();
			});
		},

		process : function(url, type, data) {
			var t = this;

			$.ajax({
				url: url,
				data: data,
				type: type,
				success: function(data) {
					var r = wpAjax.parseAjaxResponse(data);

					if (r === false ) {
						t.show(data);
					} else {
						if (r.errors) {
							$.each(r.responses, function() {
								t.show(this.supplemental.html);
							});
						} else {
							$.each(r.responses, function() {
								t.show(this.data);
								if (this.supplemental.success == 1) {
									t.overlay.off('click').click(function() {
										t.hide();
										setTimeout(function() {
											location.reload();
										}, 500);
									});
								}
							});
						}
					}
				}
			});
		},

		show : function(content) {
			if (document.getElementById('tmlAjaxOverlay') === null) {
				this.overlay.appendTo('body').fadeTo('slow', 0.75);
				this.overlay.click(this.hide);
			}

			if (document.getElementById('tmlAjaxWindow') === null) {
				this.window.appendTo('body').fadeIn('slow');
			}

			if (document.getElementById('tmlAjaxContent') === null) {
				this.content.appendTo(this.window);
			}

			this.content.html(content);

			this.window.trigger('show');

			this.content.find(':input:visible:first').focus();

			this.window.find('form').submit(function(e) {
				e.preventDefault();
				tmlAjax.process($(this).attr('action'), 'POST', $(this).serialize());
			});

			this.window.find('.tml-action-links a').click(function(e) {
				e.preventDefault();
				this.blur();
				tmlAjax.process(this.href, 'GET');
			});

			this.window.find('.tml-user-links a').attr('target', '_parent');

			this.reposition();
		},

		hide : function() {
			tmlAjax.window
				.trigger('hide')
				.fadeOut('slow', function() {
					tmlAjax.window.unbind().remove();
				});
			tmlAjax.overlay
				.fadeTo('slow', 0, function() {
					tmlAjax.overlay.unbind().remove();
				});
		},

		reposition : function() {
			this.window.css({
				marginTop: '-' + parseInt((this.window.outerHeight() / 2),10) + 'px',
				marginLeft: '-' + parseInt((this.window.outerWidth() / 2),10) + 'px'
			});
		}
	}

	$(document).ready(tmlAjax.init);
})(jQuery);
