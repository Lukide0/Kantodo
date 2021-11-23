<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Core\Request;
use Kantodo\Widgets\Form;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

/**
 * Přihlášení a registrace
 */
class AuthView implements IView
{

    public function render(array $params = [])
    {
        $authType = (isset($params['type']) && $params['type'] == 'register') ? 'right' : '';
        $fromURL  = (isset($params['path'])) ? '?path=' . $params['path'] : '';
        // TODO: frontend error
        $registerForm = new Form();

        $email  = Application::$APP->session->getFlashMessage('userEmail', '');
        $signInErrors = Application::$APP->session->getFlashMessage('signInErrors', []);

        $errors = [];
        foreach ($signInErrors['empty'] ?? [] as $name) {
            $errors[$name] = t('empty_field', 'auth');
        }

        if (isset($signInErrors['success']) && $signInErrors['success'] === false) {
            $errors['signInEmail'] = " ";
            $errors['signInPassword'] = " ";
        }
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Kantodo - Auth</title>
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/main.css">
            <link rel="stylesheet" href="<?= Application::$STYLE_URL ?>/auth.min.css">
            <script src="<?= Application::$SCRIPT_URL ?>/main.js" ></script>
            <script src="<?= Application::$SCRIPT_URL ?>/global.js" type="module"></script>
        </head>
        <body>
            <div class="container center middle full">
                <div class="auth">
                    <h2><?= t('welcome_message', 'auth') ?></h2>
                    <?= Form::start('/auth/signin', Request::METHOD_POST, 'container full-width middle') ?>
                        <?= Form::tokenCSRF() ?>
                        <?= Input::text('signInEmail', t('email', 'auth'), ['classes' => 'full-width', 'error' => $errors, 'value' => $email, 'autocomplete' => Input::AUTOCOMPLETE_EMAIL]); ?>
                        <?= Input::password('signInPassword', t('password', 'auth'), ['classes' => 'full-width', 'error' => $errors, 'autocomplete' => Input::AUTOCOMPLETE_CURRENT_PASSWORD]); ?>
                        <button class="primary full-width center big space-huge-top space-huge-bottom"><?= t('log_in', 'auth'); ?></button>
                        <a href="?AAA" target="_blank" rel="noopener noreferrer" class="space-small-bottom"><?= t('forgotten_password', 'auth'); ?></a>
                        <p><?= t('dont_have_account', 'auth') ?> <a href="#" onclick="let x=document.querySelectorAll('.auth > .container'); x[0].style.display='none'; x[1].style.display='flex';"><?= t('register_here', 'auth') ?></a></p>
                    <?= Form::end() ?>
                    <div class="container full-width middle" style="display: none;">
                        <div class="row full-width h-space-between">
                            <label class="text-field outline space-big-right">
                                <div class="field">
                                    <span>Jméno</span>
                                    <input type="text">
                                </div>
                            </label>
                            <label class="text-field outline">
                                <div class="field">
                                    <span>Příjmení</span>
                                    <input type="text">
                                </div>
                            </label>
                        </div>
                        <label class="text-field outline space-big-bottom space-big-top full-width">
                            <div class="field">
                                <span>Email</span>
                                <input type="text">
                            </div>
                        </label>
                        <div class="row full-width h-space-between">
                            <label class="text-field outline space-big-right">
                                <div class="field">
                                    <span>Heslo</span>
                                    <input type="password">
                                </div>
                            </label>
                            <label class="text-field outline">
                                <div class="field">
                                    <span>Heslo znuvu</span>
                                    <input type="password">
                                </div>
                            </label>
                        </div>
                        <button class="primary full-width center big space-huge-top space-huge-bottom">Registrovat se</button>
                        <p>Máte účet? <a href="#" onclick="let x=document.querySelectorAll('.auth > .container'); x[1].style.display='none'; x[0].style.display='flex';">Přihlaste se!</a></p>
                    <?php
                        if (isset($signInErrors['success']) && $signInErrors['success'] == false):
                    ?>
                        <script>
                            let snackbar;
                            window.addEventListener('load', function(){
                                snackbar = Modal.Snackbar.create("<?= t('wrong_log_in_details', 'auth') ?>", null, 'error');
    
                                snackbar.setParent(document.body);
    
                                snackbar.show({center: true, top: 25});
                            },{once: true});
                        </script>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}

?>