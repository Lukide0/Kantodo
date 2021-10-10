<?php

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;
use Kantodo\Models\TeamModel;

/**
 * Layout pro uživatele
 */
class ClientLayout extends Layout
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
        $headerContent = Application::$APP->header->GetContent();
        $userName = Application::$APP->session->get('user')['firstname'] . ' ' . Application::$APP->session->get('user')['lastname'];

        // TODO: generovat menu z array
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>main.css">
            <script src="<?= Application::$SCRIPT_URL ?>main.js"></script>
            <?=$headerContent;?>
        </head>
        <body>
            <header>
            <h1>Kantodo</h1>
            <nav>
                <a class="item active" href="/">
                    <span class="icon outline medium">dashboard</span>
                    <span class="text">Dashboard</span>
                </a>
                <a class="item" href="/calendar">
                    <span class="icon outline medium">event</span>
                    <span class="text">Kalendář</span>
                </a>
                <div class="item dropdown">
                    <div>
                        <span class="icon outline medium">folder</span>
                        <span class="text">Projekty</span>
                    </div>
                    <ul>
                        <?php 
                        foreach ($params['projects'] ?? [] as $project):
                        ?>
                        <li data-id='<?= $project['uuid'] ?>'><?= $project['name'] ?></li>
                        <?php endforeach; ?>
                        <li class="add"><button class="flat no-border" data-click='project'>Přidat</button></li>
                    </ul>
                </div>
                <a class="item last" href="/account">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text"><?= $userName ?></span>
                </a>
            </nav>
            <script>
                let btn = document.querySelector('button[data-click=project]');
                btn.addEventListener('click', function(e) {
                    // TODO: projekt přidat
                });

            </script>
        </header>
        <main>
            <?= $content ?>
        </main>
        </body>

        </html>
<?php

    }
}

?>