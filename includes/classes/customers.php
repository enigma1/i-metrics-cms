<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
// Front: Customers Support Class
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
  class customers {
    // compatibility constructor
    function customers() {
      extract(tep_load('database', 'sessions'));

      $this->data = array();
      $this->default = 0;
      $this->id =& $cSessions->register('customers_id', $this->default);
      $this->data = $this->get($this->id);
    }

    function get($id='') {
      extract(tep_load('database'));

      if( empty($id) ) {
        return $this->data;
      }

      $check_query = $db->fly("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$id . "'");
      if( !$db->num_rows($check_query) ) {
        return array();
      }
      return $db->fetch_array($check_query);
    }
  }
?>
