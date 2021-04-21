<?php 
/*******
 * @package xbBooks
 * @filesource site/views/categories/view.html.php
 * @version 0.8.3 17th March 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksViewCategories extends JViewLegacy {
	
	public function display($tpl = null) {
		
		$this->items 		= $this->get('Items');
//		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->params      = $this->state->get('params');
// 		$this->filterForm    	= $this->get('FilterForm');
// 		$this->activeFilters 	= $this->get('ActiveFilters');
// 		$this->searchTitle = $this->state->get('filter.search');
		
		$this->header = array();
		$this->header['showheading'] = $this->params->get('show_page_heading',0,'int');
		$this->header['heading'] = $this->params->get('page_heading','','text');
		if ($this->header['heading'] =='') {
			$this->header['heading'] = $this->params->get('page_title','','text');
		}
		$this->header['title'] = $this->params->get('list_title','','text');
		$this->header['subtitle'] = $this->params->get('list_subtitle','','text');
		$this->header['text'] = $this->params->get('list_headtext','','text');
		
// 		$this->search_bar = $this->params->get('search_bar','','int');
// 		$this->show_desc = $this->params->get('show_desc','','int');
		$this->show_parent = $this->params->get('show_parent','1','int');
		$this->show_empty = $this->params->get('show_empty','0','int');
		
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
			return false;
		}
		
		parent::display($tpl);
	} // end function display()
	
}
