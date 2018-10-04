<?php

/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

class JMTemplate extends JMFTemplate {
	public function postSetUp() {

	// ---------------------------------------------------------
	// LESS MAP
	// ---------------------------------------------------------
		
		// --------------------------------------
		// BOOTSTRAP
		// --------------------------------------
		
		$this->lessMap['bootstrap.less'] = array(
			'bootstrap_variables.less', 
			'template_variables.less',
			'override/ltr/accordion.less',
			'override/ltr/breadcrumbs.less',
			'override/ltr/button-groups.less',
			'override/ltr/buttons.less',
			'override/ltr/dropdowns.less',
			'override/ltr/forms.less',
			'override/ltr/labels-badges.less',
			'override/ltr/navbar.less',
			'override/ltr/navs.less',
			'override/ltr/pager.less',
			'override/ltr/pagination.less',
			'override/ltr/scaffolding.less',
			'override/ltr/tables.less',
			'override/ltr/type.less',
			'override/ltr/utilities.less',
			'override/ltr/wells.less'
		);
				
		$this->lessMap['bootstrap_rtl.less'] = array(
			'bootstrap_variables.less', 
			'template_variables.less',	
			'override/rtl/accordion.less',
			'override/rtl/breadcrumbs.less',
			'override/rtl/button-groups.less',
			'override/rtl/buttons.less',
			'override/rtl/dropdowns.less',
			'override/rtl/forms.less',
			'override/rtl/labels-badges.less',
			'override/rtl/navbar.less',
			'override/rtl/navs.less',
			'override/rtl/pager.less',
			'override/rtl/pagination.less',
			'override/rtl/scaffolding.less',
			'override/rtl/tables.less',
			'override/rtl/type.less',
			'override/rtl/utilities.less',
			'override/rtl/wells.less'
		);
				
		$this->lessMap['bootstrap_responsive.less'] = array(
			'bootstrap_variables.less', 
			'override/ltr/responsive-767px-max.less'
		);
		
		$this->lessMap['bootstrap_responsive_rtl.less'] = array(
			'bootstrap_variables.less', 
			'override/rtl/responsive-767px-max.less'
		);

		// --------------------------------------
		// TEMPLATE
		// --------------------------------------
		
		$this->lessMap['template.less'] = array(
			'bootstrap_variables.less', 
			'template_variables.less',
			'override/ltr/buttons.less', 
			'template_mixins.less',
			//template
			'animated_buttons.less',
			'editor.less',
			'joomla.less',
			'layout.less',
			'menus.less',
			'modules.less',
			//extensions
			'djmediatools.less'
		);
		
		//RTL
		$this->lessMap['template_rtl.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/rtl/buttons.less',
			'template_mixins.less',
			//extensions
			'djmediatools_rtl.less'
		);
		
		//RESPONSIVE
		$this->lessMap['template_responsive.less'] = array(
			'bootstrap_variables.less', 
			'template_variables.less', 
			'override/ltr/buttons.less',
			'template_mixins.less',
			//extensions
			'djmediatools_responsive.less'
		);
		
		// other files
		// ---------------------------
		
		$common_ltr = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/ltr/buttons.less',
			'template_mixins.less'
		);
		
