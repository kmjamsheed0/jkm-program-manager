<?php
/**
 * JKM Program Manager Utils
 * 
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-program-manager
 * @subpackage jkm-program-manager/includes/utils
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('JKMPM_Utils')) :
class JKMPM_Utils {

    public static $shortcode = 'JKMPM_PROGRAMS';

    public static function should_enqueue($post){
        $post_id = isset($post->ID) ? $post->ID : '';
        $post_content = isset($post->post_content) ? $post->post_content : '';
        if( ( is_a( $post, 'WP_Post' ) && has_shortcode( $post_content, self::$shortcode) ) || get_post_type() == 'jkm_programs' ) {
            return true;
        }
        return false;
    }
}
endif;