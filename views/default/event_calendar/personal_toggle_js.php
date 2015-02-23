<?php

// TODO: put the rest into a JS function

?>

<script type="text/javascript">
function event_calendar_personal_toggle(event_id, user_id) {
	elgg.action('event_calendar/toggle_personal_calendar', {
		data: {
			user_id: user_id,
			event_id: event_id,
			other: true
		},
		success: function(res) {
			$('#event_calendar_user_data_'+user_id).html(res);
		}
	});
}
</script>
