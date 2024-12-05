(function( $ ) {
	'use strict';

	$.fn.tzCheckbox = function (options) {
		options = jQuery.extend(
			{
				labels: ['ON', 'OFF']
			},
			options
		);

		return this.each(function () {
			var originalCheckBox = jQuery(this),
				labels = [];
			if (originalCheckBox.data('on')) {
				labels[0] = originalCheckBox.data('on');
				labels[1] = originalCheckBox.data('off');
			} else labels = options.labels;
			var checkBox = jQuery('<span>');
			checkBox.addClass(this.checked ? ' tzCheckBox checked' : 'tzCheckBox');
			checkBox.prepend(
				'<span class="tzCBContent">' + labels[this.checked ? 0 : 1] + '</span><span class="tzCBPart"></span>'
			);
			checkBox.insertAfter(originalCheckBox.hide());

			checkBox.click(function () {
				checkBox.toggleClass('checked');
				var isChecked = checkBox.hasClass('checked');
				originalCheckBox.attr('checked', isChecked);
				checkBox.find('.tzCBContent').html(labels[isChecked ? 0 : 1]);
			});

			originalCheckBox.bind('change', function () {
				checkBox.click();
			});
		});
	}

	function setmyCookie(cname, cvalue, exdays) {
		const d = new Date();
		d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
		let expires = "expires=" + d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	function getmyCookie(cname) {
		let name = cname + "=";
		let ca = document.cookie.split(";");
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == " ") {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}

	var tabs;
	function getTabKey(href) {
	  return href.replace('#', '');
	}
	function hideAllTabs() {
		tabs.each(function(){
			var href = getTabKey(jQuery(this).attr('href'));
			jQuery('#' + href).hide();
		});
	}
	function activateTab(tab) {
		var href = getTabKey(tab.attr('href'));
		tabs.removeClass('nav-tab-active');
		tab.addClass('nav-tab-active');
		setmyCookie("mybraintab", href, 365);
		jQuery('#' + href).show();
	}

	jQuery(document).ready(function($){
		var activeTab, firstTab, mytab;
		tabs = $('a.nav-tab');
		if (tabs.length > 0) {
			firstTab = false;
			activeTab = false;
			hideAllTabs();
			// First load, activate first tab or tab with nav-tab-active class
			mytab = getmyCookie("mybraintab");
			tabs.each(function(){
				var href = $(this).attr('href').replace('#', '');
				if (!firstTab) {
					firstTab = $(this);
				}
				if ($(this).hasClass('nav-tab-active')) {
					activeTab = $(this);
				}
				if ((mytab != '') && (mytab == href)) {
					activeTab = $(this);
				}
			});
			if (!activeTab) {
				activeTab = firstTab;
			}
			activateTab(activeTab);
			tabs.click(function(e) {
				e.preventDefault();
				hideAllTabs();
				activateTab($(this));
			});
		}

		var toggles;
		toggles = $('.mbtoggle');
		if (toggles.length > 0) {
			toggles.each(function(){
				if ($(this).hasClass('yesno')) {
					$(this).tzCheckbox({ labels: ['Yes', 'No'] });
				} else {
					if ($(this).hasClass('janee')) {
						$(this).tzCheckbox({ labels: ['Ja', 'Nee'] });
					} else {
						if ($(this).hasClass('aanuit')) {
							$(this).tzCheckbox({ labels: ['Aan', 'Uit'] });
						} else {
							$(this).tzCheckbox({ labels: ['On', 'Off'] });
						}
					}
				}
			});
		}
	});

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
