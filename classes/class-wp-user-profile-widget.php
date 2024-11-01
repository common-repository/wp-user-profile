<?php
class WP_User_Profile_Widget extends WP_Widget {

  public function __construct() {

    $this->set_datas();

    parent::__construct(
      'wp-user-profile',
      'WP User Profile',
      array('description' => __('Show User Profile', $this->textdomain))
    );
  }

  private function set_datas() {
    $datas = get_file_data(plugin_dir_path(__FILE__) . '../wp-user-profile.php', array(
      'version' => 'Version',
      'textdomain' => 'Text Domain',
      'domainpath' => 'Domain Path'
    ));

    $this->version = $datas['version'];
    $this->textdomain = $datas['textdomain'];
    $this->domainpath = $datas['domainpath'];
  }

  // 出力
  public function widget($args, $instance) {

    echo $args['before_widget'];

    if(!empty($instance['title'])) {
      $html .= $title = $args['before_title'];
      $html .= apply_filters('widget_title', $instance['title']);
      $html .= $args['after_title'];
      echo $html;
    }

    $options = get_option('wppf-setting');

    if($instance['user'] === 'auto') {
      $user_id = $options['user'];

    } else {
      $user_id = $instance['user'];
    }

    wp_user_profile($user_id);

    echo $args['after_widget'];
  }

  // ウィジェット管理画面
  public function form($instance) {

    $title = !empty($instance['title']) ? $instance['title'] : '';
    $user_id = $instance['user'];
    $users = get_users();
    $options = get_option('wppf-setting');
    $option_user_id = $options['user'];

    $html = '<p>';
    $html .= sprintf('<label for="%s">%s:</label>', $this->get_field_id('title'), __('Title', $this->textdomain));
    $html .= sprintf('<input type="text" class="widefat" id="%s" name="%s" value="%s">', $this->get_field_id('title'), $this->get_field_name('title'), $title);
    $html .= '</p>';

    $html .= '<p>';
    $html .= sprintf('<label for="%s">%s</label>', $this->get_field_id('user'), __('User to display', $this->textdomain));
    $html .= sprintf('<select id="%s" name="%s">', $this->get_field_id('user'), $this->get_field_name('user'));

    foreach($users as $user) {
      $html .= sprintf('<option value="%d"%s>%s</option>', $user->data->ID, selected($user_id, $user->data->ID, false), get_the_author_meta('nickname', $user->data->ID));
    }

    $html .= sprintf('<option value="%s"%s>%s</option>', 'auto', selected($user_id, 'auto', false), __('Auto', $this->textdomain));

    $html .= '</select>';
    $html .= '</p>';

    echo $html;
  }

  public function update($new_instance, $old_instance) {
    $new_instance['title'] = (!empty( $new_instance['title'])) ? strip_tags($new_instance['title']) : '';

    return $new_instance;
  }
}
