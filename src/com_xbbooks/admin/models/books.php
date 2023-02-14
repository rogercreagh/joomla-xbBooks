<?php
/*******
 * @package xbBooks
 * @filesource admin/models/books.php
 * @version 1.0.4.0d 14th February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;


class XbbooksModelBooks extends JModelList
{

    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            	'id', 'a.id', 'title', 'a.title',
        		'ordering','a.ordering', 'category_title', 'c.title',
        		'catid', 'a.catid', 'category_id', 'tagfilt', 'taglogic',                
                'a.first_read', 'first_read', 'a.last_read', 'last_read',
                'published','a.state', 'created', 'a.created',
                'pubyear','a.pubyear', 'pcnt', 'ccnt', 'gcnt'
            );
        }
        parent::__construct($config);
    }
    
    protected function getListQuery() {
	
    	$app = Factory::getApplication();
		$db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('a.id AS id, a.title AS title, a.subtitle AS subtitle, a.alias AS alias, 
            a.summary AS summary, a.pubyear AS pubyear, a.catid AS catid, a.fiction AS fiction,
            a.cover_img AS cover_img, a.synopsis AS synopsis, a.state AS published, 
            a.created AS created, a.created_by AS created_by, a.first_read AS first_read, a.last_read AS last_read,
            a.created_by_alias AS created_by_alias, a.ext_links AS ext_links, 
            a.checked_out AS checked_out, a.checked_out_time AS checked_out_time, 
            a.metadata AS metadata, a.ordering AS ordering, a.params AS params, a.note AS note');
//            ->select('(GROUP_CONCAT(p.person_id SEPARATOR '.$db->quote(',') .')) AS personlist');
		$query->from('#__xbbooks AS a');
        //join to persons and characters to allow filtering on them
		
		$query->select('(SELECT COUNT(DISTINCT(bp.person_id)) FROM #__xbbookperson AS bp WHERE bp.book_id = a.id) AS pcnt');
		$query->select('(SELECT COUNT(DISTINCT(bg.group_id)) FROM #__xbbookgroup AS bg WHERE bg.book_id = a.id) AS gcnt');
		$query->select('(SELECT COUNT(DISTINCT(bc.char_id)) FROM #__xbbookcharacter AS bc WHERE bc.book_id = a.id) AS chcnt');
		
		$query->select('c.title AS category_title');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');
        
        $query->select('(SELECT COUNT(*) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS revcnt');
        $query->select('(SELECT AVG(br.rating) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS averat');        
        
		// Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
                $query->where('state = ' . (int) $published);
        }

        // Filter by category.
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
        
        // Filter by search in title/id/synop
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search,'s:')===0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('(a.synopsis LIKE ' . $search.' OR a.summary LIKE '.$search.')');
            } elseif (stripos($search,':')!= 1) {           	
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search . ' OR a.subtitle LIKE ' . $search . ')');
            }
        }
        
        //filter by fiction/non-fiction
        $ffilt = $this->getState('filter.fictfilt');
        if (is_numeric($ffilt)) {
        	$query->where('a.fiction = '.$db->quote($ffilt));
        }
        
        //filter by person (optional specify role)
        $pfilt = $this->getState('filter.perfilt');
        if (is_numeric($pfilt)) {
            $query->join('LEFT OUTER',$db->quoteName('#__xbbookperson', 'p') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('p.book_id'));
            $query->where('p.person_id = '.$db->quote($pfilt));
        	$ptype = $this->getState('filter.pertype');
        	if ($ptype != '') {
        		$query->where('p.role = '.$db->quote($ptype));
        	}
        }
        
        //filter by group
        $gfilt = $this->getState('filter.groupfilt');
        if (is_numeric($gfilt)) {
            $query->join('LEFT OUTER',$db->quoteName('#__xbbookgroup', 'g') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('g.book_id'));
            $query->where('ch.char_id = '.$db->quote($chfilt));
        }
        
        //filter by character
        $chfilt = $this->getState('filter.charfilt');
        if (is_numeric($chfilt)) {
            $query->join('LEFT OUTER',$db->quoteName('#__xbbookcharacter', 'ch') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('ch.book_id'));
            $query->where('ch.char_id = '.$db->quote($chfilt));
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
 					WHERE type_alias LIKE '.$db->quote('com_xbbooks.book').')';
            if ($taglogic === '1') {
                $query->where('a.id NOT IN '.$subQuery);
            } elseif ($taglogic === '2') {
                $query->where('a.id IN '.$subQuery);
            }
        } else {
            $tagfilt = ArrayHelper::toInteger($tagfilt);
            $subquery = '(SELECT tmap.tag_id AS tlist FROM #__contentitem_tag_map AS tmap
                WHERE tmap.type_alias = '.$db->quote('com_xbbooks.book').'
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
                                WHERE tag_id IN ('.$tagIds.') AND type_alias = '.$db->quote('com_xbbooks.book').')';
                            $query->innerJoin('(' . (string) $subQueryAny . ') AS tagmap ON tagmap.content_item_id = a.id');
                        }
                    }
                    break;
            }
        } //end if $tagfilt
        
        // Add the list ordering clause.
        $orderCol       = $this->state->get('list.ordering', 'last_read');
        $orderDirn      = $this->state->get('list.direction', 'DESC');
        if (($orderCol == 'last_read') || ($orderCol == 'first_read')) {
            $query->order('CASE WHEN '.$orderCol.' IS NULL THEN 1 ELSE 0 END, '.$orderCol.' '.$orderDirn);
        } else {
            if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
                $orderCol = 'category_title '.$orderDirn.', a.ordering';
            }
            $query->order($db->escape($orderCol.' '.$orderDirn.', title'));
        }
        $query->group('a.id');
        return $query;
    }

	public function getItems() {
        $items  = parent::getItems();
		$tagsHelper = new TagsHelper;
		
        foreach ($items as $i=>$item) {  
            $item->people = XbbooksGeneral::getBookPeople($item->id,'');
            
            $roles = array_column($item->people,'role');
            $item->authcnt = count(array_keys($roles, 'author'));
            $item->editcnt = count(array_keys($roles, 'editor'));
            $item->mencnt = count(array_keys($roles, 'mention'));
            $item->othercnt = count($item->people) - $item->authcnt - $item->editcnt - $item->mencnt;
            
            $item->authlist = $item->authcnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'author','t',4,'ppvmodal');
            $item->editlist = $item->editcnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'editor','t',3,'ppvmodal');
            $item->menlist = $item->mencnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'mention','tn',3,'ppvmodal');
            $item->otherlist = $item->othercnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'other','rt',3,'ppvmodal');
            
            if ($item->gcnt > 0) {
                $item->groups = XbbooksGeneral::getBookGroups($item->id);
                $item->grplist = $item->gcnt==0 ? '' : XbcultureHelper::makeItemLists($item->groups,'','t',3,'gpvmodal');
            }
            
            if ($item->chcnt > 0) {
                $item->chars = XbbooksGeneral::getBookChars($item->id);
                $item->charlist = $item->chcnt==0 ? '' : XbcultureHelper::makeItemLists($item->chars,'char','t',3,'cpvmodal');
            }
            
            $item->reviews = XbbooksGeneral::getBookReviews($item->id);
        	
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
            $item->tags = $tagsHelper->getItemTags('com_xbbooks.book' , $item->id);
            
        } //foreach item
        return $items;
    }
    
}
 
