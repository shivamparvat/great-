<?php
/*-----------------------------------------------------------------------------------

	Plugin Name: MyThemeShop Facebook Like Box
	Description: A widget for displaying Facebook Like Box.
	Version: 1.1

-----------------------------------------------------------------------------------*/
add_action('widgets_init', 'facebook_like_load_widgets');

function facebook_like_load_widgets()
{
	register_widget('Facebook_Like_Widget');
}

class Facebook_Like_Widget extends WP_Widget {
	
	function __construct()
	{
		$widget_ops = array('classname' => 'facebook_like', 'description' => __('Add Facebook Like Box.', 'framework' ));

		$control_ops = array('id_base' => 'facebook-like-widget');

		parent::__construct('facebook-like-widget', __('FB Like Box', 'framework' ), $widget_ops, $control_ops);

	}
	
	function widget($args, $instance)
	{
		extract($args);

		$title = apply_filters('widget_title', $instance['title']);
		$page_url = $instance['page_url'];
		$width = $instance['width'];
		$show_faces = isset($instance['show_faces']) ? 'true' : 'false';
		$show_stream = isset($instance['show_stream']) ? 'true' : 'false';
		$show_header = isset($instance['show_header']) ? 'true' : 'false';
		$hide_cover = isset($instance['show_header']) ? 'false' : 'true';
		$height = '65';
		
		if($show_faces == 'true') {
			$height = '239';
		}
		
		if($show_header == 'true') {
			$height = '264';
		}

		if($show_stream == 'true') {
			$height = '600';
		}
		
		echo $before_widget;

		if($title) {
			echo $before_title.$title.$after_title;
		}

		if($page_url): ?>
			<div class="fb-page" data-href="<?php echo esc_url( $page_url ); ?>" data-width="<?php echo esc_attr( $width ); ?>" data-hide-cover="<?php echo esc_attr( $hide_cover ); ?>" data-show-facepile="<?php echo esc_attr( $show_faces ); ?>" data-show-posts="<?php echo esc_attr( $show_stream ); ?>"></div>
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.7&appId=194176677361404";
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
		<?php endif;
		
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['page_url'] = $new_instance['page_url'];
		$instance['width'] = $new_instance['width'];
		$instance['show_faces'] = $new_instance['show_faces'];
		$instance['show_stream'] = $new_instance['show_stream'];
		$instance['show_header'] = $new_instance['show_header'];
		
		return $instance;
	}

	function form($instance)
	{
		$defaults = array('title' => __('Find us on Facebook', 'framework' ), 'page_url' => '', 'width' => '300', 'show_faces' => 'on', 'show_stream' => false, 'show_header' => 'on');
		$instance = wp_parse_args((array) $instance, $defaults); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'framework' ); ?>:</label>
			<input type="text" class="widefat" style="width: 216px;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('page_url'); ?>"><?php _e('Facebook Page URL', 'framework' ); ?>:</label>
			<input type="text" class="widefat" style="width: 216px;" id="<?php echo $this->get_field_id('page_url'); ?>" name="<?php echo $this->get_field_name('page_url'); ?>" value="<?php echo esc_url( $instance['page_url'] ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', 'framework' ); ?>:</label>
			<input type="text" class="widefat" style="width: 40px;" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo esc_attr( $instance['width'] ); ?>" />
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_faces'], 'on'); ?> id="<?php echo $this->get_field_id('show_faces'); ?>" name="<?php echo $this->get_field_name('show_faces'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_faces'); ?>"><?php _e('Show faces', 'framework' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_stream'], 'on'); ?> id="<?php echo $this->get_field_id('show_stream'); ?>" name="<?php echo $this->get_field_name('show_stream'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_stream'); ?>"><?php _e('Show stream', 'framework' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_header'], 'on'); ?> id="<?php echo $this->get_field_id('show_header'); ?>" name="<?php echo $this->get_field_name('show_header'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_header'); ?>"><?php _e('Show facebook header', 'framework' ); ?></label>
		</p>
	<?php
	}
}
?>