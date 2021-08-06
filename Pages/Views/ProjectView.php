<?php 

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\IView;

use function Kantodo\Core\base64_encode_url;

class ProjectView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->registerStyle('/styles/project.css');

        $membersInitials = $params['membersInitials'] ?? [];

        $membersCount = count($membersInitials);

        ?>
        <h2>UX Design Team</h2>
        <div class="avatars">
        <?php

        for ($i=0; $i < 5 && $i < $membersCount; $i++) 
        { 
            $initials = $membersInitials[$i]['initials'];
        ?>
            <div class="avatar">
                <p><?= $initials ?></p>
            </div>
        <?php
        }

        if ($membersCount > 5) 
        {
        ?>
            <div class="avatar">
                <p>+<?= $membersCount - 5 ?></p>
            </div>
        <?php
        }

        ?>
            <button class="text icon-text"><span class="material-icons-round">add</span>invite</button>
        </div>
        <div class="actions row">
            <button class="primary icon-text"><span class="material-icons-round">add</span>Create</button>
            <button class="icon-text flat"><span class="material-icons-round">filter_alt</span>Filter</button>
        </div>
        <div id="columns">
            <?php 
            
            foreach ($params['columns'] ?? [] as $column)
            {
            ?>
                <div class="column" data-column="<?= base64_encode_url($column['id']) ?>" >
                    <div class="row">
                        <div class="title"><?= $column['name'] ?></div>
                        <div class="actions">
                            <button class="icon-small round flat">
                                <span class="meterial-icons-round">add</span>
                            </button><button class="icons-small round flat">
                                <span class="material-icons-round">more_horiz</span>
                            </button>
                        </div>
                    </div>
                    <div class="tasks"></div>

                </div>
            <?php
            }
            
            
            
            
            ?>
            <!-- <div class="column">
                <div class="row">
                    <div class="title">To Do</div>
                    <div class="actions">
                        <button class="icon-small round flat">
                            <span class="material-icons-round">add</span>
                        </button>
                        <button class="icon-small round flat">
                            <span class="material-icons-round">more_horiz</span>
                        </button>
                    </div>
                </div>
                <div class="tasks" data-drop-area="task">
                    <div class="task">
                        <div class="head">
                            <div class="name">NAME</div>
                            <button class="icon-small flat round"><span class="material-icons-round">more_horiz</span></button>
                        </div>
                        <div class="content">
                            <div class="identifier">1</div>
                            <div class="priority"></div>
                            <div class="tags">
                                <div class="tag">TAG NAME</div>
                            </div>
                        </div>
                        <div class="footer">
                            <button class="icon-small flat round">
                                <span class="material-icons-round">attach_file</span>0
                            </button>
                            <button class='icon-small flat round'>
                                <span class='material-icons-outlined'>chat</span>1
                            </button>
                            <div class="avatars">
                                <div class="avatar">LK</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <button id="addColumn">
                <span>Add column</span>
            </button>
        </div>
        <script>

        </script>
        <?php
    }
}

?>