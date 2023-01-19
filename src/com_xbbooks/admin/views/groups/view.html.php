<?php
/*******
 * @package xbBooks
 * @filesource admin/views/groups/view.html.php
 * @version 1.0.3.4 19th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;

class XbbooksViewGroups extends JViewLegacy {

    function display($tpl = null) {

        $this->items		= $this->get('Items');

        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');
        
        $this->searchTitle = $this->state->get('filter.search');
        $this->catid 		= $this->state->get('catid');
        if ($this->catid>0) {
            $this->cat 		= XbcultureHelper::getCat($this->catid);
        }
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	
            return false;
        }
        
//         $this->xbfilms_ok = Factory::getSession()->get('xbfilms_ok');
//         $this->xbbooks_ok = Factory::getSession()->get('xbbooks_ok');
//         $this->xbevents_ok = Factory::getSession()->get('xbevents_ok');
        
        // Set the toolbar & sidebar
        $this->addToolbar();
        XbbooksHelper::addSubmenu('groups');
        $this->sidebar = JHtmlSidebar::render();
        
        // Display the template
        parent::display($tpl);
        
        // Set the document
        $this->setDocument();
    }
    
    protected function addToolBar() {
    	$canDo = ContentHelper::getActions('com_xbbooks', 'component');
    	// XbpeopleHelper::getActions();
        
        $bar = Toolbar::getInstance('toolbar');
        
        ToolBarHelper::title(Text::_('XBBOOKS_TITLE_GROUPS_ADMIN'), 'users' );
        
        if ($canDo->get('core.create') > 0) {
            ToolbarHelper::custom('groups.groupnew','new','','New',false);
        }
        if ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))) {
            ToolbarHelper::editList('groups.groupedit');
        }
        if ($canDo->get('core.edit.state')) {
            ToolBarHelper::publish('groups.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('groups.publish', 'JTOOLBAR_UNPUBLISH', true);
        }

        ToolbarHelper::custom(); //spacer
        ToolbarHelper::custom('groups.allgroups', 'users', '', 'All Groups', false) ;

        
        if ($canDo->get('core.admin')) {
            ToolBarHelper::preferences('com_xbbooks');
        }
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('XBBOOKS_ADMIN_GROUPS'));
    }
    
}