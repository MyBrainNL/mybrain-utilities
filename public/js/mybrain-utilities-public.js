(function( $ ) {
	'use strict';

	setTimeout(function () {
		(function() {
			if ( window.console ) {
				console.log( "%cSTOP!", "color:#f00;font-size:xx-large" );
				console.log(
					"%cWait! This browser feature runs code that can alter your website or its security, " +
					"and is intended for developers. If you've been told to copy and paste something here " +
					"to enable a feature, someone may be trying to compromise your account. Please make " +
					"sure you understand the code and trust the source before adding anything here.",
					"font-size:large;"
				);
			}
		})();
	},2500);

})( jQuery );
