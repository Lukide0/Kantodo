<?php

declare(strict_types = 1);

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;

use function Kantodo\Core\Functions\t;

/**
 * Projekt
 */
class ProjectView implements IView
{
    public function render(array $params = [])
    {
        
        $project = $params['project'];
        $members = $params['project']['members'] ?? [];

        $icon = ((bool)$project['is_open'] === true) ? "lock_open" : "lock";

        ?>
        <div class="container">
            <h2 style="font-size: 2.8rem"><?= $project['name']?><span class="icon big round"><?= $icon ?></span></h2>
            
            <h3 class="space-huge-top"><?= t('members')?></h3>
            <div class="row space-big-top">
                <?php foreach($members as $member): ?>
                    <div class="avatar fullname">
                        <?= $member['firstname'] . ' ' . $member['lastname']; ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

<?php
    }
}

?>