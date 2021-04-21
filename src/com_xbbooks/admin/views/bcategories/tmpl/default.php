<?php
/*******
 * @package xbBooks
 * @filesource admin/views/bcategories/tmpl/default.php
 * @version 0.9.2 9th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/	
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getUser();
$userId         = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$cvlink = 'index.php?option=com_xbbooks&view=bcategory&id=';
$bvlink = 'index.php?option=com_xbbooks&view=books&catid=';
$rvlink = 'index.php?option=com_xbbooks&view=reviews&catid=';
$pvlink = 'index.php?option=com_xbbooks&view=persons&catid=';
$chvlink = 'index.php?option=com_xbbooks&view=characters&catid=';
$pplink = 'index.php?option=com_xbpeople&view=persons&catid=';
$chplink = 'index.php?option=com_xbpeople&view=characters&catid=';

$prevext ='';

?>
<form action="index.php?option=com_xbbooks&view=bcategories" method="post" id="adminForm" name="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
	<?php else : ?>
        <div id="j-main-container">
	<?php endif;?>
	
	<div>
      <h3><?php echo Text::_('COM_XBBOOKS_CATSPAGE_TITLE'); ?></h3>
      <?php if (XbcultureHelper::checkComponent('com_xbfilms') !== false) : ?>
      	<p class="xbnote"><?php echo Text::_('COM_XBBOOKS_CATSPAGE_SUBTITLE'); ?></p>
      <?php endif; ?>      
      	<p class="xb095"><?php echo Text::_('COM_XBBOOKS_CATSPAGE_SUBTITLE2'); ?></p>
    </div>
	
	<div class="pull-right span2">
		<p style="text-align:right;">
			<?php $fnd = $this->pagination->total;
			echo $fnd .' '. Text::_(($fnd==1)?'XBCULTURE_CATEGORY':'XBCULTURE_CATEGORIES').' '.Text::_('XBCULTURE_FOUND'); ?>
		</p>
	</div>
	<div class="clearfix"></div>

	<?php
        // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clearfix"></div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	

<table class="table table-striped table-hover">
<thead>
<tr>
					<th class="hidden-phone center" style="width:25px;">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
			<th width="5%">
				<?php echo Text::_('JSTATUS'); ?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'XBCULTURE_CAPCATEGORY', 'path', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo Text::_('XBCULTURE_CAPDESCRIPTION') ;?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'XBCULTURE_CAPBOOKS', 'bcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'XBCULTURE_CAPREVIEWS', 'rcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'XBCULTURE_CAPPEOPLE', 'pcnt', $listDirn, $listOrder );?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'XBCULTURE_CAPCHARACTERS', 'chcnt', $listDirn, $listOrder );?>
			</th>
			<th class="nowrap hidden-tablet hidden-phone" style="width:45px;">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder );?>
			</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canCheckin = $user->authorise('core.manage', 'com_checkin');
				if ($prevext != $item->extension) {
					switch ($item->extension) {
						case 'com_xbbooks':
							$section = 'xbBooks Categories <span class="xbnit xb09"> - for Books and Book Reviews</span>' ;
							break;
						case 'com_xbpeople':
							$section = 'xbPeople Categories <span class="xbnit xb09"> - grey counts all xbCulture components, coloured counts books only</span>';
							break;
						default:
							$section = $item->extension;
						break;
					}
					echo '<tr><td colspan="10" class="xb12 xbbold">'.$section.'</td></tr>';
					$prevext = $item->extension;
				}
				?>
			<tr class="row<?php echo $i % 2; ?>" >	
					<td class="center hidden-phone">
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
				<td class="center">
					<div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'category.', false, 'cb'); ?>
							<?php if ($item->note!=''){ ?>
								<span class="btn btn-micro active hasTooltip" title="" 
									data-original-title="<?php echo '<b>'.Text::_( 'XBCULTURE_CAPNOTE' ) .'</b>: '. htmlentities($item->note); ?>">
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
    					echo HTMLHelper::_('jgrid.checkedout', $i, Text::_('XBCULTURE_OPENED_BY').': '.$couname, $item->checked_out_time, 'categories.', false);
    				} ?>
					<span class="xbnote"> 
 					<?php 	$path = substr($item->path, 0, strrpos($item->path, '/'));
						$path = str_replace('/', ' - ', $path);
						echo $path.($path!='') ? ' - <br/>' : ''; ?>
						</span>
    					<a href="<?php echo JRoute::_($cvlink . $item->id); ?>" title="Details" 
    						class="label label-success" style="padding:2px 8px;">
    						<span class="xb11"><?php echo $item->title; ?></span>
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
   						<span class="badge">
   							<a href="<?php echo $pplink.$item->id;?>"><?php echo $item->pcnt; ?>
   						</a></span>
   						<span class="badge percnt">
   							<a href="<?php echo $pvlink.$item->id;?>"><?php echo $item->bpcnt; ?>
   						</a></span>
   					<?php endif; ?>
   				</td>
    			<td align="center">
   					<?php if ($item->chcnt >0) : ?>
   						<span class="badge">
   							<a href="<?php echo $chplink.$item->id;?>"><?php echo $item->chcnt; ?>  						
   						</a></span>
   						<span class="badge chcnt">
   							<a href="<?php echo $chvlink.$item->id;?>"><?php echo $item->bchcnt; ?>  						
   						</a></span>
   					<?php endif; ?>
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
<p><?php echo XbbooksGeneral::credit();?></p>

