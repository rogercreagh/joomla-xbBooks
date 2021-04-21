<?php
/*******
 * @package xbBooks
 * @filesource admin/views/characters/view.html.php
 * @version 0.9.1 8th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
            $this->cat 		= XbbooksHelper::getCat($this->catid);
        }
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	
            return false;
        }
        
        $this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
        
        if ($this->getLayout() !== 'modal') {
        // Set the toolbar & sidebar
            $this->addToolbar();
            XbbooksHelper::addSubmenu('characters');
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
        
        ToolBarHelper::title(JText::_('COM_XBBOOKS').': '.JText::_('XBCULTURE_TITLE_CHARMANAGER'), 'users' );
        
        if ($canDo->get('core.create') > 0) {
            ToolBarHelper::addNew('character.add');
        }
        if ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))) {
            ToolBarHelper::editList('character.edit');
        }
        if ($canDo->get('core.edit.state')) {
            ToolBarHelper::publish('character.publish', 'JTOOLBAR_PUBLISH', true);
            ToolBarHelper::unpublish('character.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolBarHelper::archiveList('character.archive');
        }
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
           ToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'character.delete','JTOOLBAR_EMPTY_TRASH');
        } else if ($canDo->get('core.edit.state')) {
           ToolBarHelper::trash('character.trash');
        }
        
        // Add a batch button
        if ($canDo->get('core.create') && $canDo->get('core.edit')
        		&& $canDo->get('core.edit.state'))
        {
        	// we use a standard Joomla layout to get the html for the batch button
        	$layout = new JLayoutFile('joomla.toolbar.batch');
        	$batchButtonHtml = $layout->render(array('title' => JText::_('JTOOLBAR_BATCH')));
        	$bar->appendButton('Custom', $batchButtonHtml, 'batch');
        }
        
        ToolbarHelper::custom(); //spacer
        if ($this->xbpeople_ok) {
        	ToolbarHelper::custom('characters.people', 'users', '', 'xbPeople', false) ;
        }
        
        if ($canDo->get('core.admin')) {
            ToolBarHelper::preferences('com_xbbooks');
        }
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_XBBOOKS_ADMIN_CHARS'));
    }
    
}