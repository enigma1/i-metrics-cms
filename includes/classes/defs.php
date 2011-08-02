<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Common Definitions Class
// Loads common definitions used by the application
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//----------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
*/
  class defs {
    // compatibility constructor
    function defs() {

      $this->link_params = array();
      //$this->request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
      $this->request_type = (getenv('SERVER_PORT') == '443') ? 'SSL' : 'NONSSL';

      if ($this->request_type == 'NONSSL') {
        define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
        $this->server = HTTP_SERVER;
        $this->cookie_domain = HTTP_COOKIE_DOMAIN;
        $this->cookie_path = HTTP_COOKIE_PATH;
      } else {
        define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
        $this->server = HTTPS_SERVER;
        $this->cookie_domain = HTTPS_COOKIE_DOMAIN;
        $this->cookie_path = HTTPS_COOKIE_PATH;
      }
      $this->relpath = $this->server . DIR_WS_CATALOG;

      $this->ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])?true:false;

      $this->script = tep_sanitize_string(basename($_SERVER['SCRIPT_NAME']));
      $this->action = (isset($_GET['action']))?tep_sanitize_string($_GET['action']):'';
      $this->media = array();
      $this->seo = false;
      $this->abstract_id = $this->gtext_id = $this->page_id = 0;
      $this->external_path = '';
      $this->external = false;
      $this->gtext_id = $this->abstract_id = '';
    }
  }
?>
