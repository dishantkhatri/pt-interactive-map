<?php
/*
 * Plugin Name: PT Interactive Map
 * Description: This WordPress plugin enables the embedding of an interactive map on a page that shows article locations. Each article has latitude and longitude fields, and when the user clicks on the map, a popup displays the article title. Clicking on the title in the popup redirects the user to the article's page.
 * Version: 1.0.0
 * Author: Dishant Khatri
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function pt_load_scripts() {
     wp_enqueue_script( 'pt-leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', false);
     wp_enqueue_script( 'pt-script', plugin_dir_url( __FILE__ ) . '/assets/js/index.js', array('pt-leaflet-js'), false);
 
     wp_enqueue_style( 'pt-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4', 'all' );

     wp_localize_script( 'pt-script', 'frontend_ajax_object',
        array( 			
            'siteURL' => get_site_url(),
        )
     );
}
add_action('wp_enqueue_scripts', 'pt_load_scripts');

add_action( 'add_meta_boxes', 'pt_add_meta_box' );
function pt_add_meta_box() {
    add_meta_box(
        'pt_location_details_group',
        'Location Details',
        'pt_meta_box_callback',
        'post',
        'normal',
        'default'
    );
}

function pt_meta_box_callback( $post ) {
    wp_nonce_field( 'pt_custom_mb_nonce', 'pt_custom_mb_nonce_field' ); 
    $latitude_value = get_post_meta( $post->ID, 'latitude', true ); 
    $longitude_value = get_post_meta( $post->ID, 'longitude', true ); 
    ?>
    <label for="latitude">Latitude:</label>
    <input type="text" id="latitude" name="latitude" value="<?php echo esc_attr( $latitude_value ); ?>">
    
    <label for="longitude">Longitude:</label>
    <input type="text" id="longitude" name="longitude" value="<?php echo esc_attr( $longitude_value ); ?>">
    <?php
}

add_action( 'save_post', 'pt_save_meta_box_data' );
function pt_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['pt_custom_mb_nonce_field'] ) || ! wp_verify_nonce( $_POST['pt_custom_mb_nonce_field'], 'pt_custom_mb_nonce' ) ) {
        return $post_id;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    if ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-edit-save' ) {
        return $post_id;
    }

    if ( isset( $_POST['latitude'] ) ) {
        $latitude = sanitize_text_field( $_POST['latitude'] );
        update_post_meta( $post_id, 'latitude', $latitude );
    }

    if ( isset( $_POST['longitude'] ) ) {
        $longitude = sanitize_textarea_field( $_POST['longitude'] );
        update_post_meta( $post_id, 'longitude', $longitude );
    }

    return $post_id;
}

/**
 * Custom endpoint to fetch posts with latitude and longitude.
 */
function register_posts_with_lat_lng_route() {
    register_rest_route( 'wp/v2', '/posts-with-lat-lng', array(
        'methods'  => WP_REST_SERVER::READABLE,
        'callback' => 'get_posts_with_lat_lng',
    ) );
}
add_action( 'rest_api_init', 'register_posts_with_lat_lng_route' );

/**
 * Handle the custom endpoint request.
 */
function get_posts_with_lat_lng( WP_REST_Request $request ) {
    $args = array(
        'post_type' => 'post',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'latitude',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'longitude',
                'compare' => 'EXISTS',
            ),
        ),
    );

    $query = new WP_Query( $args );
    $posts = array();

    while ( $query->have_posts() ) {
        $query->the_post();

        $post_id = get_the_ID();
        $latitude = get_post_meta( $post_id, 'latitude', true );
        $longitude = get_post_meta( $post_id, 'longitude', true );

        $post_data = array(
            'id'         => $post_id,
            'title'      => get_the_title(),
            'post_link'   => get_the_permalink(),
            'latitude'   => $latitude,
            'longitude'  => $longitude,
        );

        $posts[] = $post_data;
    }

    wp_reset_postdata();

    return rest_ensure_response( $posts );
}

add_shortcode( 'leaflet_map', 'pt_leaflet_map_shortcode' );

function pt_leaflet_map_shortcode( $atts ) {
    // HTML for the map container
    $map_html = '<div class="map-container" style="height:450px; width: 600px;"><div id="leaflet-map" style="height:450px;"></div></div>';

    return $map_html;
}
