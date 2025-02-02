<?php
/**
 * JKM Program manager Admin
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-program-manager
 * @subpackage jkm-program-manager/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(!class_exists('JKMPM_Admin')) :
class JKMPM_Admin {
    public function __construct() {

    }

    /**
     * 
     * Register custom post type 'jkm_programs'
     * 
     */

    public function jkmpm_register_post_type() {
        $labels = array(
            'name'               => __('Programs', 'jkm-program-manager'),
            'singular_name'      => __('Program', 'jkm-program-manager'),
            'menu_name'          => __('Radio Programs', 'jkm-program-manager'),
            'name_admin_bar'     => __( 'Radio Programs', 'jkm-program-manager'),
            'add_new'           => __('Add New', 'jkm-program-manager'),
            'add_new_item'      => __('Add New Program', 'jkm-program-manager'),
            'edit_item'         => __('Edit Program', 'jkm-program-manager'),
            'new_item'          => __('New Program', 'jkm-program-manager'),
            'view_item'         => __('View Program', 'jkm-program-manager'),
            'search_items'      => __('Search Programs', 'jkm-program-manager'),
            'not_found'         => __('No programs found', 'jkm-program-manager'),
            'not_found_in_trash'=> __('No programs found in Trash', 'jkm-program-manager'),
        );

        // $rewrite = array(
        //     'slug'                  => apply_filters('jkm_change_post_slug','programs'),
        //     'with_front'            => false,
        //     'pages'                 => true,
        //     'feeds'                 => true,
        // );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'radio_programs'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-playlist-audio',
            'supports'           => array('title', 'editor', 'thumbnail','excerpt')
        );

        register_post_type('jkm_programs', $args);

    }

    /**
     * 
     * Add metaboxes
     * 
     */
    public function jkmpm_add_meta_boxes() {
        add_meta_box(
            'program_schedule',
            __('Broadcast Schedule', 'jkm-program-manager'),
            array($this, 'render_schedule_meta_box'),
            'jkm_programs',
            'normal',
            'high'
        );

        add_meta_box(
            'program_dates',
            __('Program Period', 'jkm-program-manager'),
            array($this, 'render_dates_meta_box'),
            'jkm_programs',
            'side',
            'high'
        );
    }

    public function render_schedule_meta_box($post) {
        wp_nonce_field('jkmpm_schedule_meta_box', 'jkmpm_schedule_meta_box_nonce');
        
        $schedule = get_post_meta($post->ID, 'jkm_program_schedule', true);
        $schedule = !empty($schedule) ? json_decode($schedule, true) : array();

        ?>
        <div id="jkmpm-program-schedule">
            <?php foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day): ?>
            <div class="jkmpm-schedule-row">
                <label>
                    <input type="checkbox" 
                           name="schedule_days[]" 
                           value="<?php echo esc_attr($day); ?>"
                           <?php checked(isset($schedule[$day])); ?>>
                    <?php echo esc_html($day); ?>
                </label>
                <input type="time" 
                       name="schedule_times[<?php echo esc_attr($day); ?>]"
                       value="<?php echo isset($schedule[$day]) ? esc_attr($schedule[$day]) : ''; ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function render_dates_meta_box($post) {
        wp_nonce_field('jkmpm_dates_meta_box', 'jkmpm_dates_meta_box_nonce');
        
        $start_date = get_post_meta($post->ID, 'jkm_program_start_date', true);
        $end_date = get_post_meta($post->ID, 'jkm_program_end_date', true);
        
        ?>
        <div id="jkmpm-date-meta_wrapper">
            <p>
                <label for="jkmpm-program_start_date"><?php _e('Start Date:', 'jkm-program-manager'); ?></label>
                <input type="date" 
                       id="program_start_date" 
                       name="program_start_date" 
                       value="<?php echo esc_attr($start_date); ?>" 
                       required>
            </p>
            <p>
                <label for="jkmpm-program_end_date"><?php _e('End Date:', 'jkm-program-manager'); ?></label>
                <input type="date" 
                       id="program_end_date" 
                       name="program_end_date" 
                       value="<?php echo esc_attr($end_date); ?>" 
                       required>
            </p>
        </div>
        <?php
    }

    public function jkmpm_save_meta($post_id) {
        // Verify nonces
        if (!isset($_POST['jkmpm_schedule_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['jkmpm_schedule_meta_box_nonce'], 'jkmpm_schedule_meta_box')) {
            return;
        }

        if (!isset($_POST['jkmpm_dates_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['jkmpm_dates_meta_box_nonce'], 'jkmpm_dates_meta_box')) {
            return;
        }

        // Save dates
        if (isset($_POST['program_start_date'])) {
            update_post_meta(
                $post_id,
                'jkm_program_start_date',
                sanitize_text_field($_POST['program_start_date'])
            );
        }

        if (isset($_POST['program_end_date'])) {
            update_post_meta(
                $post_id,
                'jkm_program_end_date',
                sanitize_text_field($_POST['program_end_date'])
            );
        }

        // Save schedule
        $schedule = array();

        if (isset($_POST['schedule_days']) && is_array($_POST['schedule_days'])) {
            foreach ($_POST['schedule_days'] as $day) {
                if (isset($_POST['schedule_times'][$day])) {
                    $schedule[$day] = sanitize_text_field($_POST['schedule_times'][$day]);
                }
            }
        }

        update_post_meta(
            $post_id,
            'jkm_program_schedule',
            json_encode($schedule)
        );
    }

    public function jkmpm_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=jkm_programs',
            __('Import Programs', 'jkm-program-manager'),
            __('Import Programs', 'jkm-program-manager'),
            'manage_options',
            'import-programs',
            array($this, 'render_import_page')
        );
    }

    public function render_import_page() {
        if (isset($_POST['import_submit']) && check_admin_referer('jkmpm_import_programs')) {
            $this->handle_import();
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Import Programs', 'jkmpm'); ?></h1>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('jkmpm_import_programs'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php _e('CSV File', 'jkmpm'); ?></label>
                        </th>
                        <td>
                            <input type="file" 
                                   name="csv_file" 
                                   id="csv_file" 
                                   accept=".csv" 
                                   required>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Import', 'jkmpm'), 'primary', 'import_submit'); ?>
            </form>
        </div>
        <?php
    }

    public function jkmpm_enque_styles_and_scripts( $hook ) {
        global $post;
        global $pagenow;

        //enque on our admin page
        if (isset($post) && $post->post_type === 'jkm_programs' && in_array($pagenow, ['post.php', 'post-new.php'])) {
            wp_enqueue_style('jkmpm-admin-style', JKMPM_URL . 'admin/assets/css/jkmpm-admin.css', array(), JKMPM_VERSION);
        }
    }

}
endif;