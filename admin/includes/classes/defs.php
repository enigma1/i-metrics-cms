<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Admin: Common Definitions
// Loads the common definitions used by the I-Metrics application
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
    // Compatibility constructor
    function defs() {
      // $this->request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
      $this->request_type = (getenv('SERVER_PORT') == '443') ? 'SSL' : 'NONSSL';
      $this->ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])?true:false;
      $this->server = HTTP_SERVER;
      $this->relpath = $this->server . DIR_WS_ADMIN;
      $this->cookie_domain = HTTP_COOKIE_DOMAIN;
      $this->cookie_path = HTTP_COOKIE_PATH;

      $this->cserver = HTTP_CATALOG_SERVER;
      $this->crelpath = $this->cserver . DIR_WS_CATALOG;

      $this->script = tep_sanitize_string(basename($_SERVER['SCRIPT_NAME']));
      $this->action = (isset($_GET['action']))?tep_sanitize_string($_GET['action']):'';
      $this->media = array();
      $this->link_params = array();
    }
  }
?>
