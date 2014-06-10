-- Under Test - #3540406 --
-- Adds support for linking blog posts, internally and externally, using CLADDIER protocol
CREATE TABLE `claddier_citations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `url` text NOT NULL,
  `ping_url` text NOT NULL,
  `excerpt` text NOT NULL,
  `blog_name` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `received_at` datetime NOT NULL,
  `metadata` text NOT NULL,
  `metadata_format` text NOT NULL,
  `type` set('cites','cited-by','copy') DEFAULT NULL,
  `host_ip` text NOT NULL,
  `blog_bit_id` int(11) NOT NULL,
  `blog_bit_rid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `claddier_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repository_name` text NOT NULL,
  `repository_url` text NOT NULL,
  `repository_ip_address` varchar(45) NOT NULL,
  `registered_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `claddier_whitelist` (
`id` ,
`repository_name` ,
`repository_url` ,
`repository_ip_address` ,
`registered_at`
)
VALUES (
NULL , 'Localhost', 'localhost', '127.0.1.1', NOW()
);
