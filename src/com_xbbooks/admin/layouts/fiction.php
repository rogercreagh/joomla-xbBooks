<?php
/*******
 * @package xbBooks
 * @filesource admin/layouts/fiction.php
 * @version 0.9.9.6 17th August 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2022
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset>
<hr />
<label id="batch-fiction-lbl" for="batch-fiction" class="modalTooltip" title="
	title="<?php echo HTMLHelper::_('tooltipText', 'Fiction/non-Fiction', 'Set fiction/non-fiction vCategory'); ?>">
	Set Fiction/non-Fiction	
</label>
<select name="batch[fiction]" class= "inputbox" id="batch-fiction">
	<option value=""><?php echo Text::_('No change'); ?></option>
	<option value="2"><?php echo Text::_('XBCULTURE_NONFICTION'); ?></option>
	<option value="3"><?php echo Text::_('XBCULTURE_FICTION'); ?></option>
</select>
</fieldset>
