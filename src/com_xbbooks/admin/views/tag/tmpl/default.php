<?php
/*******
 * @package xbBooks
 * @filesource admin/views/tag/tmpl/edit.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\HTML\HTMLHelper;

$item = $this->item;
$telink = 'index.php?option=com_tags&task=tag.edit&id=';
$xblink = 'index.php?option=com_xbbooks';
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
		<form action="index.php?option=com_xbbooks&view=tag" method="post" id="adminForm" name="adminForm">
		<div class="row-fluid xbmb8">
			<div class= "span3">
				  <h3><?php echo JText::_('COM_XBBOOKS_TAG_ITEMS'); ?></h3>
			</div>
			<div class="span5">
				<a href="<?php echo $telink.$item->id; ?>" class="badge badge-info">
					<h2><?php echo $item->title; ?></h2>
				</a>
			</div>
            <div class="span2">
                <p><?php echo '<i>'.JText::_('XBCULTURE_ALIAS').'</i>: '.$item->alias; ?></p>
            </div>
			<div class= "span2">
				<p><?php echo '<i>'.JText::_('JGRID_HEADING_ID').'</i>: '.$item->id; ?></p>
			</div>
		</div>
		<div class="row-fluid xbmb8">
			<div class= "span6">
					<p class="xb11"><i><?php echo JText::_('XBCULTURE_TAG_U').' '.Jtext::_('XBCULTURE_HEIRARCHY_U'); ?>: </i>
					<?php $path = str_replace('/', ' - ', $item->path);
						echo 'root - '.$path; ?>
					</p>
			</div>
			<div class= "span6">
				<p><i><?php echo Jtext::_('XBCULTURE_ADMIN_NOTE'); ?>:</i>  <?php echo $item->note; ?></p>
			</div>
		</div>
		<div class="row-fluid xbmb8">
			<div class= "span2">
				<p><i><?php echo JText::_('XBCULTURE_DESCRIPTION'); ?>:</i></p>
			</div>
   			<div class="span10">
			<?php if ($item->description != '') : ?>
     			<div class="xbbox xbboxgrey" style="max-width:400px;">
    				<?php echo $item->description; ?>
    			</div>
    		<?php else: ?>
    			<p><i><?php echo JText::_('XBCULTURE_NO_DESCRIPTION'); ?></i></p>
			<?php endif; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class= "span6">
 				<div class="xbbox xbboxcyan">
					<p><?php echo $item->bcnt; ?> books tagged <span class="label label-info"><?php echo $item->title; ?></span></p>
					<?php if ($item->bcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->bks as $i=>$bk) { 
							echo '<li><a href="'.$xblink.'&view=book&task=book.edit&id='.$bk->bid.'">'.$bk->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
				<div class="xbbox xbboxmag">
					<p><?php echo $item->rcnt; ?> reviews tagged <span class="label label-info"><?php echo $item->title; ?></span></p>
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
					<p><?php echo $item->pcnt; ?> people tagged <span class="label label-info"><?php echo $item->title; ?></span></p>
					<?php if ($item->pcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->people as $i=>$per) { 
							echo '<li><a href="'.$xblink.'&view=person&task=person.edit&id='.$per->pid.'">'.$per->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
				<div class="xbbox xbboxcyan">
					<p><?php echo $item->chcnt; ?> Characters tagged <span class="label label-info"><?php echo $item->title; ?></span></p>
					<?php if ($item->chcnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->chars as $i=>$per) { 
							echo '<li><a href="'.$xblink.'&view=person&task=character.edit&id='.$per->pid.'">'.$per->title.'</a></li> ';
						} ?>				
						</ul>
					<?php endif; ?>
				</div>
				<div class="xbbox xbboxgrey">
					<p><?php echo $item->othercnt; ?> other items also tagged <span class="label label-info"><?php echo $item->title; ?></span></p>
					<?php if ($item->othercnt > 0 ) : ?>
						<ul>
						<?php foreach ($item->others as $i=>$oth) { 
							$comp = substr($oth->type_alias, 0,strpos($oth->type_alias, '.'));
							$ctype = substr($oth->type_alias,strpos($oth->type_alias, '.')+1);
							echo '<li><a href="index.php?option='.$comp.'">'.$comp.'</a> - '.$ctype.': '.$oth->core_title.'</li> ';
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
<div class="clearfix"></div>
<p class="xbtc xbmt16">
	<a href="<?php echo $xblink; ?>&view=tags" class="btn btn-small">
		<?php echo JText::_('COM_XBBOOKS_TAG_LIST'); ?></a>
</div>
<p><?php echo XbbooksGeneral::credit();?></p>
