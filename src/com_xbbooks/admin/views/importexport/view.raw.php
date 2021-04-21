<?php
/*******
 * @package xbBooks
 * @filesource admin/views/importexport/view.raw.php
 * @version 0.5.8a 3rd November 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
//jimport( 'joomla.application.component.view');

class XbbooksViewImportexport extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe;
        $file = $this->get('Export');
        $ext= $this->get('ExportExt');
        $tbl= $this->get('ExportTable');
        
		$doc = Factory::getDocument();
		$doc->setMimeEncoding('text/plain');

		$filename="xbbooks-export-".$tbl."-".date('Y-m-d_H-i-s').$ext;
		$ext=null;
		JResponse::setHeader('Content-disposition', 'attachment'.'; filename='.$filename);
	}
}
?>
