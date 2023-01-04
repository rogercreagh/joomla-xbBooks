<?php
/*******
 * @package xbBooks
 * @filesource admin/views/bcategory/tmpl/edit.php
 * @version 1.0.1.3 4th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$item = $this->item;
$celink = 'index.php?option=com_categories&task=category.edit&id=';
$xblink = 'index.php?option=com_xbbooks';
$xplink = 'index.php?option=com_xbpeople';
?>
<div class="row-fluid">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container" class="span12">
<?php endif;?>
		<form action="index.php?option=com_xbbooks&view=bcategory" method="post" id="adminForm" name="adminForm">
		<div class="row-fluid xbmb8">
			<div class= "span3">
				  <h3><?php echo Text::_('XBBOOKS_CAT_ITEMS'); ?></h3>
			</div>
			<div class= "span5">
    			<div class="xb11 pull-left xbit xbpt17 xbgrey xbmr20">   				 
    				<?php  $path = substr($item->path, 0, strrpos($item->path, '/'));
    					$path = str_replace('/', ' - ', $path);
    					echo 'root - '.$path; ?>
            	</div>
				<a href="<?php echo $celink.$item->id; ?>" class="badge badge-success">
					<h2><?php echo $item->title; ?></h2>
				</a></div>
            <div class="span2">
                <p><?php echo '<i>'.Text::_('XBCULTURE_ALIAS').'</i>: '.$item->alias; ?></p>
            </div>
			<div class= "span2">
				<p><?php echo '<i>'.Text::_('JGRID_HEADING_ID').'</i>: '.$item->id; ?></p>
 xbmb8			</div>
		</div>
		<div class="row-fluid xbmb8">
			<div class= "span6">
				<p><i><?php Jtext::_('XBCULTURE_ADMIN_NOTE'); ?>:</i>  <?php echo $item->note; ?></p>
			</div>
		</div>
		<div class="row-fluid xbmb8">
			<div class= "span2">
				<p><i><?php echo Text::_('XBCULTURE_DESCRIPTION'); ?>:</i></p>
			</div>
   			<div class="span10">
			<?php if ($item->description != '') : ?>
     			<div class="xbbox xbboxgrey" style="max-width:400px;">
    				<?php echo $item->description; ?>
    			</div>
    		<?php else: ?>
    			<p><i><?php echo Text::_('XBCULTURE_NO_DESCRIPTION'); ?></i></p>
			<?php endif; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class= "span6">
				<div class="xbbox xbboxcyan">
					<p><?php echo $item->bcnt.' '.Text::_('XBCULTURE_BOOKS_IN_CAT'); ?>  <span class="label label-success"><?php echo $item->title; ?></span></p>
					<?php if ($item->bcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->bks as $i=>$bk) { 
							echo '<li><a href="'.$xblink.'&view=book&task=book.edit&id='.$bk->bid.'">'.$bk->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
				<div class="xbbox xbboxmag">
					<p><?php echo $item->rcnt.' '.Text::_('XBCULTURE_REVIEWS_IN_CAT'); ?>  <span class="label label-success"><?php echo $item->title; ?></span></p>
					<?php if ($item->rcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->revs as $i=>$rev) { 
							echo '<li><a href="'.$xblink.'&view=review&task=review.edit&id='.$rev->rid.'">'.$rev->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
			</div>
			<div class= "span6">
				<div class="xbbox xbboxgrn">
					<p><?php echo $item->pcnt.' '.Text::_('XBCULTURE_PEOPLE_IN_CAT'); ?>  <span class="label label-success"><?php echo $item->title; ?></span></p>
					<?php if ($item->pcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->people as $i=>$per) { 
							echo '<li><a href="'.$xplink.'&view=person&task=person.edit&id='.$per->pid.'">'.$per->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
				<div class="xbbox xbboxgrey">
					<p><?php echo $item->chcnt.' '.Text::_('XBCULTURE_CHARS_IN_CAT'); ?>  <span class="label label-success"><?php echo $item->title; ?></span></p>
					<?php if ($item->chcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->chars as $i=>$char) { 
							echo '<li><a href="'.$xplink.'&view=character&task=character.edit&id='.$char->pid.'">'.$char->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="tid" value="<?php echo $item->id;?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
		</form>
	</div>
</div>
<center>
		<a href="<?php echo $xblink; ?>&view=bcategories" class="btn btn-small">
			<?php echo Text::_('XBBOOKS_CAT_LIST'); ?></a>
		</center>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>

