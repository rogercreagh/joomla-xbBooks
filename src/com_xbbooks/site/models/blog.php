<?php
/*******
 * @package xbBooks
 * @filesource site/models/blog.php
 * @version 0.9.9.8 21st October 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

//jimport('joomla.application.component.modellist');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksModelBlog extends JModelList {
	
	public function __construct($config = array()) {
	    $showrevs = ComponentHelper::getParams('com_xbbooks')->get('show_revs',1);
	    if (!$showrevs) {
	        header('Location: index.php?option=com_xbbooks&view=booklist');
	        exit();	        
	    }
	    
	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'ordering','a.ordering',
					'category_title', 'c.title',
					'catid', 'a.catid', 'category_id', 'tagfilt',
					'bcatid', 'b.catid', 'bcategory_id',
					'acq_date', 'a.acq_date',
					'published','a.state',
					'rel_year','a.rel_year');
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		// Load the parameters.
		$app = Factory::getApplication();
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$categoryId = $app->getUserStateFromRequest('catid', 'catid','');
		$app->setUserState('catid', '');
		$this->setState('categoryId',$categoryId);
		$tagId = $app->getUserStateFromRequest('tagid', 'tagid','');
		$app->setUserState('tagid', '');
		$this->setState('tagId',$tagId);
		
		parent::populateState($ordering, $direction);
		
		//pagination limit
		$limit = $this->getUserStateFromRequest($this->context.'.limit', 'limit', 5 );
		$this->setState('limit', $limit);
		$this->setState('list.limit', $limit);
		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', $app->get('start'));
		$this->setState('list.start', $limitstart);
		
	}
	
	protected function getListQuery() {
		
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('a.id AS id, a.title AS title, a.alias AS alias,
            a.summary AS summary, a.review AS review, a.catid AS catid,
            a.rating AS rating, a.book_id AS book_id, a.state AS published,
            a.rev_date AS rev_date, a.reviewer AS reviewer,
            a.ordering AS ordering, a.params AS params');
		$query->from('#__xbbookreviews AS a');
		$query->select('(SELECT AVG(br.rating) FROM #__xbbookreviews AS br WHERE br.book_id=a.book_id) AS averat');
		$query->select('(SELECT COUNT(fr.rating) FROM #__xbbookreviews AS fr WHERE fr.book_id=a.book_id) AS ratcnt');
		
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
		$query->join('LEFT', '#__xbbooks AS b ON b.id = a.book_id');
		$query->select('b.title AS book_title, b.subtitle AS subtitle, b.cover_img AS cover_img,b.summary AS book_summary, 
			b.synopsis AS synopsis, b.pubyear AS pubyear, b.orig_lang AS orig_lang');
		$query->join('LEFT', '#__categories AS fc ON fc.id = b.catid');
		$query->select('fc.title AS bcat_title');
		
		// Filter by published state 
		// 	`category and book must both be published state=1 as well 
		$query->where('a.state = 1');
		$query->where('c.published = 1');
		$query->where('b.state = 1');
		
		// Filter by search in title/sum/rev
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search,'s:')===0) {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
				$query->where('(a.review LIKE ' . $search.' OR a.summary LIKE '.$search.')');
			} else {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.title LIKE ' . $search );
			}
		}
		
		$searchbar = (int)$this->getState('params',0)['search_bar'];
		//if a menu filter is set this takes priority and serch filter field is hidden
 
		// Filter by review category.
		$categoryId = $this->getState('categoryId');
		$this->setState('categoryId','');
		$dosubcats = 0;
		if (empty($categoryId)) {
			$categoryId = $this->getState('params',0,'int')['menu_category_id'];
			$dosubcats=$this->getState('params',0)['menu_subcats'];
		}
		if (($searchbar==1) && ($categoryId==0)){
			$categoryId = $this->getState('filter.category_id');
			$dosubcats=$this->getState('filter.subcats');
		}
		if ($categoryId > 0) {
			if ($dosubcats) {
				$catlist = $categoryId;
				$subcatlist = XbcultureHelper::getChildCats($categoryId,'com_xbbooks');
				if ($subcatlist) { $catlist .= ','.implode(',',$subcatlist);}
				$query->where('a.catid IN ('.$catlist.')');
			} else {
				$query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
			}
		}
		
		// Filter by book category.
		$bcategoryId = $this->getState('bcategoryId');
		$this->setState('bcategoryId','');
		$dosubcats = 0;
		if (empty($bcategoryId)) {
			$bcategoryId = $this->getState('params',0,'int')['menu_bcategory_id'];
			$dosubcats=$this->getState('params',0)['menu_subcats'];
		}
		if (($searchbar==1) && ($bcategoryId==0)){
			$bcategoryId = $this->getState('filter.bcategory_id');
			$dosubcats=$this->getState('filter.subcats');
		}
		if ($bcategoryId > 0) {
			if ($dosubcats) {
				$catlist = $bcategoryId;
				$subcatlist = XbcultureHelper::getChildCats($bcategoryId,'com_xbbooks');
				if ($subcatlist) { $catlist .= ','.implode(',',$subcatlist);}
				$query->where('b.catid IN ('.$catlist.')');
			} else {
				$query->where($db->quoteName('b.catid') . ' = ' . (int) $bcategoryId);
			}
		}
		
		//filter by tag
		$tagfilt = $this->getState('tagId');
		// $this->setState('tagId','');
		$taglogic = 0;
		if (empty($tagfilt)) {
		    $tagfilt = $this->getState('params')['menu_tag'];
		    $taglogic = $this->getState('params')['menu_taglogic']; //1=AND otherwise OR
		}
		
		if (($searchbar==1) && (empty($tagfilt))) { 	//look for menu options
		    //look for filter options and ignore menu options
		    $tagfilt = $this->getState('filter.tagfilt');
		    $taglogic = $this->getState('filter.taglogic'); //1=AND otherwise OR
		}
		
		if (empty($tagfilt)) {
		    $subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
                WHERE type_alias IN ('.$db->quote('com_xbbooks.book').','.$db->quote('com_xbbooks.review').')';
		    if ($taglogic === '1') {
		        $query->where('a.id NOT IN '.$subQuery);
		    } elseif ($taglogic === '2') {
		        $query->where('a.id IN '.$subQuery);
		    }
		} else {
		    $tagfilt = ArrayHelper::toInteger($tagfilt);
		    $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
            WHERE tmap.type_alias IN ('.$db->quote('com_xbbooks.book').','.$db->quote('com_xbbooks.review').')
            AND tmap.content_item_id = a.id)';
		    switch ($taglogic) {
		        case 1: //all
		            for ($i = 0; $i < count($tagfilt); $i++) {
		                $query->where($tagfilt[$i].' IN '.$subquery);
		            }
		            break;
		        case 2: //none
		            for ($i = 0; $i < count($tagfilt); $i++) {
		                $query->where($tagfilt[$i].' NOT IN '.$subquery);
		            }
		            break;
		        default: //any
		            if (count($tagfilt)==1) {
		                $query->where($tagfilt[0].' IN '.$subquery);
		            } else {
		                $tagIds = implode(',', $tagfilt);
		                if ($tagIds) {
		                    $subQueryAny = '(SELECT DISTINCT content_item_id FROM #__contentitem_tag_map
                                WHERE tag_id IN ('.$tagIds.') AND type_alias IN ('.$db->quote('com_xbbooks.book').','.$db->quote('com_xbbooks.review').')';
		                    $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
		                }
		            }
		            break;
		    }
		} //end if $tagfilt
		
		//filter by rating
		$ratfilt = $this->getState('filter.ratfilt');
		if (!empty($ratfilt)) {
			$query->where('a.rating = '.$db->quote($ratfilt));
		}
		
		//filter by review date
		$yearfilt = $this->getState('filter.rev_year');
		if ($yearfilt != '') {
			$query->where('YEAR(rev_date) = '.$db->quote($yearfilt));
			$monthfilt = $this->getState('filter.rev_month');
			if ($monthfilt != '') {
				$query->where('MONTH(rev_date) = '.$db->quote($monthfilt));
			}
		}
		
		
		// Add the list ordering clause.
		$orderCol       = $this->state->get('list.ordering', 'rev_date');
		$orderDirn      = $this->state->get('list.direction', 'DESC');
		switch($orderCol) {
			case 'a.ordering' :
			case 'a.catid' :
				//needs a menu option to set orderCol to ordering. Also menu option to alllow user to reorder on table
				$query->order('category_title '.$orderDirn.', a.ordering');
				break;
			case 'category_title':
				$query->order('category_title '.$orderDirn.', title');
				break;
			default:
				$query->order($db->escape($orderCol.' '.$orderDirn));
				break;
		}
		
		$query->group('a.id');
		return $query;		
		
	}
	
	public function getItems() {
		$items  = parent::getItems();
		$tagsHelper = new TagsHelper;		
		
		$app = Factory::getApplication();
		$bks = array();
		for ($i = 0; $i < count($items); $i++) {
			$bks[$i] = $items[$i]->id;
		}
		$app->setUserState('bookreviews.sortorder', $bks);

		foreach ($items as $i=>$item) {			
			$auts = XbbooksGeneral::getBookRolesArray($item->book_id,'author');
			$item->acnt = count($auts);
			if ($item->acnt==0){
				$item->alist = ''; 
			} else {
				$item->alist = XbbooksGeneral::makeLinkedNameList($auts,'author',', ',true, false);
			}
			$item->tags = $tagsHelper->getItemTags('com_xbbooks.review' , $item->id);	
			
			//get bookauthors) or editor
		} //foreach item
		
		return $items;		
	}
}
