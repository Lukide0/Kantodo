<?php 

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

class AccountSettingsView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->setTitle("Kantodo - Account settings");

        ?>
        <div class="container">
            <h2 class="row space-extreme-bottom" style="font-size: 2.8rem"><?= t('account') . ' - ' . t('settings');?></h2>
            <div class="row">
                <?= Input::text('userFirstname', t('firstname'), ['value' => $params['firstname'], 'classes' => "disabled"]) ?>
                <?= Input::text('userLastname', t('lastname'), ['value' => $params['lastname'], 'classes' => "disabled space-medium-left"]) ?>
            </div>
            <div class="row">
                <?= Input::text('userEmail', t('email'), ['value' => $params['email'], 'classes' => "disabled"]) ?>
            </div>
            <div class="row">
                <button class="error action" data-action='account'><?= t('delete_account', 'settings') ?></button>
                <script>
                    let btn = document.querySelector('[data-action=account]');
                    btn.onclick = function() 
                    {
                        let dialog = Modal.Dialog.create(
                                '<?= t("confirm") ?>',
                                `
                                <p class='space-big-bottom'><?= t("do_you_want_delete_your_account", "settings")?></p>
                                <?= Input::text("userEmail", t('email'), ['classes' => 'space-medium-top']) ?>
                                <?= Input::password("userPassword", t('password', 'auth')) ?>
                                `,
                                [
                                    {
                                        'text': '<?= t("close") ?>', 
                                        'classList': 'flat no-border',
                                        'click': function(dialogOBJ) {
                                            dialogOBJ.destroy(true);
                                            return false;
                                        }
                                    }, {
                                        'text': '<?= t("yes") ?>',
                                        'classList': 'space-big-left text error',
                                        'click': deleteAccount
                                    }
                                ]
                            );
                        dialog.setParent(document.body);
                        dialog.show();

                        function deleteAccount(dialogOBJ) 
                        {
                            let data = {
                                'email': dialogOBJ.element.querySelector('[name=userEmail]').value,
                                'password': dialogOBJ.element.querySelector('[name=userPassword]').value
                            };
                            
                            let response = Request.Action('/api/account/remove', 'POST', data);
                            response.then(res => {
                                window.location = "/";
                            }).catch(reason => {
                                let snackbar = Modal.Snackbar.create(reason.statusText, null ,'error');
                                snackbar.show();
                                Kantodo.error(reason);
                            }).finally(() => {
                                dialogOBJ.destroy(true);
                            });
                        }
                        
                    };
                </script>
            </div>
        <?php
    }
}

?>