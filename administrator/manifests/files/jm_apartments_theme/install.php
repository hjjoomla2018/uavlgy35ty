<?php

defined('_JEXEC') or die('Restricted access');

class jm_apartments_themeInstallerScript {
	
	function preflight($type, $parent)
	{
		foreach ($parent->manifest->fileset->files as $eFiles) {
			$target = (string)$eFiles->attributes()->target;
			$extension = (string)$eFiles->attributes()->extension_name;
			if (false == JFolder::exists(JPath::clean(JPATH_ROOT.'/'.$target)))
			{
				$message = 'It seems that '.$extension.' has not been installed. Please make sure that you install '.$extension.' before you install this package.';
				$parent->getParent()->abort($message);
				return false;
			}
		}
	}
	
	function uninstall($parent)
	{
		foreach ($parent->manifest->fileset->files as $eFiles) {
			$target = (string)$eFiles->attributes()->target;
			$extension = (string)$eFiles->attributes()->extension_name;
			$targetFolder = JPath::clean(JPATH_ROOT.'/'.$target);
			
			if (true == JFolder::exists($targetFolder))
			{
				if (count($eFiles->children()) > 0)
				{
					// Loop through all filenames elements
					foreach ($eFiles->children() as $eFileName)
					{
						if ($eFileName->getName() == 'folder')
						{
							$folderName = $targetFolder . '/' . $eFileName;
							$files = JFolder::files($folderName);
							if (count($files) > 0) {
								foreach($files as $file) {
									JFile::delete($folderName.'/'.$file);
								}
							}
							$subfolders = JFolder::folders($folderName);
							if (count($subfolders) > 0) {
								foreach ($subfolders as $subfolder) {
									JFolder::delete($folderName.'/'.$subfolder);
								}
							}
						}
					}
				}
			}
		}
	}
}