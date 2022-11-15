<?php
/*******
 * @package xbBooks
 * @filesource site/models/book.php
 * @version 0.9.11.0 15th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class XbbooksModelBookreview extends JModelItem {
	
    public function __construct($config = array()) {
        $showrevs = ComponentHelper::getParams('com_xbbooks')->get('show_revs',1);
        if (!$showrevs) {
            header('Location: index.php?option=com_xbbooks&view=booklist');
            exit();
        }
        parent::__construct($config);
    }
       
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
				$params = new Registry;
				$params->loadString($item->params, 'JSON');
				$item->params = $params;
				
				// Merge global params with item params
				$params = clone $this->getState('params');
				$params->merge($item->params);
				$item->params = $params;				
				
				//get people and counts
				$item->people = XbbooksGeneral::getBookPeople($item->book_id);
				//get counts for director,producers,cast,crew,appearances
				$roles = array_column($item->people,'role');
				$item->authcnt = count(array_keys($roles, 'author'));
				$item->editcnt = count(array_keys($roles, 'editor'));
								
				//make author/editor list
				$item->edauths = '<i>';
				if ($item->editcnt == 0){
					if ($item->authcnt == 0){
						$item->edauths .= Text::_( 'XBBOOKS_NOAUTHOR' ).'</i>';
					} else {
						$item->edauths .= ($item->authcnt>1)?JText::_('XBCULTURE_AUTHORS'):Text::_('XBCULTURE_AUTHOR');
						$item->edauths .= '</i>: '.XbcultureHelper::makeLinkedNameList($item->people,'author','comma',false);
					}
				} else {
					$item->edauths .= Text::_('XBCULTURE_EDITOR').'</i>: '.
							XbcultureHelper::makeLinkedNameList($item->people,'editor','comma',false);
				}
				
				//get other reviews
				$item->reviews = XbbooksGeneral::getBookReviews($item->book_id);				
			} //end if loadobject			
            return $this->item;			
		} //end if item not set already and we have an id				
	} //end getitem()
	
}