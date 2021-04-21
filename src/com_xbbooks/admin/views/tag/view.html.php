<?php 
/*******
 * @package xbBooks
 * @filesource admin/views/tag/view.html.php
 * @version 0.9.1 8th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class XbbooksViewTag extends JViewLegacy {

	public function display($tpl = null) {
		
		$this->item = $this->get('Item');
		
		$this->addToolBar();
		XbbooksHelper::addSubmenu('tags');
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
		// Set the document
		$this->setDocument();
	}
	
	protected function addToolBar() {
		$canDo = XbbooksHelper::getActions();
		
		ToolBarHelper::title(JText::_( 'COM_XBBOOKS' ).': '.JText::_( 'XBCULTURE_TITLE_TAGMANAGER' ), 'tag' );
		
		ToolbarHelper::custom('tag.tags', 'tags', '', 'COM_XBBOOKS_TAG_LIST', false) ;
		ToolbarHelper::custom('tag.tagedit', 'edit', '', 'XBCULTURE_EDIT_TAG', false) ;
		
		if ($canDo->get('core.admin')) {
			ToolBarHelper::preferences('com_xbbooks');
		}
	}
	
	protected function setDocument()
	{
		$document = Factory::getDocument();
		$document->setTitle(JText::_('COM_XBBOOKS_ADMIN_TAGITEMS'));
	}
	
}
