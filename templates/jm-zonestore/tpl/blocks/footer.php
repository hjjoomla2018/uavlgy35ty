<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

//footer logo
$footerlogo = htmlspecialchars($this->params->get('footerlogo'));

$logotext = htmlspecialchars($this->params->get('logoText'));
$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');

$highContrast = htmlspecialchars($this->params->get('highContrast','0'));

?>
<footer id="jm-footer" <?php if($highContrast) echo 'tabindex="-1" role="contentinfo" aria-label="'.JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_LABEL_COPYRIGHT' ).'"'; ?>>
	<div id="jm-footer-in" class="container-fluid">
		<?php if ($footerlogo != '' && JFactory::getApplication()-> isSite()) : ?>
		<div id="jm-footer-logo">
			<a href="<?php echo JURI::base(); ?>">
				<img src="<?php echo JURI::base(), $footerlogo; ?>" alt="<?php if(!$logotext) { echo $sitename; } else { echo $logotext; }; ?>" />
			</a>
		</div>
		<?php endif; ?>
		<?php if($this->checkModules('copyrights')) : ?>
		<div id="jm-copyrights" class="<?php echo $this->getClass('copyrights') ?>">
			<jdoc:include type="modules" name="<?php echo $this->getPosition('copyrights') ?>" style="raw" />
		</div>
		<?php endif; ?>
		<div id="jm-poweredby">
			<a href="http://www.joomla-monster.com/" target="_blank" title="Joomla Templates" rel="nofollow">Joomla Templates</a> by Joomla-Monster.com
		</div>
	</div>
</footer>