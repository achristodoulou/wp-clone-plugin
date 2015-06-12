<?php

namespace Utils;

class Archive {

    /**
     * Archive a directory
     *
     * @param $archive_file_name
     * @param $dirName
     * @throws \Exception
     */
    public function zip($archive_file_name, $dirName)
    {
        $zip = new \ZipArchive();
        $zip->open($archive_file_name, \ZipArchive::CREATE);

        if (!is_dir($dirName)) {
            throw new \Exception('Directory ' . $dirName . ' does not exist');
        }

        $dirName = realpath($dirName);
        if (substr($dirName, -1) != '/') {
            $dirName.= '/';
        }

        $dirStack = array($dirName);
        //Find the index where the last dir starts
        $cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;

        while (!empty($dirStack)) {
            $currentDir = array_pop($dirStack);
            $filesToAdd = array();

            $dir = dir($currentDir);
            while (false !== ($node = $dir->read())) {
                if (($node == '..') || ($node == '.')) {
                    continue;
                }
                if (is_dir($currentDir . $node)) {
                    array_push($dirStack, $currentDir . $node . '/');
                }
                if (is_file($currentDir . $node)) {
                    $filesToAdd[] = $node;
                }
            }

            $localDir = substr($currentDir, $cutFrom);
            $zip->addEmptyDir($localDir);

            foreach ($filesToAdd as $file) {
                $zip->addFile($currentDir . $file, $localDir . $file);
            }
        }

        $zip->close();
    }

    /**
     * Extract an archived directory
     *
     * @param $archive_file_name
     * @param $extract_dir
     * @throws \Exception
     */
    public function unzip($archive_file_name, $extract_dir)
    {
        $zip = new \ZipArchive;
        if ($zip->open($archive_file_name) === TRUE) {
            $zip->extractTo($extract_dir);
            $zip->close();
        } else {
            throw new \Exception('Unzip operation failed');
        }
    }
}