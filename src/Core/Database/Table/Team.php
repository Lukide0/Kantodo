<?php 

namespace Kantodo\Core\Database\Table;

class Team
{
    public int $Id;
    public string $Name;
    public string $DirName;
    public string $Description = NULL;
    public bool $IsPublic;
}


?>