<?php
/*******
 * @package xbBooks
 * @filesource site/views/bookreviews/view.html.php
 * @version 1.0.3.7 24th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

class XbbooksViewBookreviews extends JViewLegacy {
    
    function display($tpl = null) {

        // Get data from the model
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state			= $this->get('State');
        $this->filterForm    	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');
        $this->searchTitle = $this->state->get('filter.search');
        
        $params = $this->state->get('params'); //ComponentHelper::getParams('com_xbbooks');
        $this->zero_rating = $params->get('zero_rating');
        $this->zero_class = $params->get('zero_class');
        $this->stars_class = $params->get('stars_class');
        
        $this->header = array();
        $this->header['showheading'] = $this->params->get('show_page_heading',0,'int');
        $this->header['heading'] = $this->params->get('page_heading','','text');
        if ($this->header['heading'] =='') {
            $this->header['heading'] = $this->params->get('page_title','','text');
        }
        $this->header['title'] = $this->params->get('list_title','','text');
        $this->header['subtitle'] = $this->params->get('list_subtitle','','text');
        $this->header['text'] = $this->params->get('list_headtext','','text');
        
        $this->search_bar = $this->params->get('search_bar','','int');
        $this->hide_cat = (!$this->showcat || ($this->params->get('menu_category_id',0)>0)) ? true : false;
        $this->hide_tag = (!$this->showtags || (!empty($this->params->get('menu_tag','')))) ? true : false;
        
        $this->show_pic = $this->params->get('show_bpiccol','1','int');
        $this->show_sum = $this->params->get('show_bsumcol','1','int');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
        	Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
        	return false;
        }
        
        // Display the template
        parent::display($tpl);
        
        // Set the document
        $this->setDocument();
    }
        
    protected function setDocument()
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('xbBooks: Reviews'));
    }
    
}