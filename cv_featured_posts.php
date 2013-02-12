<?php /*
Plugin Name: Caava Featured Posts
Plugin URI: https://github.com/drrobotnik/cv-featured-post
Description: Plugin purposely has a built-in dependency on a headless version of Advanced Custom Fields. You'll need to add add_filter('acf_settings', 'your_acf_settings'); in your functions file in order to enable the plugins options page. To learn more checkout the plugin page on github.
Version: 1.0
Author: Brandon Lavigne
Author URI: http://caavadesign.com
*/

if(!function_exists('get_fields'))
require_once('acf/acf-lite.php');


if(!function_exists('vt_resize'))
	require_once('vt-resize.php');

if(!function_exists('cv_post_first_image')){
	function cv_post_first_image() {
		global $post, $posts;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		if(count($matches [1]))$first_img = $matches [1] [0];
		return $first_img;
	}
}

add_action( 'widgets_init', 'caava_featured_widget' );


function caava_featured_widget() {
	register_widget( 'Caava_Featured_Widget' );
}

class Caava_featured_Widget extends WP_Widget {

	function Caava_Featured_Widget() {
		$widget_ops = array( 'classname' => 'cv_widget_featured', 'description' => 'featured posts widget.' );
		$control_ops = array( 'id_base' => 'cv-widget-featured' );
		$this->WP_Widget( 'cv-widget-featured', 'CV Featured Posts', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$sani_title = isset( $instance['title'] ) ? sanitize_text_field($instance['title']) : '';
		$title = apply_filters('widget_title', $sani_title );
		$intro = isset( $instance['intro'] ) ? sanitize_text_field($instance['intro']) : '';
		$post_count = isset( $instance['post_count'] ) ? sanitize_text_field($instance['post_count']) : '5';



		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		$featured_posts = get_field('featured_posts','options');
		$image_dimensions = (get_field('image_dimensions','options')) ? get_field('image_dimensions','options')[0] : false;
		$optional_display = (get_field('optional_display','options')) ? get_field('optional_display','options')[0] : false;

		?>
			<div class="content">
				<?php if(!empty($intro)){ ?><p><?php echo wpautop( $intro ); ?></p><?php } ?>
					<?php
					$args = array('posts_per_page'=>$post_count,'post__in' => $featured_posts );
					$cv_featured = new WP_Query( $args );
					$output_html = '';
					if ( $cv_featured->have_posts() ) { 
						while ( $cv_featured->have_posts() ) { 
							$cv_featured->the_post();

							$crop = array();

					
							if( !has_post_thumbnail() ){
								$http_addr = home_url('/');
								$found_img = cv_post_first_image();

								$thumb_id = null;
								$img_url = str_replace($http_addr, "", $found_img);
								if( !is_file( $img_url ) ){
									break;
								}
							}else{
								$thumb_id = get_post_thumbnail_id();
								$img_url = null;
							}

							
								$crop = vt_resize($thumb_id, $img_url, $image_dimensions['width'], $image_dimensions['height'], $image_dimensions['crop']);

							$output_html .= '<div class="cv-featured"><a href="'.get_post_permalink().'" class="cv-image">';
							$output_html .= '<img src="'.home_url('/').$crop["url"].'" width="'.$image_dimensions['width'].'" height="'.$image_dimensions['height'].'" /></a>';

							if($optional_display['show_date'])
								$output_html .= '<div class="cv-date"><a href="'.get_post_permalink().'">'.get_the_date( $optional_display['date_format'] ).'</a></div>';
							if($optional_display['show_title'])
								$output_html .= '<div class="cv-title"><a href="'.post_permalink().'">'. get_the_title().'</a></div>';
							
							$output_html .= '</div>';
						}
					}
					echo $output_html;
					 ?>
				</div>
		<?php echo $after_widget;
	}

	//Update the widget

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		//Strip tags from title and name to remove HTML 
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['intro'] = (isset($new_instance['intro'])) ? sanitize_text_field( $new_instance['intro'] ) : '';

		$instance['post_count'] = isset( $new_instance['post_count'] ) ? intval($new_instance['post_count']) : 5;

		return $instance;
	}

	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => 'featured Posts','intro' => '','post_count' => '5');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		Select Term
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" style="width:100%;" />
		<label for="<?php echo $this->get_field_id( 'intro' ); ?>">Intro:</label>
		<input id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" value="<?php echo $instance['intro']; ?>" class="widefat" style="width:100%;" />
		</p>
		<p><label for="<?php echo $this->get_field_id( 'post_count' ); ?>">Post Count:</label>
		<input id="<?php echo $this->get_field_id( 'post_count' ); ?>" name="<?php echo $this->get_field_name( 'post_count' ); ?>" value="<?php echo $instance['post_count']; ?>" class="widefat" style="width:10%;" /></p>
	<?php
	}
}

