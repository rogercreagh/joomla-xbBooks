<?php
/*******
 * @package xbBooks
 * @filesource admin/models/persons.php
 * @version 0.9.4 17th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;

class XbbooksModelPersons extends JModelList {
	
	protected $xbfilmsStatus;
	
    public function __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            		'id', 'a,id',
            		'firstname', 'lastname',
            		'published', 'a.state',
            		'ordering', 'a.ordering',
            		'category_title', 'c.title',
            		'catid', 'a.catid', 'category_id',
            		'sortdate' );
        }
        
        $this->xbfilmsStatus = Factory::getSession()->get('com_xbfilms',false);
        parent::__construct($config);
    }
    
    protected function getListQuery() {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('a.id AS id, a.firstname AS firstname, a.lastname AS lastname, a.alias AS alias, 
			a.summary AS summary, a.portrait AS portrait, a.biography AS biography, a.ext_links AS ext_links,
			a.nationality AS nationality, a.year_born AS year_born, a.year_died AS year_died,
			a.catid AS catid, a.state AS published, a.created AS created, a.created_by AS created_by, 
			a.created_by_alias AS created_by_alias, a.checked_out AS checked_out, a.checked_out_time AS checked_out_time, 
            a.metadata AS metadata, a.ordering AS ordering, a.params AS params, a.note AS note');
        $query->select('IF((year_born>-9999),year_born,year_died) AS sortdate');
            
        $query->from($db->quoteName('#__xbpersons','a'));
        
        $query->select('(GROUP_CONCAT(b.book_id SEPARATOR '.$db->quote(',') .')) AS booklist');
        $query->join('LEFT',$db->quoteName('#__xbbookperson', 'b') . ' ON ' .$db->quoteName('b.person_id') . ' = ' . $db->quoteName('a.id'));
        
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');
            
            // Filter: like / search
        $search = $this->getState('filter.search');
        
        if (!empty($search)) {
            if (stripos($search, 'i:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 2));
            } elseif (stripos($search, 'b:') === 0) {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim(substr($search,2)), true) . '%'));
                $query->where('(summary LIKE ' . $search.' OR biography LIKE '.$search.')');
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(lastname LIKE ' . $search.' OR firstname LIKE '.$search.')');               
            }
        }
        
        // Filter by published state
        $published = $this->getState('filter.published');
        
        if (is_numeric($published)) {
            $query->where('state = ' . (int) $published);
//        } elseif ($published === '') {
//            $query->where('(state IN (0, 1))');
        }
        
        //Filter by role
        $rolefilt = $this->getState('filter.rolefilt');
        if (empty($rolefilt)) { $rolefilt = 'book'; }
        if ($rolefilt != 'all') {
        	if ($rolefilt == 'book') {
        		$query->where('b.id IS NOT NULL');
        	} elseif ($rolefilt == 'notbook') {
        		$query->where('b.id IS NULL');
        	} elseif ($rolefilt == 'orphans') {
        		if ($this->xbfilmsStatus) {
        			$query->join('LEFT OUTER',$db->quoteName('#__xbfilmperson', 'f') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('f.person_id'));
        			$query->where('f.id IS NULL');
        		}
        		$query->where('b.id IS NULL');
        	} else {
        		$query->where('b.role = '.$db->quote($rolefilt));
        	}
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
        
        if (($taglogic === '2') && (empty($tagfilt))) {
        	//if if we select tagged=excl and no tags specified then only show untagged items
        	$subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
 					WHERE type_alias LIKE '.$db->quote('com_xb%.person').')';
        	$query->where('a.id NOT IN '.$subQuery);
        }
        
        if (!empty($tagfilt)) {
        	$tagfilt = ArrayHelper::toInteger($tagfilt);
        	
        	if ($taglogic==2) { //exclude anything with a listed tag
        		// subquery to get a virtual table of item ids to exclude
        		$subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
					WHERE type_alias LIKE '.$db->quote('com_xb%.person').
					' AND tag_id IN ('.implode(',',$tagfilt).'))';
        		$query->where('a.id NOT IN '.$subQuery);
        	} else {
        		if (count($tagfilt)==1)	{ //simple version for only one tag
        			$query->join( 'INNER', $db->quoteName('#__contentitem_tag_map', 'tagmap')
        					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id') )
        					->where(array( $db->quoteName('tagmap.tag_id') . ' = ' . $tagfilt[0],
        							$db->quoteName('tagmap.type_alias') . ' LIKE ' . $db->quote('com_xb%.person') )
        							);
        		} else { //more than one tag
        			if ($taglogic == 1) { // match ALL listed tags
        				// iterate through the list adding a match condition for each
        				for ($i = 0; $i < count($tagfilt); $i++) {
        					$mapname = 'tagmap'.$i;
        					$query->join( 'INNER', $db->quoteName('#__contentitem_tag_map', $mapname).
        							' ON ' . $db->quoteName($mapname.'.content_item_id') . ' = ' . $db->quoteName('a.id'));
        					$query->where( array(
        							$db->quoteName($mapname.'.tag_id') . ' = ' . $tagfilt[$i],
        							$db->quoteName($mapname.'.type_alias') . ' LIKE ' . $db->quote('com_xb%.person'))
        							);
        				}
        			} else { // match ANY listed tag
        				// make a subquery to get a virtual table to join on
        				$subQuery = $db->getQuery(true)
        				->select('DISTINCT ' . $db->quoteName('content_item_id'))
        				->from($db->quoteName('#__contentitem_tag_map'))
        				->where( array(
        						$db->quoteName('tag_id') . ' IN (' . implode(',', $tagfilt) . ')',
        						$db->quoteName('type_alias') . ' LIKE ' . $db->quote('com_xb%.person'))
        						);
        				$query->join(
        						'INNER',
        						'(' . $subQuery . ') AS ' . $db->quoteName('tagmap')
        						. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
        						);
        				
        			} //endif all/any
        		} //endif one/many tag
        	}
        } //if not empty tagfilt
        
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'lastname');
        $orderDirn 	= $this->state->get('list.direction', 'asc');
        if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
        	$orderCol = 'category_title '.$orderDirn.', a.ordering';
        }
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        if ($orderCol != 'lastname') {
	        $query->order('lastname ASC');
        }
        
        $query->group('a.id');
        
        return $query;
    }
    
    public function getItems() {
        $items  = parent::getItems();
        // we are going to add the list of people (with roles) for teach book
        //and apply any book title filter
        $tagsHelper = new TagsHelper;
        
        foreach ($items as $i=>$item) { 
            $item->books = XbbooksGeneral::getPersonRoleArray($item->id,'',true);
            $cnts = array_count_values(array_column($item->books, 'role'));
            $item->acnt = (key_exists('author',$cnts))?$cnts['author'] : 0;
            $item->ecnt = (key_exists('editor',$cnts))?$cnts['editor'] : 0;
            $item->mcnt = (key_exists('mention',$cnts))?$cnts['mention'] : 0;
            $item->ocnt = (key_exists('other',$cnts))?$cnts['other'] : 0;;
  
            
            $item->filmcnt = 0;
            if ($this->xbfilmsStatus) {
            	$db    = Factory::getDbo();
            	$query = $db->getQuery(true);
            	$query->select('COUNT(*)')->from('#__xbfilmperson');
            	$query->where('person_id = '.$db->quote($item->id));
            	$db->setQuery($query);
            	$item->filmcnt = $db->loadResult();
            }
    
            $item->alist='';
            if ($item->acnt>0) {
            	$item->alist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->books,'author',', ',false,true));
            }
            $item->elist='';
            if ($item->ecnt>0) {
            	$item->elist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->books,'editor',', ',false,true));
            }
            $item->mlist='';
            if ($item->mcnt>0) {
            	$item->mlist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->books,'mention',', ',false,true));
            }
            $item->olist='';
            if ($item->ocnt>0) {
            	$item->olist = htmlentities(XbbooksGeneral::makeLinkedNameList($item->books,'other',', ',false,true,true));
            }
            

            
            
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
	        $item->persontags = $tagsHelper->getItemTags('com_xbpeople.person' , $item->id);
	        $item->filmtags = $tagsHelper->getItemTags('com_xbfilms.person' , $item->id);
	        $item->booktags = $tagsHelper->getItemTags('com_xbbooks.person' , $item->id);
        } //end foreach item
	        return $items;
    }

}