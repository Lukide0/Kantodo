<?php 

namespace Kantodo\Core\Functions;

const FILE_FLAG_CREATE_DIR = 1 << 1;
const FILE_FLAG_OVERRIDE = 2 << 1;

/**
 * Bezpečné zapsání do souboru
 *
 * @param   string              $filename  cesta
 * @param   string              $content   kontent souboru
 * @param   int                 $flags     vlajky
 *
 * @return  bool                          vrací status zapsání souboru
 */
function filePutContentSafe(string $filename,string $content, int $flags = FILE_FLAG_OVERRIDE)
{
    if (!file_exists($filename)) 
    {
        if (!($flags & FILE_FLAG_OVERRIDE))
            return false;
        
        if (!is_dir(dirname($filename))) 
        {
            if (!($flags & FILE_FLAG_CREATE_DIR))
                return false;
            
            if(!mkdir(dirname($filename), 0777, true))
                return false;
        }
        if (!is_dir(dirname($filename)) && !($flags & FILE_FLAG_CREATE_DIR)) 
            return false;   
    }

    return file_put_contents($filename, $content) !== false;
}

?>