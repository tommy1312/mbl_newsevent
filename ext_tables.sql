#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_mblnewsevent_isevent tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_mblnewsevent_from int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_fromtime int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_to int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_totime int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_where tinytext NOT NULL,
	tx_mblnewsevent_organizer tinytext NOT NULL,
	tx_mblnewsevent_regfrom int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_regfromtime int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_regto int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_regtotime int(11) DEFAULT '0' NOT NULL,
	tx_mblnewsevent_regurl text NOT NULL,
	tx_mblnewsevent_hasregistration tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tx_mblnewsevent_registrationmax int(11) unsigned DEFAULT '0' NOT NULL,
	tx_mblnewsevent_price decimal(7,2) DEFAULT '0.00' NOT NULL,
	tx_mblnewsevent_pricenote tinytext NOT NULL
);