<?php 
if (!defined('ABSPATH')) exit;
if (!isset($stats)) return;
?>
<h2>Estadísticas de Autoload</h2>
<div class="wam-stats-grid">
    <div class="wam-stat-box">
        <h3>Tamaño Total de Autoload</h3>
        <p><?php echo size_format($stats->autoload_size, 2); ?></p>
    </div>
    <div class="wam-stat-box">
        <h3>Opciones con Autoload</h3>
        <p><?php echo number_format($stats->autoload_count); ?> de <?php echo number_format($stats->total_count); ?></p>
    </div>
    <div class="wam-stat-box">
        <h3>Porcentaje de Uso</h3>
        <p><?php echo round(($stats->autoload_size / $stats->total_size) * 100, 2); ?>%</p>
    </div>
</div>
