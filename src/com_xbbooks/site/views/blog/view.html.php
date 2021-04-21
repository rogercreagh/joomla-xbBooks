<?php 
/*******
 * @package xbBooks
 * @filesource site/views/blog/view.html.php
 * @version 0.8.6 2nd April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksViewBlog extends JViewLegacy {

	public function display($tpl = null) {
		$this->items 		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->params      = $this->state->get('params');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
		$this->searchTitle = $this->state->get('filter.search');
		
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
		$this->hide_cat = $this->params->get('menu_category_id',0)>0 ? true : false;
		$this->hide_tag = (!empty($this->params->get('menu_tag',''))) ? true : false;
		
		$show_cats = $this->params->get('show_cats','1','int');
		$this->show_bcat = ($show_cats) ? $this->params->get('show_bcat','2','int') :0;
		$show_tags = $this->params->get('show_tags','1','int');
		$this->show_btags = ($show_tags) ? $this->params->get('show_btags','1','int') : 0;
		$this->show_rcat = ($show_cats) ? $this->params->get('show_rcat','1','int') :0;
		$this->show_rtags = ($show_tags) ? $this->params->get('show_rtags','1','int') :0;
		
		$this->zero_rating = $this->params->get('zero_rating',1);
		$this->zero_class = $this->params->get('zero_class','fas fa-thumbs-down xbred');
		$this->star_class = $this->params->get('star_class','fa fa-star');
		$this->halfstar_class = $this->params->get('halfstar_class','fa fa-star-half');
		
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
			return false;
		}
		
		parent::display($tpl);
	} // end function display()
	
	
}