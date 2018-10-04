<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

class Djcatalog2Itemevents extends JEvent {
	
	public function onContentBeforeSave( $context, $table, $isNew ) {
		switch($context) {
			case 'com_djcatalog2.itemform' : {
				return $this->onItemBeforeSave($context, $table, $isNew);
				break;
			}
		}
	}
	
	public function onContentAfterSave( $context, $table, $isNew ) {
		switch($context) {
			case 'com_djcatalog2.itemform' : {
				return $this->onItemAfterSave($context, $table, $isNew);
				break;
			}
		}
	}
	public function onContentAfterDelete( $context, $table) {
		switch($context) {
			case 'com_djcatalog2.itemform' : {
				return $this->onItemAfterDelete($context, $table);
				break;
			}
		}
	}
	
	public function onItemBeforeSave( $context, $table, $isNew ) {
		$app = JFactory::getApplication();
		$attribs = $app->input->get('attribute',array(), 'array');
		$model = JModelAdmin::getInstance('Itemform', 'Djcatalog2Model', array());
		if (!$model->validateAttributes($attribs, $table)) {
			$errors = $model->getErrors();
			foreach ($errors as $error) {
				$app->enqueueMessage($error,'error');
			}
			$table->setError(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));
			return false;
		}
	}

	public function onItemAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$data		= $app->input->get('jform', array(), 'array');
		
		// saving additional categories
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_items_categories WHERE item_id=\''.$table->id.'\'');
		
		$category_limit = (int)$params->get('fed_multiple_categories_limit',3);
		if ($db->query()) {
			if (!isset($data['categories'])) {
				$data['categories'] = array();
			}
			
			if ($category_limit > 0) {
				$data['categories'] = array_slice($data['categories'], 0, $category_limit);
			}
			
			$data['categories'][] = $table->cat_id;
			
			if (!empty($data['categories'])) {
				$data['categories'] = array_unique($data['categories']);
				foreach ($data['categories'] as $cat_id) {
					if ($cat_id > 0) {
						$db->setQuery('INSERT INTO #__djc2_items_categories VALUES (\''.$table->id.'\', \''.$cat_id.'\')');
						$db->query();
					}
				}
			}
		}
		
		$db->setQuery('DELETE FROM #__djc2_items_groups WHERE item_id=\''.$table->id.'\'');
		if ($db->query()) {
			if (!isset($data['group_id']) || !is_array($data['group_id'])) {
				$data['group_id'] = (isset($data['group_id'])) ? array((int)$data['group_id']) : array();
			}
			$data['group_id'][] = 0;
			if (!empty($data['group_id'])) {
				$data['group_id'] = array_unique($data['group_id']);
				foreach ($data['group_id'] as $group_id) {
					$db->setQuery('INSERT INTO #__djc2_items_groups VALUES (\''.$table->id.'\', \''.$group_id.'\')');
					$db->query();
				}
			}
		}
		
		// saving images
		if (!DJCatalog2ImageHelper::saveImages('item',$table, $params, $isNew)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_SAVING_IMAGES'),'error');
		}
		
		// saving attachments
		if (!DJCatalog2FileHelper::saveFiles('item',$table, $params, $isNew)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_SAVING_FILES'),'error');
		}
		
		// saving additional attributes
		$attribs = $app->input->get('attribute',array(), 'array');
		$model = JModelAdmin::getInstance('Itemform', 'Djcatalog2Model', array());
		if (!$model->saveAttributes($attribs, $table)) {
			$app->enqueueMessage($model->getError(),'error');
		}
		
		
		if (isset($data['labels']) && $params->get('fed_labels', 1)) {
			$query = $db->getQuery(true);
			$query->delete('#__djc2_labels_items')->where('item_id='.$table->id);
			$db->setQuery($query);
			$db->execute();
			
			$labels_limit = (int)$params->get('fed_labels_limit',3);
			
			JArrayHelper::toInteger($data['labels']);
			
			if (is_array($data['labels']) && count($data['labels'])) {
				
				if ($labels_limit > 0) {
					$data['labels'] = array_slice($data['labels'], 0, $labels_limit);
				}
				
				$query = $db->getQuery(true);
				$query->insert('#__djc2_labels_items');
				$query->columns(array('item_id', 'label_id'));
				foreach($data['labels'] as  $label_id) {
					$query->values($table->id.','.$label_id);
				}
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
	public function onItemAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_items_categories WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_related WHERE item_id=\''.$table->id.'\' OR related_item=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_text WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_int WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		if (!DJCatalog2ImageHelper::deleteImages('item',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_IMAGES'),'error');
		}
		if (!DJCatalog2FileHelper::deleteFiles('item',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_FILE'),'error');
		}
	}
}