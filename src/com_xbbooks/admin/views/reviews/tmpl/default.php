<?php
/*******
 * @package xbBooks
 * @filesource admin/views/reviews/tmpl/default.php
 * @version 0.9.6.d 7th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
	$listOrder='rev_date';
	$listDirn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'),'booktitle'=>Text::_('COM_XBBOOKS_BOOKTITLE'),
		'id'=>'id','rev_date'=>Text::_('XBCULTURE_DATES'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),
		'published'=>Text::_('XBCULTURE_PUBLISHED'),'ordering'=>Text::_('XBCULTURE_ORDERING'),
		'rating'=>Text::_('XBCULTURE_RATING')
);

$saveOrder      = $listOrder == 'ordering';
$canOrder       = $user->authorise('core.edit.state', 'com_xbbooks.book');
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_xbbooks&task=reviews.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'xbbookreviewsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$belink = 'index.php?option=com_xbbooks&view=book&task=book.edit&id=';
$bvlink = 'index.php?option=com_xbbooks&view=book&task=book.edit&id='; //change this to view view when available
$relink = 'index.php?option=com_xbbooks&view=review&task=review.edit&id=';
$rvlink = 'index.php?option=com_xbbooks&view=review&task=review.edit&id='; //change this to view view when available
$celink = 'index.php?option=com_categories&task=category.edit&id=';
$cvlink = 'index.php?option=com_xbbooks&view=bcategory&id=';
$telink = 'index.php?option=com_tags&view=tag&task=tag.edit&id=';
$tvlink = 'index.php?option=com_xbbooks&view=tag&id=';

?>
<form action="index.php?option=com_xbbooks&view=reviews" method="post" id="adminForm" name="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
	<?php else : ?>
        <div id="j-main-container">
	<?php endif;?>
	<div class="pull-right span2">
		<p style="text-align:right;">
			<?php $fnd = $this->pagination->total;
			echo $fnd .' '. Text::_(($fnd==1)?'XBCULTURE_REVIEW':'XBCULTURE_REVIEWS').' '.Text::_('XBCULTURE_FOUND');
			?>
			</p>
	</div>
	<div class="clearfix"></div>
	<?php
        // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clearfix"></div>
	<?php $search = $this->searchTitle; ?>

	<?php if ($search) {
		echo '<p>Searched for <b>'; 
		if (stripos($search, 'i:') === 0) {
            echo trim(substr($search, 2)).'</b> '.Text::_('XBCULTURE_AS_ID');
		} elseif ((stripos($search, 's:') === 0) || (stripos($search, 'r:') === 0)) {
            echo trim(substr($search, 2)).'</b> '.Text::_('COM_XBBOOKS_AS_INREV');
        } else {
			echo trim($search).'</b> '.Text::_('COM_XBBOOKS_AS_INNAMES');
		}
		echo '</p>';
	} ?> 

	<div class="pagination">
		<?php  echo $this->pagination->getPagesLinks(); ?>
		<br />
	    <?php echo 'sorted by '.$orderNames[$listOrder].' '.$listDirn ; ?>
	</div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	
	<table class="table table-striped table-hover" id="xbbookreviewsList">
		<thead>
			<tr>
				<th class="nowrap center hidden-phone" style="width:25px;">
					<?php echo HTMLHelper::_('searchtools.sort', '', 'ordering', 
					    $listDirn, $listOrder, null, 'asc', 'XBCULTURE_HEADING_ORDERING_DESC', 'icon-menu-2'); ?>
				</th>
        		<th class="hidden-phone" style="width:25px;">
        			<?php echo HTMLHelper::_('grid.checkall'); ?>
        		</th>
        		<th class="nowrap center" style="width:55px">
        			<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
        		</th>
        		<th>
        			<?php echo HTMLHelper::_('searchtools.sort', 'Review Title', 'title', $listDirn, $listOrder); ?>
        		</th>
        		<th>
        			<?php echo HTMLHelper::_('searchtools.sort', 'COM_XBBOOKS_REVIEW_BOOK_U', 'booktitle', $listDirn, $listOrder); ?>
        		</th>
        		<th>
        			<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_RATING', 'rating', $listDirn, $listOrder); ?>
        			&amp;
        			<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_DATE', 'rev_date', $listDirn, $listOrder); ?>
        		</th>
        		<th class="hidden-phone">
        			<?php echo Text::_('COM_XBBOOKS_REVIEW_SUMMARY_LABEL');?>
        		</th>
 					<th class="hidden-tablet hidden-phone" style="width:15%;">
						<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_CATS','category_title',$listDirn,$listOrder ).' &amp; '.
						Text::_( 'XBCULTURE_TAGS_U' ); ?>
					</th>
        		
        		<th class="nowrap hidden-phone" style="width:45px;">
        			<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
        		</th>
        	</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php $star='<span class="icon-star" style="margin-right:0; width:10px; font-size:11px;"></span>';
				?>
				<?php foreach ($this->items as $i => $item) :
    				$canEdit    = $user->authorise('core.edit', 'com_xbbooks.book.'.$item->id);
    				$canCheckin = $user->authorise('core.manage', 'com_checkin')
    				|| $item->checked_out==$userId || $item->checked_out==0;
    				$canEditOwn = $user->authorise('core.edit.own', 'com_xbbooks.book.'.$item->id) && $item->created_by == $userId;
    				$canChange  = $user->authorise('core.edit.state', 'com_xbbooks.book.'.$item->id) && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">	
					<td class="order nowrap center hidden-phone">
                        <?php
                            $iconClass = '';
                            if (!$canChange) {
                                $iconClass = ' inactive';
                            } elseif (!$saveOrder) {
                                $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
                            }
                        ?>
                        <span class="sortable-handler<?php echo $iconClass; ?>">
                        	<span class="icon-menu" aria-hidden="true"></span>
                        </span>
                        <?php if ($canChange && $saveOrder) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                        <?php endif; ?>
					</td>
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'review.', true, 'cb'); ?>
								<?php if ($item->note!=''){ ?>
									<span class="btn btn-micro active hasTooltip" title="" 
										data-original-title="<?php echo '<b>'.Text::_( 'XBCULTURE_NOTE' ) .'</b>: '. htmlentities($item->note); ?>">
										<i class="icon- xbinfo"></i>
									</span>
								<?php } else {?>
									<span class="btn btn-micro inactive" style="visibility:hidden;" title=""><i class="icon-info"></i></span>
								<?php } ?>
							</div>
						</td>
						<td>
    						<p class="xbtitlelist">
    						<?php if ($item->checked_out) {
    						    $couname = Factory::getUser($item->checked_out)->username;
    						    echo HTMLHelper::_('jgrid.checkedout', $i, Text::_('XBCULTURE_OPENEDBY').': '.$couname, $item->checked_out_time, 'review.', $canCheckin);
    						} ?>
    						<a href="<?php echo JRoute::_($relink . $item->id); ?>" title="<?php echo Text::_('COM_XBBOOKS_EDIT_REVIEW'); ?>">
    							<?php echo $item->title; ?>
    						</a>
    						<br /><span class="xb08 xbnorm"><i><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></i></span>
    						</p>
							<p>
								<i>by: </i><b><?php echo $item->reviewer; ?></b> 
							</p>						
						</td>
						<td><?php if ($item->booktitle == '') :  ?>
							<p class="xbnote">Book not found - orphan review</p>
							<?php  else : ?>
								<p><a href="<?php echo JRoute::_($bvlink . $item->bookid); ?>">
	    							<?php echo $item->booktitle; ?>
								</a>
								</p>
							<?php endif; ?>
						</td>
						<td>
							<div style="font-size:10px;width:100%;">
							<?php if (($this->zero_rating) && ($item->rating==0)) {
									echo '<span class="'.$this->zero_class.' "></span>';
								} else {
									echo str_repeat('&#11088',$item->rating);
								} 
							 ?>
							</div>
							<p class="xb09"><i>on: </i><?php echo HtmlHelper::date($item->rev_date , 'd M Y'); ?>
							</p>
						</td>
						<td class="hidden-phone">
						<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
								<?php  echo $item->summary; ?>
    						<?php else : ?>
    							<span class="xbnit">
    							<?php if (!empty($item->review)) : ?>
    								<?php echo Text::_('COM_XBBOOKS_REVIEW_EXTRACT'); ?>: </span>
    								<?php echo XbcultureHelper::makeSummaryText($item->review,250); ?>
    							<?php else : ?>
    								<?php echo Text::_('COM_XBBOOKS_NO_SUMMARY_REVIEW'); ?></span>
    							<?php endif; ?>
    						<?php endif; ?>
                        </p>
                        <?php if ((!empty($item->review)) && (strlen($item->review)>210)) : ?>
                             <p class="xbnit xb09">   
                             <?php 
                             	echo Text::_('COM_XBBOOKS_FULLREVIEW').' '.str_word_count(strip_tags($item->review)).' '.Text::_('XBCULTURE_WORDS'); 
                             ?>
							</p>
						<?php endif; ?>
                                    
						</td>
						<td>
						<p><a  class="label label-success" 	href="<?php echo $cvlink . $item->catid.'&extension=com_xbbooks'; ?>" 
							title="<?php echo Text::_( 'XBCULTURE_VIEW_CATEGORY' );?>::<?php echo $item->category_title; ?>">
								<?php echo $item->category_title; ?>
						</a></p>						
						
						<ul class="inline">
						<?php foreach ($item->tags as $t) : ?>
							<li><a href="<?php echo $tvlink.$t->id; ?>" class="label label-info">
								<?php echo $t->title; ?></a>
							</li>													
						<?php endforeach; ?>
						</ul>						    											
					</td>
						
						</td>
						<td align="center">
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
        <?php // load the modal for displaying the batch options
            echo HTMLHelper::_(
            'bootstrap.renderModal',
            'collapseModal',
            array(
                'title' => Text::_('XBCULTURE_BATCH_TITLE'),
                'footer' => $this->loadTemplate('batch_footer')
            ),
            $this->loadTemplate('batch_body')
        ); ?>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
