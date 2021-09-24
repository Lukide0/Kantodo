<?php

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\Base\IView;
use Kantodo\Widgets\Task;

/**
 * Hlavní stránka
 */
class DashboardView implements IView
{
    public function Render(array $params = [])
    {
        ?>
        <div class="row h-space-between">
            <h2>Moje práce</h2>
            <div class="row">
                <div class="button-dropdown">
                    <button class="filled">Přidat úkol</button>
                    <button class="dropdown"><span class="icon round">expand_more</span></button>
                </div>
                <button class="flat icon outline space-medium-left">settings</button>
            </div>
        </div>
        <div class="row nowrap">
            <div class="task-list">
                <div class="dropdown-header">
                    <h3>KantodoApp</h3>
                    <div class="line"></div>
                    <button class="flat icon round">filter_alt</button>
                </div>
                <div class="container">
                    <?php 
                        echo Task::Create('TITLE', 'VERY LOOO0OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOONG DESCRIPTION', false);
                    ?>
                </div>
            </div>
            <div class="milestone-list">
                <div class="milestone">
                    <div class="date">
                        <span class="month">Sep</span>
                        <span class="day">18</span>
                    </div>
                    <div class="container">
                        <p>Write 15 blog articles on Medium</p>
                        <span class="description">Office/Marketing</span>
                        <div class="progress-bar">
                            <div class="bar">
                                <div class="completed" style="width: 72%;"></div>
                            </div>
                            <span>72% Completed</span>
                        </div>
                    </div>
                </div>
                <div class="milestone">
                    <div class="date">
                        <span class="month">Nov</span>
                        <span class="day">02</span>
                    </div>
                    <div class="container">
                        <p>Publish 20 dribbbles</p>
                        <span class="description">Office/Marketing</span>
                        <div class="progress-bar">
                            <div class="bar">
                                <div class="completed" style="width: 15%;"></div>
                            </div>
                            <span>15% Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
}
}

?>