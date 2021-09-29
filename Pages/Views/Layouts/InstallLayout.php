<?php

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;
use Kantodo\Models\TeamModel;

/**
 * Layout na instalaci
 */
class InstallLayout extends Layout
{
    /**
     * Render
     *
     * @param   string  $content  kontent
     * @param   array<mixed>   $params   parametry
     *
     * @return  void
     */
    public function Render(string $content = '', array $params = [])
    {
        $title = $params['title'] ?? 'Kantodo - Install';
        $sectionName = $params['sectionName'] ?? '';
        $action = $params['action'] ?? "install-database";
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/main.min.css">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/install.min.css">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <script src="<?= Application::$SCRIPT_URL ?>/main.js"></script>
            <title><?= $title ?></title>
        </head>

        <body>
            <div class="container full middle padding-big">
                <form class="install" method="POST" action="<?= $action ?>">
                    <h2 class="space-small-bottom"><?= $title ?></h2>
                    <h3><?= $sectionName ?></h3>
                    <div class="container middle full-width">
                        <?= $content ?>
                    </div>
                    <button class="colored big full-width center space-huge-top">Potvrdit</button>
                </form>
            </div>
        </body>

        </html>
<?php

    }
}

?>