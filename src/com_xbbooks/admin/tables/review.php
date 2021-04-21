<?php
/*******
 * @package xbBooks
 * @filesource admin/tables/review.php
 * @version 0.7.0 23rd February 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

//use Joomla\CMS\Language\Text;
//use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Observer\Tags;
use Joomla\Registry\Registry;

class XbbooksTableReview extends JTable
{
    function __construct(&$db) {
        $this->setColumnAlias('published', 'state');
        parent::__construct('#__xbbookreviews', 'id', $db);
        Tags::createObserver($this, array('typeAlias' => 'com_xbbooks.review'));
    }
    
    public function check() {
    	$params = ComponentHelper::getParams('com_xbbooks');
    	
    	$title = trim($this->title);
    	//check title and create default if none supplied
    	if ($title == '') {
    		$this->title = 'Review of "'.XbbooksHelper::getBookTitleById($this->book_id);
    		Factory::getApplication()->enqueueMessage('No review title supplied; default created - please check and change as necessary','Warning');
    	}
    	
    	if (($this->id == 0) && (XbbooksHelper::checkTitleExists($title,'#__xbbookreviews'))) {
    		$this->setError(JText::_('Review "'.$title.'" already exists; if this is a different review with the same title please append something to the title to distinguish them'));
    		return false;
    	}
    	
    	$this->title = $title;
    	
    	if (trim($this->alias) == '') {
    	    $this->alias = $title;
    	}
    	$this->alias = OutputFilter::stringURLSafe($this->alias);
    	
         //set category
    	if (!$this->catid>0) {
    	    $defcat=0;
    	    if ($params->get('def_new_revcat')>0) {
    	        $defcat=$params->get('def_new_revcat');
    	    } else {
    	        $defcat = XbbooksHelper::getIdFromAlias('#__categories', 'uncategorised');
    	    }
    	    if ($defcat>0) {
    	        $this->catid = $defcat;
    	        Factory::getApplication()->enqueueMessage(JText::_('XBCULTURE_CATEGORY_DEFAULT_SET').' ('.XbbooksHelper::getCat($this->catid)->title.')');
    	    } else {
    	    	// this shouldn't happen unless uncategorised has been deleted
    	    	$this->setError(JText::_('Please set a category'));
    	        return false;
    	    }
    	}
    	
        //warn re missing summary, create from review if missing
        if ((trim($this->summary)=='')) {
        	if (trim($this->review)=='' ) {
        		Factory::getApplication()->enqueueMessage(JText::_('XBCULTURE_MISSING_SUMMARY'));
        	}
        }
        
        //set reviewer if not set (default to current user)
        if (trim($this->reviewer) == '') {
        	$user = Factory::getUser($this->item->created_by);
        	$name = ($params->get('rev_auth') == 0) ? $user->name : $user->username;
        	$this->reviewer = $name;
        }

        //set date reviewed
        if ($this->rev_date == '') {
        	$this->rev_date = Factory::getDate()->toSql();
        }
        
        //set metadata to defaults
        $metadata = json_decode($this->metadata,true);
        //meta.author will be set to reviewer if blank. Will only be created on page display (view.html.php)
        $show_author = $params->get('show_author');
        if ($metadata['author'] == '') {
        	$metadata['author'] = $this->reviewer;
        }
        //meta.description can be set to first 150 chars of summary if not otherwise set and option is set
        $summary_metadesc = $params->get('summary_metadesc');
        if (($summary_metadesc) && (trim($metadata['metadesc']) == '')) {
        	$metadata['metadesc'] = JHtml::_('string.truncate', $this->summary,150,true,false);
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
    		$parameter = new JRegistry;
    		$parameter->loadArray($array['params']);
    		$array['params'] = (string)$parameter;
    	}
    	
    	if (isset($array['metadata']) && is_array($array['metadata'])) {
    		$registry = new JRegistry;
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