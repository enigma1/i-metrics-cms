-- ----------------------------------------------------------------------------
--  Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
--  Author: Mark Samios
--  http://www.asymmetrics.com
--  Admin Plugin: Votes System Database setup
-- ----------------------------------------------------------------------------
--  Script is intended to be used with:
--  osCommerce, Open Source E-Commerce Solutions
--  http://www.oscommerce.com
--  Copyright (c) 2003 osCommerce
-- -------------------------------------------------------------------------
--  Released under the GNU General Public License
-- -------------------------------------------------------------------------
drop table if exists votes;
create table votes (
  auto_id int(11) NOT NULL auto_increment,
  votes_id int(11) NOT NULL,
  votes_type int(3) NOT NULL default 1,
  rating int(3) NOT NULL,
  resolution int(1) NOT NULL default 2,
  ip_address VARCHAR(32) NOT NULL,
  date_added datetime NULL,
  PRIMARY KEY (auto_id),
  KEY idx_votes_id (votes_id),
  KEY idx_votes_type (votes_type),
  KEY idx_date_added (date_added),
  KEY idx_rating (rating)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
