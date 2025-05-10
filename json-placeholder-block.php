<?php
/*
 * Plugin Name:     JSON Placeholder Block
 * Description:     A simple plugin that pulls in post data from JSONPlaceholder (jsonplaceholder.org) in a JSON format and adds a block where you can view this content on the front-end and back-end using React.
 * Author:          Matt Jennings
 * Author URI:      https://www.mattjennings.net/
 * Text Domain:     json-placeholder-block
 * Version:         1.0.0
 */

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

// Start the session if it's not already started
if (!session_id()) {
    session_start();
}

// Global vars
define('PLUGIN_NAME', 'JSON Placeholder Block');
define('PLUGIN_SLUG', 'json-placeholder-block');
define('PLUGIN_PREFIX', 'jsonplaceholder_mj_');

class JSON_Placeholder_Mock_API
{
    public static function init() {
        $self = new self();
        add_action('rest_api_init', array($self, PLUGIN_PREFIX . 'mock_block_wp_rest'));
        add_action('admin_menu', array($self, PLUGIN_PREFIX . 'settings_page'));
        add_action('admin_post_' . PLUGIN_PREFIX . 'save_settings', array(
            $self,
            PLUGIN_PREFIX . 'save_settings',
        ));
        add_action('admin_enqueue_scripts', array(
            $self,
            PLUGIN_PREFIX . 'enqueue_admin_styles',
        ));
        add_action('init', array($self, 'jsonplaceholder_mj_block'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(
            $self,
            PLUGIN_PREFIX . 'plugin_settings_link'
        ));
    }

    // https://example.com/wp-json/jsonplaceholder/v1/jsonplaceholder-option
    public function jsonplaceholder_mj_mock_block_wp_rest() {
        register_rest_route('jsonplaceholder/v1', '/jsonplaceholder-option', array(
            'methods'               => 'GET',
            'callback'              => array($this, 'jsonplaceholder_mj_get_option'),
            'permission_callback'   => '__return_true'
        ));
    }

    public function jsonplaceholder_mj_get_option() {
        // Get the option value from wp_options
        $option_value = get_option('jsonplaceholder_mj_jsonplaceholder_org');

        if ($option_value) {
            return new WP_REST_Response($option_value, 200);
        } else {
            return new WP_REST_Response('Option not found', 404);
        }
    }    

    public function jsonplaceholder_mj_settings_page() {
        add_options_page(
            PLUGIN_NAME,
            PLUGIN_NAME,
            'manage_options',
            PLUGIN_SLUG,
            [$this, PLUGIN_PREFIX . 'settings_page_html']
        );
    }

    public function jsonplaceholder_mj_settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = get_option(PLUGIN_PREFIX . 'jsonplaceholder_org');
        ?>
        <div class="wrap">
        <h2><?php echo PLUGIN_NAME; ?></h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div id="message" class="updated notice notice-success is-dismissible">
                <p><?php _e(
                    'Settings saved successfully.',
                    PLUGIN_PREFIX . 'notice'
                ); ?></p>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'):
            $error_msg = isset($_SESSION['flash_error'])
                ? $_SESSION['flash_error']
                : ''; ?>
            <div id="message" class="error notice notice-error is-dismissible">
            <p><?php _e(
                "Invalid input. {$error_msg}",
                PLUGIN_PREFIX . 'notice'
            ); ?></p>
            </div>
        <?php unset($_SESSION['flash_error']);
        endif; ?>

        <form action="<?php echo esc_url(
            admin_url('admin-post.php')
        ); ?>" method="post">
            <input type="hidden" name="action" value="<?php echo PLUGIN_PREFIX .
                'save_settings'; ?>">

            <?php
            wp_nonce_field(PLUGIN_PREFIX . 'save_settings_nonce');
            $this->jsonplaceholder_mj_url($options);
            ?>

