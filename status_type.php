<?php
/*
Plugin Name: Status Update Custom Post Type
Plugin URI: http://blog.andrewshell.org/status-type
Description: Adds a Status Update custom post type
Version: 0.1.2
Author: Andrew Shell
Author URI: http://blog.andrewshell.org
License: GPL2

Copyright 2011  Andrew Shell  (email : andrew@andrewshell.org)

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

if ( is_admin() ){ // admin actions
  //add_action( 'admin_init', 'status_type_admin_init' );
}
add_action( 'init', 'status_type_init' );

/**
 * Initializes the Status Update custom post type
 */
function status_type_init()
{
  $labels = array(
    'name' => _x( 'Status Updates', 'post type general name' ),
    'singular_name' => _x( 'Status Update', 'post type singular name' ),
    'add_new' => _x( 'Add New', 'status' ),
    'add_new_item' => __( 'Add New Status Update' ),
    'edit_item' => __( 'Edit Status Update' ),
    'new_item' => __( 'New Status Update' ),
    'view_item' => __( 'View Status Update' ),
    'search_items' => __( 'Search Status Updates' ),
    'not_found' => __( 'No status updates found' ),
    'not_found_in_trash' => __( 'No status updates found in Trash' ),
    'parent_item_colon' => '',
    'menu_name' => 'Status Updates',
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'status', 'with_front' => false ),
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields' ),
  );

  // if (get_option('status_type_enabletitle')) {
  //   array_unshift($args['supports'], 'title');
  // }

  register_post_type( 'status', $args );

  add_filter( 'manage_edit-status_columns', 'status_type_add_columns' );
  add_filter( 'request', 'status_type_request' );
  // add_filter( 'the_title', 'status_type_the_title' );
  add_filter( 'wp_insert_post_data', 'status_type_wp_insert_post_data', 99, 2 );

  remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );

  add_action( 'manage_status_posts_custom_column', 'status_type_manage_columns', 10, 1 );
  add_action( 'do_feed_rss2', 'status_type_do_feed_rss2', 10, 1 );
}

/**
 * Defines what columns are displayed on the Status Update admin page
 *
 * @param array $columns
 * @return array
 */
function status_type_add_columns( $columns ) {
  $new_columns = array();
  $new_columns['cb'] = $columns['cb'];
  $new_columns['excerpt'] = _x( 'Status Update', 'column name' );
  $new_columns['comments'] = $columns['comments'];
  $new_columns['date'] = $columns['date'];

  return $new_columns;
}

/**
 * Render new columns on the Status Update admin page
 *
 * @param string $column_name
 */
function status_type_manage_columns($column_name) {
  global $post;
  $edit_link = get_edit_post_link( $post->ID );
  $post_type_object = get_post_type_object( $post->post_type );
  $can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

  if ( "excerpt" == $column_name ) {
    if ( 140 < strlen( $post->post_excerpt ) ) {
      $short_excerpt = esc_html( substr( $post->post_excerpt, 0, 139 ) ) . '&hellip;';
    } else {
      $short_excerpt = esc_html( $post->post_excerpt );
    }
    echo '<strong>';
    if ( $can_edit_post && $post->post_status != 'trash' ) {
      echo '<a class="row-title" href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $short_excerpt ) ) . '">' . $short_excerpt . '</a>';
    } else {
      echo $title;
    };
    _post_states( $post );
    echo '</strong>';
  }
}

/**
 * Automatically fill out fields when submitting a Status Update
 *
 * @param array $data
 * @param array $postarr
 * @return array
 */
