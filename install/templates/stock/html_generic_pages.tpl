<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2007-2009 Asymmetric Software - Innovation & Excellence
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Front: Text Pages Display Template File
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
    $generic_query = $g_db->fly("select gtext_title, gtext_description, date_added from " . TABLE_GTEXT . " where gtext_id='" . (int)$current_gtext_id . "'");
    $generic_array = $g_db->fetch_array($generic_query);
?>
            <div class="pageHeader"><h1><?php echo $generic_array['gtext_title']; ?></h1></div>
            <div><b><?php echo tep_date_long($generic_array['date_added']); ?></b></div>
            <div><table>
              <tr>
                <td><div class="pageContent desc"><?php echo $generic_array['gtext_description']; ?></div></td>
              </tr>
            </table></div>
