<?php

$dir = dirname(dirname(__FILE__));

return [
    'Kantodo\\'              => $dir . '/Src/',

    // Pages
    'Kantodo\\Controllers\\' => $dir . '/Pages/Controllers/',
    'Kantodo\\Models\\'      => $dir . '/Pages/Models/',
    'Kantodo\\Views\\'       => $dir . '/Pages/Views/',
    'Kantodo\\Middlewares\\' => $dir . '/Pages/Middlewares/',
    'Kantodo\\Widgets\\'     => $dir . '/Pages/Widgets/',

    // API
    'Kantodo\\API\\Controllers\\' => $dir . '/api/Controllers/',

    // Migrations
    'Migrations\\'           => $dir . '/Migrations/Versions',
];
