<?php
/*******
 * @package xbBooks
 * @filesource site/models/people.php
 * @version 0.9.9.9 6th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksModelPeople extends JModelList {

	protected $xbfilmsStatus;
	protected $prole;
	
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array ( 'firstname', 'lastname',
					'catid', 'a.catid', 'category_id', 'tagfilt',
					'category_title', 'c.title',
			    'a.nationality', 'nationality',
					'sortdate','bcnt' );
		}
		$this->xbfilmsStatus = Factory::getSession()->get('xbfilms_ok',false);
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		// Load the parameters.
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
		$limit = $this->getUserStateFromRequest($this->context.'.limit', 'limit', 25 );
		$this->setState('limit', $limit);
		$this->setState('list.limit', $limit);
		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', $app->get('start'));
		$this->setState('list.start', $limitstart);
		
	}
	
	protected function getListQuery() {
		$searchbar = (int)$this->getState('params',0)['search_bar'];
		//if menu option set it will take precedence and hide the corresponding filter option
		$prole = $this->getState('params')['menu_prole'];
		if (($searchbar==1) && ($prole==0)) { 	//look for filter setting
		    $prole = $this->getState('filter.prole');
		}
		$this->prole = $prole;		
		
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('DISTINCT(a.id) AS id, a.firstname AS firstname, a.lastname AS lastname, 
            a.summary AS summary, a.year_born AS year_born, a.year_died AS year_died, a.catid AS catid,
            a.portrait AS portrait, a.biography AS biography, a.state AS published,
            a.created AS created, a.created_by_alias AS created_by_alias,
            a.ordering AS ordering, a.params AS params, a.note AS note');
		$query->select('IF((year_born>-9999),year_born,year_died) AS sortdate');
//            ->select('(GROUP_CONCAT(p.person_id SEPARATOR '.$db->quote(',') .')) AS personlist');
		$query->from($db->quoteName('#__xbpersons','a'));
		
		if ($this->xbfilmsStatus) $query->select('(SELECT COUNT(DISTINCT(fp.film_id)) FROM #__xbfilmperson AS fp WHERE fp.person_id = a.id) AS fcnt');
		$query->select('(SELECT COUNT(DISTINCT(bp.book_id)) FROM #__xbbookperson AS bp WHERE bp.person_id = a.id) AS bcnt');
		
		//only get book people
		$query->join('INNER','#__xbbookperson AS bp ON bp.person_id = a.id');
		
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
        $query->select('c.title AS category_title');
           
        // Filter by published state, we only show published items in the front-end. Both item and its category must be published.
        $query->where('a.state = 1');
            //$query->where('c.published = 1'); 
            
        // Filter by search in title/id/synop
        $search = $this->getState('filter.search');
            
            if (!empty($search)) {
            	if (stripos($search,'s:')===0) {
            		$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
            		$query->where('(a.biography LIKE ' . $search.' OR a.summary LIKE '.$search.')');
            	} else {
            		$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            		$query->where('(a.firstname LIKE ' . $search . ' OR a.lastname LIKE ' . $search . ')');
            	}
            }
            
            //filter by nationality
            $natfilt = $this->getState('filter.nationality');
            if (!empty($natfilt)) {
                $query->where('a.nationality = '.$db->quote($natfilt));
            }
            
            // Filter by category and subcats
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
//            if ($this->getState('catid')>0) { $categoryId = $this->getState('catid'); }
            if ($categoryId > 0) {
            	if ($dosubcats) {
            		$catlist = $categoryId;
            		$subcatlist = XbcultureHelper::getChildCats($categoryId,'com_xbpeople');
            		if ($subcatlist) { $catlist .= ','.implode(',',$subcatlist);}
            		$query->where('a.catid IN ('.$catlist.')');
            	} else {
            		$query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
            	}
            }
            
        	switch ($prole) {
        		case 1: //all
         			break;
        		case 2: //authors
        			$query->where('bp.role = '. $db->quote('author'));
        			break;
        		case 3: //editors
         			$query->where('bp.role = '. $db->quote('editor'));
       			break;
        		case 4: //mention
        			$query->where('bp.role = '. $db->quote('mention'));
        			break;
        		case 5: //other
        		    $query->where('bp.role = '. $db->quote('other'));
        		    break;
        		default:
        			break;        			
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
 					WHERE type_alias LIKE '.$db->quote('com_xbpeople.person').')';
        	    if ($taglogic === '1') {
        	        $query->where('a.id NOT IN '.$subQuery);
        	    } elseif ($taglogic === '2') {
        	        $query->where('a.id IN '.$subQuery);
        	    }
        	} else {
        	    $tagfilt = ArrayHelper::toInteger($tagfilt);
        	    $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_xbpeople.person').'
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
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_xbpeople.person').')';
        	                    $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
        	                }
        	            }
        	            break;
        	    }
        	} //end if $tagfilt
        	
            // Add the list ordering clause.
            $orderCol       = $this->state->get('list.ordering', 'lastname');
            $orderDirn      = $this->state->get('list.direction', 'ASC');
            switch($orderCol) {
                case 'a.ordering' :
                case 'a.catid' :
                    //needs a menu option to set orderCol to ordering. Also menu option to alllow user to reorder on table
                    $query->order('category_title '.$orderDirn.', a.ordering');
                    break;
                case 'category_title':
                	$query->order('category_title '.$orderDirn.', lastname');
                	break;
                case 'bcnt':
                	$query->order('bcnt '.$orderDirn.', lastname');
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
		$peep = array();
		for ($i = 0; $i < count($items); $i++) {
			$peep[$i] = $items[$i]->id;
		}
		$app->setUserState('people.sortorder', $peep);
		$showcnts = $this->getState('params')['showcnts'];
		
		foreach ($items as $i=>$item) {
			$item->tags = $tagsHelper->getItemTags('com_xbpeople.person' , $item->id);
			
			$item->books = XbcultureHelper::getPersonBooks($item->id);
			$item->brolecnt = count($item->books);
			$item->booklist = $item->brolecnt==0 ? '' : XbcultureHelper::makeLinkedNameList($item->books,'','ul',true,3);
						
// 			$item->brolecnt = 0;
// 		    if ($item->bcnt > 0) {
// 		        $item->books = XbcultureHelper::getPersonBookRoles($item->id,'','title ASC', $showcnts);
// 		        $item->brolecnt = count($item->books);
// 		    } else {
// 		        $item->books = '';
// 		    }
			
		} //end foreach item
		return $items;
	}
		
}
            
            
