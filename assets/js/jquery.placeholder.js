/* ===================================================
 * jquery.placeholder.js v0.1
 * https://github.com/miko2u/jquery-placeholder
 * ===================================================
 * Copyright 2012 Miko.Hoshina
 * Dual licensed under the MIT or GPLv2 licenses.
 * ================================================ */

!function ($) {

  $(function () {

	"use strict"; // jshint ;_;

	$.support.placeholder = 'placeholder' in document.createElement('input');

	$.fn.placeholder = function() {
		var supported = $.support.placeholder;
		if (!supported) {
			this.each(function() {
				var input = $('input[placeholder]', this);
				if (input.length) {
					var placeholder = $('<div class="placeholder">' + input.attr('placeholder') + '</div>')
						.insertBefore(input)
						.click(function() {
							placeholder.hide();
							input.focus();
						});
					input.blur(function() {
						if (!input.val()) {
							placeholder.show();
						}
					})
					.blur();
				}
			});
		}
		return this;
	};

}(window.jQuery);
