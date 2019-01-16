function custom_banners_setup_preview_window_color_picker() {
    var e = custom_banners_preview_window.find("#custom_banners_theme_preview_content");
    jQuery("#custom_banners_theme_preview_color_picker .iris-palette").bind("click", function(n) {
        var s = this;
        setTimeout(function() {
            var n = jQuery(s).css("backgroundColor");
            e.css("backgroundColor", n)
        }, 1)
    }), jQuery("#custom_banners_theme_preview_color_picker").bind("wp-color-picker-value-changed", function(n, s) {
        e.css("backgroundColor", s)
    }), e.css("backgroundColor", jQuery("#custom_banners_preview_window_background").val())
}

function custom_banners_update_preview_window() {
    var e = custom_banners_preview_window.find(".banner_wrapper .banner"),
        n = custom_banners_preview_window.find(".custom-b-cycle-controls"),
        s = custom_banners_preview_window.find(".custom-banners-cycle-slideshow"),
        r = jQuery("#custom_banners_theme"),
        o = jQuery("option:selected", r);
    o.parent().attr("label"), jQuery("#custom_banners_themes_pro_warning");
    e.removeClass(function(e, n) {
        return (n.match(/(^|\s)custom-banners-theme-\S+/g) || []).join(" ")
    }), n.removeClass(function(e, n) {
        return (n.match(/(^|\s)custom-banners-controls-theme-\S+/g) || []).join(" ")
    }), s.removeClass(function(e, n) {
        return (n.match(/(^|\s)custom-banners-cycle-slideshow-theme-\S+/g) || []).join(" ")
    });
    var t = r.val();
    if ("no_style" !== t) {
        var _ = t.indexOf("-") > 0 ? t.slice(0, t.indexOf("-")) : "";
        if (_.length > 0) {
            var c = _.length > 0 ? "custom-banners-theme-" + _ : "",
                a = _.length > 0 ? "custom-banners-controls-theme-" + _ : "",
                i = _.length > 0 ? "custom-banners-cycle-slideshow-theme-" + _ : "";
            e.addClass(c), n.addClass(a), s.addClass(i)
        }
        var u = "custom-banners-theme-" + t,
            w = "custom-banners-controls-theme-" + t,
            m = "custom-banners-cycle-slideshow-theme-" + t;
        e.addClass(u), n.addClass(w), s.addClass(m)
    }
    s.gp_cycle("next"), "no_style" !== r.val() ? custom_banners_preview_window.show() : custom_banners_preview_window.hide();
	
	var pager_tmpl = '<span><a href=&quot;#&quot;>{{slideNum}}</a></span>';	
	if ( t.indexOf('classic_tile') > -1 || t.indexOf('tile') > -1 ) {
		pager_tmpl = '"<span><a href=\'#\'><img src=\'{{firstChild.firstChild.src}}\'></a></span>"';
	}
	jQuery(s).attr('data-cycle-pager-template', pager_tmpl);
	s.gp_cycle('reinit');
}
var custom_banners_preview_window;
jQuery(function() {
    if (custom_banners_preview_window = jQuery("#custom_banners_theme_preview"), custom_banners_preview_window.length > 0) {
        custom_banners_preview_window.find(".banner_wrapper .banner"), custom_banners_preview_window.find("#custom_banners_theme_preview_content");
        custom_banners_setup_preview_window_color_picker(), custom_banners_update_preview_window(), jQuery("#custom_banners_theme").bind("change", function() {
            custom_banners_update_preview_window()
        })
    }
});