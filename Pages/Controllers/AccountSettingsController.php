<?php 


namespace Kantodo\Controllers;

use Kantodo\Auth\Auth;
use Kantodo\Core\Base\AbstractController;
use Kantodo\Models\UserModel;
use Kantodo\Views\AccountSettingsView;
use Kantodo\Views\Layouts\ClientLayout;

class AccountSettingsController extends AbstractController
{
    /**
     * Nastavení účtu
     *
     * @return  void
     */
    public function settings()
    {
        $userModel = new UserModel();
        $user = Auth::getUser() ?? [];
        $userDetails = $userModel->getSingle(['firstname', 'lastname', 'email'], ['email' => $user['email']]);

        if ($userDetails == false)
            $userDetails = [];

        $this->renderView(AccountSettingsView::class, $userDetails, ClientLayout::class);
    }
}



?>