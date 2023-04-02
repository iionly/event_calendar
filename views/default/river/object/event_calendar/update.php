<?php

$item = elgg_extract('item', $vars);
if (!($item instanceof ElggRiverItem)) {
	return;
}

$object = $item->getObjectEntity();
if (!($object instanceof EventCalendar)) {
	return;
}

$excerpt = strip_tags($object->description);
$excerpt = elgg_get_excerpt($excerpt);

echo elgg_view('river/elements/layout', [
	'item' => $item,
	'message' => $excerpt,
]);
