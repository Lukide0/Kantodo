<?php 

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Model;
use Kantodo\Core\Generator;
use PDO;

class UserModel extends Model
{

    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('users');
    }

    public function create(string $firstname, string $lastname, string $email, string $password, string $nickname = NULL)
    {
        $secret = Generator::uuidV4();
        $nickname = $nickname ?? 'NULL';
        $sth = $this->con->prepare("INSERT INTO {$this->table} (firstname, lastname, email, password, secret, nickname) VALUES ( :firstname, :lastname, :email, :password, :secret, :nickname)");
        $status = $sth->execute([
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':email' => $email,
            ':password' => $password,
            ':secret' => $secret,
            ':nickname' => $nickname
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }

    public function remove(int $id) 
    {
        $sth = $this->con->prepare("DELETE FROM TABLE {$this->table} WHERE `user_id` = :id");
        return $sth->execute([
            ":id" => $id
        ]);
    }

    public function addMeta(string $key, string $value, int $userID) 
    {
        $userMeta = Connection::formatTableName('user_meta');
        $sth = $this->con->prepare("INSERT INTO {$userMeta} (`key`, `value`, `user_id`) VALUES (:key, :value, :user_id)");
        $status = $sth->execute([
            ':key' => $key,
            ':value' => $value,
            ':user_id' => $userID
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }

    public function existsEmail(string $email)
    {
        $sth = $this->con->prepare("SELECT user_id FROM {$this->table} WHERE email = :email LIMIT 1");
        $sth->execute([
            ':email' => $email
        ]);

        $user = $sth->fetch(PDO::FETCH_ASSOC);
        
        return count($user) == 1;
    }

    public function getSingle(array $columns = ['*'], array $search = []) 
    {
        $user = $this->get($columns, $search, 1);

        if (count($user) == 1)
            return $user[0];
        return false;
    }


    public function get(array $columns = ['*'], array $search = [], int $limit = 0) 
    {
        if (count($columns) == 0) 
            return [];

        $tableColumns = ['user_id', 'firstname', 'lastname', 'email', 'password', 'secret', 'nickname'];

        return $this->query($this->table, $tableColumns, $columns, $search, $limit);
    }

    public function getMeta(int $userID, string $key, bool $multiple = false)
    {
        $userMeta = Connection::formatTableName('user_meta');

        $limit = ' LIMIT 1';

        if ($multiple)
            $limit = '';

        $sth = $this->con->prepare("SELECT `value` FROM {$userMeta} WHERE `user_id` = :user_id AND `key` = :key" . $limit);
        $sth->execute([
            ':key' => $key,
            ':user_id' => $userID
        ]);

        if ($multiple) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        
        return $sth->fetch(PDO::FETCH_ASSOC);

    }

    public function exists(array $data) 
    {
        if (count($data) == 0) 
            return false;

        $tableColumns = ['user_id', 'firstname', 'lastname', 'email', 'password', 'secret', 'nickname'];

        $search = [];
        $queryData = [];

        foreach ($tableColumns as $column) {
            if (isset($data[$column])) 
            {
                $search[] = "{$column} = :{$column}";
                $queryData[":$column"] = $data[$column];
            }
        }

        if (count($search) == 0) 
            return false;
        
        $queryWhere = implode(" AND ", $search);

        $sth = $this->con->prepare("SELECT user_id FROM {$this->table} WHERE {$queryWhere} LIMIT 1");
        $sth->execute($queryData);
        $user = $sth->fetch(PDO::FETCH_NUM);

        return $user !== false;
    }
}


?>