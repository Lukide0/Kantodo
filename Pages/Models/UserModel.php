<?php

declare(strict_types=1);

namespace Kantodo\Models;

use Kantodo\Core\Base\Model;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use PDO;

/**
 * Model na uživatele
 */
class UserModel extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->table = Connection::formatTableName('users');

        $this->setColumns([
            'user_id',
            'firstname',
            'lastname',
            'email',
            'password',
            'secret',
            'nickname',
        ]);
    }

    /**
     * Vytvoří uživatele
     *
     * @param   string  $firstname  jméno
     * @param   string  $lastname   příjmení
     * @param   string  $email      email
     * @param   string  $password   heslo (hash)
     * @param   string  $nickname   nickname
     *
     * @return  array{int,string}|false   vrací id a secret záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $firstname, string $lastname, string $email, string $password, string $nickname = null)
    {
        $secret   = Generator::uuidV4();
        $nickname = $nickname ?? 'NULL';
        $sth      = $this->con->prepare("INSERT INTO {$this->table} (firstname, lastname, email, password, secret, nickname) VALUES ( :firstname, :lastname, :email, :password, :secret, :nickname)");
        $status   = $sth->execute([
            ':firstname' => $firstname,
            ':lastname'  => $lastname,
            ':email'     => $email,
            ':password'  => $password,
            ':secret'    => $secret,
            ':nickname'  => $nickname,
        ]);

        return ($status === true) ? [(int)$this->con->lastInsertId(), $secret] : false;
    }

    /**
     * Smaže uživatele
     *
     * @param   int  $id  id uživatele
     *
     * @return  bool      status
     */
    public function delete(int $id)
    {
        $sth = $this->con->prepare("DELETE FROM {$this->table} WHERE user_id = :id");
        return $sth->execute([
            ":id" => $id,
        ]);
    }

    /**
     * Přidá meta uživateli
     *
     * @param   string  $key     klíč
     * @param   string  $value   hodnota
     * @param   int     $userID  id uživatele
     *
     * @return  int|false        vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function addMeta(string $key, string $value, int $userID)
    {
        $userMeta = Connection::formatTableName('user_meta');
        $sth      = $this->con->prepare("INSERT INTO {$userMeta} (`key`, `value`, `user_id`) VALUES (:key, :value, :user_id)");
        $status   = $sth->execute([
            ':key'     => $key,
            ':value'   => $value,
            ':user_id' => $userID,
        ]);

        return ($status === true) ? (int)$this->con->lastInsertId() : false;
    }

    /**
     * Existuje email
     *
     * @param   string  $email  email
     *
     * @return  bool
     */
    public function existsEmail(string $email)
    {
        $sth = $this->con->prepare("SELECT user_id FROM {$this->table} WHERE email = :email LIMIT 1");
        $sth->execute([
            ':email' => $email,
        ]);

        $user = $sth->fetch(PDO::FETCH_ASSOC);
        return ($user !== false && count($user) == 1);
    }

    /**
     * Získá záznam z tabulky
     *
     * @param   array<string>|array<string,string>  $columns  sloupce z tabulky, které chceme získat ve tvaru ['sloupec1', 'sloupec2'] nebo ['sloupec1' => 'alias', 'sloupec2']
     * @param   array<string,mixed>  $search   např. ['id' => 5]
     *
     * @return  array<mixed>|false      vrací false pokud nepodařilo získat záznamy
     */
    public function getSingle(array $columns = ['*'], array $search = [])
    {
        $user = $this->get($columns, $search, 1);

        if ($user != false && count($user) == 1) {
            return $user[0];
        }

        return false;
    }

    /**
     * Získá meta uživatele
     *
     * @param   int     $userID    id uživatele
     * @param   string  $key       klíč
     * @param   bool    $multiple  pokud existuje více záznamů s tím to klíčem
     *
     * @return  array<mixed>|false        vrací false pokud se nepodařilo získat
     */
    public function getMeta(int $userID, string $key, bool $multiple = false)
    {
        $userMeta = Connection::formatTableName('user_meta');

        $limit = ' LIMIT 1';

        if ($multiple) {
            $limit = '';
        }

        $sth    = $this->con->prepare("SELECT `value` FROM {$userMeta} WHERE `user_id` = :user_id AND `key` = :key" . $limit);
        $status = $sth->execute([
            ':key'     => $key,
            ':user_id' => $userID,
        ]);

        if ($status === false) {
            return false;
        }

        if ($multiple) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Existuje
     *
     * @param   array<string,string>  $data  data ve formátu ['sloupec' => hodnota]
     *
     * @return  bool
     */
    public function exists(array $data)
    {
        if (count($data) == 0) {
            return false;
        }

        $tableColumns = ['user_id', 'firstname', 'lastname', 'email', 'password', 'secret', 'nickname'];

        $search    = [];
        $queryData = [];

        foreach ($tableColumns as $column) {
            if (isset($data[$column])) {
                $search[]              = "{$column} = :{$column}";
                $queryData[":$column"] = $data[$column];
            }
        }

        if (count($search) == 0) {
            return false;
        }

        $queryWhere = implode(" AND ", $search);

        $sth = $this->con->prepare("SELECT user_id FROM {$this->table} WHERE {$queryWhere} LIMIT 1");
        $sth->execute($queryData);
        $user = $sth->fetch(PDO::FETCH_NUM);

        return $user !== false;
    }
}