		$common_rtl = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/rtl/buttons.less',
			'template_mixins.less'
		);
		
		$this->lessMap['comingsoon.less'] = $common_ltr;
		$this->lessMap['offcanvas.less'] = $common_ltr;
		$this->lessMap['offline.less'] = $common_ltr;
		$this->lessMap['custom.less'] = $common_ltr;
		
		//extensions
		$this->lessMap['djmegamenu.less'] = $common_ltr;
		$this->lessMap['djmegamenu_rtl.less'] = $common_rtl;

		$this->lessMap['djcatalog.less'] = $common_ltr;
		$this->lessMap['djcatalog_rtl.less'] = $common_rtl;
		$this->lessMap['djcatalog_responsive.less'] = $common_ltr;
		
		// ---------------------------------------------------------
		// LESS VARIABLES
		// ---------------------------------------------------------
		
		$bootstrap_vars = array();

		/* Template Layout */

		$templatefluidwidth = $this->params->get('JMfluidGridContainerLg', $this->defaults->get('JMfluidGridContainerLg'));
		$bootstrap_vars['JMfluidGridContainerLg'] = $templatefluidwidth;

		$gutterwidth = $this->params->get('JMbaseSpace', $this->defaults->get('JMbaseSpace'));
		$bootstrap_vars['JMbaseSpace'] = $gutterwidth;

		$offcanvaswidth = $this->params->get('JMoffCanvasWidth', $this->defaults->get('JMoffCanvasWidth'));
		$bootstrap_vars['JMoffCanvasWidth'] = $offcanvaswidth;

		/* Font Modifications */

		//body

		$bodyfontsize = (int)$this->params->get('JMbaseFontSize', $this->defaults->get('JMbaseFontSize'));
		$bootstrap_vars['JMbaseFontSize'] = $bodyfontsize.'px';

		$bodyfonttype = $this->params->get('bodyFontType', $this->defaults->get('bodyFontType'));
		$bodyfontfamily = $this->params->get('bodyFontFamily', $this->defaults->get('bodyFontFamily')); 
		$bodygooglewebfontfamily = $this->params->get('bodyGoogleWebFontFamily', $this->defaults->get('bodyGoogleWebFontFamily')); 
		$bodygeneratedwebfontfamily = $this->params->get('bodyGeneratedWebFont');

		switch($bodyfonttype) {
			case "0" : {
				$bootstrap_vars['JMbaseFontFamily'] = $bodyfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMbaseFontFamily'] = $bodygooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMbaseFontFamily'] = $bodygeneratedwebfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMbaseFontFamily'] = $this->defaults->get('bodyGoogleWebFontFamily');
				break;
			}
		}

		//module title

		$headingsfontsize = (int)$this->params->get('JMmoduleTitleFontSize', $this->defaults->get('JMmoduleTitleFontSize'));
		$bootstrap_vars['JMmoduleTitleFontSize'] = $headingsfontsize.'px';

		$headingsfonttype = $this->params->get('headingsFontType', $this->defaults->get('headingsFontType'));
		$headingsfontfamily = $this->params->get('headingsFontFamily', $this->defaults->get('headingsFontFamily')); 
		$headingsgooglewebfontfamily = $this->params->get('headingsGoogleWebFontFamily', $this->defaults->get('headingsGoogleWebFontFamily'));
		$headingsgeneratedwebfontfamily = $this->params->get('headingsGeneratedWebFont');

		switch($headingsfonttype) {
			case "0" : {
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsgooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsgeneratedwebfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $this->defaults->get('headingsGoogleWebFontFamily');
				break;
			}
		}

		//top menu horizontal

		$djmenufontsize = (int)$this->params->get('JMtopmenuFontSize', $this->defaults->get('JMtopmenuFontSize'));
		$bootstrap_vars['JMtopmenuFontSize'] = $djmenufontsize.'px';

		$djmenufonttype = $this->params->get('djmenuFontType', $this->defaults->get('djmenuFontType'));
		$djmenufontfamily = $this->params->get('djmenuFontFamily', $this->defaults->get('djmenuFontFamily'));
		$djmenugooglewebfontfamily = $this->params->get('djmenuGoogleWebFontFamily', $this->defaults->get('djmenuGoogleWebFontFamily'));
		$djmenugeneratedwebfontfamily = $this->params->get('djmenuGeneratedWebFont');

			switch($djmenufonttype) {
				case "0" : {
					$bootstrap_vars['JMtopmenuFontFamily'] = $djmenufontfamily;
					break;
				}
				case "1" :{
					$bootstrap_vars['JMtopmenuFontFamily'] = $djmenugooglewebfontfamily;
					break;
				}
				case "2" :{
					$bootstrap_vars['JMtopmenuFontFamily'] = $djmenugeneratedwebfontfamily;
					break;
				}
				default: {
					$bootstrap_vars['JMtopmenuFontFamily'] = $this->defaults->get('djmenuGoogleWebFontFamily');
					break;
				}
			}

		//blog title

		$blogfontsize = (int)$this->params->get('JMblogTitleFontSize', $this->defaults->get('JMblogTitleFontSize'));
		$bootstrap_vars['JMblogTitleFontSize'] = $blogfontsize.'px';

		$blogfonttype = $this->params->get('blogFontType', $this->defaults->get('blogFontType'));
		$blogfontfamily = $this->params->get('blogFontFamily', $this->defaults->get('blogFontFamily'));
		$bloggooglewebfontfamily = $this->params->get('blogGoogleWebFontFamily');
		$bloggeneratedfontfamily = $this->params->get('blogGeneratedWebFont');

		switch($blogfonttype) {
			case "0" : {
				$bootstrap_vars['JMblogTitleFontFamily'] = $blogfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMblogTitleFontFamily'] = $bloggooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMblogTitleFontFamily'] = $bloggeneratedfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMblogTitleFontFamily'] = $this->defaults->get('blogFontFamily');
				break;
			}
		}

		//article title

		$articlesfontsize = (int)$this->params->get('JMarticleTitleFontSize', $this->defaults->get('JMarticleTitleFontSize'));
		$bootstrap_vars['JMarticleTitleFontSize'] = $articlesfontsize.'px';

		$articlesfonttype = $this->params->get('articlesFontType', $this->defaults->get('articlesFontType'));
		$articlesfontfamily = $this->params->get('articlesFontFamily', $this->defaults->get('articlesFontFamily'));
		$articlesgooglewebfontfamily = $this->params->get('articlesGoogleWebFontFamily');
		$articlesgeneratedfontfamily = $this->params->get('articlesGeneratedWebFont');

		switch($articlesfonttype) {
			case "0" : {
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesgooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesgeneratedfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMarticleTitleFontFamily'] = $this->defaults->get('articlesFontFamily');
				break;
			}
		}

		/* Basic Settings */

		$JMmainContentWidth = $this->params->get('JMmainContentWidth', $this->defaults->get('JMmainContentWidth'));
		$bootstrap_vars['JMmainContentWidth'] = $JMmainContentWidth;

		$fixedcolumnwidth = $this->params->get('JMfixedColumnWidth', $this->defaults->get('JMfixedColumnWidth'));
		$bootstrap_vars['JMfixedColumnWidth'] = $fixedcolumnwidth;

		/* Color Modifications */

		//scheme color
		$JMcolorVersion = $this->params->get('JMcolorVersion', $this->defaults->get('JMcolorVersion'));
		$bootstrap_vars['JMcolorVersion'] = $JMcolorVersion;

		$JMschemeFontColor = $this->params->get('JMschemeFontColor', $this->defaults->get('JMschemeFontColor'));
		$bootstrap_vars['JMschemeFontColor'] = $JMschemeFontColor;

		//scheme images directory
		$JMimagesDir = $this->params->get('JMimagesDir', 'scheme1');
		$bootstrap_vars['JMimagesDir'] = $JMimagesDir;

		// global
		// -------------------------------------

		$JMpageBackground = $this->params->get('JMpageBackground', $this->defaults->get('JMpageBackground'));
		$bootstrap_vars['JMpageBackground'] = $JMpageBackground;

		$JMborder = $this->params->get('JMborder', $this->defaults->get('JMborder'));
		$bootstrap_vars['JMborder'] = $JMborder;

		$JMbaseFontColor = $this->params->get('JMbaseFontColor', $this->defaults->get('JMbaseFontColor'));
		$bootstrap_vars['JMbaseFontColor'] = $JMbaseFontColor;

		$JMstyleLink = $this->params->get('JMstyleLink', $this->defaults->get('JMstyleLink'));
		$bootstrap_vars['JMstyleLink'] = $JMstyleLink;

		$JMmoduleTitleColor = $this->params->get('JMmoduleTitleColor', $this->defaults->get('JMmoduleTitleColor'));
		$bootstrap_vars['JMmoduleTitleColor'] = $JMmoduleTitleColor;

		$JMarticleTitleColor = $this->params->get('JMarticleTitleColor', $this->defaults->get('JMarticleTitleColor'));
		$bootstrap_vars['JMarticleTitleColor'] = $JMarticleTitleColor;


		// logo-nav
		// -------------------------------------

		$JMlogonavBackground = $this->params->get('JMlogonavBackground', $this->defaults->get('JMlogonavBackground'));
		$bootstrap_vars['JMlogonavBackground'] = $JMlogonavBackground;

		$JMlogonavFontColor = $this->params->get('JMlogonavFontColor', $this->defaults->get('JMlogonavFontColor'));
		$bootstrap_vars['JMlogonavFontColor'] = $JMlogonavFontColor;

		$JMlogonavBorder = $this->params->get('JMlogonavBorder', $this->defaults->get('JMlogonavBorder'));
		$bootstrap_vars['JMlogonavBorder'] = $JMlogonavBorder;

		$JMlogonavInputBorder = $this->params->get('JMlogonavInputBorder', $this->defaults->get('JMlogonavInputBorder'));
		$bootstrap_vars['JMlogonavInputBorder'] = $JMlogonavInputBorder;

		// topmenu
		// -------------------------------------

		$JMtopmenuBackground = $this->params->get('JMtopmenuBackground', $this->defaults->get('JMtopmenuBackground'));
		$bootstrap_vars['JMtopmenuBackground'] = $JMtopmenuBackground;

		$JMtopmenuFontColor = $this->params->get('JMtopmenuFontColor', $this->defaults->get('JMtopmenuFontColor'));
		$bootstrap_vars['JMtopmenuFontColor'] = $JMtopmenuFontColor;

		$JMtopmenuFontColorHover = $this->params->get('JMtopmenuFontColorHover', $this->defaults->get('JMtopmenuFontColorHover'));
		$bootstrap_vars['JMtopmenuFontColorHover'] = $JMtopmenuFontColorHover;

		$JMtopmenuBorder = $this->params->get('JMtopmenuBorder', $this->defaults->get('JMtopmenuBorder'));
		$bootstrap_vars['JMtopmenuBorder'] = $JMtopmenuBorder;

		//submenu
		$JMtopSubmenuBackground = $this->params->get('JMtopSubmenuBackground', $this->defaults->get('JMtopSubmenuBackground'));
		$bootstrap_vars['JMtopSubmenuBackground'] = $JMtopSubmenuBackground;
		
		$JMtopSubmenuFontColor = $this->params->get('JMtopSubmenuFontColor', $this->defaults->get('JMtopSubmenuFontColor'));
		$bootstrap_vars['JMtopSubmenuFontColor'] = $JMtopSubmenuFontColor;
		
		$JMtopSubmenuFontColorHover = $this->params->get('JMtopSubmenuFontColorHover', $this->defaults->get('JMtopSubmenuFontColorHover'));
		$bootstrap_vars['JMtopSubmenuFontColorHover'] = $JMtopSubmenuFontColorHover;

		$JMtopSubmenuBorder = $this->params->get('JMtopSubmenuBorder', $this->defaults->get('JMtopSubmenuBorder'));
		$bootstrap_vars['JMtopSubmenuBorder'] = $JMtopSubmenuBorder;

		// header
		// -------------------------------------

		$JMheaderBackground = $this->params->get('JMheaderBackground', $this->defaults->get('JMheaderBackground'));
		$bootstrap_vars['JMheaderBackground'] = $JMheaderBackground;

		$JMheaderFontColor = $this->params->get('JMheaderFontColor', $this->defaults->get('JMheaderFontColor'));
		$bootstrap_vars['JMheaderFontColor'] = $JMheaderFontColor;

		$JMheaderModuleTitle = $this->params->get('JMheaderModuleTitle', $this->defaults->get('JMheaderModuleTitle'));
		$bootstrap_vars['JMheaderModuleTitle'] = $JMheaderModuleTitle;

		// top1
		// -------------------------------------

		$JMtop1Background = $this->params->get('JMtop1Background', $this->defaults->get('JMtop1Background'));
		$bootstrap_vars['JMtop1Background'] = $JMtop1Background;

		$JMtop1FontColor = $this->params->get('JMtop1FontColor', $this->defaults->get('JMtop1FontColor'));
		$bootstrap_vars['JMtop1FontColor'] = $JMtop1FontColor;

		$JMtop1ModuleTitle = $this->params->get('JMtop1ModuleTitle', $this->defaults->get('JMtop1ModuleTitle'));
		$bootstrap_vars['JMtop1ModuleTitle'] = $JMtop1ModuleTitle;

		// top2
		// -------------------------------------

		$JMtop2Background = $this->params->get('JMtop2Background', $this->defaults->get('JMtop2Background'));
		$bootstrap_vars['JMtop2Background'] = $JMtop2Background;

		$JMtop2FontColor = $this->params->get('JMtop2FontColor', $this->defaults->get('JMtop2FontColor'));
		$bootstrap_vars['JMtop2FontColor'] = $JMtop2FontColor;

		$JMtop2ModuleTitle = $this->params->get('JMtop2ModuleTitle', $this->defaults->get('JMtop2ModuleTitle'));
		$bootstrap_vars['JMtop2ModuleTitle'] = $JMtop2ModuleTitle;

		// bottom1
		// -------------------------------------

		$JMbottom1Background = $this->params->get('JMbottom1Background', $this->defaults->get('JMbottom1Background'));
		$bootstrap_vars['JMbottom1Background'] = $JMbottom1Background;

		$JMbottom1FontColor = $this->params->get('JMbottom1FontColor', $this->defaults->get('JMbottom1FontColor'));
		$bootstrap_vars['JMbottom1FontColor'] = $JMbottom1FontColor;

		$JMbottom1ModuleTitle = $this->params->get('JMbottom1ModuleTitle', $this->defaults->get('JMbottom1ModuleTitle'));
		$bootstrap_vars['JMbottom1ModuleTitle'] = $JMbottom1ModuleTitle;

		// bottom2
		// -------------------------------------

		$JMbottom2Background = $this->params->get('JMbottom2Background', $this->defaults->get('JMbottom2Background'));
		$bootstrap_vars['JMbottom2Background'] = $JMbottom2Background;

		$JMbottom2FontColor = $this->params->get('JMbottom2FontColor', $this->defaults->get('JMbottom2FontColor'));
		$bootstrap_vars['JMbottom2FontColor'] = $JMbottom2FontColor;

		$JMbottom2ModuleTitle = $this->params->get('JMbottom2ModuleTitle', $this->defaults->get('JMbottom2ModuleTitle'));
		$bootstrap_vars['JMbottom2ModuleTitle'] = $JMbottom2ModuleTitle;

		// footer
		// -------------------------------------

		$JMfooterBackground = $this->params->get('JMfooterBackground', $this->defaults->get('JMfooterBackground'));
		$bootstrap_vars['JMfooterBackground'] = $JMfooterBackground;

		$JMfooterFontColor = $this->params->get('JMfooterFontColor', $this->defaults->get('JMfooterFontColor'));
		$bootstrap_vars['JMfooterFontColor'] = $JMfooterFontColor;

		$JMfooterModuleTitle = $this->params->get('JMfooterModuleTitle', $this->defaults->get('JMfooterModuleTitle'));
		$bootstrap_vars['JMfooterModuleTitle'] = $JMfooterModuleTitle;

		// offcanvas
		// -------------------------------------

		$JMoffCanvasBackground = $this->params->get('JMoffCanvasBackground', $this->defaults->get('JMoffCanvasBackground'));
		$bootstrap_vars['JMoffCanvasBackground'] = $JMoffCanvasBackground;

		$JMoffCanvasFontColor = $this->params->get('JMoffCanvasFontColor', $this->defaults->get('JMoffCanvasFontColor'));
		$bootstrap_vars['JMoffCanvasFontColor'] = $JMoffCanvasFontColor;

		$JMoffCanvasModuleTitle = $this->params->get('JMoffCanvasModuleTitle', $this->defaults->get('JMoffCanvasModuleTitle'));
		$bootstrap_vars['JMoffCanvasModuleTitle'] = $JMoffCanvasModuleTitle;

		// modules
		// -------------------------------------

		$JMcolor1msBackground = $this->params->get('JMcolor1msBackground', $this->defaults->get('JMcolor1msBackground'));
		$bootstrap_vars['JMcolor1msBackground'] = $JMcolor1msBackground;

		$JMcolor1msBorder = $this->params->get('JMcolor1msBorder', $this->defaults->get('JMcolor1msBorder'));
		$bootstrap_vars['JMcolor1msBorder'] = $JMcolor1msBorder;

		$JMcolor1msFontColor = $this->params->get('JMcolor1msFontColor', $this->defaults->get('JMcolor1msFontColor'));
		$bootstrap_vars['JMcolor1msFontColor'] = $JMcolor1msFontColor;

		$JMcolor1msModuleTitle = $this->params->get('JMcolor1msModuleTitle', $this->defaults->get('JMcolor1msModuleTitle'));
		$bootstrap_vars['JMcolor1msModuleTitle'] = $JMcolor1msModuleTitle;

		$JMcolor2msBackground = $this->params->get('JMcolor2msBackground', $this->defaults->get('JMcolor2msBackground'));
		$bootstrap_vars['JMcolor2msBackground'] = $JMcolor2msBackground;

		$JMcolor2msBorder = $this->params->get('JMcolor2msBorder', $this->defaults->get('JMcolor2msBorder'));
		$bootstrap_vars['JMcolor2msBorder'] = $JMcolor2msBorder;

		$JMcolor2msFontColor = $this->params->get('JMcolor2msFontColor', $this->defaults->get('JMcolor2msFontColor'));
		$bootstrap_vars['JMcolor2msFontColor'] = $JMcolor2msFontColor;

		$JMcolor2msModuleTitle = $this->params->get('JMcolor2msModuleTitle', $this->defaults->get('JMcolor2msModuleTitle'));
		$bootstrap_vars['JMcolor2msModuleTitle'] = $JMcolor2msModuleTitle;

		$JMfeatures1msBackground = $this->params->get('JMfeatures1msBackground', $this->defaults->get('JMfeatures1msBackground'));
		$bootstrap_vars['JMfeatures1msBackground'] = $JMfeatures1msBackground;

		$JMfeatures1msShapeColor = $this->params->get('JMfeatures1msShapeColor', $this->defaults->get('JMfeatures1msShapeColor'));
		$bootstrap_vars['JMfeatures1msShapeColor'] = $JMfeatures1msShapeColor;

		$JMfeatures1msFontColor = $this->params->get('JMfeatures1msFontColor', $this->defaults->get('JMfeatures1msFontColor'));
		$bootstrap_vars['JMfeatures1msFontColor'] = $JMfeatures1msFontColor;

		$JMfeatures1msTitleColor = $this->params->get('JMfeatures1msTitleColor', $this->defaults->get('JMfeatures1msTitleColor'));
		$bootstrap_vars['JMfeatures1msTitleColor'] = $JMfeatures1msTitleColor;

		$JMfeatures2msBackground = $this->params->get('JMfeatures2msBackground', $this->defaults->get('JMfeatures2msBackground'));
		$bootstrap_vars['JMfeatures2msBackground'] = $JMfeatures2msBackground;

		$JMfeatures2msShapeColor = $this->params->get('JMfeatures2msShapeColor', $this->defaults->get('JMfeatures2msShapeColor'));
		$bootstrap_vars['JMfeatures2msShapeColor'] = $JMfeatures2msShapeColor;

		$JMfeatures2msFontColor = $this->params->get('JMfeatures2msFontColor', $this->defaults->get('JMfeatures2msFontColor'));
		$bootstrap_vars['JMfeatures2msFontColor'] = $JMfeatures2msFontColor;

		$JMfeatures2msTitleColor = $this->params->get('JMfeatures2msTitleColor', $this->defaults->get('JMfeatures2msTitleColor'));
		$bootstrap_vars['JMfeatures2msTitleColor'] = $JMfeatures2msTitleColor;

		// -------------------------------------
		// extensions
		// -------------------------------------

		$JMmediatoolsDescriptionFontColor = $this->params->get('JMmediatoolsDescriptionFontColor', $this->defaults->get('JMmediatoolsDescriptionFontColor'));
		$bootstrap_vars['JMmediatoolsDescriptionFontColor'] = $JMmediatoolsDescriptionFontColor;

		$JMmediatoolsDescriptionBackground = $this->params->get('JMmediatoolsDescriptionBackground', $this->defaults->get('JMmediatoolsDescriptionBackground'));
		$bootstrap_vars['JMmediatoolsDescriptionBackground'] = $JMmediatoolsDescriptionBackground;

		$JMcatalogAddToCartBackground = $this->params->get('JMcatalogAddToCartBackground', $this->defaults->get('JMcatalogAddToCartBackground'));
		$bootstrap_vars['JMcatalogAddToCartBackground'] = $JMcatalogAddToCartBackground;

		$JMcatalogAddToCartText = $this->params->get('JMcatalogAddToCartText', $this->defaults->get('JMcatalogAddToCartText'));
		$bootstrap_vars['JMcatalogAddToCartText'] = $JMcatalogAddToCartText;

		$JMcatalogAddToQueryBackground = $this->params->get('JMcatalogAddToQueryBackground', $this->defaults->get('JMcatalogAddToQueryBackground'));
		$bootstrap_vars['JMcatalogAddToQueryBackground'] = $JMcatalogAddToQueryBackground;

		$JMcatalogAddToQueryText = $this->params->get('JMcatalogAddToQueryText', $this->defaults->get('JMcatalogAddToQueryText'));
		$bootstrap_vars['JMcatalogAddToQueryText'] = $JMcatalogAddToQueryText;


		// -------------------------------------

		$this->params->set('jm_bootstrap_variables', $bootstrap_vars);

		// -------------------------------------
		// compile LESS
		// -------------------------------------

		$app=JFactory::getApplication();

		// Offline Page
		$this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/offline.less'), true);

		// DJ-Catalog
		$djcatalog_theme_css = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djcatalog.less'), true, true);
		$djcatalog_theme_rtl_css = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djcatalog_rtl.less'), true, true);
		$djcatalog_responsive_css = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djcatalog_responsive.less'), true, true);

		$djcatalog_theme_less = 'templates/'.$app->getTemplate().'/less/djcatalog.less';
		$djcatalog_theme_rtl_less = 'templates/'.$app->getTemplate().'/less/djcatalog_rtl.less';
		$djcatalog_responsive_less = 'templates/'.$app->getTemplate().'/less/djcatalog_responsive.less';

		// DJ-Megamenu
		$djmegamenu_theme_css = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djmegamenu.less'), true, true);
		$djmegamenu_theme_css_rtl = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djmegamenu_rtl.less'), true, true);

		$djmegamenu_theme_less = 'templates/'.$app->getTemplate().'/less/djmegamenu.less';
		$djmegamenu_theme_less_rtl = 'templates/'.$app->getTemplate().'/less/djmegamenu_rtl.less';
		
		// -------------------------------------
		// extensions themes
		// -------------------------------------

		$themer=(int)$this->params->get('themermode',0)==1?true:false;

		if($themer) {// add LESS files when Theme Customizer enabled
			$urlsToRemove=array(
			'templates/'.$app->getTemplate().'/css/djmegamenu.css' => array('url' => $djmegamenu_theme_less, 'type' => 'less'),
			'templates/'.$app->getTemplate().'/css/djmegamenu_rtl.css' => array('url' => $djmegamenu_theme_less_rtl, 'type' => 'less'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/theme.css' => array('url' => $djcatalog_theme_less, 'type' => 'less'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/responsive.css' => array('url' => $djcatalog_responsive_less, 'type' => 'less'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/theme.rtl.css' => array('url' => $djcatalog_theme_rtl_less, 'type' => 'less'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/responsive.rtl.css' => array('url' => $djcatalog_responsive_less, 'type' => 'less')
			);
			$app->set('jm_remove_stylesheets',$urlsToRemove);

		} else {// add CSS files when Theme Customizer disabled
			$urlsToRemove=array(
			'templates/'.$app->getTemplate().'/css/djmegamenu.css' => array('url' => $djmegamenu_theme_css, 'type' => 'css'),
			'templates/'.$app->getTemplate().'/css/djmegamenu_rtl.css' => array('url' => $djmegamenu_theme_css_rtl, 'type' => 'css'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/theme.css' => array('url' => $djcatalog_theme_css, 'type' => 'css'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/responsive.css' => array('url' => $djcatalog_responsive_css, 'type' => 'css'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/theme.rtl.css' => array('url' => $djcatalog_theme_rtl_css, 'type' => 'css'),
			'components/com_djcatalog2/themes/'.$app->getTemplate().'/css/responsive.rtl.css' => array('url' => $djcatalog_responsive_css, 'type' => 'css')
			);
			$app->set('jm_remove_stylesheets',$urlsToRemove);
		}
	}
}