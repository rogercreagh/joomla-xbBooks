<?php
/*******
 * @package xbBooks
 * @filesource admin/models/books.php
 * @version 0.9.9.8 21st October 2022
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
            	'id', 'a.id',
            	'title', 'a.title',
        		'ordering','a.ordering',
        		'category_title', 'c.title',
        		'catid', 'a.catid', 'category_id',
        		'acq_date', 'a.acq_date',
                'a.first_read', 'last_read',
        		'published','a.state',
        		'pubyear','a.pubyear'
            );
        }
        parent::__construct($config);
    }
    
    //??? should there be a populateState function ???

    protected function getListQuery() {
	
    	$app = Factory::getApplication();
    	//		$user   = JFactory::getUser();
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
		$query->from('#__xbbooks AS a')
        //join to persons and characters to allow filtering on them
		->join('LEFT OUTER',$db->quoteName('#__xbbookperson', 'p') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('p.book_id'))
		->join('LEFT OUTER',$db->quoteName('#__xbbookcharacter', 'ch') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('ch.book_id'));
		
		$query->select('c.title AS category_title');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');
        
        $query->select('(SELECT COUNT(*) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS revcnt');

        $query->select('(SELECT AVG(br.rating) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS averat');
//        $query->select('(SELECT MAX(fr.rev_date) FROM #__xbbookreviews AS fr WHERE fr.book_id=a.id) AS lastread');
//        $query->select('GREATEST(a.acq_date, COALESCE(a.last_read, 0)) AS sort_date');
        
        
		// Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
                $query->where('state = ' . (int) $published);
        } else if ($published === '') {
                $query->where('(state IN (0, 1))');
        }

        // Filter by category.
        $categoryId = $app->getUserStateFromRequest('catid', 'catid','');
        $app->setUserState('catid', '');
//        $subcats=0;
        if ($categoryId=='') {
        	$categoryId = $this->getState('filter.category_id');
//        $subcats = $this->getState('filter.subcats');
        }
        if (is_numeric($categoryId)) {
//            if ($subcats) {
//                $query->where('a.catid IN ('.(int)$categoryId.','.self::getSubCategoriesList($categoryId).')');
//            } else {
                $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
//            }
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
        
        //filter by person (any role)
        $pfilt = $this->getState('filter.perfilt');
        if (is_numeric($pfilt)) {
        	$query->where('p.person_id = '.$db->quote($pfilt));
        	$ptype = $this->getState('filter.pertype');
        	if ($ptype != '') {
        		$query->where('p.role = '.$db->quote($ptype));
        	}
        }
        
        //filter by character 
        $chfilt = $this->getState('filter.charfilt');
        if (is_numeric($chfilt)) {
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
            $item->people = XbbooksGeneral::getBookRolesArray($item->id,'',true);
            $cnts = array_count_values(array_column($item->people, 'role'));
            $item->authcnt = (key_exists('author',$cnts))? $cnts['author'] : 0;
            $item->editcnt = (key_exists('editor',$cnts))? $cnts['editor'] : 0;
            $item->mencnt = (key_exists('mention',$cnts))? $cnts['mention'] : 0;
            $item->othcnt = (key_exists('other',$cnts))? $cnts['other'] : 0;
            
            $item->chars = XbbooksGeneral::getBookCharsArray($item->id);
            $item->charcnt = count($item->chars);
            
            $item->alist ='';
            if ($item->authcnt > 0) {
            	$item->alist = XbbooksGeneral::makeLinkedNameList($item->people,'author');
            }
            $item->elist = '';
            if ($item->editcnt > 0) {
            	$item->elist = XbbooksGeneral::makeLinkedNameList($item->people,'editor');
            }          
            $item->mlist = '';
            if (($item->mencnt)>0){
            	$item->mlist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->people,'mention',', ',false));
            }
            $item->olist = '';
            if (($item->othcnt)>0){
            	$item->olist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->people,'other',', ',false, true, 2));
            }
            $item->clist = '';
            if (($item->charcnt)>0){
            	$item->clist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->chars,'',', ',false));
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
        //reorder if ordering by review
        return $items;
    }
    
}

/****
 protected function populateState($ordering = 'a.ordering', $direction = 'asc') {
 $app = JFactory::getApplication();
 
 // Adjust the context to support modal layouts.
 //    	if ($layout = $app->input->get('layout'))
 //    	{
 //    		$this->context .= '.' . $layout;
 //    	}
 
 $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
 $this->setState('filter.search', $search);
 
 $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
 $this->setState('filter.published', $published);
 
 //    	$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
 //    	$this->setState('filter.level', $level);
 
 //    	$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
 //    	$this->setState('filter.language', $language);
 
 $formSubmited = $app->input->post->get('form_submited');
 
 //    	$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
 //    	$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
 $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
 $this->setState('filter.categoryId', $categoryId);
 
 $tagfilt = $this->getUserStateFromRequest($this->context . '.filter.tagfilt', 'filter_tagfilt', '');
 $this->setState('filter.tagfilt', $tagfilt);
 
 if ($formSubmited)
 {
 //   		$access = $app->input->post->get('access');
 //   		$this->setState('filter.access', $access);
 
 //    		$authorId = $app->input->post->get('author_id');
 //    		$this->setState('filter.author_id', $authorId);
 
 $categoryId = $app->input->post->get('category_id');
 $this->setState('filter.category_id', $categoryId);
 
 $tagfilt = $app->input->post->get('tagfilt');
 $this->setState('filter.tagfilt', $tagfilt);
 }
 
 // List state information.
 parent::populateState($ordering, $direction);
 
 }
 ***/
 
