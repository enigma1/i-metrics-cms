<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Ajax Abstract Zones Selection module
//----------------------------------------------------------------------------
// I-Metrics CMS
//----------------------------------------------------------------------------
// Script is intended to be used with:
// osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
------------------------------------------------------------------------------
// Released under the GNU General Public License
//----------------------------------------------------------------------------
//
*/
  $action = (isset($_GET['action']) ? $g_db->prepare_input($_GET['action'], true) : '');
  $gtext_id = (isset($_POST['gtext_id']) ? (int)$_POST['gtext_id'] : '');

  switch ($action) {
    case 'assign':
      $zones_array = (isset($_POST['zone_id']) ? $g_db->prepare_input($_POST['zone_id'], true) : '');

      if( empty($gtext_id) ) {
        break;
      }

      if( empty($zones_array) ) {
        $delete_query = $g_db->query("delete from " . TABLE_GTEXT_TO_DISPLAY . " where gtext_id = '" . (int)$gtext_id . "'");
        break;
      }

      $delete_query = $g_db->query("delete from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id not in (" . $g_db->filter(implode(',',array_keys($zones_array))) . ") and gtext_id = '" . (int)$gtext_id . "'");
      foreach($zones_array as $key => $value ) {
        $check_query = $g_db->query("select count(*) as total from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$key . "' and gtext_id = '" . (int)$gtext_id . "'");
        $check_array = $g_db->fetch_array($check_query);
        if( $check_array['total'] ) continue;

        $data_query = $g_db->query("select gtext_title from " . TABLE_GTEXT . " where gtext_id = '" . (int)$gtext_id . "'");
        if( !$g_db->num_rows($data_query) ) continue;
        $data_array = $g_db->fetch_array($data_query);
        $sql_data_array = array(
                                'abstract_zone_id' => (int)$key,
                                'gtext_id' => (int)$gtext_id,
                                'gtext_alt_title' => $data_array['gtext_title'],
                                'sequence_order' => 1,
                               );
        $g_db->perform(TABLE_GTEXT_TO_DISPLAY, $sql_data_array);
      }
      break;
    default:
      break;
  }
?>
    <div id="list_result" style="display: none;"></div>
    <div id="abstract_list">
      <div class="comboHeading" style="border: 1px solid #777;">
<?php
  if( $action != 'error' ) {
      $replace_array = array();

      $html_string = '';
      $html_string .= 
      '          <div class="listArea">' . tep_draw_form("form_assign", basename($PHP_SELF), 'action=assign', 'post', 'id="core_assign_form" enctype="multipart/form-data"') . '<table border="0" width="100%" cellspacing="1" cellpadding="3">' . "\n" . 
      '            <tr class="dataTableHeadingRow">' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TEXT_PAGE_SELECT . tep_draw_hidden_field('module', 'abstract_zones') . tep_draw_hidden_field('gtext_id', $gtext_id) . '</td>' . "\n" . 
      '              <td class="dataTableHeadingContent">' . TABLE_HEADING_ABSTRACT_ZONES . '</td>' . "\n" . 
      '            </tr>' . "\n";

      $rows = 0;
      $zones_query = $g_db->query("select az.abstract_zone_id, az.abstract_zone_name, az.status_id from " . TABLE_ABSTRACT_ZONES . " az left join " . TABLE_ABSTRACT_TYPES . " at on (az.abstract_types_id=at.abstract_types_id) where at.abstract_types_class='generic_zones' order by az.sort_id, az.status_id desc, az.abstract_zone_name");
      while( $zones = $g_db->fetch_array($zones_query) ) {

        $check_query = $g_db->query("select count(*) as total from " . TABLE_GTEXT_TO_DISPLAY . " where abstract_zone_id = '" . (int)$zones['abstract_zone_id'] . "' and gtext_id = '" . (int)$gtext_id . "'");
        $check_array = $g_db->fetch_array($check_query);
        $bCheck  = $check_array['total']?true:false;

        if($bCheck) {
          $replace_array[] = '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES, 'zID=' . $zones['abstract_zone_id'] . '&action=list') . '" title="' . $zones['abstract_zone_name'] . '" class="list_abstract_zones" attr="' . $gtext_id . '">' . $zones['abstract_zone_name'] . '</a>';
        }

        $rows++;
        $row_class = ($rows%2)?'dataTableRow':'dataTableRowAlt';
        if( !$zones['status_id'] ) {
          $row_class = 'dataTableRowHigh';
        }
        $html_string .= 
        '            <tr class="' . $row_class . '">' . "\n" . 
        '              <td class="dataTableContent">' . tep_draw_checkbox_field('zone_id[' . $zones['abstract_zone_id'] . ']', 'on', $bCheck) . '</td>' . "\n" . 
        '              <td class="dataTableContent">' . $zones['abstract_zone_name'] . '</td>' . "\n";
        '            </tr>' . "\n";
      }
      $html_string .= 
      '            </table></form></div>' . "\n";
      echo $html_string;

      $html_string = '';
      $html_string .= '<div id="target_abstract_zones" style="display:none">' . "\n";
      if( count($replace_array) ) {
        $html_string .= implode('<br />', $replace_array);
      } else {
        $html_string .= '<a href="' . tep_href_link(FILENAME_ABSTRACT_ZONES) . '" class="list_abstract_zones" attr="' . $gtext_id . '"><b style="color: #FF0000;">' . TEXT_INFO_ZONES_NOT_ASSIGNED . '</b></a>';
      }
      $html_string .= '</div>' . "\n";
      echo $html_string;
?>

<?php
  } else {
?>
        <div><?php echo ERROR_INCOMPLETE_PARAMETERS; ?></div>
<?php
  }
?>
      </div>
    </div>