if(function_exists("register_field_group")){
	function cv_acf_additional_settings( $options ){
		// set options page structure    
		$options_pages = $options['options_page']['pages'];
		array_push($options_pages, 'Featured Posts');



		$options['options_page']['pages'] = $options_pages;
		return $options;
	}

	add_filter('acf_settings', 'cv_acf_additional_settings',100);

	register_field_group(array (
		'id' => '5119695e4fff7',
		'title' => 'Featured Posts',
		'fields' => 
		array (
			0 =>
			array (
				'key' => 'featured_posts',
				'label' => 'Featured Posts',
				'name' => 'featured_posts',
				'type' => 'relationship',
				'instructions' => 'Select the posts to be displayed as within the blog sidebar',
				'required' => '0',
				'post_type' => 
				array (
					0 => 'post',
				),
				'taxonomy' => 
				array (
					0 => 'all',
				),
				'max' => 5,
			),
			1 => 
			array (
				'key' => 'field_12',
				'label' => 'Image Dimensions',
				'name' => 'image_dimensions',
				'type' => 'repeater',
				'order_no' => 0,
				'instructions' => 'Enter the dimensions of the Featured Posts Thumbnail.',
				'required' => 0,
				'conditional_logic' => 
				array (
					'status' => 0,
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
						),
					),
					'allorany' => 'all',
				),
				'sub_fields' => 
				array (
					'field_13' => 
					array (
						'label' => 'Width',
						'name' => 'width',
						'type' => 'number',
						'instructions' => '',
						'column_width' => '',
						'default_value' => 177,
						'order_no' => 0,
						'key' => 'field_13',
					),
					'field_14' => 
					array (
						'label' => 'Height',
						'name' => 'height',
						'type' => 'number',
						'instructions' => '',
						'column_width' => '',
						'default_value' => 123,
						'order_no' => 1,
						'key' => 'field_14',
					),
					'field_15' => 
					array (
						'label' => 'Crop',
						'name' => 'crop',
						'type' => 'true_false',
						'instructions' => 'WP crops from the center of the image. Meaning whichever is hit first height or width, will be chopped from the ends. Does a pretty good job for consistency.',
						'column_width' => '',
						'message' => 'Crop the image?',
						'order_no' => 2,
						'key' => 'field_15',
					),
				),
				'row_min' => 1,
				'row_limit' => 1,
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
			2 => 
			array (
				'key' => 'field_16',
				'label' => 'Optional Display',
				'name' => 'optional_display',
				'type' => 'repeater',
				'order_no' => 0,
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 
				array (
					'status' => 0,
					'rules' => 
					array (
						0 => 
						array (
							'field' => 'null',
							'operator' => '==',
						),
					),
					'allorany' => 'all',
				),
				'sub_fields' => 
				array (
					'field_17' => 
					array (
						'label' => 'Show Title',
						'name' => 'show_title',
						'type' => 'true_false',
						'instructions' => '',
						'column_width' => '',
						'order_no' => 0,
						'key' => 'field_17',
					),
					'field_18' => 
					array (
						'label' => 'Show Date',
						'name' => 'show_date',
						'type' => 'true_false',
						'instructions' => '',
						'column_width' => '',
						'order_no' => 1,
						'key' => 'field_18',
					),
					'field_19' => 
					array (
						'label' => 'Date Format',
						'name' => 'date_format',
						'type' => 'text',
						'instructions' => '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>',
						'default_value' => get_option( 'date_format' ),
						'column_width' => '',
						'order_no' => 1,
						'key' => 'field_19',
					)
				),
				'row_min' => 1,
				'row_limit' => 1,
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
		),
		'location' => 
		array (
			'rules' => 
			array (
				0 => 
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'acf-options-featured-posts',
					'order_no' => 0,
				),
			),
			'allorany' => 'all',
		),
		'options' => 
		array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => 
			array (
			),
		),
		'menu_order' => 0,
	));
}