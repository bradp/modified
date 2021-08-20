<?php
/**
 * Plugin Name: Modified
 * Description: Quickly and easily view the last modified posts.
 * Version:     1.1.0
 * Author:      Brad Parbs
 * Author URI:  https://bradparbs.com/
 * License:     GPLv2
 * Text Domain: modified
 * Domain Path: /lang/
 *
 * @package modified
 */

namespace Modified;

use WP_Query;

add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\\add_dashboard_widget' );

/**
 * Add new dashboard widget with list of recently modified posts.
 */
function add_dashboard_widget() {
	$name = sprintf(
		'<span><span class="dashicons %s" style="padding-right: 10px"></span>%s</span>',
		apply_filters( 'modified_widget_icon', 'dashicons-welcome-write-blog' ),
		apply_filters( 'modified_widget_title', esc_attr__( 'Recently Modified', 'modified' ) )
	);

	wp_add_dashboard_widget( 'modified', $name, __NAMESPACE__ . '\\dashboard_widget' );
}

/**
 * Add dashboard widget for recently modified posts.
 */
function dashboard_widget() {
	$post_types = apply_filters( 'modified_post_types_to_show', get_post_types() );
	$query_args = apply_filters( 'modified_widget_query_args', [
		'post_type'      => $post_types,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'posts_per_page' => 25,
		'no_found_rows'  => true,
	] );

	$posts     = new WP_Query( $query_args );
	$modified = get_modified_posts( $posts );

	printf(
		'<div id="modified-posts-widget-wrapper">
			<div id="modified-posts-widget" class="activity-block" style="padding-top: 0;">
				<ul>%s</ul>
			</div>
		</div>',
		display_modified_in_widget( $modified ) // phpcs:ignore
	);
}

/**
 * Get the recently modified posts to display in the dashboard widget.
 *
 * @param WP_Query $posts WP_Query object.
 *
 * @return array Array of recently modified posts.
 */
function get_modified_posts( $posts ) {
	$modified = [];

	if ( $posts->have_posts() ) {
		while ( $posts->have_posts() ) {
			$posts->the_post();

			$add_to_modified = apply_filters( 'schedules_show_in_widget', [
				'ID'      => get_the_ID(),
				'title'   => get_the_title(),
				'date'    => gmdate( 'F j, g:ia', get_the_time( 'U' ) ),
				'preview' => get_preview_post_link(),
			] );

			if ( isset( $add_to_modified ) ) {
				$modified[] = $add_to_modified;
			}
		}
	}

	return $modified;
}

/**
 * Display recently modified posts in widget.
 *
 * @param array $posts Post data.
 *
 * @return string Output of post data.
 */
function display_modified_in_widget( $posts ) {
	$output = '';

	foreach ( $posts as $post ) {
		$output .= sprintf(
			'<li><em style="%4$s">%1$s</em> <a href="%2$s">%3$s</a></li>',
			isset( $post['date'] ) ? $post['date'] : '',
			isset( $post['preview'] ) ? $post['preview'] : '',
			isset( $post['title'] ) ? $post['title'] : '',
			'display: inline-block; margin-right: 5px; min-width: 125px; color: #646970;'
		);
	}

	return $output;
}
