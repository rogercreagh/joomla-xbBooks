use Joomla\CMS\Component\ComponentHelper;
<?php
/*******
 * @package xbBooks
 * @filesource site/models/tag.php
 * @version 0.9.8.7 5th June 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class XbbooksModelTag extends JModelItem {

    public function __construct($config = array()) {
        $showtags = ComponentHelper::getParams('com_xbbooks')->get('show_tags',1);
        if (!$showtags) {
            header('Location: index.php?option=com_xbbooks&view=booklist');
            exit();
        }
        parent::__construct($config);
    }
    
	protected function populateState() {
		$app = Factory::getApplication('site');
		
		// Load state from the request.
		$id = $app->input->getInt('id');
		$this->setState('tag.id', $id);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
	}
	
	public function getItem($id = null) {
		if (!isset($this->item) || !is_null($id)) {
			$id    = is_null($id) ? $this->getState('tag.id') : $id;
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('t.id AS id, t.path AS path, t.title AS title, t.note AS note, t.description AS description,
				t.published AS published,  t.metadata AS metadata' );
			$query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mb WHERE mb.type_alias='.$db->quote('com_xbbooks.book').' AND mb.tag_id = t.id) AS bcnt');
			$query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mp WHERE mp.type_alias='.$db->quote('com_xbpeople.person').' AND mp.tag_id = t.id) AS pcnt');
			$query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mr WHERE mr.type_alias='.$db->quote('com_xbbooks.review').' AND mr.tag_id = t.id) AS rcnt');
			$query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS mch WHERE mch.type_alias='.$db->quote('com_xbpeople.character').' AND mch.tag_id = t.id) AS chcnt');
			$query->select('(SELECT COUNT(*) FROM #__contentitem_tag_map AS ma WHERE ma.tag_id = t.id) AS allcnt ');
			$query->from('#__tags AS t');
			$query->where('t.id = '.$id);
			$query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
			
			$db->setQuery($query);
			
			if ($this->item = $db->loadObject()) {
				$item = &$this->item;
				//calculate how many non xbbooks items the tag applies to to save doing it later
				$item->othercnt = $item->allcnt - ($item->bcnt + $item->pcnt + $item->rcnt);
				//get titles and ids of books, people and reviews with this tag
				$db    = Factory::getDbo();
				if ($item->bcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('b.id AS bid, b.title AS title')->from('#__tags AS t');
					$query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
					$query->join('LEFT','#__xbbooks AS b ON b.id = m.content_item_id');
					$query->where("t.id='".$item->id."' AND m.type_alias='com_xbbooks.book'");
					$query->order('b.title');
					$db->setQuery($query);
					$item->bks = $db->loadObjectList();
				} else {
					$item->bks = '';
				}
				if ($item->pcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('p.id AS pid, CONCAT(p.firstname,'.$db->quote(' '). ',p.lastname) AS title, bp.book_id AS inbook')->from('#__tags AS t');
					$query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
					$query->join('LEFT','#__xbpersons AS p ON p.id = m.content_item_id');
					$query->join('LEFT','#__xbbookperson AS bp ON bp.person_id = p.id');
					$query->where("t.id='".$item->id."' AND m.type_alias LIKE 'com_xb%.person'");
					$query->group('p.id');
					$query->order('p.lastname');
					$db->setQuery($query);
					$item->people = $db->loadObjectList();
				} else {
					$item->people='';
				}
				if ($item->chcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('p.id AS pid, p.name AS title, bp.book_id AS inbook')->from('#__tags AS t');
					$query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
					$query->join('LEFT','#__xbcharacters AS p ON p.id = m.content_item_id');
					$query->join('LEFT','#__xbbookcharacter AS bp ON bp.char_id = p.id');
					$query->where("t.id='".$item->id."' AND m.type_alias LIKE 'com_xb%.character'");
					$query->group('p.id');
					$query->order('p.name');
					$db->setQuery($query);
					$item->chars = $db->loadObjectList();
				} else {
					$item->chars='';
				}
				if ($item->rcnt > 0) {
					$query = $db->getQuery(true);
					$query->select('r.id AS rid, r.title AS title')->from('#__tags AS t');
					$query->join('LEFT','#__contentitem_tag_map AS m ON m.tag_id = t.id');
					$query->join('LEFT','#__xbbookreviews AS r ON r.id = m.content_item_id');
					$query->where("t.id='".$item->id."' AND m.type_alias='com_xbbooks.review'");
					$query->order('r.title');
					$db->setQuery($query);
					$item->revs = $db->loadObjectList();
				} else {
					$item->revs = '';
				}
				if ($item->othercnt > 0) {
					$query = $db->getQuery(true);
					$query->select('m.type_alias AS type_alias, m.core_content_id, m.content_item_id AS othid, c.core_title AS core_title, c.core_content_item_id AS item_id');
					$query->from('#__contentitem_tag_map AS m');
					$query->join('LEFT','#__ucm_content AS c ON m.core_content_id = c.core_content_id');
					$query->where('m.tag_id = '.$item->id);
					$query->where('m.type_alias NOT IN ('.$db->quote('com_xbbooks.book').','.$db->quote('com_xbbooks.review').')');
					$query->where('m.type_alias NOT LIKE '.$db->quote('com_xb%.person').' AND m.type_alias NOT LIKE '.$db->quote('com_xb%.character'));
					$query->order('m.type_alias, c.core_title');
					$db->setQuery($query);
					$item->others = $db->loadObjectList();
					$item->othcnts = array();
					foreach ($item->others as $i=>$oth) {
						$comp = substr($oth->type_alias, 0,strpos($oth->type_alias, '.'));
						if (array_key_exists($comp,$item->othcnts)) {
							$item->othcnts[$comp] ++;
						} else {
							$item->othcnts[$comp] = 1;
						}
					}
				} else {
					$item->others = '';
				}
			}
			
			return $this->item;
		} //endif isset
	} //end function getItem
}
		
