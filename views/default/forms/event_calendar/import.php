<?php

echo elgg_format_element('div', ['class' => 'mlb'], elgg_format_element('h3', [], elgg_echo('event_calendar:import:settings')));

// import into which calendar
$options_values = [];

if (event_calendar_can_add()) {
	$options_values[0] = elgg_echo('event_calendar:site_calendar');
}

$groups = elgg_get_logged_in_user_entity()->getGroups(['limit' => false]);
if ($groups) {
	foreach ($groups as $group) {
		if (event_calendar_can_add($group->guid)) {
			$options_values[$group->guid] = elgg_echo('group') . ': ' . $group->name;
		}
	}
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('event_calendar:import:type'),
	'name' => 'container_guid',
	'value' => $vars['group_guid'],
	'options_values' => $options_values,
]);

echo elgg_view_field([
	'#type' => 'file',
	'#label' => elgg_echo('event_calendar:file:upload'),
	'name' => 'ical_file',
]);

echo '<div class="mbm"><label for="timezone">' . elgg_echo('event_calendar:timezone') . '</label><br>';
echo '<select name="timezone">';
$timezone_identifiers = DateTimeZone::listIdentifiers();
foreach($timezone_identifiers as $value ) {
	if (preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $value ) ) {
		$ex = explode("/", $value);//obtain continent,city
		if ($continent != $ex[0]) {
			if ($continent != "") {
				echo '</optgroup>';
			}
			echo '<optgroup label="'.$ex[0].'">';
		}

		$continent = array_shift($ex);
		$city = implode('/', $ex);

		echo '<option value="'.$value.'"'; if (date_default_timezone_get() == $value) echo " selected=\"yes\" "; echo ">".$city.'</option>';
	}
}
echo '</optgroup></select>';
echo '<br>' . elgg_view('output/longtext', ['value' => elgg_echo('event_calendar:timezone:help'), 'class' => 'elgg-subtext mtn']);
echo '</div>';

echo elgg_view_field([
	'#type' => 'access',
	'#label' => elgg_echo('event_calendar:import:access'),
	'name' => 'access_id',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('event_calendar:import'),
]);

elgg_set_form_footer($footer);
