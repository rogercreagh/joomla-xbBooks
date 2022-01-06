<?php
/*******
 * @package xbBooks
 * @filesource site/models/book.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksModelBookreview extends JModelItem {
		
	protected function populateState() {
		$app = Factory::getApplication('site');
		
		// Load state from the request.
		$id = $app->input->getInt('id');
		$this->setState('bookreview.id', $id);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
	}
	
	public function getItem($id = null) {
		if (!isset($this->item) ) { //|| !is_null($id)
			$id    = is_null($id) ? $this->getState('bookreview.id') : $id;
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id AS id, a.title AS title, 
				a.book_id AS book_id, b.cover_img AS cover_img, b.title AS book_title,
				a.rev_date AS rev_date, a.rating AS rating, a.summary AS summary, a.review AS review, a.reviewer AS reviewer,
				a.state AS published, a.catid AS catid, a.params AS params, a.metadata AS metadata  ');
			$query->from('#__xbbookreviews AS a');
			$query->select('c.title AS category_title');
			$query->leftJoin('#__categories AS c ON c.id = a.catid');
			$query->join('LEFT', '#__xbbooks AS b ON b.id = a.book_id');
			$query->where('a.id = '.$id);
			$db->setQuery($query);
			
			if ($this->item = $db->loadObject()) {
				
				$item = &$this->item;
				// Load the JSON string
				$params = new JRegistry;
				$params->loadString($item->params, 'JSON');
				$item->params = $params;
				
				// Merge global params with item params
				$params = clone $this->getState('params');
				$params->merge($item->params);
				$item->params = $params;				
				
				//get people and counts
				$item->people = XbbooksGeneral::getBookRolesArray($item->book_id,'',false);
				$cnts = array_count_values(array_column($item->people, 'role'));
				$item->authcnt = (key_exists('author',$cnts))? $cnts['author'] : 0;
				$item->editcnt = (key_exists('editor',$cnts))? $cnts['editor'] : 0;
				
				//make author/editor list
				$item->edauths = '<i>';
				if ($item->editcnt == 0){
					if ($item->authcnt == 0){
						$item->edauths .= JText::_( 'COM_XBBOOKS_NOAUTHOR' ).'</i>';
					} else {
						$item->edauths .= ($item->authcnt>1)?JText::_('XBCULTURE_CAPAUTHORS'):JText::_('XBCULTURE_AUTHOR');
						$item->edauths .= '</i>: '.XbbooksGeneral::makeLinkedNameList($item->people,'author',',',false);
					}
				} else {
					$item->edauths .= JText::_('XBCULTURE_EDITOR').'</i>: '.
							XbbooksGeneral::makeLinkedNameList($item->people,'editor',',',false);
				}
				
				//get other reviews
				$item->reviews = XbbooksGeneral::getBookReviews($item->book_id);				
			} //end if loadobject			
            return $this->item;			
		} //end if item not set already and we have an id				
	} //end getitem()
	
}