<?php
/*
Plugin Name: WP User Profile
Plugin URI: http://e-joint.jp/works/wp-user-profile/
Description: A WordPress plugin that makes author's profile easily.
Version: 0.1.0
Author: e-JOINT.jp
Author URI: http://e-joint.jp
Text Domain: wp-user-profile
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2018 e-JOINT.jp (email : mail@e-joint.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'classes/class-wp-user-profile-widget.php';

class WP_User_Profile {
  public $options;
  private $version;
  public $textdomain;
  private $domainpath;

  public function __construct(){

    $this->set_datas();
    $this->options = get_option('wppf-setting');

    // 翻訳ファイルの読み込み
    add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    // 設定画面を追加
    add_action('admin_menu', array($this, 'add_plugin_page'));
    // 設定画面の初期化
    add_action('admin_init', array($this, 'page_init'));
    // ウィジェットの初期化
    add_action('widgets_init', array($this, 'register_widget'));
    add_action('wp_enqueue_scripts', array($this, 'add_styles'));


    add_filter('user_contactmethods', array($this, 'user_contactmethods'));
  }

  public function load_plugin_textdomain() {
    load_plugin_textdomain($this->textdomain, false, dirname(plugin_basename(__FILE__)) . $this->domainpath);
  }

  private function set_datas() {
    $datas = get_file_data(__FILE__, array(
      'version' => 'Version',
      'textdomain' => 'Text Domain',
      'domainpath' => 'Domain Path'
    ));

    $this->version = $datas['version'];
    $this->textdomain = $datas['textdomain'];
    $this->domainpath = $datas['domainpath'];
  }

  public function user_contactmethods($ucm) {
    $ucm['mail'] = __('Mail address', $this->textdomain) . '<br><small>WP User Profile</small>';
    $ucm['twitter'] = 'Twitter' . __('Username', $this->textdomain) . ' <small> (' . __('Without @', $this->textdomain) . ')</small><br><small>WP User Profile</small>';
    $ucm['facebook'] = 'Facebook ' . __('Username', $this->textdomain) . '<br><small>WP User Profile</small>';
    $ucm['linkedin'] = 'LinkedIn ID<br><small>WP User Profile</small>';
    $ucm['google-plus'] = 'Google Plus ID<small> (' . __('Without +', $this->textdomain) . ')</small><br><small>WP User Profile</small>';
    $ucm['youtube'] = 'YouTube'  . __('Channel ID', $this->textdomain) . '<br><small>WP User Profile</small>';
    $ucm['instagram'] = 'Instagram ' . __('Username', $this->textdomain) . '<br><small>WP User Profile</small>';
    $ucm['pinterest'] = 'Pinterest ' . __('Username', $this->textdomain) . '<br><small>WP User Profile</small>';
    $ucm['github'] = 'Github ' . __('Username', $this->textdomain) . '<br><small>WP User Profile</small>';
    // $ucm['skype'] = __('Skype Username', $this->textdomain);
    // $ucm['line'] = __('LINE ID', $this->textdomain);

    return $ucm;
  }


  // 設定画面を追加
  public function add_plugin_page() {
    add_options_page(
      __('WP User Profile', $this->textdomain),
      __('WP User Profile', $this->textdomain),
      'manage_options',
      'wppf-setting',
      array($this, 'create_admin_page')
    );
  }

  // 設定画面を生成
  public function create_admin_page() { ?>
    <div class="wrap">
      <h2>WP User Profile</h2>
      <?php
      global $parent_file;
      if($parent_file != 'options-general.php') {
        require(ABSPATH . 'wp-admin/options-head.php');
      }
      ?>

      <form method="post" action="options.php">
      <?php
        settings_fields('wppf-setting');
        do_settings_sections('wppf-setting');
        submit_button();
      ?>
      </form>

      <p><?php echo __('For details of setting, please see', $this->textdomain); ?> <a href="http://e-joint.jp/works/wp-user-profile/">http://e-joint.jp/works/wp-user-profile/</a></p>
    </div>
  <?php
  }

  // 設定画面の初期化
  public function page_init(){
    register_setting('wppf-setting', 'wppf-setting');
    add_settings_section('wppf-setting-section-id', '', '', 'wppf-setting');

    add_settings_field(
      'nocss',
      __('Do not use plugin\'s CSS', $this->textdomain),
      array($this, 'nocss_callback'),
      'wppf-setting',
      'wppf-setting-section-id'
    );

    add_settings_field(
      'default_user',
      __('Default user', $this->textdomain),
      array($this, 'user_callback'),
      'wppf-setting',
      'wppf-setting-section-id'
    );
  }

  public function register_widget() {
    register_widget('WP_User_Profile_Widget');
  }

  public function user_callback() {

    $users = get_users();

    $html = '<select name="wppf-setting[user]">';
    foreach($users as $user) {
      $html .= sprintf('<option value="%d"%s>%s</option>', $user->data->ID, selected($this->options['user'], $user->data->ID, false), get_the_author_meta('nickname', $user->data->ID));
    }
    $html .= '</select>';
    $html .= ' <small>' . __('Select the user to display when using outside the WordPress loop.', $this->textdomain) . '</small>';

    echo $html;
  }

  public function nocss_callback() {
    $checked = isset($this->options['nocss']) ? checked($this->options['nocss'], 1, false) : '';
    ?><input type="checkbox" id="nocss" name="wppf-setting[nocss]" value="1"<?php echo $checked; ?>><?php
  }

  // スタイルシートの追加
  public function add_styles() {
    if(!$this->options['nocss']) {
      wp_enqueue_style('wppf', plugins_url('assets/css/wp-user-profile.css', __FILE__), array(), $this->version, 'all');
    }
  }


}

$wppf = new WP_User_Profile();

abstract class WP_User_Profile_Icon {
  public function __construct($value = '') {
    $this->url = $this->api . $value;
  }
}

class WP_User_Profile_Icon_Twitter extends WP_User_Profile_Icon {
  public $name = 'twitter';
  public $api = 'https://twitter.com/';
}

class WP_User_Profile_Icon_Facebook extends WP_User_Profile_Icon {
  public $name = 'facebook';
  public $api = 'https://www.facebook.com/';
}

class WP_User_Profile_Profile {

  private $icons = array(
    'user_url' => '',
    'mail' => 'mailto:',
    'twitter' => 'https://twitter.com/',
    'facebook' => 'https://www.facebook.com/',
    'linkedin' => 'https://www.linkedin.com/in/',
    'google-plus' => 'https://plus.google.com/',
    'youtube' => 'https://www.youtube.com/channel/',
    'instagram' => 'https://www.instagram.com/',
    'pinterest' => 'https://www.pinterest.com/',
    'github' => 'https://github.com/'

    // 'skype' => 'https://',
    // 'line' => 'https://'
  );

  public function __construct($id = null) {
    $this->id = is_null($id) ? $this->get_id() : $id;
  }

  private function icon_name($name) {
    switch ($name) {

      // case 'user_email':
      //   $name = 'mail';
      //   break;

      case 'user_url':
        $name = 'web';
        break;
    }

    return $name;
  }

  private function list_item($name, $api) {

    if($meta = get_the_author_meta($name, $this->id)) {
      return sprintf('<li class="wppf-profile__icons__item"><a href="%s%s" class="wppf-profile__icons__a"><i class="icon-%s"></i></a></li>', $api, $meta, $this->icon_name($name));
    }
  }

  private function get_id() {

    if(in_the_loop()) {
      return null;

    } else {

      $options = get_option('wppf-setting');
      $user = $options['user'];

      if($user) {
        return $user;

      } else {
        $users = get_users();
        $data = $users[0]->data;
        $id = $data->ID;
        return $id;
      }
    }
  }

  public function icons() {

    $html = '<ul class="wppf-profile__icons">';

    foreach ($this->icons as $name => $api) {
      $html .= $this->list_item($name, $api);
    }

    $html .= '</ul>';

    return $html;
  }

  public function name() {
    return get_the_author_meta('nickname', $this->id);
  }

  public function description() {
    return get_the_author_meta('description', $this->id);
  }

  public function avatar() {
    return get_avatar(get_the_author_meta('ID', $this->id), 150);
  }
}

function wp_user_profile($id = null) {
  $name = 'wppf-template.php';
  $custom = locate_template($name);
  $default = dirname(__FILE__) . '/template/' . $name;

  $wp_user_profile = new WP_User_Profile_Profile($id);

  if(file_exists($custom)) {
    include $custom;
  } else {
    include $default;
  }
}
