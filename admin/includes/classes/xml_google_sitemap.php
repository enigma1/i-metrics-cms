<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// SEO-G XML Google Sitemap Class for Admin.
// Support class to generate sitemap from the recorded urls
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class xml_google_sitemap extends xml_core {
    // Compatibility constructor
    function xml_google_sitemap($options=0) {
      $this->options = $options;
      parent::xml_core();
      $this->insert_raw_entry('<?xml version="1.0" encoding="UTF-8"?>');
    }

    function build_map() {
      $this->insert_raw_entry('<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">');
      $this->set_urls();
      $this->insert_entry('urlset', true);
    }

    function set_urls() {
      extract(tep_load('defs', 'database'));

      $seo_query = $db->query("select su.seo_url_get, su.seo_url_org, su.seo_url_priority, su.date_added, sgf.seo_frequency_name from " . TABLE_SEO_URL . " su, " . TABLE_SEO_FREQUENCY . " sgf where sgf.seo_frequency_id=su.seo_frequency_id order by su.seo_url_hits desc");
      while( $seo = $db->fetch_array($seo_query) ) {
        $seo['seo_url_get'] = $cDefs->crelpath . $seo['seo_url_get'];
        $this->insert_entry('url');
        $this->insert_closed_entry('loc', htmlspecialchars(utf8_encode($seo['seo_url_get'])) );
        $this->insert_closed_entry('lastmod', $seo['date_added']);
        $this->insert_closed_entry('priority', $seo['seo_url_priority']);
        $this->insert_closed_entry('changefreq', $seo['seo_frequency_name']);
        $this->insert_entry('url', true);
      }
    }

  }
?>