<?php
/*******
 * @package xbBooks
 * @filesource admin/views/person/view.html.php
 * @version 0.9.5 29th ay 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class XbbooksViewPerson extends JViewLegacy {
    
    protected $form = null;
    
    public function display($tpl = null) {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->canDo = XbbooksHelper::getActions('com_xbbooks', 'person', $this->item->id);
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	return false;
        }
               
        // Set the toolbar
        $this->addToolBar();
        
        // Display the template
        parent::display($tpl);
        
        // Set the document
        $this->setDocument();
    }
    
    protected function addToolBar() {
        
        $input = Factory::getApplication()->input;        
        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);
        $user = Factory::getUser();
        $userId = $user->get('id');
        $checkedOut     = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        
        $canDo = $this->canDo;
        
        $isNew = ($this->item->id == 0);
        
        $title = JText::_( 'XBBOOKS' ).': ';
        if ($isNew) {
            $title .= JText::_('XBCULTURE_TITLE_NEWPERSON');
        } elseif ($checkedOut) {
            $title = Text::_('XBCULTURE_TITLE_VIEWPERSON');
        } else {
            $title .= JText::_('XBCULTURE_TITLE_EDITPERSON');
        }
        
        ToolbarHelper::title($title, 'user');
        
        ToolbarHelper::apply('person.apply');
        ToolbarHelper::save('person.save');
        ToolbarHelper::save2new('person.save2new');
        if ($isNew) {
            ToolbarHelper::cancel('person.cancel','JTOOLBAR_CANCEL');
        } else {
            ToolbarHelper::cancel('person.cancel','JTOOLBAR_CLOSE');
        }
    }
    
    protected function setDocument() {
        $isNew = ($this->item->id < 1);
        $document = Factory::getDocument();
        $document->setTitle($isNew ? Text::_('XBBOOKS_PERSON_CREATING') :
            Text::_('XBBOOKS_PERSON_EDITING'));
    }
}