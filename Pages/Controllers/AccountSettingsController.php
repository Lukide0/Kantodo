<?php 


namespace Kantodo\Controllers;

use Kantodo\Core\Base\AbstractController;
use Kantodo\Views\AccountSettingsView;
use Kantodo\Views\Layouts\ClientLayout;

class AccountSettingsController extends AbstractController
{
    public function settings()
    {
        $this->renderView(AccountSettingsView::class, [], ClientLayout::class);
    }
}



?>