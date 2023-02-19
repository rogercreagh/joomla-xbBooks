<?php
/*******
 * @package xbBooks
 * @filesource admin/views/books/tmpl/default.php
 * @version 1.0.4.0e 17th February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_TAGS')));
HTMLHelper::_('formbehavior.chosen', '.multipleCats', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_CATS')));
HTMLHelper::_('formbehavior.chosen', 'select');

$user   = Factory::getUser();
$userId         = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='last_read';
    $listDirn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'),'pubyear'=>Text::_('XBBOOKS_PUBYEARCOL'),
	'id'=>'id','first_read'=>Text::_('XBBOOKS_FIRST_READ'),'last_read'=>Text::_('XBBOOKS_LAST_READ'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),
    'pcnt'=>Text::_('XBCULTURE_PEOPLE'),'gcnt'=>Text::_('XBCULTURE_GROUPS'),'chcnt'=>Text::_('XBCULTURE_CHARACTERS'),
    'published'=>Text::_('XBCULTURE_STATUS'),'ordering'=>Text::_('XBCULTURE_ORDERING'),'a.created'=>Text::_('XBCULTURE_DATE_ADDED')
);


$saveOrder      = $listOrder == 'ordering';
$canOrder       = $user->authorise('core.edit.state', 'com_xbbooks.book');
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_xbbooks&task=books.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'xbbooksList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}


$nofile = "media/com_xbbooks/images/nofile.jpg";

$belink = 'index.php?option=com_xbbooks&view=book&task=book.edit&id=';
$relink = 'index.php?option=com_xbbooks&view=review&task=review.edit&id=';
$rvlink = 'index.php?option=com_xbbooks&view=review&task=review.edit&id='; //change this to view view when available
$cvlink = 'index.php?option=com_xbbooks&view=category&id=';
$tvlink = 'index.php?option=com_xbbooks&view=tag&id=';

?>
<style type="text/css" media="screen">
    .xbpvmodal .modal-body iframe { max-height:calc(100vh - 190px);}
    .xbpvmodal .modal-body { max-height:none; height:auto;}
</style>
<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=books'); ?>" method="post" name="adminForm" id="adminForm">
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
			echo $fnd .' '. Text::_(($fnd==1)?'XBCULTURE_BOOK':'XBCULTURE_BOOKS').' '.Text::_('XBCULTURE_FOUND').', ';
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
	<?php if ($search) : ?>
		<?php echo '<p>Searched for <b>'; ?>
		<?php if (stripos($search, 'i:') === 0) {
                echo trim(substr($search, 2)).'</b> '.Text::_('XBBOOKS_AS_BOOKID');
            } elseif (stripos($search, 's:') === 0) {
                echo trim(substr($search, 2)).'</b> '.Text::_('XBBOOKS_AS_INSYNOP');
            } else {
				echo trim($search).'</b> '.Text::_('XBBOOKS_AS_INTITLE');
			}
			echo '</p>';
        ?>	
	<?php endif; ?> 

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	
		<table class="table table-striped table-hover"" id="xbbooksList">	
			<colgroup>
				<col class="hidden-phone" style="width:25px;"><!-- ordering -->
				<col class="hidden-phone" style="width:25px;"><!-- checkbox -->
				<col style="width:55px;"><!-- status -->
				<col style="width:80px;"><!-- picture -->
				<col ><!-- title, year -->
				<col ><!-- people -->
				<col class="hidden-phone" style="width:230px;" ><!-- summary, extlinks -->
				<col ><!-- reviews -->
				<col class="hidden-phone" style="width:105px;" ><!-- seendates -->
				<col class="hidden-tablet hidden-phone" style="width:230px;"><!-- cats & tags -->
				<col class="hidden-phone" style="width:45px;"><!-- id -->
			</colgroup>	
			<thead>
				<tr>
					<th class="nowrap center">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'ordering', 
						    $listDirn, $listOrder, null, 'asc', 'XBCULTURE_HEADING_ORDERING_DESC', 'icon-menu-2'); ?>
					</th>
					<th class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="nowrap center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo Text::_( 'XBBOOKS_COVER' ); ?>
					</th>			
					<th>
						<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_TITLE','title',$listDirn,$listOrder).
    						' <span class="xb095;">'.
     						Text::_('XBCULTURE_AUTHOR').', '.
     						HTMLHelper::_('searchtools.sort','XBBOOKS_PUBYEARCOL','pubyear',$listDirn,$listOrder ).' '.
    					    '</span>';
						?>
					</th>					
					<th>
						<?php echo ucfirst(Text::_('XBCULTURE_PEOPLE'));?>
					</th>
					<th>
						<?php echo Text::_('XBCULTURE_SUMMARY');?>
					</th>
					<th>
						<?php echo Text::_('XBCULTURE_REVIEWS_U'); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort','First','first_read',$listDirn,$listOrder ).'/'; 
						echo HTMLHelper::_('searchtools.sort','Last','last_read',$listDirn,$listOrder ); ?>					    
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_CATS','category_title',$listDirn,$listOrder ).' &amp; ';						
						echo Text::_( 'XBCULTURE_TAGS_U' ); ?>
					</th>
					<th class="nowrap>
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder );?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :
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
					<td class="center hidden-phone">
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'book.', $canChange, 'cb'); ?>
							<?php if ($item->note!=""){ ?>
								<span class="btn btn-micro active hasTooltip" title="" data-original-title="<?php echo '<b>'.Text::_( 'XBCULTURE_NOTE' ) .'</b>: '. htmlentities($item->note); ?>">
									<i class="icon- xbinfo"></i>
								</span>
							<?php } else {?>
								<span class="btn btn-micro inactive" style="visibility:hidden;" title=""><i class="icon-info"></i></span>
							<?php } ?>
						</div>
					</td>
					<td>
						<?php if(!empty($item->cover_img)) : ?>
						<img class="img-polaroid hasTooltip xbimgthumb" title="" 
							data-original-title="<?php echo $item->cover_img;?>"
							<?php 
    							$src = $item->cover_img;
    							if (!file_exists(JPATH_ROOT.'/'.$src)) {
    							    $src = $nofile;
    							}
    							$src = Uri::root().$src;
							?>
							src="<?php echo $src; ?>"
							border="0" alt="" />	
						<?php endif; ?>					
					</td>
					<td>
						<p class="xbtitlelist">
						<?php if ($item->checked_out) {
						    $couname = Factory::getUser($item->checked_out)->username;
						    echo HTMLHelper::_('jgrid.checkedout', $i, Text::_('XBCULTURE_OPENEDBY').': '.$couname, $item->checked_out_time, 'book.', $canCheckin);
						} ?>
						<?php if ($canEdit || $canEditOwn) : ?>
							<a href="<?php echo Route::_($belink.$item->id);?>" <?php echo ($item->published<>1) ? 'class="xbhlt"' : ''; ?>
								title="<?php echo Text::_('XBCULTURE_EDIT'); ?>" >
								<b><?php echo $this->escape($item->title); ?></b>
							</a>&nbsp;<a href="#ajax-xbmodal" 
								data-toggle="modal" data-target="#ajax-xbmodal" data-backdrop="static" 
								onclick="window.com='books';window.view='book';window.pvid=<?php echo $item->id; ?>;"><i class="far fa-eye"></i></a>
						<?php else : ?>
							<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
						<?php if (!empty($item->subtitle)) {
                          echo '<br /><span class="xbnorm xb09">'.$this->escape($item->subtitle).'</span>';
                        } ?>
                        <br />                        
						<?php $alias = Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
                        <span class="xbnit xb08"><?php echo $alias;?></span>
						<br /><span class="xb09">
							<?php echo $item->pubyear > 0 ? '<span class="xbnit">'.Text::_('XBCULTURE_PUBLISHED').': </span>'.$item->pubyear : ''; ?>						
						</span>
						</p>						
					</td>
					<td>
						<span class="xb095">
						<?php if ($item->authcnt>0) : ?>
							<span class="xbnit"><?php echo $item->authcnt>1 ? Text::_('XBCULTURE_AUTHORS') : Text::_('XBCULTURE_AUTHOR'); ?>
							: </span>
							<?php echo $item->authlist['commalist']; ?>
						<?php else : ?>
							<span class="xbnit">No author listed</span>
						<?php endif; ?>
						<?php if($item->editcnt>0) :?>
							<details>
								<summary class="xbnit">
									<?php echo $item->editcnt.' ';
									echo ($item->editcnt>1) ? Text::_('XBCULTURE_EDITORS') : Text::_('XBCULTURE_EDITOR').' '.Text::_('XBCULTURE_LISTED'); ?>
								</summary>
								<?php echo $item->editlist['ullist']; ?>	
							</details>
						<?php endif; ?>
						<?php if($item->othercnt>0) :?>
							<details>
								<summary class="xbnit">
									<?php echo ($item->othercnt).' '.Text::_('XBCULTURE_OTHER_ROLES').' '.Text::_('XBCULTURE_LISTED'); ?></span>
								</summary>
								<?php echo $item->otherlist['ullist']; ?>	
							</details>
						<?php endif; ?>
						<?php if($item->mencnt>0) :?>
							<details>
								<summary class="xbnit">
									<?php echo ($item->mencnt).' ';
									echo ($item->mencnt>1) ? Text::_('XBCULTURE_APPEARANCES') : Text::_('XBCULTURE_APPEARANCE').' '.Text::_('XBCULTURE_LISTED'); ?>
								</summary>
								<?php echo $item->menlist['ullist']; ?>	
							</details>
						<?php endif; ?>
						<?php if($item->gcnt>0) :?>
							<details>
								<summary class="xbnit">
									<?php echo ($item->gcnt).' ';
									echo ($item->gcnt>1) ? Text::_('XBCULTURE_GROUPS') : Text::_('XBCULTURE_GROUP').' '.Text::_('XBCULTURE_LISTED'); ?>
								</summary>
								<?php echo $item->grplist['ullist']; ?>	
							</details>
						<?php endif; ?>
						<?php if($item->chcnt>0) :?>
							<details>
								<summary class="xbnit">
									<?php echo ($item->chcnt).' ';
									echo ($item->chcnt>1) ? Text::_('XBCULTURE_CHARACTERS') : Text::_('XBCULTURE_CHARACTER').' '.Text::_('XBCULTURE_LISTED'); ?>
								</summary>
								<?php echo $item->charlist['ullist']; ?>	
							</details>
						<?php endif; ?>	
					</td>
					<td>
						<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
									<?php echo $item->summary; ?>
    						<?php else : ?>
    							<span class="xbnit">
    							<?php if (!empty($item->synopsis)) : ?>
    								<?php echo Text::_('XBBOOKS_SYNOPSIS_EXTRACT'); ?>: </span>
    								<?php echo XbcultureHelper::makeSummaryText($item->synopsis,200); ?>
    							<?php else : ?>
    								<?php echo Text::_('XBBOOKS_NO_SUMMARY_SYNOPSIS'); ?></span>
    							<?php endif; ?>
    						<?php endif; ?>
                        </p>
                        <?php if ((!empty($item->synopsis)) && (strlen(strip_tags($item->synopsis))>200)) : ?>
                        	<p class="xbnit xb09">   
                             <?php 
                             	echo Text::_('XBBOOKS_FULLSYNOPSIS').' '.str_word_count(strip_tags($item->synopsis)).' '.Text::_('XBCULTURE_WORDS'); 
                             ?>
							</p>
						<?php endif; ?>
                                    
						<?php if($item->ext_links_cnt >0 ) : ?>
							<p class="xbnit xb095">	
								<?php echo Text::_('XBCULTURE_EXTLINK_LBL').': '; 
	                            echo '<span class="xb09 xbnorm">';
	                            echo $item->ext_links_list.'</span>'; ?>
	                    	</p>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($item->revcnt==0) : ?>
                            <i><?php echo Text::_('XBBOOKS_NOREVIEW'); ?></i><br /> 
						<?php else: ?>
                           	<?php if ($item->revcnt>1) : ?>
                                <div class="xbbb1">Average: 
    								<?php echo XbcultureHelper::getStarStr($item->averat,'com_xbbooks')?>
                                    <?php if (($item->averat - floor($item->averat))>0) : ?>
                                        <span style="color:darkgray;">
                                        	(<?php echo round($item->averat,1); ?>)                                  
                                        </span> 
                                     <?php endif; ?>                        
                                </div>
                            <?php endif; ?>
   							<?php foreach ($item->reviews as $rev) : ?>
								<div class="xbbb1">
    								<?php echo XbcultureHelper::getStarStr($rev->rating,'com_xbfilms')?>
									<br /><a href="<?php echo Route::_($rvlink.$rev->id);?>">
	    								<span class="xbnit"><?php echo Text::_('XBCULTURE_BY').':';?>
    	    								<?php if ($rev->reviewer) {
    	    								    echo $rev->reviewer;
    	    								} else {
    	    								    echo Factory::getUser($rev->created_by)->name;
    	    								} ?>
	    								</span>
	    								<span class="xb09"> <?php echo HtmlHelper::date($rev->rev_date , 'd M Y'); ?></span>
									</a>&nbsp;<a href="#ajax-xbmodal" data-toggle="modal" data-target="#ajax-xbmodal" data-backdrop="static" 
										onclick="window.com='books';window.view='bookreview';window.pvid=<?php echo $rev->id; ?>;">
										<i class="far fa-eye"></i>
									</a>
								</div>
							<?php endforeach; ?>
                        <?php endif; ?>
						<div style="margin-top:5px;">
							<a href="<?php echo Route::_($relink.'0&book_id='.$item->id); ?>" 
								class="btn btn-mini btn-success">
								<?php echo Text::_('XBBOOKS_ADDREVIEW'); ?>
							</a>
						</div>
										
					</td>
					<td><?php if($item->first_read) {
						  echo HtmlHelper::date($item->first_read , 'd M Y');                      
                        } ?>
						<br />
						<?php  if(($item->last_read) && ($item->last_read != $item->first_read)) {
						  echo HtmlHelper::date($item->last_read , 'd M Y'); 
                        } ?>
					</td>
					<td>
						<p><a class="label label-success" href="<?php echo $cvlink.$item->catid; ?>" 
							title="<?php echo Text::_( 'XBCULTURE_VIEW_CATEGORY' );?>::<?php echo $item->category_title; ?>">
								<?php echo $item->category_title; ?>
							</a>
							<?php echo ($item->fiction==1) ? ' <span class="label">fiction</span>' : ' <span class="label label-inverse">non-fiction</span>'; ?>
						</p>						
						
						<ul class="inline">
						<?php foreach ($item->tags as $t) : ?>
							<li><a href="<?php echo $tvlink.$t->id; ?>" class="label label-info">
								<?php echo $t->title; ?></a>
							</li>												
						<?php endforeach; ?>
						</ul>						    											
					</td>
					<td class="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
 		<?php // load the modal for displaying the batch options
        echo HTMLHelper::_('bootstrap.renderModal', 'collapseModal',
            array( 'title' => Text::_('XBCULTURE_BATCH_TITLE'),
                'footer' => $this->loadTemplate('batch_footer')
            ),
            $this->loadTemplate('batch_body')
        ); ?>
	<?php endif; ?>
	<?php echo $this->pagination->getListFooter(); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>

<?php echo LayoutHelper::render('xbculture.layoutpvmodal', array(), JPATH_ROOT .'/components/com_xbpeople/layouts');   ?>