            <p>
                <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e(
                    'Save'
                ); ?>" />
            </p>
        </form>
        </div>
        <?php
    }

    public function jsonplaceholder_mj_url($options) {
        $jsonplaceholder_url = isset($options['jsonplaceholder_url'])
            ? esc_attr($options['jsonplaceholder_url'])
            : '';
        $error_class =
            isset($_GET['status']) && $_GET['status'] == 'error'
                ? 'field-error'
                : '';
        ?>
        <h3>JSONPLaceHolder.org URL with JSON Data</h3>
        
        <div>
        <p>Please enter a single URL from <a href="https://www.jsonplaceholder.org/" target="_blank">jsonplaceholder.org</a> that displays posts using JSON like the examples below:</p>
        <ol>
            <li><a href="https://jsonplaceholder.org/posts" target="_blank">https://jsonplaceholder.org/posts</a></li>
            <li><a href="https://jsonplaceholder.org/posts/1" target="_blank">https://jsonplaceholder.org/posts/1</a></li>
            <li><a href="https://jsonplaceholder.org/posts/100" target="_blank">https://jsonplaceholder.org/posts/100</a></li>
            <li>Or look at <a href="https://www.jsonplaceholder.org/#posts" target="_blank">documentation on JSONPlaceholder.org about how to view posts or a post URL in a JSON format</a>. 
        </ol>
        </div>
        
            <label for="<?php echo PLUGIN_PREFIX; ?>jsonplaceholder_url">
            <h4>JSON URL</h4>            
            <p>
                <input type="text" name="<?php echo PLUGIN_PREFIX; ?>jsonplaceholder_url" id="<?php echo PLUGIN_PREFIX; ?>jsonplaceholder_url" class="<?php echo $error_class; ?>" value="<?php echo esc_attr($jsonplaceholder_url); ?>" size="50" />
            </p>
            </label>
        <?php
    }

    public function jsonplaceholder_mj_save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verify nonce
        if (
            !isset($_POST['_wpnonce']) ||
            !wp_verify_nonce(
                $_POST['_wpnonce'],
                PLUGIN_PREFIX . 'save_settings_nonce'
            )
        ) {
            wp_die(__('Nonce verification failed.', PLUGIN_PREFIX . 'notice'));
        }

        $options = isset($_POST[PLUGIN_PREFIX . 'jsonplaceholder_org'])
            ? $_POST[PLUGIN_PREFIX . 'jsonplaceholder_org']
            : [];

        // Validate the jsonplaceholder_url textarea input
        $jsonplaceholder_url = isset(
            $_POST[PLUGIN_PREFIX . 'jsonplaceholder_url']
        )
            ? $_POST[PLUGIN_PREFIX . 'jsonplaceholder_url']
            : '';
        $valid = true;
        $jsonplaceholder_posts_url = '/\bhttps?:\/\/jsonplaceholder\.org\/posts(\/([1-9][0-9]?|100))?$/i';

        $url = trim($jsonplaceholder_url);

        if (empty($url)) {
            $_SESSION['flash_error'] =
                'The <strong>JSON URL</strong> field must not be empty.';
            $valid = false;
        } elseif (!preg_match($jsonplaceholder_posts_url, $url)) {
            $_SESSION[
                'flash_error'
            ] = "The <code>{$url}</code> value in the <strong>JSON URL</strong> is not a valid <a href=\"jsonplaceholder.org\" target=\"blank\">jsonplaceholder.org</a> URL to show a post or posts in a JSON format (<a href=\"https://www.jsonplaceholder.org/#posts\" target=\"_blank\">see documentation</a>).";
            $valid = false;
        }

        if (!$valid) {
            // Redirect with an error message
            $redirect_url = add_query_arg(
                [
                    'page' => PLUGIN_SLUG,
                    'status' => 'error',
                ],
                admin_url('options-general.php')
            );
            wp_redirect($redirect_url);
            exit();
        }

        $options['jsonplaceholder_url'] = $url;

        // Update the options in the database
        update_option(PLUGIN_PREFIX . 'jsonplaceholder_org', $options);

        // Redirect back to the settings page with a success message
        $redirect_url = add_query_arg(
            [
                'page' => PLUGIN_SLUG,
                'status' => 'success',
            ],
            admin_url('options-general.php')
        );

        wp_redirect($redirect_url);
        exit();
    }

    public function jsonplaceholder_mj_enqueue_admin_styles($hook) {
        // Only enqueue the CSS on the plugin settings page
        if ($hook !== 'settings_page_' . PLUGIN_SLUG) {
            return;
        }

        wp_enqueue_style(
            PLUGIN_PREFIX . 'admin',
            plugin_dir_url(__FILE__) . 'dist/css/admin.min.css',
            [],
            '1.0.0',
            'all'
        );
    }

    public function jsonplaceholder_mj_plugin_settings_link($links) {
        $settings_url = admin_url('options-general.php?page=' . PLUGIN_SLUG);
        $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function jsonplaceholder_mj_block() {
        register_block_type( __DIR__ . '/build' );
    }
}

JSON_Placeholder_Mock_API::init();
?>