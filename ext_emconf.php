<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mbl_newsevent".
 *
 * Auto generated 28-11-2016 11:22
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'News Event',
  'description' => 'Adds event date/time, location, price and registration info to news (tt_news). Event information can be downloaded to calendars through an iCalendar (.ics) feed. Requires some TypoScript setup: Please read the manual!',
  'category' => 'plugin',
  'state' => 'stable',
  'uploadfolder' => false,
  'createDirs' => '',
  'clearCacheOnLoad' => 1,
  'author' => 'Mathias Bolt Lesniak',
  'author_email' => 'mathias@pixelant.no',
  'author_company' => 'Pixelant',
  'version' => '2.6.0',
  '_md5_values_when_last_written' => 'a:20:{s:20:"class.ext_update.php";s:4:"a347";s:23:"class.mbl_newsevent.php";s:4:"87de";s:19:"event_template.tmpl";s:4:"3435";s:12:"ext_icon.gif";s:4:"c028";s:17:"ext_localconf.php";s:4:"a4ee";s:14:"ext_tables.php";s:4:"7b8b";s:14:"ext_tables.sql";s:4:"9c7e";s:28:"ext_typoscript_constants.txt";s:4:"b736";s:24:"ext_typoscript_setup.txt";s:4:"763a";s:13:"locallang.xml";s:4:"b4fc";s:16:"locallang_db.xml";s:4:"1062";s:34:"tt_news_v2_template_newsevent.html";s:4:"25f6";s:14:"doc/manual.sxw";s:4:"096b";s:19:"doc/wizard_form.dat";s:4:"1b9f";s:20:"doc/wizard_form.html";s:4:"2a29";s:21:"res/ics_template.tmpl";s:4:"e156";s:24:"static/ics/constants.txt";s:4:"68b3";s:20:"static/ics/setup.txt";s:4:"720e";s:29:"static/standard/constants.txt";s:4:"e2a5";s:25:"static/standard/setup.txt";s:4:"7804";}',
  'constraints' =>
  array (
    'depends' =>
    array (
      'tt_news' => '7.6.0-7.6.99',
      'php' => '5.2.0-7.1.99',
      'typo3' => '7.6.0-7.6.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  'clearcacheonload' => true,
);
