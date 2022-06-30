<?php 
/*******
 * @package xbBooks
 * @filesource site/views/person/view.html.php
 * @version 0.9.9.0 30th June 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;

class XbbooksViewPerson extends JViewLegacy {
	
	protected $item;
	
	public function display($tpl = null) {
		$this->item 		= $this->get('Item');
		$this->state		= $this->get('State');
		$this->params      = $this->state->get('params');
		
		$this->hide_empty = $this->params->get('hide_empty',1);
		$this->show_image = $this->params->get('show_pimage',1);
		
		$this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
		$show_cats = ($this->xbpeople_ok) ? $this->params->get('show_cats','1','int') : 0;
		$this->show_cat = ($show_cats) ? $this->params->get('show_pcat','2','int') :0;
		$show_tags = $this->params->get('show_tags','1','int');
		$this->show_tags = ($show_tags) ? $this->params->get('show_ptags','1','int') : 0;
				
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
			return false;
		}
		
		$this->booklist = '';
		if ($this->item->bcnt>0) {
		    $role = '';
		    foreach ($this->item->books as $book) {
		        if ($role != $book->role) {
		            if ($role != '') {
		                $this->booklist .= '</ul>';
		            }
		            $role = $book->role;
		            $roletext = '';
		            switch ($role) {
		                case 'mention':
		                    $roletext = Text::_('XBCULTURE_APPEARS_IN').' '.Text::_('XBCULTURE_AS_CHAR_SUBJ');
		                    break;
		                case 'other':
		                    $roletext = Text::_('XBCULTURE_OTHER_ROLE');
		                    break;
		                default:
		                    $roletext = 'as '.ucfirst($book->role);
		                    break;
		            }
		            $this->booklist .= '<i>'.$roletext.'</i><ul>';
		        }
		        $this->booklist .= '<li>'.$book->link;
		        if ($book->role_note!='') {
		            $this->booklist .= ' <i>('. $book->role_note.')</i>';
		        }
		        $this->booklist .= '</li>';
		    }
		    $this->booklist .= '</ul>';
		}
		$app = Factory::getApplication();
		$srt = $app->getUserState('people.sortorder');
		if (!empty($srt)) {
			$i = array_search($this->item->id, $srt);
			if ($i<count($srt)-1) {
				$this->item->next = $srt[$i+1];
			} else { $this->item->next = 0; }
			if ($i>0) {
				$this->item->prev = $srt[$i-1];
			} else { $this->item->prev = 0; }
			
		} else {
			$this->item->prev = 0;
			$this->item->next = 0;
		}
		
		$tagsHelper = new TagsHelper;
		$this->item->tags = $tagsHelper->getItemTags('com_xbpeople.person' , $this->item->id);

		$document = $this->document; //Factory::getDocument();
		$document->setTitle($this->item->firstname.' '.$this->item->lastname);
		$document->setMetaData('title', Text::_('XBCULTURE_PERSON_CATALOGUE').' '.$this->item->firstname.' '.$this->item->lastname);
		$metadata = json_decode($this->item->metadata,true);
		if (!empty($metadata['metadesc'])) { $document->setDescription($metadata['metadesc']); }
		if (!empty($metadata['metakey'])) { $document->setMetaData('keywords', $metadata['metakey']);}
		if (!empty($metadata['rights'])) { $document->setMetaData('rights', $metadata['rights']);}
		if (!empty($metadata['robots'])) { $document->setMetaData('robots', $metadata['robots']);}
		if (!empty($metadata['author'])) { $document->setMetaData('author', $metadata['author']);}
		
		
		parent::display($tpl);
	} // end function display()
	
	
}