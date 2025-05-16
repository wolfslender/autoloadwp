<?php
namespace WAM;

/**
 * Plugin Name: WP Autoload Manager
 * Description: Manages automatic loading of WordPress options.
 * Version: 1.1
 * Author: Alexis Olivero
 * author URI: https://oliverodev.pages.dev/
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('WAM_VERSION', '1.1');
define('WAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WAM_VIEWS_DIR', WAM_PLUGIN_DIR . 'views/');

/**
 * Clase principal del plugin
 */
class AutoloadManager {
    /**
     * @var string Capacidad requerida para gestionar el plugin
     */
    const REQUIRED_CAPABILITY = 'manage_options';

    /**
     * @var string Slug del menú
     */
    const MENU_SLUG = 'wp-autoload-manager';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_head', [$this, 'addAdminStyles']);
        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    /**
     * Agrega el menú de administración
     */
    public function addAdminMenu() {
        add_menu_page(
            'Autoload Manager',
            'Autoload Manager',
            self::REQUIRED_CAPABILITY,
            self::MENU_SLUG,
            [$this, 'renderAdminPage'],
            'dashicons-performance'
        );
    }

    /**
     * Obtiene estadísticas de autoload
     * @return object|null
     */
    private function getAutoloadStats() {
        global $wpdb;
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(LENGTH(option_value)), 0) as total_size,
                COALESCE(SUM(CASE WHEN autoload = 'yes' THEN LENGTH(option_value) ELSE 0 END), 0) as autoload_size,
                COUNT(CASE WHEN autoload = 'yes' THEN 1 END) as autoload_count
            FROM {$wpdb->options}
            WHERE option_name NOT LIKE '_transient%'
        ");

        // Asegurar que no hay valores nulos
        if ($stats) {
            $stats->total_size = max($stats->total_size, 0);
            $stats->autoload_size = max($stats->autoload_size, 0);
            $stats->total_count = max($stats->total_count, 0);
            $stats->autoload_count = max($stats->autoload_count, 0);
        }

