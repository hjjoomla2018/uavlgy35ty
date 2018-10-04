<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

class Djcatalog2Controller extends JControllerLegacy
{
	protected $default_view = 'cpanel';
	
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/djcatalog2.php';
		Djcatalog2AdminHelper::addSubmenu(JFactory::getApplication()->input->getCmd('view', 'cpanel'));
		parent::display($cachable, $urlparams);
	}
	public function download_file() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_djcatalog2') && !$user->authorise('core.admin', 'com_djcatalog2')){
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		$path = $app->input->get('path', null, 'base64');
		$file_path = JPATH_ROOT.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, base64_decode($path));
		
		if (empty($path) || !JFile::exists($file_path) || strpos($file_path, 'media') === false || strpos($file_path, 'djcatalog2') === false) {
			$this->setRedirect( 'index.php?option=com_djcatalog2', JText::sprintf('COM_DJCATALOG2_ERROR_FILE_MISSING', base64_decode($path)), 'error' );
			return false;
		}
		
		if (!DJCatalog2FileHelper::getFileByPath($file_path, null, 'text/csv')){
			//JError::raiseError(404);
			throw new Exception('', 404);
			return false;
		}
		$app->close();
		return true;
	}
	
	public function multiupload() {
	
		// todo: secure upload from injections
		$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_djcatalog2')){
			echo JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN');
			exit(0);
		}
	
		DJCatalog2UploadHelper::upload();
	
		return true;
	}
	
	public function getStatesByCountry() {
		$app = JFactory::getApplication();
		$country = $app->input->getInt('country');
	
		$results = array();
	
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('cs.*')->from('#__djc2_countries_states AS cs')->order('cs.name asc');
	
		if ($country > 0) {
			$query->where('cs.country_id='.(int)$country);
		} else {
			$query->join('inner', '#__djc2_countries AS c ON c.id=cs.country_id AND c.is_default=1');
		}
	
		$db->setQuery($query);
		$results = $db->loadObjectList();
	
		echo json_encode($results);
		$app->close();
	}
	
	public function findItemByName()
	{
		// Required objects
		$app = JFactory::getApplication();
		
		$like = $app->input->getString('like', '');
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id as value, concat(name, " [", id, "]") as text')
		->from('#__djc2_items')
		->where('name like '.$db->quote('%'.$db->escape($like).'%'))
		->order('name asc');
		
		$db->setQuery($query);
		
		if ($results = $db->loadObjectList())
		{
			// Output a JSON object
			echo json_encode($results);
		}
		
		$app->close();
	}
}

?>