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
     * @param   array   $params   parametry
     *
     * @return  void
     */
    public function Render(string $content = '', array $params = [])
    {
        $headerContent = Application::$APP->header->GetContent();

        // TODO: generovat menu z array
        //$userID = Application::$APP->session->get('user')['id'];

        $path = Application::$URL_PATH;
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= $path ?>/styles/main.css">
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
                <div class="item dropdown expanded">
                    <div>
                        <span class="icon outline medium">folder</span>
                        <span class="text">Projekty</span>
                    </div>
                    <ul>
                        <li>KantodoApp</li>
                        <li class="add">Přidat</li>
                    </ul>
                </div>
                <a class="item last" href="/account">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text">Účet</span>
                </a>
            </nav>
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