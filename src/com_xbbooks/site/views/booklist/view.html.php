<?php 
/*******
 * @package xbBooks
 * @filesource site/views/booklist/view.html.php
 * @version 1.1.0.1 27th March 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksViewBooklist extends JViewLegacy {
	
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
		
		$show_cats = $this->params->get('show_cats','1','int');
		$this->showcat = ($show_cats) ? $this->params->get('show_bcat','1','int') : 0;
		
		$show_tags = $this->params->get('show_tags','1','int');
		$this->showtags = ($show_tags) ? $this->params->get('show_btags','1','int') : 0;

		$this->showfict = $this->params->get('show_fict','1','int');
		
		$this->show_ctcol = $this->showcat + $this->showtags + $this->showfict;
		
		$this->hide_empty = $this->params->get('hide_empty',1);
		
		$this->search_bar = $this->params->get('search_bar','','int');
		$this->hide_cat = (!$this->showcat || ($this->params->get('menu_category_id',0)>0)) ? true : false;
		$this->hide_tag = (!$this->showtags || (!empty($this->params->get('menu_tag','')))) ? true : false;
		$this->hide_peep = $this->params->get('menu_perfilt',0)>0 ? true : false;
		$this->hide_char = $this->params->get('menu_charfilt',0)>0 ? true : false;
		$this->hide_fict = (!$this->showfict || ($this->params->get('menu_fiction',0)>0)) ? true : false;

		$this->show_pic = $this->params->get('show_bpiccol','1','int');
		$this->show_sum = $this->params->get('show_bsumcol','1','int');
		$show_revs = $this->params->get('show_revs','1','int');
		$this->show_revs = ($show_revs) ? $this->params->get('show_brevcol','2','int') : 0;


		$this->show_bdates = $this->params->get('show_bdates','1','int');
		
		$this->zero_rating = $this->params->get('zero_rating',1);
		$this->zero_class = $this->params->get('zero_class','fas fa-thumbs-down xbred');
		$this->star_class = $this->params->get('star_class','fa fa-star');
		$this->halfstar_class = $this->params->get('halfstar_class','fa fa-star-half');

		
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
			return false;
		}
		//set metadata
		$document=$this->document;
		$document->setMetaData('title', JText::_('XBBOOKS_BOOK_CATALOGUE'));
		$metadesc = $this->params->get('menu-meta_description');
		if (!empty($metadesc)) { $document->setDescription($metadesc); }
		$metakey = $this->params->get('menu-meta_keywords');
		if (!empty($metakey)) { $document->setMetaData('keywords', $metakey);}
		$metarobots = $this->params->get('robots');
		if (!empty($metarobots)) { $document->setMetaData('robots', $metarobots);}
		$document->setMetaData('generator', $this->params->get('def_generator'));
		$metaauthor = $this->params->get('def_author');
		if (!empty($metaauthor)) { $document->setMetaData('author',$metaauthor);}
		
		parent::display($tpl);
	} // end function display()
		
}