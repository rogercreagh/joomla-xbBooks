<?php
/*******
 * @package xbBooks
 * @filesource admin/models/review.php
 * @version 0.9.7 11th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;

class XbbooksModelReview extends JModelAdmin {
	
	public $typeAlias = 'com_xbbooks.review';
	
	public function getItem($pk = null) {
		
		if ($item = parent::getItem($pk)) {
			
			// Convert the metadata field to an array.
			$registry = new Registry($item->metadata);
			$item->metadata = $registry->toArray();				
			if (!empty($item->id)) {
				//get the tags
				$tagsHelper = new TagsHelper();
				$item->tags = $tagsHelper->getTagIds($item->id, 'com_xbbooks.review');
			} else {
				//check for preset book for new review
				$app = Factory::getApplication();
				$item->book_id = $app->getUserState('bk');
				if ($item->book_id>0) {
				    $item->title = 'Review of "'.XbbooksHelper::getBookTitleById($item->book_id).'"';
				}
			}		
			return $item;
		}
	}
		    
    public function getTable($type = 'Review', $prefix = 'XbbooksTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }
    
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_xbbooks.review', 'review',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );
        
        if (empty($form)) {
            return false;
        }
        
        return $form;
    }
    
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState( 'com_xbbooks.edit.review.data', array() );
        
        if (empty($data)) {
            $data = $this->getItem();
        }
        
        return $data;
    }
    
    protected function prepareTable($table)
    {
        $date = Factory::getDate();
        $user = Factory::getUser();
        
        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->alias = ApplicationHelper::stringURLSafe($table->alias);
        
        if (empty($table->alias))
        {
            $table->alias = ApplicationHelper::stringURLSafe($table->title);
        }
        
        // Set the values
        if (empty($table->created)) {
            $table->created = $date->toSql();
        }
        if (empty($table->created_by)) {
            $table->created_by = $user->id;
        }
        if (empty($table->rev_date)) {
        	$table->rev_date = $date->toSql();
        }
        if (empty($table->reviewer)) {
        	$table->reviewer = Factory::getUser()->username;
        }
        if (empty($table->id)) {
            
            // Set ordering to the last item if not set
            if (empty($table->ordering))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                ->select('MAX(ordering)')
                ->from($db->quoteName('#__xbbookreviews'));
                
                $db->setQuery($query);
                $max = $db->loadResult();
                
                $table->ordering = $max + 1;
            }
            else
            {
                // Set the values
                $table->modified    = $date->toSql();
                $table->modified_by = $user->id;
            }
        }
    }
    
    public function publish(&$pks, $value = 1) {
        if (!empty($pks)) {
            foreach ($pks as $item) {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                ->update($db->quoteName('#__xbbookreviews'))
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
            $table = $this->getTable('review');
            foreach ($pks as $i=>$item) {
                $table->load($item);
                if (!$table->delete($item)) {
                    $revdel = ($cnt == 1)? ' review':' reviews';
                    Factory::getApplication()->enqueueMessage($cnt.$revdel.' deleted');
                    $this->setError($table->getError());
                    return false;
                }
                $table->reset();
                $cnt++;
            }
            $revdel = ($cnt == 1)? ' review':' reviews';
            Factory::getApplication()->enqueueMessage($cnt.$revdel.' deleted');
            return true;
        }
    }
}