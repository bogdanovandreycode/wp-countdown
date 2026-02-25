<?php
namespace CountdownPlugin;

use WpToolKit\Controller\ViewLoader;
use WpToolKit\Loader\AttributeLoader;
use CountdownPlugin\Main;

class Boot {
    public function __construct(
        private string $pluginFilePath,
    ) {
        $this->setUp_wp-countdown();

        add_action('init', [$this, 'hookInit_wp-countdown']);

        add_action('plugins_loaded', function () {
            load_plugin_textdomain(
                'wp-countdown-plugin',
                false,
                '/wp-countdown/languages/'
            );

            $this->hookPluginsLoaded_wp-countdown();
        });

        register_activation_hook( $pluginFilePath, [$this, 'activate_wp-countdown'] );
        register_deactivation_hook( $pluginFilePath, [$this, 'deactivate_wp-countdown'] );
    }

    public function setUp_wp-countdown(): void 
    {
        //TODO CODE
    }

    public function hookInit_wp-countdown(): void 
    {
        //TODO CODE
    }

    public function hookPluginsLoaded_wp-countdown(): void
    {
        $views = new ViewLoader();
        $views->loadFromYaml($this->pluginFilePath . '/config/views.yml', $this->pluginFilePath);

        $loader = new AttributeLoader(
            'CountdownPlugin\Controller\Route',
            plugin_dir_path(__FILE__) . 'Controller/Route',
        );

        $loader->loadRoutes();

        new Main(
            $this->pluginFilePath,
            $views
        );
    }

    public function activate_wp-countdown(): void 
    {
        //TODO CODE
    }

    public function deactivate_wp-countdown(): void 
    {
        //TODO CODE
    }
}