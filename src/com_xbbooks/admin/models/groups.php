<?php
/*******
 * @package xbBooks
 * @filesource admin/models/groups.php
 * @version 1.0.4.0 3rd February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;

class XbbooksModelGroups extends JModelList {
	    
    public function __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id', 'title', 'a.title',
                'published', 'a.state', 'ordering', 'a.ordering',
            	'category_title', 'c.title','catid', 'a.catid', 'category_id',
            	'year_formed', 'year_disolved' );
        }
        
        parent::__construct($config);
    }
    
    protected function getListQuery() {
        $sess = Factory::getSession();
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('a.id AS id, a.title AS title,  a.alias AS alias,
			a.summary AS summary, a.picture AS picture, a.description AS description, a.ext_links AS ext_links,
			 a.year_formed AS year_formed, a.year_disolved AS year_disolved,
			a.catid AS catid, a.state AS published, a.created AS created, a.created_by AS created_by,
			a.created_by_alias AS created_by_alias, a.checked_out AS checked_out, a.checked_out_time AS checked_out_time,
            a.metadata AS metadata, a.ordering AS ordering, a.params AS params, a.note AS note');            
        $query->from($db->quoteName('#__xbgroups','a'));
        
        $query->select('(SELECT COUNT(DISTINCT(gp.person_id)) FROM #__xbgroupperson AS gp WHERE gp.group_id = a.id) AS pcnt');
        
        $query->select('(SELECT COUNT(DISTINCT(bg.book_id)) FROM #__xbbookgroup AS bg WHERE bg.group_id = a.id) AS bcnt');

        if ($sess->get('xbfilms_ok',false)==1) $query->select('(SELECT COUNT(DISTINCT(fg.film_id)) FROM #__xbfilmgroup AS fg WHERE fg.group_id = a.id) AS fcnt');
        if ($sess->get('xbevents_ok',false)==1) $query->select('(SELECT COUNT(DISTINCT(eg.event_id)) FROM #__xbeventgroup AS eg WHERE eg.group_id = a.id) AS ecnt');
        
        $query->join('LEFT',$db->quoteName('#__xbbookgroup', 'b') . ' ON ' . $db->quoteName('b.group_id') . ' = ' .$db->quoteName('a.id'));
        
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
            // Filter: like / search
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search, 'd:') === 0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('(summary LIKE ' . $search.' OR description LIKE '.$search.')');
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(title LIKE ' . $search.')');               
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
        $orderCol	= $this->state->get('list.ordering', 'title');
        $orderDirn 	= $this->state->get('list.direction', 'asc');
        if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
        	$orderCol = 'category_title '.$orderDirn.', a.ordering';
        }
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        $query->group('a.id');
        
        return $query;
    }
    
    public function getItems() {
        $sess = Factory::getSession();
        $items  = parent::getItems();
        // we are going to add the list of people (with roles) for each book
         $tagsHelper = new TagsHelper;
        
        foreach ($items as $i=>$item) { 
            if ($sess->get('xbfilms_ok',false)!=1) $item->fcnt = 0;
            if ($sess->get('xbevents_ok',false)!=1) $item->ecnt = 0;
            
            if ($item->pcnt>0) {
                $item->members = XbcultureHelper::getGroupMembers($item->id);
                $item->memberlist = XbcultureHelper::makeItemLists($item->members,'','tr',4,'ppvmodal');
            }
            $item->books = XbcultureHelper::getGroupBooks($item->id);
            $item->booklist = XbcultureHelper::makeItemLists($item->books,'','t',4, 'bpvmodal');
            
            $item->ext_links = json_decode($item->ext_links);
            $item->ext_links_list ='';
            $item->ext_links_cnt = 0;
            if(is_object($item->ext_links)) {
                $item->ext_links_cnt = count((array)$item->ext_links);
                foreach($item->ext_links as $lnk) {
                    $item->ext_links_list .= '<a href="'.$lnk->link_url.'" target="_blank">'.$lnk->link_text.'</a>, ';
                }
                $item->ext_links_list = trim($item->ext_links_list,', ');
            
	        } //end if is_object
	        $item->tags = $tagsHelper->getItemTags('com_xbpeople.group' , $item->id);
        } //end foreach item
	    return $items;
    }

}
