<?php

class EventCalendar extends ElggObject {

	const SUBTYPE = 'event_calendar';

	protected function initializeAttributes() {
		parent::initializeAttributes();

		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
