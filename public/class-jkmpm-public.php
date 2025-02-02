<?php
/**
 * JKM Program Manager Public
 * 
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-program-manager
 * @subpackage jkm-program-manager/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(!class_exists('JKMPM_Public')) :
class JKMPM_Public {

    public function __construct( ) {

        add_action('wp_ajax_get_schedule', array($this, 'jkmpm_ajax_get_schedule')); //loggedin users
        add_action('wp_ajax_nopriv_get_schedule', array($this, 'jkmpm_ajax_get_schedule')); //public users
    }

    public function jkmpm_enqueue_public_styles_and_scripts() {
        global $post;

        if( JKMPM_Utils::should_enqueue($post) ){
            $in_footer = apply_filters( 'jkmpm_enqueue_script_in_footer', true );
            $deps = array('jquery');
            wp_enqueue_style( 'jkmpm-public-style', JKMPM_URL . 'public/assets/css/jkmpm-public.css', array(), JKMPM_VERSION, 'all' );

            wp_register_script('jkmpm-public-script', JKMPM_URL .'public/assets/js/jkmpm-public.js', $deps, JKMPM_VERSION, $in_footer);
            wp_enqueue_script('jkmpm-public-script');   

            $public_var = array(
                'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
                'ajax_nonce'                    => wp_create_nonce('jkmpm_schedule_nonce'),
            );
            wp_localize_script('jkmpm-public-script', 'jkmpm_public_var', $public_var);

        }
    }

    /**
     * Shortcode rendering
     */
    public function shortcode_jkm_program_listing($atts) {
        $week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;
        return $this->render_schedule($week_offset);
    }

    /**
     * AJAX callback to get schedule
     */
    public function jkmpm_ajax_get_schedule() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'jkmpm_schedule_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $week_offset = isset($_POST['week_offset']) ? intval($_POST['week_offset']) : 0;
        $html = $this->render_schedule($week_offset);
        wp_send_json_success($html);
    }

    private function render_schedule($week_offset) {

        // Get current week's start and end dates
        $start_of_week = strtotime('monday this week ' . ($week_offset ? $week_offset . ' weeks' : ''));
        $end_of_week = strtotime('sunday this week ' . ($week_offset ? $week_offset . ' weeks' : ''));

        // Fetch programs for the current week
        $programs = $this->get_weekly_programs($start_of_week, $end_of_week);

        ob_start();
        ?>
        <div class="jkmpm-schedule-wrapper">
            <div class="jkmpm-navigation">
                <button class="jkmpm-prev-week" data-offset="<?php echo $week_offset - 1; ?>">
                    <?php _e('Previous Week', 'jkm-program-manager'); ?>
                </button>
                <span class="jkmpm-week-display">
                    <?php echo date('M d', $start_of_week) . ' - ' . date('M d', $end_of_week); ?>
                </span>
                <button class="jkmpm-next-week" data-offset="<?php echo $week_offset + 1; ?>">
                    <?php _e('Next Week', 'jkm-program-manager'); ?>
                </button>
            </div>

            <div class="jkmpm-schedule-grid">
                <?php
                $days = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
                foreach ($days as $day) {
                    $current_date = strtotime($day . ' this week ' . ($week_offset ? $week_offset . ' weeks' : ''));
                    ?>
                    <div class="jkmpm-day-column">
                        <h3 class="jkmpm-day-header">
                            <?php 
                            echo esc_html($day) . '<br>';
                            echo date('M d', $current_date);
                            ?>
                        </h3>
                        <div class="jkmpm-programs">
                            <?php
                            if (isset($programs[$day]) && !empty($programs[$day])) {
                                foreach ($programs[$day] as $program) {
                                    $this->render_program_card($program);
                                }
                            } else {
                                echo '<p class="jkmpm-no-programs">' . 
                                     __('No programs scheduled', 'jkm-program-manager') . 
                                     '</p>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_weekly_programs($start_date, $end_date) {
        $args = array(
            'post_type' => 'jkm_programs',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'jkm_program_start_date',
                    'value' => date('Y-m-d', $end_date),
                    'compare' => '<=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => 'jkm_program_end_date',
                    'value' => date('Y-m-d', $start_date),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );

        $programs_query = new \WP_Query($args);
        $programs = array();

        while ($programs_query->have_posts()) {
            $programs_query->the_post();
            $schedule = get_post_meta(get_the_ID(), 'jkm_program_schedule', true);
            $schedule = json_decode($schedule, true);

            if (!empty($schedule)) {
                foreach ($schedule as $day => $time) {
                    if (!isset($programs[$day])) {
                        $programs[$day] = array();
                    }

                    $programs[$day][] = array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                        'time' => $time
                    );
                }
            }
        }

        wp_reset_postdata();

        // Sort programs by time for each day
        foreach ($programs as $day => &$day_programs) {
            usort($day_programs, function($a, $b) {
                return strtotime($a['time']) - strtotime($b['time']);
            });
        }

        return $programs;
    }

    private function render_program_card($program) {
        ?>
        <div class="jkmpm-program-card">
            <?php if (!empty($program['thumbnail'])): ?>
                <img src="<?php echo esc_url($program['thumbnail']); ?>" 
                     alt="<?php echo esc_attr($program['title']); ?>"
                     class="jkmpm-program-thumbnail">
            <?php endif; ?>
            <div class="jkmpm-program-info">
                <h4 class="jkmpm-program-title">
                    <?php echo esc_html($program['title']); ?>
                </h4>
                <time class="jkmpm-program-time">
                    <?php echo esc_html(date('H:i', strtotime($program['time']))); ?>
                </time>
            </div>
        </div>
        <?php
    }
}
endif;