function status_type_wp_insert_post_data( $data , $postarr ) {
  if ( 'status' == $data['post_type'] ) {
    // if ( get_option('status_type_enabletitle') ) {
    //   if ( empty( $data['post_title'] ) || 0 == strcmp( 'Auto Draft', $data['post_title'] ) ) {
    //     $data['post_title'] = status_type_title_from_content( $data['post_content'] );
    //   }
    // } elseif ( !empty( $data['post_title'] ) ) {
      $data['post_title'] = '';
    // }
    if ( empty( $data['post_excerpt'] ) ) {
      $data['post_excerpt'] = status_type_excerpt_from_content( $data['post_content'] );
    }
    if ( empty( $data['post_name'] ) || false !== stripos( $data['post_name'], 'auto-draft' ) ) {
      $post_title        = ( empty($data['post_title']) ? status_type_title_from_content( $data['post_content'] ) : $data['post_title'] );
      $data['post_name'] = sanitize_title( $post_title );
      $data['post_name'] = wp_unique_post_slug( $data['post_name'], $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent'] );
    }
  }

  return $data;
}

/**
 * Generate a title from raw html
 * This is a copy of p2_title_from_content
 *
 * @author Automattic
 * @param string $content
 * @return string
 */
function status_type_title_from_content( $content ) {
  $title = status_type_excerpted_title( $content, 8 ); // limit title to 8 full words

  // Try to detect image or video only posts, and set post title accordingly
  if ( empty( $title ) ) {
    if ( preg_match("/<object|<embed/", $content ) )
      $title = __( 'Video Post', 'p2' );
    elseif ( preg_match( "/<img/", $content ) )
      $title = __( 'Image Post', 'p2' );
  }

  return $title;
}

/**
 * Clean up html and parse out the first $word_count words from the text.
 * This is a modified copy of p2_excerpted_title
 *
 * @author Automattic
 * @author Andrew Shell
 * @param string $content
 * @param int $word_count
 * @return string
 */
function status_type_excerpted_title( $content, $word_count ) {
  $content = stripslashes($content);
  if ( preg_match_all( '!<img([^>]*)>!isU', $content, $images ) ) {
    foreach ( array_keys( $images[0] ) as $i ) {
      if ( preg_match( '!alt="(.*)"!isU', $images[1][$i], $matches ) ) {
        $text = $matches[1] . ' ';
      } elseif ( preg_match( '!title="(.*)"!isU', $images[1][$i], $matches ) ) {
        $text = $matches[1] . ' ';
      } else {
        $text = '';
      }

      $content = str_replace( $images[0][$i], $text, $content );
    }
  }

  $content = addslashes($content);
  $content = strip_tags( $content );
  $words = preg_split( '/([\s_;?!\/\(\)\[\]{}<>\r\n\t"]|\.$|(?<=\D)[:,.\-]|[:,.\-](?=\D))/', $content, $word_count + 1, PREG_SPLIT_NO_EMPTY );

  if ( count( $words ) > $word_count ) {
    array_pop( $words ); // remove remainder of words
    $content = implode( ' ', $words );
    $content = $content . '...';
  } else {
    $content = implode( ' ', $words );
  }

  $content = trim( strip_tags( $content ) );

  return $content;
}

/**
 * Clean up html and generate a plain text excerpt
 *
 * @param string $content
 * @return string
 */
function status_type_excerpt_from_content( $content ) {
  $content = stripslashes( $content );

  // Convert images to text

  if ( preg_match_all( '!<img([^>]*)>!isU', $content, $images ) ) {
    foreach ( array_keys( $images[0] ) as $i ) {
      if ( preg_match( '!src="(.*)"!isU', $images[1][$i], $matches ) ) {
        $src = $matches[1];

        if ( preg_match( '!alt="(.*)"!isU', $images[1][$i], $matches ) ) {
          $text = $matches[1] . ' - ';
        } elseif ( preg_match( '!title="(.*)"!isU', $images[1][$i], $matches ) ) {
          $text = $matches[1] . ' - ';
        } else {
          $text = '';
        }

        $content = str_replace( $images[0][$i], $text . $src, $content );
      }
    }
  }

  // Convert links to text

  if ( preg_match_all( '!<a[^>]*href="(.*)"[^>]*>(.*)</a>!isU' , $content, $links ) ) {
    foreach ( array_keys( $links[0] ) as $i ) {
      $content = str_replace( $links[0][$i], $links[2][$i] . ' - ' . $links[1][$i], $content );
    }
  }

  $content = addslashes(strip_tags($content));
  return $content;
}

/**
 * Make sure Status Updates show up on the homepage
 *
 * @param array $qv
 * @return array
 */
function status_type_request( $qv ) {
  if ( isset( $qv['feed'] ) && !isset( $qv['post_type'] ) ) {
    $qv['post_type'] = array( 'status', 'post' );
  } elseif (empty($qv)) {
    $qv['post_type'] = array( 'status', 'post' );
  }
  return $qv;
}

/**
 * Since titles are optional in RSS2 Feeds I'm removing empty title tags
 *
 * @param bool @for_comments
 */
function status_type_do_feed_rss2( $for_comments ) {
  ob_start();
  do_feed_rss2( $for_comments );
  $feed = ob_get_contents();
  ob_end_clean();

  echo preg_replace( '!<title>\s*</title>\s*!is', '', $feed );
}

/**
 * Initializes the settings
 */
function status_type_admin_init() {
  add_settings_section( 'status_type_section',
    'Status Update Custom Post Type',
    'status_type_section',
    'writing' );

  add_settings_field( 'status_type_enabletitle',
    'Enable Title',
    'status_type_enabletitle',
    'writing',
    'status_type_section' );

  register_setting( 'writing', 'status_type_enabletitle' );
}

/**
 * Render Default Section
 */
function status_type_section()
{
  echo "<p>You have the Status Update custom post type enabled.  Here are some settings to customize how it works.</p>";
}

/**
 * Render enabletitle field
 */
function status_type_enabletitle()
{
  echo '<label for="status_type_enabletitle"><input name="status_type_enabletitle" id="status_type_enabletitle" type="checkbox" value="1" ' . checked( 1, get_option('status_type_enabletitle'), false ) . ' /> Enable titles on your status updates</label>';
}

/**
 * Format the title based on options
 */
function status_type_the_title($title)
{
  global $post;

  if ( 0 == strcmp('status', $post->post_type) ) {
    if ( get_option( 'status_type_enabletitle' ) ) {
      if (empty($title)) {
        $title = status_type_title_from_content( $post->post_content );
      }
    } else {
      $title = '';
    }
  }

  return $title;
}
