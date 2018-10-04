<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die ('Restricted access');
?>
<div class="djf_mod djf_default <?php echo $moduleclass_sfx; ?>" id="djf_mod_<?php echo $mid;?>">
	<div class="djf_left">
		<div class="djf_gal" id="djfgal_<?php echo $mid;?>">
			<div class="djf_thumbwrapper">
				<!-- gallery  START -->
			<?php
			$counter = 0;
			for ($row = 0; $row < $params->get('rows'); $row++)
			{
				?>
				<div class="djf_row">
				<?php
				for ($col = 0; $col < $params->get('cols'); $col++)
				{
					?>
					<div id="djfptd_<?php echo $mid;?>_<?php echo $counter;?>" class="djc_col">
					</div>
						<?php
						$counter++;
				}
				?>
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<!-- gallery  END -->

		<!-- fullsize image outer START -->
		<div class="djf_img">
			<!-- fullsize image  START -->
			<?php $imgclass = $params->get('modalimage', 1) == 1 ? 'modal' : ''; ?>
			<?php $target = $params->get('modalimage', 1) == 1 ? 'target="_blank"' : ''; ?>
			<a style="display: block" id="djfimg_<?php echo $mid;?>"
				<?php echo $target; ?> class="<?php echo $imgclass; ?>"></a>
			<!-- fullsize image  END -->
		</div>
		<!-- fullsize image outer END -->
	</div>

	<!-- intro text outer START -->
	<div class="djf_text">
		<!-- Category Title START -->
	<?php if ($params->get('showcattitle') == 1) { ?>
		<div class="djf_cat">
			<div id="djfcat_<?php echo $mid;?>"></div>
		</div>
		<?php } ?>
		<!-- Category Title END -->

		<div id="djftext_<?php echo $mid;?>">
			<!-- intro text  START -->
		</div>
		<!-- intro text  END -->
	</div>
	<!-- intro text outer END -->

	<!-- pagination START -->
	<?php if ((int)$params->get('showpagination') > 0) { ?>
	<div style="clear: both;"></div>
	<div class="djf_pag">
		<div id="djfpag_<?php echo $mid;?>"></div>
	</div>
	<?php } ?>
	<!-- pagination END -->
</div>