        return $stats;
    }

    /**
     * Obtiene la lista de opciones
     * @return array
     */
    private function getOptions() {
        global $wpdb;
        return $wpdb->get_results("
            SELECT 
                option_id, 
                option_name, 
                autoload,
                LENGTH(option_value) as size 
            FROM {$wpdb->options}
            WHERE option_name NOT LIKE '_transient%'
            ORDER BY LENGTH(option_value) DESC
        ");
    }

    /**
     * Actualiza el estado de autoload
     * @param int $optionId
     * @param string $autoload
     * @return bool
     */
    private function updateAutoloadStatus($optionId, $autoload) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->options,
            ['autoload' => $autoload],
            ['option_id' => $optionId],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Renderiza la página de administración
     */
    public function renderAdminPage() {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(__('Access denied.'));
        }

        // Procesar formulario
        if ($this->processForm()) {
            echo '<div class="notice notice-success is-dismissible"><p>Autoload updated successfully.</p></div>';
        }

        // Obtener datos
        $stats = $this->getAutoloadStats();
        $options = $this->getOptions();

        if (!$stats || !$options) {
            echo '<div class="notice notice-error"><p>Error retrieving data.</p></div>';
            return;
        }

        // Renderizar directamente en lugar de usar include
        ?>
        <div class="wrap">
            <h1>WordPress Autoload Manager</h1>
            <div class="wam-stats">
                <h2>Autoload Statistics</h2>
                <div class="wam-stats-grid">
                    <div class="wam-stat-box">
                        <h3>Total Autoload Size</h3>
                        <p><?php echo size_format($stats->autoload_size, 2); ?></p>
                    </div>
                    <div class="wam-stat-box">
                        <h3>Autoload Options</h3>
                        <p><?php echo number_format($stats->autoload_count); ?> of <?php echo number_format($stats->total_count); ?></p>
                    </div>
                    <div class="wam-stat-box">
                        <h3>Usage Percentage</h3>
                        <p><?php echo round(($stats->autoload_size / max($stats->total_size, 1)) * 100, 2); ?>%</p>
                    </div>
                </div>
            </div>

            <div class="wam-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Option Name</th>
                            <th>Size</th>
                            <th>Autoload</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($options as $option): 
                        $is_autoload = $option->autoload === 'yes';
                        $size = size_format($option->size, 2);
                    ?>
                        <tr>
                            <td><?php echo esc_html($option->option_name); ?></td>
                            <td><?php echo esc_html($size); ?></td>
                            <td>
                                <span class="autoload-status <?php echo $is_autoload ? 'enabled' : 'disabled'; ?>">
                                    <?php echo $is_autoload ? 'ON' : 'OFF'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="post">
                                    <?php wp_nonce_field('wam_update_autoload'); ?>
                                    <input type="hidden" name="option_id" value="<?php echo esc_attr($option->option_id); ?>">
                                    <input type="hidden" name="autoload" value="<?php echo $is_autoload ? 'no' : 'yes'; ?>">
                                    <button type="submit" name="update_autoload" class="button <?php echo $is_autoload ? 'button-secondary' : 'button-primary'; ?>">
                                        <?php echo $is_autoload ? 'Disable' : 'Enable'; ?> Autoload
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Procesa el formulario de actualización
     * @return bool
     */
    private function processForm() {
        if (!isset($_POST['update_autoload'])) {
            return false;
        }

        check_admin_referer('wam_update_autoload');

        $optionId = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
        $autoload = filter_input(INPUT_POST, 'autoload', FILTER_SANITIZE_STRING);

        if (!$optionId || !in_array($autoload, ['yes', 'no'])) {
            return false;
        }

        return $this->updateAutoloadStatus($optionId, $autoload);
    }

    /**
     * Agrega estilos de administración
     */
    public function addAdminStyles() {
        if (!$this->isPluginPage()) {
            return;
        }
        include WAM_VIEWS_DIR . 'styles.php';
    }

    /**
     * Verifica si estamos en la página del plugin
     * @return bool
     */
    private function isPluginPage() {
        return isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG;
    }

    /**
     * Activación del plugin
     */
    public function activate() {
        $this->createViewFiles();
    }

    /**
     * Crea los archivos de vistas
     */
    private function createViewFiles() {
        if (!file_exists(WAM_VIEWS_DIR)) {
            mkdir(WAM_VIEWS_DIR, 0755, true);
        }

        $templates = [
            'admin-page.php' => $this->getAdminTemplate(),
            'styles.php' => $this->getStylesTemplate(),
            'stats.php' => $this->getStatsTemplate(),
            'table.php' => $this->getTableTemplate()
        ];

        foreach ($templates as $file => $content) {
            $filepath = WAM_VIEWS_DIR . $file;
            if (!file_exists($filepath)) {
                file_put_contents($filepath, $content);
            }
        }
    }

    /**
     * Obtiene la plantilla de administración
     * @return string
     */
    private function getAdminTemplate() {
        return '<?php defined("ABSPATH") || exit; ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">WordPress Autoload Manager</h1>
            <div id="wam-content">
                <?php include WAM_VIEWS_DIR . "stats.php"; ?>
                <?php include WAM_VIEWS_DIR . "table.php"; ?>
            </div>
        </div>';
    }

    /**
     * Obtiene la plantilla de estilos
     * @return string
     */
    private function getStylesTemplate() {
        return '<style>
            .wam-stats { margin: 20px 0; }
            .wam-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .wam-stat-box {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                text-align: center;
            }
            .wam-stat-box h3 {
                margin: 0 0 10px 0;
                color: #23282d;
            }
            .wam-stat-box p {
                font-size: 24px;
                margin: 0;
                color: #007cba;
            }
            .autoload-status {
                padding: 4px 8px;
                border-radius: 3px;
                font-weight: bold;
            }
            .autoload-status.enabled {
                background: #00a32a;
                color: white;
            }
            .autoload-status.disabled {
                background: #ccd0d4;
                color: #23282d;
            }
            .button {
                margin: 2px !important;
            }
        </style>';
    }

    /**
     * Obtiene la plantilla de estadísticas
     * @return string
     */
    private function getStatsTemplate() {
        return '<?php
        defined("ABSPATH") || exit;
        $autoload_size = size_format($stats->autoload_size, 2);
        $total_count = number_format($stats->total_count);
        $autoload_count = number_format($stats->autoload_count);
        $percentage = round(($stats->autoload_size / max($stats->total_size, 1)) * 100, 2);
        ?>
        <div class="wam-stats">
            <h2>Autoload Statistics</h2>
            <div class="wam-stats-grid">
                <div class="wam-stat-box">
                    <h3>Total Autoload Size</h3>
                    <p><?php echo esc_html($autoload_size); ?></p>
                </div>
                <div class="wam-stat-box">
                    <h3>Autoload Options</h3>
                    <p><?php echo esc_html($autoload_count); ?> of <?php echo esc_html($total_count); ?></p>
                </div>
                <div class="wam-stat-box">
                    <h3>Usage Percentage</h3>
                    <p><?php echo esc_html($percentage); ?>%</p>
                </div>
            </div>
        </div>';
    }

    /**
     * Obtiene la plantilla de tabla
     * @return string
     */
    private function getTableTemplate() {
        return '<?php
        defined("ABSPATH") || exit;
        ?>
        <div class="wam-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Option Name</th>
                        <th>Size</th>
                        <th>Autoload</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($options as $option):
                        $is_autoload = $option->autoload === "yes";
                        $size = size_format($option->size, 2);
                    ?>
                    <tr>
                        <td><?php echo esc_html($option->option_name); ?></td>
                        <td><?php echo esc_html($size); ?></td>
                        <td>
                            <span class="autoload-status <?php echo $is_autoload ? "enabled" : "disabled"; ?>">
                                <?php echo $is_autoload ? "ON" : "OFF"; ?>
                            </span>
                        </td>
                        <td>
                            <form method="post">
                                <?php wp_nonce_field("wam_update_autoload"); ?>
                                <input type="hidden" name="option_id" value="<?php echo esc_attr($option->option_id); ?>">
                                <input type="hidden" name="autoload" value="<?php echo $is_autoload ? "no" : "yes"; ?>">
                                <button type="submit" name="update_autoload" class="button <?php echo $is_autoload ? "button-secondary" : "button-primary"; ?>">
                                    <?php echo $is_autoload ? "Disable" : "Enable"; ?> Autoload
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>';
    }
}

// Inicializar el plugin
new AutoloadManager();
