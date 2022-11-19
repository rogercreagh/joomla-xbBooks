<?php
/*******
 * @package xbBooks
 * @filesource admin/models/reviews.php
 * @version 0.9.11.2 18th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksModelReviews extends JModelList {
    
    public function __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            		'id', 'a.id',
            		'title', 'booktitle',
            		'rev_date', 'rating',
            		'published', 'a.state',
            		'ordering', 'a.ordering',
            		'category_title', 'c.title',
            		'catid', 'a.catid', 'category_id');
        }
        
        parent::__construct($config);
    }
    
    protected function getListQuery() {
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        // Create the base select statement.
        $query->select('a.id AS id, a.title AS title, a.alias AS alias, a.summary AS summary, a.catid AS catid,
            a.review AS review, a.rating AS rating, a.state AS published, a.reviewer AS reviewer,
            a.created_by AS created_by, a.rev_date AS rev_date, a.note as note, a.ordering AS ordering,
            a.checked_out AS checked_out, a.checked_out_time AS checked_out_time')
            ->from($db->quoteName('#__xbbookreviews','a'));
                   
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
        // Join with books table to get the book title
        $query->select($db->quoteName('b.id','bookid').','.$db->quoteName('b.title', 'booktitle'))
            ->join('LEFT', $db->quoteName('#__xbbooks', 'b') . ' ON b.id = a.book_id');
        
        // Filter by search in title/id/summary/biog
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search,'s:')===0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('a.review' . ' LIKE ' . $search.' OR a.summary LIKE '.$search);
            } elseif (stripos($search,':')!= 1) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search .') OR (a.alias LIKE ' . $search .')');
            }
        }
            
        // Filter by published state
        $published = $this->getState('filter.published');        
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        }
        
        // Filter by category.
        $app = Factory::getApplication();
        $categoryId = $app->getUserStateFromRequest('catid', 'catid','');
        $app->setUserState('catid', '');
        if ($categoryId=='') {
        	$categoryId = $this->getState('filter.category_id');
        }
//        $subcats=0;
        if (is_numeric($categoryId))
        {
        	$query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
        }
        
        //Filter by rating
        $ratfilt = $this->getState('filter.ratfilt');
        if (is_numeric($ratfilt)) {
            $query->where('a.rating = '.$ratfilt);
        }
        
        //filter by tags
        $tagId = $app->getUserStateFromRequest('tagid', 'tagid','');
        $app->setUserState('tagid', '');
        if (!empty($tagId)) {
        	$tagfilt = array(abs($tagId));
        	$taglogic = $tagId>0 ? 0 : 2;
        } else {
        	$tagfilt = $this->getState('filter.tagfilt');
        	$taglogic = $this->getState('filter.taglogic');  //0=ANY 1=ALL 2= None
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
        $orderCol	= $this->state->get('list.ordering', 'rev_date');
        $orderDirn 	= $this->state->get('list.direction', 'desc');
        
        if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
            $orderCol = 'a.category_title '.$orderDirn.', a.ordering';  //TODO change this to category_title rather than id
        }
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
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
