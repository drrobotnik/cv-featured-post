# CV Featured Posts

This plug-in uses a lite version of the [Advanced Custom Fields](http://advancedcustomfields.com/) WordPress plugin and is intended for WP developers who use this plug-in.

-----------------------

### Dependency

ACF has optional "Options Page" and "Repeater Field" add-ons which CV Featured Posts uses. In an effort to support the developer we require that you [include your own registration key](http://www.advancedcustomfields.com/add-ons/).

After you've purchased the registration key, you can enable the additional functionality like so:

	function cv_acf_settings( $options ){
		// activate add-ons
		if(!$options['activation_codes']['repeater']) $options['activation_codes']['repeater'] = 'XXXX-XXXX-XXXX-XXXX';
		if(!$options['activation_codes']['options_page']) $options['activation_codes']['options_page'] = 'XXXX-XXXX-XXXX-XXXX';
	}

	add_filter('acf_settings', 'cv_acf_settings');

I know this seems like a hassle, but if you're not already using ACF, you're missing out, and these keys can be used in all your other WP Projects.

### Notes

ACF Author recommends that you do not use this `lite` version with an active 'full' version of ACF running. You will recieve a white screen of death (PHP error) because both the `acf plugin` and the 'lite code' use the same classes / functions. I've built checks for this, but if you do receive a fatal error, disable the plugins, then enable ACF, and then CV Featured Posts.