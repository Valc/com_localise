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
JHtml::_('stylesheet', 'com_localise/localise.css', null, true);

$parts = explode('-', $this->state->get('translation.reference'));
$src   = $parts[0];
$parts = explode('-', $this->state->get('translation.tag'));
$dest  = $parts[0];

$session = JFactory::getSession();
$time_left = ($session->getExpire() / 60) + 1;

// No use to filter if target language is also reference language
if ($this->state->get('translation.reference') != $this->state->get('translation.tag'))
{
	$istranslation = 1;
}
else
{
	$istranslation = 0;
}

$tab    = $this->state->get('translation.tab');
$input	= JFactory::getApplication()->input;
$posted	= $input->post->get('jform', array(), 'array');

if (isset($posted['select']['keystatus'])
	&& !empty($posted['select']['keystatus'])
	&& $posted['select']['keystatus'] != 'allkeys'
	)
{
	$filter       = $posted['select']['keystatus'];
	$keystofilter = array ($this->item->$filter);

		if ($filter == 'translatedkeys')
		{
			$devkeystofilter = array ($this->item->devtranslatedkeys);
		}
		elseif ($filter == 'untranslatedkeys')
		{
			$devkeystofilter = array ($this->item->devuntranslatedkeys);
		}
		else
		{
			$devkeystofilter = array();
		}

	$tabchoised   = 'strings';
	$tabchoised2   = 'released';

		if ($tab == 'in_dev')
		{
			$tabchoised2   = $tab;
		}
}
else
{
	$filter          = 'allkeys';
	$keystofilter    = array();
	$devkeystofilter = array();
	$tabchoised      = 'default';
	$tabchoised2     = 'released';

		if ($tab == 'in_dev' || $tab == 'released')
		{
			$tabchoised   = 'strings';
			$tabchoised2   = $tab;
		}
}

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	if (typeof(Localise) === 'undefined') {
		Localise = {};
	}
	Localise.language_src = '" . $src . "';
	Localise.language_dest = '" . $dest . "';
");

$fieldSets = $this->form->getFieldsets();
$sections  = $this->form->getFieldsets('strings');
$sectionsindev  = $this->form->getFieldsets('stringsindev');
$ftpSets   = $this->formftp->getFieldsets();

// Prepare Bing translation
JText::script('COM_LOCALISE_BINGTRANSLATING_NOW');
?>
<script type="text/javascript">
	var bingTranslateComplete = false, translator;
	var Localise = {};
	Localise.language_src = '<?php echo $src; ?>';
	Localise.language_dest = '<?php echo $dest; ?>';

	function AzureTranslator(obj, targets, i, token, transUrl){
		var idname = jQuery(obj).attr('rel');
		if(translator && !translator.status){
			alert(Joomla.JText._('COM_LOCALISE_BINGTRANSLATING_NOW'));
			return;
		}

		translator =jQuery.ajax({
			type:'POST',
			uril:'index.php',
			data:'option=com_localise&view=translator&format=json&id=<?php echo $this->form->getValue('id');?>&from=<?php echo $src;?>&to=<?php echo $dest;?>&text='+encodeURI(jQuery('#'+idname+'text').val())+'&'+token+'=1',
			dataType:'json',
			success:function(res){
				if(res.success){
					jQuery('#'+idname).val(res.text);
				}
				if(targets && targets.length > (i+1)){
					AzureTranslator(targets[i+1], targets, i+1, token);
					jQuery('html,body').animate({scrollTop:jQuery(targets[i+1]).offset().top-150}, 0);
				} else {
					bingTranslateComplete = false;
					if(targets.length > 1)
						jQuery('html,body').animate({scrollTop:0}, 0);
				}
			}
		});
	}

	function returnAll()
	{
		$$('i.return').each(function(e){
			if(e.click)
				e.click();
			else
				e.onclick();
		});
	}

	function translateAll()
	{
		if(bingTranslateComplete){
			alert(Joomla.JText._('COM_LOCALISE_BINGTRANSLATING_NOW'));
			return false;
		}

		bingTranslateComplete = true;
		var targets = $$('i.translate');
		AzureTranslator(targets[0], targets, 0, '<?php echo JSession::getFormToken();?>');
	}

	Joomla.submitbutton = function(task)
	{
		if (task == 'translation.cancel' || document.formvalidator.isValid(document.id('localise-translation-form')))
		{
			Joomla.submitform(task, document.getElementById('localise-translation-form'));
		}
	}
