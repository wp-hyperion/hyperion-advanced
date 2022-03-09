<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Hyperion
 *
 * @wordpress-plugin
 * Plugin Name:       Hyperion
 * Description:       Futurist ship running through wordpress
 * Version:           1.0.0
 * Author:            Grégory COLLIN
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hyperion
 * Domain Path:       /languages
 */

if ( ! defined('WPINC' ) ) {
    die;
}

add_action('init', [\Hyperion\Hyperion::class, 'init']);
add_action(\Hyperion\Loader\HyperionLoader::HYPERION_CONTAINER_READY, function (\Hyperion\Loader\Service\ContainerEngine $containerEngine) {
    $containerEngine->getContainer()->get(\Hyperion\Hyperion::class)->ignition();
});