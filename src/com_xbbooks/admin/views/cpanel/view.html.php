<?php
/*******
 * @package xbBooks
 * @filesource admin/views/cpanel/view.html.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

class XbbooksViewCpanel extends JViewLegacy
{
 //   protected $buttons;
	protected $books;
	protected $categories;
	protected $authors;
	protected $editors;
 
	public function display($tpl = null) {
	    $this->xbpeople_ok = Factory::getSession()->get('xbpeople_ok');
	    $this->xbfilms_ok = Factory::getSession()->get('xbfilms_ok');
	    $this->xblive_ok = Factory::getSession()->get('xblive_ok');
	    
	    if ($this->xbpeople_ok) {
	        $this->bookStates = $this->get('BookStates');
    		$this->catStates = $this->get('CatStates');
    		$this->pcatStates = $this->get('PcatStates');
    		$this->revStates = $this->get('RevStates');
    		$this->perStates = $this->get('PerStates');
    		$this->charStates = $this->get('CharStates');
    		$this->totPeople = XbcultureHelper::getItemCnt('#__xbpersons');
    		$this->totChars = XbcultureHelper::getItemCnt('#__xbcharacters');
    		$this->otherRoles = $this->get('OtherRoles');
    		
    		$this->orphanrevs = $this->get('OrphanReviews');
    		$this->orphanpeep = $this->get('OrphanPeople');
    		$this->orphanchars = $this->get('OrphanChars');
    		
    		$this->books = $this->get('BookCnts');  //getting fiction and nonfiction and reviews
    		$this->ratCnts = $this->get('RatCnts'); 
    		$this->people = $this->get('RoleCnts');
    		$this->cats = $this->get('Cats');
    		$this->pcats = $this->get('PeopleCats');
    		
    		$this->tags = $this->get('Tagcnts');
    		$this->xmldata = Installer::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR . '/xbbooks.xml');
    		$this->client = $this->get('Client');
    		
    		$params = ComponentHelper::getParams('com_xbbooks');
    		$this->show_sample = $params->get('show_sample');
    		$this->zero_rating = $params->get('zero_rating');
    		$this->zero_class = $params->get('zero_class');

    		$this->show_cat = $params->get('show_cats');
    		$this->show_bookcat = $params->get('show_bcat');
    		$this->show_revcat = $params->get('show_rcat');
    		$this->show_percat = $params->get('show_pcat');
    		
    		$this->show_tags = $params->get('show_tags');
    		$this->show_booktags = $params->get('show_btags');
    		$this->show_revtags = $params->get('show_rtags');
    		$this->show_pertags = $params->get('show_ptags');
    		
    		$this->show_search = $params->get('search_bar');
    		
    		$this->hide_empty = $params->get('hide_empty');
    		
    		$this->covers = $params->get('cover_path');
    		$this->portraits = $params->get('portrait_path');
    		$this->show_booklist_covers = $params->get('show_bpiccol');
    		$this->show_book_cover = $params->get('show_bimage');
    		$this->show_review_cover = $params->get('show_rimage');
    		
    		$this->show_people_portraits = $params->get('show_ppiccol');
    		$this->show_person_portrait = $params->get('show_pimage');
    		
    		$this->show_booklist_rating = $params->get('show_brevcol');
    		$this->show_book_review = $params->get('show_brevs');
    		
    		XbbooksHelper::addSubmenu('cpanel');
		
            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                throw new Exception(implode("\n", $errors), 500);
            }
    
            $clink='index.php?option=com_xbbooks&view=bcategory&id=';
            $this->catlist = '<ul style="list-style-type: none;">';
            foreach ($this->cats as $key=>$value) {
            	if ($value['level']==1) {
            		$this->catlist .= '<li>';
            	} else {
            		$this->catlist .= str_repeat('-&nbsp;', $value['level']-1);
            	}
            	$this->catlist .='<a class="label label-success" href="'.$clink.$value['id'].'">'.$value['title'].'</a>&nbsp;(<i>'.$value['bookcnt'].':'.$value['revcnt'].'</i>) ';
            	if ($value['level']==1) {
            		$this->catlist .= '</li>';
            	}
            }
            $this->catlist .= '</ul>';
            
            $this->pcatlist = '<ul style="list-style-type: none;">';
            foreach ($this->pcats as $key=>$value) {
            	if ($value['level']==1) {
            		$this->pcatlist .= '<li>';
            	} else {
            		$this->pcatlist .= str_repeat('-&nbsp;', $value['level']-1);
            	}
            	$this->pcatlist .='<a class="label label-success" href="'.$clink.$value['id'].'">'.$value['title'].'</a>&nbsp;(<i>'.$value['percnt'].':'.$value['chrcnt'].'</i>) ';
            	if ($value['level']==1) {
            		$this->pcatlist .= '</li>';
            	}
            }
            $this->pcatlist .= '</ul>';
            
            $tlink='index.php?option=com_xbbooks&view=tag&id=';
            $this->taglist = '<ul class="inline">';
            foreach ($this->tags['tags'] as $key=>$value) {
            	//       	$result[$key] = $t->tagcnt;
                $this->taglist .= '<li><a class="label label-info" href="'.$tlink.$value['id'].'">'.$key.'</a>&nbsp;(<i>'.$value['tbcnt'].':'.$value['trcnt'].':'.$value['tpcnt'].':'.$value['tccnt'].')</i></li> ';
            }
            $this->taglist .= '</ul>';
            //        $result['taglist'] = trim($result['taglist'],', ');
            $this->sidebar = JHtmlSidebar::render();
	    }
        
        $this->addToolbar();
        parent::display($tpl);
        // Set the document
        $this->setDocument();
	}

    protected function addToolbar() {
        $canDo = XbbooksHelper::getActions();
        $bar = Toolbar::getInstance('toolbar');
        
        ToolBarHelper::title(JText::_( 'XBBOOKS' ).': '.JText::_('COM_XBBOOKS_TITLE_CPANEL'),'info-2');
        
        if ($this->xbpeople_ok) {
	        $samplesexist = XbbooksHelper::getIdFromAlias('#__categories', 'sample-books');
	        if ($this->show_sample==1) {
	        	if ($samplesexist > 0) {
	        		ToolBarHelper::custom('cpanel.unsample', 'file-minus', '', 'COM_XBBOOKS_REMOVE_SAMPLE', false) ;
	        	} else {
	        		ToolBarHelper::custom('cpanel.sample', 'file-plus', '', 'COM_XBBOOKS_INSTALL_SAMPLE', false) ;
	        	}
	        	ToolbarHelper::custom(); //spacer
	        }
	       	ToolbarHelper::custom('cpanel.people', 'info-2', '', 'xbPeople', false) ;
	        
	        ToolBarHelper::custom('cpanel.films', 'screen', '', 'xbFilms', false) ;
	        ToolBarHelper::custom('cpanel.live', 'music', '', 'xbLive', false) ;
	        if ($canDo->get('core.admin')) {
	            ToolBarHelper::preferences('com_xbbooks');
	        }
	        ToolBarHelper::help( '', false,'https://crosborne.uk/xbbooks/doc?tmpl=component#admin-cpanel' );
        } else {
        	ToolBarHelper::help( '', false,'https://www.crosborne.uk/downloads/file/11-xbpeople-component?tmpl=component' );
        }
    }
    
    protected function setDocument() {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_XBBOOKS_ADMIN_CPANEL'));
    }
}