</script>
<script type="text/javascript">
	var countdown;
	var session_expiration;

	function countdown_init()
	{
		session_expiration = '<?php echo $time_left; ?>';
		revise_countdown();
	}

	function revise_countdown()
	{
		if (session_expiration > 0)
		{
			session_expiration--;
			document.getElementById('show_countdown').innerHTML = session_expiration;

			if (session_expiration == 5)
			{
				var position_x; 
				var position_y; 
				position_x=(screen.width/2)-(100); 
				position_y=(screen.height/2)-(100);

				var w = window.open('','',"width=200,height=200,left="+position_x+",top="+position_y+"");
				w.document.write('Please, save your translation task. The session live time is 5 minutes left to finish!');
				w.focus();
				setTimeout(function() {w.close();}, 5000);
				countdown = setTimeout('revise_countdown()', 60000);
			}
			else if (session_expiration == 1)
			{
				var position_x; 
				var position_y; 
				position_x=(screen.width/2)-(100); 
				position_y=(screen.height/2)-(100);
				var w = window.open('','',"width=200,height=200,left="+position_x+",top="+position_y+"");
				w.document.write('Please, save your translation task. The session live time is 1 minute left to finish!');
				w.focus();
				setTimeout(function() {w.close();}, 5000);
				countdown = setTimeout('revise_countdown()', 60000);
			}
			else if (session_expiration > 0)
			{
				countdown = setTimeout('revise_countdown()', 60000);
			}
			else
			{
				document.getElementById('show_countdown').innerHTML = 'Expired! 0';
				window.alert("Your translation time is expired!");
				Joomla.submitform('translation.cancel', document.getElementById('localise-translation-form'));

			}
		}
	}
</script>
<form action="" method="post" name="adminForm" id="localise-translation-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Localise Translation -->
		<div class="span12 form-horizontal">
			<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => $this->ftp ? 'ftp' : $tabchoised)); ?>
					<?php if ($this->ftp) : ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ftp', JText::_($ftpSets['ftp']->label, true)); ?>
							<?php if (!empty($ftpSets['ftp']->description)):?>
								<p class="tip"><?php echo JText::_($ftpSets['ftp']->description); ?></p>
							<?php endif;?>
							<?php if (JError::isError($this->ftp)): ?>
								<p class="error"><?php echo JText::_($this->ftp->message); ?></p>
							<?php endif; ?>
							<?php foreach($this->formftp->getFieldset('ftp',false) as $field) : ?>
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
						<?php if (!empty($fieldSets['default']->description)) : ?>
							<p class="alert alert-info"><?php echo JText::_($fieldSets['default']->description); ?></p>
						<?php endif;?>
						<?php foreach($this->form->getFieldset('default') as $field) : ?>
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
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'strings', JText::_('COM_LOCALISE_FIELDSET_TRANSLATION_STRINGS')); ?>
						<div class="accordion" id="com_localise_legend_translation">
							<div class="accordion-group">
								<div class="accordion-heading">
									<a class="accordion-toggle alert-info" data-toggle="collapse" data-parent="com_localise_legend_translation" href="#legend">
										<?php echo JText::_($fieldSets['legend']->label);?>
									</a>
								</div>
								<div id="legend" class="accordion-body collapse">
									<div class="accordion-inner">
										<?php if (!empty($fieldSets['legend']->description)) : ?>
											<p class="tip"><?php echo JText::_($fieldSets['legend']->description); ?></p>
										<?php endif; ?>
										<ul class="adminformlist">
										<?php foreach($this->form->getFieldset('legend') as $field) : ?>
											<li>
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</li>
										<?php endforeach; ?>
										</ul>
									</div>
								</div>
							</div>
						</div>

							<div id="translationbar">
								<?php if ($istranslation) : ?>
									<div class="pull-left">
										<?php foreach($this->form->getFieldset('select') as $field): ?>
											<?php if ($field->type != "Spacer") : ?>
												<?php
													$field->value = $filter;
													echo JText::_('JSEARCH_FILTER_LABEL');
													echo $field->input;
												?>
											<?php else : ?>
												<?php echo $field->label; ?>
											<?php endif; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
