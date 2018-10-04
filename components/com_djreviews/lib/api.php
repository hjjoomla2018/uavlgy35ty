<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

/**
 *
 * Example usage:
 *
 require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
 $review = DJReviewsAPI::getInstance(array(
 'group' => '8',
 'type'  => 'com_djcatalog2.item',
 'name'	=> $this->item->name,
 'link'	=> JRoute::_((string)JUri::getInstance()),
 'id'	=> $this->item->id));
 echo $review->getFullReview();
 *
 */

class DJReviewsAPI  {
	
	public $id = 0;
	
	public $rating_group_id;
	
	public $object_type;
	
	public $entry_id = 0;
	
	public $name = '';
	
	public $link = '';
	
	public $avg_rate = 0;
	
	public $params = false;
	
	protected static $instances = array();
	
	protected static $assets = false;
	
	protected static $js_core = false;
	
	protected $models = array();
	
	protected $views = array();
	
	/**
	 *
	 * @param int $rating_group Rating Group ID
	 * @param string $object_type Type of an object, eg. com_content, com_content.article
	 * @param string $object_name Friendly name, title
	 * @param string $object_link Object's URL, eg. link to an article
	 * @param int $entry_id rated object's ID
	 * @throws RuntimeException
	 */
	
	public function __construct($rating_group, $object_type, $object_name, $object_link, $entry_id, $params = false, $plugin = null) {
		if (!(int)$rating_group) {
			throw new RuntimeException('Missing rating group');
		}
		$object_type = strtolower(preg_replace('#[^a-z0-9\.\_\-]#i', '.', $object_type));
		
		if (!$object_type) {
			throw new RuntimeException('Missing object type');
		}
		if (!$object_name) {
			throw new RuntimeException('Missing object name');
		}
		if (!$object_link) {
			throw new RuntimeException('Missing object link');
		}
		if (!(int)$entry_id) {
			throw new RuntimeException('Missing or invalid object ID');
		}
		
		$this->rating_group_id 	= $rating_group;
		$this->object_type 		= $object_type;
		$this->entry_id 		= $entry_id;
		$this->name 			= $object_name;
		$this->link 			= $object_link;
		$this->plugin			= $plugin;
		
		require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/helpers/djreviews.php'));
		
		if (!$params) {
			$params = DJReviewsHelper::getParams($this->rating_group_id);
		}
		$this->params			= $params;
		
		if (!self::$assets) {
			self::recalculate();
			DJReviewsHelper::setAssets($this->rating_group_id);
			self::$assets = true;
		}
		
	}
	
	/**
	 * @param array $config
	 * @throws RuntimeException
	 * @return DJReviewsAPI
	 */
	
	public static function getInstance($config) {
		
		$sig = md5(serialize($config));
		
		if (!isset($config['params']) || !($config['params'] instanceof JRegistry)) {
			$config['params'] = JComponentHelper::getParams('com_djreviews');
		}
		
		if (!isset($config['plugin'])) {
			$config['plugin'] = '';
		}
		
		if (empty(self::$instances[$sig])) {
			try
			{
				$instance = new DJReviewsAPI($config['group'], $config['type'], $config['name'], $config['link'], $config['id'], $config['params'], $config['plugin']);
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(sprintf('DJ-Reviews - unable to create review object: %s', $e->getMessage()));
			}
			
			self::$instances[$sig] = $instance;
		}
		
		return self::$instances[$sig];
	}
	
	/**
	 * @param bool $avg Toggles between average or detailed rating.
	 */
	
	public function getRating($avg = false) {
		if ($avg) {
			return $this->getRatingLayout('avg');
		}
		return $this->getRatingLayout('default');
	}
	
