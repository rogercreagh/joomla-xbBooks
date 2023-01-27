<?php
/*******
 * @package xbBooks
 * @filesource site/models/book.php
 * @version 1.0.3.8 27th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class XbbooksModelBook extends JModelItem {
		
	protected function populateState() {
		$app = Factory::getApplication('site');
		
		// Load state from the request.
		$id = $app->input->getInt('id');
		$this->setState('book.id', $id);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
	}
	
	public function getItem($id = null) {
		if (!isset($this->item) || !is_null($id)) {
			$id    = is_null($id) ? $this->getState('book.id') : $id;
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id AS id, a.title AS title, a.subtitle AS subtitle, a.cover_img AS cover_img, a.pubyear AS pubyear,
				a.summary AS summary, a.synopsis AS synopsis, a.publisher AS publisher, a.first_read AS first_read, a.last_read AS last_read,
				a.ext_links AS ext_links, a.fiction AS fiction, a.orig_lang AS orig_lang, a.edition AS edition, a.format AS format,
				a.state AS published, a.catid AS catid, a.params AS params, a.metadata AS metadata, a.created AS created ');
			$query->from('#__xbbooks AS a');
			$query->select('(SELECT AVG(br.rating) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS averat');
			$query->select('(SELECT COUNT(DISTINCT(bp.person_id)) FROM #__xbbookperson AS bp WHERE bp.book_id = a.id) AS pcnt');
			$query->select('(SELECT COUNT(DISTINCT(bc.char_id)) FROM #__xbbookcharacter AS bc WHERE bc.book_id = a.id) AS ccnt');
			$query->select('(SELECT COUNT(DISTINCT(bg.group_id)) FROM #__xbbookgroup AS bg WHERE bg.book_id = a.id) AS gcnt');
			$query->select('c.title AS category_title');
			$query->leftJoin('#__categories AS c ON c.id = a.catid');
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
				$target = ($params->get('extlink_target')==1) ? 'target="_blank"' : '';
				// Convert the JSON-encoded links info into an array
				$extlinks = new Registry;
				$extlinks->loadString($item->ext_links, 'JSON');
				$item->ext_links = $extlinks;
				$item->ext_links_cnt = 0;
//				$item->ext_links = json_decode($item->ext_links);
				$item->ext_links_list ='';
				if(is_object($item->ext_links)) {
					$item->ext_links_cnt = 0;
					$item->ext_links_list = '<ul>';
					foreach($item->ext_links as $lnk) {
						$item->ext_links_list .= '<li><a href="'.$lnk->link_url.'" '.$target.'>'.$lnk->link_text.
							'</a> - '.$lnk->link_desc.'</li>';
						$item->ext_links_cnt += 1;
					}
					$item->ext_links_list .= '</ul>';
				}
				
				
				//get authors,editors,characters
				$item->people = XbbooksGeneral::getBookPeople($item->id);
				//get counts for director,producers,cast,crew,appearances
				$roles = array_column($item->people,'role');
				$item->authcnt = count(array_keys($roles, 'author'));
				$item->editcnt = count(array_keys($roles, 'editor'));
				$item->mencnt = count(array_keys($roles, 'mention'));
				$item->othcnt = count(array_keys($roles, 'other'));
				
				$item->alist = $item->authcnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'author','tn',5,'ppvmodal');
				$item->elist = $item->editcnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'editor','tn',5,'ppvmodal');
				$item->mlist = $item->mencnt==0 ? '' : XbcultureHelper::makeItemLists($item->people,'mention','tn',5,'ppvmodal');
				$item->olist =$item->othcnt==0 ? '' :  XbcultureHelper::makeItemLists($item->people,'other','rtn',5,'ppvmodal');				
				
				if ($item->ccnt>0) {
    				$item->chars = XbbooksGeneral::getBookChars($item->id);
    				$item->clist = $item->ccnt==0 ? '' : XbcultureHelper::makeItemLists($item->chars,'char','tn',5,'cpvmodal');				    
				}
								
				if ($item->gcnt>0) {
				    $item->groups = XbbooksGeneral::getBookGroups($item->id);
				    $item->grouplist = XbcultureHelper::makeItemLists($item->groups,'','rtn',5,'gpvgmodal');
				}
				
				//order by review rating or date?
				$item->reviews = XbbooksGeneral::getBookReviews($item->id);
				$item->revcnt = count($item->reviews);
			} //end if loadobject			
            return $this->item;			
		} //end if item not ok				
	} //end getitem()
	
}
