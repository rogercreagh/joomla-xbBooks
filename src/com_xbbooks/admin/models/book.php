<?php
/*******
 * @package xbBooks
 * @filesource admin/models/book.php
 * @version 1.0.3.2 8th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\Registry\Registry;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\UCM\UCMType;

class XbbooksModelBook extends JModelAdmin {
    
    // batch processes supported by helloworld (over and above the standard batch processes)
    protected $xbbooks_batch_commands = array(
        'fiction' => 'batchFiction',
    );
    
    public $typeAlias = 'com_xbbooks.book';
    
    /**
     * Method overriding batch in JModelAdmin so that we can include the additional batch processes
     * which the helloworld component supports.
     */
    public function batch($commands, $pks, $contexts)
    {
        $this->batch_commands = array_merge($this->batch_commands, $this->xbbooks_batch_commands);
        return parent::batch($commands, $pks, $contexts);
    }
    
    /**
     * Method implementing the batch setting of fiction/non-fiction value
     */
    protected function batchFiction($value, $pks, $contexts) {
        
        if (!empty($value)) {
            if (empty($this->batchSet)) {
                // Set some needed variables.
                $this->user = Factory::getUser();
                $this->table = $this->getTable();
                $this->tableClassName = get_class($this->table);
                $this->contentType = new UCMType;
                $this->type = $this->contentType->getTypeByTable($this->tableClassName);
            }
            
            $db = Factory::getDbo();
            
            foreach ($pks as $pk) {
                if ($this->user->authorise('core.edit', $contexts[$pk])) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__xbbooks'))
                        ->set('fiction = ' . (int) $value-2)
                        ->where('id='.$pk);
                    $db->setQuery($query);
                    if (!($db->execute())) {
                        $this->setError($db->getErrorMsg());
                        return false;
                    }
                } else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));                    
                    return false;
                }
            }
        }
        return true;
    }
    
	
	public function getItem($pk = null) {
		
		$item = parent::getItem($pk);
		
		if (!empty($item->id)) {
			// Convert the metadata field to an array.
			$registry = new Registry($item->metadata);
			$item->metadata = $registry->toArray();
			
			$tagsHelper = new TagsHelper;
			$item->tags = $tagsHelper->getTagIds($item->id, 'com_xbbooks.book');

			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('br.rating AS rate, br.rev_date AS rev_date')->from('#__xbbookreviews AS br')
			->where('br.book_id='.$db->quote($item->id))->order('rev_date DESC');
			$db->setQuery($query);
			$item->lastrat = $db->loadAssoc();
			if ((!empty($item->lastrat)) && (empty($item->last_read))) {
			    $item->last_read = $item->lastrat['rev_date'];
			}
		}
		return $item;
	}
		
	public function getTable($type = 'Book', $prefix = 'XbbooksTable', $config = array()) {

        return Table::getInstance($type, $prefix, $config);
    }
    
    public function getForm($data = array(), $loadData = true) {

        $form = $this->loadForm( 'com_xbbooks.book', 'book',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );
        
        if (empty($form)) {
            return false;
        }
        
        $params = ComponentHelper::getParams('com_xbbooks');
        $cover_path = $params->get('cover_path','');
        if ($cover_path != '') { 
        	$form->setFieldAttribute('cover_img','directory',$cover_path);
        }
        
        return $form;
    }
    
    protected function loadFormData() {
        $data = Factory::getApplication()->getUserState('com_xbbooks.edit.book.data', array() );
        
        if (empty($data)) {
            $data = $this->getItem();          
	        $data->authorlist=$this->getBookPeoplelist('author');
	        $data->editorlist=$this->getBookPeoplelist('editor');
	        $data->otherlist=$this->getBookPeoplelist('other');
	        $data->menlist=$this->getBookPeoplelist('mention');
	        $data->charlist=$this->getBookCharlist();
	        $data->grouplist=$this->getBookGrouplist();
        }
             
        $tagsHelper = new TagsHelper;
        $params = ComponentHelper::getParams('com_xbbooks');
        $taggroup1_parent = $params->get('taggroup1_parent','');
        if ($taggroup1_parent && !(empty($data->tags))) {
            $taggroup1_tags = $tagsHelper->getTagTreeArray($taggroup1_parent);
            $data->taggroup1 = array_intersect($taggroup1_tags, explode(',', $data->tags));
        }
        $taggroup2_parent = $params->get('taggroup2_parent','');
        if ($taggroup2_parent && !(empty($data->tags))) {
            $taggroup2_tags = $tagsHelper->getTagTreeArray($taggroup2_parent);
            $data->taggroup2 = array_intersect($taggroup2_tags, explode(',', $data->tags));
        }
        $taggroup3_parent = $params->get('taggroup3_parent','');
        if ($taggroup3_parent && !(empty($data->tags))) {
            $taggroup3_tags = $tagsHelper->getTagTreeArray($taggroup3_parent);
            $data->taggroup3 = array_intersect($taggroup3_tags, explode(',', $data->tags));
        }
        $taggroup4_parent = $params->get('taggroup4_parent','');
        if ($taggroup4_parent && !(empty($data->tags))) {
            $taggroup4_tags = $tagsHelper->getTagTreeArray($taggroup4_parent);
            $data->taggroup4 = array_intersect($taggroup4_tags, explode(',', $data->tags));
        }
        
        return $data;
    }
    
    protected function prepareTable($table) {
        $date = Factory::getDate();
        $user = Factory::getUser();
        $db = Factory::getDbo();
        
        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->subtitle = htmlspecialchars_decode($table->subtitle, ENT_QUOTES);
        $table->alias = ApplicationHelper::stringURLSafe($table->alias);
        
        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->title);
        }

        if (empty($table->created)) {
            $table->created = $date->toSql();
        }
        if (empty($table->created_by)) {
        	$table->created_by = Factory::getUser()->id;
        }
        if (empty($table->created_by_alias)) {           
        	$table->created_by_alias = Factory::getUser()->username; //make it an option to use name instead of username
        }
        if (empty($table->id)) {           
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $query = $db->getQuery(true)
                ->select('MAX(ordering)')
                ->from($db->quoteName('#__xbbooks'));
                
                $db->setQuery($query);
                $max = $db->loadResult();
                
                $table->ordering = $max + 1;
            }
        } else {
            // not new so set the modified by
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }
    
    public function publish(&$pks, $value = 1) {
        if (!empty($pks)) {
            foreach ($pks as $item) {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__xbbooks'))
                    ->set('state = ' . (int) $value)
                    ->where('id='.$item);
                $db->setQuery($query);
                if (!($db->execute())) {
                    $this->setError($db->getErrorMsg());
                    return false;
                }
            }
            return true;
        }
    }
        
    public function delete(&$pks, $value = 1) {
        if (!empty($pks)) {
            $cnt = 0;
            $table = $this->getTable('book');
            foreach ($pks as $i=>$item) {
                $table->load($item);
                if (!$table->delete($item)) {
                	$bookword = ($cnt == 1)?  Text::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');
                    Factory::getApplication()->enqueueMessage($cnt.$bookword.' deleted');
                    $this->setError($table->getError());
                    return false;
                }
                $table->reset();
                $cnt++;
            }
            $bookword = ($cnt == 1)? Text::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');
            Factory::getApplication()->enqueueMessage($cnt.$bookword.' deleted');
            return true;
            //delete char and people and group links happens in table delete() function
        }
    }
    
    public function getBookPeoplelist($role) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('a.id as person_id, ba.role_note AS role_note');
            $query->from('#__xbbookperson AS ba');
            $query->innerjoin('#__xbpersons AS a ON ba.person_id = a.id');
            $query->where('ba.book_id = '.(int) $this->getItem()->id);
            if ($role == 'other') {
                $query->where($db->qn('ba.role')." NOT IN ('author','editor','mention')");
            } else {
                $query->where('ba.role = "'.$role.'"');
            }
            $query->order('ba.listorder ASC');
            $db->setQuery($query);
            return $db->loadAssocList();
    }

    public function getBookGrouplist() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id as person_id, ba.role AS role, ba.role_note AS role_note');
        $query->from('#__xbbookgroup AS ba');
        $query->innerjoin('#__xbgroups AS a ON ba.group_id = a.id');
        $query->where('ba.book_id = '.(int) $this->getItem()->id);
        $query->order('ba.listorder ASC');
        $db->setQuery($query);
        return $db->loadAssocList();
    }   
    
    public function getBookCharlist() {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('ba.char_id as char_id, ba.char_note AS char_note');
            $query->from('#__xbbookcharacter AS ba');
            $query->innerjoin('#__xbcharacters AS a ON ba.char_id = a.id');
            $query->where('ba.book_id = '.(int) $this->getItem()->id);
            $query->order('ba.listorder ASC');
            $db->setQuery($query);
            return $db->loadAssocList();
    }
    
    public function save($data) {
        $input = Factory::getApplication()->input;
        
        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));
            
            if ($data['title'] == $origTable->title) {
                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['title'] = $title;
                $data['alias'] = $alias;
            } else {
                if ($data['alias'] == $origTable->alias) {
                    $data['alias'] = '';
                }
            }
            // standard Joomla practice is to set the new copy record as unpublished
            $data['published'] = 0;
        }
        
        //merge groups back into tags
        if ($data['taggroup1']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup1'])) : $data['taggroup1'];
        }
        if ($data['taggroup2']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup2'])) : $data['taggroup2'];
        }
        if ($data['taggroup3']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup3'])) : $data['taggroup3'];
        }
        if ($data['taggroup4']) {
            $data['tags'] = ($data['tags']) ? array_unique(array_merge($data['tags'],$data['taggroup4'])) : $data['taggroup4'];
        }
        
        //if only first_seen or last_seen is set then copy to other one
        if (($data['first_read']=='') && ($data['last_read']!='')) {
            $data['first_read']=$data['last_read'];
        }
        if (($data['last_read']=='') && ($data['first_read']!='')) {
            $data['last_read']=$data['first_read'];
        }
        if (parent::save($data)) {
            //get the saved id (valid for new items as well where $data['id'] will still = 0
        	$bid = $this->getState('book.id');
        	$db = $this->getDbo();
            // set nulls for empty year and last_read (otherwise empty value defaults to 0000-00-00 00:00:00 which is invalid in latest myql strict mode)
    	    if ($data['pubyear']=='') {
    	        $query= $db->getQuery(true);
    	        $query = 'UPDATE `#__xbbooks`  AS a SET `pubyear` = NULL ';
    	        $query .= 'WHERE a.id  ='.$bid.' ';
    	        $db->setQuery($query);
    	        $db->execute();
    	    }
    	    if ($data['last_read']=='') {
    	        $query= $db->getQuery(true);
    	        $query = 'UPDATE `#__xbbooks`  AS a SET `last_read` = NULL ';
    	        $query .= 'WHERE a.id  ='.$bid.' ';
    	        $db->setQuery($query);
    	        $db->execute();
    	    }
    	    if ($data['first_read']=='') {
    	        $query= $db->getQuery(true);
    	        $query = 'UPDATE `#__xbbooks`  AS a SET `first_read` = NULL ';
    	        $query .= 'WHERE a.id  ='.$bid.' ';
    	        $db->setQuery($query);
    	        $db->execute();
    	    }        	    
        	//the checkedouttime has been set to null in the table constructor
        	$this->storeBookPersons($bid,'author', $data['authorlist']);
        	$this->storeBookPersons($bid,'editor', $data['editorlist']);
        	$this->storeBookPersons($bid,'other', $data['otherlist']);
        	$this->storeBookPersons($bid,'mention', $data['menlist']);
            
        	$this->storeBookChars($bid, $data['charlist']);
        	
        	$this->storeBookGroups($bid, $data['grouplist']);
        	
            if ($data['quick_rating'] !='')  {
            	$params = ComponentHelper::getParams('com_xbbooks');
            	$date = Factory::getDate();
            	$db = $this->getDbo();
            	//need to create a title (unique from rev cnt), alias, bookid, catid (uncategorised), reviewer
            	$query= $db->getQuery(true);
            	$query->select('COUNT(r.id) as revcnt')->from('#__xbbookreviews AS r')
            	->where('r.book_id = '.$bid);
            	$db->setQuery($query);
            	$revs=$db->loadResult()+1;
            	$revs = $revs==0 ? '' : '-'.($revs+1);
            	$rtitle = 'Quick Rating '.$data['alias'];
            	$ralias = OutputFilter::stringURLSafe($rtitle.$revs);
            	$reviewer = Factory::getUser()->name;
            	if ($params->get('def_new_revcat')>0) {
            		$catid=$params->get('def_new_revcat');
            	} else {
            		$catid = XbcultureHelper::getIdFromAlias('#__categories', 'uncategorised','com_xbbooks');
            	}
            	$qry = 'INSERT INTO '.$db->quoteName('#__xbbookreviews').' (title, alias, book_id, catid, reviewer, rating, rev_date, created, created_by, state ) ';
              	$qry .= 'VALUES ('.$db->quote($rtitle).','.$db->quote($ralias).','.$bid.','.$catid.','.$db->quote($reviewer).','.
              	$data['quick_rating'].','.$db->quote($data['acq_date']).','.$db->quote($date->toSql()).','.$db->quote($data['created_by']).',1)';
              	$db->setQuery($qry);
              	$db->execute();
            }
            return true;
        }
        
        return false;
    }
     
    function storeBookPersons($book_id, $role, $personList) {
        $db = Factory::getDbo();
        //delete existing role list
        $where = $db->qn('book_id').' = '.$db->q($book_id);
        if ($role == 'other') {
            $where .= ' AND '.$db->qn('role').' NOT IN ('.$db->q('author').','.$db->q('editor').','.$db->q('mention').')';
        } else {
            $where .= ' AND '.$db->qn('role').' = '.$db->q($role);
        }
        if (XbcultureHelper::deleteFromTable('#__xbbookperson', $where)) {            
            //restore the new list
            $listorder = 0;
            foreach ($personList as $item) {
                if ($item['person_id']>0) {
                     if ($role == 'other') {
                         $thisrole = ($item['role']=='') ? $item['newrole'] : $item['role'];
                     } else {
                         $thisrole = $role;
                     }
                     if ($role != '') {
                     	$listorder ++;
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__xbbookperson'));
                        $query->columns('book_id,person_id,role,role_note,listorder');
                        $query->values($db->q($book_id).','.$db->q($item['person_id']).','.$db->q($thisrole).','.$db->q($item['role_note']).','.$db->q($listorder));
                        $db->setQuery($query);
                        try {
                            $db->execute();
                        }
                        catch (\RuntimeException $e) {
                            throw new \Exception($e->getMessage(), 500);
                            return false;
                        }                     
                     }
                }
            }
        }
    }

    function storeBookGroups($book_id, $grpList) {
        $db = Factory::getDbo();
        //delete existing group list
        $where = $db->qn('book_id').' = '.$db->q($book_id);
        if (XbcultureHelper::deleteFromTable('#__xbbookgroup', $where)) {
        //restore the new list
        $listorder = 0;
        foreach ($grpList as $item) {
            $role = ($item['role']=='') ? $item['newrole'] : $item['role'];
            if (($role !='') && ($item['group_id'] > 0)) {
                $listorder ++;
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__xbbookgroup'));
                $query->columns('book_id,group_id,role,role_note,listorder');
                $query->values('"'.$book_id.'","'.$item['group_id'].'","'.$item['role'].'","'.$item['role_note'].'","'.$listorder.'"');
                $db->setQuery($query);
                try {
                    $db->execute();
                }
                catch (\RuntimeException $e) {
                    throw new \Exception($e->getMessage(), 500);
                    return false;
                }
            }
        }    
    }
    
    function storeBookChars($book_id, $charList) {
        $db = Factory::getDbo();
        //delete existing char list
        $where = $db->qn('book_id').' = '.$db->q($book_id);
        if (XbcultureHelper::deleteFromTable('#__xbbookcharacter', $where)) {
        //restore the new list
        $listorder = 0;
        foreach ($charList as $item) {
            if ($item['char_id'] > 0) {
                $listorder ++;
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__xbbookcharacter'));
                $query->columns('book_id,char_id,char_note,listorder');
                $query->values('"'.$book_id.'","'.$item['char_id'].'","'.$item['char_note'].'","'.$listorder.'"');
                $db->setQuery($query);
                $db->execute();              
            }
        }        
    }
    
}
     