	public function getRatingLayout($layout) {
		if (!$this->initialise()) {
			return false;
		}
		$output = '';
		$app = JFactory::getApplication();
		
		$view = $this->getView('Rating');
		
		if (!$view) {
			return false;
		}
		
		$model = $this->getModel('Rating');
		
		if (!$model) {
			return false;
		}
		
		$state = $model->getState();
		$model->setState('filter.object_id', $this->id);
		
		$view->setModel($model, true);
		$view->setLayout($layout);
		
		ob_start();
		$view->display();
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	public function getRatingFull() {
		return $this->getRatingLayout('default');
	}
	
	public function getRatingAvg() {
		return $this->getRatingLayout('avg');
	}
	
	public function getRatingAvgShort() {
		return $this->getRatingLayout('avgshort');
	}
	
	public function getRatingStars() {
		return $this->getRatingLayout('stars');
	}
	
	public function getReviews() {
		return $this->getReviewsLayout('default');
	}
	
	public function getReviewsLayout($layout = 'default') {
		if (!$this->initialise()) {
			return false;
		}
		$output = '';
		$app = JFactory::getApplication();
		
		$view = $this->getView('ReviewsList');
		
		if (!$view) {
			return false;
		}
		
		$model = $this->getModel('ReviewsList');
		
		if (!$model) {
			return false;
		}
		
		$state = $model->getState();
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		
		$model->setState('list.limit', $this->params->get('revlist_limit', 10));
		$limitstart = $app->input->getInt('djreviews_limitstart', 0);
		$model->setState('list.start', $limitstart);
		
		if ($layout == 'preview') {
			$model->setState('list.limit', $this->params->get('revlist_limit_preview', 3));
			$model->setState('list.start', 0);
		}
		
		$model->setState('filter.object_id', $this->id);
		$model->setState('filter.published', 'front');
		
		$view->setModel($model, true);
		$view->setLayout($layout);
		
		ob_start();
		$view->display();
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	public function getForm() {
		if (!$this->initialise()) {
			return false;
		}
		
		$app = JFactory::getApplication();
		
		$view = $this->getView('ReviewForm');
		
		if (!$view) {
			return false;
		}
		
		$model = $this->getModel('ReviewForm');
		
		if (!$model) {
			return false;
		}
		
		$state = $model->getState();
		//$model->setState('reviewform.id', $this->id);
		$model->setState('review.rating_group_id', $this->rating_group_id);
		$model->setState('review.object_id', $this->id);
		$model->setState('review.object_type', $this->object_type);
		
		$review_id = $app->input->getInt('djreviews_review', 0);
		if ($review_id > 0) {
			$model->setState('reviewform.id', $review_id);
		}
		
		$view->setModel($model, true);
		
		ob_start();
		$view->display();
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	public function getFullReview() {
		if (($form = $this->getForm()) !== false) {
			return $this->getRating(false) . $this->getReviews() . $form;
		}
	}
	
	protected function getView($name) {
		
		if (!isset($this->views[$name])) {
			require_once JPATH_ROOT.JPath::clean('/components/com_djreviews/views/'.strtolower($name).'/view.raw.php');
			
			$vars = array(
				'id' => $this->id
			);
			
			$v_config = array(
				'base_path' => JPATH_ROOT.JPath::clean('/components/com_djreviews'),
				'template_path' => JPATH_ROOT.JPath::clean('/components/com_djreviews/views/'.strtolower($name).'/tmpl'),
				'reviews_variables' => $vars
			);
			
			$className = 'DJReviewsView'.$name;
			
			if (class_exists($className)){
				$this->views[$name] = new $className($v_config);
			}
		}
		
		return $this->views[$name];
	}
	
	protected function getModel($name) {
		if (!isset($this->models[$name])) {
			JTable::addIncludePath(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djreviews/tables'));
			JModelLegacy::addIncludePath(JPATH_ROOT.JPath::clean('/components/com_djreviews/models'), 'DJReviewsModel');
			$this->models[$name] = JModelLegacy::getInstance($name, 'DJReviewsModel', array('ignore_request' => true));
		}
		
		return $this->models[$name];
	}
	
	public function initialise() {
		// already initialised
		if ((int)$this->id > 0) {
			return true;
		}
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__djrevs_objects');
		$query->where('entry_id='.(int)$this->entry_id);
		$query->where('object_type='.$db->quote($this->object_type));
		
		// not sure about this
		//$query->where('rating_group_id='.(int)$this->rating_group_id);
		
		$db->setQuery($query);
		
		//$object = $db->loadObject();
		// we are selecting all matching objects and choosing only the one with appropriate ID
		$objects = $db->loadObjectList();
		
		$object = null;
		
		foreach($objects as $obj) {
			if ($obj->rating_group_id != (int)$this->rating_group_id) {
				// we are removeing objects and reviews that match given content type but review group is different
				// it may happen when admin changes rating group
				self::purgeObject($obj->id);
			} else {
				$object = $obj;
			}
		}
		
		$update_needed 	= false;
		$is_new 		= true;
		if ($object) {
			$is_new = false;
			
			$this->id 		= $object->id;
			
			if ($this->name != $object->name) {
				//$this->name 	= $object->name;
				$update_needed = true;
			}
			
			if ($this->link != $object->link) {
				//$this->link 	= $object->link;
				$update_needed = true;
			}
			
			/*if ($this->rating_group_id != $object->rating_group_id) {
			 //$this->rating_group_id 	= $object->rating_group_id;
			 $update_needed = true;
			 }*/
			
			if ($this->plugin != $object->plugin) {
				$update_needed = true;
			}
			
			$this->avg_rate = $object->avg_rate;
		}
		
		if ($is_new || $update_needed) {
			$row = new stdClass();
			
			$row->id 				= $this->id;
			$row->object_type 		= $this->object_type;
			$row->entry_id 			= $this->entry_id;
			$row->rating_group_id	= $this->rating_group_id;
			$row->name 				= $this->name;
			$row->link 				= $this->link;
			$row->avg_rate 			= $this->avg_rate;
			$row->plugin 			= $this->plugin;
			
			if ($is_new) {
				try {
					$db->insertObject('#__djrevs_objects', $row, 'id');
				} catch (RuntimeException $e) {
					if (defined('JDEBUG') && JDEBUG) {
						JLog::add('DJ-Reviews - Cannot initialise object', JLOG::ERROR);
					}
					return false;
				}
				
				$this->id = $row->id;
			} else if ($update_needed) {
				try {
					$db->updateObject('#__djrevs_objects', $row, 'id', false);
				} catch (RuntimeException $e) {
					if (defined('JDEBUG') && JDEBUG) {
						JLog::add('DJ-Reviews - Cannot update object', JLOG::ERROR);
					}
					return false;
				}
			}
		}
		
		$options = array(
			//'url' => JRoute::_(JUri::base(false).'index.php', false),
			'url' => JUri::base(false),
			'object_url' => $this->link,
			'object_id' => $this->id,
			'lang' => array('invalid_form' => JText::_('COM_DJREVIEWS_ERROR_FORM_MALFORMED')),
			'captcha_key' => null
		);
		
		$user = JFactory::getUser();
		
		$captcha_key = '';
		if (JPluginHelper::isEnabled('captcha', 'recaptcha') && $user->guest) {
			$recaptcha =  JPluginHelper::getPlugin('captcha', 'recaptcha');
			$recaptcha_params = $recaptcha->params;
			if (!is_object($recaptcha_params) && $recaptcha_params !='') {
				$recaptcha_params = new Registry($recaptcha_params);
			}
			
			if ($recaptcha_params->get('public_key') && $recaptcha_params->get('private_key')) {
				$captcha_key = $recaptcha_params->get('public_key');
			}
		}
		
		if ($captcha_key) {
			JHtml::_('script', 'https://www.google.com/recaptcha/api.js?render=explicit&hl=' . JFactory::getLanguage()->getTag());
			$options['captcha_key'] = $captcha_key;
		}
		
		if (!self::$js_core) {
			
			$version = new JVersion;
			$document = JFactory::getDocument();
			
			if ($this->params->get('jquery', '0') != '3') {
				if ($this->params->get('jquery', '0') == '0' && JFile::exists(JPath::clean(JPATH_ROOT.'/libraries/cms/html/jquery.php'))) {
					JHtml::_('jquery.framework', true);
				} else if ($this->params->get('jquery') == '1'){
					$document->addScript(JUri::base().'/components/com_djreviews/assets/js/jquery.min.js');
				} else {
					$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js');
				}
			}
			
			
			
			$document->addScript(JUri::base().'components/com_djreviews/assets/js/core.js');
			
			self::$js_core = true;
		}
		
		JFactory::getDocument()->addScriptDeclaration('
			jQuery(document).ready(function(){
				new DJReviewsCore('.json_encode($options).');
			});
		');
		
		return true;
	}
	
	public static function recalculate() {
		$db = JFactory::getDbo();
		
		// First we need to recalculate reviews rating of which might have changed due to changes in rating fields
		$db->setQuery('select distinct id from #__djrevs_reviews where recalculate = 1');
		$reviewIds = $db->loadColumn();
		
		if (count($reviewIds)) {
			JTable::addIncludePath(JPath::clean(JPATH_ROOT.'/administrator/components/com_djreviews/tables'));
			$table = JTable::getInstance('Reviews', 'DJReviewsTable', array());
			
			foreach ($reviewIds as $reviewId) {
				$table->reset();
				if ($table->loadGreedy($reviewId)) {
					// set 'recalculate' to 2 which means that rating object will have to be recalculated as well
					$table->recalculate = 2;
					// rating is being recalculated upon saving review object
					$table->store();
				}
			}
		}
		
		// Now we need to recalculate rating objects
		$db->setQuery('select distinct object_id from #__djrevs_reviews where recalculate = 2');
		$objectIds = $db->loadColumn();
		
		if (count($objectIds)) {
			JTable::addIncludePath(JPath::clean(JPATH_ROOT.'/administrator/components/com_djreviews/tables'));
			$table = JTable::getInstance('Objects', 'DJReviewsTable', array());
			
			foreach ($objectIds as $objectId) {
				$table->reset();
				if ($table->load($objectId)) {
					if ($table->recalculateRating()) {
						$db->setQuery('UPDATE #__djrevs_reviews SET recalculate=0 WHERE object_id = '.$table->id);
						$db->query();
					}
				}
			}
		}
		
		$db->setQuery('UPDATE #__djrevs_objects SET avg_rate=0 WHERE id NOT IN (SELECT DISTINCT object_id FROM #__djrevs_reviews)');
		$db->query();
	}
	
	public static function purgeObject($object_id) {
		$db = JFactory::getDbo();
		$db->setQuery($db->getQuery(true)->delete()->from('#__djrevs_objects')->where('id='.(int)$object_id))->execute();
		$db->setQuery($db->getQuery(true)->delete()->from('#__djrevs_objects_items')->where('object_id='.(int)$object_id))->execute();
		
		$review_ids = $db->setQuery($db->getQuery(true)->select('id')->from('#__djrevs_reviews')->where('object_id='.(int)$object_id))->loadColumn();
		
		if (count($review_ids) > 0) {
			$db->setQuery($db->getQuery(true)->delete()->from('#__djrevs_reviews')->where('object_id='.(int)$object_id))->execute();
			$db->setQuery($db->getQuery(true)->delete()->from('#__djrevs_reviews_items')->where('review_id IN ('.implode(',', $review_ids).')'))->execute();
		}
		
		return true;
	}
}