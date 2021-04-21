<?php
/*******
 * @package xbBooks
 * @filesource admin/views/review/view.html.php
 * @version 0.9.1 8th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class XbbooksViewReview extends JViewLegacy {
    
    protected $form = null;

    public function display($tpl = null) {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        
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
        $input = JFactory::getApplication()->input;
        
        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);
        
        $isNew = ($this->item->id == 0);
        
        $title = Text::_( 'COM_XBBOOKS' ).': ';
        if ($isNew) {
            $title .= Text::_('XBCULTURE_TITLE_NEWREVIEW');
        } else {
            $title .= Text::_('XBCULTURE_TITLE_EDITREVIEW');
        }
        
        ToolBarHelper::title($title, 'user');
        
        ToolBarHelper::apply('review.apply');
        ToolBarHelper::save('review.save');
        ToolBarHelper::save2new('review.save2new');
        if ($isNew) {
            ToolBarHelper::cancel('review.cancel','JTOOLBAR_CANCEL');
        } else {
            ToolBarHelper::cancel('review.cancel','JTOOLBAR_CLOSE');
        }
    }
    
    protected function setDocument() {
        $isNew = ($this->item->id < 1);
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? Text::_('COM_XBBOOKS_REVIEW_CREATING') :
            Text::_('COM_XBBOOKS_REVIEW_EDITING'));
    }
}