<?php
/*******
 * @package xbBooks
 * @filesource site/models/book.php
 * @version 0.9.9.8 21st October 2022
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
				a.state AS published, a.catid AS catid, a.params AS params, a.metadata AS metadata ');
			$query->from('#__xbbooks AS a');
			$query->select('(SELECT AVG(br.rating) FROM #__xbbookreviews AS br WHERE br.book_id=a.id) AS averat');
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
				$item->people = XbbooksGeneral::getBookRolesArray($item->id,'',false);
				$cnts = array_count_values(array_column($item->people, 'role'));
				$item->authcnt = (key_exists('author',$cnts))? $cnts['author'] : 0;
				$item->editcnt = (key_exists('editor',$cnts))? $cnts['editor'] : 0;
				$item->mencnt = (key_exists('mention',$cnts))? $cnts['mention'] : 0;
				$item->othcnt = (key_exists('other',$cnts))? $cnts['other'] : 0;
				
				$item->chars = XbbooksGeneral::getBookCharsArray($item->id);
				$item->charcnt = count($item->chars);
				
				
				//make author/editor/char lists
				if ($item->authcnt==0){
					$item->alist = ''; //'<i>'.JText::_( 'XBBOOKS_NOAUTHOR' ).'</i>';
				} else {
					$item->alist = XbbooksGeneral::makeLinkedNameList($item->people,'author',',', (($item->editcnt)==0)? true:false);
				}
				if (($item->editcnt)==0){
					$item->elist = '';
				} else {
					$item->elist = ': '.XbbooksGeneral::makeLinkedNameList($item->people,'editor',',');
				}
				if (($item->charcnt)==0){
					$item->clist = '';
				} elseif ($item->charcnt < 4) {
					$item->clist = XbbooksGeneral::makeLinkedNameList($item->chars,'','<br />',true, false);
				} else {
					$item->clist = XbbooksGeneral::makeLinkedNameList($item->chars,'',', ',false);
				}
				if (($item->mencnt)==0){
				    $item->mlist = '';
				} elseif ($item->mencnt < 4) {
				    $item->mlist = XbbooksGeneral::makeLinkedNameList($item->people,'mention','<br />',true, false);
				} else {
				    $item->mlist = XbbooksGeneral::makeLinkedNameList($item->people,'mention',', ',false);
				}
				if (($item->othcnt)==0){
				    $item->olist = '';
				} else {
				    $item->olist = XbbooksGeneral::makeLinkedNameList($item->people,'other','<br />',true, false, 1);
				}
				
				//order by review rating or date?
				$item->reviews = XbbooksGeneral::getBookReviews($item->id);
				$item->revcnt = count($item->reviews);
			} //end if loadobject			
            return $this->item;			
		} //end if item not ok				
	} //end getitem()
	
}
