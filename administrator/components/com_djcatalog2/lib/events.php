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

class Djcatalog2Event extends JEvent {
	
	public function onContentBeforeSave( $context, $table, $isNew ) {
		switch($context) {
			case 'com_djcatalog2.item' : {
				return $this->onItemBeforeSave($context, $table, $isNew);
				break;
			}
		}
	}
	
	public function onContentAfterSave( $context, $table, $isNew ) {
		switch($context) {
			case 'com_djcatalog2.item' : {
				return $this->onItemAfterSave($context, $table, $isNew);
				break;
			}
			case 'com_djcatalog2.category' : {
				return $this->onCategoryAfterSave($context, $table, $isNew);
				break;
			}
			case 'com_djcatalog2.producer' : {
				return $this->onProducerAfterSave($context, $table, $isNew);
				break;
			}
			case 'com_djcatalog2.field' : {
				return $this->onFieldAfterSave($context, $table, $isNew);
				break;
			}
			case 'com_djcatalog2.payment' : {
				return $this->onPaymentAfterSave($context, $table, $isNew);
				break;
			}
			case 'com_djcatalog2.cartfield' : {
				return $this->onCartFieldAfterSave($context, $table, $isNew);
				break;
			}
		}
	}
	public function onContentAfterDelete( $context, $table) {
		switch($context) {
			case 'com_djcatalog2.item' : {
				return $this->onItemAfterDelete($context, $table);
				break;
			}
			case 'com_djcatalog2.category' : {
				return $this->onCategoryAfterDelete($context, $table);
				break;
			}
			case 'com_djcatalog2.producer' : {
				return $this->onProducerAfterDelete($context, $table);
				break;
			}
			case 'com_djcatalog2.field' : {
				return $this->onFieldAfterDelete($context, $table);
				break;
			}
			case 'com_djcatalog2.payment' : {
				return $this->onPaymentAfterDelete($context, $table);
				break;
			}
			case 'com_djcatalog2.cartfield' : {
				return $this->onCartFieldAfterDelete($context, $table);
				break;
			}
		}
	}
	
