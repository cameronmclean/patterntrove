ALTER TABLE  `users` ADD  `user_cache` DATETIME NOT NULL;

-- Under Test - #3429369 --
-- Gives the option to use MySQL full text search --
-- See use_mysql_fulltext_search in default_config.php --
ALTER TABLE blog_bits ADD FULLTEXT (bit_content, bit_title);


-- Under Test - #3438710 --
-- Adds extra provision for storing references to larger files rather than the file itself
ALTER TABLE blog_data ADD mode varchar(10);
ALTER TABLE blog_data ADD filesize int;
ALTER TABLE blog_data ADD filepath varchar(1024);
ALTER TABLE blog_data ADD checksum varchar(60);
