<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class BackupHelper
{
	/**
	 * File-array
	 *
	 * @access private
	 * @var array
	 */
	private $filesArray = array();

	/**
	 * Ignore this files
	 *
	 * @access private
	 * @var array
	 */
	private $filesIgnoreArray = array('static', 'stored', 'templates', 'backups', '.settings', '.buildpath', '.project');


	/**
	 * Start file-backup
	 *
	 * @access public
	 * @param string $filePath Path to file
	 */
	public function createFileBackup($filePath)
	{
		$this->readDirectory(Settings::get('appPath'));
		$zip = new ZipArchive();
		$zip->open($filePath, ZipArchive::CREATE);

		foreach ($this->filesArray as $folder => $files)
		{
			foreach ($files as $file)
			{
				if ($file[0] == '.' || strpos($file, '.'))
				{
					echo $folder.$file."<br />";
					$zip->addFile($folder.$file, str_replace(Settings::get('appPath'), '', $folder.$file));
				}
			}
		}

		$zip->close();
	}

	/**
	 * Creates file-array
	 *
	 * @access private
	 * @param string dir folder
	 */
	private function readDirectory($dir)
	{
		$files = new RecursiveDirectoryIterator($dir);

		for ($files->rewind(); $files->valid(); $files->next())
		{
			if ($files->isDir() && $files->isDot() == false && in_array($files->getFilename(), $this->filesIgnoreArray) == false)
			{
				$this->filesArray[$dir][] = $files->getFilename();
				 
				if ($files->hasChildren())
				{
					$this->readDirectory($dir.$files->getFilename().'/');
				}
				 
			} elseif ($files->isFile() && in_array($files->getFilename(), $this->filesIgnoreArray) == false) {

				$this->filesArray[$dir][] = $files->getFilename();
			}
		}
	}
}

?>