<?php

/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app   = JFactory::getApplication();
$doc   = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

if (!defined('JMF_EXEC')) {
	throw new Exception(JText::_('TPL_JMTEMPLATE_MISSING_JFM'));
}

include_once(JMF_TPL_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jm_template.php');

$jmtpl = new JMTemplate($this);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/<?php echo $this->template ?>/css/print.css" type="text/css" />
	<?php $jmtpl->renderBlock('head'); ?>
</head>
<body class="contentpane modal">
	<div id="jm-allpage">
		<jdoc:include type="message" />
		<jdoc:include type="component" />
	</div>
</body>
</html>