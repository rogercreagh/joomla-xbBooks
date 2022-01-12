<?php
/*******
 * @package xbBooks
 * @filesource site/models/person.php
 * @version 0.9.7 11th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class XbbooksModelPerson extends JModelItem {
	
	protected $xbfilmsStatus;
	
	public function __construct($config = array()) {
		$this->xbfilmsStatus = XbcultureHelper::checkComponent('com_xbfilms');
		parent::__construct($config);
	}
	
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
			$query->select('a.id AS id, a.firstname AS firstname, a.lastname AS lastname, a.portrait AS portrait, 
				a.summary AS summary, a.biography AS biography, a.year_born AS year_born, a.year_died AS year_died,
				a.nationality AS nationality, a.ext_links AS ext_links, 
				a.state AS published, a.catid AS catid, a.params AS params, a.metadata AS metadata  ');
			$query->from('#__xbpersons AS a');
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
				$item->ext_links = json_decode($item->ext_links);
				$item->ext_links_list ='';
				$item->ext_links_cnt = 0;
				if(is_object($item->ext_links)) {
					$item->ext_links_cnt = count((array)$item->ext_links);
					$item->ext_links_list = '<ul>';
					foreach($item->ext_links as $lnk) {
						$item->ext_links_list .= '<li><a href="'.$lnk->link_url.'" '.$target.'>'.$lnk->link_text.'</a></li>';
					}
					$item->ext_links_list .= '</ul>';
				}
				$item->books = XbbooksGeneral::getPersonRoleArray($item->id);
				$item->bcnt = count($item->books);
				$cnts = array_count_values(array_column($item->books, 'role'));
				$item->acnt = (key_exists('author',$cnts))?$cnts['author'] : 0;
				$item->ecnt = (key_exists('editor',$cnts))?$cnts['editor'] : 0;
				$item->mcnt = (key_exists('mention',$cnts))?$cnts['mention'] : 0;
				$item->ocnt = (key_exists('other',$cnts))?$cnts['other'] : 0;
				
				//make author/editor/char lists
				if ($item->acnt == 0){
					$item->alist = '';
				} else {
					$item->alist = XbbooksGeneral::makeLinkedNameList($item->books,'author','<br />', true, false, 2);
				}
				if ($item->ecnt == 0){
					$item->elist = '';
				} else {
					$item->elist = XbbooksGeneral::makeLinkedNameList($item->books,'editor','<br />',true, false, 2);
				}
				if ($item->mcnt == 0){
				    $item->mlist = '';
				} else {
				    $item->mlist = XbbooksGeneral::makeLinkedNameList($item->books,'mention','<br />',true, false, 2);
				}
				if ($item->ocnt == 0){
				    $item->olist = '';
				} else {
				    $item->olist = XbbooksGeneral::makeLinkedNameList($item->books,'other','<br />',true, false, 1);
				}
				
				$item->filmcnt = 0;
				if ($this->xbfilmsStatus===true) {
					$db    = Factory::getDbo();
					$query = $db->getQuery(true);
					$query->select('COUNT(*)')->from('#__xbfilmperson');
					$query->where('person_id = '.$db->quote($item->id));
					$db->setQuery($query);
					$item->filmcnt = $db->loadResult();
				}
				
			}
		}
		return $this->item;
	}
}
	
