-- ----------------------------------------------------------------------------
--  Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
--  Author: Mark Samios
--  http://www.asymmetrics.com
--  Comments System mySQL Database File
-- ----------------------------------------------------------------------------
--  Script is intended to be used with:
--  osCommerce, Open Source E-Commerce Solutions
--  http://www.oscommerce.com
--  Copyright (c) 2003 osCommerce
-- -------------------------------------------------------------------------
--  Released under the GNU General Public License
-- -------------------------------------------------------------------------
drop table if exists comments;
create table comments (
  auto_id int(11) NOT NULL auto_increment,
  comments_id int(11) not null, -- Entity ID can be text, collection etc.
  content_type int(3) NOT NULL default 1, -- Content Type whether its 1=text, 2=collection
  comments_author varchar(64) not null,
  comments_email varchar(96) not null,
  comments_url varchar(255) not null,
  comments_rating int(1) not null,
  comments_key varchar(32) not null,
  comments_body text not null,
  resolution int(1) not null default 5,
  ip_address varchar(15) not null,
  date_added datetime not null,
  status_id tinyint(1) default 0 not null,
  read_id tinyint(1) default 0 not null,
  PRIMARY KEY (auto_id),
  KEY idx_comments_id (comments_id),
  KEY idx_content_type (content_type),
  KEY idx_comments_key (comments_key),
  KEY idx_status_id (status_id)
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

drop table if exists comments_to_content;
create table comments_to_content (
  comments_id int(11) not null, -- Entity ID can be text, collection etc.
  content_type int(3) NOT NULL default 1, -- Content Type whether its 1=text, 2=collection
  KEY idx_comments_id (comments_id),
  KEY idx_content_type (content_type)
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
