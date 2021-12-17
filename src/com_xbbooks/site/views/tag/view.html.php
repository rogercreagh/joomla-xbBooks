<?php 
/*******
 * @package xbBooks
 * @filesource site/views/tag/view.html.php
 * @version 0.9.6.a 17th December 2021
 * @since v0.5.1
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class XbbooksViewTag extends JViewLegacy {

	public function display($tpl = null) {
		
		$this->item = $this->get('Item');
		
		$this->state = $this->get('State');
		$this->params = $this->state->get('params');

		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors),'error');
			return false;
		}
		
		$this->hide_empty = $this->params->get('hide_empty','','int');
		
		$document = $this->document; //Factory::getDocument();
		$document->setTitle('Tag view: '.$this->item->title);
		$document->setMetaData('title', Text::_('XBCULTURE_TITLE_TAGMANAGER').' '.$this->item->title);
		$metadata = json_decode($this->item->metadata,true);
		if (!empty($metadata['metadesc'])) { $document->setDescription($metadata['metadesc']); }
		if (!empty($metadata['metakey'])) { $document->setMetaData('keywords', $metadata['metakey']);}
		if (!empty($metadata['rights'])) { $document->setMetaData('rights', $metadata['rights']);}
		if (!empty($metadata['robots'])) { $document->setMetaData('robots', $metadata['robots']);}
		if (!empty($metadata['author'])) { $document->setMetaData('author', $metadata['author']);}
		
		
		parent::display($tpl);
	}
	
}