	public function onItemBeforeSave( $context, $table, $isNew ) {
		$app = JFactory::getApplication();
		
		if ($app->input->getCmd('task') == 'import') {
			return true;
		}
		
		$attribs = $app->input->get('attribute',array(), 'array');
		$model = JModelAdmin::getInstance('Item', 'Djcatalog2Model', array());
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
		
		$task = $app->input->getCmd('task');
		
		// saving additional categories
		$db = JFactory::getDbo();
		
		if ($task == 'import') {
			if ($table->cat_id > 0) {
				$db->setQuery('DELETE FROM #__djc2_items_categories WHERE category_id=\''.$table->cat_id.'\' AND item_id=\''.$table->id.'\'');
				if ($db->query()) {
					$db->setQuery('INSERT INTO #__djc2_items_categories VALUES (\''.$table->id.'\', \''.$table->cat_id.'\')');
					$db->query();
				}
			}
		} else {
			$db->setQuery('DELETE FROM #__djc2_items_categories WHERE item_id=\''.$table->id.'\'');
			if ($db->query()) {
				if (!isset($data['categories'])) {
					$data['categories'] = array();
				}
				$data['categories'][] = $table->cat_id;
				if (!empty($data['categories'])) {
					JArrayHelper::toInteger($data['categories']);
					$data['categories'] = array_unique($data['categories']);
					foreach ($data['categories'] as $cat_id) {
						$db->setQuery('INSERT INTO #__djc2_items_categories VALUES (\''.$table->id.'\', \''.$cat_id.'\')');
						$db->query();
					}
				}
			}
		}
		
		if (!isset($data['group_id']) || !is_array($data['group_id'])) {
			$data['group_id'] = array();
		}
		$data['group_id'][] = 0;
		JArrayHelper::toInteger($data['group_id']);
		$data['group_id'] = array_unique($data['group_id']);
		
		if ($task == 'import') {
			$db->setQuery('DELETE FROM #__djc2_items_groups WHERE item_id=\''.$table->id.'\' AND group_id IN ('.implode(',', $data['group_id']).')');
		}
		else {
			$db->setQuery('DELETE FROM #__djc2_items_groups WHERE item_id=\''.$table->id.'\'');
		}
		if ($db->execute()) {
			foreach ($data['group_id'] as $group_id) {
				$db->setQuery('INSERT INTO #__djc2_items_groups VALUES (\''.$table->id.'\', \''.$group_id.'\')');
				$db->execute();
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
		
		$model = JModelAdmin::getInstance('Item', 'Djcatalog2Model', array());

		// saving additional attributes
		$attribs = $app->input->get('attribute',array(), 'array');
		if (!$model->saveAttributes($attribs, $table)) {
			$app->enqueueMessage($model->getError(),'error');
		}
		
		// saving combinations
		$combinations = $app->input->get('combinations',array(), 'array');
		$hasCombinations = $app->input->getInt('hasCombinations');
		if ($hasCombinations == 1) {
			if (!$model->saveCombinations($combinations, $table, $isNew)) {
				$app->enqueueMessage($model->getError(),'error');
			}
		}
		
		$customisations = $app->input->get('customisations',array(), 'array');
		$hasCustomisations = $app->input->getInt('hasCustomisations');
		if ($hasCustomisations == 1) {
			if (!$model->saveCustomisations($customisations, $table, $isNew)) {
				$app->enqueueMessage($model->getError(),'error');
			}
		}
		
		if (isset($data['price_tiers'])) {
			$query = $db->getQuery(true);
			$query->delete('#__djc2_items_price_tiers')->where('item_id='.$table->id);
			$db->setQuery($query);
			$db->execute();
			
			if (is_array($data['price_tiers']) && count($data['price_tiers'])) {
				$quantities = array();
				foreach($data['price_tiers'] as $k=>$v) {
					$quantity = (int)$v['quantity'];
					$price = floatval($v['price']);
					
					if (!$quantity || $price <= 0.00) {
						continue;
					}
					
					if (array_key_exists($quantity, $quantities)) {
						continue;
					}
					
					$quantities[$quantity] = $price;
				}
				
				if (count($quantities)) {
					$query = $db->getQuery(true);
					$query->insert('#__djc2_items_price_tiers');
					$query->columns(array('item_id', 'quantity', 'price'));
					foreach($quantities as $quantity => $price) {
						$query->values($table->id.','.$quantity.','.$price);
					}
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		
		if ($task != 'import') {
			$query = $db->getQuery(true);
			$query->delete('#__djc2_labels_items')->where('item_id='.$table->id);
			$db->setQuery($query);
			$db->execute();
			
			if (isset($data['labels'])) {
				JArrayHelper::toInteger($data['labels']);
				
				if (is_array($data['labels']) && count($data['labels'])) {
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
	}
	public function onItemAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_items_categories WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_items_groups WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_related WHERE item_id=\''.$table->id.'\' OR related_item=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_text WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_int WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_date WHERE item_id=\''.$table->id.'\'');
		$db->query();
		
		if (!DJCatalog2ImageHelper::deleteImages('item',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_IMAGES'),'error');
		}
		if (!DJCatalog2FileHelper::deleteFiles('item',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_FILE'),'error');
		}
		
		$db->setQuery('SELECT id FROM #__djc2_items_combinations WHERE item_id=\''.$table->id.'\'');
		$combination_ids = $db->loadColumn();
		if (count($combination_ids) > 0) {
			$db->setQuery('DELETE FROM #__djc2_items_combinations WHERE item_id=\''.$table->id.'\'');
			$db->query();
			
			$db->setQuery('DELETE FROM #__djc2_items_combinations_fields WHERE combination_id IN ('.implode(',', $combination_ids).')');
			$db->query();
		}
		
		$db->setQuery('DELETE FROM #__djc2_items_customisations WHERE item_id=\''.$table->id.'\'');
		$db->query();
	}
	public function onCategoryAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		if (!DJCatalog2ImageHelper::saveImages('category',$table, $params, $isNew)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_SAVING_IMAGES'),'error');
		}
	}
	public function onCategoryAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		if (!DJCatalog2ImageHelper::deleteImages('category',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_IMAGES'),'error');
		}
	}
	public function onProducerAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		if (!DJCatalog2ImageHelper::saveImages('producer',$table, $params, $isNew)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_SAVING_IMAGES'),'error');
		}
	}
	public function onProducerAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		if (!DJCatalog2ImageHelper::deleteImages('producer',$table->id)) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_ERROR_DELETING_IMAGES'),'error');
		}
	}
	public function onFieldAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$values = ($app->input->get('fieldtype',array(),'array'));
		$model = JModelAdmin::getInstance('Field', 'Djcatalog2Model', array());
		if (!$model->saveOptions($values, $table, $isNew)) {
			$app->enqueueMessage($model->getError(),'error');
		}
	}
	public function onFieldAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$model = JModelAdmin::getInstance('Field', 'Djcatalog2Model', array());
		if (!$model->deleteOptions($table)) {
			$app->enqueueMessage($model->getError(),'error');
		}
	}
	
	
	public function onPaymentAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$data		= $app->input->get('jform', array(), 'array');
	
		$task = $app->input->getCmd('task');
	
		$db->setQuery('DELETE FROM #__djc2_deliveries_payments WHERE payment_id=\''.$table->id.'\'');
		if ($db->query()) {
			if (!empty($data['deliveries'])) {
				JArrayHelper::toInteger($data['deliveries']);
				$data['deliveries'] = array_unique($data['deliveries']);
				foreach ($data['deliveries'] as $delivery_id) {
					$db->setQuery('INSERT INTO #__djc2_deliveries_payments (payment_id, delivery_id) VALUES (\''.$table->id.'\', \''.$delivery_id.'\')');
					$db->query();
				}
			}
		}
	}
	
	public function onPaymentAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
	
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_deliveries_payments WHERE payment_id=\''.$table->id.'\'');
		$db->query();
	}
	public function onCartFieldAfterSave( $context, $table, $isNew ) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$values = ($app->input->get('fieldtype',array(),'array'));
		$model = JModelAdmin::getInstance('CartField', 'Djcatalog2Model', array());
		if (!$model->saveOptions($values, $table, $isNew)) {
			$app->enqueueMessage($model->getError(),'error');
		}
	}
	public function onCartFieldAfterDelete( $context, $table) {
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		$app = JFactory::getApplication();
		$model = JModelAdmin::getInstance('CartField', 'Djcatalog2Model', array());
		if (!$model->deleteOptions($table)) {
			$app->enqueueMessage($model->getError(),'error');
		}
	}
}