<?php
/*******
 * @package xbBooks
 * @filesource admin/views/reviews/tmpl/default_batch_body.php
 * @version 0.3.2e 4th July 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>

<div class="container-fluid">

	<div class="row-fluid">

		<div class="control-group span6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.item', array('extension' => 'com_xbbooks')); ?>
			</div>
		</div>

		<div class="control-group span6">
		<!-- 
			<div class="controls">
				<?php // echo JLayoutHelper::render('joomla.html.batch.language', array()); ?>
			</div>
		 -->
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.access', array()); ?>
			</div>
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.tag', array()); ?>
			</div>
		</div>

	</div>
	
</div>