<?php 
/*******
 * @package xbBooks
 * @filesource site/views/tags/tmpl/default.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
HtmlHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$xblink = 'index.php?option=com_xbbooks&view=';

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getTagsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$tvlink = $xblink.'tag'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getPeopleRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$pllink = $xblink.'people'.$itemid.'&tagid=';

$itemid = XbbooksHelperRoute::getBooksRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$bllink = $xblink.'booklist'.$itemid.'&tagid=';

$itemid = XbbooksHelperRoute::getReviewsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$rllink = $xblink.'bookreviews'.$itemid.'&tagid=';

$itemid = XbbooksHelperRoute::getCharsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$chllink = $xblink.'characters'.$itemid.'&tagid=';

?>

<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
		echo XbbooksHelper::sitePageheader($this->header);
	} ?>
	
	<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=tags'); ?>" method="post" name="adminForm" id="adminForm">
	
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped table-hover" style="table-layout:fixed;" id="xbtags">	
			<thead>
				<tr>
					<th><?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_TAG_U', 'title', $listDirn, $listOrder );?></th>
				<?php  if ($this->show_desc != 0) : ?>      
					<th class="hidden-phone"><?php echo JText::_('XBCULTURE_DESCRIPTION');?></th>
				<?php endif; ?>
				<th class="center" style="width:50px;"><?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_BOOKS_U', 'bcnt', $listDirn, $listOrder );?></th>
				<th class="center" style="width:50px;"><?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_REVIEWS_U', 'rcnt', $listDirn, $listOrder );?></th>
				<th class="center" style="width:50px;"><?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_PEOPLE_U', 'pcnt', $listDirn, $listOrder );?></th>
				<th class="center" style="width:50px;"><?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_CHARACTERS_U', 'chcnt', $listDirn, $listOrder );?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
				<tr>
	 				<td>
						<p class="xbml10">
 						<?php  if ($this->show_parent != 0) : ?>
 						    <span class="xbnote xb09">
 						    <?php if (substr_count($item->path,'/')>0) {
 						    	$ans = substr($item->path, 0, strrpos($item->path, '/'));
 						    	echo str_replace('/',' - ',$ans).' - ';
 						    } ?>
                        	</span>
						<?php endif; //show_parent?>
	    				<span  class="xb11 xbbold">
	    					<a href="<?php echo Route::_($tvlink . $item->id); ?>" title="Details">
	    						<?php echo $item->title; ?>
	    					</a>
	    				</span>
	    				</p>
	    			</td>
				<?php  if ($this->show_desc != 0) : ?>      
					<td class="hidden-phone"><?php echo $item->description; ?></td>
				<?php endif; ?>
	    			<td class="center">
	   					<?php if ($item->bcnt >0) : ?> 
	   						<a class="badge bkcnt" href="<?php  echo $bllink.$item->id; ?>"><?php echo $item->bcnt; ?></a>
	   					<?php endif; ?>
	   				</td>
	    			<td class="center">
	   					<?php if ($item->rcnt >0) : ?> 
	   						<a class="badge revcnt" href="<?php  echo $rllink.$item->id; ?>"><?php echo $item->rcnt; ?></a>
	   					<?php endif; ?>
	   				</td>
	    			<td class="center">
	   					<?php if ($item->pcnt >0) : ?> 
	   						<a class="badge percnt" href="<?php  echo $pllink.$item->id; ?>"><?php echo $item->pcnt; ?></a>
	   					<?php endif; ?>
	   				</td>
	    			<td class="center">
	   					<?php if ($item->chcnt >0) : ?> 
	   						<a class="badge percnt" href="<?php  echo $chllink.$item->id; ?>"><?php echo $item->chcnt; ?></a>
	   					<?php endif; ?>
	   				</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo HtmlHelper::_('form.token'); ?>
	</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
</div>
