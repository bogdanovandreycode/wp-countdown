<?php

namespace CountdownPlugin;

use WpToolKit\Controller\ViewLoader;
use WpToolKit\Loader\AttributeLoader;

class Boot
{
    public function __construct(
        private string $pluginFilePath,
        private string $pluginDirPath,
    ) {
        $this->setUpWpCountdown();

        add_action('init', [$this, 'hookInitWpCountdown']);

        add_action('plugins_loaded', function () {
            load_plugin_textdomain(
                'wp-countdown-plugin',
                false,
                '/wp-countdown/languages/'
            );

            $this->hookPluginsLoadedWpCountdown();
        });

        register_activation_hook($pluginFilePath, [$this, 'activateWpCountdown']);
        register_deactivation_hook($pluginFilePath, [$this, 'deactivateWpCountdown']);
    }

    public function setUpWpCountdown(): void
    {
        //TODO CODE
    }

    public function hookInitWpCountdown(): void
    {
        //TODO CODE
    }

    public function hookPluginsLoadedWpCountdown(): void
    {
        $views = new ViewLoader();

        $views->loadFromYaml(
            $this->pluginDirPath . '/configs/views.yml',
            $this->pluginDirPath
        );

        $loader = new AttributeLoader(
            'CountdownPlugin\Controllers\Routes',
            $this->pluginDirPath . '/src/Controllers/Routes',
        );

        $loader->loadRoutes();

        new Main(
            $this->pluginFilePath,
            $this->pluginDirPath,
            $views
        );
    }

    public function activateWpCountdown(): void
    {
        //TODO CODE
    }

    public function deactivateWpCountdown(): void
    {
        //TODO CODE
    }
}
