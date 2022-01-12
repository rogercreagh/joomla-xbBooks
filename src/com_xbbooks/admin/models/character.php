<?php
/*******
 * @package xbBooks
 * @filesource admin/models/character.php
 * @version 0.9.7 11th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Table\Table;

class XbbooksModelCharacter extends JModelAdmin {
 
	public $typeAlias = 'com_xbbooks.character';
	
	public function getItem($pk = null) {
		
		if ($item = parent::getItem($pk)) {
			// Convert the metadata field to an array.
			$registry = new Registry($item->metadata);
			$item->metadata = $registry->toArray();
			if (!empty($item->id))
			{
				$tagsHelper = new TagsHelper;
				$item->tags = $tagsHelper->getTagIds($item->id, 'com_xbpeople.character');
			}
		}
		return $item;
	}
	
	public function getTable($type = 'Character', $prefix = 'XbbooksTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }
    
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_xbbooks.character', 'character',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );
        
        if (empty($form)) {
            return false;
        }
        
        $params = ComponentHelper::getParams('com_xbbooks');
        $image_path = $params->get('image_path','');
        if ($image_path != '') {
        	$form->setFieldAttribute('image','directory',$image_path);
        }
        
        if (Factory::getSession()->get('xbpeople_ok')==false) {
        	$form->setFieldAttribute('catid','name','pcatid');
        	$form->setFieldAttribute('hcatid','name','catid');
        }
        
        return $form;
    }
    
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState( 'com_xbbooks.edit.character.data', array() );
        
        if (empty($data)) {
            $data = $this->getItem();
        }
        if (is_object($data)) {
	        $data->bookcharlist=$this->getCharacterBookslist();       	
        }
        
        return $data;
    }

	protected function prepareTable($table) {
		$date = Factory::getDate();
		$user = Factory::getUser();

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias = ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->name);
		}

		// Set the values
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
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('MAX(ordering)')
					->from($db->quoteName('#__xbcharacters'));

				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		} else {
			$table->modified    = $date->toSql();
			$table->modified_by = $user->id;			
		}
	}

	public function publish(&$pks, $value = 1) {
	    if (!empty($pks)) {
	        foreach ($pks as $item) {
	            $db = $this->getDbo();
	            $query = $db->getQuery(true)
    	            ->update($db->quoteName('#__xbcharacters'))
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

	public function delete(&$pks) {
	    if (!empty($pks)) {
	        $cnt = 0;
	        $table = $this->getTable('character');
	        foreach ($pks as $i=>$item) {
	            $table->load($item);	            
	            if (!$table->delete($item)) {
	                $personpeople = ($cnt == 1)? Text::_('XBCULTURE_PERSON') : Text::_('XBCULTURE_PEOPLE');
	                Factory::getApplication()->enqueueMessage($cnt.' '.$personpeople.' deleted');
	                $this->setError($table->getError());
	                return false;
	            }
	            $table->reset();
	            $cnt++;
	        }
	        $personpeople = ($cnt == 1)? Text::_('XBCULTURE_PERSON') : Text::_('XBCULTURE_PEOPLE');
	        Factory::getApplication()->enqueueMessage($cnt.' '.$personpeople.' deleted');
	        return true;
	    }
	}

	public function getCharacterBookslist() {
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id as book_id, ba.char_note AS char_note');
		$query->from('#__xbbookcharacter AS ba');
		$query->innerjoin('#__xbbooks AS a ON ba.book_id = a.id');
		$query->where('ba.char_id = '.(int) $this->getItem()->id);
		$query->order('a.title ASC');
		$db->setQuery($query);
		return $db->loadAssocList();
	}
	
	public function save($data) {
		$input = Factory::getApplication()->input;
		if (parent::save($data)) {
			$this->storeCharacterBooks($this->getState('character.id'),$data['bookcharlist']);
			
			return true;
		}
		
		return false;
	}
	
	function storeCharacterBooks($char_id, $charList) {
		//delete existing role list
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__xbbookcharacter'));
		$query->where('char_id = '.$char_id.' ');
		$db->setQuery($query);
		$db->execute();
		//restore the new list
		foreach ($charList as $ch) {
		    if ($ch['book_id'] >0 ) {
    			$query = $db->getQuery(true);
    			$query->insert($db->quoteName('#__xbbookcharacter'));
    			$query->columns('char_id,book_id,char_note');
    			$query->values('"'.$char_id.'","'.$ch['book_id'].'","'.$ch['char_note'].'"');
    			$db->setQuery($query);
    			$db->execute();		        
		    }
		}
	}
	
}