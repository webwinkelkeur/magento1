<?php 
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_WebwinkelKeur
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
 
$installer = $this;
$installer->startSetup();
$installer->run(
    "
	DROP TABLE IF EXISTS {$this->getTable('webwinkelconnect_reviews')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('webwinkelconnect_reviews')} (
	  `review_id` int(10) NOT NULL AUTO_INCREMENT,
	  `shop_id` int(5) NOT NULL,
	  `company` varchar(255) DEFAULT NULL,
	  `hash` varchar(255) NOT NULL,
	  `name` text NOT NULL,
	  `experience` text NOT NULL,
	  `date` date NOT NULL,
	  `rating` tinyint(1) DEFAULT NULL,
	  `delivery_time` tinyint(1) DEFAULT NULL,
	  `userfriendlyness` tinyint(1) DEFAULT NULL,
	  `price_quality` tinyint(1) DEFAULT NULL,
	  `aftersales` tinyint(1) DEFAULT NULL,
	  `sidebar` tinyint(1) NOT NULL DEFAULT '1',
	  `status` tinyint(5) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`review_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS {$this->getTable('webwinkelconnect_log')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('webwinkelconnect_log')} (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `type` varchar(255) NOT NULL,
	  `shop_id` varchar(255) NOT NULL,
	  `company` varchar(255) DEFAULT NULL,
	  `review_update` int(5) DEFAULT '0',
	  `review_new` int(5) DEFAULT '0',
	  `response` text,
	  `order_id` int(10) DEFAULT NULL,
	  `cron` varchar(255) DEFAULT NULL,
	  `date` datetime NOT NULL,
	  `time` varchar(255) NOT NULL,
	  `api_url` text,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS {$this->getTable('webwinkelconnect_stats')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('webwinkelconnect_stats')} (
	  `id` int(5) NOT NULL AUTO_INCREMENT,
	  `company` varchar(255) DEFAULT NULL,
	  `shop_id` int(5) NOT NULL,
	  `average` smallint(6) DEFAULT '0',
	  `average_stars` smallint(6) DEFAULT '0',
	  `votes` int(5) DEFAULT '0',
	  `percentage_positive` smallint(6) DEFAULT '0',
	  `number_positive` int(5) DEFAULT '0',
	  `percentage_neutral` smallint(6) DEFAULT '0',
	  `number_neutral` int(5) DEFAULT '0',
	  `percentage_negative` smallint(6) DEFAULT '0',
	  `number_negative` int(5) DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;	
"
);
$installer->endSetup(); 