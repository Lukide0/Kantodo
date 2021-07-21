<?php 

namespace Kantodo\Views;

use Kantodo\Core\Application;
use Kantodo\Core\IView;

class ProjectsListView implements IView
{
    public function render(array $params = [])
    {
        Application::$APP->header->registerStyle('Styles/projects.css');
        ?>
        <table class="projects">
            <thead>
                <tr>
                    <td id="status">
                        <div>
                            <div class="active">1 Open</div>
                            <div>0 Close</div>        
                        </div>
                    </td>
                    <td></td>
                    <td id="filter">
                        <div>
                            <span class="material-icons-round">
                            filter_alt
                            </span>
                        </div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div>
                            <h3>Managment</h3>
                            <div class="progress">
                                <div class="failed" style="width: 10px;"></div>
                                <div class="completed" style="width: 50px;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="description">No description</td>
                    <td>Lukas Koliandr</td>
                </tr>       
            </tbody>
            <tfoot></tfoot>
        </table>
        <?php
    }
}

?>