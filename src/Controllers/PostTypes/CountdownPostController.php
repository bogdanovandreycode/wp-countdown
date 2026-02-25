<?php

namespace CountdownPlugin\Controllers\PostTypes;

use WpToolKit\Controller\PostController;
use WpToolKit\Entity\Post;

class CountdownPostController extends PostController
{
    public function __construct()
    {
        parent::__construct(new Post(
            name: 'countdown',
            title: 'Обратный отсчёт',
            icon: 'dashicons-clock',
            role: 'manage_options',
            supports: ['title'],
            public: true,
            rest: false,
            position: 25,
        ));

        $this->addToMenu();
    }
}
