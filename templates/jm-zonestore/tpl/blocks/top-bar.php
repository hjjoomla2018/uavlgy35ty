<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

$highContrast = htmlspecialchars($this->params->get('highContrast','0'));

?>

<?php if($this->checkModules('skip-menu')) : ?>
	<div id="jm-skip-menu" <?php if($highContrast) echo 'aria-label="'.JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_LABEL_SKIP_MENU' ).'"'; ?>>
		<nav id="jm-skip-menu-in" class="<?php echo $this->getClass('skip-menu') ?>">
			<jdoc:include type="modules" name="<?php echo $this->getPosition('skip-menu') ?>" style="jmmoduleraw" />
		</nav>
	</div>
<?php endif; ?>

<?php

if($this->checkModules('top-bar')) : ?>
	<div id="jm-top-bar" class="<?php echo $this->getClass('block#top-bar') ?>" <?php if($highContrast) echo 'tabindex="-1" role="region" aria-label="'.JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_LABEL_TOPBAR' ).'"'; ?>>
		<jdoc:include type="modules" name="<?php echo $this->getPosition('top-bar') ?>" style="jmmoduleraw" />
	</div>
<?php endif; ?>
