<?php
/*******
 * @package xbBooks
 * @filesource admin/views/groups/tmpl/default.php
 * @version 1.0.4.0 3rd February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_TAGS')));
HTMLHelper::_('formbehavior.chosen', '.multipleCats', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_CATS')));
HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
	$listOrder='title';
	$listDirn = 'ascending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'),
		'id'=>'id','sortdate'=>Text::_('XBCULTURE_DATES'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),
		'published'=>Text::_('XBCULTURE_STATUS'),'a.ordering'=>Text::_('XBCULTURE_ORDERING'),
    'bcnt'=>Text::_('XBCULTURE_BOOKS_U'),'fcnt'=>Text::_('XBCULTURE_FILMS_U'), 'bcnt'=>Text::_('XBCULTURE_EVENTS'),
    'a.created'=>Text::_('XBCULTURE_DATE_ADDED')
    
);

$saveOrder      = $listOrder == 'ordering';
$canOrder       = $user->authorise('core.edit.state', 'com_xbpeople.group');
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_xbpeople&task=groups.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'xbpersonsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$nofile = "media/com_xbpeople/images/nofile.jpg";

$gelink = 'index.php?option=com_xbpeople&view=group&task=group.edit&id=';
$cvlink = 'index.php?option=com_xbpeople&view=pcategory&id=';
$tvlink = 'index.php?option=com_xbpeople&view=tag&id=';

?>
<style type="text/css" media="screen">
	.xbpvmodal .modal-content {padding:15px;max-height:calc(100vh - 190px); overflow:scroll; }
</style>
<form action="index.php?option=com_xbbooks&view=groups" method="post" id="adminForm" name="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
	<?php else : ?>
        <div id="j-main-container">
	<?php endif;?>
 	<div class="pull-right span6 xbtr xbm0">
 			<?php $fnd = $this->pagination->total;
			echo $fnd .' '. Text::_(($fnd==1)?'XBCULTURE_GROUP':'XBCULTURE_GROUPS').' '.Text::_('XBCULTURE_FOUND').', ';
			?>
            <?php echo 'sorted by '.$orderNames[$listOrder].' '.$listDirn ; ?>
	</div>
	<div class="clearfix"></div>
    <div class="pull-right pagination xbm0" style="padding-left:10px;">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
	<?php // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clearfix"></div>
	<?php $search = $this->searchTitle; ?>

	<?php if ($search) {
		echo '<p>Searched for <b>'; 
		if (stripos($search, 'i:') === 0) {
            echo trim(substr($search, 2)).'</b> '.Text::_('XBCULTURE_AS_ID');
		} elseif ((stripos($search, 's:') === 0) || (stripos($search, 'b:') === 0)) {
            echo trim(substr($search, 2)).'</b> '.Text::_('XBCULTURE_IN_DESC');
        } else {
			echo trim($search).'</b> '.Text::_('XBCULTURE_IN_TITLE');
		}
		echo '</p>';
	} ?> 

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	
	<table class="table table-striped table-hover" id="xbgroupsList">
		<thead>
			<tr>
				<th class="nowrap center hidden-phone" style="width:25px;">
					<?php echo HTMLHelper::_('searchtools.sort', '', 'ordering', 
					    $listDirn, $listOrder, null, 'asc', 'XBCULTURE_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
    			<th class="hidden-phone" style="width:25px;">
    				<?php echo HTMLHelper::_('grid.checkall'); ?>
    			</th>
    			<th class="nowrap center" style="width:55px">
					<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
    			</th>
    			<th class="center" style="width:80px">
    				<?php echo Text::_('XBCULTURE_PICTURE') ;?>
    			</th>
    			<th >
					<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_NAME', 'title', $listDirn, $listOrder); ?>
					<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_DATES', 'year_formed', $listDirn, $listOrder); ?>
    			</th>
    			<th>
    				<?php echo Text::_('XBCULTURE_MEMBERS'); ?>
    			</th>
    			<th>
    				<?php echo Text::_('XBCULTURE_SUMMARY'); ?>
    			</th>
    			<th>
					<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_BOOKS_U', 'bcnt', $listDirn, $listOrder); ?>
    			</th>
     			<th class="hidden-tablet hidden-phone" style="width:15%;">
					<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_CATS','category_title',$listDirn,$listOrder ).' &amp; ';
						echo Text::_( 'XBCULTURE_TAGS_U' ); ?>
				</th>   			
    			<th class="nowrap hidden-phone" style="width:45px;">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
			<?php foreach ($this->items as $i => $item) :
    			$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$userId || $item->checked_out==0;
    			$canChange  = $user->authorise('core.edit.state', 'com_xbpeople.person.'.$item->id) && $canCheckin;
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
							<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'groups.', true, 'cb'); ?>
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
						<?php if(!empty($item->picture)) : ?>
							<?php 
    							$src = $item->picture;
    							if (!file_exists(JPATH_ROOT.'/'.$src)) {
    								$src = $nofile;
    							}
    							$src = Uri::root().$src;
							?>
							<img class="img-polaroid hasTooltip xbimgthumb" title="" 
								data-original-title="<?php echo $item->picture;?>"
								src="<?php echo $src; ?>" border="0" alt="" />
						<?php endif; ?>						
					</td>
					<td>
						<p class="xbtitlelist">
							<?php if ($item->checked_out) {
							    $couname = Factory::getUser($item->checked_out)->username;
							    echo HTMLHelper::_('jgrid.checkedout', $i, Text::_('XBCULTURE_OPENEDBY').':,'.$couname, $item->checked_out_time, 'person.', $canCheckin); 
							} ?>
							
							<a href="<?php echo $gelink.$item->id; ?>" title="<?php echo Text::_('XBPEOPLE_EDIT_GROUP'); ?>">
								<?php echo ' '.$item->title; ?> 
							</a>&nbsp;
    						<a href="" data-toggle="modal"  class="xbpv" data-target="#ajax-gpvmodal"  onclick="window.pvid= <?php echo $item->id; ?>;">
                				<i class="far fa-eye"></i>
                			</a>					
							<br />
							<span class="xb08 xbnorm"><i><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></i></span>
						</p>
						<p>
						<?php 
							if($item->year_formed > 0) { echo '<i>'.Text::_('XBCULTURE_FORMED').' </i>: '.$item->year_formed;} 
							if($item->year_disolved > 0) { 
								echo '&nbsp;&nbsp;<i>'.Text::_('XBCULTURE_DISBANDED').' </i>: '.$item->year_disolved;
							} ?>						
						</p>							
					</td>
					<td>
						<?php if ($item->pcnt) : ?>
							<details>
    							<summary>
    								<?php echo $item->pcnt; ?> members listed
    							</summary>
    							<ul class="xbdetails"> 
                                	<?php echo $item->memberlist['ullist']; ?>
    							</ul>
							</details>							
                        	<?php if (!empty($item->description)) : ?>
								<p class="xbnote">also see description</p>
							<?php endif; ?>
						<?php else : ?>
                        	<?php if (!empty($item->description)) : ?>
								<p class="xbnote">see description</p>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td>						
						<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
								<?php echo $item->summary; ?>
    						<?php else : ?>
    							<span class="xbnit">
    							<?php if (!empty($item->description)) : ?>
    								<?php echo Text::_('XBCULTURE_BIOG_EXTRACT'); ?>: </span>
    								<?php echo XbcultureHelper::makeSummaryText($item->description,0); ?>
    							<?php else : ?>
    								<?php echo Text::_('XBCULTURE_NO_SUMMARY_BIOG'); ?></span>
    							<?php endif; ?>
    						<?php endif; ?>
    					</p>
                        <?php if (!empty($item->description)) : ?>
                        	<p class="xbnit xb09">   
                             <?php 
                             echo Text::_('XBCULTURE_BIOG').' '.str_word_count(strip_tags($item->description)).' '.Text::_('XBCULTURE_WORDS'); 
                             ?>
							</p>
						<?php endif; ?>
						<?php if($item->ext_links_cnt >0 ) : ?>
							<p class="xbnit xb095">	
								<?php echo Text::_('XBCULTURE_FIELD_EXTLINK_LABEL').': '; 
	                            echo '<span class="xb09 xbnorm">';
	                            echo $item->ext_links_list.'</span>'; ?>
	                    	</p>
						<?php endif; ?>
                    </td>
					<td>
    					<?php if ($item->bcnt>0) : ?>
							<details>
    							<summary>
    								<?php echo $item->bcnt.' ';
                                        echo ($item->bcnt==1)?Text ::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');
                                        echo ' '.Text::_('XBCULTURE_LISTED'); ?>    								
    							</summary>
    							<ul class="xbdetails"> 
                                	<?php echo $item->booklist['ullist']; ?>
    							</ul>
							</details>							
    					<?php endif; ?>	
    				</td>				
					<td>
						<p><a  class="label label-success" href="<?php echo $cvlink . $item->catid; ?>" 
							title="<?php echo Text::_( 'XBCULTURE_VIEW_CATEGORY' );?>::<?php echo $item->category_title; ?>">
								<?php echo $item->category_title; ?>
						</a></p>						
						
						<ul class="inline">
						<?php foreach ($item->tags as $t) : ?>
							<li><a href="<?php echo $tvlink.$t->id; ?>" class="label percnt">
								<?php echo $t->title; ?></a>
							</li>													
						<?php endforeach; ?>
						</ul>						    											
					</td>					
					<td align="center">
						<?php echo $item->id; ?>
					</td>
					<td>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbbooks');?></p>
<script>
jQuery(document).ready(function(){
//for preview modals
    jQuery('#ajax-ppvmodal').on('show', function () {
        // Load view vith AJAX
      jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=person&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-gpvmodal').on('show', function () {
        // Load view vith AJAX
      jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=group&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-bpvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=book&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-ppvmodal,#ajax-gpvmodal,#ajax-bpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
});
</script>
<!-- preview modal windows -->
<div class="modal fade xbpvmodal" id="ajax-ppvmodal" style="max-width:1000px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Person</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-gpvmodal" style="max-width:1000px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Group</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-bpvmodal" style="max-width:1000px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Book</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>

