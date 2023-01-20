<?php 
/*******
 * @package xbBooks
 * @filesource site/views/groups/tmpl/default.php
 * @version 1.0.3.5 19th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;

HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
HtmlHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='title';
    $orderDrn = 'asscending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_NAME'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),
'bcnt'=>'Number of books','fcnt'=>'Number of films','ecnt'=>'number of events');

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbpeople&view=category'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getGroupsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$plink = 'index.php?option=com_xbpeople&view=group'.$itemid.'&id=';

?>
<div class="xbculture">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
	    echo XbcultureHelper::sitePageheader($this->header);
	} ?>
	
<form action="<?php echo Route::_('index.php?option=com_xbpeople&view=groups'); ?>" method="post" name="adminForm" id="adminForm">
		<?php  // Search tools bar
			if ($this->search_bar) {
				$hide = '';
				if ((!$this->showcat) || ($this->hide_cat)) { $hide .= 'filter_category_id, filter_subcats,';}
				if (!$this->showtags || $this->hide_tag) { $hide .= 'filter_tagfilt,filter_taglogic,';}
				echo '<div class="row-fluid"><div class="span12">';
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this,'hide'=>$hide));
				echo '</div></div>';
			} 
		?>
		<div class="row-fluid pagination" style="margin-bottom:10px 0;">
			<div class="pull-right">
				<p class="counter" style="text-align:right;margin-left:10px;">
					<?php echo $this->pagination->getResultsCounter().'.&nbsp;&nbsp;'; 
					   echo $this->pagination->getPagesCounter().'&nbsp;&nbsp;'.$this->pagination->getLimitBox().' per page'; ?>
				</p>
			</div>
			<div>
				<?php  echo $this->pagination->getPagesLinks(); ?>
                <?php echo 'sorted by '.$orderNames[$listOrder].' '.$listDirn ; ?>
			</div>
		</div>

		<div class="row-fluid">
        	<div class="span12">		
	<?php if (empty($this->items)) { ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php } else { ?>
		<table class="table table-striped table-hover" style="table-layout:fixed;" id="xbcharacters">	
		<thead>
			<tr>
				<?php if($this->show_pic) : ?>
					<th class="center" style="width:80px">
						<?php echo Text::_( 'XBCULTURE_PICTURE' ); ?>
					</th>	
                <?php endif; ?>
				<th>
					<?php echo HtmlHelper::_('searchtools.sort','Name','title',$listDirn,$listOrder);
						?>
				</th>					
				<?php if($this->show_gdates) : ?>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort','Dates','sortdate',$listDirn,$listOrder); ?>
					</th>
                <?php endif; ?>
                <th>
					<?php echo JText::_('XBCULTURE_MEMBERS');?>
                </th>
				<?php if($this->show_sum) : ?>
				<th>
					<?php echo JText::_('XBCULTURE_SUMMARY');?>
				</th>
                <?php endif; ?>
                <?php if ($this->showccnts) : ?>
    				<?php if($this->xbbooksStatus) : ?>
        				<th>
        					<?php echo HtmlHelper::_('searchtools.sort','Books','bcnt',$listDirn,$listOrder ); ?>
        				</th>
                   <?php endif; ?>
    				<?php if($this->xbeventsStatus) : ?>
        				<th>
        					<?php echo HtmlHelper::_('searchtools.sort','Events','ecnt',$listDirn,$listOrder ); ?>
        				</th>
                   <?php endif; ?>
    				<?php if($this->xbfilmsStatus) : ?>
        				<th>
        					<?php echo HtmlHelper::_('searchtools.sort','Films','fcnt',$listDirn,$listOrder ); ?>
        				</th>
                   <?php endif; ?>
                <?php endif; ?>
				<?php if($this->showcat || $this->showtags) : ?>
    				<th class="hidden-tablet hidden-phone">
    					<?php if ($this->showcat) {
    						echo HtmlHelper::_('searchtools.sort','XBCULTURE_CATEGORY','category_title',$listDirn,$listOrder );
    					}
    					if (($this->showcat) && ($this->showtags)) {
    					    echo ' &amp; ';
    					}
    					if($this->showtags) {
    					    echo Text::_( 'XBCULTURE_TAGS_U' ); 
    					} ?>                
    				</th>
                <?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
             		<?php if($this->show_pic) : ?>
					<td>
	 					<?php $src = trim($item->picture);
							if (($src != '') && (file_exists(JPATH_ROOT.'/'.$src))) : ?>
								<?php 
									$src = Uri::root().$src;
									$tip = '<img src=\''.$src.'\' style=\'max-width:250px;\' />';
								?>
								<img class="img-polaroid hasTooltip xbimgthumb" title="" 
									data-original-title="<?php echo $tip; ?>"
									src="<?php echo $src; ?>"
									border="0" alt="" />	
							<?php endif; ?>					
					</td>
                    <?php endif; ?>
				<td>
					<p class="xbtitlelist">
						<a href="<?php echo Route::_($plink.$item->id);?>" >
							<b><?php echo $this->escape($item->title); ?></b>
						</a>
					</p>
				</td>
				<?php if($this->show_gdates) : ?>
					<td>
					<p class="xb095">
					<?php if ($item->year_formed > 0) {						
							echo '<span class="xbnit">'.Text::_('XBCULTURE_FORMED').'</span>: '.$item->year_formed.'<br />'; 
						}
						if ($item->year_disolved > 0) {						
							echo '<span class="xbnit">'.Text::_('XBCULTURE_DISBANDED').'</span>: '.$item->year_disolved; 
						}              
					?>					
					</p>
					</td>
				<?php endif; ?>
				<td>
					<?php if ($item->pcnt>0) : ?>
    					<details>
    						<summary><span class="xbnit">
								<?php echo $item->pcnt.' ';
								    echo $item->pcnt ==1 ? Text::_('XBCULTURE_MEMBER') : lcfirst(Text::_('XBCULTURE_MEMBERS')); ?>       					
    						</span></summary>
    						<?php echo $item->memberlist; ?>    						
    					</details>
					<?php else : ?>
						<p class="xbnit"><?php echo Text::_('None listed')?></p>
					<?php endif; ?>
				</td>
				<?php if($this->show_sum) : ?>
				<td>
					<p class="xb095">
						<?php if (!empty($item->summary)) : ?>
							<?php echo $item->summary; ?>
    					<?php else : ?>
    						<?php if (!empty($item->description)) : ?>
    							<?php echo XbcultureHelper::makeSummaryText($item->description,0); ?>
    						<?php else : ?>
    							<span class="xbnit xb09"><?php echo Text::_('XBCULTURE_NO_DESCRIPTION'); ?></span>
    						<?php endif; ?>
    					<?php endif; ?>
                    </p>
                    <?php if (!empty($item->description)) : ?>
                        <p class="xbnit xb09">   
                            <?php 
                             echo Text::_('XBCULTURE_DESCRIPTION').' '.str_word_count(strip_tags($item->description)).' '.Text::_('XBCULTURE_WORDS'); 
                             ?>
						</p>
					<?php endif; ?>
				</td>
				<?php endif; ?>
                <?php if ($this->showccnts) : ?>
    				<?php if ($this->xbbooksStatus) : ?>
        				<td>
    						<?php if ($item->bcnt>0) :?>
        					<details>
        						<summary><span class="xbnit">
    								<?php echo $item->bcnt.' ';
    								    echo $item->bcnt ==1 ? Text::_('XBCULTURE_BOOK') : Text::_('XBCULTURE_BOOKS'); ?>
        						</span></summary>
        						<?php echo $item->booklist; ?>    						
        					</details>
        					<?php endif; ?>
        				</td>
        			<?php endif; ?>
    				<?php if ($this->xbeventsStatus) : ?>
        				<td>
    						<?php if ($item->ecnt>0) :?>
        					<details>
        						<summary><span class="xbnit">
    								<?php echo $item->ecnt.' ';
    								    echo $item->ecnt ==1 ? lcfirst(Text::_('XBCULTURE_EVENT')) : lcfirst(Text::_('XBCULTURE_EVENTS')); ?>
        						</span></summary>
        						<?php echo $item->eventlist; ?>    						
        					</details>
        					<?php endif; ?>
        				</td>
        			<?php endif; ?>
        			<?php if ($this->xbfilmsStatus) : ?>
        				<td>
    						<?php if ($item->fcnt>0) :?>
        					<details>
        						<summary><span class="xbnit">
    								<?php echo $item->fcnt.' ';
    								    echo $item->fcnt ==1 ? Text::_('XBCULTURE_FILM') : Text::_('XBCULTURE_FILMS'); ?>       					
        						</span></summary>
        						<?php echo $item->filmlist; ?>    						
        					</details>
        					<?php endif; ?>
        				</td>
        			<?php endif; ?>
    			<?php endif; ?>
    			<?php if($this->show_ctcol) : ?>
					<td class="hidden-phone">
 						<?php if ($this->showcat) : ?>												
							<p>
								<?php if($this->showcat == 2) : ?>
    								<a class="label label-success" href="<?php echo $clink.$item->catid; ?>">
    									<?php  echo $item->category_title; ?></a>		
    							<?php else: ?>
    								<span class="label label-success"><?php  echo $item->category_title; ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ($this->showtags) : ?>	
							<?php  $tagLayout = new FileLayout('joomla.content.tags');
    							echo $tagLayout->render($item->tags);?>
    					<?php endif; ?>
					</td>
                <?php endif; ?>
				</tr>
				
			<?php endforeach; ?>
		</tbody>
		</table>


	<?php echo $this->pagination->getListFooter(); ?>
	<?php } //endif; ?>
	<?php echo HtmlHelper::_('form.token'); ?>
	</div>
	</div>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
</div>
