<?php
/*******
 * @package xbBooks
 * @filesource admin/tables/book.php
 * @version 0.9.11.0 15th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

//use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Observer\Tags;
use Joomla\Registry\Registry;

class XbbooksTableBook extends Table
{
	public function __construct(&$db) {
		$this->setColumnAlias('published', 'state');
        parent::__construct('#__xbbooks', 'id', $db);
        $this->_supportNullValue = true;  //write empty checkedouttime as null
        Tags::createObserver($this, array('typeAlias' => 'com_xbbooks.book'));
	}

	public function check() {
	    $params = ComponentHelper::getParams('com_xbbooks');
	    
	    $title = trim($this->title);
	    //require title
	    if ($title == '') {
	        $this->setError(Text::_('XBCULTURE_PROVIDE_VALID_TITLE'));
	        return false;
	    }
	    
	    if (($this->id == 0) && (XbcultureHelper::checkTitleExists($title,'#__xbbooks'))) {
	    	$this->setError(Text::_('Book "'.$title.'" already exists; if this is a different book with the same title please append something to the title to distinguish them'));
	    	return false;
	    }
	    
	    $this->title = $title;
	    //create alias if not set
	    if (trim($this->alias) == '') {
	        $this->alias = $title;
	    }
	    $this->alias = OutputFilter::stringURLSafe(strtolower($this->alias));
	    
	    //set category
	    if (!$this->catid>0) {
	        $defcat=0;
	        if ($params->get('def_new_bookcat')>0) {
	            $defcat=$params->get('def_new_bookcat');
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
	    
	    //require summary, warn if missing
	    if ((trim($this->summary)=='')) {
	        if (trim($this->synopsis)=='' ) {
	            Factory::getApplication()->enqueueMessage(Text::_('XBCULTURE_MISSING_SUMMARY'));
	        }
	    }
	    
	    //json encode ext_links if set
	    if (is_array($this->ext_links)) {
	        $this->ext_links = json_encode($this->ext_links);
	    }
	    
	    //set metadata to defaults
	    $metadata = json_decode($this->metadata,true);
	    // meta.author will be created_by_alias (see above)
	    if ($metadata['author'] == '') {
	        if ($this->created_by_alias =='') {
	            $metadata['author'] = $params->get('def_author');
	        } else {
	            $metadata['author'] = $this->created_by_alias;
	        }
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
 	    
        // 		if (isset($array['rules']) && is_array($array['rules'])) {
        //             $rules = new JAccessRules($array['rules']);
        //             $this->setRules($rules);
        //         }
        
        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new Registry;
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string)$registry;
        }
        return parent::bind($array, $ignore);
        
    }

	protected function _getAssetName() {
                $k = $this->_tbl_key;
                return 'com_xbbooks.book.'.(int) $this->$k;
        }

	protected function _getAssetTitle() {
                return $this->title;
        }

	protected function _getAssetParentId(Table $table = null, $id = null)
        {
            // We will retrieve the parent-asset from the Asset-table
            $assetParent = Table::getInstance('Asset');
            // Default: if no asset-parent can be found we take the global asset
            $assetParentId = $assetParent->getRootId();
            
            // Find the parent-asset
            if (($this->catid)&& !empty($this->catid))
            {
                // The item has a category as asset-parent
                $assetParent->loadByName('com_xbbooks.category.' . (int) $this->catid);
            }
            else
            {
                // The item has the component as asset-parent
                $assetParent->loadByName('com_xbbooks');
            }
            
            // Return the found asset-parent-id
            if ($assetParent->id)
            {
                $assetParentId=$assetParent->id;
            }
            return $assetParentId;
	}

	public function delete($pk=null) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete()->from('#__xbbookcharacter')->where('book_id = '. $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = $db->getQuery(true);
		$query->delete()->from('#__xbbookperson')->where('book_id = '. $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();
		return parent::delete($pk);
    }

}
