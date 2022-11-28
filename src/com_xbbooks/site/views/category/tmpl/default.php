<?php
/*******
 * @package xbBooks
 * @filesource site/views/category/tmpl/default.php
 * @version 0.10.0.4 28th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Text;

$item = $this->item;

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getBooksRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$blink = 'index.php?option=com_xbbooks&view=book'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getPeopleRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$plink = 'index.php?option=com_xbpeople&view=person'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getCharsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$chlink = 'index.php?option=com_xbpeople&view=character'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getReviewsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$rlink = 'index.php?option=com_xbbooks&view=bookreview'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
if ($itemid !== null) {
	$cclink = 'index.php?option=com_xbbooks&Itemid='.$itemid.'';
} else {
	$cclink = 'index.php?option=com_xbbooks&view=categories';
}

$show_catdesc = $this->params->get('show_catdesc',1);

?>
<div class="xbculture ">
<div class="row-fluid" style="margin-bottom:20px;">
	<div class="span3">
		<h3 style="padding-top:3px;"><?php echo Text::_('XBCULTURE_CATEGORY'); ?>:</h3>		
	</div>	
	<div class="span9">         
		<?php if (($this->show_catpath) && ($item->level>1)) : ?>
			<div class="xb11 pull-left xbmr10 xbpt17 xbit xbgrey">				
				<?php  $path = substr($item->path, 0, strrpos($item->path, '/'));
					$path = str_replace('/', ' - ', $path);
					echo $path.' - ' ; ?>
        	</div>
        <?php endif; ?>
		<div class="badge badge-success pull-left"><h3><?php echo $item->title; ?></h3></div>
	</div>	
</div>
<?php if (($show_catdesc) && ($item->description != '')) : ?>
	<div class="row-fluid">
		<div class= "span2">
			<p><i>Description:</i></p>
		</div>
		<div class="span10">
			<?php echo $item->description; ?>
		</div>
	</div>
<?php endif; ?>
<div class="row-fluid">
	<?php if($item->extension == 'com_xbbooks') : ?>
    	<?php if(($item->bcnt > 0) || (!$this->hide_empty)) : ?>
        	<div class= "span6">
        		<div class="xbbox xbboxcyan xbyscroll xbmh300">
        			<p><?php echo $item->bcnt; ?> books</p>
        			<?php if ($item->bcnt > 0 ) : ?>
        				<ul>
        				<?php foreach ($item->bks as $i=>$bk) { 
        					echo '<li><a href="'.$blink.$bk->bid.'">'.$bk->title.'</a></li> ';
        				} ?>				
        				</ul>
        			<?php else: ?>
        				<p class="xbnit"><?php echo Text::_('XBCULTURE_CAT_NO_ITEMS')?></p>
        			<?php endif; ?>
        		</div>
        	</div>
      	<?php endif; ?>
    	<?php if(($item->rcnt > 0) || (!$this->hide_empty)) : ?>
        	<div class="span6">
        		<div class="xbbox xbboxmag xbyscroll xbmh300">
        			<p><?php echo $item->rcnt; ?> reviews</p>
        			<?php if ($item->rcnt > 0 ) : ?>
        				<ul>
        				<?php foreach ($item->revs as $i=>$rev) { 
        					echo '<li><a href="'.$rlink.$rev->rid.'">'.$rev->title.'</a></li> ';
        				} ?>				
        				</ul>
        			<?php endif; ?>
        		</div>
        	</div>
    	<?php endif; ?>
	<?php else: ?>
    	<?php if(($item->pcnt > 0) || (!$this->hide_empty)) : ?>
        	<div class= "span6">
        		<div class="xbbox xbboxgrn  xbyscroll xbmh300">
        			<p><?php echo $item->pcnt; ?> people</p>
        			<?php if ($item->pcnt > 0 ) : ?>
        				<ul>
        				<?php foreach ($item->people as $i=>$per) { 
        					echo '<li><a href="'.$plink.$per->pid.'">'.$per->title.'</a></li> ';
        				} ?>				
        				</ul>
        			<?php endif; ?>
        		</div>
        	</div>
     	<?php endif; ?>
    	<?php if(($item->chcnt > 0) || (!$this->hide_empty)) : ?>
        	<div class="span6">
        		<div class="xbbox xbboxcyan  xbyscroll xbmh300">
        			<p><?php echo $item->chcnt; ?> characters</p>
        			<?php if ($item->chcnt > 0 ) : ?>
        				<ul>
        				<?php foreach ($item->chars as $i=>$char) { 
        					echo '<li><a href="'.$chlink.$char->pid.'">'.$char->title.'</a></li> ';
        				} ?>			
        				</ul>
        			<?php endif; ?>
        		</div>
        	</div>
     	<?php endif; ?>
	<?php endif; ?>
</div>
<div class="clearfix"></div>
<p class="xbtc xbmt16">
	<a href="<?php echo $cclink; ?>" class="btn btn-small">
		<?php echo JText::_('XBBOOKS_CAT_COUNTS'); ?>
	</a>
</p>
<div class="clearfix"></div>
</div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>

