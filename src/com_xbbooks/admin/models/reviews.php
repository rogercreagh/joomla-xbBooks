<?php
/*******
 * @package xbBooks
 * @filesource admin/models/reviews.php
 * @version 0.8.6 4th April 2021
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
            
            // we have reviewer column now, this not used
//        $query->select($db->quoteName('u.username', 'username'))
//            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.created_by');
        
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
        } elseif ($published === '') {
            $query->where('(a.state IN (0, 1))');
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
        
        if (($taglogic === '2') && (empty($tagfilt))) {
        	//if if we select tagged=excl and no tags specified then only show untagged items
        	$subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
 					WHERE type_alias = '.$db->quote('com_xbbooks.book').')';
        	$query->where('a.id NOT IN '.$subQuery);
        }
        
        if (!empty($tagfilt)) {
        	$tagfilt = ArrayHelper::toInteger($tagfilt);
        	
        	if ($taglogic==2) { //exclude anything with a listed tag
        		// subquery to get a virtual table of item ids to exclude
        		$subQuery = '(SELECT content_item_id FROM #__contentitem_tag_map
					WHERE type_alias = '.$db->quote('com_xbbooks.book').
					' AND tag_id IN ('.implode(',',$tagfilt).'))';
        		$query->where('a.id NOT IN '.$subQuery);
        	} else {
        		if (count($tagfilt)==1)	{ //simple version for only one tag
        			$query->join( 'INNER', $db->quoteName('#__contentitem_tag_map', 'tagmap')
        					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id') )
        					->where(array( $db->quoteName('tagmap.tag_id') . ' = ' . $tagfilt[0],
        							$db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_xbbooks.book') )
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
        							$db->quoteName($mapname.'.type_alias') . ' = ' . $db->quote('com_xbbooks.book'))
        							);
        				}
        			} else { // match ANY listed tag
        				// make a subquery to get a virtual table to join on
        				$subQuery = $db->getQuery(true)
        				->select('DISTINCT ' . $db->quoteName('content_item_id'))
        				->from($db->quoteName('#__contentitem_tag_map'))
        				->where( array(
        						$db->quoteName('tag_id') . ' IN (' . implode(',', $tagfilt) . ')',
        						$db->quoteName('type_alias') . ' = ' . $db->quote('com_xbbooks.book'))
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
