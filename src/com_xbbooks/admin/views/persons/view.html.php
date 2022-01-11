<?php
/*******
 * @package xbBooks
 * @filesource admin/views/persons/view.html.php
 * @version 0.9.6.f 11th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class XbbooksViewPersons extends JViewLegacy {

    function display($tpl = null) {
        // Get application
        $app = Factory::getApplication();
        $context = "xbbooks.list.admin.persons";
        // Get data from the model
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');
        $this->searchTitle = $this->state->get('filter.search');
//        $this->catid 		= $this->state->get('catid');
        $this->catid 		=  $app->getUserStateFromRequest('catid', 'catid','');
        if ($this->catid>0) {
            $this->cat 		= XbbooksHelper::getCat($this->catid);
        }
        
        $this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	
            return false;
        }
        
        if ($this->getLayout() !== 'modal') {
        // Set the toolbar & sidebar
            $this->addToolbar();
            XbbooksHelper::addSubmenu('persons');
            $this->sidebar = JHtmlSidebar::render();
        }
        
        // Display the template
        parent::display($tpl);
        
        // Set the document
        $this->setDocument();
    }
    
    protected function addToolBar() {
        $canDo = XbbooksHelper::getActions();
        
        $bar = Toolbar::getInstance('toolbar');
        
        ToolBarHelper::title(JText::_('COM_XBBOOKS').': '.JText::_('XBCULTURE_TITLE_PEOPLEMANAGER'), 'users' );
        
        if ($canDo->get('core.create') > 0) {
            ToolBarHelper::addNew('person.add');
        }
        if ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))) {
            ToolBarHelper::editList('person.edit');
        }
        if ($canDo->get('core.edit.state')) {
            ToolBarHelper::publish('person.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('person.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolBarHelper::archiveList('person.archive');
        }
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
           ToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'person.delete','JTOOLBAR_EMPTY_TRASH');
        } else if ($canDo->get('core.edit.state')) {
           ToolBarHelper::trash('person.trash');
        }
        
        // Add a batch button
        if ($canDo->get('core.create') && $canDo->get('core.edit')
        		&& $canDo->get('core.edit.state'))
        {
        	// we use a standard Joomla layout to get the html for the batch button
        	$layout = new FileLayout('joomla.toolbar.batch');
        	$batchButtonHtml = $layout->render(array('title' => JText::_('JTOOLBAR_BATCH')));
        	$bar->appendButton('Custom', $batchButtonHtml, 'batch');
        }
        
        ToolbarHelper::custom(); //spacer
        if ($this->xbpeople_ok) {
        	ToolbarHelper::custom('persons.people', 'users', '', 'xbPeople', false) ;
        }
        
        if ($canDo->get('core.admin')) {
            ToolBarHelper::preferences('com_xbbooks');
        }
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#admin-people' );
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(JText::_('COM_XBBOOKS_ADMIN_PEOPLE'));
    }
    
}