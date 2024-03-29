<?php 
/*******
 * @package xbBooks
 * @filesource site/xbbooks.php
 * @version 0.12.0.1 11th December 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

//test if xbpeople here, if not dispay site root page
if (!file_exists(JPATH_ROOT."/administrator/components/com_xbpeople/")) {
    header('Location: '.Uri::root());
    exit;
}
$document = Factory::getDocument();
//xb popover stuff
HTMLHelper::_('bootstrap.framework');
$document->addScript('media/com_xbpeople/js/xbculture.js');

// Require helper files
//JLoader::register('XbbooksHelper', JPATH_COMPONENT . '/helpers/xbbooks.php');
JLoader::register('XbbooksGeneral', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/xbbooksgeneral.php');
JLoader::register('XbcultureHelper', JPATH_ADMINISTRATOR . '/components/com_xbpeople/helpers/xbculture.php');

$params = ComponentHelper::getParams('com_xbbooks');
$usexbcss = $params->get('use_xbcss',1);
if ($usexbcss<2) {
    $cssFile = Uri::root(true)."/media/com_xbpeople/css/xbculture.css";
    $altcss = $params->get('css_file','');
    if ($usexbcss==0) {
        if ($altcss && file_exists(JPATH_ROOT.$altcss)) {
            $cssFile = $altcss;
        }
    }
    $document->addStyleSheet($cssFile,array('version'=>'auto'));
    $popcolour = $params->get('popcolour','');
    if ($popcolour != '') {
        $stylestr = XbcultureHelper::popstylecolours($popcolour);
        $document->addStyleDeclaration($stylestr);
    }
    
}

$cssFile = "https://use.fontawesome.com/releases/v5.8.1/css/all.css\" integrity=\"sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf\" crossorigin=\"anonymous";
$document->addStyleSheet($cssFile);

Factory::getLanguage()->load('com_xbculture', JPATH_ADMINISTRATOR);

Factory::getSession()->set('xbbooks_ok',true);
//detect related components and set session flag
XbcultureHelper::checkComponent('com_xbpeople');
XbcultureHelper::checkComponent('com_xbfilms');
XbcultureHelper::checkComponent('com_xbevents');

// Get an instance of the controller 
$controller = BaseController::getInstance('Xbbooks');

// Perform the Request task
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

