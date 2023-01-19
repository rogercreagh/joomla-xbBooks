<?php
/*******
 * @package xbBooks
 * @filesource admin/views/characters/view.html.php
 * @version 1.0.3.4 19th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class XbbooksViewCharacters extends JViewLegacy {

    function display($tpl = null) {
           
        $this->items		= $this->get('Items');
        
        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');
        
        $this->searchTitle = $this->state->get('filter.search');
        $this->catid 		= $this->state->get('catid');

        
        $this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
        
       if ($this->catid>0) {
           $this->cat 		= XbcultureHelper::getCat($this->catid);
       }
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
            return false;
        }
                
        // Set the toolbar & sidebar
        $this->addToolbar();
        XbbooksHelper::addSubmenu('characters');
        $this->sidebar = JHtmlSidebar::render();
        
        // Display the template
        parent::display($tpl);
        
        // Set the document
        $this->setDocument();
    }
    
    protected function addToolBar() {
        $canDo = XbbooksHelper::getActions();
        
        $bar = Toolbar::getInstance('toolbar');
        
        ToolBarHelper::title(JText::_('XBBOOKS').': '.JText::_('XBCULTURE_TITLE_CHARMANAGER'), 'users' );
        
        if ($canDo->get('core.create') > 0) {
            ToolbarHelper::custom('characters.charnew','new','','New',false);
        }
        if ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))) {
            ToolbarHelper::editList('characters.charedit');
        }
        
        if ($canDo->get('core.edit.state')) {
            ToolBarHelper::publish('characters.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('characters.publish', 'JTOOLBAR_UNPUBLISH', true);
        }

        ToolbarHelper::custom(); //spacer
        	ToolbarHelper::custom('characters.allchars', 'users', '', 'All Characters', false) ;
        
        if ($canDo->get('core.admin')) {
            ToolBarHelper::preferences('com_xbbooks');
        }
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('XBBOOKS_ADMIN_CHARS'));
    }
    
}