<?php 

namespace Kantodo\Views;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Models\ProjectModel;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\t;

class ProjectSettingsView implements IView
{
    public function render(array $params = [])
    {
        $project = $params['project'];
        $projectUUID = $params['projectUUID'];

        $members = $params['members'];
        $positions = $params['positions'];

        $email = Auth::getUser()['email'] ?? '';

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
                            <td><?= t('firstname') ?></td>
                            <td><?= t('lastname') ?></td>
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
                                    <?php
                                    if ($member['email'] === $email) 
                                    {
                                        echo $positions[$member['project_position_id']];
                                    }
                                    else {
                                        echo '<select onchange="updatePosition(event)">';
                                        foreach (array_keys(ProjectModel::POSITIONS) as $key) {
                                            if ($key === 'admin')
                                                continue;

                                            if ($positions[$member['project_position_id']] === $key)
                                                echo "<option value='{$key}' selected>{$key}</option>";
                                            else
                                                echo "<option value='{$key}'>{$key}</option>";
                                        }                                        
                                        echo "</select>";
                                    }
                                    ?>
                                </td>
                                <td><button onclick="deleteUser(event)" class="error icon outline">person_remove_alt_1</button></td>
                            </tr>
                        <?php endforeach; ?>  
                    </tbody>
                </table>
            </div>
            <script>
                function updatePosition(e) 
                {
                    let u = e.target.parentElement.parentElement.dataset.user;
                    let response = Request.Action('/api/project/change_position', 'POST', { 'project': '<?= $projectUUID ?>', 'user': u, 'position': e.target.value });
                    response.then(res => {
                        Kantodo.info(res);
                    }).catch(err => {
                        Kantodo.error(err);
                    })

                }

                function deleteUser(e) 
                {
                    console.log(this);
                }

            </script>
        </div>



        <?php
    }
}

?>