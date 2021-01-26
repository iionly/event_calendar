define(function(require) {
	var elgg = require("elgg");
	var $ = require("jquery");
	
	function init() {
		$('.event-calendar-datepicker-inline').each(function () {
			var $this = $(this);

			var selectedWeek = $this.data('selectedweek');
			var firstDayWeek = $this.data('firstdayweek');
			var name = $this.data('name');
			var linkbit = $this.data('linkbit');
			var startdate = $this.data('startdate');
			var enddate = $this.data('enddate');
			var mode = $this.data('mode');

			var highlightWeek = function(d) {
				if (selectedWeek == '') {
					return [true,''];
				}

				var dayOfWeek = d.getUTCDay();
				var weekNumber = $.datepicker.iso8601Week(d);
				var currentMonth = d.getUTCMonth() + 1;
				// special handling of first and last week of a year
				weekNumber = (weekNumber == 1) ? ((currentMonth == 12) ? 53 : 1) : ((weekNumber >= 51) ? ((currentMonth == 1) ? 0 : weekNumber) : weekNumber);

				if (firstDayWeek == 0  && dayOfWeek == 6) {
					weekNumber += 1;
				}
				
				if (selectedWeek == weekNumber) {
					return [true,'week-highlight'];
				}
				return [true,''];
			}

			var loadDatePickerInline = function() {
				var done_loading = false;

				$("#"+name).datepicker( {
					onChangeMonthYear: function(year, month, inst) {
						if(inst.onChangeToday) {
							day = inst.selectedDay;
						} else {
							day = 1;
							var d_temp = new Date(year, month-1, 1);
							if (mode == 'week') {
								day = 7 - d_temp.getUTCDay() + firstDayWeek;
							} else {
								day = 1;
							}
						}
						if (done_loading) {
							document.location.href = linkbit.replace('%s', year+'-'+month+'-'+day+'/'+mode);
						}
					},
					onSelect: function(date) {
						// jump to the new page
						document.location.href = linkbit.replace('%s', date.substring(0,10)+'/'+mode);
					},
					nextText: '&#xBB;',
					prevText: '&#xAB;',
					dateFormat: "yy-mm-dd",
					defaultDate: startdate+' - '+enddate,
					firstDay: firstDayWeek,
					beforeShowDay: highlightWeek,
					showOtherMonths: true,
					selectOtherMonths: true,
					changeMonth: true,
					changeYear: true
				});

				var start_date = $.datepicker.parseDate("yy-mm-dd", startdate);
				var end_date = $.datepicker.parseDate("yy-mm-dd", enddate);
				// not sure why this is necessary, but it seems to be
				if (mode == "month") {
					end_date += 1;
				}

				$("#"+name).datepicker("setDate", start_date, end_date);
				done_loading = true;
			};
			
			if ($("#"+name).length) {
				var deps = ['jquery-ui', 'jquery-ui/datepicker'];
				if (elgg.get_language() != 'en') {
					deps.push('jquery-ui/i18n/datepicker-'+ elgg.get_language() + '.min');
				}
				require(deps, loadDatePickerInline);
			}
		});
	}

	return init();
});