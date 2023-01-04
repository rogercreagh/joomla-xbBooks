<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/tmpl/modal.php
 * @version 0.9.3 12th April 2021
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
<div class="modal-body xbml20 xbmr20">
	<iframe src="index.php?option=com_xbpeople&view=person&layout=qnew&tmpl=component" title="Quick Person Form" id="newp"></iframe>      
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary"  onclick="document.getElementById('newp').contentWindow.Joomla.submitbutton('person.save');" data-dismiss="modal">Save &amp; Close</button>
</div>

