<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE!='BE')	{
	require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mbl_newsevent').'Classes/class.mbl_newsevent.php');
}

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'tx_mblnewsevent'; 

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraCodesHook'][] = 'tx_mblnewsevent'; 

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['selectConfHook'][] = 'tx_mblnewsevent';

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraGlobalMarkerHook'][] = 'tx_mblnewsevent';
?>