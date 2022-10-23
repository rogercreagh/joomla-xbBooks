<?php
/*******
 * @package xbBooks
 * @filesource admin/models/book.php
 * @version 0.9.9.8 21st October 2022
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
        //if acq_date empty then if last_read is set copy it to acq_date
        //if acq_date empty and last_read not set then set acq_date to today
//         if (empty($table->acq_date)) {
//             if (!empty($table->last_read)) {
//                 $table->acq_date = $table->last_read;
//             } else {
//         	   //default to today
//             	$table->acq_date = $date->toSql();
//             }
//         }            
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
        }
    }
    
    public function getBookPeoplelist($role) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('a.id as person_id, ba.role_note AS role_note');
            $query->from('#__xbbookperson AS ba');
            $query->innerjoin('#__xbpersons AS a ON ba.person_id = a.id');
            $query->where('ba.book_id = '.(int) $this->getItem()->id);
            $query->where('ba.role = "'.$role.'"');
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
        if (parent::save($data)) {
            //get the saved id (valid for new items as well where $data['id'] will still = 0
        	$bid = $this->getState('book.id');
            // set nulls for empty year and last_read (otherwise empty value defaults to 0000-00-00 00:00:00 which is invalid in latest myql strict mode)
        	if (($data['first_read']=='') || ($data['last_read']=='') || ($data['pubyear']=='')){
        	    $db = $this->getDbo();
        	    $query= $db->getQuery(true);
        	    if ($data['pubyear']=='') {
        	        $query = 'UPDATE `#__xbbooks`  AS a SET `pubyear` = NULL ';
        	        $query .= 'WHERE a.id  ='.$bid.' ';
        	        $db->setQuery($query);
        	        $db->execute();
        	    }
        	    if ($data['last_read']=='') {
        	        $query = 'UPDATE `#__xbbooks`  AS a SET `last_read` = NULL ';
        	        $query .= 'WHERE a.id  ='.$bid.' ';
        	        $db->setQuery($query);
        	        $db->execute();
        	    }
        	    if ($data['first_seen']=='') {
        	        $query = 'UPDATE `#__xbbooks`  AS a SET `first_read` = NULL ';
        	        $query .= 'WHERE a.id  ='.$bid.' ';
        	        $db->setQuery($query);
        	        $db->execute();
        	    }        	    
        	}
        	//the checkedouttime has been set to null in the table constructor
        	$this->storeBookPersons($bid,'author', $data['authorlist']);
        	$this->storeBookPersons($bid,'editor', $data['editorlist']);
        	$this->storeBookPersons($bid,'other', $data['otherlist']);
        	$this->storeBookPersons($bid,'mention', $data['menlist']);
            
            $this->storeBookChars($bid, $data['charlist']);

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
            		$catid = XbbooksHelper::getIdFromAlias('#__categories', 'uncategorised');
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
        //delete existing role list
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__xbbookperson'));
        $query->where('book_id = '.$book_id.' AND role = "'.$role.'"');
        $db->setQuery($query);
        $db->execute();
        //restore the new list
        $listorder = 0;
         foreach ($personList as $pers) {
             if ($pers['person_id'] > 0) {
             	$listorder ++;
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__xbbookperson'));
                $query->columns('book_id,person_id,role,role_note,listorder');
                $query->values('"'.$book_id.'","'.$pers['person_id'].'","'.$role.'","'.$pers['role_note'].'","'.$listorder.'"');
                $db->setQuery($query);
                $db->execute();            
                
             }
        }
    }

    function storeBookChars($book_id, $charList) {
        //delete existing char list
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__xbbookcharacter'));
        $query->where('book_id = '.$book_id);
        $db->setQuery($query);
        $db->execute();
        //restore the new list
        foreach ($charList as $pers) {
            if ($pers['char_id'] > 0) {
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__xbbookcharacter'));
                $query->columns('book_id,char_id,char_note,listorder');
                $query->values('"'.$book_id.'","'.$pers['char_id'].'","'.$pers['char_note'].'","'.$pers['listorder'].'"');
                $db->setQuery($query);
                $db->execute();              
            }
        }
            
    }
}
     
