<?php
/*******
 * @package xbBooks
 * @filesource admin/views/characters/tmpl/default_batch_footer.php
 * @version 0.6.1d 13th November 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;
?>
<a class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('character.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>