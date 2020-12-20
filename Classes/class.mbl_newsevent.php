<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Mathias Bolt Lesniak <mathias@lilio.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Adds event capabilities to tt_news.
 *
 * @author  Mathias Bolt Lesniak <mathias@lilio.com>
 */
//require_once(PATH_tslib."class.tslib_pibase.php");

use RG\TtNews\Database\Database;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class tx_mblnewsevent extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
  var $prefixId = "tx_mblnewsevent";    // Same as class name
  var $scriptRelPath = "class.mblnewsevent.php";  // Path to this script relative to the extension dir.
  var $extKey = "mbl_newsevent";  // The extension key.

  function extraGlobalMarkerProcessor($tt_news, $markerArray) {

    $this->conf = &$tt_news->conf['mbl_newsevent.'];

    if(
      $this->conf['currentCode'] == 'EVENT_FUTURE' ||
      $this->conf['currentCode'] == 'EVENT_PAST' ||
      $this->conf['currentCode'] == 'LATEST_EVENT_FUTURE' ||
      $this->conf['currentCode'] == 'LATEST_EVENT_PAST'
    ) {
      $markerArray['###EVENT_SELECTOR_LIST###'] = $this->_dateSelectorMenu($tt_news);
    } else {
      $markerArray['###EVENT_SELECTOR_LIST###'] = '';
    }

    return $markerArray;
  }

  function _dateSelectorMenu($tt_news) {
	$templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
    $db = Database::getInstance();
    $this->pi_loadLL('EXT:mbl_newsevent/Resources/locallang.xml');
    $this->cObj = $tt_news->cObj;

    $tt_news->arcExclusive = 1;
    $selectConf = $tt_news->getSelectConf('', 1);
    // Finding maximum and minimum values:
    //$selectConf['where'] = '1=1';
    //$selectConf = $this->processSelectConfHook($tt_news, $selectConf);
    $selectConf['selectFields'] = 'max(tt_news.tx_mblnewsevent_from) as maxval, min(tt_news.tx_mblnewsevent_from) as minval';
    //echo $tt_news->getQuery('tt_news', $selectConf);
    $res = $tt_news->exec_getQuery('tt_news', $selectConf);

    $row = $db->sql_fetch_assoc($res);
    //var_dump($tt_news);
    if ($row['minval'] || $row['maxval']) {
      // if ($row['minval']) {
      $dateArr = array();
      $arcMode = $this->conf['dateSelMode'];
      //echo $arcMode;
      $c = 0;
      do {
        switch ($arcMode) {
          case 'month':
          $theDate = mktime (0, 0, 0, date('m', $row['minval']) + $c, 1, date('Y', $row['minval']));
          break;
          case 'quarter':
          $theDate = mktime (0, 0, 0, floor(date('m', $row['minval']) / 3) + 1 + (3 * $c), 1, date('Y', $row['minval']));
          break;
          case 'year':
          $theDate = mktime (0, 0, 0, 1, 1, date('Y', $row['minval']) + $c);
          break;
        }
        $dateArr[] = $theDate;
        $c++;
        if ($c > 1000) break;
      }
      while ($theDate < $row['maxval']);
      //echo "#" . $row['minval'] . "-" . $row['maxval'] . "#";
      reset($dateArr);
      $periodAccum = array();
      //debug($dateArr);
      $selectConf2['where'] = $selectConf['where'];
      while (list($k, $v) = each($dateArr)) {
        if (!isset($dateArr[$k + 1])) {
          break;
        }

        $periodInfo = array();
        $periodInfo['event_start'] = $dateArr[$k];
        $periodInfo['event_stop'] = $dateArr[$k + 1]-1;
        $periodInfo['event_HRstart'] = date('d-m-Y', $periodInfo['event_start']);
        $periodInfo['event_HRstop'] = date('d-m-Y', $periodInfo['event_stop']);
        $periodInfo['event_quarter'] = floor(date('m', $dateArr[$k]) / 3) + 1;
        // execute a query to count the archive periods
        $selectConf['selectFields'] = 'count(distinct(uid))';
        $selectConf['where'] = $selectConf2['where'] . ' AND tt_news.tx_mblnewsevent_from >= ' . $periodInfo['event_start'] . ' AND tt_news.tx_mblnewsevent_from < ' . $periodInfo['event_stop'];
        //debug($periodInfo);
        $res = $tt_news->exec_getQuery('tt_news', $selectConf);

        $row = $db->sql_fetch_row($res);
        $periodInfo['event_count'] = $row[0];

        if (!$this->conf['dateSelMenuNoEmpty'] || $periodInfo['event_count']) {
          $periodAccum[] = $periodInfo;
        }
      }
      //debug($periodAccum);

	  $path = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['templateFile']);
      if ($path !== null && file_exists($path)) {
          $templateFile = file_get_contents($path);
      }

      // get template subpart
      $t['total'] = $templateService->getSubpart(
        $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
        '###DATESEL_LIST###' //Marker string, eg. "###CONTENT_PART###"
      );
      $t['item'] = $templateService->getSubpart(
        $t['total'], //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
        '###ITEM###' //Marker string, eg. "###CONTENT_PART###"
      );
      $m['total'] = $templateService->getSubpart(
        $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
        '###DATESEL_MENU###' //Marker string, eg. "###CONTENT_PART###"
      );
      $m['item'] = $templateService->getSubpart(
        $t['total'], //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
        '###ITEM###' //Marker string, eg. "###CONTENT_PART###"
      );
      $cc = 0;

      $veryLocal_cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
      // reverse amenu order if 'reverseAMenu' is given
      if ($tt_news->conf['reverseAMenu']) {
        arsort($periodAccum);
      }

      $archiveLink = $this->conf['dateSelTypoLink.']['parameter'];
      $this->conf['parent.']['addParams'] = $this->conf['dateSelTypoLink.']['addParams'];
      reset($periodAccum);
      $itemsOutArr = array();

      while(list(, $pArr) = each($periodAccum)) {


        // Print Item Title
        $wrappedSubpartArray = array();


        if (!$this->conf['disableCategoriesInDateSelLinks']) {
          if ($tt_news->config['catSelection'] && $this->config['dateSelMenuWithCatSelector']) {
            // use the catSelection from GPvars only if 'amenuWithCatSelector' is given.
            $amenuLinkCat = $tt_news->config['catSelection'];
          } else {
            $amenuLinkCat = $tt_news->catExclusive;
          }
        }

        $hiddenItemsArr = array();
        if ($tt_news->conf['useHRDates']) {
          $year  = date('Y',$pArr['event_start']);
          $month = date('m',$pArr['event_start']);
          if ($arcMode == 'year') {
            $archLinkArr = $tt_news->pi_linkTP_keepPIvars(
              '|',
              array(
                'cat' => ($amenuLinkCat?$amenuLinkCat:null),
                'event_year' => $year),
              $tt_news->allowCaching,
              1,
              ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)
            );
            $hiddenItemsArr = array(
              'cat' => ($amenuLinkCat?$amenuLinkCat:null),
              'event_year' => $year
            );
          } else {
            $archLinkArr = $tt_news->pi_linkTP_keepPIvars(
              '|',
              array(
                'cat' => ($amenuLinkCat?$amenuLinkCat:null),
                'event_year' => $year,
                'event_month' => $month),
              $tt_news->allowCaching,
              1,
              ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)
            );
            $hiddenItemsArr = array(
              'cat' => ($amenuLinkCat?$amenuLinkCat:null),
              'event_year' => $year,
              'event_month' => $month
            );
          }
          $wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $archLinkArr);
        } else {
          $wrappedSubpartArray['###LINK_ITEM###'] = explode(
            '|',
            $tt_news->pi_linkTP_keepPIvars(
              '|',
              array(
                'cat' => ($amenuLinkCat?$amenuLinkCat:null),
                'event_pS' => $pArr['event_start'],
                'event_pL' => ($pArr['event_stop'] - $pArr['event_start']),
                ),
              $tt_news->allowCaching,
              1,
              ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)
            )
          );

        }

        $markerArray = array();


        $veryLocal_cObj->start($pArr, '');
        $markerArray['###EVENT_PERIOD_NAME###'] = $veryLocal_cObj->cObjGetSingle(
          $this->conf['dateSelTitleCObject'],
          $this->conf['dateSelTitleCObject.'],
          'dateSelTitle'
        );
        $markerArray['###EVENT_PERIOD_COUNT###'] = $pArr['event_count'];
        $markerArray['###EVENT_PERIOD_COUNT_LABEL###'] = htmlspecialchars($this->pi_getLL('dateSelItemItems'));

        // fill the generated data to an array to pass it to a userfuction as a single variable
        $itemsOutArr[] = array(
          'html' => $templateService->substituteMarkerArrayCached(
            $t['item'],
            $markerArray,
            array(),
            $wrappedSubpartArray
          ),
          'data' => $pArr
        );
        //debug($itemsOutArr);
        $cc++;
      }
      // Pass to user defined function
      /*if ($tt_news->conf['newsAmenuUserFunc']) {
        $itemsOutArr = $tt_news->userProcess('newsAmenuUserFunc', $itemsOutArr);
      }*/

      foreach ($itemsOutArr as $itemHtml) {
        $tmpItemsArr[] = $itemHtml['html'];
      }

      if (is_array($tmpItemsArr)) {
        $itemsOut = implode('', $tmpItemsArr);
      }

      // Reset:
      $subpartArray = array();
      $wrappedSubpartArray = array();
      $markerArray = array();
      $markerArray['###DATESEL_HEADER###'] = $tt_news->local_cObj->stdWrap(
        $tt_news->pi_getLL('dateselHeader'),
        $tt_news->conf['dateselHeader_stdWrap.']
      );
      // Set content
      $subpartArray['###ITEM###'] = $itemsOut;
      $content = $templateService->substituteMarkerArrayCached(
        $t['total'],
        $markerArray,
        $subpartArray,
        $wrappedSubpartArray
      );
    } else {
      $content = '';
    }

    return $content;
  }


  function _convertDates($tt_news) {
    if (!$tt_news->piVars['event_year'] && $tt_news->piVars['event_pS']) {
      $tt_news->piVars['event_year'] = date('Y',$tt_news->piVars['event_pS']);
    }
    if (!$tt_news->piVars['event_month'] && $tt_news->piVars['event_pS']) {
      $tt_news->piVars['event_month'] = date('m',$tt_news->piVars['event_pS']);
    }
    if (!$tt_news->piVars['event_day'] && $tt_news->piVars['event_pS']) {
      $tt_news->piVars['event_day'] = date('j',$tt_news->piVars['event_pS']);
    }
    if ($tt_news->piVars['event_year'] || $tt_news->piVars['event_month'] || $tt_news->piVars['event_day']) {
      $mon = ($tt_news->piVars['event_month'] ? $tt_news->piVars['event_month'] : 1);
      $day = ($tt_news->piVars['event_day']   ? $tt_news->piVars['event_day']   : 1);

      $tt_news->piVars['event_pS'] = mktime (0, 0, 0, $mon, $day, $tt_news->piVars['event_year']);
      switch ($this->conf['dateSelMode']) {
        case 'month':
          $tt_news->piVars['event_pL'] = mktime (0, 0, 0, $mon+1, 1, $tt_news->piVars['event_year'])-$tt_news->piVars['event_pS']-1;
        break;
        case 'quarter':
          $tt_news->piVars['event_pL'] = mktime (0, 0, 0, $mon+3, 1, $tt_news->piVars['event_year'])-$tt_news->piVars['event_pS']-1;
        break;
        case 'year':
          $tt_news->piVars['event_pL'] = mktime (0, 0, 0, 1, 1, $tt_news->piVars['event_year']+1)-$tt_news->piVars['event_pS']-1;
        break;
      }
    }
  }


  function processSelectConfHook($tt_news, $selectConf) {
    $this->conf = &$tt_news->conf['mbl_newsevent.'];

    if ($tt_news->conf['useHRDates']) {
      $this->_convertDates($tt_news);
    }

    $execTime = $GLOBALS['SIM_EXEC_TIME']/*+$this->getAdjustTime()*/;

    $eventview = false;

    switch($this->conf['currentCode']) {
      case 'LATEST_EVENT_FUTURE':
      case 'EVENT_FUTURE':
        if($this->conf['displayEventUntilEnd']) {
          $selectConf['where'] .= ' AND (((tt_news.tx_mblnewsevent_to + tt_news.tx_mblnewsevent_totime) >= ' . $execTime .' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) <= ' . $execTime .') OR (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) >= ' . $execTime . ') ';
        } else {
          $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) >= ' . $execTime;
        }
        $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1';
        $eventview = true;
        break;
      case 'LATEST_EVENT_PAST':
      case 'EVENT_PAST':
        if($this->conf['displayEventUntilEnd']) {
          $selectConf['where'] .= ' AND (((tt_news.tx_mblnewsevent_to + tt_news.tx_mblnewsevent_totime) > ' . $execTime .' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . $execTime .') OR (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . $execTime . ') ';
        } else {
          $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . $execTime;
        }
        $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1';
        $eventview = true;
        break;
      case 'EVENT_CURRENT':
      case 'LATEST_EVENT_CURRENT':
        $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_to + tt_news.tx_mblnewsevent_totime) > ' . $execTime . ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . $execTime;
        $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1';
        $eventview = true;
        break;
      case 'EVENT_REGISTERABLE':
      case 'LATEST_EVENT_REGISTERABLE':
        if($this->conf['registerAvailableUntilEnd']) {
          $selectConf['where'] .= ' AND
            IF(tt_news.tx_mblnewsevent_regfrom=0,
              tt_news.datetime < ' . $execTime . ',
              (tt_news.tx_mblnewsevent_regfrom + tt_news.tx_mblnewsevent_regfromtime) < ' . $execTime . ')
            AND
            IF(tt_news.tx_mblnewsevent_regto=0,
              (tt_news.tx_mblnewsevent_to + tt_news.tx_mblnewsevent_totime) > ' . $execTime . ',
              (tt_news.tx_mblnewsevent_regto + tt_news.tx_mblnewsevent_regtotime) > ' . $execTime . ')';
        } else {
          $selectConf['where'] .= ' AND
            IF(tt_news.tx_mblnewsevent_regfrom=0,
              tt_news.datetime < ' . $execTime . ',
              (tt_news.tx_mblnewsevent_regfrom + tt_news.tx_mblnewsevent_regfromtime) < ' . $execTime . ')
            AND
            IF(tt_news.tx_mblnewsevent_regto=0,
              (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) > ' . $execTime . ',
              (tt_news.tx_mblnewsevent_regto + tt_news.tx_mblnewsevent_regtotime) > ' . $execTime . ')';
        }
        $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1 AND tt_news.tx_mblnewsevent_hasregistration = 1';
        $eventview = true;
        break;
    }

    if($eventview == true) {
      if ($tt_news->arcExclusive != 1 && intval($tt_news->piVars['event_pS']) && $tt_news->piVars['event_pS'] != 0) {
        // select news from a certain event period
        $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) >= ' . intval($tt_news->piVars['event_pS']);
        if (intval($tt_news->piVars['event_pL'])) {
          $pL = intval($tt_news->piVars['event_pL']);
            //selecting news for a certain day only
          if(intval($tt_news->piVars['day'])) {
            $pL = 86400; // = 24h, as pS always starts at the beginning of a day (00:00:00)
          }

          $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . (intval($tt_news->piVars['event_pS']) + $pL);
        }
      }
    }

    if($this->conf['hideEvents']) {
      $selectConf['where'] .= ' AND tx_mblnewsevent_isevent = 0 ';
    }

    return $selectConf;
  }


  function extraCodesProcessor($tt_news) {

    $this->conf = &$tt_news->conf['mbl_newsevent.'];

    //Store the code
    $this->conf['currentCode'] = $tt_news->theCode;

    switch($tt_news->theCode) {
      case 'EVENT_FUTURE':
        //Pretend that this is just a normal list
        $tt_news->theCode = 'LIST';
        //Run list function
        $content = $tt_news->displayList();
        break;
      case 'EVENT_PAST':
        $tt_news->theCode = 'LIST';
        $content = $tt_news->displayList();
        break;
      case 'LATEST_EVENT_FUTURE':
        $tt_news->theCode = 'LATEST';
        $content = $tt_news->displayList();
        break;
      case 'LATEST_EVENT_PAST':
        $tt_news->theCode = 'LATEST';
        $content = $tt_news->displayList();
        break;
      case 'ICS':
        $content = $this->icsHandler($tt_news);
        break;
      case 'SINGLE_ICS':
        $content = $this->icsHandler($tt_news);
        break;
      case 'EVENT_CURRENT':
        $tt_news->theCode = 'LIST';
        $content = $tt_news->displayList();
        break;
      case 'LATEST_EVENT_CURRENT':
        $tt_news->theCode = 'LATEST';
        $content = $tt_news->displayList();
        break;
      case 'EVENT_REGISTERABLE':
        $tt_news->theCode = 'LIST';
        $content = $tt_news->displayList();
        break;
      case 'LATEST_EVENT_REGISTERABLE':
        $tt_news->theCode = 'LATEST';
        $content = $tt_news->displayList();
        break;
    }

    //Remove the stored code
    $this->conf['currentCode'] = $tt_news->theCode;

    return $content;
  }


  function icsHandler($tt_news) {
	$templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
    $db = Database::getInstance();
    $this->cObj = &$tt_news->cObj;

    if($tt_news->conf['defaultCode'] == 'SINGLE_ICS') {
      $confPrefix = 'singleics.';
      $getSinlgeEvent = TRUE;
    } else {
      $confPrefix = 'ics.';
      $getSinlgeEvent = FALSE;
    }

    //Get the template file
    //$mainTemplate = $this->cObj->FileResource($this->conf[$confPrefix]['templateFile']);
	$path = $GLOBALS['TSFE']->tmpl->getFileName($this->conf[$confPrefix]['templateFile']);
    if ($path !== null && file_exists($path)) {
        $mainTemplate = file_get_contents($path);
    }
	
    $headerTemplate = $templateService->getSubpart(
      $mainTemplate, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
      '###HEADER###' //Marker string, eg. "###CONTENT_PART###"
    );

    $elementTemplate = $templateService->getSubpart(
      $mainTemplate, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
      '###ELEMENT###' //Marker string, eg. "###CONTENT_PART###"
    );

    $organizerTemplate = $templateService->getSubpart(
      $elementTemplate, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
      '###ORGANIZER###' //Marker string, eg. "###CONTENT_PART###"
    );

    //Set header
    $markerArray = array();

    //Only set the calendar name if we're NOT getting single event.
    if($getSinlgeEvent) {
      $headerTemplate = $templateService->substituteSubpart(
          $headerTemplate, //The content stream, typically HTML template content.
          '###CALNAME###', //The marker string, typically on the form "###[the marker string]###"
          '' //The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
      );
    } else {
      $calnameTemplate = $templateService->getSubpart(
        $headerTemplate, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
        '###CALNAME###' //Marker string, eg. "###CONTENT_PART###"
      );
      $calnameArray = array('###CALENDAR_NAME###' => $this->conf[$confPrefix]['icsName']);
      $headerTemplate = $templateService->substituteSubpart(
        $headerTemplate, //The content stream, typically HTML template content.
        '###CALNAME###', //The marker string, typically on the form "###[the marker string]###"
        $templateService->substituteMarkerArray(
          $calnameTemplate, //The content stream, typically HTML template content.
          $calnameArray //Regular marker-array where the 'keys' are substituted in $content with their values
        ) //The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
      );
    }

    $headerTemplate = $templateService->substituteMarkerArrayCached(
      $headerTemplate, //The content stream, typically HTML template content.
      $markerArray //Regular marker-array where the 'keys' are substituted in $content with their values
    );
    $mainTemplate = $templateService->substituteSubpart(
      $mainTemplate, //The content stream, typically HTML template content.
      '###HEADER###', //The marker string, typically on the form "###[the marker string]###"
      $headerTemplate //The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
    );

    $selectConf = $tt_news->getSelectConf('', 1);

    //Get single event
    if($getSinlgeEvent) {
      $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1 AND tt_news.uid = ' . (int) $tt_news->piVars['tt_news'] . ' ';
    //Get all events
    } else {
      $selectConf['where'] .= ' AND tt_news.tx_mblnewsevent_isevent = 1';
      $selectConf['where'] .= ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) > ' . strtotime($this->conf[$confPrefix]['from']) . ' AND (tt_news.tx_mblnewsevent_from + tt_news.tx_mblnewsevent_fromtime) < ' . strtotime($this->conf[$confPrefix]['to']) . ' ';
    }

    //var_dump( $tt_news->cObj->getQuery('tt_news', $selectConf)); die();
    $res = $tt_news->exec_getQuery('tt_news', $selectConf);

    $seq = 0;
    $elements = '';
    while($row = $db->sql_fetch_assoc($res /*MySQL result pointer (of SELECT query) / DBAL object*/)) {
      $markerArray = array();
      $markerArray['###SEQUENCE###'] = $seq;
      $markerArray['###START_DATE###'] = gmdate('Ymd\THis\Z', $row['tx_mblnewsevent_from']+$row['tx_mblnewsevent_fromtime']);
      // EndDate
      if ($row['tx_mblnewsevent_to'] == 0) {
        $row['tx_mblnewsevent_to'] = $row['tx_mblnewsevent_from'];
      }
      // EndTime
      if ($row['tx_mblnewsevent_totime'] == 0) {
        $row['tx_mblnewsevent_totime'] = (23*60*60)+(59*60);
      }
      $markerArray['###END_DATE###'] = gmdate('Ymd\THis\Z', $row['tx_mblnewsevent_to']+$row['tx_mblnewsevent_totime']);
      $markerArray['###CREATED_DATE###'] = gmdate('Ymd\THis\Z', $row['tstamp']);
      $markerArray['###TITLE###'] = $this->icsEscape($row['title']);
      $markerArray['###LOCATION###'] = $this->icsEscape($row['tx_mblnewsevent_where']);
      $markerArray['###BLURB###'] = $this->icsEscape($row['short']);
      $markerArray['###UID###'] = 'tt_news' . $row['uid'] . "@" . $_SERVER['HTTP_HOST'];
      if ($row['type']) { // News type article or external url
        $tt_news->local_cObj->setCurrentVal($row['type'] == 1 ? $row['page'] : $row['ext_url']);
        $markerArray['###EVENT_URL###'] = $this->icsEscape($this->cObj->typolinkWrap($tt_news->conf['pageTypoLink.']));
      } else {
        $markerArray['###EVENT_URL###'] = $this->icsEscape(
          \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
          $tt_news->pi_getPageLink(
            $tt_news->config['singlePid'],
            '',
            array('tx_ttnews'=>array(
              'tt_news'=>$row['uid'],
              'backPid'=>$tt_news->config['backPid']
            )),
            $tt_news->allowCaching,
            '',
            $tt_news->config['singlePid']
          )
        );
      }

      if($row['tx_mblnewsevent_organizer']) {
        $organizerArray = $this->_getOrganizerArray($row);
        $organizerMarkerArray = array();
        $organizerMarkerArray['###ORGANIZER_NAME###'] = $this->icsEscape($organizerArray['name']);
        $organizerMarkerArray['###ORGANIZER_EMAIL###'] = $this->icsEscape($organizerArray['email']);

        $organizerContent = $templateService->substituteMarkerArray(
          $organizerTemplate, //The content stream, typically HTML template content.
          $organizerMarkerArray //Regular marker-array where the 'keys' are substituted in $content with their values
        );
      } else {
        $organizerContent = '';
      }

      $tmpElementTemplate = $elementTemplate;

      $tmpElementTemplate = $templateService->substituteSubpart(
        $tmpElementTemplate, //The content stream, typically HTML template content.
        '###ORGANIZER###', //The marker string, typically on the form "###[the marker string]###"
        $organizerContent //The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
      );

      $elements .= $templateService->substituteMarkerArray(
        $tmpElementTemplate, //The content stream, typically HTML template content.
        $markerArray //Regular marker-array where the 'keys' are substituted in $content with their values
      );

      $seq++;
    }

    $mainTemplate = $templateService->substituteSubpart(
      $mainTemplate, //The content stream, typically HTML template content.
      '###ELEMENT###', //The marker string, typically on the form "###[the marker string]###"
      $elements //The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
    );

    return $mainTemplate;

  }


  function _getOrganizerArray($row) {
    $table = substr(
      $row['tx_mblnewsevent_organizer'],
      0,
      strrpos($row['tx_mblnewsevent_organizer'], '_')
    );
    $uid = substr(
      $row['tx_mblnewsevent_organizer'],
      strrpos($row['tx_mblnewsevent_organizer'], '_')+1
    );
    $organizer = $GLOBALS['TSFE']->sys_page->getRawRecord(
      $table, //The table name to search
      $uid //The uid to look up in $table
    );

    //Handle the different table names
    switch($table) {
      //Front end users
      case 'fe_users':
        if($organizer['first_name'] == '' && $organizer['last_name'] == '') {
          $organizerName = $organizer['name'];
        } else {
          $organizerName = $organizer['first_name'] . ' ' . $organizer['last_name'];
        }
        $organizerEmail = $organizer['email'];
        break;
      case 'be_users':
        $organizerName = ($organizer['realName'] != '')? $organizer['realName'] : $organizer['username'];
        $organizerEmail = $organizer['email'];
        break;
      case 'tt_address':
        $organizerName = $organizer['name'];
        $organizerEmail = $organizer['email'];
        break;
    }

    return array(
      'name'=>$organizerName,
      'email'=>$organizerEmail
    );
  }


  function icsEscape($s) {
    $original = array('\\', ';', ',', "\n", "\r");
    $replace = array('\\\\', '\;', '\,', "\\n", "\\r");

    return str_replace($original, $replace, $s);
  }

  function getAdjustTime() {
    $automaticAdjustTime = 0;
    if($this->conf['automaticAdjustTime']) {
      $automaticAdjustTime = (date('Z')*-1)+(date('I')*60*60)+(24*60*60);
    }
    return (int) $automaticAdjustTime+$this->conf['adjustTime'];
  }

  function getAdjustDate() {
    $automaticAdjustDate = 0;
    if($this->conf['automaticAdjustDate']) {
      $automaticAdjustDate = (date('Z')*-1)+(date('I')*60*60)+(24*60*60);
    }
    return (int) $automaticAdjustDate+$this->conf['adjustDate'];
  }

  function extraItemMarkerProcessor($parentMarkerArray, $row, $lConf, $tt_news) {
	$templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
    $db = Database::getInstance();
    $this->cObj = $tt_news->local_cObj;
    //$this->cObj->start($row, 'tt_news');

    //debug($row);
    if($row['tx_mblnewsevent_isevent']) {
      //Initiate language
      $this->conf = &$tt_news->conf['mbl_newsevent.'];

      $this->pi_setPiVarDefaults();
      $this->pi_loadLL('EXT:mbl_newsevent/Resources/locallang.xml');

      $markerArray = array();
      $markerArray['###EVENT_FROM_DATE###'] = $this->cObj->stdWrap(
        $row['tx_mblnewsevent_from']+$this->getAdjustDate(), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
        $this->conf['date_stdWrap.'] //TypoScript "stdWrap properties".
      );
      $markerArray['###EVENT_START_DATE###'] = $markerArray['###EVENT_FROM_DATE###'];
      $markerArray['###EVENT_FROM_TIME###'] = $this->cObj->stdWrap(
        $row['tx_mblnewsevent_fromtime']+$this->getAdjustTime(), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
        $this->conf['time_stdWrap.'] //TypoScript "stdWrap properties".
      );
      $markerArray['###EVENT_START_TIME###'] = $markerArray['###EVENT_FROM_TIME###'];

      $markerArray['###EVENT_TO_DATE###'] = $this->cObj->stdWrap(
        $row['tx_mblnewsevent_to']+$this->getAdjustDate(), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
        $this->conf['date_stdWrap.'] //TypoScript "stdWrap properties".
      );
      $markerArray['###EVENT_END_DATE###'] = $markerArray['###EVENT_TO_DATE###'];
      $markerArray['###EVENT_TO_TIME###'] = $this->cObj->stdWrap(
        $row['tx_mblnewsevent_totime']+$this->getAdjustTime(), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
        $this->conf['time_stdWrap.'] //TypoScript "stdWrap properties".
      );
      $markerArray['###EVENT_END_TIME###'] = $markerArray['###EVENT_TO_TIME###'];

      $markerArray['###EVENT_WHERE###'] = $this->cObj->stdWrap(
        $row['tx_mblnewsevent_where'], //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
        $this->conf['where_stdWrap.'] //TypoScript "stdWrap properties".
      );

      //Determine the actual event registration start and end times. If the registration start date is not set, the tt_news datetime is used (so that the registration is open instantly).
      if($this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration']) {
        if($row['tx_mblnewsevent_regfrom']) {
          $regFromDate = $row['tx_mblnewsevent_regfrom'];
          $regFromTime = $row['tx_mblnewsevent_regfromtime'];
        } else {
          $regFromDate = (floor($row['datetime']/86400)*86400);
          $regFromTime = ($row['datetime']%86400);
        }
        if($row['tx_mblnewsevent_regto']) {
          $regToDate = $row['tx_mblnewsevent_regto'];
          $regToTime = $row['tx_mblnewsevent_regtotime'];
        } else {
          $regToDate = $row['tx_mblnewsevent_to'];
          $regToTime = $row['tx_mblnewsevent_totime'];
        }

        if($this->conf['enableRegistrationsTracking']) {
          //registrationsTable
          if(is_array($this->conf['registrationsTable.'])) {
            $registrationTable = $this->cObj->stdWrap(
                $this->conf['registrationsTable'], //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
                $this->conf['registrationsTable.'] //TypoScript "stdWrap properties".
            );
          } else {
            $registrationTable = $this->conf['registrationsTable'];
          }

          if($registrationTable !== '') {
            //registrationsSelect
            if(is_array($this->conf['registrationsSelect.'])) {
              $registrationSelect = $this->cObj->stdWrap(
                  $this->conf['registrationsSelect'], //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
                  $this->conf['registrationsSelect.'] //TypoScript "stdWrap properties".
              );
            } else {
              $registrationSelect = $this->conf['registrationsSelect']?$this->conf['registrationsSelect']:'count(*)';
            }

            //registrationsWhere
            if(is_array($this->conf['registrationsWhere.'])) {
              $registrationWhere = $this->cObj->stdWrap(
                  $this->conf['registrationsWhere'], //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
                  $this->conf['registrationsWhere.'] //TypoScript "stdWrap properties".
              );
            } else {
              $registrationWhere = $this->conf['registrationsWhere'];
            }


            $res = $db->exec_SELECTquery(
                $registrationSelect, //List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
                $registrationTable, //Table(s) from which to select. This is what comes right after "FROM ...". Required value.
                $registrationWhere, //Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
                '', //Optional GROUP BY field(s), if none, supply blank string.
                '', //Optional ORDER BY field(s), if none, supply blank string.
                1 //Optional LIMIT value ([begin,]max), if none, supply blank string.
            );
            $regRow = $db->sql_fetch_row($res);

            $registrationsCount = (int) $regRow[0];
            $registrationsMax = (int) $row['tx_mblnewsevent_registrationmax'];
            $eventFull = ($registrationsMax > 0 && $registrationsCount >= $registrationsMax);
          }
        } else {
          $registrationsCount = 0;
          $registrationsMax = 0;
          $eventFull = FALSE;
        }
      }

      //Boolean shortcuts
      $eventOpened = ($regFromDate+$regFromTime < time());
      $eventClosed = ($regToDate+$regToTime < time());

      $regFromDate += $this->getAdjustDate();
      $regFromTime += $this->getAdjustTime();
      $regToDate += $this->getAdjustDate();
      $regToTime += $this->getAdjustTime();

      if($row['tx_mblnewsevent_regfrom'] && !$eventClosed && $this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration']) {
        $markerArray['###REGISTER_FROM_DATE###'] = $this->cObj->stdWrap(
          $regFromDate, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['date_stdWrap.'] //TypoScript "stdWrap properties".
        );
        $markerArray['###REGISTER_START_DATE###'] = $markerArray['###REGISTER_FROM_DATE###'];
        $markerArray['###REGISTER_FROM_TIME###'] = $this->cObj->stdWrap(
          $regFromTime, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['time_stdWrap.'] //TypoScript "stdWrap properties".
        );
        $markerArray['###REGISTER_START_TIME###'] = $markerArray['###REGISTER_FROM_TIME###'];
        $markerArray['###REGISTER_FROM_LABEL###'] = htmlspecialchars($this->pi_getLL('registerStartLabel'));
        $markerArray['###REGISTER_START_LABEL###'] = $markerArray['###REGISTER_FROM_LABEL###'];
      } else {
        $markerArray['###REGISTER_FROM_DATE###'] = '';
        $markerArray['###REGISTER_START_DATE###'] = '';
        $markerArray['###REGISTER_FROM_TIME###'] = '';
        $markerArray['###REGISTER_START_TIME###'] = '';
        $markerArray['###REGISTER_FROM_LABEL###'] = '';
        $markerArray['###REGISTER_START_LABEL###'] = '';
      }

      if($row['tx_mblnewsevent_regto'] && !$eventClosed && $this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration']) {
        $markerArray['###REGISTER_TO_DATE###'] = $this->cObj->stdWrap(
          $regToDate, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['date_stdWrap.'] //TypoScript "stdWrap properties".
        );
        $markerArray['###REGISTER_END_DATE###'] = $markerArray['###REGISTER_TO_DATE###'];
        $markerArray['###REGISTER_TO_TIME###'] = $this->cObj->stdWrap(
          $regToTime, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['time_stdWrap.'] //TypoScript "stdWrap properties".
        );
        $markerArray['###REGISTER_END_TIME###'] = $markerArray['###REGISTER_TO_TIME###'];
        $markerArray['###REGISTER_TO_LABEL###'] = htmlspecialchars($this->pi_getLL('registerEndLabel'));
        $markerArray['###REGISTER_END_LABEL###'] = $markerArray['###REGISTER_TO_LABEL###'];
      } else {
        $markerArray['###REGISTER_TO_DATE###'] = '';
        $markerArray['###REGISTER_END_DATE###'] = '';
        $markerArray['###REGISTER_TO_TIME###'] = '';
        $markerArray['###REGISTER_END_TIME###'] = '';
        $markerArray['###REGISTER_TO_LABEL###'] = '';
        $markerArray['###REGISTER_END_LABEL###'] = '';
      }

      if($this->conf['enableRegistration'] && $this->conf['enableRegistrationsTracking'] && $row['tx_mblnewsevent_hasregistration']) {
        if($this->conf['displayCurrentRegistrations']) {
          $markerArray['###REGISTER_REGISTEREDCOUNT###'] = $this->cObj->stdWrap(
              (int) $registrationsCount, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
              $this->conf['currentRegistrations_stdWrap.'] //TypoScript "stdWrap properties".
          );
          $markerArray['###REGISTER_REGISTEREDCOUNT_LABEL###'] = $this->cObj->stdWrap(
              $this->pi_getLL('registeredCount'),
              $this->conf['currentRegistrationsLabel_stdWrap.'] //TypoScript "stdWrap properties".
          );
        } else {
          $markerArray['###REGISTER_REGISTEREDCOUNT###'] = '';
          $markerArray['###REGISTER_REGISTEREDCOUNT_LABEL###'] = '';
        }

        if($this->conf['displayPlacesFree']) {
          if($registrationsMax == 0) {
            $tmpRegCount = $this->pi_getLL('registeredUnlimited');
          } else {
            $tmpRegCount = $registrationsMax-$registrationsCount;
            $tmpRegCount = (int) ($tmpRegCount<0?0:$tmpRegCount);
          }

          $markerArray['###REGISTER_FREECOUNT###'] = $this->cObj->stdWrap(
              $tmpRegCount, //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
              $this->conf['placesFree_stdWrap.'] //TypoScript "stdWrap properties".
          );
          $markerArray['###REGISTER_FREECOUNT_LABEL###'] = $this->cObj->stdWrap(
              $this->pi_getLL('registeredFreeCount'),
              $this->conf['currentRegistrationsLabel_stdWrap.'] //TypoScript "stdWrap properties".
          );
        } else {
          $markerArray['###REGISTER_FREECOUNT###'] = '';
          $markerArray['###REGISTER_FREECOUNT_LABEL###'] = '';
        }

        if($this->conf['displayMaxRegistrations']) {
          $markerArray['###REGISTER_MAX###'] = $this->cObj->stdWrap(
              $registrationsMax > 0 ? (int)$registrationsMax : $this->pi_getLL('registeredUnlimited'), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
              $this->conf['maxRegistrations_stdWrap.'] //TypoScript "stdWrap properties".
          );
          $markerArray['###REGISTER_MAX_LABEL###'] = $this->cObj->stdWrap(
              $this->pi_getLL('registeredMax'),
              $this->conf['currentRegistrationsLabel_stdWrap.'] //TypoScript "stdWrap properties".
          );
        } else {
          $markerArray['###REGISTER_MAX###'] = '';
          $markerArray['###REGISTER_MAX_LABEL###'] = '';
        }
      } else {
        $markerArray['###REGISTER_REGISTEREDCOUNT###'] = '';
        $markerArray['###REGISTER_FREECOUNT###'] = '';
        $markerArray['###REGISTER_MAX###'] = '';
        $markerArray['###REGISTER_REGISTEREDCOUNT_LABEL###'] = '';
        $markerArray['###REGISTER_FREECOUNT_LABEL###'] = '';
        $markerArray['###REGISTER_MAX_LABEL###'] = '';
      }

      if((!$eventClosed && !($eventFull && $this->conf['disableIfFull'])) && $eventOpened && $this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration']) {
        $markerArray['###REGISTER_LINK###'] = $this->cObj->typoLink(
          htmlspecialchars($this->pi_getLL('registerLinkLabel')),
          $this->conf['registrationLink_typolink.']
        );
      } else {
        $markerArray['###REGISTER_LINK###'] = '';
      }

      if($eventClosed && $this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration']) {
        $markerArray['###REGISTER_CLOSED_LABEL###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('registerClosed')),
          $this->conf['registrationClosed_stdWrap.']
        );
      } else {
        $markerArray['###REGISTER_CLOSED_LABEL###'] = '';
      }

      if($eventFull && $this->conf['enableRegistration'] && $this->conf['enableRegistrationsTracking'] && $row['tx_mblnewsevent_hasregistration']) {
        $markerArray['###REGISTER_FULL_LABEL###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('registerFull')),
          $this->conf['registrationFull_stdWrap.']
        );
      } else {
        $markerArray['###REGISTER_FULL_LABEL###'] = '';
      }

      //Prioritize "CLOSED" or "FULL" info shown
      if($markerArray['###REGISTER_FULL_LABEL###'] != '' && $markerArray['###REGISTER_CLOSED_LABEL###'] != '' && $this->conf['closedFullPriority'] == 'closed') {
        $markerArray['###REGISTER_FULL_LABEL###'] = '';
      } elseif($markerArray['###REGISTER_FULL_LABEL###'] != '' && $markerArray['###REGISTER_CLOSED_LABEL###'] != '' && $this->conf['closedFullPriority'] == 'full') {
        $markerArray['###REGISTER_CLOSED_LABEL###'] = '';
      }

      if($row['tx_mblnewsevent_organizer'] != '') {
        //Split into table and user
        $organizerArray = $this->_getOrganizerArray($row);

        $markerArray['###EVENT_ORGANIZER###'] =  $this->cObj->stdWrap(
            $this->cObj->typoLink(
                htmlspecialchars($organizerArray['name']),
                array('parameter'=>$organizerArray['email'])
            ),
            $this->conf['organizer_stdWrap.']
        );
      } else {
        $markerArray['###EVENT_ORGANIZER###'] = '';
      }

      $markerArray['###EVENT_DATE_TEXT###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('event_date')),
          $this->conf['dateLabel_stdWrap.']
      );
      $markerArray['###EVENT_TO_TEXT###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('to')),
          $this->conf['toLabel_stdWrap.']
      );
      $markerArray['###EVENT_DATES_TEXT###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('event_dates')),
          $this->conf['datesLabel_stdWrap.']
      );
      $markerArray['###EVENT_WHERE_TEXT###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('location')),
          $this->conf['whereLabel_stdWrap.']
      );
      $markerArray['###EVENT_ORGANIZER_TEXT###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('organizer')),
          $this->conf['organizerLabel_stdWrap.']
      );

      if($this->conf['enablePrice']) {
        //If the price is zero or less, it's free
        if($row['tx_mblnewsevent_price'] > 0) {
          $markerArray['###EVENT_PRICE###'] = $this->cObj->stdWrap(
            number_format(
            $row['tx_mblnewsevent_price'],
            $this->conf['priceDecimals']
            ), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
            $this->conf['price_stdWrap.'] //TypoScript "stdWrap properties".
          );
        } else {
          $markerArray['###EVENT_PRICE###'] = $this->cObj->stdWrap(
            $this->pi_getLL('freePrice'), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
            $this->conf['price_stdWrap.'] //TypoScript "stdWrap properties".
          );
        }
        $markerArray['###EVENT_PRICE_LABEL###'] = htmlspecialchars($this->pi_getLL('price'));

        $markerArray['###EVENT_PRICE_NOTE###'] = $this->cObj->stdWrap(
          htmlspecialchars($row['tx_mblnewsevent_pricenote']), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['pricenote_stdWrap.'] //TypoScript "stdWrap properties".
        );
        $markerArray['###EVENT_PRICE_NOTE_LABEL###'] = htmlspecialchars($this->pi_getLL('priceNote'));
      } else {
        $markerArray['###EVENT_PRICE###'] = '';
        $markerArray['###EVENT_PRICE_LABEL###'] = '';
        $markerArray['###EVENT_PRICE_NOTE###'] = '';
        $markerArray['###EVENT_PRICE_NOTE_LABEL###'] = '';
      }

      //Single ICS link
      if($this->conf['enableSingleICSLink']) {
        $markerArray['###EVENT_SINGLE_ICS_LINK###'] = $this->cObj->stdWrap(
          htmlspecialchars($this->pi_getLL('downloadSingleICS')), //Input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
          $this->conf['singleICSLink_stdWrap.'] //TypoScript "stdWrap properties".
        );
      } else {
        $markerArray['###EVENT_SINGLE_ICS_LINK###'] = '';
      }

      //$templateFile = $this->cObj->FileResource($this->conf['templateFile']);
	  $path = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['templateFile']);
      if ($path !== null && file_exists($path)) {
          $templateFile = file_get_contents($path);
      }


      //Find the correct event registration subpart. This subpart can stand alone or as a part of ###EVENT_WRAP###
      $regSubpartMarker = '';
      if($this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration'] && !$eventClosed) {
        //If there is no registration start or end date
        if(!$row['tx_mblnewsevent_regfrom'] && !$row['tx_mblnewsevent_regto']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_END_NO_START###';
        //No from date and to to time
        } elseif(!$row['tx_mblnewsevent_regfrom'] && !$row['tx_mblnewsevent_regtotime']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_START_NO_TIME###';
        //No to date and no from time
        } elseif(!$row['tx_mblnewsevent_regto'] && !$row['tx_mblnewsevent_regfromtime']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_END_NO_TIME###';
        //No from date
        } elseif(!$row['tx_mblnewsevent_regfrom'] && $row['tx_mblnewsevent_regto']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_START###';
        } elseif(!$row['tx_mblnewsevent_regto'] && $row['tx_mblnewsevent_regfrom']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_END###';
        //No time
        } elseif(!$row['tx_mblnewsevent_regfromtime'] && !$row['tx_mblnewsevent_regtotime']) {
          $regSubpartMarker = '###EVENT_REGISTER_NO_TIME###';
        } else {
          $regSubpartMarker = '###EVENT_REGISTER###';
        }
      } elseif($this->conf['enableRegistration'] && $row['tx_mblnewsevent_hasregistration'] && $eventClosed && $this->conf['replaceWithClosed']) {
        $regSubpartMarker = '###EVENT_REGISTER_CLOSED###';
      }

      if($regSubpartMarker != '') {
        $regTemplate = $templateService->getSubpart(
          $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
          $regSubpartMarker //Marker string, eg. "###CONTENT_PART###"
        );
      } else {
        $regTemplate = '';
      }

      //Find out which template to use, depending on time and span of event
      //If event has only a start date
      if(!$row['tx_mblnewsevent_to'] && !$row['tx_mblnewsevent_totime'] && !$row['tx_mblnewsevent_fromtime']) {
        $mainTemplate = $templateService->getSubpart(
          $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
          '###ONE_DAY_EVENT_NO_TIME###' //Marker string, eg. "###CONTENT_PART###"
        );
      //If event has only a start date and a start time
      } elseif(!$row['tx_mblnewsevent_to'] && !$row['tx_mblnewsevent_totime']) {
        $mainTemplate = $templateService->getSubpart(
          $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
          '###NO_END_EVENT###' //Marker string, eg. "###CONTENT_PART###"
        );
      //If the event goes within one day
      } elseif(date('dmy', $row['tx_mblnewsevent_from']) == date('dmy', $row['tx_mblnewsevent_to'])) {
        //If there is no time set
        if($row['tx_mblnewsevent_fromtime'] == 0 && $row['tx_mblnewsevent_totime'] == 0) {
          $mainTemplate = $templateService->getSubpart(
            $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
            '###ONE_DAY_EVENT_NO_TIME###' //Marker string, eg. "###CONTENT_PART###"
          );
        } else {
          $mainTemplate = $templateService->getSubpart(
            $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
            '###ONE_DAY_EVENT###' //Marker string, eg. "###CONTENT_PART###"
          );
        }
      //If the event spans multiple days
      } else {
        //If there is no time set
        if($row['tx_mblnewsevent_fromtime'] == 0 && $row['tx_mblnewsevent_totime'] == 0) {
          $mainTemplate = $templateService->getSubpart(
            $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
            '###MULTIPLE_DAY_EVENT_NO_TIME###' //Marker string, eg. "###CONTENT_PART###"
          );
        } else {
          $mainTemplate = $templateService->getSubpart(
            $templateFile, //Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
            '###MULTIPLE_DAY_EVENT###' //Marker string, eg. "###CONTENT_PART###"
          );
        }
      }

      $parentMarkerArray = array_merge($parentMarkerArray, $markerArray);

      //Inserting registration wrap in both event wrap and global.
      $markerArray['###EVENT_REGISTER_WRAP###'] = $parentMarkerArray['###EVENT_REGISTER_WRAP###'] = $templateService->substituteMarkerArrayCached(
        $regTemplate, //The content stream, typically HTML template content.
        $markerArray //Regular marker-array where the 'keys' are substituted in $content with their values
      );

      $parentMarkerArray['###EVENT_WRAP###'] = $templateService->substituteMarkerArrayCached(
        $mainTemplate, //The content stream, typically HTML template content.
        $markerArray //Regular marker-array where the 'keys' are substituted in $content with their values
      );

      //debug($tt_news->cObj);
      //debug($regFromDate+$regFromTime); debug($regToDate+$regToTime); debug(time());
      //debug($markerArray);
    } else {
      $parentMarkerArray['###EVENT_WRAP###'] = '';
      $parentMarkerArray['###EVENT_REGISTER_WRAP###'] = '';
    }

    return $parentMarkerArray;
  }

}

?>
