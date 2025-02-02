<?php
/**
 * Woo Force Sells Settings
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-force-sells
 * @subpackage jkm-force-sells/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(!class_exists('JKMPM')) :
class JKMPM {
    
    private static $instance = null;

    private function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {
        require_once JKMPM_PATH . 'admin/class-jkmpm-admin.php';
        require_once JKMPM_PATH . 'public/class-jkmpm-public.php';
        require_once JKMPM_PATH . 'includes/utils/class-jkmpm-utils.php';
    }

    private function define_admin_hooks() {
        $admin = new JKMPM_Admin();
        add_action('init', array($admin, 'jkmpm_register_post_type'));
        add_action('add_meta_boxes', array($admin, 'jkmpm_add_meta_boxes'));
        add_action('save_post', array($admin, 'jkmpm_save_meta'));
        add_action('admin_menu', array($admin, 'jkmpm_admin_menu'));
        add_action('admin_enqueue_scripts', array($admin, 'jkmpm_enque_styles_and_scripts'));
    }

    private function define_public_hooks() {
        $public = new JKMPM_Public();

        add_action('wp_enqueue_scripts', array($public, 'jkmpm_enqueue_public_styles_and_scripts'));
        add_shortcode( JKMPM_Utils::$shortcode , array($public, 'shortcode_jkm_program_listing'));

    }
}
endif;