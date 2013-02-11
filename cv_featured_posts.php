<?php /*
Plugin Name: Caava Sticky Posts
Plugin URI: http://caavadesign.com
Description: Sticky posts highlighting the featured image in that post.
Version: 1.0
Author: Brandon Lavigne
Author URI: http://caavadesign.com
*/

require_once('vt-resize.php');

add_action( 'widgets_init', 'caava_sticky_widget' );


function caava_sticky_widget() {
	register_widget( 'Caava_Sticky_Widget' );
}

class Caava_Sticky_Widget extends WP_Widget {

	function Caava_Sticky_Widget() {
		$widget_ops = array( 'classname' => 'cv_widget_sticky', 'description' => 'sticky posts widget.' );
		$control_ops = array( 'id_base' => 'cv-widget-sticky' );
		$this->WP_Widget( 'cv-widget-sticky', 'CV Sticky Posts', $widget_ops, $control_ops );
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
		?>
			<div class="content">
				<?php if(!empty($intro)){ ?><p><?php echo wpautop( $intro ); ?></p><?php } ?>
					<?php
					wp_reset_query();
					wp_reset_postdata();
					$args = array('posts_per_page'=>$post_count,'post__in' => get_option( 'sticky_posts' ));
					$cv_sticky = new WP_Query( $args );
					if ( $cv_sticky->have_posts() ) : while ( $cv_sticky->have_posts() ) : $cv_sticky->the_post();
					if( has_post_thumbnail() ){
						?><div class="cv-sticky"><a href="<?php echo post_permalink(); ?>"><?php
						$crop = vt_resize(get_post_thumbnail_id(), null, 177, 123, 1);
						echo "<img src='{$crop["url"]}' />";
						?></a>
						<div class="cv-date"><a href="<?php echo post_permalink(); ?>"><?php echo get_the_date( ); ?></a></div><?php
						?><div class="cv-title"><a href="<?php echo post_permalink(); ?>"><?php the_title(); ?></a></div><?php 
						?></div><?php
					}

					endwhile;
					endif; ?>
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
		$defaults = array( 'title' => 'Sticky Posts','intro' => '','post_count' => '5');
		$instance = wp_parse_args( (array) $instance, $defaults );

		 ?>
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