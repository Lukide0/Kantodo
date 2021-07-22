<?php 

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Model;
use PDO;

class TaskModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('tasks');
    }
}


?>