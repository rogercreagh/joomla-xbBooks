<?php
/*******
 * @package xbBooks
 * @filesource site/models/groups.php
 * @version 1.0.4.0 5th February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2022
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Categories;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksModelGroups extends JModelList {
    
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array ( 'title', 
					'catid', 'a.catid', 'category_id', 'tagfilt',
			     'year_formed', 'year_disolved',
					'category_title', 'c.title',
					'sortdate','fcnt','bcnt' );
		}
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
		$sess = Factory::getSession();
		$searchbar = (int)$this->getState('params',0)['search_bar'];
		
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('a.id AS id, a.title AS title, a.catid AS catid,
            a.summary AS summary, a.year_formed AS year_formed, a.year_disolved AS year_disolved,            
            a.picture AS picture, a.description AS description, a.state AS published,
            a.created AS created, a.created_by_alias AS created_by_alias,
            a.ordering AS ordering, a.params AS params, a.note AS note');
 		$query->from($db->quoteName('#__xbgroups','a'));
 		
 		
 		$query->select('(SELECT COUNT(DISTINCT(gp.person_id)) FROM #__xbgroupperson AS gp JOIN #__xbpersons AS p ON gp.person_id = p.id  WHERE gp.group_id = a.id AND p.state=1) AS pcnt');
 		
 		$query->select('(SELECT COUNT(DISTINCT(bg.book_id)) FROM #__xbbookgroup AS bg JOIN #__xbbooks AS b ON bg.book_id = b.id WHERE bg.group_id = a.id AND b.state=1) AS bcnt');
 		if ($sess->get('xbfilms_ok',false)==1) {
 		    $query->select('(SELECT COUNT(DISTINCT(fg.film_id)) FROM #__xbfilmgroup AS fg JOIN #__xbfilms AS f ON fg.film_id = f.id WHERE fg.group_id = a.id AND f.state=1) AS fcnt');
 		} else {
 		    $query->select('0 AS fcnt');
 		}
 		if ($sess->get('xbevents_ok',false)==1) {
 		    $query->select('(SELECT COUNT(DISTINCT(eg.event_id)) FROM #__xbeventgroup AS eg JOIN #__xbevents AS e ON eg.event_id = e.id WHERE eg.group_id = a.id AND e.state=1) AS ecnt');
 		} else {
 		    $query->select('0 AS ecnt');
 		}
 		$query->having('bcnt > 0');
 		
 		
 		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
        $query->select('c.title AS category_title');
           
        // Filter by published state, we only show published items in the front-end. Both item and its category must be published.
        $query->where('a.state = 1');
            //$query->where('c.published = 1'); 
            
        // Filter by search in title/id/synop
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if ((stripos($search,'d:')===0) || (stripos($search,'s:')===0)) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('(a.description LIKE ' . $search.' OR a.summary LIKE '.$search.')');
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search .')');
            }
        }
        
            // Filter by category and subcats
            $categoryId = $this->getState('categoryId');
            $this->setState('categoryId','');
            if (empty($categoryId)) {
                $categoryId = $this->getState('params',0,'int')['menu_category_id'];
            }
            if (($searchbar==1) && ($categoryId==0)){
            	$categoryId = $this->getState('filter.category_id');
            }
            if (is_numeric($categoryId))
            {
                $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
            } elseif (is_array($categoryId)) {
                $categoryId = implode(',', $categoryId);
                $query->where($db->quoteName('a.catid') . ' IN ('.$categoryId.')');
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
 					WHERE type_alias LIKE '.$db->quote('com_xbpeople.group').')';
                if ($taglogic === '1') {
                    $query->where('a.id NOT IN '.$subQuery);
                } elseif ($taglogic === '2') {
                    $query->where('a.id IN '.$subQuery);
                }
            } else {
                $tagfilt = ArrayHelper::toInteger($tagfilt);
                $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_xbpeople.group').'
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
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_xbpeople.group').')';
                                $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
                            }
                        }
                        break;
                }
            } //end if $tagfilt
            
            // Add the list ordering clause.
            $orderCol       = $this->state->get('list.ordering', 'title');
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
		$grps = array();
		for ($i = 0; $i < count($items); $i++) {
		    $grps[$i] = $items[$i]->id;
		}
		$app->setUserState('groups.sortorder', $grps);
		
	    $db    = Factory::getDbo();
		foreach ($items as $i=>$item) {
			$item->tags = $tagsHelper->getItemTags('com_xbpeople.group' , $item->id);
	
			if ($item->bcnt>0) {
			    $item->books = XbcultureHelper::getGroupBooks($item->id);
			    $item->booklist = XbcultureHelper::makeItemLists($item->books,'','tr',3,'bpvmodal');
			}
			if ($item->pcnt>0) {
			    $item->members = XbcultureHelper::getGroupMembers($item->id);
			    $item->memberlist = XbcultureHelper::makeItemLists($item->members,'','tr',3,'gpvmodal');
			}
			
			
		} //end foreach item
		return $items;
	}
		
}
            
            
