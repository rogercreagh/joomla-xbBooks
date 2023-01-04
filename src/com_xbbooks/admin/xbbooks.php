<?php
/*******
 * @package xbBooks
 * @filesource admin/xbbooks.php
 * @version 1.0.1.3 4th January 2022
 * @since 0.2.0 23rd February 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;

$app = Factory::getApplication();
if (!Factory::getUser()->authorise('core.manage', 'com_xbbooks')) {
    $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'warning');	
    return false;
}

$document = Factory::getDocument();
Factory::getLanguage()->load('com_xbculture');

//add the component, xbculture and fontawesome css
$params = ComponentHelper::getParams('com_xbbooks');
if ($params->get('savedata','notset')=='notset') {
    $app->enqueueMessage(Text::_('XBCULTURE_OPTIONS_UNSAVED'),'Error');
}
$usexbcss = $params->get('use_xbcss',1);
if ($usexbcss<2) {
    $cssFile = Uri::root(true)."/media/com_xbpeople/css/xbculture.css";
    $altcss = $params->get('css_file','');
    if ($usexbcss==0) {
        if ($altcss && file_exists(JPATH_ROOT.$altcss)) {
            $cssFile = $altcss;
        }
    }
    $document->addStyleSheet($cssFile);
}
//add fontawesome5
$cssFile = "https://use.fontawesome.com/releases/v5.8.1/css/all.css\" integrity=\"sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf\" crossorigin=\"anonymous";
$document->addStyleSheet($cssFile);

// Require helper file
JLoader::register('XbbooksHelper', JPATH_ADMINISTRATOR . '/components/com_xbbooks/helpers/xbbooks.php');
JLoader::register('XbbooksGeneral', JPATH_ADMINISTRATOR . '/components/com_xbbooks/helpers/xbbooksgeneral.php');
JLoader::register('XbcultureHelper', JPATH_ADMINISTRATOR . '/components/com_xbpeople/helpers/xbculture.php');

$sess = Factory::getSession();
$sess->set('xbbooks_ok',true);

//detect related components and set session flag
if ($sess->get('xbpeople_ok',false) != 1) {
    if (XbbooksGeneral::checkComPeople() != 1) {
        if ($app->input->get('view')!='dashboard') {
            $app->redirect('index.php?option=com_xbbooks&view=dashboard');
            $app->close();
        }
    }
}
//if there is no session variable for films/events check them.
if (!$sess->has('xbfilms_ok')) {
    XbcultureHelper::checkComponent('com_xbfilms');
}
if (!$sess->has('xbevents_ok')) {
    XbcultureHelper::checkComponent('com_xbevents');
}

// Get an instance of the controller prefixed
$controller = BaseController::getInstance('xbbooks');

// Perform the Request task and Execute request task
$controller->execute(Factory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
