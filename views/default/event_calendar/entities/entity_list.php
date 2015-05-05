<?php

/**
 * View a list of entities
 *
 * @package Elgg
 * @author Curverider Ltd <info@elgg.com>
 * @link http://elgg.com/
 *
 */

$context = $vars['context'];
$offset = $vars['offset'];
$entities = $vars['entities'];
$limit = $vars['limit'];
$count = $vars['count'];
$base_url = $vars['base_url'];
$context = $vars['context'];
$viewtype = $vars['viewtype'];
$pagination = $vars['pagination'];
$full_view = $vars['full_view'];

$html = "";
$nav = "";

if (isset($vars['list_type_toggle'])) {
	$list_type_toggle = $vars['list_type_toggle'];
} else {
	$list_type_toggle = true;
}

if ($context == "search" && $count > 0 && $list_type_toggle) {
	$nav .= elgg_view('navigation/viewtype', array(
		'base_url' => $base_url,
		'offset' => $offset,
		'count' => $count,
		'viewtype' => $viewtype,
	));
}

if ($pagination) {
	$nav .= elgg_view('navigation/pagination', array(
		'base_url' => $base_url,
		'offset' => $offset,
		'count' => $count,
		'limit' => $limit,
	));
}

$html .= $nav;

if (is_array($entities) && sizeof($entities) > 0) {
	foreach($entities as $entity) {
		$html .= elgg_view_entity($entity, $full_view);
	}
}

if ($count) {
	$html .= $nav;
}

echo $html;
