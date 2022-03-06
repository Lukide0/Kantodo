<?php

declare(strict_types=1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

/**
 * Instalace
 */
class InstallView implements IView
{
    public function render(array $params = [])
    {
        $lang = Application::$APP->lang;
        $lang->load('install');

        $page = $params['page'] ?? 0;
        $opt = [
            'error' => Application::$APP->session->getFlashMessage('errors', [])
        ];

        if ($page == 0) {
?>
            <?= Input::text('dbName', t('name_of_db', 'install'), $opt) ?>
            <?= Input::text('dbUser', t('username', 'install'), $opt) ?>
            <?= Input::password('dbPass', t('password', 'auth'), $opt) ?>
            <?= Input::text('dbHost', t('database_server', 'install'), $opt) ?>
            <?= Input::text('dbPrefix', t('table_prefix', 'install'), $opt) ?>
<?php
        }
        else if ($page == 1) 
        {
            $defaultCache = Application::$ROOT_DIR . '/App/Cache';
            $defaultBackup = Application::$ROOT_DIR . '/App/Backup';
?>
            <?= Input::text('folderCache', t('cache_folder', 'install'), array_merge($opt, ['value' => $defaultCache])) ?>
            <?= Input::text('folderBackup', t('backup_folder', 'install'), array_merge($opt, ['value' => $defaultBackup])) ?>
<?php
        }
        else {
?>
            <?= Input::text('firstname', t('firstname'), $opt) ?>
            <?= Input::text('lastname', t('lastname'), $opt) ?>
            <?= Input::text('email', t('email'), $opt) ?>
            <?= Input::password('password', t('password', 'auth'), $opt) ?>
            <?= Input::password('controlPassword', t('password_again', 'auth'), $opt) ?>
<?php
        }
        
    }
}

?>