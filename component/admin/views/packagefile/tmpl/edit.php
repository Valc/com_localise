<?php
/**
 * @package     Com_Localise
 * @subpackage  views
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');

$fieldSets = $this->form->getFieldsets();
$ftpSets   = $this->formftp->getFieldsets();
JText::script('COM_LOCALISE_MSG_CONFIRM_PACKAGE_SAVE');

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if ((task == 'packagefile.apply' || task == 'packagefile.save') && document.formvalidator.isValid(document.id('localise-package-form')))
		{
			if (confirm(Joomla.JText._('COM_LOCALISE_MSG_CONFIRM_PACKAGE_SAVE')))
			{
				Joomla.submitform(task, document.getElementById('localise-package-form'));
			}
		}
		else if (task == 'packagefile.cancel' || task == 'packagefile.download' || task == 'packagefile.downloaddev')
		{
			Joomla.submitform(task, document.getElementById('localise-package-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_localise&view=packagefile&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="localise-package-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Localise Package -->
		<div class="span12 form-horizontal">
			<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => $this->ftp ? 'ftp' : 'default')); ?>
					<?php if ($this->ftp) : ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ftp', JText::_($ftpSets['ftp']->label, true)); ?>
						<?php if (!empty($ftpSets['ftp']->description)):?>
								<p class="tip"><?php echo JText::_($ftpSets['ftp']->description); ?></p>
						<?php endif;?>
						<?php if (JError::isError($this->ftp)): ?>
								<p class="error"><?php echo JText::_($this->ftp->message); ?></p>
						<?php endif; ?>
						<?php foreach($this->formftp->getFieldset('ftp',false) as $field): ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endforeach; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'default', JText::_($fieldSets['default']->label, true)); ?>
						<div class="span6">
							<?php if (!empty($fieldSets['default']->description)):?>
									<p class="tip"><?php echo JText::_($fieldSets['default']->description); ?></p>
							<?php endif;?>
							<?php foreach($this->form->getFieldset('default') as $field): ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
							<?php endforeach; ?>
						</div>
						<div class="span6">
						<?php echo JHtml::_('bootstrap.startTabSet', 'myTab2', array('active' => 'released')); ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab2', 'released', 'Released') ?>
							<?php echo JText::_($fieldSets['translations']->label); ?>
							<?php if (!empty($fieldSets['translations']->description)):?>
									<p class="tip"><?php echo JText::_($fieldSets['translations']->description); ?></p>
							<?php endif;?>
							<?php foreach($this->form->getFieldset('translations') as $field): ?>
									<div class="control-group form-vertical">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
							<?php endforeach; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab2', 'indev', 'In dev'); ?>
							<?php echo JText::_($fieldSets['translationsindev']->label); ?>
							<?php if (!empty($fieldSets['translationsindev']->description)):?>
									<p class="tip"><?php echo JText::_($fieldSets['translationsindev']->description); ?></p>
							<?php endif;?>
							<?php foreach($this->form->getFieldset('translationsindev') as $field): ?>
									<div class="control-group form-vertical">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
							<?php endforeach; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
						<?php echo JHtml::_('bootstrap.endTabSet'); ?>
						</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'indevconfig', JText::_($fieldSets['indevconfig']->label, true)); ?>
						<?php if (!empty($fieldSets['indevconfig']->description)):?>
								<p class="tip"><?php echo JText::_($fieldSets['indevconfig']->description); ?></p>
						<?php endif;?>
						<?php foreach($this->form->getFieldset('indevconfig') as $field): ?>
								<div class="control-group form-vertical">
									<div class="control-label">
											<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endforeach; ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_($fieldSets['permissions']->label, true)); ?>
						<?php if (!empty($fieldSets['permissions']->description)):?>
								<p class="tip"><?php echo JText::_($fieldSets['permissions']->description); ?></p>
						<?php endif;?>
						<?php foreach($this->form->getFieldset('permissions') as $field): ?>
								<div class="control-group form-vertical">
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endforeach; ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>

					<input type="hidden" name="task" value="" />

					<?php echo JHtml::_('form.token'); ?>

				<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</fieldset>
		</div>
		<!-- End Localise Package -->
	</div>
</form>
