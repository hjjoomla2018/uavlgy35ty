<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

?>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=search'); ?>" method="post" name="DJC2searchForm" id="DJC2searchForm" >
	<fieldset class="djc_mod_search djc_clearfix">
		<?php if ($params->get('show_label', 1) == 1) { ?>
		<label for="mod_djcatsearch"><?php echo JText::_('MOD_DJC2SEARCH_SEARCH'); ?></label>
		<?php } ?>
		
		<input type="text" class="inputbox" name="search" id="mod_djcatsearch" value="" <?php if ($params->get('show_label', 1) == 0) echo 'placeholder="'.JText::_('MOD_DJC2SEARCH_SEARCH').'" ';?>/>
		
		<?php if ($params->get('show_button', 1) == 1) { ?>
		<button class="btn" onclick="document.DJC2searchForm.submit();"><?php echo JText::_( 'MOD_DJC2SEARCH_GO' ); ?></button>
		<?php } ?>
	</fieldset>
    
	<input type="hidden" name="option" value="com_djcatalog2" />
	<input type="hidden" name="view" value="items" />
	<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
	<input type="hidden" name="task" value="search" />
	<input type="submit" style="display: none;"/>
</form>
