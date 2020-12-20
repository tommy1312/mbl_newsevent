<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'News Event',
	'description' => 'Adds event date/time, location, price and registration info to news (tt_news). Event information can be downloaded to calendars through an iCalendar (.ics) feed. Requires some TypoScript setup: Please read the manual!',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '9.5.3',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Mathias Bolt Lesniak / tommy1312',
	'author_email' => 'mathias@lilio.com',
	'author_company' => 'LiliO Design',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '9.5.0-9.5.99',
			'php' => '5.6.0-7.4.99',
			'typo3' => '8.7.0-9.5.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>