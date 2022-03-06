<?php

$dir = dirname(dirname(__FILE__));

return [
    'Kantodo\\'              => $dir . '/src/',

    // Pages
    'Kantodo\\Controllers\\' => $dir . '/pages/Controllers/',
    'Kantodo\\Models\\'      => $dir . '/pages/Models/',
    'Kantodo\\Views\\'       => $dir . '/pages/Views/',
    'Kantodo\\Middlewares\\' => $dir . '/pages/Middlewares/',
    'Kantodo\\Widgets\\'     => $dir . '/pages/Widgets/',

    // API
    'Kantodo\\API\\Controllers\\' => $dir . '/api/Controllers/',

    // Migrations
    'Migrations\\'           => $dir . '/migrations/Versions',
];
