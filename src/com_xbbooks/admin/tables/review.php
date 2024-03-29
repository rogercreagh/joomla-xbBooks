<?php
/*******
 * @package xbBooks
 * @filesource admin/tables/review.php
 * @version 1.0.3.7 24th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Observer\Tags;
use Joomla\Registry\Registry;

class XbbooksTableReview extends Table
{
    function __construct(&$db) {
        $this->setColumnAlias('published', 'state');
        parent::__construct('#__xbbookreviews', 'id', $db);
        $this->_supportNullValue = true;  //write empty checkedouttime as null
        Tags::createObserver($this, array('typeAlias' => 'com_xbbooks.review'));
    }
    
    public function check() {
    	$params = ComponentHelper::getParams('com_xbbooks');
    	
    	//get count of existing reviews +1 for this review to use in default alias
    	$db = $this->getDbo();
    	$query= $db->getQuery(true);
    	$query->select('COUNT(r.id) as revcnt')->from('#__xbbookreviews AS r')
    	->where('r.book_id = '.$this->book_id);
    	$db->setQuery($query);
    	$revno = $db->loadResult();
    	$revno = $revno==0 ? '' : '-'.($revno+1);
    	//get film title for default review/rating title
    	$btitle = XbbooksHelper::getBookTitleById($this->book_id);
    	
    	$title = trim($this->title);
    	//check title and create default if none supplied
    	if (($title == '') && (trim($this->summary)=='') && (trim($this->synopsis==''))) {
    		//do quick rating
    	    $title = 'rating of "'.$btitle.'"'.$revno;
    		if (trim($this->alias) == '') {
    			$this->alias = 'rating-'.$btitle.$revno;
    		}
    	} else if ($title == '') {
    	    $this->setError(Text::_('Review title not set. Please enter a title (eg "Review of "[book-title]")'));
    	    return false;
    	} else {
    		if (($this->id == 0) && (XbcultureHelper::checkTitleExists($title,'#__xbbookreviews'))) {
    			$this->setError(Text::_('Review "'.$title.'" already exists; if this is a different review with the same title please append something to the title (eg book title or review date) to distinguish them'));
    			return false;
       		}
    		if (trim($this->alias) == '') {
    			$this->alias = $title.$revno;
    		}
       	}
    	
    	$this->title = $title;
    	
    	$this->alias = OutputFilter::stringURLSafe($this->alias);
    	
    	//set reviewer if not set (default to current user)
    	if (trim($this->reviewer) == '') {
    		$user = Factory::getUser($this->item->created_by);
    		$this->reviewer = '';
    		switch ($params->get('rev_auth')) {
    		    case 1:
    		        $this->reviewer = $user->name;
    		        break;
    		    case 2:
    		        $this->reviewer = $user->username;
    		        break;   		        
    		}
    	}
    	//set date reviewed
    	if ($this->rev_date == '') {
    		$this->rev_date = Factory::getDate()->toSql();
    	}
    	
    	if (!$this->catid>0) {
    		$defcat=0;
    		if ($params->get('def_new_revcat')>0) {
    			$defcat= $params->get('def_new_revcat');
    		} else {
    		    $defcat = XbcultureHelper::getIdFromAlias('#__categories', 'uncategorised','com_xbbooks');
    		}
    		if ($defcat>0) {
    			$this->catid = $defcat;
    			Factory::getApplication()->enqueueMessage(Text::_('XBCULTURE_CATEGORY_DEFAULT_SET').' ('.XbcultureHelper::getCat($this->catid)->title.')');
    		} else {
    			// this shouldn't happen unless uncategorised has been deleted
    			$this->setError(Text::_('XBCULTURE_CATEGORY_MISSING'));
    			return false;
    		}
    	}
    	
        //warn re missing summary, create from review if missing
        if ((trim($this->summary)=='')) {
        	if (trim($this->review)=='' ) {
        		Factory::getApplication()->enqueueMessage(Text::_('XBCULTURE_MISSING_SUMMARY'));
        	}
        }
                
        //set metadata to defaults
        $metadata = json_decode($this->metadata,true);
        //meta.author will be set to reviewer if blank. Will only be created on page display (view.html.php)
//        $show_author = $params->get('show_author');
        if ($metadata['author'] == '') {
        	$metadata['author'] = $this->reviewer;
        }
        //meta.description can be set to first 150 chars of summary if not otherwise set and option is set
        $summary_metadesc = $params->get('summary_metadesc');
        if (($summary_metadesc) && (trim($metadata['metadesc']) == '')) {
        	$metadata['metadesc'] = HtmlHelper::_('string.truncate', $this->summary,150,true,false);
        }
        //meta.rights will be set to default if not otherwise set
        $def_rights = $params->get('def_rights');
        if (($def_rights != '') && (trim($metadata['rights']) == '')) {
        	$metadata['rights'] = $def_rights;
        }
        //meta.keywords will be set to a list of tags unless otherwise set if the option is set
        //TODO update this when tags are added
        // convert existing keyword list to array, get tag names as array, merge arrays and implode to a list
        $tags_keywords = $params->get('tags_keywords');
        if (($tags_keywords) && (trim($metadata['metakey']) == '')) {
        	$tagsHelper = new TagsHelper;
        	$tags = implode(',',$tagsHelper->getTagNames(explode(',',$tagsHelper->getTagIds($this->id,'com_xbbooks.book'))));
        	$metadata['metakey'] = $tags;
        }
        $this->metadata = json_encode($metadata);
        
        return true;
    }
    
    public function bind($array, $ignore = '') {
    	if (isset($array['params']) && is_array($array['params'])) {
    		// Convert the params field to a string.
    		$parameter = new Registry;
    		$parameter->loadArray($array['params']);
    		$array['params'] = (string)$parameter;
    	}
    	
    	if (isset($array['metadata']) && is_array($array['metadata'])) {
    		$registry = new Registry;
    		$registry->loadArray($array['metadata']);
    		$array['metadata'] = (string)$registry;
    	}
    	return parent::bind($array, $ignore);
    	
//     	if (isset($array['rules']) && is_array($array['rules'])) {
//     		$rules = new JAccessRules($array['rules']);
//     		$this->setRules($rules);
//     	}
    	
    }
    
}