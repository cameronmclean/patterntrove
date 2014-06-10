CREATE TABLE `chemspider_blog_keywords` (
  `blogkw_id` int(11) NOT NULL AUTO_INCREMENT,
  `blogkw_bit_rid` int(11) NOT NULL,
  `blogkw_kw_id` int(11) NOT NULL,
  `blogkw_confidence` double NOT NULL,
  `blogkw_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`blogkw_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `chemspider_keywords` (
  `kw_id` int(11) NOT NULL AUTO_INCREMENT,
  `kw_value` varchar(255) NOT NULL,
  `kw_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `kw_md5` varchar(50) NOT NULL,
  PRIMARY KEY (`kw_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `chemspider_keyword_properties` (
  `kwprop_id` int(11) NOT NULL AUTO_INCREMENT,
  `kwprop_kw_id` int(11) NOT NULL,
  `kwprop_key` varchar(255) NOT NULL,
  `kwprop_value` varchar(255) NOT NULL,
  `kwprop_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kwprop_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

