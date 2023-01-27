<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/view.html.php
 * @version 1.0.3.8 27th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class XbbooksViewBook extends JViewLegacy {
    
    protected $form = null;
    protected $params = '';
    
    public function display($tpl = null) {

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->canDo = XbbooksHelper::getActions('com_xbbooks', 'book', $this->item->id);
        
        $this->params      = $this->get('State')->get('params');
        $this->zero_class = $this->params->get('zero_class','fas fas-thumbs-down');
        $this->star_class = $this->params->get('star_class','fa fa-star');
        $this->halfstar_class = $this->params->get('halfstar_class');
        
        $this->taggroups = $this->params->get('enable_taggroups',0);
        if ($this->taggroups) {
            $taggroup_ids = array();
            $this->taggroup1_parent = $this->params->get('taggroup1_parent',0);
            if ($this->taggroup1_parent) $taggroup_ids[] = $this->taggroup1_parent;
            $this->taggroup2_parent = $this->params->get('taggroup2_parent',0);
            if ($this->taggroup2_parent) $taggroup_ids[] = $this->taggroup2_parent;
            $this->taggroup3_parent = $this->params->get('taggroup3_parent',0);
            if ($this->taggroup3_parent) $taggroup_ids[] = $this->taggroup3_parent;
            $this->taggroup4_parent = $this->params->get('taggroup4_parent',0);
            if ($this->taggroup4_parent) $taggroup_ids[] = $this->taggroup4_parent;
            
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id, title, description')->from($db->quoteName('#__tags'))
            ->where('id IN ('.implode(',',$taggroup_ids).')');
            $db->setQuery($query);
            $this->taggroupinfo = $db->loadAssocList('id');
        }
        
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

        $title = Text::_('XBBOOKS').': ';

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
        ToolbarHelper::custom('book.save2NewRev', 'comment', '', 'XBCULTURE_BTN_SAVE2REV', false) ;
        if ($isNew) {
            ToolbarHelper::cancel('book.cancel','JTOOLBAR_CANCEL');
        } else {
            ToolbarHelper::cancel('book.cancel','JTOOLBAR_CLOSE');
        }
        ToolbarHelper::custom(); //spacer
        
        $bar = Toolbar::getInstance( 'toolbar' );
        if ($this->item->id > 0) {
            $dhtml = '<a href="index.php?option=com_xbbooks&view=book&layout=modalpv&tmpl=component&id='.$this->item->id.'"
            	data-toggle="modal" data-target="#ajax-pvmodal"
            	class="btn btn-small btn-primary"><i class="far fa-eye"></i> '.Text::_('Preview').'</a>';
            $bar->appendButton('Custom', $dhtml);
        }
        ToolbarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#bookedit' );
    }
    
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(($this->item->id == 0) ? Text::_('XBBOOKS_NEW_BOOK') : Text::_('XBBOOKS_EDIT_BOOK'));
    }
    
    
}