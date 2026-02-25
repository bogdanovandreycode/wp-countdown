<?php

namespace CountdownPlugin;

use CountdownPlugin\Controllers\Shortcodes\CountdownVcBlock;
use WpToolKit\Controller\ViewLoader;

class Main
{
    public function __construct(
        private string $pluginFilePath,
        private string $pluginDirPath,
        private ViewLoader $views,
    ) {
        new CountdownVcBlock($this->pluginFilePath, $this->pluginDirPath);
    }
}
