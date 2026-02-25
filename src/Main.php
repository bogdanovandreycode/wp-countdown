<?php

namespace CountdownPlugin;

use WpToolKit\Controller\ViewLoader;

class Main {
    public function __construct(
        private string $pluginFilePath,
        private ViewLoader $views,
    ) { 
        //TODO CODE
    }
}