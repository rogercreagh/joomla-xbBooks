<?php
/*******
 * @package xbBooks
 * @filesource admin/views/people/tmpl/default.php
 * @version 1.0.1.3 4th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_TAGS')));
HTMLHelper::_('formbehavior.chosen', '.multipleCats', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_CATS')));

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
	$listOrder='lastname';
	$listDirn = 'ascending';
}
$orderNames = array('firstname'=>Text::_('XBCULTURE_FIRSTNAME'),'lastname'=>Text::_('XBCULTURE_LASTNAME'),
	'id'=>'id','sortdate'=>Text::_('XBCULTURE_DATES'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),
	'published'=>Text::_('XBCULTURE_PUBLISHED'),'ordering'=>Text::_('XBCULTURE_ORDERING'));

$saveOrder      = $listOrder == 'ordering';
$canOrder       = $user->authorise('core.edit.state', 'com_xbbooks.book');
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_xbbooks&task=persons.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'xbpersonsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$nofile = "media/com_xbbooks/images/nofile.jpg";

$pelink = 'index.php?option=com_xbpeople&view=person&task=person.edit&id=';
$cvlink = 'index.php?option=com_xbbooks&view=bcategory&id=';
$tvlink = 'index.php?option=com_xbbooks&view=tag&id=';

?>
<form action="index.php?option=com_xbbooks&view=persons" method="post" id="adminForm" name="adminForm">
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
			echo $fnd .' '. Text::_(($fnd==1)?'XBCULTURE_PERSON':'XBCULTURE_PEOPLE').' '.Text::_('XBCULTURE_FOUND').', ';
			?>
            <?php echo 'sorted by '.$orderNames[$listOrder].' '.$listDirn ; ?>
	</div>
	<div class="clearfix"></div>
    <div class="pull-right pagination xbm0" style="padding-left:10px;">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
	<div class="clearfix"></div>
	<?php
        // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clearfix"></div>
	<?php $search = $this->searchTitle; ?>

	<?php if ($search) {
		echo '<p>Searched for <b>'; 
		if (stripos($search, 'i:') === 0) {
            echo trim(substr($search, 2)).'</b> '.Text::_('XBCULTURE_AS_ID');
		} elseif ((stripos($search, 's:') === 0) || (stripos($search, 'b:')===0)) {
            echo trim(substr($search, 2)).'</b> '.Text::_('XBBOOKS_AS_INBIOG');
        } else {
			echo trim($search).'</b> '.Text::_('XBBOOKS_AS_INNAMES');
		}
		echo '</p>';
	} ?> 

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>	
	<table class="table table-striped table-hover" id="xbpersonsList">
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
    			<th class="center" style="width:80px">
    				<?php echo Text::_('XBCULTURE_PORTRAIT') ;?>
    			</th>
    			<th >
					<?php echo HTMLHelper::_('searchtools.sort', 'XBBOOKS_FIRSTNAME', 'firstname', $listDirn, $listOrder); ?>
					<?php echo HTMLHelper::_('searchtools.sort', 'XBBOOKS_LASTNAME', 'lastname', $listDirn, $listOrder); ?>					
					<?php echo HTMLHelper::_('searchtools.sort', 'XBCULTURE_DATES', 'year_born', $listDirn, $listOrder); ?>
    			</th>
    			<th>
    				<?php echo Text::_('XBCULTURE_BIOGRAPHY'); ?>
    			</th>
    			<th >
    				<?php echo Text::_('XBCULTURE_BOOKS_U') ;?>
    			</th>
    			<th class="hidden-tablet hidden-phone" style="width:15%;">
						<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_CATS','category_title',$listDirn,$listOrder ).' &amp; '.
						Text::_( 'XBCULTURE_TAGS_U' ); ?>
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
    			$canEdit    = $user->authorise('core.edit', 'com_xbbooks.person.'.$item->id);
    			$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$userId || $item->checked_out==0;
    			$canEditOwn = $user->authorise('core.edit.own', 'com_xbbooks.person.'.$item->id) && $item->created_by == $userId;
    			$canChange  = $user->authorise('core.edit.state', 'com_xbbooks.person.'.$item->id) && $canCheckin;
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
							<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'person.', true, 'cb'); ?>
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
						<?php if(!empty($item->portrait)) : ?>
							<?php 
    							$src = $item->portrait;
    							if (!file_exists(JPATH_ROOT.'/'.$src)) {
    								$src = $nofile;
    							}
    							$src = Uri::root().$src;
							?>
							<img class="img-polaroid hasTooltip xbimgthumb" title="" 
								data-original-title="<?php echo $item->portrait;?>"
								src="<?php echo $src; ?>" border="0" alt="" />
						<?php endif; ?>						
					</td>
					<td>
						<p class="xbtitlelist">
							<?php if ($item->checked_out) {
							    $couname = Factory::getUser($item->checked_out)->username;
							    echo HTMLHelper::_('jgrid.checkedout', $i, Text::_('XBCULTURE_OPENEDBY').':,'.$couname, $item->checked_out_time, 'person.', $canCheckin); 
							} ?>
							
							<a href="<?php echo $pelink.$item->id; ?>" title="<?php echo Text::_('XBBOOKS_EDIT_PERSON'); ?>">
								<?php echo ($item->firstname=='')? '... ' : $item->firstname; ?>
								<?php echo ' '.$item->lastname; ?> 
							</a>
							<br />
							<span class="xb08 xbnorm"><i><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></i></span>
						</p>
						<p>
						<?php 
							if($item->year_born > 0) { echo '<i>'.Text::_('XBBOOKS_BORN').' </i>: '.$item->year_born;} 
							if($item->year_died > 0) { 
								echo '&nbsp;&nbsp;<i>'.Text::_('XBBOOKS_DIED').' </i>: '.$item->year_died;
							}
							if($item->nationality) { 
                        		echo '<br /><i>'.Text::_('XBCULTURE_NATIONALITY').' </i>: '.$item->nationality;
                        	} ?>						
						</p>							
					</td>
					<td>						
						<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
								<?php echo $item->summary; ?>
    						<?php else : ?>
    							<span class="xbnit">
    							<?php if (!empty($item->biography)) : ?>
    								<?php echo Text::_('XBBOOKS_BIOG_EXTRACT'); ?>: </span>
    								<?php echo XbcultureHelper::makeSummaryText($item->biography,200); ?>
    							<?php else : ?>
    								<?php echo Text::_('XBBOOKS_NO_SUMMARY_BIOG'); ?></span>
    							<?php endif; ?>
    						<?php endif; ?>
                        </p>
                        <?php if ((!empty($item->biography)) && (strlen(strip_tags($item->biography))>200)) : ?>
                        	<p class="xbnit xb09">   
                             <?php 
                             echo Text::_('XBCULTURE_BIOGRAPHY').' '.str_word_count(strip_tags($item->biography)).' '.Text::_('XBCULTURE_WORDS'); 
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
						<?php if ($item->acnt>0) : ?>						
        					<?php if (($item->brcnt == 1) && ($item->acnt < 3)) : ?>
        						<?php echo $item->alist; ?>
							<?php else : ?>
                                <details>
                                <summary><span class="xbnit">
                                	<?php echo Text::_('XBCULTURE_AUTHOR_OF').' '.$item->acnt.' ';
                                    echo ($item->acnt==1)?Text ::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');   ?>
                                </span></summary>
                                <?php echo $item->alist; ?>
                                </details>
        					<?php endif; ?>
						<?php endif; ?> 
						<?php if ($item->ecnt>0) : ?>
        					<?php if (($item->brcnt == 1) && ($item->ecnt < 3)) : ?>
        						<?php echo $item->elist; ?>
							<?php else : ?>
                                  <details>
                                  	<summary><span class="xbnit">
         								<?php echo Text::_('XBCULTURE_EDITOR_OF').' '.$item->ecnt.' ';
                                        echo ($item->ecnt==1)?Text ::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');   ?>
                                    </span></summary>
         							<?php echo $item->elist; ?>
                                  </details>
							<?php endif; ?> 
						<?php endif; ?> 
						<?php if ($item->ocnt>0) : ?>
        					<?php if (($item->brcnt == 1) && ($item->ocnt < 3)) : ?>
        						<?php echo $item->olist; ?>
							<?php else : ?>
                                  <details>
                                  	<summary><span class="xbnit">
         								<?php echo Text::_('XBCULTURE_OTHER_ROLE_ON').' '.$item->ocnt.' '; 
                                        echo ($item->ocnt==1)?Text ::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');   ?>
                                    </span></summary>
         							<?php echo $item->olist; ?>
                                  </details>
							<?php endif; ?> 
						<?php endif; ?> 
						<?php if ($item->mcnt>0) : ?>
        					<?php if (($item->brcnt == 1) && ($item->mcnt < 3)) : ?>
        						<?php echo $item->mlist; ?>
							<?php else : ?>
                                  <details>
                                  	<summary><span class="xbnit">
         								<?php echo Text::_('XBBOOKS_MENTION_IN').' '.$item->mcnt.' ';
                                        echo ($item->mcnt==1)?Text ::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS');   ?>
                                    </span></summary>
         							<?php echo $item->mlist; ?>
                                  </details>
							<?php endif; ?> 
						<?php endif; ?> 
						<?php if (($item->fcnt + $item->ecnt)>0) {
						    echo '<span class="xbnit">'.Text::_('Also').' ';
						    echo '</span>';
						    if ($item->fcnt>0) {
						        echo $item->fcnt.' '.Text::_(($item->fcnt==1)?'XBCULTURE_FILM':'XBCULTURE_FILMS');
						        echo ($item->ecnt>0) ? ' &amp; ': '';
						    }
						    if ($item->ecnt>0) {
						        echo $item->ecnt.' '.lcfirst(Text::_(($item->ecnt==1)?'XBCULTURE_EVENT':'XBCULTURE_EVENTS'));
						    }
						} ?>
					</td>
					<td>
						<p><a  class="label label-success" href="<?php echo $cvlink . $item->catid.'&extension=com_xbbooks'; ?>" 
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
				</tr>
			<?php endforeach; ?>
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
