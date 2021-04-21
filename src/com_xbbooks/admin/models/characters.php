<?php
/*******
 * @package xbBooks
 * @filesource admin/models/characters.php
 * @version 0.9.4 17th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;

class XbbooksModelCharacters extends JModelList {

    public function __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            		'id', 'a.id',
            		'name', 'a.name',
            		'ordering','a.ordering',
            		'category_title', 'c.title',
            		'catid', 'a.catid', 'category_id',
            		'published','a.state'	
            );
        }
        $this->xbfilmsStatus = Factory::getSession()->get('com_xbfilms',false);
        
        parent::__construct($config);
    }
    
    protected function getListQuery() {
        //TODO need to also get roles list with book titles
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        
        // Create the base select statement.
        $query->select('a.id AS id, a.name AS name, a.alias AS alias, 
			a.summary AS summary, a.image AS image, a.description AS description, 
			a.catid AS catid, a.state AS published, a.created AS created, a.created_by AS created_by, 
			a.created_by_alias AS created_by_alias, a.checked_out AS checked_out, a.checked_out_time AS checked_out_time, 
            a.metadata AS metadata, a.ordering AS ordering, a.params AS params, a.note AS note')

            ->from($db->quoteName('#__xbcharacters','a'));
        
        $query->select('(GROUP_CONCAT(b.book_id SEPARATOR '.$db->quote(',') .')) AS booklist')
            ->join('LEFT OUTER',$db->quoteName('#__xbbookcharacter', 'b') . ' ON ' .$db->quoteName('a.id') . ' = ' . $db->quoteName('b.char_id'));
        
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
        } elseif ($published === '') {
            $query->where('(state IN (0, 1))');
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
        
//         //        $subcats = $this->getState('filter.subcats');
//         if (is_numeric($categoryId)) {
//             if ($subcats) {
//                 //                $query->where('a.catid IN ('.(int)$categoryId.','.self::getSubCategoriesList($categoryId).')');
//             } else {
//                 $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
//             }
//         }
        
        // filter by in role
        $rolefilt = $this->getState('filter.inbooks');
        if (empty($rolefilt)) { $rolefilt = 'book'; }
        if ($rolefilt!='all') {
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
        	}
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
 					WHERE type_alias LIKE '.$db->quote('com_xb%.character').')';
        	$query->where('a.id NOT IN '.$subQuery);
        }
        
        if (!empty($tagfilt)) {
        	$tagfilt = ArrayHelper::toInteger($tagfilt);
        	
        	if ($taglogic==2) { //exclude anything with a listed tag
        		// subquery to get a virtual table of item ids to exclude
        		$subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
					WHERE type_alias LIKE '.$db->quote('com_xb%.character').
					' AND tag_id IN ('.implode(',',$tagfilt).'))';
        		$query->where('a.id NOT IN '.$subQuery);
        	} else {
        		if (count($tagfilt)==1)	{ //simple version for only one tag
        			$query->join( 'INNER', $db->quoteName('#__contentitem_tag_map', 'tagmap')
        					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id') )
        					->where(array( $db->quoteName('tagmap.tag_id') . ' = ' . $tagfilt[0],
        							$db->quoteName('tagmap.type_alias') . ' LIKE ' . $db->quote('com_xb%.character') )
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
        							$db->quoteName($mapname.'.type_alias') . ' LIKE ' . $db->quote('com_xb%.character'))
        							);
        				}
        			} else { // match ANY listed tag
        				// make a subquery to get a virtual table to join on
        				$subQuery = $db->getQuery(true)
        				->select('DISTINCT ' . $db->quoteName('content_item_id'))
        				->from($db->quoteName('#__contentitem_tag_map'))
        				->where( array(
        						$db->quoteName('tag_id') . ' IN (' . implode(',', $tagfilt) . ')',
        						$db->quoteName('type_alias') . ' LIKE ' . $db->quote('com_xb%.character'))
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
        $orderCol	= $this->state->get('list.ordering', 'name');
        $orderDirn 	= $this->state->get('list.direction', 'asc');
        
        if ($orderCol == 'a.ordering' || $orderCol == 'a.catid') {
        	$orderCol = 'a.category_title '.$orderDirn.', a.ordering';  //TODO change this to category_title rather than id
        }
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        if ($orderCol != 'name') {
	        $query->order('name ASC');
        }
        $query->group('a.id');
        
        return $query;
    }
    
    public function getItems() {
        $items  = parent::getItems();
        // we are going to add the list of characters for each book
        $tagsHelper = new TagsHelper;
        foreach ($items as $i=>$item) { 
            $item->books = $this->getCharacterArray($item->id);
            
	        $item->tags = $tagsHelper->getItemTags('com_xbpeople.character' , $item->id);
        } //end foreach item
	        return $items;
    }

    public function getCharacterArray($personid) {
        $link = 'index.php?option=com_xbbooks&task=book.edit&id=';
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        
        $query->select('b.title, b.subtitle, b.pubyear, b.id')
            ->from('#__xbbookcharacter AS a')
            ->join('LEFT','#__xbbooks AS b ON b.id=a.book_id')
            ->where('a.char_id = "'.$personid.'"' )
            ->order('b.pubyear, b.title', 'ASC');
        $db->setQuery($query);
        $list = $db->loadObjectList();
        foreach ($list as $i=>$item){
            $tlink = JRoute::_($link . $item->id);
            $item->link = '<a href="'.$tlink.'">'.$item->title.'</a>';
            $item->display = $item->title;
        }
        return $list;
    }   
}