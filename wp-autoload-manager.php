<?php
namespace WAM;

/**
 * Plugin Name: WP Autoload Manager
 * Description: Manages automatic loading of WordPress options.
 * Version: 1.1
 * Author: Alexis Olivero
 * author URI: https://oliverodev.com
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
        return $wpdb->get_row("
            SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(LENGTH(option_value)), 0) as total_size,
                COALESCE(SUM(CASE WHEN autoload = 'yes' THEN LENGTH(option_value) ELSE 0 END), 0) as autoload_size,
                COUNT(CASE WHEN autoload = 'yes' THEN 1 END) as autoload_count
            FROM {$wpdb->options}
            WHERE option_name NOT LIKE '_transient%'
        ");
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
            wp_die(__('Acceso denegado.'));
        }

        // Procesar formulario
        if ($this->processForm()) {
            echo '<div class="notice notice-success is-dismissible"><p>Autoload actualizado correctamente.</p></div>';
        }

        // Obtener datos
        $stats = $this->getAutoloadStats();
        $options = $this->getOptions();

        if (!$stats || !$options) {
            echo '<div class="notice notice-error"><p>Error al obtener datos.</p></div>';
            return;
        }

        // Renderizar vistas
        include WAM_VIEWS_DIR . 'admin-page.php';
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

        $files = [
            'admin-page.php' => 'getAdminTemplate',
            'styles.php' => 'getStylesTemplate'
        ];

        foreach ($files as $file => $method) {
            $filepath = WAM_VIEWS_DIR . $file;
            if (!file_exists($filepath)) {
                file_put_contents($filepath, $this->$method());
            }
        }
    }
}

// Inicializar el plugin
new AutoloadManager();
