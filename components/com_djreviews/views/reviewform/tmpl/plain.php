<?php
/**
 * @version $Id: default.php 53 2016-12-29 11:59:48Z michal $
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

//JHtml::_('behavior.tooltip', '.djrv_tooltip');
JHtml::_('behavior.formvalidator');

$user = JFactory::getUser();
$app = JFactory::getApplication();
$params = DJReviewsHelper::getParams((int)$this->form->getValue('rating_group_id'));

$canRate = true;
$canSubmit = true;
foreach ($this->userRating as $userRate) {
	if ($userRate->avg_rate > 0 && (int)$this->item->id != $userRate->id) {
		$canRate = false;
	}
}

if (!$canRate && ((int)$params->get('message', 2) == 0 || (int)$params->get('followup', 1) == 0)) {
	$canSubmit = false;
}

$style = 'style="display: none;"';
if ($app->input->get('djreviews_action') == 'add' || $app->input->get('djreviews_action') == 'edit') {
	$style= '';
}

//custom
$style= '';

$captcha = $app->input->getString('captcha');

?>
<div id="djrv-your-review-<?php echo (int)$this->form->getValue('object_id'); ?>" <?php echo $style; ?> class="djrv_review_form djreviews djrv_clearfix">
<?php if ($user->authorise('core.create', 'com_djreviews') || ($user->authorise('review.create', 'com_djreviews'))) { ?>

<h3><?php echo JText::_('COM_DJREVIEWS_YOUR_REVIEW'); ?></h3>

<div id="djrv_msg-<?php echo (int)$this->form->getValue('object_id'); ?>"></div>

<form action="<?php echo JRoute::_('index.php?option=com_djreviews'); /*(string)JUri::getInstance();*/ ?>" method="post" name="djrv_review_form" id="djrv_review_form-<?php echo (int)$this->form->getValue('object_id'); ?>" class="form-validate djrv_review_form">
	<?php if (!$canSubmit) {?>
		<p><?php echo JText::_('COM_DJREVIEWS_CANNOT_ADD_MORE_REVIEWS'); ?></p>
	<?php } else {?>
	<div class="row-fluid clearfix">
		<div class="span12 form-horizontal">
			<fieldset>
			<?php if ($user->guest) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('user_name'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_login'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('user_login'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('email'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('email'); ?></div>
				</div>
				<?php if ($captcha) {?>
					<div class="control-group">
						<div class="controls">
							<div id="djrv_captcha-<?php echo (int)$this->form->getValue('object_id'); ?>" data-sitekey="<?php echo $captcha; ?>" data-theme="light" data-size="compact"></div>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
			
			<?php if ((int)$params->get('title', 2) > 0 ) {?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<?php } ?>
			<?php if ((int)$params->get('message', 2) > 0 ) {?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('message'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('message'); ?></div>
			</div>
			<?php } ?>
			</fieldset>
			
				
				<div class="djrv_rating_fields">
				<?php if (count($this->item->rating_fields) && $canRate) { ?>
					<?php $rating_state = $this->form->getValue('rating'); ?>
					<?php foreach($this->item->rating_fields as $field_id => $field) {?>
					<?php 
					if (!empty($rating_state) && isset($rating_state[$field_id])) {
						$field->rating = $rating_state[$field_id];
					}
					?>
					<div class="djrv_rating_field">
						<?php if ($field->type != 'rating') {?>
							<div class="control-group">
								<div class="control-label">
									<?php if ($field->description) { ?>
										<label for="djrv_rating_<?php echo $field_id?>" class="djrv_field_name djrv_tooltip" title="<?php echo $field->label; ?>" data-content="<?php echo htmlspecialchars($field->description); ?>"><?php echo $field->label; ?><?php if ($field->required == 1) echo '&nbsp;<sup>*</sup>';?></label>
									<?php } else {?>
										<label for="djrv_rating_<?php echo $field_id?>" class="djrv_field_name"><?php echo $field->label; ?><?php if ($field->required == 1) echo '&nbsp;<sup>*</sup>';?></label>
									<?php } ?>
								</div>
								<div class="controls">
									<?php if ($field->type == 'list') {?>
										<?php 
										$options = array();
										$options_set = explode(PHP_EOL, $field->list_options); 
										foreach($options_set as $option_element) {
											$pair = explode('=', $option_element, 2);
											if (count($pair) == 2) {
												$opt = new stdClass();
												$opt->value = htmlspecialchars(trim($pair[0]));
												$opt->text = $pair[1];
												$options[] = $opt;
											}
										}
										?>
										<select id="djrv_rating_<?php echo $field_id?>" class="input input-medium <?php echo $field->required ? 'required' : ''; ?>" name="jform[rating][<?php echo $field_id; ?>]">
											<?php foreach ($options as $option) {?>
												<?php 
													$selected = $option->value == $field->rating ? 'selected="selected"' : '';
												?>
												<option value="<?php echo $option->value; ?>" <?php echo $selected; ?>><?php echo $option->text; ?></option>
											<?php } ?>
										</select>
									<?php } else { ?>
										<?php 
										$class = 'input input-medium';
										$class .= $field->required ? ' required' : '';
										$extra = '';
										if ($field->regular_exp) {
											$class .= ' validate-attribute'.$field->id;
											if (!$field->required) {
												$extra = ' onfocus="jQuery(this).addClass(\'required\')" onblur="if (this.value != \'\') {jQuery(this).addClass(\'required\');} else {jQuery(this).removeClass(\'required\')}" ';
											}
										} ?>
									
										<input id="djrv_rating_<?php echo $field_id?>" type="text" class="<?php echo $class; ?>" name="jform[rating][<?php echo $field_id; ?>]" value="<?php echo $field->rating; ?>" <?php echo $extra; ?>/>
										<?php if ($field->regular_exp) { ?>
											<script>
												jQuery(document).ready(function(){
													if (typeof document.formvalidator != 'undefined') {
														try {
														document.formvalidator.setHandler("attribute<?php echo $field->id; ?>", function(value) {
															regex = new RegExp(/<?php echo $field->regular_exp; ?>/);
																return regex.test(value);
															});
														} catch(e) {
															console.log(e);
														}
													}
												});
											</script>
										<?php }?>
									<?php } ?>
								</div>
							</div>
						<?php } else {?>
						
							<fieldset class="radio <?php if ($field->required == 1) echo 'required';?>" id="djrv_rating_<?php echo $field_id?>">
								<div class="control-group">
									<div class="control-label">
										<?php if ($field->description) { ?>
											<label class="djrv_field_name djrv_tooltip" for="djrv_rating_<?php echo $field_id?>" title="<?php echo $field->label; ?>" data-content="<?php echo htmlspecialchars($field->description); ?>"><?php echo $field->label; ?><?php if ($field->required == 1) echo '&nbsp;<sup>*</sup>';?></label>
										<?php } else {?>
											<label class="djrv_field_name" for="djrv_rating_<?php echo $field_id?>"><?php echo $field->label; ?><?php if ($field->required == 1) echo '&nbsp;<sup>*</sup>';?></label>
										<?php } ?>
									</div>
								<div class="controls">
										<div class="djrv_field_rating">
											<div>
												<div>
													<div>
														<div>
															<input id="djrv_rating_<?php echo $field_id?>-0" type="radio" name="jform[rating][<?php echo $field_id; ?>]" value="1" <?php if (floor($field->rating) == 1) echo 'checked="checked"'; ?>>
															<label for="djrv_rating_<?php echo $field_id?>-0"><span>1</span></label>
														</div>
														<input id="djrv_rating_<?php echo $field_id?>-1" type="radio" name="jform[rating][<?php echo $field_id; ?>]" value="2" <?php if (floor($field->rating) == 2) echo 'checked="checked"'; ?>>
														<label for="djrv_rating_<?php echo $field_id?>-1"><span>2</span></label>
													</div>
													<input id="djrv_rating_<?php echo $field_id?>-2" type="radio" name="jform[rating][<?php echo $field_id; ?>]" value="3" <?php if (floor($field->rating) == 3) echo 'checked="checked"'; ?>>
													<label for="djrv_rating_<?php echo $field_id?>-2"><span>3</span></label>
												</div>
												<input id="djrv_rating_<?php echo $field_id?>-3" type="radio" name="jform[rating][<?php echo $field_id; ?>]" value="4" <?php if (floor($field->rating) == 4) echo 'checked="checked"'; ?>>
												<label for="djrv_rating_<?php echo $field_id?>-3"><span>4</span></label>
											</div>
											<input id="djrv_rating_<?php echo $field_id?>-4" type="radio" name="jform[rating][<?php echo $field_id; ?>]" value="5" <?php if (floor($field->rating) == 5) echo 'checked="checked"'; ?>>
											<label for="djrv_rating_<?php echo $field_id?>-4"><span>5</span></label>
										</div>
									</div>
								</div>
							</fieldset>
						<?php } ?>
					</div>
					<?php } ?>
				<?php } else if (!$canRate) {?>
				<div class="control-group">
					<div class="controls">
						<p><?php echo JText::_('COM_DJREVIEWS_CANNOT_ADD_MORE_REVIEWS'); ?></p>
					</div>
				</div>
				<?php } ?>
				</div>
				
		
		<fieldset>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn button validate btn-success"><?php echo JText::_('COM_DJREVIEWS_SUBMIT');?></button>
					<?php /*?><button type="button" class="djrv_close_form_button btn button"><?php echo JText::_('COM_DJREVIEWS_CLOSE')?></button><?php */ ?>
				</div>
			</div>
		</fieldset>
		</div>
	<input type="hidden" name="task" value="review.save" />
	<input type="hidden" name="option" value="com_djreviews" />
	<input type="hidden" name="id" value="<?php echo (int)$this->item->id; ?>" />
	
	<?php 
	JUri::reset();

	$uri = JUri::getInstance();
	$query = $uri->getQuery(true);
	foreach ($query as $k=>$v) {
		if (strpos($k, 'djreviews_') === 0) {
			unset($query[$k]);
		}
	}
	$uri->setQuery($query);
	$uri->setFragment(null);
	$return_url = $uri->toString();
	JUri::reset();

	?>
	
	<input type="hidden" name="return" value="<?php echo base64_encode($return_url)?>" />

	<?php echo $this->form->getInput('object_id'); ?>
	<?php echo $this->form->getInput('rating_group_id'); ?>
	<?php echo $this->form->getInput('object_type'); ?>
	
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
	</div>
	<?php } ?>
</form>
<?php } ?>
</div>