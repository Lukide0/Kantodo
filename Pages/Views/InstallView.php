<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

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
        <label class="text-field outline">
            <div class="field">
                <span>Název databáze</span>
                <input type="text" name="dbName">
            </div>
            <div class="text">
                Lorem ipsum dolor sit amet.
            </div>
        </label>
        <label class="text-field outline">
            <div class="field">
                <span>Uživatelké jméno</span>
                <input type="text" name="dbUser">
            </div>
            <div class="text">
                Lorem ipsum dolor sit amet.
            </div>
        </label>
        <label class="text-field outline">
            <div class="field">
                <span>Heslo</span>
                <input type="password" name="dbPass">
            </div>
            <div class="text">
                Lorem ipsum dolor sit amet.
            </div>
        </label>
        <label class="text-field outline">
            <div class="field">
                <span>Databázový server</span>
                <input type="text" name="dbHost">
            </div>
            <div class="text">
                Lorem ipsum dolor sit amet.
            </div>
        </label>
        <label class="text-field outline">
            <div class="field">
                <span>Předpona tabulek</span>
                <input type="text" name="dbPrefix">
            </div>
            <div class="text">
                Lorem ipsum dolor sit amet.
            </div>
        </label>
<?php
    }
    else if ($page == 1) 
    {
    ?>
    <label class="text-field">
        <div class="field">
            <span>Složka s daty</span>
            <input type="text" name="folderData" value="<?= Application::$ROOT_DIR . '/App/Data'?>">
        </div>
        <div class="text">
            Lorem ipsum dolor sit amet.
        </div>
    </label>
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
            <span>Složka s dočasnými soubory</span>
            <input type="text" name="folderTmp" value="<?= Application::$ROOT_DIR . '/App/TMP'?>">
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
    <label class="text-field">
        <div class="field">
            <span>Složka s pluginy</span>
            <input type="text" name="folderPlugin" value="<?= Application::$ROOT_DIR . '/App/Plugin'?>">
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