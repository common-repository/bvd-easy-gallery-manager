<?php
/*
  Plugin Name: BVD Easy Gallery Manager
  Plugin URI:  https://balcom-vetillo.com/products/wordpress-gallery-manager/
  Description: Create and display galleries with easy image uploading
  Author: Balcom-Vetillo Design, Inc.
  Version: 1.0.6
  Author URI: https://balcom-vetillo.com
 
*/

define("PTR_URL", "https://balcom-vetillo.com/bvd-plugins-tracking-redirect/index.php"); //Licensing server URL Only contacted after a Pro Key has been entered

class bvdGalleryManager {

    public $uuid; //site unique ID
    public $secret; //site unique ID
    private $submit_success = 0;
    private $free_trial_submit_success = 0;
    private $license_key_error = false;
    private $free_trial_error = false;
    private $bvd_pt_license_key_item_reference = "BVD WordPress Plugin";
    private $bvd_frontend_editing_type_option = 'show';

    function __construct() {

        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'setup_admin_menu'));

        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box_data'));

        add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
        
        if ($this->check_pro_key()) {
            add_shortcode('bvd-gallery-frontend-admin', array($this, "bvd_gallery_frontend_admin_display"));
        }

        add_shortcode('bvd-gallery', array($this, "bvd_gallery_display"));

        add_action('wp_ajax_nopriv_adminUpdateGallery', array($this, 'admin_update_gallery'));
        add_action('wp_ajax_adminUpdateGallery', array($this, 'admin_update_gallery'));

        add_action('wp_ajax_nopriv_frontendAdminGetAdvancedUpload', array($this, 'frontend_admin_get_advanced_upload'));
        add_action('wp_ajax_frontendAdminGetAdvancedUpload', array($this, 'frontend_admin_get_advanced_upload'));
        
        add_action('wp_ajax_nopriv_frontendAdminGetBasicUpload', array($this, 'frontend_admin_get_basic_upload'));
        add_action('wp_ajax_frontendAdminGetBasicUpload', array($this, 'frontend_admin_get_basic_upload'));

        add_action('manage_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        add_filter('manage_bvd-gallery_posts_columns', array($this, 'add_custom_columns'));

        add_action('wp_enqueue_scripts', array($this, 'add_frontend_scripts'));
        
        //add_filter( 'the_content', 'add_gallery_shortcode' );
        
        update_option("bvads_plugin_tracking_used_free_trial", null);
    }

    public function init() {
        $this->uuid = get_option("bvads_plugin_tracking_uuid");
        if (!$this->uuid) {
            update_option("bvads_plugin_tracking_uuid", $this->guid());
            $this->uuid = get_option("bvads_plugin_tracking_uuid");
        }

        $this->secret = get_option("bvads_plugin_tracking_secret");
        if (!$this->secret) {
            $this->secret = '';
        }

        $labels = array(
            'name' => 'Galleries',
            'singular_name' => 'Gallery',
            'menu_name' => 'Galleries',
            'name_admin_bar' => 'Gallery',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Gallery',
            'new_item' => 'New Gallery',
            'edit_item' => 'Edit Gallery',
            'view_item' => 'View Gallery',
            'all_items' => 'All Galleries',
            'search_items' => 'Search Galleries',
            'not_found' => 'No Galleries Found',
            'not_found_in_trash' => 'No Galleries Found in Trash'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title')
        );

        register_post_type('bvd-gallery', $args);

        if (isset($_REQUEST['bvd-gallery-action'])) {
            switch ($_REQUEST['bvd-gallery-action']) {
                case 'save-frontend-admin' :
                    $image_ids = $_REQUEST['bvd-gallery-frontend-admin-images'];
                    $gallery_id = $_REQUEST['bvd-gallery-select-gallery'];

                    $encode = base64_encode(json_encode(explode(',', $image_ids)));
                    update_post_meta($gallery_id, 'bvd_images_gallery', $encode);
                    break;

                case 'set-pro-key' :
                    $this->license_key_activation($_REQUEST['pro-key']);
                    break;

                case 'deactivate-pro-key' :
                    $this->license_key_deactivation($_REQUEST['pro-key']);
                    break;
                
                case 'save-frontend-editing-option' :
                    if(isset($_REQUEST['bvd-frontend-editing-option'])) {
                        $type_option = 'show';
                    } else {
                        $type_option = 'hide';
                    }
                    update_option('bvd-gallery-frontend-editing-type-option', $type_option);
                    
                    $this->bvd_frontend_editing_type_option = get_option("bvd-gallery-frontend-editing-type-option");
                    break;
                
                case 'frontend-admin-basic-upload' :
                    $gallery_id = $_REQUEST['bvd-gallery-frontend-admin-gallery-id'];
                    
                    if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
                        $upload_dir = wp_upload_dir();

                        $time_stamp = time();
                        $file_name = $upload_dir['path'] . '/' . $time_stamp . '-' . $_FILES["file"]["name"];
                        
                        // Check the type of file. We'll use this as the 'post_mime_type'.
                        $filetype = wp_check_filetype( basename( $file_name ), null );
                        if($filetype['ext'] == 'jpg' || $filetype['ext'] == 'jpeg' || $filetype['ext'] == 'png' || $filetype['ext'] == 'gif') {
                            move_uploaded_file($_FILES["file"]["tmp_name"], $file_name);

                            // Prepare an array of post data for the attachment.
                            $attachment = array(
                                    'guid'           => $upload_dir['url'] . '/' . basename( $file_name ), 
                                    'post_mime_type' => $filetype['type'],
                                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                            );

                            // Insert the attachment.
                            $attach_id = wp_insert_attachment( $attachment, $file_name, 0 );

                            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                            require_once( ABSPATH . 'wp-admin/includes/image.php' );

                            // Generate the metadata for the attachment, and update the database record.
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name );
                            wp_update_attachment_metadata( $attach_id, $attach_data );

                            $values = get_post_custom($gallery_id);
                            if (isset($values['bvd_images_gallery'])) {
                                // The json decode and base64 decode return an array of image ids
                                $ids = json_decode(base64_decode($values['bvd_images_gallery'][0]));
                            } else {
                                $ids = array();
                            }

                            array_unshift($ids, $attach_id);

                            $encode = base64_encode(json_encode($ids));
                            update_post_meta($gallery_id, 'bvd_images_gallery', $encode);
                            
                            $this->bvd_attachment_uploaded($attach_id);
                        }
                    }
                    break;
                    
                case 'frontend-editing-trial' :
                    if(empty($_REQUEST['frontend-edit-7-day-trial-email'])) {
                        $this->free_trial_submit_success = 0;
                        $this->free_trial_error = 'The email field is required. Please fill out an email and try again.';
                    } else {
                        //Add site, since signing up for free trial
                        $this->secret = get_option("bvads_plugin_tracking_secret");
                        if (!$this->secret) {
                            $resp = json_decode($this->url_get_contents(PTR_URL . "?ptr_uuid=" . $this->uuid . "&ptr_register=1&callback_url=" . $this->callback_url() . "&plugin_name=" . urlencode("BVD Easy Gallery Manager")));
                            update_option("bvads_plugin_tracking_secret", $resp->secret);
                            $this->secret = get_option("bvads_plugin_tracking_secret");
                        }

                        //Now do the free trial (if available)
                        $request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&ptr_start_trial=1&ptr_secret=" . get_option("bvads_plugin_tracking_secret") . "&ptr_email=" . $_REQUEST['frontend-edit-7-day-trial-email'] . "&server=" . $_SERVER['SERVER_NAME'] . "&plugin=" . urlencode('BVD Easy Gallery Manager');
                        $data = $this->url_get_contents($request);
                        $data = json_decode($data);
                        if ($data->ACK == 'SUCCESS') {
                            $this->free_trial_submit_success = 1;
                            update_option("bvads_plugin_tracking_used_free_trial", "already-used");
                        } else {
                            $this->free_trial_submit_success = 0;
                            $this->free_trial_error = $data->message;
                        }
                    }
                    break;
            }
        }
    }

    public function add_frontend_scripts() {
        if ( is_admin() ) {
            return;
        }
        
        wp_enqueue_media();
      
        wp_register_style( 'bvd-gallery-dropzone-css', plugins_url('assets/css/dropzone.css', __FILE__) );
        wp_enqueue_style( 'bvd-gallery-dropzone-css' );

        wp_enqueue_script('bvd-gallery-dropzone-js', plugins_url('assets/js/dropzone.js', __FILE__), array('jquery'), '', true);

        wp_register_script('bvd-gallery-frontend-admin-js', plugins_url('assets/js/bvd-gallery-frontend-admin.js', __FILE__), array('jquery'), '', true);

        $translation_array = array(
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script('bvd-gallery-frontend-admin-js', 'bvdGalleryFrontendAjax', $translation_array);

        wp_enqueue_script('bvd-gallery-frontend-admin-js');

        wp_enqueue_style('tosrus-css', plugins_url('assets/css/jquery.tosrus.all.css', __FILE__));

        wp_enqueue_script('hammer-js', plugins_url('assets/js/hammer.min.js', __FILE__), array('jquery'), '20120206', true);
        wp_enqueue_script('jquery-hammer-js', plugins_url('assets/js/jquery.hammer.js', __FILE__), array('jquery'), '20120206', true);
        wp_enqueue_script('tosrus-js', plugins_url('assets/js/jquery.tosrus.min.all.js', __FILE__), array('jquery'), '20120206', true);
        
        wp_enqueue_style('bvd-gallery-fontawesome', plugins_url('font-awesome/font-awesome.min.css', __FILE__));
        
        wp_register_style('bvd-gallery-manager-user-css', plugins_url('assets/css/bvd-gallery-manager-user-style.css', __FILE__));
        wp_enqueue_style('bvd-gallery-manager-user-css');
    }

    public function register_admin_scripts() {
        wp_enqueue_media();
        
        wp_register_script('bvd-gallery-metabox', plugins_url('assets/js/bvd-gallery-metabox.js', __FILE__), array('jquery'), '', true);

        $translation_array = array(
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script('bvd-gallery-metabox', 'bvdGalleryAjax', $translation_array);

        wp_enqueue_script('bvd-gallery-metabox');

        wp_register_style('bvdGalleryAdminStylesheet', plugins_url('assets/css/bvd-gallery-manager-admin-style.css', __FILE__));
        wp_enqueue_style('bvdGalleryAdminStylesheet');
    }

    public function setup_admin_menu() {
        add_menu_page("BVD Gallery", "BVD Gallery", "manage_options", "edit.php?post_type=bvd-gallery", null, 'dashicons-format-gallery', '63.3');
        add_submenu_page("edit.php?post_type=bvd-gallery", "Plugin Details", "Plugin Details", "manage_options", "bvd-gallery-manager-details", array($this, 'admin_page'));
    }

    public function admin_page() {
        include "bvd-gallery-manager-plugin-details-page.php";
    }

    public function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach ($get_intermediate_image_sizes as $_size) {

            if (in_array($_size, array('thumbnail', 'medium', 'large'))) {

                $sizes[$_size]['width'] = get_option($_size . '_size_w');
                $sizes[$_size]['height'] = get_option($_size . '_size_h');
                $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$_size])) {

                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop']
                );
            }
        }

        return $sizes;
    }

    public function bvd_get_attachment_attributes($attachment_id) {
        $attachment = get_post($attachment_id);
        return array(
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'title' => $attachment->post_title
        );
    }

    private function guid() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125); // "}"
            return $uuid;
        }
    }

    public static function callback_url() {
        return urlencode(home_url());
    }

    public function license_key_activation($license_key) {
        //First time we can contact our server to add the site
        $this->secret = get_option("bvads_plugin_tracking_secret");
        if (!$this->secret) {
            $resp = json_decode($this->url_get_contents(PTR_URL . "?ptr_uuid=" . $this->uuid . "&ptr_register=1&callback_url=" . $this->callback_url() . "&plugin_name=" . urlencode("BVD Easy Gallery Manager")));
            update_option("bvads_plugin_tracking_secret", $resp->secret);
            $this->secret = get_option("bvads_plugin_tracking_secret");
        }
        
        //Now do the license key activation
        $request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&ptr_activate_license=1&ptr_secret=" . get_option("bvads_plugin_tracking_secret") . "&ptr_license_key=" . $license_key . "&server=" . $_SERVER['SERVER_NAME'];
        $data = $this->url_get_contents($request);
        $data = json_decode($data);
        if ($data->ACK == 'SUCCESS') {
            $this->submit_success = 1;
            update_option("bvads_plugin_tracking_pro_key", $data->pro_key);
        } else {
            $this->submit_success = 0;
            $this->license_key_error = $data->error;
        }
    }

    public function license_key_deactivation($license_key) {
        $request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&ptr_deactivate_license=1&ptr_secret=" . get_option("bvads_plugin_tracking_secret") . "&ptr_license_key=" . $license_key . "&server=" . $_SERVER['SERVER_NAME'];
        $data = $this->url_get_contents($request);
        $data = json_decode($data);
        if ($data->ACK == 'SUCCESS') {
            $this->submit_success = 2;
            update_option("bvads_plugin_tracking_pro_key", null);
        } else {
            $this->submit_success = 0;
            $this->license_key_error = $data->error;
        }
    }

    //Check if there is a pro key
    public function check_pro_key() {
        $pro_key = get_option("bvads_plugin_tracking_pro_key");
        if(!$pro_key) {
            return false;
        } else {
            $request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&action=verify_pro_key";
            $data = $this->url_get_contents($request);
            $data = json_decode($data);

            if ($data->key_status == "SUCCESS") {
                return true;
            } else {
                update_option("bvads_plugin_tracking_pro_key", null);
                return false;
            }
        }
    }
    
    //Get the pro key
    public function get_pro_key() {
        $request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&action=get_pro_key";
        $data = $this->url_get_contents($request);
        $data = json_decode($data);

        if ($data->key_status == "SUCCESS") {
            return $data->pro_key;
        } else {
            return false;
        }
    }
    
    //Just decides whether or not to show the free trial form.
    public function check_free_trial() {
        $free_trial = get_option("bvads_plugin_tracking_used_free_trial");
        if(!$free_trial) {
            /*$request = PTR_URL . "?ptr_uuid=" . get_option("bvads_plugin_tracking_uuid") . "&action=check_free_trial";
            $data = $this->url_get_contents($request);
            $data = json_decode($data);

            if ($data->trial_status == "SUCCESS") {
                update_option("bvads_plugin_tracking_free_trial", "already-used");
                return true;
            } else {
                return false;
            }*/
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box($post_type) {
        $post_types = array('bvd-gallery');     //limit meta box to certain post types
        if (in_array($post_type, $post_types)) {
            if(!$this->check_pro_key()) {
                add_meta_box(
                    'bvd_gallery_pro_ad_metabox', 'Gallery Pro Version', array($this, 'render_gallery_pro_ad_meta_box_content'), $post_type, 'advanced', 'high'
                );
            }
            
            add_meta_box(
                    'bvd_gallery_gallery_metabox', 'Gallery Images', array($this, 'render_gallery_meta_box_content'), $post_type, 'advanced', 'high'
            );

            add_meta_box(
                    'bvd_gallery_options_metabox', 'Gallery Options', array($this, 'render_gallery_options_meta_box_content'), $post_type, 'advanced', 'high'
            );

            add_meta_box(
                    'bvd_gallery_shortcode_metabox', 'Shortcode', array($this, 'render_gallery_shortcode_meta_box_content'), $post_type, 'advanced', 'low'
            );

            add_meta_box(
                    'bvd_gallery_logo_metabox', 'BVD Gallery', array($this, 'render_gallery_logo_meta_box_content'), $post_type, 'side', 'low'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_data($post_id) {
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
        // Check if our nonce is set.
        if (!isset($_POST['bvd_gallery_meta_box_nonce']))
            return $post_id;

        $nonce = $_POST['bvd_gallery_meta_box_nonce'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'bvd_gallery_save_meta_box_nonce'))
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        // so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
        } else {
            if (!current_user_can('edit_post', $post_id))
                return $post_id;
        }
        
        if(get_post_type($post_id) == 'bvd-gallery') {

            /* OK, its safe for us to save the data now. */

            // Sanitize the user input.
            //$mydata = sanitize_text_field( $_POST['myplugin_new_field'] );
            // Check if data is in post
            if (isset($_POST['bvd_gallery_ids'])) {
                // Encode so it can be stored and retrieved properly
                $encode = base64_encode(json_encode(explode(',', $_POST['bvd_gallery_ids'])));
                update_post_meta($post_id, 'bvd_images_gallery', $encode);
            }

            if (isset($_POST['bvd-gallery-option-gallery-type'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-gallery-type']));
                update_post_meta($post_id, 'bvd-gallery-gallery-type', $encode);
            }

            if (isset($_POST['bvd-gallery-option-slides-visible'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-slides-visible']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-slides-visible', $encode);
            }

            if (isset($_POST['bvd-gallery-option-slides-slide'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-slides-slide']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-slides-slide', $encode);
            }

            if (isset($_POST['bvd-gallery-option-thumbs-padding'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-thumbs-padding']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-thumbs-padding', $encode);
            }

            if (isset($_POST['bvd-gallery-option-captions'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-captions']));
                update_post_meta($post_id, 'bvd-gallery-captions', $encode);
            }

            if (isset($_POST['bvd-gallery-option-effect'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-effect']));
                update_post_meta($post_id, 'bvd-gallery-effect', $encode);
            }

            if (isset($_POST['bvd-gallery-option-infinite'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-infinite']));
                update_post_meta($post_id, 'bvd-gallery-infinite', $encode);
            }

            if (isset($_POST['bvd-gallery-option-pagination'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-pagination']));
                update_post_meta($post_id, 'bvd-gallery-pagination', $encode);
            }

            if (isset($_POST['bvd-gallery-option-pagination-type'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-pagination-type']));
                update_post_meta($post_id, 'bvd-gallery-pagination-type', $encode);
            }

            if (isset($_POST['bvd-gallery-option-image-size'])) {
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-image-size']));
                update_post_meta($post_id, 'bvd-gallery-image-size', $encode);
            }

            if (isset($_POST['bvd-gallery-option-image-size-width'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-image-size-width']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-image-size-width', $encode);
            }

            if (isset($_POST['bvd-gallery-option-image-size-height'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-image-size-height']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-image-size-height', $encode);
            }

            if (isset($_POST['bvd-gallery-option-image-count'])) {
                $optdata = sanitize_text_field($_POST['bvd-gallery-option-image-count']);
                $encode = base64_encode(json_encode($optdata));
                update_post_meta($post_id, 'bvd-gallery-image-count', $encode);
            }

            if (isset($_POST['bvd-gallery-option-gallery-page'])) {
                $values = get_post_custom($post_id);
                if(isset($values['bvd-gallery-page'])) {
                    $last_page = $values['bvd-gallery-page'];
                } else {
                    $last_page = '';
                }
                
                $encode = base64_encode(json_encode($_POST['bvd-gallery-option-gallery-page']));
                update_post_meta($post_id, 'bvd-gallery-page', $encode);
                if($_POST['bvd-gallery-option-gallery-page'] != 0) {
                    if(!empty($last_page)) {
                        if($last_page != $_POST['bvd-gallery-option-gallery-page']) {
                            $post_update = get_post($_POST['bvd-gallery-option-gallery-page']);
                            $post_content = $post_update->post_content;
                            $pos = strpos($post_content, '[bvd-gallery id=');
                            if($pos === false) {
                                $post_content .= '<br/><br/>[bvd-gallery id=' . $post_id . ']';
                            }

                            $gallery_post = array(
                                'ID'           => $_POST['bvd-gallery-option-gallery-page'],
                                'post_content' => $post_content
                            );

                            // Update the post into the database
                            wp_update_post( $gallery_post );
                        }
                    }
                }
            }
        }
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_gallery_pro_ad_meta_box_content() {
        global $post;
        ?>
        <div class="bvd-gallery-pro-ad-wrapper">
            <a href="https://www.balcom-vetillo.com/plugin-keys/" target="_blank"><img src="<?php echo plugins_url('assets/images/gallery-pro.jpg', __FILE__); ?>" /></a>
            <p><a href="https://www.balcom-vetillo.com/plugin-keys/" target="_blank">Get a Pro Key</a></p>
        </div>
        <?php
    }

    public function render_gallery_meta_box_content() {
        global $post;

        // Here we get the current images ids of the gallery
        $values = get_post_custom($post->ID);
        if (isset($values['bvd_images_gallery'])) {
            // The json decode and base64 decode return an array of image ids
            $ids = json_decode(base64_decode($values['bvd_images_gallery'][0]));
        } else {
            $ids = array();
        }
        
        wp_nonce_field('bvd_gallery_save_meta_box_nonce', 'bvd_gallery_meta_box_nonce'); // Security
        // Implode the array to a comma separated list
        $cs_ids = implode(",", $ids);
        
        // A button which we will bind to later on in JavaScript
        $html = '<input class="bvd_gallery_manage_gallery" title="Manage gallery" type="button" value="Manage Gallery Images" />';
        // We display the gallery
        $html .= '<div id="bvd-gallery-manager-gallery-admin-container">';
        if(empty($ids) || empty($ids[0])) {
            $html .= '<p>There are no images currently in this gallery.</p>';
        } else {
            $html .= do_shortcode('[gallery ids="' . $cs_ids . '"]');
        }
        $html .= '<div style="clear:both"></div>';
        $html .= '</div>';
        // Here we store the image ids which are used when saving the gallery
        $html .= '<input id="bvd_gallery_ids" type="hidden" name="bvd_gallery_ids" value="' . $cs_ids . '" />';
        // A button which we will bind to later on in JavaScript
        $html .= '<input class="bvd_gallery_manage_gallery" title="Manage gallery" type="button" value="Manage Gallery Images" />';

        echo $html;
    }

    public function render_gallery_logo_meta_box_content() {
        ?>
        <div class="designed-by-wrapper">
            <p>Plugin designed and developed by<br/><a href="https://www.balcom-vetillo.com/" target="_blank">Balcom-Vetillo Design</a>.</p>
            <a href="https://www.balcom-vetillo.com/" target="_blank"><img src="<?php echo plugins_url('assets/images/BVD-Logo-vert.png', __FILE__); ?>" /></a>
        </div>
        <?php
    }

    public function render_gallery_options_meta_box_content() {
        global $post;
        
        if($this->check_pro_key() && file_exists(ABSPATH."wp-content/plugins/bvd-gallery-manager-pro/bvd-gallery-manager-gallery-options-page-pro.php")){
            include ABSPATH."wp-content/plugins/bvd-gallery-manager-pro/bvd-gallery-manager-gallery-options-page-pro.php";
        }
        else{
            include 'bvd-gallery-manager-gallery-options-page.php';
        }
    }
    
     public function render_gallery_shortcode_meta_box_content() {
        global $post;
        ?>
        <div class="bvd-gallery-shortcode-wrapper">
            <h3>Basic Shortcode</h3>
            <p>[bvd-gallery id=<?php echo $post->ID; ?>]</p>
            <div class="shortcode-attributes">
                <h3>Shortcode Options</h3>
                <p><strong>id</strong> -- REQUIRED -- integer -- The id of the gallery you want to display. -- This gallery id: <?php echo $post->ID; ?><span class="shortcode-attributes-example">Example: [bvd-gallery id=<?php echo $post->ID; ?>]</span></p>
                <p><strong>count</strong> -- integer -- The number of photos to display from the gallery. Enter -1 to show all photos. This will override the Image Count option set above.<span class="shortcode-attributes-example">Example: [bvd-gallery id=<?php echo $post->ID; ?> count=-1]</span></p>
            </div>
        </div>
        <?php
    }

    public function custom_columns($column, $post_id) {
        switch ($column) {
            case 'gallery_id':
                echo $post_id;
                break;

            case 'basic_shortcode':
                echo '[bvd-gallery id=' . $post_id . ']';
                break;
        }
    }

    public function add_custom_columns($columns) {
        return array_merge($columns, array('gallery_id' => 'Gallery ID', 'basic_shortcode' => 'Basic Shortcode'));
    }

    public function admin_update_gallery() {
        ob_start();

        if (isset($_REQUEST['galleryIDs'])) {
            echo do_shortcode('[gallery ids="' . $_REQUEST['galleryIDs'] . '"]');
            echo '<div style="clear:both"></div>';
        }

        $output_string = ob_get_contents();
        ob_end_clean();

        echo json_encode($output_string);

        wp_die();
    }

    public function bvd_gallery_frontend_admin_display($atts) {
        if(class_exists("bvdGalleryManagerPro")){
			$frontend = new bvdGalleryManagerPro();
			return $frontend->bvd_gallery_frontend_admin_display($atts);
		}
    }
    
    public function frontend_admin_get_basic_upload() {
        global $wpdb;

        ob_start();

        $id = $_REQUEST['galleryID']; 
        
        $values = get_post_custom($id);
        if (isset($values['bvd-gallery-page'])) {
            $page_id = json_decode(base64_decode($values['bvd-gallery-page'][0]));
        } else {
            $page_id = '';
        }
        
        if(!empty($page_id)) {
            $page_link = get_page_link( $page_id );
        } else {
            $page_link = '';
        }
        ?>
        <form action="/" method="post" enctype="multipart/formdata" class="dropzone" id="bvd-frontend-gallery-basic-upload-form">
            <input type="hidden" name="bvd-gallery-action" value="frontend-admin-basic-upload" />
            <input type="hidden" name="bvd-gallery-frontend-admin-gallery-id" value="<?php echo $id; ?>" />
            <div class="dz-message dz-default">
                <div class="dz-message-desktop">
                    <div class="dz-message-desktop-icon">
                        <i class="fa fa-picture-o"></i>
                    </div>
                    Drop photos here or click to browse.
                </div>
                <div class="dz-message-phone">
                    <i class="fa fa-picture-o"></i> Browse for Multiple Images
                </div>
            </div>
            <div class="fallback">
                <input type="file" name="file" />
            </div>
        </form>
            
        <form action="/" method="post" enctype="multipart/formdata" class="dropzone" id="bvd-frontend-gallery-basic-upload-form-phone-camera">
            <input type="hidden" name="bvd-gallery-action" value="frontend-admin-basic-upload" />
            <input type="hidden" name="bvd-gallery-frontend-admin-gallery-id" value="<?php echo $id; ?>" />
            <div class="dz-message dz-default dz-phone-camera-button-message">
                <i class="fa fa-camera"></i> Take a Photo
            </div>
            <div class="fallback">
                <input type="file" name="file" />
            </div>
        </form>
        
        <?php
        if(!empty($page_link)){
            ?>
            <div class="bvd-frontend-admin-basic-view-gallery-link-wrapper">
                <a href="<?php echo $page_link; ?>">view full gallery</a>
            </div>
            <?php
        }

        $output_string = ob_get_contents();
        ob_end_clean();

        echo json_encode($output_string);

        wp_die();
    }

    public function frontend_admin_get_advanced_upload() {
        global $wpdb;
        
        $editing_option = get_option("bvd-gallery-frontend-editing-type-option");
        if($editing_option == 'show') {
            ob_start();

            if (isset($_REQUEST['galleryIDs'])) {
                $ids = $_REQUEST['galleryIDs'];
                $gallery_upload_success = true;
            } else {
                $values = get_post_custom($_REQUEST['galleryID']);
                if (isset($values['bvd_images_gallery'])) {
                    // The json decode and base64 decode return an array of image ids
                    $ids = json_decode(base64_decode($values['bvd_images_gallery'][0]));
                } else {
                    $ids = array();
                }

                $gallery_upload_success = false;
            }
            ?>
            <input type="hidden" name="bvd-gallery-frontend-admin-images" id="bvd-gallery-frontend-admin-images" value="<?php echo implode(",", $ids); ?>" />
                <div id="bvd-frontend-gallery-advanced-btn">advanced</div>
            <div id="bvd-gallery-frontend-admin-advanced-hidden">
                <?php
                if($gallery_upload_success) {
                    echo '<div class="bvd-frontend-gallery-upload-success"><p>Gallery Changes have been made. <strong>Don\'t forget to click the save button!</strong></div>';
                }
                ?>
                <button type="button" id="bvd-gallery-frontend-admin-btn">manage gallery</button>
                <button type="submit" id="bvd-gallery-frontend-admin-submit-btn">save</button>
            </div>
            <?php
            $output_string = ob_get_contents();
            ob_end_clean();

            echo json_encode($output_string);
        }

        wp_die();
    }
    
    public function bvd_gallery_display($atts) {
        global $wpdb;
        ob_start();

        include 'bvd-gallery-manager-frontend-gallery.php';

        $output_string = ob_get_contents();
        ob_end_clean();

        return $output_string;
    }
    
    public function bvd_attachment_uploaded($id) {
        $attachment = get_post( $id );

        if ( 'image/jpeg' == $attachment->post_mime_type ) {
            $this->bvd_fix_rotation( $attachment->guid );
            $attachment_meta = wp_generate_attachment_metadata( $attachment->ID, str_replace( get_bloginfo('url'), ABSPATH, $attachment->guid ) );
            wp_update_attachment_metadata( $attachment->ID, $attachment_meta );
        }
    }
    
    public function bvd_fix_rotation( $source ) {

	$source = str_replace( get_bloginfo('url'), ABSPATH, $source );
	$sourceFile = explode( '/', $source );
	$filename = $sourceFile[5];

	$destination = $source;

	$size = getimagesize( $source );

	$width = $size[0];
	$height = $size[1];

	$sourceImage = imagecreatefromjpeg( $source );

	$destinationImage = imagecreatetruecolor( $width, $height );

	imagecopyresampled( $destinationImage, $sourceImage, 0, 0, 0, 0, $width, $height, $width, $height );

	$exif = exif_read_data( $source );

	$ort = $exif['Orientation'];

	switch ( $ort ) {

		case 2:
			$this->bvd_flip_image( $destinationImage );
			break;
		case 3:
			$destinationImage = imagerotate( $destinationImage, 180, -1 );
			break;
		case 4:
			$this->bvd_flip_image( $destinationImage );
			break;
		case 5:
			$this->bvd_flip_image( $destinationImage );
			$destinationImage = imagerotate( $destinationImage, -90, -1 );
			break;
		case 6:
			$destinationImage = imagerotate( $destinationImage, -90, -1 );
			break;
		case 7:
			$this->bvd_flip_image( $destinationImage );
			$destinationImage = imagerotate( $destinationImage, -90, -1 );
			break;
		case 8:
			$destinationImage = imagerotate( $destinationImage, 90, -1 );
			break;
	}

	return imagejpeg( $destinationImage, $destination, 100 );
}

    public function bvd_flip_image( &$image ) {

	$x = 0;
	$y = 0;
	$height = null;
	$width = null;

        if ( $width  < 1 )
            $width  = imagesx( $image );

        if ( $height < 1 )
            $height = imagesy( $image );

        if ( function_exists('imageistruecolor') && imageistruecolor( $image ) )
            $tmp = imagecreatetruecolor( 1, $height );
        else
            $tmp = imagecreate( 1, $height );

        $x2 = $x + $width - 1;

        for ( $i = (int)floor( ( $width - 1 ) / 2 ); $i >= 0; $i-- ) {
            imagecopy( $tmp, $image, 0, 0, $x2 - $i, $y, 1, $height );
            imagecopy( $image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height );
            imagecopy( $image, $tmp, $x + $i,  $y, 0, 0, 1, $height );
        }

        imagedestroy( $tmp );

        return true;
    }
    
    public function url_get_contents($url) {
        if (function_exists('curl_exec')){ 
            $conn = curl_init($url);
            curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            $url_get_contents_data = (curl_exec($conn));
            curl_close($conn);
        }elseif(function_exists('file_get_contents')){
            $url_get_contents_data = file_get_contents($url);
        }elseif(function_exists('fopen') && function_exists('stream_get_contents')){
            $handle = fopen ($url, "r");
            $url_get_contents_data = stream_get_contents($handle);
            fclose($handle);
        }else{
            $url_get_contents_data = false;
        }
        return $url_get_contents_data;
    }
 
}

$bvdGalleryManager = new bvdGalleryManager();
