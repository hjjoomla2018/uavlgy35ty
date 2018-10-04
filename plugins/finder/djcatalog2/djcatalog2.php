<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';
require_once JPATH_ADMINISTRATOR . '/components/com_djcatalog2/lib/categories.php';

/**
 * Smart Search adapter for com_content.
 *
 * @since  2.5
 */
class PlgFinderDJCatalog2 extends FinderIndexerAdapter
{
	protected $context = 'DJCatalog2';
	protected $extension = 'com_djcatalog2';
	protected $layout = 'item';
	protected $type_title = 'DJ-Catalog2 Item';
	protected $table = '#__djc2_items';
	protected $autoloadLanguage = true;
	protected $state_field = 'published';
	protected $categories = null;
	
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->categories = Djc2Categories::getInstance();
	}
	
	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_djcatalog2.item')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}

		// Remove item from the index.
		return $this->remove($id);
	}

	/**
	 * Smart Search after save content method.
	 * Reindexes the link information for an article that has been saved.
	 * It also makes adjustments if the access level of an item or the
	 * category to which it belongs has changed.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object.
	 * @param   boolean  $isNew    True if the content has just been created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle articles here.
		if ($context == 'com_djcatalog2.item' || $context == 'com_djcatalog2.itemform')
		{
			// Check if the access levels are different.
			if (!$isNew && $this->old_access != $row->access)
			{
				// Process the change.
				$this->itemAccessChange($row);
			}

			if ($row->parent_id == 0) {
				// Reindex the item.
				$this->reindex($row->id);
			} else {
				// Remove from index
				$this->remove($row->id);
			}
		}

		// Check for access changes in the category.
		if ($context == 'com_djcatalog2.category')
		{
			// Check if the access levels are different.
			if (!$isNew && $this->old_cataccess != $row->access)
			{
				$this->categoryAccessChange($row);
			}
		}

		return true;
	}

	/**
	 * Smart Search before content save method.
	 * This event is fired before the data is actually saved.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object.
	 * @param   boolean  $isNew    If the content is just about to be created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle articles here.
		if ($context == 'com_djcatalog2.item' || $context == 'com_djcatalog2.itemform')
		{
			// Query the database for the old access level if the item isn't new.
			if (!$isNew)
			{
				$this->checkItemAccess($row);
			}
		}

		// Check for access levels from the category.
		if ($context == 'com_djcatalog2.category')
		{
			// Query the database for the old access level if the item isn't new.
			if (!$isNew)
			{
				$this->checkCategoryAccess($row);
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      An array of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle articles here.
		if ($context == 'com_djcatalog2.item' || $context == 'com_djcatalog2.itemform')
		{
			$this->itemStateChange($pks, $value);
		}
		
		if ($context == 'com_djcatalog2.category')
		{
			$this->categoryStateChange($pks, $value);
		}

		// Handle when the plugin is disabled.
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format.  Not used.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		$item->setLanguage();

		// Check if the extension is enabled.
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Initialise the item parameters.
		$registry = new Registry;
		$registry->loadString($item->params);
		$item->params = JComponentHelper::getParams('com_djcatalog2', true);
		$item->params->merge($registry);

		$registry = new Registry;
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		// Trigger the onContentPrepare event.
		$summary = trim(strip_tags($item->summary));
		if ($summary == '') {
			$item->summary = $item->body;
		}
		$body = trim(strip_tags($item->body));
		if ($body == '') {
			$item->body  = $item->summary;
		}
		$item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
		$item->body = FinderIndexerHelper::prepareContent($item->body, $item->params);

		// Build the necessary route and path information.
		$item->url = $this->getUrl($item->id, $this->extension, $this->layout);
		$item->route = DJCatalog2HelperRoute::getItemRoute($item->slug, $item->catslug, $item->language);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		// Add the meta-author.
		$item->metaauthor = $item->metadata->get('author');

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');

		// Translate the state. Articles should only be published if the category is published.
		$item->state = $this->translateState($item->state, $item->cat_state);

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', $this->type_title);

		// Add the author taxonomy data.
		if (!empty($item->author) || !empty($item->created_by_alias))
		{
			$item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
		}

		// Add the category taxonomy data.
		$item->addTaxonomy('DJ-Catalog2 Category', $item->category, $item->cat_state, $item->cat_access);
		
		if ($item->producer) {
			$item->addTaxonomy('DJ-Catalog2 Producer', $item->producer);
		}

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		// Load dependent classes.
		include_once JPATH_SITE . '/components/com_djcatalog2/helpers/route.php';
		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();

		// Check if we can use the supplied SQL query.
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.id, a.name as title, a.alias, a.intro_desc AS summary, a.description AS body')
			->select('a.published as state, a.cat_id, a.created AS start_date, a.created_by')
			->select('u.name as created_by_alias, a.params')
			->select('a.metakey, a.metadesc, "" as metadata, "*" as language, a.access, 0 as version, a.ordering')
			->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
			->select('c.name AS category, c.published AS cat_state, c.access AS cat_access')
			->select('p.name AS producer');

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
		$case_when_item_alias .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id . ' END as slug';
		$query->select($case_when_item_alias);

		$case_when_category_alias = ' CASE WHEN ';
		$case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
		$case_when_category_alias .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when_category_alias .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when_category_alias .= ' ELSE ';
		$case_when_category_alias .= $c_id . ' END as catslug';
		$query->select($case_when_category_alias)

			->select('u.name AS author')
			->from('#__djc2_items AS a')
			->join('LEFT', '#__djc2_categories AS c ON c.id = a.cat_id')
			->join('LEFT', '#__djc2_producers AS p ON p.id = a.producer_id')
			->join('LEFT', '#__users AS u ON u.id = a.created_by')
			->where('a.parent_id=0');

		return $query;
	}
	
	protected function getStateQuery()
	{
		
		$query = $this->db->getQuery(true);
	
		// Item ID
		$query->select('a.id');
	
		// Item and category published state
		$query->select('a.' . $this->state_field . ' AS state, c.published AS cat_state');
	
		// Item and category access levels
		$query->select('a.access, c.access AS cat_access')
		->from($this->table . ' AS a')
		->join('LEFT', '#__djc2_categories AS c ON c.id = a.cat_id');
	
		return $query;
	}
	
	protected function checkCategoryAccess($row)
	{
		$query = $this->db->getQuery(true)
		->select($this->db->quoteName('access'))
		->from($this->db->quoteName('#__djc2_categories'))
		->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
		$this->db->setQuery($query);
	
		// Store the access level to determine if it changes
		$this->old_cataccess = $this->db->loadResult();
	}
	
	protected function categoryAccessChange($row)
	{
		$query = clone $this->getStateQuery();
		
		$parent = $this->categories->get((int)$row->id);
		
		$childrenList = array($row->id);
		$parent->makeChildrenList($childrenList);
		
		//$query->where('c.id = ' . (int) $row->id);
		$query->where('c.id IN ('.implode(',', $childrenList).')');
		// Get the access level.
		$this->db->setQuery($query);
		$items = $this->db->loadObjectList();

		// Adjust the access level for each item within the category.
		foreach ($items as $item)
		{
			// Set the access level.
			$temp = max($item->access, $row->access);
	
			// Update the item.
			$this->change((int) $item->id, 'access', $temp);
			// Reindex the item
			$this->reindex($item->id);
		}
	}
	
	protected function getItem($id)
	{
		
		// Get the list query and add the extra WHERE clause.
		$query = $this->getListQuery();
		$query->where('a.id = ' . (int) $id);

		// Get the item to index.
		$this->db->setQuery($query);
		$row = $this->db->loadAssoc();

		// Convert the item to a result object.
		$item = JArrayHelper::toObject($row, 'FinderIndexerResult');
	
		// Set the item type.
		$item->type_id = $this->type_id;
	
		// Set the item layout.
		$item->layout = $this->layout;
	
		return $item;
	}
}
