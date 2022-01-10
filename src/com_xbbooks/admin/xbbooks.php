<?php
/*******
 * @package xbBooks
 * @filesource admin/xbbooks.php
 * @version 0.9.6.f 10th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;

if (!Factory::getUser()->authorise('core.manage', 'com_xbbooks')) {
	Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'),'warning');	
    return false;
}

//add the component, xbculture and fontawesome css
$document = Factory::getDocument();
$cssFile = Uri::root(true)."/media/com_xbpeople/css/xbculture.css";
$document->addStyleSheet($cssFile);
$cssFile = "https://use.fontawesome.com/releases/v5.8.1/css/all.css\" integrity=\"sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf\" crossorigin=\"anonymous";
$document->addStyleSheet($cssFile);

Factory::getLanguage()->load('com_xbculture', JPATH_ADMINISTRATOR);

// Require helper file
JLoader::register('XbbooksHelper', JPATH_ADMINISTRATOR . '/components/com_xbbooks/helpers/xbbooks.php');
JLoader::register('XbbooksGeneral', JPATH_ADMINISTRATOR . '/components/com_xbbooks/helpers/xbbooksgeneral.php');
JLoader::register('XbcultureHelper', JPATH_ADMINISTRATOR . '/components/com_xbpeople/helpers/xbculture.php');

Factory::getSession()->set('xbbooks_ok',true);

//detect related components and set session flag
if (!Factory::getSession()->get('xbpeople_ok',false)) {
    if (file_exists(JPATH_ADMINISTRATOR . '/components/com_xbpeople/helpers/xbculture.php')) {
        XbcultureHelper::checkComponent('com_xbpeople');
    } else {
        $app = Factory::getApplication();
        if ($app->input->get('view')!='cpanel') {
            $app->redirect('index.php?option=com_xbbooks&view=cpanel');
            $app->close();
        }
    }
}

// Get an instance of the controller prefixed
$controller = BaseController::getInstance('xbbooks');

// Perform the Request task and Execute request task
$controller->execute(Factory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
