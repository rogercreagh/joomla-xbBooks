<?php
/*******
 * @package xbBooks
 * @filesource admin/views/importexport/view.html.php
 * @version 0.9.6.c 6th January 2022
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
	    
	    ToolBarHelper::title(JText::_( 'COM_XBBOOKS' ).': '.JText::_( 'XBCULTURE_TITLE_DATAMANAGER' ), 'file-check importexport' );

	    $samplesexist = XbbooksHelper::getIdFromAlias('#__categories', 'sample-books');
	    if ($this->show_sample==1) {
	    	if ($samplesexist > 0) {
	    		JToolbarHelper::custom('cpanel.unsample', 'file-minus', '', 'COM_XBBOOKS_REMOVE_SAMPLE', false) ;
	    	} else {
	    		JToolbarHelper::custom('cpanel.sample', 'file-plus', '', 'COM_XBBOOKS_INSTALL_SAMPLE', false) ;
	    	}
	    }
	    
	    if ($canDo->get('core.admin')) {
	        JToolbarHelper::preferences('com_xbbooks');
	    }	    
	    JToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#impexp' );
	}
}
