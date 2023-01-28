<?php
/*******
 * @package xbBooks
 * @filesource site/models/bookreviews.php
 * @version 1.0.3.9 28th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2023
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksModelBookreviews extends JModelList {
    
    public function __construct($config = array()) {
        $showrevs = ComponentHelper::getParams('com_xbbooks')->get('show_revs',1);
        if (!$showrevs) {
            header('Location: index.php?option=com_xbbooks&view=booklist');
            exit();
        }
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            		'id', 'a.id',
            		'title', 'booktitle',
            		'rev_date', 'rating',           		
            		'category_title', 'c.title',
            		'catid', 'a.catid', 'category_id');
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
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        // Create the base select statement.
        $query->select('a.id AS id, a.title AS title, a.alias AS alias, a.summary AS summary, a.catid AS catid,
            a.review AS review, a.rating AS rating, a.state AS published, a.reviewer AS reviewer,
            a.created_by AS created_by, a.rev_date AS rev_date, a.note as note, a.ordering AS ordering,
            a.params AS params,
            a.checked_out AS checked_out, a.checked_out_time AS checked_out_time, a.created AS created')
            ->from($db->quoteName('#__xbbookreviews','a'));
                   
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
        // Join with books table to get the book title
            $query->select($db->quoteName('b.id','bookid').','.$db->quoteName('b.title', 'booktitle').','.$db->quoteName('b.cover_img', 'bookcover'))
            ->join('LEFT', $db->quoteName('#__xbbooks', 'b') . ' ON b.id = a.book_id');
        
        // Filter by search in title/id/summary/biog
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search,'s:')===0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('a.review' . ' LIKE ' . $search.' OR a.summary LIKE '.$search);
            } elseif (stripos($search,'b:')===0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('b.title' . ' LIKE ' . $search);
            } elseif (stripos($search,':')!= 1) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search .') OR (a.alias LIKE ' . $search .')');
            }
        }
            
        // Filter by published state
            $query->where('a.state = 1 ');
        
            $searchbar = (int)$this->getState('params')['search_bar'];
            //if a menu filter is set this takes priority and serch filter field is hidden
            
            // Filter by category
            $categoryId = $this->getState('categoryId');
            $this->setState('categoryId','');
            if (empty($categoryId)) {
                $categoryId = $this->getState('params')['menu_category_id'];
            }
            if (($searchbar==1) && ($categoryId==0)){
                $categoryId = $this->getState('filter.category_id');
            }
            if ((is_numeric($categoryId)) && ($categoryId > 0) ){
                $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
            } elseif (is_array($categoryId)) {
                $catlist = implode(',', $categoryId);
                $query->where($db->quoteName('a.catid') . ' IN ('.$catlist.')');
            }
            
        //Filter by rating
        $ratfilt = $this->getState('filter.ratfilt');
        if (is_numeric($ratfilt)) {
            $query->where('a.rating = '.$ratfilt);
        }
        
        //filter by tags
        $tagfilt = $this->getState('tagId');
        // $this->setState('tagId','');
        $taglogic = 0;
        if (empty($tagfilt)) { //look for menu options
            $tagfilt = $this->getState('params')['menu_tag'];
            $taglogic = $this->getState('params')['menu_taglogic']; //1=AND otherwise OR
        }
        if ((!is_array($tagfilt)) && (!empty($tagfilt))) {
            $tagfilt = array($tagfilt);
        }
        
        if (($searchbar==1) && (empty($tagfilt))) {
            //look for filter options and ignore menu options
            $tagfilt = $this->getState('filter.tagfilt');
            $taglogic = $this->getState('filter.taglogic'); //1=AND otherwise OR
        }
        
        if (empty($tagfilt)) {
            $subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
 					WHERE type_alias LIKE '.$db->quote('com_xbbooks.review').')';
            if ($taglogic === '1') {
                $query->where('a.id NOT IN '.$subQuery);
            } elseif ($taglogic === '2') {
                $query->where('a.id IN '.$subQuery);
            }
        } else {
            $tagfilt = ArrayHelper::toInteger($tagfilt);
            $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_xbbooks.review').'
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
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_xbbooks.review').')';
                            $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
                        }
                    }
                    break;
            }
        } //end if $tagfilt
        
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
        foreach ($items as $i=>$item) {
        	$item->tags = $tagsHelper->getItemTags('com_xbbooks.review' , $item->id);            
        } 
        return $items;
    }
}
