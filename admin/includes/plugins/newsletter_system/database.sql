-- ----------------------------------------------------------------------------
--  Copyright (c) 2006-2010 Asymmetric Software - Innovation & Excellence
--  Author: Mark Samios
--  http://www.asymmetrics.com
--  Admin Plugin: Newsletter MySQL Database Tables
-- ----------------------------------------------------------------------------
--  Script is intended to be used with:
--  osCommerce, Open Source E-Commerce Solutions
--  http://www.oscommerce.com
--  Copyright (c) 2003 osCommerce
-- -------------------------------------------------------------------------
--  Released under the GNU General Public License
-- -------------------------------------------------------------------------

drop table if exists newsletters;
create table newsletters (
  template_id int(11) NOT NULL,
  customers_id int(11) NOT NULL,
  newsletter_hits int(8) NOT NULL,
  newsletter_sent int(8) NOT NULL,
  times_sent int(3) NOT NULL default 0,
  date_sent datetime NULL,
  PRIMARY KEY (template_id),
  KEY idx_template_id (template_id),
  KEY idx_date_sent (date_sent)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
