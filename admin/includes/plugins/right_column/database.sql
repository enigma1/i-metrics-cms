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
drop table if exists right_to_content;
create table right_to_content (
  auto_id int(11) not null auto_increment,
  content_id int(11) not null, -- Entity ID can be text, collection etc.
  content_type int(3) NOT NULL default 1, -- Content Type whether its 1=text, 2=collection
  content_name VARCHAR(255) not null,
  content_text text not null,
  sort_id int(1) default 1 not null,
  status_id tinyint(1) default 0 not null,
  PRIMARY KEY (auto_id),
  KEY idx_content_id (content_id),
  KEY idx_content_type (content_type),
  KEY idx_sort_id (sort_id),
  KEY idx_status_id (status_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
