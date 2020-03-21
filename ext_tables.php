<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

$tempColumns = Array (
	"tx_mblnewsevent_isevent" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_isevent",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_mblnewsevent_from" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_from",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "date",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_mblnewsevent_fromtime" => Array (		
		"exclude" => 1,	
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_fromtime",
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "20",
			"eval" => "time",
			"default" => "0",
			"checkbox" => "0"
		)
	),
	"tx_mblnewsevent_to" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_to",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "date",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_mblnewsevent_totime" => Array (		
		"exclude" => 1,	
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_totime",
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "20",
			"eval" => "time",
			"default" => "0",
			"checkbox" => "0"
		)
	),
	"tx_mblnewsevent_hasregistration" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_hasregistration",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_mblnewsevent_regfrom" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_regfrom",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "date",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_mblnewsevent_regfromtime" => Array (		
		"exclude" => 1,	
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_regfromtime",
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "20",
			"eval" => "time",
			"default" => "0",
			"checkbox" => "0"
		)
	),
	"tx_mblnewsevent_regto" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_regto",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "date",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_mblnewsevent_regtotime" => Array (		
		"exclude" => 1,	
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_regtotime",
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "20",
			"eval" => "time",
			"default" => "0",
			"checkbox" => "0"
		)
	),
	'tx_mblnewsevent_regurl' => Array (
		'l10n_mode' => 'mergeIfNotBlank',
		'label' => 'LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_regurl',
		'config' => Array (
			'type' => 'input',
			'size' => '40',
			'max' => '256',
			'wizards' => Array(
				'_PADDING' => 2,
				'link' => Array(
					'type' => 'popup',
					'title' => 'Link',
					'icon' => 'link_popup.gif',
					'module' => array(
						'name' => 'browse_links',
						'urlParameters' => array(
							'mode' => 'wizard',
						)
					),
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				)
			)
		)
	),
	'tx_mblnewsevent_registrationmax' => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_registrationmax",
		"config" => Array (
			"type" => "input",
			"size" => "4",
			"eval" => "int",
		)
	),
	"tx_mblnewsevent_where" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_where",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_mblnewsevent_organizer" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_organizer",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "fe_users,be_users,tt_address",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,	
			//"MM" => "tt_news_tx_mblfenewsadd_feuser_mm",
		)
	),
	'tx_mblnewsevent_price' => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_price",		
		"config" => Array (
			"type" => "input",
			"size" => "6",
			"max" => "20",
			"eval" => "double2",
			"default" => "0.00"
		)
	),
	'tx_mblnewsevent_pricenote' => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mbl_newsevent/Resources/locallang_db.php:tt_news.tx_mblnewsevent_pricenote",		
		"config" => Array (
			"type" => "input",
			"size" => "30",
			"max" => "255",
		)
	),
);

if(is_array($confArr) && $confArr['multiLineWhere']) {
	$tempColumns['tx_mblnewsevent_where']['config']['type'] = 'text';
	$tempColumns['tx_mblnewsevent_where']['config']['cols'] = 30;
	$tempColumns['tx_mblnewsevent_where']['config']['rows'] = 4;
}


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_news",$tempColumns,1);

/*
Thanks to Peter Klein!
*/
$TCA['tt_news']['palettes']['tx_mblnewsevent_from_palette'] = array('showitem' => 'tx_mblnewsevent_from,tx_mblnewsevent_fromtime','canNotCollapse' => 1);
$TCA['tt_news']['palettes']['tx_mblnewsevent_to_palette'] = array('showitem' => 'tx_mblnewsevent_to,tx_mblnewsevent_totime','canNotCollapse' => 1);
$TCA['tt_news']['palettes']['tx_mblnewsevent_regfrom_palette'] = array('showitem' => 'tx_mblnewsevent_regfrom,tx_mblnewsevent_regfromtime','canNotCollapse' => 1);
$TCA['tt_news']['palettes']['tx_mblnewsevent_regto_palette'] = array('showitem' => 'tx_mblnewsevent_regto,tx_mblnewsevent_regtotime','canNotCollapse' => 1);
$TCA['tt_news']['palettes']['tx_mblnewsevent_price_palette'] = array('showitem' => 'tx_mblnewsevent_pricenote','canNotCollapse' => 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("tt_news",",--div--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:eventEditSection,tx_mblnewsevent_isevent;;;;1-1-1, --palette--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:tt_news.tx_mblnewsevent_fromlabel:;tx_mblnewsevent_from_palette;;,--palette--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:tt_news.tx_mblnewsevent_tolabel:;tx_mblnewsevent_to_palette;;, tx_mblnewsevent_hasregistration;;;;2-2-2, --palette--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:tt_news.tx_mblnewsevent_regfromlabel:;tx_mblnewsevent_regfrom_palette;;,--palette--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:tt_news.tx_mblnewsevent_regtolabel:;tx_mblnewsevent_regto_palette;;, tx_mblnewsevent_registrationmax, tx_mblnewsevent_regurl, tx_mblnewsevent_where;;;;3-3-3, tx_mblnewsevent_organizer;;;;4-4-4,tx_mblnewsevent_price;;;;5-5-5,--palette--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:tt_news.tx_mblnewsevent_pricenote:;tx_mblnewsevent_price_palette;;,;;;;6-6-6,--div--;LLL:EXT:mbl_newsevent/Resources/locallang_db.xml:accessSection");

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/standard/','News Event');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/ics/','News Event iCalendar (.ics) feed (type=101)');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/singleics/','Single News Event iCalendar (.ics) (type=102)');


if (TYPO3_MODE=='BE')	{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_FUTURE', 'EVENT_FUTURE');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_PAST', 'EVENT_PAST');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LATEST_EVENT_PAST', 'LATEST_EVENT_PAST');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LATEST_EVENT_FUTURE', 'LATEST_EVENT_FUTURE');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_CURRENT', 'EVENT_CURRENT');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LATEST_EVENT_CURRENT', 'LATEST_EVENT_CURRENT');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_REGISTERABLE', 'EVENT_REGISTERABLE');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('LATEST_EVENT_REGISTERABLE', 'LATEST_EVENT_REGISTERABLE');
	
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['order_by'][] = array('Event start', '(tx_mblnewsevent_from + tx_mblnewsevent_fromtime)');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['order_by'][] = array('Event end', '(tt_news.tx_mblnewsevent_to + tt_news.tx_mblnewsevent_totime)');
	//$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_SELECTOR', 'EVENT_SELECTOR');
	//require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mbl_newsevent').'class.mbl_newsevent.php');
}

?>