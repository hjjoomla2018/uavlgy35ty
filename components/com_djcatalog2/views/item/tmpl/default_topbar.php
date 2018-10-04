<?php
/**
* @version $Id: default_topbar.php 589 2016-01-21 06:55:48Z michal $
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/


defined ('_JEXEC') or die('Restricted access');

/*
 $display_top_bar = false;
$display_top_bar = (bool)($this->navigation && (!empty($this->navigation['prev']) || !empty($this->navigation['next'])) && ($this->params->get('show_navigation', '0') == 'top' || $this->params->get('show_navigation', '0') == 'all'));
$display_top_bar = (bool)($display_top_bar || $this->params->get('show_print_button_item', false) == '1' || $this->params->get('show_pdf_button_item', false) == '1');
$display_top_bar = (bool)($display_top_bar || ((int)$this->params->get('show_author_item', 0) > 0 && $this->item->author));
$display_top_bar = (bool)($display_top_bar || ((int)$this->params->get('show_date_item', 0) == 1 && $this->item->created != $nullDate));
$display_top_bar = (bool)($display_top_bar || ((int)$this->params->get('show_publishdate_item', 0) == 1 && $this->item->publish_up != $nullDate));
$display_top_bar = (bool)($display_top_bar || ((int)$this->params->get('show_hits_item', 0) == 1));
*/

$nullDate = JFactory::getDbo()->getNullDate();

?>

<div class="djc2_product_bar">

<?php if ($this->navigation && (!empty($this->navigation['prev']) || !empty($this->navigation['next'])) && ($this->params->get('show_navigation', '0') == 'top' || $this->params->get('show_navigation', '0') == 'all')) { ?>
	<span class="djc_product_nav">
		<?php if (!empty($this->navigation['prev'])) { ?>
			<a class="djc_prev_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['prev']->slug, $this->navigation['prev']->catslug)); ?>"><span><?php echo JText::_('COM_DJCATALOG2_PREVIOUS'); ?></span></a>
		<?php } ?>
		<?php if (!empty($this->navigation['next'])) { ?>
			<a class="djc_next_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['next']->slug, $this->navigation['next']->catslug)); ?>"><span><?php echo JText::_('COM_DJCATALOG2_NEXT'); ?></span></a>
		<?php } ?>
	</span>
<?php } ?>

<?php if ((int)$this->params->get('show_author_item', 0) > 0 && $this->item->author) { ?>
	<span class="djc_author">
		<small><?php echo JText::_('COM_DJCATALOG2_CREATED_BY').': '; ?> <?php if ((int)$this->params->get('show_author_item') == 1 && $this->item->created_by) {?>
			<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&aid='.$this->item->created_by.':'.JApplication::stringURLSafe($this->item->author));?>"><span><?php echo $this->item->author; ?></span></a>
			<?php } else {?>
			<span><?php echo $this->item->author; ?></span>
		<?php } ?>
		</small>
	</span>
	<?php } ?>

	<?php if ((int)$this->params->get('show_date_item', 0) == 1 && $this->item->created != $nullDate) { ?>
	<span class="djc_date djc_created_date">
		<small> <?php echo JText::_('COM_DJCATALOG2_CREATED_ON').': '; ?> <span><?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC3')); ?>
		</span>
		</small>
	</span>
	<?php } ?>

	<?php if ((int)$this->params->get('show_publishdate_item', 0) == 1 && $this->item->publish_up != $nullDate) { ?>
	<span class="djc_date djc_publish_date">
		<small> <?php echo JText::_('COM_DJCATALOG2_PUBLISHED_ON').': '; ?> <span><?php echo JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC3')); ?>
		</span>
		</small>
	</span>
	<?php } ?>

	<?php if ((int)$this->params->get('show_hits_item', 0) == 1) { ?>
	<span class="djc_hits">
		<small> <?php echo JText::_('COM_DJCATALOG2_HITS').': '; ?> <span><?php echo $this->item->hits; ?>
		</span>
		</small>
	</span>
	<?php } ?>

<?php if ($this->params->get('show_print_button_item', false) == '1' || $this->params->get('show_pdf_button_item', false) == '1') {?>
	<?php if ($this->params->get('show_print_button_item', false) == '1') {?>
		<a rel="nofollow" class="djc_printable_version" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug).'&tmpl=component&print=1&layout=print'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRINTABLE_BUTTON'); ?></a>
	<?php } ?>
	<?php if ($this->params->get('show_pdf_button_item', false) == '1') { ?>
		<a rel="nofollow" class="djc_print_pdf_button" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug).'&tmpl=component&print=1&layout=print&pdf=1'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRINT_PDF_BUTTON'); ?></a>
	<?php } ?>
<?php } ?>
	
</div>