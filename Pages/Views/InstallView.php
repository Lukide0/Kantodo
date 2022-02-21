<?php

declare(strict_types = 1);

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

        if ($page == 0) {
?>
            <?= Input::text('dbName', t('name_of_db', 'install')) ?>
            <?= Input::text('dbUser', t('username', 'install')) ?>
            <?= Input::password('dpPass', t('password', 'auth')) ?>
            <?= Input::text('dbHost', t('database_server', 'install')) ?>
            <?= Input::text('dbPrefix', t('table_prefix', 'install')) ?>
<?php
        }
        else if ($page == 1) 
        {
?>
    <label class="text-field">
        <div class="field">
            <span>Složka cache</span>
            <input type="text" name="folderCache" value="<?= Application::$ROOT_DIR . '/App/Cache'?>">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
    <label class="text-field">
        <div class="field">
            <span>Zálohová složka</span>
            <input type="text" name="folderBackup" value="<?= Application::$ROOT_DIR . '/App/Backup'?>">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
<?php
    }
    else {
    ?>
    <label class="text-field outline">
        <div class="field">
            <span>Jméno</span>
            <input type="text" name="firstname">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
    <label class="text-field outline">
        <div class="field">
            <span>Příjmení</span>
            <input type="text" name="lastname">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
    <label class="text-field outline">
        <div class="field">
            <span>email</span>
            <input type="email" name="email">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
    <label class="text-field outline">
        <div class="field">
            <span>Heslo</span>
            <input type="password" name="password">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
    <label class="text-field outline">
        <div class="field">
            <span>Heslo znovu</span>
            <input type="password" name="controlPassword">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
<?php
    }
        
}
}

?>