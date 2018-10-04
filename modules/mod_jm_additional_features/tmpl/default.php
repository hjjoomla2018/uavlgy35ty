<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Additional Features is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Additional Features is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Additional Features. If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$i = 0;
$row = 0;

?>

<div id="<?php echo $id; ?>" class="jmm-add-features <?php echo $theme_class . ' ' . $icon_side . ' ' . $mod_class_suffix; ?>">
	<div class="jmm-add-features-in">
		<div class="jmm-items <?php echo 'rows-' . $columns; ?>">
				<div class="jmm-row row-<?php echo $row; ?>">
				<?php foreach($output_data as $item) {

					if($i % $columns == 0 && $i > 0) {
						$row++;
						echo '</div><div class="jmm-row row-' . $row . '">';
					}

					$i++;
					$alt = ( !empty($item->alt) ) ? $item->alt : '';
				?>

				<div class="jmm-item item-<?php echo $i; ?>">
					<div class="jmm-item-in">
					<?php if( $icon_side == 'left' || $icon_side == 'above' ) :

						if( !empty($item->image_icon) ) {
							echo '<div class="jmm-icon image">';
							if( !empty($item->url) && $icon_link ) {
								echo '<a href="' . $item->url . '">';
							}
							echo '<img src="' . $item->image_icon . '" alt="' . $alt . '">';
							if( !empty($item->url) && $icon_link ) {
								echo '</a>';
							}
							echo'</div>';
						} elseif( !empty($item->icon) ) {
							echo '<div class="jmm-icon">';
							if( !empty($item->url) && $icon_link ) {
								echo '<a href="' . $item->url . '" aria-label="' . $alt . '">';
							}
							echo '<span class="' . $item->icon . '" aria-hidden="true"></span>';
							if( !empty($item->url) && $icon_link ) {
								echo '</a>';
							}
							echo '</div>';
						}

					endif; ?>

					<?php if( $item->title || $item->text ) : ?>
					<div class="jmm-description">
						<?php if( $item->title ) : ?>
							<div class="jmm-title">
								<?php if( $item->url ) : ?>
									<a href="<?php echo $item->url; ?>">
								<?php endif; ?>

									<?php echo $item->title; ?>

								<?php if( $item->url ) : ?>
									</a>
								<?php endif; ?>

							</div>
						<?php endif; ?>

						<?php if( $item->text ) : ?>
							<div class="jmm-text"><?php echo $item->text; ?></div>
						<?php endif; ?>
						<?php if( !empty($item->url) && !empty($item->item_readmore) && $item->item_readmore == 1 ) : ?>
							<a class="readmore" href="<?php echo $item->url; ?>"><?php echo JText::_('MOD_JM_ADDITIONAL_FEATURES_FIELD_READMORE_NAME'); ?></a>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<?php if( $icon_side == 'right' ) :

						if( !empty($item->image_icon) ) {
							echo '<div class="jmm-icon image">';
							if( !empty($item->url) && $icon_link ) {
								echo '<a href="' . $item->url . '">';
							}
							echo '<img src="' . $item->image_icon . '" alt="' . $alt . '">';
							if( !empty($item->url) && $icon_link ) {
								echo '</a>';
							}
							echo'</div>';
						} elseif( !empty($item->icon) ) {
							echo '<div class="jmm-icon">';
							if( !empty($item->url) && $icon_link ) {
								echo '<a href="' . $item->url . '" aria-label="' . $alt . '">';
							}
							echo '<span class="' . $item->icon . '" aria-hidden="true"></span>';
							if( !empty($item->url) && $icon_link ) {
								echo '</a>';
							}
							echo '</div>';
						}

					endif; ?>
					</div>
				</div>

				<?php } ?>
				</div>
		</div>
		<?php if( $show_readmore && $readmore_name && $readmore_url ) : ?>
			<div class="jmm-button"><a href="<?php echo $readmore_url; ?>" class="btn"><?php echo $readmore_name; ?></a></div>
		<?php endif; ?>

	</div>
</div>
