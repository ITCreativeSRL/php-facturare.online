CREATE TABLE IF NOT EXISTS `facturareonline_facturi` (
	`id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`orderid` varchar ( 255 ) NOT NULL,
	`invoiceid` INT ( 11 ) UNSIGNED NOT NULL,
	`serie` VARCHAR ( 255 ) NOT NULL,
	`numar` INT ( 3 ) UNSIGNED NOT NULL,
	`total` DECIMAL ( 20, 2 ) NOT NULL DEFAULT 0.00,
	`moneda` CHAR ( 3 ) NOT NULL DEFAULT 'RON',
	`tip` enum ( 'ff', 'fp' ) NOT NULL DEFAULT 'ff',
	`code` VARCHAR ( 255 ) NOT NULL,
	`status` INT ( 3 ) NOT NULL DEFAULT '1',
	`document` MEDIUMBLOB NOT NULL COMMENT 'document = max 16MB',
	`created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY ( `id` ),
	UNIQUE KEY `invoiceid_UNIQUE` ( `invoiceid` ),
	KEY `fk_ps_facturareonline_1_idx` ( `orderid` ),
	KEY `orderid_invoiceid` ( `orderid`, `invoiceid` ),
	KEY `orderid_status` ( `orderid`, `status` ) 
) ENGINE = INNODB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `facturareonline_chitante` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `receiptid` int(11) unsigned NOT NULL,
    `invoiceid` int(11) unsigned NOT NULL,
    `serie` varchar(255) NOT NULL,
    `numar` int(3) unsigned NOT NULL,
    `total` decimal(20,2) NOT NULL DEFAULT '0.00',
    `moneda` char(3) NOT NULL DEFAULT 'RON',
    `code` varchar(255) NOT NULL,
    `document` mediumblob NOT NULL COMMENT 'document = max 16MB',
    `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `receiptid_UNIQUE` (`receiptid`),
    KEY `receiptid_invoiceid` (`receiptid`,`invoiceid`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `facturareonline_incasari_banca` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `invoiceid` int(11) unsigned NOT NULL,
    `amount` decimal(20,2) NOT NULL,
    `date` datetime NOT NULL,
    `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `invoiceid` (`invoiceid`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;
