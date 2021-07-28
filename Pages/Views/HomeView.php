<?php 

namespace Kantodo\Views;

use Kantodo\Core\IView;

class HomeView implements IView
{
    public function Render(array $params = [])
    {
        echo "Good morning, {NAME}!";
        echo "<br>DATE";
    }
}

?>