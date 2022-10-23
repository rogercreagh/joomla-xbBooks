<?php
/*******
 * @package xbBooks
 * @filesource admin/views/tags/tmpl/default.php
 * @version 0.9.9.8 23rd October 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getUser();
$userId         = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$telink = 'index.php?option=com_tags&view=tag&task=tag.edit&id=';
$tvlink = 'index.php?option=com_xbbooks&view=tag&id=';
$bvlink = 'index.php?option=com_xbbooks&view=books&tagid=';
$rvlink = 'index.php?option=com_xbbooks&view=reviews&tagid=';
$pvlink = 'index.php?option=com_xbbooks&view=persons&tagid=';
$chvlink = 'index.php?option=com_xbbooks&view=characters&tagid=';
?>
<form action="index.php?option=com_xbbooks&view=tags" method="post" id="adminForm" name="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
	<?php else : ?>
        <div id="j-main-container">
	<?php endif;?>
	
	<div>
      <h3><?php echo JText::_('XBBOOKS_TAGSPAGE_TITLE'); ?></h3>
      <p><?php echo JText::_('XBBOOKS_TAGSPAGE_INFO'); ?></p>
      </div>
	
	<div class="pull-right span2">
		<p style="text-align:right;">
			<?php $fnd = count($this->items);
			echo $fnd .' '. JText::_(($fnd==1)?'XBCULTURE_TAG':'XBCULTURE_TAGS').' '.JText::_('XBCULTURE_FOUND'); ?>
		</p>
	</div>
	<div class="clearfix"></div>

	<?php
        // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clearfix"></div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	

<table class="table table-striped table-hover">
<thead>
<tr>
					<th class="hidden-phone center" style="width:25px;">
						<?php echo HtmlHelper::_('grid.checkall'); ?>
					</th>
			<th width="5%">
				<?php echo HtmlHelper::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_TAG_U', 'path', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo JText::_('XBCULTURE_DESCRIPTION') ;?>
			</th>
			<th>
				<?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_BOOKS_U', 'bcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_REVIEWS_U', 'rcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_PEOPLE_U', 'pcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HtmlHelper::_('grid.sort', 'XBCULTURE_CHARACTERS_U', 'chcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo JText::_('XBCULTURE_OTHERS') ;?>
			</th>
			<th class="nowrap hidden-tablet hidden-phone" style="width:45px;">
				<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder );?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canCheckin = $user->authorise('core.manage', 'com_checkin')
			?>
			<tr class="row<?php echo $i % 2; ?>" >	
					<td class="center hidden-phone">
						<?php echo HtmlHelper::_('grid.id', $i, $item->id); ?>
					</td>
				<td class="center">
					<div class="btn-group">
						<?php echo HtmlHelper::_('jgrid.published', $item->published, $i, 'tag.', false, 'cb'); ?>
							<?php if ($item->note!=''){ ?>
								<span class="btn btn-micro active hasTooltip" title="" 
									data-original-title="<?php echo '<b>'.JText::_( 'XBCULTURE_NOTE' ) .'</b>: '. htmlentities($item->note); ?>">
									<i class="icon- xbinfo"></i>
								</span>
							<?php } else {?>
								<span class="btn btn-micro inactive" style="visibility:hidden;" title=""><i class="icon-info"></i></span>
							<?php } ?>
					</div>
				</td>
 				<td>
					<?php if ($item->checked_out) {
    					$couname = Factory::getUser($item->checked_out)->username;
    					echo HtmlHelper::_('jgrid.checkedout', $i, JText::_('XBCULTURE_OPENEDBY').': '.$couname, $item->checked_out_time, 'tags.', false);
    				} ?>
					<span class="xbnote"> 
 					<?php 	$path = substr($item->path, 0, strrpos($item->path, '/'));
						$path = str_replace('/', ' - ', $path);
						echo $path; ?>
					  - </span>    				
    				<a href="<?php echo Route::_($tvlink . $item->id); ?>" title="Details" 
    					class="label label-info" style="padding:4px 8px;">
    					<span class="xb12"><?php echo $item->title; ?></span>
    				</a>
    			</td>
    			<td class="xb09">
    				<?php echo $item->description; ?>
    			</td>
    			<td align="center">
   					<?php if ($item->bcnt >0) : ?> 
   						<span class="badge bkcnt">
   							<a href="<?php echo $bvlink.$item->id;?>"><?php echo $item->bcnt; ?>
   						</a></span>
   					<?php endif; ?>
   				</td>
    			<td align="center">
   					<?php if ($item->rcnt >0) : ?> 
   						<span class="badge revcnt">
   							<a href="<?php echo $rvlink.$item->id;?>"><?php echo $item->rcnt; ?>
   						</a></span>
   					<?php endif; ?>
   				</td>
    			<td align="center">
   					<?php if ($item->pcnt >0) : ?> 
   						<span class="badge percnt">
   							<a href="<?php echo $pvlink.$item->id;?>"><?php echo $item->pcnt; ?>
   						</a></span>
   					<?php endif; ?>
   				</td>
    			<td align="center">
   					<?php if ($item->chcnt >0) : ?>
   						<span class="badge chcnt">
   							<a href="<?php echo $chvlink.$item->id;?>"><?php echo $item->chcnt; ?>  						
   						</a></span>
   					<?php endif; ?>
   				</td>
    			<td align="center">
   					<?php if ($item->othcnt >0) : ?>
   						<span class="badge othcnt">
   							<a href="<?php echo $tvlink.$item->id;?>"><?php echo $item->othcnt; ?>  						
   						</a></span>
   					<?php endif; ?>
   				</td>
  				<td align="center">
					<?php echo $item->id; ?>
				</td>


  				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>

