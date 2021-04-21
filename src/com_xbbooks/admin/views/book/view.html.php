<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/view.html.php
 * @version 0.9.3 12th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class XbbooksViewBook extends JViewLegacy {
    
    protected $form = null;
    
    public function display($tpl = null) {

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->canDo = XbbooksHelper::getActions('com_xbbooks', 'book', $this->item->id);
        
        $params      = $this->get('State')->get('params');
        $this->zero_class = $params->get('zero_class','fas fas-thumbs-down');
        $this->star_class = $params->get('star_class','fa fa-star');
        $this->halfstar_class = $params->get('halfstar_class');
        
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	return false;
        }

        $this->addToolBar();

        parent::display($tpl);
        // Set the document
        $this->setDocument();
    }
    
    protected function addToolBar() 
    {
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);
        $user = Factory::getUser();
        $userId = $user->get('id');
        $checkedOut     = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        
        $canDo = $this->canDo;
        
        $isNew = ($this->item->id == 0);
        $icon = $isNew ? 'bookadd' : 'bookedit';

        $title = Text::_('COM_XBBOOKS').': ';

        if ($isNew) {
            $title .= JText::_('XBCULTURE_TITLE_NEWBOOK');
        } elseif ($checkedOut) {
            $title .= JText::_('XBCULTURE_TITLE_VIEWBOOK');
        } else {
            $title .= JText::_('XBCULTURE_TITLE_EDITBOOK');
        }
        ToolBarHelper::title($title, 'book');
        
        ToolbarHelper::apply('book.apply');
        ToolbarHelper::save('book.save');
        ToolbarHelper::save2new('book.save2new');
        ToolbarHelper::save2copy('book.save2copy');
        ToolbarHelper::custom('book.save2NewPer', 'user', '', 'XBCULTURE_BTN_SAVE2PER', false) ;
        ToolbarHelper::custom('book.save2NewChar', 'user', '', 'XBCULTURE_BTN_SAVE2CHAR', false) ;
        ToolbarHelper::custom('book.save2NewRev', 'comment', '', 'XBCULTURE_BTN_SAVE2REV', false) ;
        if ($isNew) {
            ToolbarHelper::cancel('book.cancel','JTOOLBAR_CANCEL');
        } else {
            ToolbarHelper::cancel('book.cancel','JTOOLBAR_CLOSE');
        }
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#bookedit' );
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(($this->item->id == 0) ? Text::_('COM_XBBOOKS_NEW_BOOK') : Text::_('COM_XBBOOKS_EDIT_BOOK'));
    }
    
    
}