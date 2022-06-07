<?php 
/*******
 * @package xbBooks
 * @filesource site/views/people/tmpl/compact.php
 * @version 0.9.8.9 7th June 2022
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
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
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
				if ((!$this->showtags) || ($this->hide_tag)) { $hide .= 'filter_tagfilt,filter_taglogic,';}
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
		<table class="table table-striped table-hover" style="table-layout:fixed;" id="xbpeople">	
		<thead>
			<tr>
				<th>
					<?php echo HTMLHelper::_('searchtools.sort','Firstname','firstname',$listDirn,$listOrder).' '.
							HTMLHelper::_('searchtools.sort','Lastname','lastname',$listDirn,$listOrder); ?>
				</th>					
				<?php if($this->show_pdates) : ?>
					<th class="hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort','Dates','sortdate',$listDirn,$listOrder); ?>
					</th>
                <?php endif; ?>
				<?php if($this->show_books != 0) : ?>
				<th>
					<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_BOOKS_U','bcnt',$listDirn,$listOrder); ?>
				</th>
				<?php endif; ?>
				<?php if($this->showcats || $this->showtags) : ?>
    				<th class="hidden-tablet hidden-phone">
    					<?php if ($this->showcats) {
    						echo HtmlHelper::_('searchtools.sort','XBCULTURE_CATEGORY','category_title',$listDirn,$listOrder ).' &amp; ';
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
				<td>
					<p class="xbtitlelist">
						<a href="<?php echo Route::_($plink.$item->id);?>" >
							<b><?php echo $this->escape($item->firstname).' '.$this->escape($item->lastname); ?></b>
						</a>
					</p>
				</td>
				<?php if($this->show_pdates) : ?>
					<td class="hidden-phone">
						<p><?php if ($item->year_born != 0) {						
								echo $item->year_born; 
							}
							if ($item->year_died != 0) {						
								echo ($item->year_born == 0) ? '???? - ': ' - ';
								echo $item->year_died; 
							}              
						?></p>
					</td>
				<?php endif; ?>
				<?php if ($this->show_books != 0) : ?>
				<td>
				<td><p class="xbit xb095">
					<?php if ($item->bcnt > 0) : ?>
						<span tabindex="<?php echo $item->id; ?>"
						<?php if ($this->show_books > 1) : ?>
								class="xbpop xbcultpop xbfocus" data-trigger="focus"
								title data-original-title="Books and Role" 
								data-content="<?php echo htmlentities($item->allbooks); ?>"
						<?php endif; ?>
						>
    					<?php echo Text::_('XBCULTURE_LISTED_WITH').' '.$item->bcnt.' '.Text::_(($item->bcnt ==1) ? 'XBCULTURE_BOOK' : 'XBCULTURE_BOOKS'); ?>
						</span>
					<?php endif; ?>
					</p>
				</td>
				<?php endif; ?>
    			<?php if(($this->showcats) || ($this->showtags)) : ?>
					<td class="hidden-phone">
 						<?php if ($this->showcats) : ?>												
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
