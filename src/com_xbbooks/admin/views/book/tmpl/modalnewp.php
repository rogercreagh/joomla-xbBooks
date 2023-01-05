<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/tmpl/modalnewp.php
 * @version 1.0.1.3 5th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
     <h4 class="modal-title">Quick New Person</h4>

</div>
<div class="modal-body">
    <div style="margin:0 30px;">
		<iframe src="index.php?option=com_xbpeople&view=person&layout=qnew&tmpl=component" title="Quick Person Form" id="newp"></iframe>      
	</div>    
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary"  onclick="document.getElementById('newp').contentWindow.Joomla.submitbutton('person.save');" data-dismiss="modal">Save &amp; Close</button>
</div>

