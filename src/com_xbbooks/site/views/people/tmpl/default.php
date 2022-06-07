<?php 
/*******
 * @package xbBooks
 * @filesource site/views/people/tmpl/default.php
 * @version 0.9.8.8 7th June 2022
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

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='lastname';
    $listDirn = 'ascending';
}
$orderNames = array('firstname'=>Text::_('XBCULTURE_FIRSTNAME'),'lastname'=>Text::_('XBCULTURE_LASTNAME'),
    'sortdate'=>Text::_('XBCULTURE_DATES'),'category_title'=>Text::_('XBCULTURE_CATEGORY'),'bcnt'=>'Number of books');

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getPeopleRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$plink = 'index.php?option=com_xbbooks&view=person' . $itemid.'&id=';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category' . $itemid.'&id=';

?>
<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
		echo XbbooksHelper::sitePageheader($this->header);
	} ?>
	
<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=people'); ?>" method="post" name="adminForm" id="adminForm">
		<?php  // Search tools bar
			if ($this->search_bar) {
				$hide = '';
				if ($this->hide_prole) { $hide .= 'filter_prole,';}
				if ((!$this->showcats) || ($this->hide_cat)) { $hide .= 'filter_category_id,filter_subcats,';}
				if ($this->hide_tag) { $hide .= 'filter_tagfilt,filter_taglogic,';}
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
		<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php } else { ?>
		<table class="table table-striped table-hover" style="table-layout:fixed;" id="xbpeople">	
		<thead>
			<tr>
				<?php if($this->show_pic) : ?>
					<th class="center" style="width:80px">
						<?php echo Text::_( 'XBCULTURE_PORTRAIT' ); ?>
					</th>	
                <?php endif; ?>
				<th>
					<?php echo HTMLHelper::_('searchtools.sort','Firstname','firstname',$listDirn,$listOrder).' '.
							HTMLHelper::_('searchtools.sort','Lastname','lastname',$listDirn,$listOrder);    						
						?>
				</th>					
				<?php if($this->show_pdates) : ?>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort','Dates','sortdate',$listDirn,$listOrder); ?>
					</th>
                <?php endif; ?>
				<?php if($this->show_sum) : ?>
				<th>
					<?php echo Text::_('XBCULTURE_SUMMARY');?>
				</th>
                <?php endif; ?>
				<?php if($this->show_books != 0) : ?>
    				<th>
    					<?php echo Text::_('XBCULTURE_BOOKS_U'); ?>
    				</th>
               <?php endif; ?>
				<?php if($this->showcats || $this->showtags) : ?>
    				<th class="hidden-tablet hidden-phone">
    					<?php if ($this->showcats) {
    						echo HtmlHelper::_('searchtools.sort','XBCULTURE_CATEGORY','category_title',$listDirn,$listOrder );
    					}
    					if (($this->showcats) && ($this->showtags)) {
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
			<?php foreach ($this->items as $i => $item) { ?>
				<tr class="row<?php echo $i % 2; ?>">
             		<?php if($this->show_pic) : ?>
					<td>
						<?php $src = $item->portrait;
						if ((!empty($src)) && (file_exists(JPATH_ROOT.'/'.$src))) : ?>
							<?php
								$src = Uri::root().$src;
								$tip = '<img src=\''.$src.'\' style=\'max-width:250px;\' />';
							?>						
							<img class="img-polaroid hasTooltip xbimgthumb" title="" 
								data-original-title="<?php echo $tip; ?>"
								src="<?php echo $src; ?>" border="0" alt="" />
						<?php endif; ?>						
					</td>
                    <?php endif; ?>
				<td>
					<p class="xbtitlelist">
						<a href="<?php echo Route::_($plink.$item->id);?>" >
							<b><?php echo $this->escape($item->firstname).' '.$this->escape($item->lastname); ?></b>
						</a>
					</p>
				</td>
				<?php if($this->show_pdates) : ?>
					<td>
					<p class="xb095">
					<?php if ($item->year_born > 0) {						
							echo '<span class="xbnit">'.Text::_('XBCULTURE_BORN_U').'</span>: '.$item->year_born.'<br />'; 
						}
						if ($item->year_died > 0) {						
							echo '<span class="xbnit">'.Text::_('XBCULTURE_DIED_U').'</span>: '.$item->year_died; 
						}              
					?>					
					</p>
					</td>
				<?php endif; ?>
				<?php if($this->show_sum) : ?>
				<td>
					<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
								<?php echo $item->summary; ?>
    						<?php else : ?>
        						<?php if (!empty($item->biography)) : ?>
		        					<span class="xbnit">
        								<?php echo Text::_('XBBOOKS_BIOG_EXTRACT'); ?>: 
        							</span>
        							<?php echo XbcultureHelper::makeSummaryText($item->biography,0); ?>
        						<?php else : ?>
		        					<span class="xbnit">
        								<?php echo Text::_('XBBOOKS_NO_SUMMARY_BIOG'); ?>
        							</span>
        						<?php endif; ?>
        					<?php endif; ?>
                        </p>
                        <?php if (!empty($item->biography)) : ?>
                            <p class="xbnit xb09">   
                                 <?php 
                                 	echo Text::_('XBCULTURE_BIOGRAPHY').' '.str_word_count(strip_tags($item->biography)).' '.Text::_('XBCULTURE_WORDS'); 
                                 ?>
    						</p>
    					<?php endif; ?>
				</td>
				<?php endif; ?>
				<?php if ($this->show_books != '0') : ?>
				<td><p class="xbit xb095">
					<?php if ($item->acnt > 0) : ?>
						<span tabindex="<?php echo $item->id; ?>"
						<?php if ($this->show_books == '2') : ?>
								class="xbpop xbcultpop xbfocus" data-trigger="focus"
								title data-original-title="Books as Author" 
								data-content="<?php echo htmlentities($item->alist); ?>"
						<?php endif; ?>
						>
						<?php echo Text::_('XBBOOKS_AUTHOR_OF').' ';
						if ($this->show_books == '3') {
							echo $item->alist;
						} else { //implies show_books=cnt
							echo $item->acnt.' books';
						} ?>
						</span><br />
					<?php endif; ?>
					<?php if ($item->ecnt > 0) : ?>
						<span tabindex="<?php echo $item->id; ?>"
						<?php if ($this->show_books == '2') : ?>
								class="xbpop xbcultpop xbfocus" data-trigger="focus"
								title data-original-title="Books as Editor" 
								data-content="<?php echo htmlentities($item->elist); ?>"
						<?php endif; ?>
						>
						<?php echo Text::_('XBBOOKS_EDITOR_OF').' ';
						if ($this->show_books == '3') {
							echo $item->elist;
						} else { //implies show_books=cnt
							echo $item->ecnt.' books';
						} ?>
						</span><br />
					<?php endif; ?>
					<?php if ($item->ocnt > 0) : ?>
						<span tabindex="<?php echo $item->id; ?>" 
						<?php if ($this->show_books == '2') : ?>
								class="xbpop xbcultpop xbfocus" data-trigger="focus"
								title data-original-title="Books - other roles" 
								data-content="<?php echo htmlentities($item->olist); ?>"
						<?php endif; ?>
						>
						<?php echo Text::_('XBCULTURE_OTHER_ROLE_IN').' ';
						if ($this->show_books == '3') {
							echo $item->olist;
						} else { //implies show_books=cnt
							echo $item->ocnt.' books';
						} ?>
						</span><br />
					<?php endif; ?>
					<?php if ($item->mcnt > 0) : ?>
						<span tabindex="<?php echo $item->id; ?>" 
						<?php if ($this->show_books == '2') : ?>
								class="xbpop xbcultpop xbfocus" data-trigger="focus"
								title data-original-title="Books - mentioned in" 
								data-content="<?php echo htmlentities($item->mlist); ?>"
						<?php endif; ?>
						>
						<?php echo Text::_('XBBOOKS_MENTION_IN').' ';
						if ($this->show_books == '3') {
							echo $item->mlist;
						} else { //implies show_books=cnt
							echo $item->mcnt.' books';
						} ?>
						</span></br />
					<?php endif; ?>
					</p>
    				<?php if ($item->filmcnt > 0) {
    						echo '<p class="xbit xb095"><span>'.Text::_('XBCULTURE_LISTED_WITH').'</span>: '.$item->filmcnt.' '.Text::_('XBCULTURE_FILMS').'</p>';
    					}
    				?>
				</td>
				<?php endif; ?>
    			<?php if(($this->showcats) || ($this->showtagss)) : ?>
					<td class="hidden-phone">
 						<?php if (($this->showcats) && ($this->xbpeople_ok)) : ?>												
							<p>
								<?php if($this->showcats == 2) : ?>
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
			<?php } // endforeach; ?>
		</tbody>
		</table>


	<?php echo $this->pagination->getListFooter(); ?>
	<?php } //endif; ?>
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</div>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
</div>
