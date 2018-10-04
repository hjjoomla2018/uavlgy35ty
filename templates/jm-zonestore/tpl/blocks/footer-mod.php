<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

// get information about 'back to top' button
$backtotop = $this->params->get('backToTop', '1');

$highContrast = htmlspecialchars($this->params->get('highContrast','0'));

if($this->countFlexiblock('footer') or ($backtotop == '1')) : ?>
<div id="jm-footer-mod" class="<?php echo $this->getClass('block#footer-mod') ?>" <?php if($highContrast) echo 'tabindex="-1" role="region" aria-label="'.JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_LABEL_FOOTER_MOD' ).'"'; ?>>
	<?php if($backtotop == '1' && JFactory::getApplication()-> isSite()) : ?>
	<div id="jm-back-top">
		<div class="container-fluid">
			<a href="#top"><i class="fa fa-angle-up" aria-hidden="true"></i><span><?php echo JText::_('PLG_SYSTEM_JMFRAMEWORK_BACK_TO_TOP'); ?></span></a>
		</div>
	</div>
	<?php endif; ?>
	<?php if($this->countFlexiblock('footer')) : ?>
	<div id="jm-footer-mod-in" class="container-fluid">
		<?php echo $this->renderFlexiblock('footer','jmmodule'); ?>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>
