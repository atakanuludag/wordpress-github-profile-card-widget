<?php
/**
 * Plugin Name: Github Profile Widget Card
 * Plugin URI: https://www.atknuludag.com
 * Description: Github profile card widget.
 * Version: 1.0.0
 * Author: Atakan Yasin Uludag
 * Author URI: https://www.atknuludag.com
 * Text Domain: gh-profile-plugin
 * Domain Path: /languages/
 */


if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

function load_widget() {
    register_widget( 'GH_PROFILE_CARD_WIDGET' );
}

add_action( 'widgets_init', 'load_widget' );


class GH_PROFILE_CARD_WIDGET extends WP_Widget {

    function __construct() {

        $this->text_domain = 'gh-profile-plugin';

        add_action( 'wp_enqueue_scripts', array( $this, 'plugin_scripts' ) );
        load_plugin_textdomain('gh-profile-plugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

        parent::__construct(
          'github_profile_widget_card',
          __('Github Profile Card', $this->text_domain),
          array( 'description' => __( 'Github profile card widget.', $this->text_domain ) )
        );
    }



    public function plugin_scripts() {

      	wp_enqueue_style( 'gh-profile-widget-style', plugins_url('/style.css', __FILE__), false, '1.0.0', 'all');


        if(wp_style_is('font-awesome') === false && wp_style_is('fontawesome') === false){
          wp_enqueue_style( 'fontawesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, '4.7.0', 'all');
        }


    }

    public function github_api($username){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => 'https://api.github.com/users/'.$username,
          CURLOPT_HTTPHEADER => array('Content-type: application/json'),
          CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
        ));

        $resp = curl_exec($curl);

        curl_close($curl);
        return json_decode($resp);
    }

    public function widget( $args, $instance ) {
      $github_username = $instance['github_username'];

      if(isset($github_username)){

        echo $args['before_widget'];

    		if ( ! empty( $instance['title'] ) ) {
    			echo $args['before_title'] . '<i class="gh-title-icon fa fa-github"></i>'. apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
    		}

        $show_follow_button = $instance['show_follow_button'];
        $show_counter = $instance['show_counter'];
        $show_information = $instance['show_information'];
        $show_desc = $instance['show_desc'];

        $collection = $this->github_api($github_username);
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

    }

    public function formCheckbox($data){
        if($data === 'true'){
          return 'checked';
        }
        return '';
    }


    public function form( $instance ) {

      $title = !empty( $instance['title'] ) ? $instance['title'] : __( 'Github Profile', $this->text_domain );
      $github_username = $instance['github_username'];

      $show_follow_button = $instance['show_follow_button'];
      $show_counter = $instance['show_counter'];
      $show_information = $instance['show_information'];
      $show_desc = $instance['show_desc'];


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
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_follow_button' ); ?>" name="<?php echo $this->get_field_name( 'show_follow_button' ); ?>" value="true" <?php echo $this->formCheckbox($show_follow_button); ?>>
        <label for="<?php echo $this->get_field_id( 'show_follow_button' ); ?>"><?php _e( 'Show Follow Button ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_counter' ); ?>" name="<?php echo $this->get_field_name( 'show_counter' ); ?>" value="true" <?php echo $this->formCheckbox($show_counter); ?>>
        <label for="<?php echo $this->get_field_id( 'show_counter' ); ?>"><?php _e( 'Show Counter ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_information' ); ?>" name="<?php echo $this->get_field_name( 'show_information' ); ?>" value="true" <?php echo $this->formCheckbox($show_information); ?>>
        <label for="<?php echo $this->get_field_id( 'show_information' ); ?>"><?php _e( 'Show Information ?', $this->text_domain ); ?></label>
      </p>

      <p>
        <input type="checkbox" id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>" value="true" <?php echo $this->formCheckbox($show_desc); ?>>
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
