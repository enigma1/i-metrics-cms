<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2011 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin: Database Backup support class
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
*/
  class database_backup {
    // Compatibility constructor
    function database_backup() {
      extract(tep_load('database'));
      $this->max_packet = $db->max_packet_size();
    }

    function save_tables($backup_file, $tables_array) {
      extract(tep_load('database', 'message_stack'));
      tep_set_time_limit(0);

      $fp = fopen($backup_file, 'w');
      if( !$fp ) {
        return false;
      }

      $schema = 
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- Copyright (c) 2006-' . date('Y') . ' Asymmetric Software. Innovation & Excellence.' . "\n" . 
        '-- http://www.asymmetrics.com' . "\n" .
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- I-Metrics CMS' . "\n" .
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- Script is intended to be used with:' . "\n" .
        '-- osCommerce, Open Source E-Commerce Solutions' . "\n" .
        '-- http://www.oscommerce.com' . "\n" .
        '-- Copyright (c) 2003 osCommerce' . "\n" .
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- Database Backup File:' . "\n" .
        '-- ' . $backup_file . "\n" .
        '-- Copyright (c) ' . date('Y') . ' ' . STORE_OWNER . "\n" .
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- Database: ' . DB_DATABASE . "\n" .
        '-- Database Server: ' . DB_SERVER . "\n" .
        '-- Backup Date: ' . date(PHP_DATE_TIME_FORMAT) . "\n" . 
        '-- ----------------------------------------------------------------------------' . "\n" .
        '-- Released under the GNU General Public License' . "\n" .
        '-- ----------------------------------------------------------------------------' . "\n\n";
      fputs($fp, $schema);

      foreach($tables_array as $table ) {
        $table_list = array();

        $schema = 
          'drop table if exists ' . $table . ';' . "\n" .
          'create table ' . $table . ' (' . "\n";

        $fields_array = $db->query_to_array("show fields from " . $table);
        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          $table_list[] = $fields_array[$i]['Field'];
          $schema .= '  ' . $fields_array[$i]['Field'] . ' ' . $fields_array[$i]['Type'];
          if( strlen($fields_array[$i]['Default']) > 0) $schema .= ' default \'' . $fields_array[$i]['Default'] . '\'';
          if( $fields_array[$i]['Null'] != 'YES') $schema .= ' not null';
          if( isset($fields_array[$i]['Extra'])) $schema .= ' ' . $fields_array[$i]['Extra'];
          $schema .= ',' . "\n";
        }
        $schema = preg_replace("/,\n$/", '', $schema);

        // add the keys
        $index = array();
        $keys_array = $db->query_to_array("show keys from " . $table);
        for($i=0, $j=count($keys_array); $i<$j; $i++) {
          $kname = $keys_array[$i]['Key_name'];
          if (!isset($index[$kname])) {
            $index[$kname] = array(
              'unique' => !$keys_array[$i]['Non_unique'],
              'columns' => array()
            );
          }
          $index[$kname]['columns'][] = $keys_array[$i]['Column_name'];
        }

        foreach($index as $kname => $info ) {
          $schema .= ',' . "\n";
          $columns = implode($info['columns'], ', ');
          if( $kname == 'PRIMARY' ) {
            $schema .= '  PRIMARY KEY (' . $columns . ')';
          } elseif ($info['unique']) {
            $schema .= '  UNIQUE ' . $kname . ' (' . $columns . ')';
          } else {
            $schema .= '  KEY ' . $kname . ' (' . $columns . ')';
          }
        }
        $schema .= "\n" . ') ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;' . "\n\n";
        fputs($fp, $schema);

        // dump the data
        $data_query = $db->query("select " . implode(',', $table_list) . " from " . $table);
        if( $db->num_rows($data_query) ) {
          $schema = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values ' . "\n";
          $write_flag = false;
          while( $data_array = $db->fetch_array($data_query) ) {
            $tmp_schema = '(';
            $write_flag = true;
            reset($table_list);
            while (list(,$i) = each($table_list)) {
              if (!isset($data_array[$i])) {
                $tmp_schema .= 'NULL, ';
              } elseif (tep_not_null($data_array[$i])) {
                $row = addslashes($data_array[$i]);
                $row = preg_replace("/\n#/", "\n".'\#', $row);

                $tmp_schema .= '\'' . $row . '\', ';
              } else {
                $tmp_schema .= '\'\', ';
              }
            }

            $tmp_schema = preg_replace('/, $/', '', $tmp_schema) . '),' . "\n";
            if( strlen($schema)+strlen($tmp_schema) > $this->max_packet ) {
              $write_flag = false;
              $schema = substr($schema, 0, -2);
              $schema .= ';' . "\n\n";
              fputs($fp, $schema);
              $schema = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values ' . "\n";
            }
            $schema .= $tmp_schema;
          }
          if( $write_flag ) {
            $schema = substr($schema, 0, -2);
            $schema .= ';' . "\n\n";
            fputs($fp, $schema);
          }
        }
      }
      fclose($fp);
      return true;
    }

    function save_records($backup_file, $tables_array, $where_array) {
      extract(tep_load('database', 'message_stack'));
      tep_set_time_limit(0);

      if( !is_array($tables_array) || !is_array($where_array) || count($tables_array) != count($where_array) ) return false;

      $fp = fopen($backup_file, 'a');
      if( !$fp ) {
        return false;
      }

      for($p=0, $q=count($tables_array); $p<$q; $p++) {
        $table = $tables_array[$p];
        $table_list = array();

        $fields_array = $db->query_to_array("show fields from " . $table);
        for($i=0, $j=count($fields_array); $i<$j; $i++) {
          $table_list[] = $fields_array[$i]['Field'];
        }

        // dump the data
        $where = $where_array[$p];
        if( !empty($where) ) {
          $where = " where " . $where;
        }

        $data_query = $db->query("select " . implode(',', $table_list) . " from " . $table . $where);
        if( $db->num_rows($data_query) ) {
          $schema = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values ' . "\n";
          $write_flag = false;
          while( $data_array = $db->fetch_array($data_query) ) {
            $tmp_schema = '(';
            $write_flag = true;
            reset($table_list);
            while (list(,$i) = each($table_list)) {
              if (!isset($data_array[$i])) {
                $tmp_schema .= 'NULL, ';
              } elseif (tep_not_null($data_array[$i])) {
                $row = addslashes($data_array[$i]);
                $row = preg_replace("/\n#/", "\n".'\#', $row);

                $tmp_schema .= '\'' . $row . '\', ';
              } else {
                $tmp_schema .= '\'\', ';
              }
            }

            $tmp_schema = preg_replace('/, $/', '', $tmp_schema) . '),' . "\n";
            if( strlen($schema)+strlen($tmp_schema) > $this->max_packet ) {
              $write_flag = false;
              $schema = substr($schema, 0, -2);
              $schema .= ';' . "\n\n";
              fputs($fp, $schema);
              $schema = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values ' . "\n";
            }
            $schema .= $tmp_schema;
          }
          if( $write_flag ) {
            $schema = substr($schema, 0, -2);
            $schema .= ';' . "\n\n";
            fputs($fp, $schema);
          }
        }
      }
      fclose($fp);
      return true;
    }

    function restore_file($backup_file, $display=false) {
      $result = $db->file_exec($backup_file, $display);
      return $result;
    }
  }

?>