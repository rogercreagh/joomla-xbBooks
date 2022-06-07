<?php
/*******
 * @package xbBooks
 * @filesource site/models/category.php
 * @version 0.9.8.7 5th June 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class XbbooksModelCategory extends JModelItem {
	
    public function __construct($config = array()) {
        $showcats = ComponentHelper::getParams('com_xbbooks')->get('show_cats',1);
        if (!$showcats) {
            header('Location: index.php?option=com_xbbooks&view=booklist');
            exit();
        }
        parent::__construct($config);
    }
    
    protected function populateState() {
		$app = Factory::getApplication('site');
		
		// Load state from the request.
		$id = $app->input->getInt('id');
		$this->setState('cat.id', $id);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
	}
	
	public function getItem($id = null) {
		if (!isset($this->item) || !is_null($id)) {
			$id    = is_null($id) ? $this->getState('cat.id') : $id;
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('c.id AS id, c.path AS path, c.title AS title, c.description AS description, c.note AS note, c.metadata AS metadata, c.extension AS extension');
			$query->select('(SELECT COUNT(*) FROM #__xbbooks AS mb WHERE mb.catid = c.id) AS bcnt');
			$query->select('(SELECT COUNT(*) FROM #__xbpersons AS mp WHERE mp.catid = c.id) AS pcnt');
			$query->select('(SELECT COUNT(*) FROM #__xbbookreviews AS mr WHERE mr.catid = c.id) AS rcnt');
			$query->select('(SELECT COUNT(*) FROM #__xbcharacters AS mch WHERE mch.catid = c.id) AS chcnt');
			$query->from('#__categories AS c');
			$query->where('c.id = '.$id);
			
			try {
				$db->setQuery($query);
				$this->item = $db->loadObject();
			} catch (Exception $e) {
				$dberr = $e->getMessage();
				Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
			}
			if ($this->item) {
				$item = &$this->item;
				//get titles and ids of books, people and reviews in this category
				if ($item->bcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('b.id AS bid, b.title AS title')
						->from('#__categories AS c');
					$query->join('LEFT','#__xbbooks AS b ON b.catid = c.id');
					$query->where('c.id='.$item->id);
					$query->order('b.title');
					$db->setQuery($query);
					$item->bks = $db->loadObjectList();
				} else {
					$item->bks = '';
				}
				if ($item->pcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('p.id AS pid, CONCAT(p.firstname,'.$db->quote(' '). ',p.lastname) AS title')
						->from('#__categories AS c');
					$query->join('LEFT','#__xbpersons AS p ON p.catid = c.id');
					$query->where('c.id='.$item->id);
					$query->order('p.lastname');
					$db->setQuery($query);
					$item->people = $db->loadObjectList();
				} else {
					$item->people='';
				}
				if ($item->rcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('r.id AS rid, r.title AS title')
						->from('#__categories AS c');
					$query->join('LEFT','#__xbbookreviews AS r ON r.catid = c.id');
					$query->where('c.id='.$item->id);
					$query->order('r.title');
					$db->setQuery($query);
					$item->revs = $db->loadObjectList();
				} else {
					$item->revs = '';
				}
				if ($item->chcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('ch.id AS pid, ch.name AS title')
					->from('#__categories AS c');
					$query->join('LEFT','#__xbcharacters AS ch ON ch.catid = c.id');
					$query->where('c.id='.$item->id);
					$query->order('ch.name');
					$db->setQuery($query);
					$item->chars = $db->loadObjectList();
				} else {
					$item->chars='';
				}
			}
			
			return $this->item;
		} //endif isset
	} //end function getItem
}
		
