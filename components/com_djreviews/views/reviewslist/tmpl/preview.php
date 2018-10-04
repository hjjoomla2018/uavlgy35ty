<?php
/**
 * @version $Id: default.php 58 2017-03-30 14:10:35Z michal $
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
$params = DJReviewsHelper::getParams($this->review_object->rating_group_id);
$schemaWrapper = $params->get('schema_wrapper', '');
?>
<div class="djrv_reviews_list djreviews clearfix">
<?php if ($params->get('reviews', '1') == '1') { ?>

<?php if ($schemaWrapper) { ?>
<div itemscope itemtype="http://schema.org/<?php echo $schemaWrapper;?>">
<meta itemprop="name" content="<?php echo $this->escape($this->review_object->name); ?>" />

<?php if (!empty($this->review_object->_rating)) { ?>
	<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
	<meta itemprop="ratingValue" content="<?php echo $this->review_object->_rating->avg_rate;?>" />
	<meta itemprop="reviewCount" content="<?php echo $this->review_object->_rating->rat_count; ?>" />
	<meta itemprop="worstRating" content="1"/>
	<meta itemprop="bestRating" content="5"/>
	</div>
<?php } ?>
<?php } ?>

<h3><?php echo JText::_('COM_DJREVIEWS_USERS_REVIEWS_HEADING'); ?></h3>

<?php if (count($this->items) > 0) {?>
<div class="djrv_listing row-striped">
<?php foreach ($this->items as $item) { ?>
	<?php if ($item->published == 0) continue;?>
	
	<?php 
	$avatar = ($item->created_by > 0) ? $params->get('registered_avatar') : $params->get('public_avatar');
	if ($avatar && $avatar != '-1') {
		$avatar = JUri::base(true).'/media/djreviews/avatars/'.$avatar;
	} else {
		$avatar = false;
	}
	?> 
	
	<div class="djrv_single_review row-fluid djrv_clearfix" itemprop="review" itemscope itemtype="http://schema.org/Review">
	<?php if ($avatar) {?>
		<div class="span3 djrv_user_avatar"><img alt="<?php echo $item->user_login; ?>" src="<?php echo $avatar; ?>" /></div>
	<?php } ?>
	<div class="span<?php echo $avatar ? '9' : '12'; ?> djrv_user_rating">
	<meta itemprop="itemReviewed" content="<?php echo $item->object_name; ?>" />
		<?php if ($item->avg_rate > 0.0 && $params->get('rating', '1') == '1') { ?>
		<div class="djrv_rating djrv_user_rating pull-right" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<span class="djrv_stars">
			<?php /*for ($i = 1; $i <= 5; $i++) {?>
				<span class="djrv_star <?php if ($i <= $item->avg_rate || ($item->avg_rate - $i) >= -0.5) echo 'active';?>"></span>
			<?php }*/ ?>
			<span class="djrv_stars_sprite"><span class="djrv_stars_sprite_rating" style="width:<?php echo 100 * ($item->avg_rate / 5); ?>%;"></span></span>
			
				<span class="djrv_avg">
					<?php echo $item->avg_rate; ?>
				</span>
			
			</span>
			
			<meta itemprop="worstRating" content="1"/>
      		<meta itemprop="ratingValue" content="<?php echo $item->avg_rate; ?>" />
      		<meta itemprop="bestRating" content="5"/>
		</div>
		<?php } ?>
		
		<?php if ($item->title && (int)$params->get('title', 2) > 0) {?>
		<h4 itemprop="name"><?php echo $item->title; ?></h4>
		<?php } ?>
		
		<div class="djrv_post_info">
			<?php /*?>
			<div class="djrv_poster small">
				<?php echo JText::_('COM_DJREVIEWS_POSTED_BY')?><span><?php echo $item->user_login; ?></span>
			</div>
			<div class="djrv_review_date small">
				<?php echo JText::_('COM_DJREVIEWS_POSTED_ON');?><span><?php echo JHtml::_('date', $item->created, $params->get('date_format', 'd-m-Y H:i'));?></span>
			</div>
			<?php */ ?>
			<div class="djrv_poster small">
				<?php echo JText::sprintf('COM_DJREVIEWS_POSTED_BY_ON', '<span itemprop="author">'.$item->user_login.'</span>', JHtml::_('date', $item->created, $params->get('date_format', 'd-m-Y H:i'))); ?>
				<meta itemprop="datePublished" content="<?php echo JHtml::_('date', $item->created, 'Y-m-d'); ?>" />
			</div>
		</div>
		
		<?php if ($item->message && (int)$params->get('message', 2) > 0) {?>			
		<p class="djrv_message_quote" itemprop="reviewBody"><?php echo nl2br($item->message); ?></p>
		<?php } ?>
		
		<?php if (!empty($item->text_rating_list)) {?>
			<?php foreach($item->text_rating_list as $entry) {?>
				<p>
					<strong><?php echo $entry->f_name;?></strong><br /><br />
					<?php echo nl2br($entry->rating); ?>
				</p>
			<?php } ?>
		<?php } ?>
		
		</div>
	</div>
<?php } ?>
</div>
<?php } ?>
<?php } ?>

<?php if ($schemaWrapper && $params->get('reviews', '1') == '1') {?>
</div>
<?php } ?>

</div>
