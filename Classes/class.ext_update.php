<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Mathias Bolt Lesniak, 
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


class ext_update {
	public function main() {
		if (!\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('do_update')) {
			$onClick = "document.location='".\TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript(array('do_update' => 1))."'; return false;";
			if ($this->hasDateAndTimeCombined()) {
				$content = '<b>'.$this->hasDateAndTimeCombined().' news articles have date and time combined in one field, and should be updated.</b><br /><br />';
				$performActions = TRUE;
			}
			
			if($performActions) {
				$content .= '<br /><br /><br/><b>Do you want to perform the updates now?</b><br />
					To be on the safe side, please back up database data before continuing.
					<br/><br/><form action=""><input type="submit" value="Update now" onclick="'.htmlspecialchars($onClick).'"></form>';
			}
			return $content;
		} else {
			if ($this->hasDateAndTimeCombined()) {
				$rows = $this->separateDateAndTime();
				$content .= '<b>Date and time separated in ' . intval($rows) . ' articles.</b><br /><br />';
			}
			
			return $content;
		}
	}
	
	
	public function hasDateAndTimeCombined() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news', 'tx_mblnewsevent_from % 86400 != 0 || tx_mblnewsevent_to % 86400 != 0');
		if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
			return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}
		return 0;
	}
	
	public function separateDateAndTime() {
		$res = $GLOBALS['TYPO3_DB']->sql_query('UPDATE tt_news SET tx_mblnewsevent_fromtime = tx_mblnewsevent_from % 86400, tx_mblnewsevent_from = tx_mblnewsevent_from DIV 86400 * 86400, tx_mblnewsevent_totime = tx_mblnewsevent_to % 86400, tx_mblnewsevent_to = tx_mblnewsevent_to DIV 86400 * 86400 WHERE tx_mblnewsevent_from % 86400 != 0 || tx_mblnewsevent_to % 86400 != 0');
		
		return  $GLOBALS['TYPO3_DB']->sql_affected_rows($res);
	}
	
	public function access() {
		return (bool) ($this->hasDateAndTimeCombined());
	}
}