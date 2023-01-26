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
        $this->header['showheading'] = $params->get('show_page_heading',0,'int');
        $this->header['heading'] = $params->get('page_heading','','text');
        if ($this->header['heading'] =='') {
            $this->header['heading'] = $params->get('page_title','','text');
        }
        $this->header['title'] = $params->get('list_title','','text');
        $this->header['subtitle'] = $params->get('list_subtitle','','text');
        $this->header['text'] = $params->get('list_headtext','','text');
        
        $show_cats = $params->get('show_cats','1','int');
        $this->showcat = ($show_cats) ? $params->get('show_bcat','1','int') : 0;
        
        $show_tags = $params->get('show_tags','1','int');
        $this->showtags = ($show_tags) ? $params->get('show_btags','1','int') : 0;
        
        $this->search_bar = $params->get('search_bar','','int');
        $this->hide_cat = (!$this->showcat || ($params->get('menu_category_id',0)>0)) ? true : false;
        $this->hide_tag = (!$this->showtags || (!empty($params->get('menu_tag','')))) ? true : false;
        
        $this->show_pic = $params->get('show_bpiccol','1','int');
        $this->show_sum = $params->get('show_bsumcol','1','int');
        
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