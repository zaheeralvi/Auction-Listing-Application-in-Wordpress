jQuery(function () {
	var sajak_tabs = false;
	
	var jquery_ui_classes = [
		".ui-tabs",
		".ui-tabs-nav",
		".ui-tabs-panel",
		".ui-widget",
		".ui-widget-header",
		".ui-widget-content",
		".ui-corner-all",
		".ui-corner-top",
		".ui-corner-bottom",
		".ui-helper-clearfix",
		".ui-helper-reset",
		".ui-state-default",
		".ui-state-active",
		".ui-state-focus",
		".ui-state-hover",	
		".ui-tabs-active"
	];
	var jqui_match_str = jquery_ui_classes.join(", ");
	var jqui_rm_str = jquery_ui_classes.join(" ").replace(/\./g, "");
	
	var update_tabs = function (event, ui) {
		toggle_save_visibility(event, ui);
		update_action(event, ui);
		jQuery(this).find('.ui-state-active').addClass('sajak-state-active');
		remove_jqui_classes();
	};
	
	var toggle_save_visibility = function (event, ui) {
		panel = get_target_panel(ui);
		if (panel) {
			react_to_panel(panel);
		}
	};	
	
	var update_action = function (event, ui) {
		var panel = get_target_panel(ui);
		var id = panel.attr('id');
		var input = panel.parents('form:first')
						 .find('input[name="_wp_http_referer"]');
		var current_url = input.val();
		var spot = current_url.indexOf('#');
		if (spot) {
			var parts = current_url.split('#');
			current_url = parts[0];
		}
						 
		if ( id && input ) {
			input.val(current_url + '#' + id);
		}
	};
	
	var remove_jqui_classes = function() {
		if (!sajak_tabs) {
			return;
		}
		$elems = jQuery(sajak_tabs).find( jqui_match_str ).andSelf();
		$elems.removeClass( jqui_rm_str );
	};
	
	var get_target_panel = function(ui) {
		if (ui.newPanel) {
			return ui.newPanel;
		} else if ( ui.panel ) {
			return ui.panel;
		}
		return false;
	}
	
	var react_to_panel = function (panel) {
		var btn = jQuery('.gp_sajak_save_button input[type="submit"]');
		if ( typeof( panel.data('show-save-button') ) !== undefined && panel.data('show-save-button') == 0 ) {
			btn.css('display', 'none');
		} else {
			btn.css('display', 'block');
		}
	};	
	
	var handle_jqui_events = function(event, ui)
	{
		if ( event && event.type  && event.type == 'click' ) {
			jQuery(this).parents('.gp_sajak:first').find('.sajak-state-active').removeClass('sajak-state-active');
			jQuery(this).addClass('sajak-state-active');
		}
		remove_jqui_classes();
		return true;		
	};

	sajak_tabs = jQuery( ".gp_sajak .gp_sajak_body" ).tabs({
		create: update_tabs,
		beforeActivate: update_tabs
	});
	
	jQuery('.gp_sajak').on('mouseover mouseout focus click', '.gp_sajak_menu_label', handle_jqui_events);	
	remove_jqui_classes();
	setTimeout(remove_jqui_classes, 20);
	setTimeout(remove_jqui_classes, 200);
	
	// prevent jump on page load
	if (sajak_tabs.length && location.hash) {
		setTimeout(function() {
			window.scrollTo(0, 0);
		}, 1);
	}	
});