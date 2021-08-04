<?php 

namespace Kantodo\Views;

use Kantodo\Core\IView;
use Kantodo\Models\TeamModel;

class TeamView implements IView
{
    public function Render(array $params = [])
    {
        $teamID = $params['teamID'];

        $teamModel = new TeamModel();

        $teamInfo = $teamModel->getInfo($teamID);



        ?>
        <h2><?= $teamInfo['name'] ?></h2>

        <?php

        var_dump($teamInfo);
    }
}

?>