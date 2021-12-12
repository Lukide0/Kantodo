<?php 

namespace Kantodo\Views;

use Kantodo\Core\Base\IView;
use Kantodo\Models\ProjectModel;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

class ProjectSettingsView implements IView
{
    public function render(array $params = [])
    {
        $project = $params['project'];
        $members = $params['members'];
        $positions = $params['positions'];

        ?>
        <div class="container">
            <h2 class="row space-extreme-bottom" style="font-size: 2.8rem"><?=$project['name'] . ' - ' . t('settings');?></h2>
            <div class="row">
                <?= Input::text('projectName', t('project_name'), ['value' => $project['name']]) ?>
            </div>
            <div class="row">
                <table>
                    <thead>
                        <tr>
                            <td><?= t('name') ?></td>
                            <td><?= t('surname') ?></td>
                            <td><?= t('email') ?></td>
                            <td><?= t('position') ?></td>
                            <td><?= t('actions') ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $member): ?>
                            <tr data-user="<?= $member['email'] ?>">
                                <td><?= $member['firstname'] ?></td>
                                <td><?= $member['lastname'] ?></td>
                                <td><?= $member['email'] ?></td>
                                <td>
                                    <select>
                                        <?php 
                                            foreach (array_keys(ProjectModel::POSITIONS) as $key) {
                                                if ($positions[$member['project_position_id']] === $key)
                                                    echo "<option selected>{$key}</option>";
                                                else
                                                    echo "<option>{$key}</option>";
                                            }                                        
                                        ?>
                                    </select>
                                </td>
                                <td><button class="error icon outline">person_remove_alt_1</button></td>
                            </tr>
                        <?php endforeach; ?>  
                    </tbody>
                </table>
            </div>

        </div>



        <?php
    }
}

?>