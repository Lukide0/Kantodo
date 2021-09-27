<?php 


namespace Kantodo\Plugin;

// TODO: dodelat + predelat config.php na container
class Loader
{
    const ERR_NOT_FOUNT = 0;

    public function load(string $name)
    {
        $folder = STORAGE_PLUGIN . '/' . $name;

        if (!is_dir($folder))
            return self::ERR_NOT_FOUNT;
    }


}
