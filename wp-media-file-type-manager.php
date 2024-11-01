<?php
/**
 * Plugin Name: WP Media File Type Manager
 * Plugin URI: https://wordpress.org/plugins/wp-media-file-type-manager/
 * Description: WP Media File Type Manager will allow you to manage different file types in Media Library.
 * Version: 2.2.8
 * Author: Seerox
 * Author URI: http://seerox.com
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Requires at least: 3.8
 * Tested up to: 6.6.2
 */

// Prevents direct file access
if ( ! defined( 'WPINC' ) ) {
    die();
}

/**
 * Runs When plugin is activated.
 * Update Plugin activation time.
 * Loads activation functions.
 *
 * @since 1.0.0
 *
 * @uses seerox_wpmftm_default_allowed_typs() Save Default media file types.
 *
 * @return void
 */
function seerox_wpmftm_activation() {
    update_option( 'seerox_wpmftm_activated', time() );
    seerox_wpmftm_default_allowed_typs();
}
register_activation_hook( __FILE__, 'seerox_wpmftm_activation' );

/**
 * Runs When plugin is deactivated.
 * Clears any temporary data stored by plugin.
 * Loads deactivation functions
 *
 * @since 1.0.0
 *
 * @uses update_option()
 *
 * @return void
 */
function seerox_wpmftm_deactivation() {
    update_option( 'seerox_wpmftm_deactivated', time() );
}
register_deactivation_hook( __FILE__, 'seerox_wpmftm_deactivation' );

