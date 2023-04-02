<?php

$event_guid = get_input('guid', false);

if (!$event_guid) {
	return true;
}

$event = get_entity($event_guid);

if (!($event instanceof EventCalendar)) {
	return true;
}

$owner_icon = elgg_view_entity_icon($event->getOwnerEntity(), 'tiny');

$info = '';
$event_items = event_calendar_get_formatted_full_items($event);

foreach($event_items as $item) {
	$value = $item->value;
	if (!empty($value)) {
		$body = elgg_format_element('label', [], $item->title .": ");
		$info .= elgg_format_element('div', ['class' => 'mts'], $body . $item->value);
	}
}

if ($event->description) {
	$info .= elgg_format_element('div', ['class' => 'mts'], $event->description);
}

$params = [
	'entity' => $event,
	'title' => false,
	'metadata' => '',
];
$list_body = elgg_view('object/elements/summary', $params);

$summary = elgg_view_image_block($owner_icon, $list_body, $params);

$title = elgg_view('output/url', [
	'href' => $event->getURL(),
	'text' => elgg_view_title($event->title),
]);

echo '<div style="max-width:320px;">' . $title . $summary . $info . '</div>';
echo '<div align="center" class="mtm mbs">' . elgg_view('output/url', [
	'href' => $event->getURL() . '#event_calendar-comments',
	'text' => elgg_echo('generic_comments:add'),
	'is_trusted' => true,
	'class' => 'elgg-button elgg-button-action'
]) . '</div>';
