<?php
/**
 * To import programs data as csv file.
 *
 * @package    jkm-program-manager
 * @subpackage jkm-program-manager/includes
 */

if(!defined('WPINC')){  die; }

if(!class_exists('JKMPM_Import_Handle')) :
class JKMPM_Import_Handle {
    
    public function run_import($file) {
        $results = array(
            'success' => false,
            'imported' => 0,
            'errors' => array(),
            'messages' => array()
        );

        // Validate file upload
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $results['errors'][] = __('Error uploading file', 'jkm-program-manager');
            return $results;
        }

        // Check file type
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'csv') {
            $results['errors'][] = __('Please upload a valid CSV file', 'jkm-program-manager');
            return $results;
        }

        // Open file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $results['errors'][] = __('Error reading file', 'jkm-program-manager');
            return $results;
        }

        // Get and validate headers
        $headers = fgetcsv($handle);
        if (!$this->validate_headers($headers)) {
            $results['errors'][] = __('Invalid CSV format. Please check the template format.', 'jkm-program-manager');
            fclose($handle);
            return $results;
        }

        $row = 2; // Start at row 2 (after headers)
        $imported = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $import_result = $this->process_row($data, $headers);
            
            if (is_wp_error($import_result)) {
                $results['errors'][] = sprintf(
                    __('Row %d: %s', 'jkm-program-manager'),
                    $row,
                    $import_result->get_error_message()
                );
            } else {
                $imported++;
                if ( 'updated' == $import_result['action'] || 'created' == $import_result['action'] ) {
                    $results['messages'][] = sprintf(
                        __('Row %d: Updated program "%s"', 'jkm-program-manager'),
                        $row,
                        $import_result['title']
                    );
                }
            }
            $row++;
        }

        fclose($handle);

        $results['success'] = true;
        $results['imported'] = $imported;
        return $results;
    }

    /**
     * Validate CSV headers
     */
    private function validate_headers($headers) {
        $required_headers = array(
            'Program Name',
            'Program Description',
            'Program Start Date',
            'Program End Date',
            'Program Thumbnail',
            'Broadcast Schedule'
        );

        foreach ($required_headers as $required) {
            if (!in_array($required, $headers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process a single row from the CSV
     */
    private function process_row($data, $headers) {
        // Map CSV columns to data
        $program_data = array_combine($headers, $data);
        
        // Validate required fields
        $required_fields = array(
            'Program Name',
            'Program Start Date',
            'Program End Date'
        );

        foreach ($required_fields as $field) {
            if (empty($program_data[$field])) {
                return new \WP_Error(
                    'missing_required',
                    sprintf(__('Missing required field: %s', 'jkm-program-manager'), $field)
                );
            }
        }

        // Validate dates
        foreach (['Program Start Date', 'Program End Date'] as $date_field) {
            if (!$this->validate_date($program_data[$date_field])) {
                return new \WP_Error(
                    'invalid_date',
                    sprintf(__('Invalid date format in field: %s. Use YYYY-MM-DD format.', 'jkm-program-manager'), $date_field)
                );
            }
        }

        // Validate schedule JSON
        if (!empty($program_data['Broadcast Schedule'])) {
            $schedule = json_decode($program_data['Broadcast Schedule'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new \WP_Error(
                    'invalid_schedule',
                    __('Invalid broadcast schedule format. Must be valid JSON.', 'jkm-program-manager')
                );
            }

            // Validate schedule format
            if (!$this->validate_schedule_format($schedule)) {
                return new \WP_Error(
                    'invalid_schedule_format',
                    __('Invalid schedule format. Must be {"day": "time"} format.', 'jkm-program-manager')
                );
            }
        }

        // Check if program already exists
        $existing_program = $this->get_existing_program($program_data['Program Name']);
        
        // Prepare post data
        $post_data = array(
            'post_title'   => sanitize_text_field($program_data['Program Name']),
            'post_content' => wp_kses_post($program_data['Program Description']),
            'post_type'    => 'jkm_programs',
            'post_status'  => 'publish'
        );

        if ($existing_program) {
            $post_data['ID'] = $existing_program->ID;
            $action = 'updated';
        } else {
            $action = 'created';
        }

        // Insert or update post
        $post_id = wp_insert_post($post_data);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Update meta fields
        update_post_meta($post_id, 'jkm_program_start_date', sanitize_text_field($program_data['Program Start Date']));
        update_post_meta($post_id, 'jkm_program_end_date', sanitize_text_field($program_data['Program End Date']));
        
        if (!empty($program_data['Broadcast Schedule'])) {
            update_post_meta($post_id, 'jkm_program_schedule', sanitize_text_field($program_data['Broadcast Schedule']));
        }

        // Handle thumbnail
        if (!empty($program_data['Program Thumbnail'])) {
            $this->handle_thumbnail($post_id, $program_data['Program Thumbnail']);
        }

        return array(
            'action' => $action,
            'title' => $program_data['Program Name']
        );
    }

    /**
     * Validate date format
     */
    private function validate_date($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate schedule format
     */
    private function validate_schedule_format($schedule) {
        if (!is_array($schedule)) {
            return false;
        }

        $valid_days = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
        
        foreach ($schedule as $day => $time) {
            if (!in_array($day, $valid_days)) {
                return false;
            }
            
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check for existing program by name
     */
    private function get_existing_program($program_name) {
        $args = array(
            'post_type' => 'jkm_programs',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'title' => $program_name
        );

        $query = new \WP_Query($args);
        return $query->have_posts() ? $query->posts[0] : null;
    }

    /**
     * Handle thumbnail upload/attachment
     */
    private function handle_thumbnail($post_id, $thumbnail_url) {
        if (filter_var($thumbnail_url, FILTER_VALIDATE_URL)) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Download file to temp dir
            $tmp = download_url($thumbnail_url);

            if (is_wp_error($tmp)) {
                return;
            }

            $file_array = array(
                'name' => basename($thumbnail_url),
                'tmp_name' => $tmp
            );

            // Set thumbnail
            $thumbnail_id = media_handle_sideload($file_array, $post_id);

            if (!is_wp_error($thumbnail_id)) {
                set_post_thumbnail($post_id, $thumbnail_id);
            }

            // Clean up temp file
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
        }
    }
}
endif;