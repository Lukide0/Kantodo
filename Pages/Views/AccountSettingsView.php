<?php 

namespace Kantodo\Views;

use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

class AccountSettingsView implements IView
{
    public function render(array $params = [])
    {
        ?>
        <div class="container">
            <h2 class="row space-extreme-bottom" style="font-size: 2.8rem"><?= t('account') . ' - ' . t('settings');?></h2>
            <div class="row">
                <?= Input::text('userFirstname', t('firstname')) ?>
                <?= Input::text('userLastname', t('lastname')) ?>
            </div>
            <div class="row">
                <?= Input::text('userEmail', t('email')) ?>
            </div>
            <div class="row">
                <div class="container">
                    <div class="row">
                        <?= Input::text('userPasswordCurrent', t('password', 'auth')) ?>
                    </div>
                    <div class="row">
                        <?= Input::text('userPasswordNew', t('password_new', 'auth')) ?>
                        <?= Input::text('userPasswordNewConfirm', t('password_confirm', 'auth')) ?>
                    </div>
                </div>
            </div>
        <?php
    }
}

?>