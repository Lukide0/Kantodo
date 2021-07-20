<?php 

$dir = dirname(dirname(__FILE__));

return array(
    'Kantodo\\' => $dir . '/Src/',
    
    // Pages
    'Kantodo\\Controllers\\' => $dir . '/Pages/Controllers/',
    'Kantodo\\Models\\' => $dir . '/Pages/Models/',
    'Kantodo\\Views\\' => $dir . '/Pages/Views/',
    'Kantodo\\Middlewars\\' => $dir . '/Pages/Middlewares/',
    'Kantodo\\Widgets\\' => $dir . '/Pages/Widgets/',

    // Migrations
    'Migrations\\' => $dir . '/Migrations/Versions'
);

?>