<div class="pull-right">
<div>Session time left: <span id="show_countdown">Unset</span> minutes. </div>
</div>
								<a href="javascript:void(0);" class="btn bnt-small" id="translateall" onclick="translateAll();">
									<i class="icon-translate-bing"></i> <?php echo JText::_('COM_LOCALISE_BUTTON_TRANSLATE_ALL');?>
								</a>
								<a href="javascript:void(0);" class="btn bnt-small" onclick="returnAll();">
									<i class="icon-reset"></i> <?php echo JText::_('COM_LOCALISE_BUTTON_RESET_ALL');?>
								</a>
							</div>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab2', array('active' => $tabchoised2)); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab2', 'released', JText::_('COM_LOCALISE_FIELDSET_TRANSLATION_RELEASED')); ?>
						<div class="key">
							<?php
								$showed_data = 0;
								if (count($sections) > 1) :
									echo '<br />';
									echo JHtml::_('bootstrap.startAccordion', 'localise-translation-sliders');
								endif;
									$i = 0;
									foreach ($sections as $name => $fieldSet) :
										if (count($sections) > 1) :
										echo JHtml::_('bootstrap.addSlide',
										'localise-translation-sliders',
										JText::_($fieldSet->label), 'collapse' . $i++);
										endif;
							?>
							<ul class="adminformlist">
								<?php
								foreach ($this->form->getFieldset($name) as $field) :
									$showkey = 0;
									if ($filter != 'allkeys' && !empty($keystofilter)) :
										foreach ($keystofilter as $data => $ids) :
											foreach ($ids as $keytofilter) :
												$showkey = 0;
												$pregkey = preg_quote('<b>'
												. $keytofilter
												.'</b>', '/<>');
												if (preg_match("/$pregkey/", $field->label)) :
													$showkey = 1;
													break;
												endif;
											endforeach;
										endforeach;
										if ($showkey == '1') : $showed_data = 1; ?>
											<li>
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</li>
										<?php else : ?>
											<div style="display:none;">
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</div>
										<?php endif; ?>
									<?php elseif ($filter == 'allkeys') : $showed_data = 1; ?>
										<li>
											<?php echo $field->label; ?>
											<?php echo $field->input; ?>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
							<?php
								if (count($sections) > 1) :
								echo JHtml::_('bootstrap.endSlide');
								endif;

								endforeach;

								if (count($sections) > 1) :
								echo JHtml::_('bootstrap.endAccordion');
								endif;
							?>
						<?php if ($showed_data == '0') : ?>
						<?php echo "<p>No matches to show.</p>"; ?>
						<?php endif; ?>
						</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab2', 'in_dev', JText::_('COM_LOCALISE_FIELDSET_TRANSLATION_IN_DEV')); ?>
						<div class="key">
							<?php
								$showed_dev_data = 0;
								if (count($sectionsindev) > 1) :
									echo '<br />';
									echo JHtml::_('bootstrap.startAccordion', 'localise-translation-sliders');
								endif;
									//$i = 0;
									foreach ($sectionsindev as $name => $fieldSet) :
										if (count($sectionsindev) > 1) :
										echo JHtml::_('bootstrap.addSlide',
										'localise-translation-sliders',
										JText::_($fieldSet->label), 'collapse' . $i++);
										endif;
							?>
							<ul class="adminformlist">
								<?php
								foreach ($this->form->getFieldset($name) as $field) :
									$showkey = 0;
									if (	($filter == 'translatedkeys'
										|| $filter == 'untranslatedkeys')) :
										foreach ($devkeystofilter as $dev_id => $dev_data) :
											foreach ($dev_data as $dev_name => $ids) :
												if ($dev_name == $name) :
													foreach ($ids as $keytofilter) :
														$showkey = 0;
														$pregkey = preg_quote('<b>'
														. $keytofilter
														.'</b>', '/<>');
														if (preg_match("/$pregkey/", $field->label)) :
															$showkey = 1;
															break;
														endif;
													endforeach;
												endif;
											endforeach;
										endforeach;
										if ($showkey == '1') : $showed_dev_data = 1; ?>
											<li>
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</li>
										<?php else : ?>
											<div style="display:none;">
												<?php echo $field->label; ?>
												<?php echo $field->input; ?>
											</div>
										<?php endif; ?>
									<?php elseif ($filter != 'allkeys') : ; ?>
										<div style="display:none;">
											<?php echo $field->label; ?>
											<?php echo $field->input; ?>
										</div>
									<?php elseif ($filter == 'allkeys') : $showed_dev_data = 1; ?>
										<li>
											<?php echo $field->label; ?>
											<?php echo $field->input; ?>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>

							</ul>
							<?php
								if (count($sectionsindev) > 1) :
								echo JHtml::_('bootstrap.endSlide');
								endif;

								endforeach;

								if (count($sectionsindev) > 1) :
								echo JHtml::_('bootstrap.endAccordion');
								endif;
							?>
						<?php if ($showed_dev_data == '0') : ?>
						<?php echo "<p>No matches in develop to show.</p>"; ?>
						<?php endif; ?>
						</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_($fieldSets['permissions']->label, true)); ?>
						<?php if (!empty($fieldSets['permissions']->description)):?>
							<p class="tip"><?php echo JText::_($fieldSets['permissions']->description); ?></p>
						<?php endif;?>
						<?php foreach($this->form->getFieldset('permissions') as $field) : ?>
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
		<!-- End Localise Translation -->
	</div>
</form>
<script>
window.onload = countdown_init; 
</script>
