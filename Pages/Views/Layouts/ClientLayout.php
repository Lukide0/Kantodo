<?php

namespace Kantodo\Views\Layouts;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Layout;
use function Kantodo\Core\Functions\t_;

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

        // TODO: generovat menu z array
        //$userID = Application::$APP->session->get('user')['id'];
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/main.min.css">
            <script src="<?= Application::$SCRIPT_URL ?>main.js"></script>
            <script src="<?= Application::$SCRIPT_URL ?>global.js" type="module"></script>
            <?=$headerContent;?>
        </head>
        <body>
            <script>
                function afterLoad(callback) {
                    document.addEventListener('load', callback, {once: true});
                }
            </script>
            <header>
            <h1>Kantodo</h1>
            <nav>
                <a class="item active" href="/">
                    <span class="icon outline medium">dashboard</span>
                    <span class="text"><?= t_('dashboard') ?></span>
                </a>
                <a class="item" href="/calendar">
                    <span class="icon outline medium">event</span>
                    <span class="text"><?= t_('calendar') ?></span>
                </a>
                <div class="item dropdown expanded">
                    <div>
                        <span class="icon outline medium">folder</span>
                        <span class="text"><?= t_('projects') ?></span>
                    </div>
                    <ul>
                        <?php 
                        foreach ($params['projects'] ?? [] as $project):
                        ?>
                        <li data-id='<?= $project['uuid'] ?>'><?= $project['name'] ?></li>
                        <?php endforeach; ?>
                        <li class="add"><button class="flat no-border" data-action="project"><?= t_('add') ?></button></li>
                    </ul>
                </div>
                <a class="item last" href="/account">
                    <span class="icon outline medium">account_circle</span>
                    <span class="text"><?= t_('account') ?></span>
                </a>
                <script>
                    afterLoad(
                        function() {
                            console.log("a");
                            let btn = document.querySelector("button[data-action=project]");
                            let win = Modal.ModalProject.create();
                            win.setParent(document.body.querySelector('main'));
                            btn.addEventListener('click', function(e) {
                                win.show();
                            });
                        }
                    );
                    
                </script>
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