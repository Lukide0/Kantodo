<?php

namespace Kantodo\Views;

use Kantodo\Core\Base\IView;

/**
 * Tým
 */
class TeamView implements IView
{
    public function Render(array $params = [])
    {
        $teamInfo = $params['teamInfo'];

        ?>
        <h2><?=$teamInfo['name'];?></h2>

<?php

        var_dump($teamInfo);
    }
}

?>