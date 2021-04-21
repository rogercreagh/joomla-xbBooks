<?php
/*******
 * @package xbBooks
 * @filesource admin/views/bcategories/view.html.php
 * @version 0.9.4 14th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class XbbooksViewBcategories extends JViewLegacy {
    
    function display($tpl = null) {
        // Get data from the model
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        $this->searchTitle = $this->state->get('filter.search');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	
            return false;
        }
        
        $this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
        
        XbbooksHelper::addSubmenu('bcategories');
        $this->sidebar = JHtmlSidebar::render();
        
        // Set the toolbar
        $this->addToolBar();
        
        // Display the template
        parent::display($tpl);
    }
    
    protected function addToolBar() {
        $canDo = XbbooksHelper::getActions();
        
        ToolbarHelper::title(Text::_( 'COM_XBBOOKS' ).': '.Text::_( 'XBCULTURE_TITLE_CATSMANAGER' ), 'folder' );
        
        //index.php?option=com_categories&view=category&layout=edit&extension=com_xbbooks
        if ($canDo->get('core.create') > 0) {
            ToolbarHelper::custom('bcategories.categorynew','new','','COM_XBBOOKS_NEW_FCAT',false);
            if (Factory::getSession()->get('xbpeople_ok')!=0) {
                ToolbarHelper::custom('bcategories.categorynewpeep','new','','COM_XBBOOKS_NEW_PCAT',false);
            }
        }
        if ($canDo->get('core.admin')) {
            ToolbarHelper::editList('bcategories.categoryedit', 'COM_XBBOOKS_EDIT_CAT');
        }
        
//        ToolbarHelper::custom('bcategories.categorylist','list-2','','COM_XBBOOK_LIST_CAT',true);
        
        ToolbarHelper::custom(); //spacer
        if ($this->xbpeople_ok) {
        	ToolbarHelper::custom('bcategories.people', 'folder', '', 'xbPeople', false) ;
        }
        
        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_xbbooks');
        }
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#admin-cats' );
    }
    
    protected function setDocument() {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_XBBOOKS_ADMIN_CATS'));
    }
}