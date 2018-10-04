<?php
	/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

// get logo and site description
$logo = htmlspecialchars($this->params->get('logo'));
$logotext = htmlspecialchars($this->params->get('logoText'));
$sitedescription = htmlspecialchars($this->params->get('siteDescription'));
$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');

$nightVersion = htmlspecialchars($this->params->get('nightVersion','0'));
$highContrast = htmlspecialchars($this->params->get('highContrast','0'));
$wideSite = htmlspecialchars($this->params->get('wideSite','0'));
$fontswitcher = $this->params->get('fontSizeSwitcher', '0');

if ($nightVersion
	or $highContrast
	or $wideSite
	or $fontswitcher
	or ($logo != '')
	or ($logotext != '')
	or ($sitedescription != '')
	or $this->checkModules('skip-menu')
	or $this->checkModules('search')
	or $this->checkModules('top-menu-nav')) : ?>
<header id="jm-logo-nav" class="<?php echo $this->getClass('block#logo-nav') ?>" <?php if($highContrast) echo 'role="banner"'; ?>>
	<?php if(($nightVersion or $highContrast or $wideSite or $fontswitcher) and JFactory::getApplication()-> isSite()) : ?>
		<div id="jm-wcag" aria-hidden="true">
			<div class="container-fluid">
				<ul class="jm-wcag-settings pull-right">
					<?php if($nightVersion or $highContrast) : ?>
						<li class="contrast">
							<ul>
								<li class="contrast-label"><span class="jm-separator"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_CONTRAST_TITLE' );?></span></li>
								<li><a href="index.php?contrast=normal" class="jm-normal" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_DEFAULT_LAYOUT_DESC' );?>"><span class="fa fa-sun-o" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_DEFAULT_LAYOUT_LABEL' );?></span></a></li>
								<?php if($nightVersion) : ?>
									<li><a href="index.php?contrast=night" class="jm-night" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_NIGHT_VERSION_DESC' );?>"><span class="fa fa-moon-o" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_NIGHT_VERSION_LABEL' );?></span></a></li>
								<?php endif; ?>
								<?php if($highContrast) : ?>
									<li><a href="index.php?contrast=highcontrast" class="jm-highcontrast" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST1_DESC' );?>"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST1_LABEL' );?></span></a></li>
									<li><a href="index.php?contrast=highcontrast2" class="jm-highcontrast2" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST2_DESC' );?>"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST2_LABEL' );?></span></a></li>
									<li><a href="index.php?contrast=highcontrast3" class="jm-highcontrast3" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST3_DESC' );?>"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_HIGH_CONTRAST3_LABEL' );?></span></a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if($wideSite) : ?>
						<li class="container-width">
							<ul>
								<li class="width-label"><span class="jm-separator"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_WIDTH_TITLE' );?></span></li>
								<li><a href="index.php?width=fixed" class="jm-fixed" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_FIXED_SITE_DESC' );?>"><span class="fa fa-compress" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_FIXED_SITE_LABEL' );?></span></a></li>
								<li><a href="index.php?width=wide" class="jm-wide" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_WIDE_SITE_DESC' );?>"><span class="fa fa-expand" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_WIDE_SITE_LABEL' );?></span></a></li>
							</ul>
						</li>
					<?php endif; ?>
					<?php if($fontswitcher) : ?>
						<li class="resizer">
							<ul>
								<li class="resizer-label"><span class="jm-separator"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_TITLE' );?></span></li>
								<li><a href="index.php?fontsize=70" class="jm-font-smaller" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_SMALL_DESC' );?>"><span class="fa fa-minus-circle" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_SMALL_LABEL' );?></span></a></li>
								<li><a href="index.php?fontsize=100" class="jm-font-normal" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_NORMAL_DESC' );?>"><span class="fa fa-font" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_NORMAL_LABEL' );?></span></a></li>
								<li><a href="index.php?fontsize=130" class="jm-font-larger" title="<?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_LARGE_DESC' );?>"><span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only"><?php echo JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_RESIZER_LARGE_LABEL' );?></span></a></li>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	<?php if (($logo != '') or ($logotext != '') or ($sitedescription != '') or $this->checkModules('search')) : ?>
	<div id="jm-logo-search">
		<div class="container-fluid">
			<div class="row-fluid">
				<?php if (($logo != '') or ($logotext != '') or ($sitedescription != '')) : ?>
				<div id="jm-logo-sitedesc" class="span4">
					<?php if (($logo != '') or ($logotext != '')) : ?>
					<div id="jm-logo">
						<a href="<?php echo JURI::base(); ?>">
							<?php if ($logo != '') : ?>
							<img src="<?php echo JURI::base(), $logo; ?>" alt="<?php if(!$logotext) { echo $sitename; } else { echo $logotext; }; ?>" />
							<?php else : ?>
							<?php echo '<span>'.$logotext.'</span>';?>
							<?php endif; ?>
						</a>
					</div>
					<?php endif; ?>
					<?php if ($sitedescription != '') : ?>
					<div id="jm-sitedesc">
						<?php echo $sitedescription; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if($this->checkModules('search') || $this->checkModules('mobile-button')) : ?>
				<div id="jm-search-mobilebutton" class="span8 <?php echo $this->getClass('search') ?>">
					<?php if($this->checkModules('mobile-button')) : ?>
					<div id="jm-mobile-button">
						<jdoc:include type="modules" name="<?php echo $this->getPosition('mobile-button') ?>" style="jmmoduleraw" />
					</div>
					<?php endif; ?>
					<?php if($this->checkModules('search')) : ?>
					<div id="jm-search">
						<jdoc:include type="modules" name="<?php echo $this->getPosition('search') ?>" style="jmmoduleraw" />
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<?php if($this->checkModules('top-menu-nav') || $this->checkModules('tools')) : ?>
	<nav id="jm-top-menu" class="<?php echo $this->getClass('top-menu-nav') ?>" <?php if($highContrast) echo 'tabindex="-1" role="navigation" aria-label="' . JText::_( 'PLG_SYSTEM_JMFRAMEWORK_CONFIG_TOPMENU' ) . '"'; ?>>
		<div id="jm-top-menu-in" class="container-fluid">
			<jdoc:include type="modules" name="<?php echo $this->getPosition('top-menu-nav') ?>" style="jmmoduleraw" />
			<?php if ($this->checkModules('tools')) :?>
			<div id="jm-tools">
				<jdoc:include type="modules" name="<?php echo $this->getPosition('tools') ?>" style="jmmoduleraw" />
			</div>
			<?php endif; ?>
		</div>
	</nav>
	<?php endif; ?>
</header>
<?php endif; ?>