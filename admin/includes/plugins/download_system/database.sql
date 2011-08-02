-- ----------------------------------------------------------------------------
--  Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
--  Author: Mark Samios
--  http://www.asymmetrics.com
--  Admin Plugin: Download System MySQL Database Tables
-- ----------------------------------------------------------------------------
--  Script is intended to be used with:
--  osCommerce, Open Source E-Commerce Solutions
--  http://www.oscommerce.com
--  Copyright (c) 2003 osCommerce
-- -------------------------------------------------------------------------
--  Released under the GNU General Public License
-- -------------------------------------------------------------------------
drop table if exists download;
create table download (
  auto_id int(11) NOT NULL auto_increment,
  content_id int(11) NOT NULL,
  content_type int(3) NOT NULL,
  content_name VARCHAR(255) not null,
  content_text text not null,
  filename VARCHAR(255) not null,
  downloads int(8) NOT NULL,
  sort_id int(3) NOT NULL default 100,
  status_id tinyint(1) NOT NULL default 1,
  date_added datetime NULL,
  PRIMARY KEY (auto_id),
  KEY idx_content_id (content_id),
  KEY idx_status_id (status_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