// Define Constants
define ( 'SEEROX_WPMFTM_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define ( 'SEEROX_WPMFTM_PLUGIN_ASSETS_URL', SEEROX_WPMFTM_PLUGIN_URL . '/assets' );
define ( 'SEEROX_WPMFTM_PLUGIN_CSS_URL', SEEROX_WPMFTM_PLUGIN_ASSETS_URL . '/css' );
define ( 'SEEROX_WPMFTM_PLUGIN_IMAGES_URL', SEEROX_WPMFTM_PLUGIN_ASSETS_URL . '/images' );
define ( 'SEEROX_WPMFTM_PLUGIN_JS_URL', SEEROX_WPMFTM_PLUGIN_ASSETS_URL . '/js' );

/**
 * Display links at plugins page after plugin name
 *
 * @since 1.0.0
 *
 * @param  array $links
 *
 * @return array
 */
function seerox_wpmftm_plugin_action_links( $links ) {
    $wpmftm_settings_links = array(
        '<a href="' . admin_url( '?page=wpmftm-manager' ) . '">Settings</a>',
    );

    return array_merge( $links, $wpmftm_settings_links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'seerox_wpmftm_plugin_action_links' );

/**
 * Add page to the menu
 *
 * @since 1.0.0
 *
 * @return void
 */
function seerox_wpmftm_admin_menu() {
    add_menu_page( 'WP Media File Type Manager', 'WP Media File Type Manager', 'manage_options', 'wpmftm-manager', 'seerox_wpmftm_settings' );
}
add_action( 'admin_menu', 'seerox_wpmftm_admin_menu' );

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 *
 * @return void
 */
function seerox_wpmftm_register_admin_scripts() {
    remove_menu_page( 'edit.php?post_type=mftype' );
    $wpmftm_pages = array( 'wpmftm-manager' );

    $page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false;
    if ( $page && in_array( $page, $wpmftm_pages ) ) {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_register_script( 'jquery-dataTables-min-js', SEEROX_WPMFTM_PLUGIN_JS_URL . '/jquery.dataTables.min.js' );
        wp_enqueue_script( 'jquery-dataTables-min-js' );

        wp_register_script( 'seerox_wpmftm_admin_js', SEEROX_WPMFTM_PLUGIN_JS_URL . '/admin.js' );
        wp_enqueue_script( 'seerox_wpmftm_admin_js' );

        wp_enqueue_style( 'jquery-dataTables-min-css', SEEROX_WPMFTM_PLUGIN_CSS_URL . '/jquery.dataTables.min.css' );
        wp_enqueue_style( 'seerox_wpmftm_admin_css', SEEROX_WPMFTM_PLUGIN_CSS_URL . '/admin.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'seerox_wpmftm_register_admin_scripts' );

/**
 * Register Custom Post Types
 *
 * @since 1.0.0
 *
 * @uses seerox_wpmftm_cpt_mftype()
 *
 * @return void
 */
function seerox_wpmftm_register_custom_post_types() {
    seerox_wpmftm_cpt_mftype();
}
add_action( 'wp_loaded', 'seerox_wpmftm_register_custom_post_types' );

/**
 * Registers mftype custom post type.
 *
 * @since 1.0.0
 *
 * @return void
 */
function seerox_wpmftm_cpt_mftype() {

    // Labels
    $labels = array(
        'name'               => _x( 'WP Media File Type', 'mftype' ),
        'singular_name'      => _x( 'WP Media File Type', 'mftype' ),
        'add_new'            => _x( 'Add New', 'mftype' ),
        'add_new_item'       => _x( 'Add New', 'mftype' ),
        'edit'               => _x( 'Edit', 'mftype' ),
        'edit_item'          => _x( 'Edit item', 'mftype' ),
        'new_item'           => _x( 'New', 'mftype' ),
        'view'               => _x( 'View', 'mftype' ),
        'view_item'          => _x( 'View Item', 'mftype' ),
        'search_items'       => _x( 'Search File Type', 'mftype' ),
        'not_found'          => _x( 'No File Type found', 'mftype' ),
        'not_found_in_trash' => _x( 'No File Type found in Trash', 'mftype' ),
        'parent'             => _x( 'Parent File Type',  'mftype' )
    );

    // Arguments.
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_position' => 15,
        'supports'      => array( 'editor', 'thumbnail', 'title', 'custom_fields' ),
        'taxonomies'    => array( '' ),
        'has_archive'   => true,
        'query_var'     => true
    );
    register_post_type( 'mftype', $args );
}

/**
 * Main Settings page.
 *
 * @since 1.0.0
 *
 * @uses seerox_wpmftm_get_all_types() Get media file type posts
 *
 * @return void
 */
function seerox_wpmftm_settings() {
    $wpmftm_save_file_type = isset( $_POST["wpmftm_save_file_type"] ) ? sanitize_text_field( $_POST["wpmftm_save_file_type"] ) : false;
    if ( $wpmftm_save_file_type ) {
        $mft_post_id = isset( $_POST["mft_post_id"] ) ? intval( sanitize_text_field( $_POST["mft_post_id"] ) ) : 0;
        if ( 0 > $mft_post_id ) {
            $mft_post_id = 0;
        }

        $mftype = array(
            'ID'           => $mft_post_id,
            'post_type'    => 'mftype',
            'post_title'   => trim( sanitize_text_field( $_POST['file_extension'] ), '.' ),
            'post_content' => sanitize_text_field( $_POST['file_type'] ),
            'post_status'  => 'publish'
        );
        $post_id = wp_insert_post( $mftype );
        update_post_meta( $post_id, 'file_residential_type', 'Custom' );
    }

    $button_label = 'Add';
    $wpmf_post    = '';
    $action       = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
    if ( 'edit' == $action ) {
        $wpmf_id      = sanitize_text_field( $_POST['wpmf_type_id'][0] );
        $wpmf_post    = get_post( $wpmf_id );
        $button_label = 'Update';
    }

    $post_id      = 0;
    $post_title   = '';
    $post_content = '';
    if ( ! empty( $wpmf_post ) && 'edit' == $action  ) {
        $post_id      = $wpmf_post->ID;
        $post_title   = $wpmf_post->post_title;
        $post_content = $wpmf_post->post_content;
    }
    ?>
    <div class="wrap">
        <h1>WP Media File Type Settings</h1>
        <div style=" max-width: 600px; width: 100%; margin: 0 auto;">
            <form method="post" name="frm_wpmftm_manager" action="">
                <input type="hidden" name="mft_post_id" value="<?php echo $post_id; ?>" />
                <fieldset style=" width: 70%;margin: 30px auto;">
                    <legend><?php echo $button_label ?> Files</legend>
                    <table class="striped widefat">
                        <tbody>
                            <tr><td><label>File Extension</label></td></tr>
                            <tr><td><input type="text" name="file_extension" class="file_extension" value="<?php echo $post_title ?>" style="width:100%"></td></tr>
                            <tr><td><label>File Type</label></td></tr>
                            <tr><td><input type="text" name="file_type" class="file_type" value="<?php echo $post_content ?>" style="width:100%"></td></tr>
                            <tr><td><input name="wpmftm_save_file_type" type="submit" class="button button-primary button-large" value="<?php echo $button_label ?>"></td></tr>
                        </tbody>
                    </table>
                </fieldset>
            </form>
            <?php
            $apply_action = isset( $_POST['apply_action'] ) ? sanitize_text_field( $_POST['apply_action'] ) : false;
            if ( $apply_action ) {
                $action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
                if ( 'delete' == $action) {
                    foreach ( $_POST['wpmf_type_id'] as $ids ) {
                        wp_delete_post( $ids );
                    }
                }
            }
            ?>
            <form method="post">
                <fieldset>
                    <legend>All Files</legend>
                    <table class="wpmftm_datatable striped widefat">
                        <thead>
                            <tr>
                                <th class="checkbox_th"><input type="checkbox" class="wpmf_bulk_select"/></th>
                                <th>File Extension</th>
                                <th>File Type</th>
                                <th>File Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ( seerox_wpmftm_get_all_types() as $wpmf_type ) {
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="wpmf_type_id[]" class="wpmf_select" value="<?php echo $wpmf_type->ID ?>"/></td>
                                    <td><?php echo '.' . $wpmf_type->post_title ?></td>
                                    <td><?php echo $wpmf_type->post_content ?></td>
                                    <td><?php echo get_post_meta( $wpmf_type->ID, 'file_residential_type', true ); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Get media file type posts
 *
 * @since 1.0.0
 *
 * @return array    $posts      Returns all published mftype posts.
 */
function seerox_wpmftm_get_all_types() {
    $args = array(
        'post_type'      => 'mftype',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    );
    $wpmftm_posts = new WP_Query( $args );

    return $posts = $wpmftm_posts->posts;
}

/**
 * Add or remove allowed mime types and file extensions.
 *
 * @since 1.0.0
 *
 * @uses seerox_wpmftm_get_all_types()  Get media file type posts
 *
 * @param  array  $mimes                allowed mime types and file extensions
 *
 * @return array  $mimes                Added more allowed mime types and file extensions
 */
function seerox_wpmftm_upload_mimes( $mimes = array() ) {
    $posts = array();
    $posts = seerox_wpmftm_get_all_types();
    if ( ! empty( $posts ) ) {
        foreach ( $posts as $post ) {
            $mimes[ $post->post_title ] = $post->post_content;
        }
    }

    return $mimes;
}
add_action( 'upload_mimes', 'seerox_wpmftm_upload_mimes' );

/**
 * Save Default media file types.
 *
 * @since 1.0.0
 *
 * @uses seerox_wpmftm_save_default_file_types()   Saves Default media file types.
 *
 * @return void
 */
function seerox_wpmftm_default_allowed_typs() {
    $allowed_files_types = get_allowed_mime_types();
    if ( ! empty ( $allowed_files_types ) ) {

        $mftype = array(
            'ID'           => 0,
            'post_type'    => 'mftype',
            'post_title'   => '',
            'post_content' => '',
            'post_status'  => 'publish'
        );
        foreach ( $allowed_files_types as $exts => $type ) {

            $mftype['post_content'] = $type;

            if ( strpos( $exts, '|' ) ) {

                $exploded = explode( '|', $exts );
                foreach ( $exploded as $ext ) {
                    $mftype['post_title'] = $ext;
                    seerox_wpmftm_save_default_file_types( $mftype );
                }
            } else {
                $mftype['post_title'] = $exts;
                seerox_wpmftm_save_default_file_types( $mftype );
            }
        }
    }

}

/**
 * Saves Media file types.
 *
 * @since 1.0.0
 *
 * @param  string $mftype custom post type
 *
 * @return void
 */
function seerox_wpmftm_save_default_file_types( $mftype ) {
    global $wpdb;
    $mftype_post_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '{$mftype['post_title']}' AND post_type = '{$mftype['post_type']}'" );

    if ( $mftype_post_id > 0 ) {
        $mftype['ID'] = $mftype_post_id;
    }

    $post_id = wp_insert_post( $mftype );
    update_post_meta( $post_id, 'file_residential_type', 'Defult' );
}
