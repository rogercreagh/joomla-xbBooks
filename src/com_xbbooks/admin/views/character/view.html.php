<?php
/*******
 * @package xbBooks
 * @filesource admin/views/person/view.html.php
 * @version 0.9.1 8th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class XbbooksViewCharacter extends JViewLegacy {
    
    protected $form = null;
    
    public function display($tpl = null) {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->canDo = XbbooksHelper::getActions('com_xbbooks', 'character', $this->item->id);
        
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
        
        $isNew = ($this->item->id == 0);
        
        $title = Text::_( 'XBBOOKS' ).': ';
        if ($isNew) {
            $title .= Text::_('XBCULTURE_TITLE_NEWCHAR');
        } else {
            $title .= Text::_('XBCULTURE_TITLE_EDITCHAR');
        }
        
        ToolbarHelper::title($title, 'user');
        
        ToolbarHelper::apply('character.apply');
        ToolbarHelper::save('character.save');
        ToolbarHelper::save2new('character.save2new');
        if ($isNew) {
            ToolbarHelper::cancel('character.cancel','JTOOLBAR_CANCEL');
        } else {
            ToolbarHelper::cancel('character.cancel','JTOOLBAR_CLOSE');
        }
    }
    
    protected function setDocument() {
        $isNew = ($this->item->id < 1);
        $document = Factory::getDocument();
        $document->setTitle($isNew ? JText::_('XBBOOKS_CHAR_CREATING') :
            JText::_('XBBOOKS_CHAR_EDITING'));
    }
}