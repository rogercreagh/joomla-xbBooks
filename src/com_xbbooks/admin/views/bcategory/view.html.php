<?php 
/*******
 * @package xbBooks
 * @filesource admin/views/bcategory/view.html.php
 * @version 0.9.4 14th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class XbbooksViewBcategory extends JViewLegacy {

	public function display($tpl = null) {
		
		$this->item = $this->get('Item');
		
		$this->addToolBar();
		XbbooksHelper::addSubmenu('bcategories');
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
		// Set the document
		$this->setDocument();
	}
	
	protected function addToolBar() {
		$canDo = XbbooksHelper::getActions();
		
		ToolBarHelper::title(JText::_( 'COM_XBBOOKS' ).': '.JText::_( 'XBCULTURE_TITLE_CATMANAGER' ), 'tag' );
		
		ToolbarHelper::custom('bcategory.bcategories', 'folder', '', 'COM_XBBOOKS_CAT_LIST', false) ;
		
		if ($canDo->get('core.admin')) {
			ToolBarHelper::preferences('com_xbbooks');
		}
	}
	
	protected function setDocument()
	{
		$document = Factory::getDocument();
		$document->setTitle(JText::_('COM_XBBOOKS_ADMIN_CATITEMS'));
	}
	
}
