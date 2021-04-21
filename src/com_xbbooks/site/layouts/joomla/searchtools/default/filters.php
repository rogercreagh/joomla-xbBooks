<?php 
/*******
 * @package xbBooks
 * @filesource site/layouts/joomla/searchtools/default/filters.php
 * @version 0.5.4c 22nd October 2020
 * @desc adds labels to the filter fields
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$hide = $data['hide'];
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ($fieldName !== 'filter_search') : ?>
		<?php if (strpos($hide, $fieldName) === false) : ?>
			<?php $dataShowOn = ''; ?>			
			<?php if ($field->showon) : ?>
				<?php JHtml::_('jquery.framework'); ?>
				<?php JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
				<?php $dataShowOn = " data-showon='" . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . "'"; ?>
			<?php endif; ?>
			<div class="js-stools-field-filter" <?php echo $dataShowOn; ?> >
		       <?php echo $field->label; ?>
				<?php echo $field->input; ?>
 			</div>
 		<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

