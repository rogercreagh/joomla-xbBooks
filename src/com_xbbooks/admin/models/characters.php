<?php
/*******
 * @package xbBooks
 * @filesource admin/models/characters.php
 * @version 1.0.4.0e 17th February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;

class XbbooksModelCharacters extends JModelList {

    public function __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            		'id', 'a.id',
            		'name', 'a.name',
            		'ordering','a.ordering',
            		'category_title', 'c.title',
                'catid', 'a.catid', 'category_id', 'tagfilt', 'taglogic',
            		'published','a.state'	
            );
        }
        
        parent::__construct($config);
    }
    
    protected function getListQuery() {
        $sess = Factory::getSession();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        // Create the base select statement.
        $query->select('a.id AS id, a.name AS name, a.alias AS alias, 
			a.summary AS summary, a.image AS image, a.description AS description, 
			a.catid AS catid, a.state AS published, a.created AS created, a.created_by AS created_by, 
			a.created_by_alias AS created_by_alias, a.checked_out AS checked_out, a.checked_out_time AS checked_out_time, 
            a.metadata AS metadata, a.ordering AS ordering, a.params AS params, a.note AS note')

            ->from($db->quoteName('#__xbcharacters','a'));
        
        $query->select('(SELECT COUNT(DISTINCT(bp.book_id)) FROM #__xbbookcharacter AS bp WHERE bp.char_id = a.id) AS bcnt');
        if ($sess->get('xbfilms_ok',false)==1) $query->select('(SELECT COUNT(DISTINCT(fp.film_id)) FROM #__xbfilmcharacter AS fp WHERE fp.char_id = a.id) AS fcnt');
        if ($sess->get('xbevents_ok',false)==1) $query->select('(SELECT COUNT(DISTINCT(ep.event_id)) FROM #__xbeventcharacter AS ep WHERE ep.char_id = a.id) AS ecnt');
        //        $query->select('(GROUP_CONCAT(b.film_id SEPARATOR '.$db->quote(',') .')) AS filmlist');
        $query->join('LEFT OUTER',$db->quoteName('#__xbbookcharacter', 'b') . ' ON ' . $db->quoteName('b.char_id') . ' = ' .$db->quoteName('a.id'));
                    
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
            // Filter: like / search
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search, 'b:') === 0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('(summary LIKE ' . $search.' OR description LIKE '.$search.')');
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(name LIKE ' . $search.')');               
            }
        }
        
        // Filter by published state
        $published = $this->getState('filter.published');        
        if (is_numeric($published)) {
            $query->where('state = ' . (int) $published);
        }
        
        // Filter by category.
        $app = Factory::getApplication();
        $categoryId = $app->getUserStateFromRequest('catid', 'catid','');
        $app->setUserState('catid', '');
        if ($categoryId=='') {
        	$categoryId = $this->getState('filter.category_id');
        }
        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
        } elseif (is_array($categoryId)) {
            $categoryId = implode(',', $categoryId);
            $query->where($db->quoteName('a.catid') . ' IN ('.$categoryId.')');
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
 					WHERE type_alias LIKE '.$db->quote('com_xbpeople.character').')';
            if ($taglogic === '1') {
                $query->where('a.id NOT IN '.$subQuery);
            } elseif ($taglogic === '2') {
                $query->where('a.id IN '.$subQuery);
            }
        } else {
            $tagfilt = ArrayHelper::toInteger($tagfilt);
            $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_xbpeople.character').'
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
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_xbpeople.character').')';
                            $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
                        }
                    }
                    break;
            }
        } //end if $tagfilt
        
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'name');
        $orderDirn 	= $this->state->get('list.direction', 'asc');
        
        if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
        	$orderCol = 'a.category_title '.$orderDirn.', a.ordering';  
        }
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        if ($orderCol != 'name') {
	        $query->order('name ASC');
        }
        $query->group('a.id');
        
        return $query;
    }
    
    public function getItems() {
        $sess = Factory::getSession();
        $items  = parent::getItems();
        // we are going to add the list of characters for each book
        $tagsHelper = new TagsHelper;
        foreach ($items as $i=>$item) { 
            if ($sess->get('xbfilms_ok',false)!=1) $item->fcnt = 0;
            if ($sess->get('xbevents_ok',false)!=1) $item->ecnt = 0;
            $item->bookcnt = 0;
            $item->booklist='';
            if ($item->bcnt>0) {
                $item->books = XbcultureHelper::getCharBooks($item->id);
                $item->bookcnt = count($item->books);
                $item->booklist = $item->bookcnt==0 ? '' : XbcultureHelper::makeItemLists($item->books,'','t',4,'book');
            } //bcnt is the number of books, bookcnt is the number of roles (maybe 2 roles in a book)
            
	        $item->tags = $tagsHelper->getItemTags('com_xbpeople.character' , $item->id);
        } //end foreach item
	    return $items;
    }
    
}