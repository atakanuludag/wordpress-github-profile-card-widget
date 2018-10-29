<?php
/**
 * Plugin Name: Widget Github Profile Card
 * Plugin URI: https://www.atknuludag.com
 * Description: Shows your github profile in detail.
 * Version: 1.0.0
 * Author: Atakan Yasin Uludag
 * Author URI: https://github.com/atakanuludag
 * Text Domain: widget-gh-profile-card
 * Domain Path: /languages/
 */


if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

function GHWC_load_widget() {
    register_widget( 'WIDGET_GH_PROFILE_CARD' );
}

add_action( 'widgets_init', 'GHWC_load_widget' );


class WIDGET_GH_PROFILE_CARD extends WP_Widget {

    function __construct() {

        $this->text_domain = 'widget-gh-profile-card';

        add_action( 'wp_enqueue_scripts', array( $this, 'GHWC_plugin_scripts' ) );
        load_plugin_textdomain('widget-gh-profile-card', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

        parent::__construct(
          'widget_github_profile_card',
          __('Github Profile Card', $this->text_domain),
          array( 'description' => __( 'Shows your github profile in detail.', $this->text_domain ) )
        );
    }



    public function GHWC_plugin_scripts() {

      	wp_enqueue_style( 'gh-profile-widget-style', plugins_url('/style.css', __FILE__), false, '1.0.0', 'all');


        if(wp_style_is('font-awesome') === false && wp_style_is('fontawesome') === false){
          wp_enqueue_style( 'fontawesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, '4.7.0', 'all');
        }


    }

    public function GHWC_github_api($username){

        $url = 'https://api.github.com/users/'.$username;
        $args = array(
            'Content-type' => 'application/json',
            'User-Agent'  =>  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        );
        $resp = wp_remote_retrieve_body(wp_remote_get($url,$args));

        return json_decode($resp);
    }

    public function widget( $args, $instance ) {
      $github_username = $instance['github_username'];

      if(empty($github_username)) return;

      $collection = $this->GHWC_github_api($github_username);

      if(!isset($collection->name)) return;

      echo $args['before_widget'];

  		if ( ! empty( $instance['title'] ) ) {
  			echo $args['before_title'] . '<i class="gh-title-icon fa fa-github"></i>'. apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
  		}

      $show_follow_button = $instance['show_follow_button'];
      $show_counter = $instance['show_counter'];
      $show_information = $instance['show_information'];
      $show_desc = $instance['show_desc'];

      $github_profile_url = "https://github.com/".$github_username;

      echo '<div class="github-profile-card">';

        echo '<a href="'.$github_profile_url.'" title="'.__('Follow', $this->text_domain).'" target="_blank">';

          echo '<div class="g-avatar">';
            echo '<img src="'.$collection->avatar_url.'" alt="'.$collection->name.'" />';
          echo '</div>';

          echo '<h1 class="g-names">';
            echo '<span class="g-name">'.$collection->name.'</span>';
            echo '<span class="g-nickname">'.$collection->login.'</span>';
          echo '</h1>';

        echo '</a>';

        if($show_desc === 'true') echo '<p>'.$collection->bio.'</p>';

        if($show_follow_button === 'true') echo '<a class="g-follow" href="'.$github_profile_url.'" title="'.__('Follow', $this->text_domain).'"  target="_blank">'.__('Follow', $this->text_domain).'</a>';

        if($show_counter === 'true'){
          echo '<div class="g-profile-stats">';
            echo '<ul>';
              echo '<li><a href="'.$github_profile_url.'?tab=repositories" title="'.__('Repositories', $this->text_domain).'" target="_blank"><span class="g-stat-title">'.__('Repositories', $this->text_domain).'</span><span class="g-stat-count">'.$collection->public_repos.'</span></a></li>';
              echo '<li><a href="'.$github_profile_url.'?tab=followers" title="'.__('Followers', $this->text_domain).'" target="_blank"><span class="g-stat-title">'.__('Followers', $this->text_domain).'</span><span class="g-stat-count">'.$collection->followers.'</span></a></li>';
            echo '</ul>';
          echo '</div>';
        }

        if($show_information === 'true'){
          echo '<div class="g-details">';
            echo '<ul>';
              echo '<li><i class="fa fa-map-marker"></i>'.$collection->location.'</li>';
              echo '<li><i class="fa fa-link"></i><a href="'.$collection->blog.'" title="'.__('Blog', $this->text_domain).'" target="_blank">'.$collection->blog.'</a></li>';
            echo '</ul>';
          echo '</div>';
        }






        echo '</div>';


    		echo $args['after_widget'];



    }

    public function GHWC_formCheckbox($data){
        if($data === 'true'){
          return 'checked';
        }
        return '';
    }


    public function form( $instance ) {

      $title = !empty( $instance['title'] ) ? $instance['title'] : __( 'Github Profile', $this->text_domain );
      $github_username = $instance['github_username'];

      $show_follow_button = !empty( $instance['show_follow_button'] ) ? $instance['show_follow_button'] : 'true';
      $show_counter = !empty( $instance['show_counter'] ) ? $instance['show_counter'] : 'true';
      $show_information = !empty( $instance['show_information'] ) ? $instance['show_information'] : 'true';
      $show_desc = !empty( $instance['show_desc'] ) ? $instance['show_desc'] : 'true';

      ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', $this->text_domain ); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
      </p>

      <p>
        <label for="<?php echo $this->get_field_id( 'github_username' ); ?>"><?php _e( 'Github Username', $this->text_domain ); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'github_username' ); ?>" name="<?php echo $this->get_field_name( 'github_username' ); ?>" type="text" value="<?php echo esc_attr( $github_username ); ?>" />
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_follow_button' ); ?>" name="<?php echo $this->get_field_name( 'show_follow_button' ); ?>" value="true" <?php echo $this->GHWC_formCheckbox($show_follow_button); ?>>
        <label for="<?php echo $this->get_field_id( 'show_follow_button' ); ?>"><?php _e( 'Show Follow Button ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_counter' ); ?>" name="<?php echo $this->get_field_name( 'show_counter' ); ?>" value="true" <?php echo $this->GHWC_formCheckbox($show_counter); ?>>
        <label for="<?php echo $this->get_field_id( 'show_counter' ); ?>"><?php _e( 'Show Counter ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_information' ); ?>" name="<?php echo $this->get_field_name( 'show_information' ); ?>" value="true" <?php echo $this->GHWC_formCheckbox($show_information); ?>>
        <label for="<?php echo $this->get_field_id( 'show_information' ); ?>"><?php _e( 'Show Information ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>" value="true" <?php echo $this->GHWC_formCheckbox($show_desc); ?>>
        <label for="<?php echo $this->get_field_id( 'show_desc' ); ?>"><?php _e( 'Show Description ?', $this->text_domain ); ?></label>
      </p>

      <?php
    }


    public function update( $new_instance, $old_instance ) {
      $instance = array();
      $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
      $instance['github_username'] = ( ! empty( $new_instance['github_username'] ) ) ? strip_tags( $new_instance['github_username'] ) : '';

      if($new_instance['show_follow_button'] === 'true'){
        $instance['show_follow_button'] = 'true';
      } else {
        $instance['show_follow_button'] = 'false';
      }

      if($new_instance['show_counter'] === 'true'){
        $instance['show_counter'] = 'true';
      } else {
        $instance['show_counter'] = 'false';
      }

      if($new_instance['show_information'] === 'true'){
        $instance['show_information'] = 'true';
      } else {
        $instance['show_information'] = 'false';
      }

      if($new_instance['show_desc'] === 'true'){
        $instance['show_desc'] = 'true';
      } else {
        $instance['show_desc'] = 'false';
      }


      return $instance;
    }
}
