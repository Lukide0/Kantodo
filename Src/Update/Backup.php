<?php

declare(strict_types = 1);

namespace Kantodo\Update;

use Kantodo\Core\Application;
use ZipArchive;

class Backup
{
    const FOLDERS = ['Lang', 'Loader', 'Migrations', 'Pages', 'Sass', 'scripts', 'styles', 'Src', 'util'];

    /**
     * Vytvoří archiv se zdroj. soubory
     *
     * @return  void
     */
    public function createZip()
    {
        $path = Application::$ROOT_DIR;

        /** @phpstan-ignore-next-line */
        $version = VERSION;
        /** @phpstan-ignore-next-line */
        $zipPath = STORAGE_BACKUP . "/kantodo_ver_{$version}.zip";

        $zipObj = new ZipArchive();

        $zipObj->open($zipPath, ZipArchive::CREATE);

        foreach (self::FOLDERS as $folder) {
            $zipObj->addEmptyDir($folder);

            $this->addFolderToZip($path . '/' . $folder, $folder, $zipObj);
        }

        $zipObj->close();
    }

    /**
     * Přidá složku do archivu
     *
     * @param   string      $pathToFolder       cesta k složce
     * @param   string      $localPathToFolder  cesta k složce v archivu
     * @param   ZipArchive  $zipObj
     *
     * @return  void
     */
    private function addFolderToZip(string $pathToFolder, string $localPathToFolder, ZipArchive &$zipObj)
    {
        $dirHandle = opendir($pathToFolder);

        if ($dirHandle == false) {
            return;
        }

        while (($readHandle = readdir($dirHandle)) !== false) {
            if ($readHandle == '.' || $readHandle == '..') {
                continue;
            }

            $filePath  = $pathToFolder . '/' . $readHandle;
            $localPath = $localPathToFolder . '/' . $readHandle;

            if (is_file($filePath)) {
                $zipObj->addFile($filePath, $localPath);

            } else if (is_dir($filePath)) {
                $zipObj->addEmptyDir($localPath);

                $this->addFolderToZip($filePath, $localPath, $zipObj);
            }
        }
    }
}
