<?php
/*
//----------------------------------------------------------------------------
// Copyright (c) 2006-2010 Asymmetric Software. Innovation & Excellence.
// Author: Mark Samios
// http://www.asymmetrics.com
//----------------------------------------------------------------------------
// Admin Plugin: Install Class for CSS Menu
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
  class install_css_menu extends plug_manager {
    var $options_array = array(
      'template' => 'stock'
    );

    // Compatibility constructor
    function install_css_menu() {
      parent::plug_manager();
      // Never set the key member
      $this->title = 'CSS Menu';
      $this->author = 'Mark Samios';
      $this->version = '1.00';
      $this->framework = '1.11';
      $this->help = ''; // Brief description of a plugin or use a file
      tep_read_contents($this->admin_path.'readme.txt', $this->help);
      $this->front = 1;
      $this->back = 0;
      $this->status = 1;

      $this->template_path = 'front/templates/';
      // The array of files that operate on the web-front
      // Left(Key)     => Source File with Path relative to the plugins directory (to copy file from)
      // Right(Value)  => Destination Path and File (to copy source file to)
      $this->files_array = array(
        'front/css_menu.php'             => $this->web_path.'css_menu.php',
      );

      // Common Template filenames
      $this->template_array = array(
        'css_menu.css'                   => $this->web_template_path.'css_menu.css',
        'css_menu.tpl'                   => $this->web_template_path.'css_menu.tpl',
        'words_space.gif'                => $this->web_template_path.'words_space.gif',
        'back.png'                       => $this->web_template_path.'back.png',
      );

      $this->strings = tep_get_strings($this->admin_path . 'strings.php');
    }

    function install() {
      $this->set_posted_template();
      $result = parent::install();
      $this->save_options($this->options_array);
      return $result;
    }

    function uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
      parent::uninstall();
      return true;
    }

    function pre_install() {
      return $this->common_select();
    }
    function pre_copy_front() {
      return $this->common_select();
    }

    function pre_uninstall() {
      $options_array = $this->load_options();
      $this->load_template_files($options_array['template']);
      return true;
    }

    function re_copy_front() {
      $this->set_posted_template();
      return parent::re_copy_front();
    }

    function common_select() {
      $cStrings =& $this->strings;

      $tmp_array = $this->get_templates();
      if( !count($tmp_array) ) return false;

      echo '<div class="comboHeading">' . "\n";
      echo '<b>' . $cStrings->TEXT_SELECT_TEMPLATE . '</b>&nbsp;&nbsp;' . tep_draw_pull_down_menu('template', $tmp_array, $this->options_array['template']) . "\n";
      echo '&nbsp;&nbsp;' . $cStrings->TEXT_ADDITIONAL_TEMPLATE_FILES . "\n";
      echo '</div>' . "\n";
      return true;
    }
  }
?>
