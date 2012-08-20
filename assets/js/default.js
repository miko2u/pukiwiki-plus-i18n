/*!
* mikostyle.js
* Copyright 2012 Miko.Hoshina
* http://www.apache.org/licenses/LICENSE-2.0.txt
*/
$(function(){

	// Prettify
	window.prettyPrint && prettyPrint();

	// Pjax
	if ($.fn.pjax) {
		$("a.pjax").pjax("#main", {"timeout": 8000});
		$('#main')
			.bind('pjax:start', function(){ /* 開始時の処理 */ })
			.bind('pjax:end',   function(){ /* 終了時の処理 */ });
	}

	// Tablesorter
	if ($.fn.tablesorter) {
		$('.tablesorter').tablesorter();
	}

	// Typeahead(same Autocomplete)
	if ($.fn.typeahead) {
		$('.typeahead').typeahead();
	}

	// Tooltip
	if ($.fn.tooltip) {
		$('.tooltips').tooltip();
	}

	// Popover
	if ($.fn.popover) {
		$('.popovers').popover();
	}

	// TextCount
	if ($.fn.textcount) {
		$('textarea, input[type="text"], input[type="password"]').textcount();
	}

	// Datepicker http://tech-sketch.jp/2011/12/datepicker.html
	if ($.fn.datepicker) {
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			beforeShowDay: function(day) {
				var result;
				switch (day.getDay()) {
					case 0:
						result = [false, "ui-datepicker-sunday"];
						break;
					case 6:
						result = [true, "ui-datepicker-saturday"];
						break;
					default:
						result = [true];
						break;
				}
				return result;
			}
		});
		$('.datepicker-inline').datepicker({
			dateFormat: 'yy-mm-dd',
			inline: true,
			beforeShowDay: function(day) {
				var result;
				switch (day.getDay()) {
					case 0:
						result = [false, "ui-datepicker-sunday"];
						break;
					case 6:
						result = [true, "ui-datepicker-saturday"];
						break;
					default:
						result = [true];
						break;
				}
				return result;
			}
		});
	}

});
