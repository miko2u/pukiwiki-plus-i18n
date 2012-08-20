/* ===================================================
 * jquery.inputcount.js v0.1
 * https://github.com/miko2u/jquery-inputcount
 * ===================================================
 * Copyright 2012 Miko.Hoshina
 * Dual licensed under the MIT or GPLv2 licenses.
 * ================================================ */

!function ($) {

  $(function () {

	"use strict"; // jshint ;_;

	$.fx.interval = 33;
	$.support.placeholder = 'placeholder' in document.createElement('input');

	$.fn.textcount = function(options) {
		var config = $.extend({
			focusDisplay: true
		}, options);

		this.each(function() {
			var target = $(this),
				supported = $.support.placeholder,
				targetOffset = target.offset(),
				border = {
					rightWidth: parseInt(target.css('borderRightWidth')),
					bottomWidth: parseInt(target.css('borderBottomWidth'))
				},
				currentCount = getCount(target),
				count = $('<span class="count"></span>').text(currentCount),
				pos = {};

			if (! target.attr('maxLength')) {
				return true; // no maxLength, continue each.
			}

			setCountStyle(currentCount, count);

			$(window).resize(function() {
				setCountPos(target, count, getCountPos(target, count), border);
			});

			$('body').append(count);
			setCountPos(target, count, getCountPos(target, count), border);

			if (config.focusDisplay) {
				count.hide();
				target.focus(function() {
					var currentCount = getCount(target);
					count.stop(true, true).fadeIn(250);
					setCountPos(target, count, getCountPos(target, count), border);
				}).blur(function() {
					count.stop(true, true).fadeOut(250);
				});
			}
			if (target.prop('tagName') == 'TEXTAREA' && !(supported)) {
				target.bind('keydown', function(e) {
					var currentCount = getCount(target),
						code = e.keyCode;
					if (currentCount >= 0) return;
					switch (code) {
					case  8: case  9: case 16: case 17: case 18: case 35:
					case 36: case 37: case 38: case 39: case 40: case 45: case 46:
						return;
					}
					return false;
				});
			}
			target.bind('keyup', function() {
				var currentCount = getCount(target);
				setCountStyle(currentCount, count);
				count.text(currentCount);
				setCountPos(target, count, getCountPos(target, count), border);
			}).bind('cut paste input change', function(e) {
				setTimeout(function() {
					var maxCount = target.attr('maxlength');
					if (target.val().length > maxCount) {
						target.val(target.val().substr(0,maxCount));
					}
					var currentCount = getCount(target);
					setCountStyle(currentCount, count);
					count.text(currentCount);
					setCountPos(target, count, getCountPos(target, count), border);
				}, 20);
			});
		});

		function getCount(target) {
			return target.attr('maxLength') - target.val().length;
		}

		function setCountStyle(currentCount, count) {
			if (currentCount < 1) {
				count.removeClass('count-info count-warn count-stop');
				count.addClass('count-stop');
			} else if (currentCount < 5) {
				count.removeClass('count-info count-warn count-stop');
				count.addClass('count-warn');
			} else {
				count.removeClass('count-info count-warn count-stop');
				count.addClass('count-info');
			}
		}

		function getCountPos(target, extra) {
			var targetOffset = target.offset();
			return {
				x: Math.floor(targetOffset.left),
				y: Math.floor(targetOffset.top),
				xdash: target.prop('offsetWidth'),
				ydash: target.prop('offsetHeight'),
				cx: extra.prop('offsetWidth'),
				cy: extra.prop('offsetHeight')
			}
		}

		function setCountPos(target, count, pos, border) {
			if (target.prop('tagName') == 'TEXTAREA') {
				return count.css({
					left: pos.x + pos.xdash - pos.cx - border.rightWidth - 2,
					top: pos.y + pos.ydash - pos.cy - border.bottomWidth - 2
				});
			}
			return count.css({
				left: pos.x + pos.xdash - pos.cx - border.rightWidth - 2,
				top: pos.y + pos.ydash - pos.cy - border.bottomWidth - 2
			});
		}

	}

  })

}(window.jQuery);

