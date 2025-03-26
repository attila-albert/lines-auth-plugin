<?php
/**
 * Autoloader for Lines Auth Plugin.
 *
 * Dynamically loads class files from the plugin directory and its subdirectories.
 *
 * @param string $class The class name.
 */
function lines_auth_plugin_autoloader( $class ) {
    $class_file = strtolower( str_replace( '_', '-', $class ) ) . '.php';
    $plugin_dir = LINES_AUTH_PLUGIN_PATH;
    $iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $plugin_dir ) );
    foreach ( $iterator as $file ) :
        if ( $file->isFile() && $file->getFilename() === $class_file ) :
            require_once $file->getRealPath();
            return;
        endif;
    endforeach;
}
