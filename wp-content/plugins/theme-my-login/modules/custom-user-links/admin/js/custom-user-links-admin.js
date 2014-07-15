jQuery(document).ready( function($) {
	$('#theme_my_login_user_links tbody').wpList( {
		addBefore: function( s ) {
			var cls = $(s.target).attr('class').split(':'),
				role = cls[1].split('-')[0];
			s.what = role + '-link';
			return s;
		},
		addAfter: function( xml, s ) {
			var cls = $(s.target).attr('class').split(':'),
				role = cls[1].split('-')[0];
			$('table#' + role + '-link-table').show();
		},
		delBefore: function( s ) {
			var cls = $(s.target).attr('class').split(':'),
				role = cls[1].split('-')[0];
			s.data.user_role = role;
			return s;
		},
		delAfter: function( r, s ) {
			var t = $('#' + s.element).closest('tbody');
			$('#' + s.element).remove();
			if (t.children('tr').length == 0)
				t.parent().hide();
		}
	} );
	
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};
	
	$('#theme_my_login_user_links table.sortable tbody').sortable({
		axis: 'y',
		helper: fixHelper,
		items: 'tr'
	});

	postboxes.add_postbox_toggles(pagenow);
} );
