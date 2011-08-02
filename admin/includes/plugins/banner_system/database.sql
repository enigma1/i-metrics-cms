-- ----------------------------------------------------------------------------
-- Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
-- http://www.asymmetrics.com
-- ----------------------------------------------------------------------------
-- I-Metrics CMS
-- ----------------------------------------------------------------------------
-- Script is intended to be used with:
-- osCommerce, Open Source E-Commerce Solutions
-- http://www.oscommerce.com
-- Copyright (c) 2003 osCommerce
-- ----------------------------------------------------------------------------
-- Database Backup File:
-- C:/Server/websites/i-metrics-cms/admin/includes/plugins/banner_system/database.sql
-- Copyright (c) 2011 Joe Doe
-- ----------------------------------------------------------------------------
-- Database: isample
-- Database Server: localhost
-- Backup Date: 07/21/2011 10:49:04
-- ----------------------------------------------------------------------------
-- Released under the GNU General Public License
-- ----------------------------------------------------------------------------

drop table if exists banners_group;
create table banners_group (
  group_id int(11) not null auto_increment,
  group_name varchar(32) not null ,
  group_pos int(3) default '1' not null ,
  group_type tinyint(1) default '1' not null ,
  group_width int(3) not null ,
  group_height int(3) not null ,
  PRIMARY KEY (group_id),
  KEY idx_group_pos (group_pos)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

drop table if exists banners;
create table banners (
  auto_id int(11) not null auto_increment,
  group_id int(11) not null ,
  content_id int(11) not null ,
  content_type int(3) not null ,
  content_name varchar(255) not null ,
  content_link text not null ,
  filename varchar(255) not null ,
  impressions int(11) not null ,
  clicks int(11) not null ,
  sort_id int(3) default '100' not null ,
  status_id tinyint(1) default '1' not null ,
  date_added datetime ,
  PRIMARY KEY (auto_id),
  KEY idx_content_id (content_id),
  KEY idx_group_id (group_id),
  KEY idx_status_id (status_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

