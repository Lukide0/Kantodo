<?php 

namespace Kantodo\Views;

use Kantodo\Auth\Auth;
use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Models\ProjectModel;
use Kantodo\Widgets\Input;

use function Kantodo\Core\Functions\base64DecodeUrl;
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
                                <?php
                                if ($member['email'] === $email) 
                                {
                                    echo '<td>' . $positions[$member['project_position_id']] . '</td>';
                                    echo '<td></td>';
                                }
                                else {
                                    echo '<td><select onfocus="this.oldVal = this.value" onchange="updatePosition(event,this)">';
                                    foreach (array_keys(ProjectModel::POSITIONS) as $key) {
                                        if ($key === 'admin')
                                            continue;

                                        if ($positions[$member['project_position_id']] === $key)
                                            echo "<option value='{$key}' selected>{$key}</option>";
                                        else
                                            echo "<option value='{$key}'>{$key}</option>";
                                    }                                        
                                    echo "</select></td>";
                                    echo '<td><button onclick="deleteUser(event)" class="error icon outline">person_remove_alt_1</button></td>';
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>  
                    </tbody>
                </table>
            </div>
            <script>
                function updatePosition(e, self) 
                {
                    let u = e.target.parentElement.parentElement.dataset.user;

                    let dialog = Modal.Dialog.create('<?= t("confirm") ?>', `<?= t("do_you_want_change_position_of_this_user")?> (${u})`, [
                        {
                            'text': '<?= t("close") ?>', 
                            'classList': 'flat no-border',
                            'click': function(dialogOBJ) {

                                self.value = self.oldVal;
                                dialogOBJ.hide();

                                return false;
                            }
                        },
                        {
                            'text': '<?= t("yes") ?>',
                            'classList': 'space-big-left text',
                            'click': function(dialogOBJ) {
                                
                                let response = Request.Action('/api/project/change_position', 'POST', { 'project': '<?= $projectUUID ?>', 'user': u, 'position': e.target.value });
                                response.then(res => {
                                    Kantodo.info(res);
                                }).catch(err => {
                                    Kantodo.error(err);
                                })
                                dialogOBJ.hide();
                            }
                        }
                    ]);
                
                    dialog.setParent(document.body.querySelector('main'));
                    dialog.show();


                }

                function deleteUser(e) 
                {
                    let u = e.target.parentElement.parentElement.dataset.user;
                    let dialog = Modal.Dialog.create('<?= t("confirm") ?>', `<?= t("do_you_want_remove_this_user")?> (${u})`, [
                        {
                            'text': '<?= t("close") ?>', 
                            'classList': 'flat no-border',
                            'click': function(dialogOBJ) {
                                dialogOBJ.hide();
                            }
                        },
                        {
                            'text': '<?= t("yes") ?>',
                            'classList': 'space-big-left text error',
                            'click': function(dialogOBJ) {
                                
                                console.log(u);

                                dialogOBJ.hide();
                            }
                        }
                    ]);
                
                    dialog.setParent(document.body.querySelector('main'));
                    dialog.show();
                }
            </script>
        </div>



        <?php
    }
}

?>