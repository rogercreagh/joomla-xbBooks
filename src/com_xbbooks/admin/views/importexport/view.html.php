<?php
/*******
 * @package xbBooks
 * @filesource admin/views/importexport/view.html.php
 * @version 0.9.8.2 18th May 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

//jimport( 'joomla.application.component.view' );
HtmlHelper::addIncludePath(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers');

class XbbooksViewImportexport extends JViewLegacy {
//	protected $state;
	protected $form;
	
	function display($tpl = null) {
		$params = ComponentHelper::getParams('com_xbbooks');
		$this->show_sample = $params->get('show_sample');
		
		$this->form = $this->get('Form');
		XbbooksHelper::addSubmenu('Importexport');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
		    throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar() {
	    $canDo = XbbooksHelper::getActions();
	    $bar = Toolbar::getInstance('toolbar');
	    
	    ToolBarHelper::title(JText::_( 'XBBOOKS' ).': '.JText::_( 'XBCULTURE_TITLE_DATAMANAGER' ), 'file-check importexport' );

	    $samplesexist = XbcultureHelper::getIdFromAlias('#__categories', 'sample-books','com_xbbooks');
	    if ($this->show_sample==1) {
	    	if ($samplesexist > 0) {
	    		ToolbarHelper::custom('dashboard.unsample', 'file-minus', '', 'XBCULTURE_REMOVE_SAMPLE', false) ;
	    	} else {
	    		ToolbarHelper::custom('dashboard.sample', 'file-plus', '', 'XBCULTURE_INSTALL_SAMPLE', false) ;
	    	}
	    }
	    
	    if ($canDo->get('core.admin')) {
	        ToolbarHelper::preferences('com_xbbooks');
	    }	    
	    ToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#impexp' );
	}
}
