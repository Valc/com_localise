<?php
/**
 * @package     Com_Localise
 * @subpackage  models
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Form Field Key class.
 *
 * @package     Extensions.Components
 * @subpackage  Localise
 *
 * @since       1.0
 */
class JFormFieldKey extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Key';

	/**
	 * Method to get the field label.
	 *
	 * @return  string    The field label.
	 */

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since  1.6
	 */
	protected function getLabel()
	{
		$istranslation = (int) $this->element['istranslation'];
		$status = (string) $this->element['status'];

		$visibleiduntranslatable = "visible_untranslatable_" . $this->element['name'];
		$hiddediduntranslatable = "hidded_untranslatable_" . $this->element['name'];

		$visibleidblocked = "visible_blocked_" . $this->element['name'];
		$hiddedidblocked = "hidded_blocked_" . $this->element['name'];

		$visibleidextra = "visible_extra_" . $this->element['name'];
		$hiddedidextra = "hidded_extra_" . $this->element['name'];

		$isuntranslatable = (int) $this->element['isuntranslatable'];
		$isblocked        = (int) $this->element['isblocked'];
		$iskeytokeep      = (int) $this->element['iskeytokeep'];
		$iskeytodelete    = (int) $this->element['iskeytodelete'];
		$istranslated     = (int) $this->element['istranslated'];
		$isuntranslated   = (int) $this->element['isuntranslated'];
		$isunchanged      = (int) $this->element['isunchanged'];

		if ($istranslation == '1')
		{
		$untranslatable_mode = $this->element['untranslatable_mode'];
		$blocked_mode = $this->element['blocked_mode'];
		$keep_mode = $this->element['keep_mode'];

			if ($status == 'keytodelete' || $status == 'extra')
			{
				if ($iskeytokeep == '1' || $status == 'extra')
				{
					$checkedextra = ' checked="checked" ';
					$valueextra   = 'true';

					if ($keep_mode == '1')
					{
					$disabledextra = '';
					}
					else
					{
					$disabledextra = ' disabled="disabled" ';
					}
				}
				else
				{
					$checkedextra = '';
					$valueextra   = 'false';

					if ($keep_mode == '1')
					{
					$disabledextra = '';
					}
					else
					{
					$disabledextra = ' disabled="disabled" ';
					}
				}

			$onclickextra = "javascript:document.id(
							'" . $hiddedidextra . "'
							)
							.set(
							'value', document.getElementById('" . $visibleidextra . "' ).checked
							);
							if (document.id('" . $hiddedidextra . "').get('value')=='true')
							{
							document.id('"
							. $this->id . "').set('class','width-45 extra');
							}
							else if (document.id('" . $hiddedidextra . "').get('value')=='false')
							{
								document.id('" . $this->id . "').set('class','width-45 keytodelete');
							}";

			$checkboxextra = '<div><b>Key to keep</b><input style="max-width:5%; min-width:5%;" 
					id="' . $visibleidextra . '" type="checkbox" ' . $disabledextra . '
					name="jform[extracheckbox][]" 
					value="' . $this->element['name'] . '" 
					title="Extra" onclick="' . $onclickextra . '" ' . $checkedextra . '></input></div>
					<div><input id="' . $hiddedidextra . '" 
					type="hidden" name="jform[extras][' . $this->element['name'] . ']" 
					value="' . $valueextra . '" ></input></div>';

			return '<div><label id="' . $this->id . '-lbl" for="
						' . $this->id . '">
						' . $this->element['label'] . '<br />
						' . $checkboxextra . '</div></label>';
			}
			else
			{
				if ($this->value == $this->element['description'])
				{
					$isclickableuntranslatable = '1';
				}
				else
				{
					$isclickableuntranslatable = '0';
				}

				if ($isuntranslatable == '1' && $isclickableuntranslatable == '1')
				{
					if ($untranslatable_mode == '1')
					{
						$checkeduntranslatable = ' checked="checked" ';
						$valueuntranslatable   = 'true';
						$disableduntranslatable = '';
					}
					else
					{
						$checkeduntranslatable = ' checked="checked" ';
						$valueuntranslatable   = 'true';
						$disableduntranslatable = ' disabled="disabled" ';
					}

					$checkedblocked = '';
					$valueblocked   = 'false';
					$disabledblocked = ' disabled="disabled" ';
				}
				elseif ($isuntranslatable == '1' && $isclickableuntranslatable == '0')
				{
					$status = 'translated';
					$checkeduntranslatable = '';
					$valueuntranslatable   = 'false';
					$disableduntranslatable = ' disabled="disabled" ';

					if ($blocked_mode == '1')
					{
						$checkedblocked = '';
						$valueblocked   = 'false';
						$disabledblocked = '';
					}
					else
					{
						$checkedblocked = '';
						$valueblocked   = 'false';
						$disabledblocked = ' disabled="disabled" ';
					}
				}
				elseif ($isuntranslatable == '0' && $isclickableuntranslatable == '1')
				{
					if ($isblocked == '1')
					{
						$checkedblocked = ' checked="checked" ';
						$valueblocked   = 'true';

							if ($blocked_mode == '1')
							{
								$disabledblocked = '';
							}
							else
							{
								$disabledblocked = ' disabled="disabled" ';
							}

						$checkeduntranslatable = '';
						$valueuntranslatable   = 'false';
						$disableduntranslatable = ' disabled="disabled" ';
					}
					else
					{
						if ($blocked_mode == '1')
						{
							$checkedblocked = '';
							$valueblocked   = 'false';
							$disabledblocked = '';
						}
						else
						{
							$checkedblocked = '';
							$valueblocked   = 'false';
							$disabledblocked = ' disabled="disabled" ';
						}

						if ($untranslatable_mode == '1')
						{
							$checkeduntranslatable = '';
							$valueuntranslatable   = 'false';
							$disableduntranslatable = '';
						}
						else
						{
							$checkeduntranslatable = '';
							$valueuntranslatable   = 'false';
							$disableduntranslatable = ' disabled="disabled" ';
						}
					}
				}
				elseif ($isuntranslatable == '0' && $isclickableuntranslatable == '0')
				{
					$checkeduntranslatable = '';
					$valueuntranslatable   = 'false';
					$disableduntranslatable = ' disabled="disabled" ';

						if ($isblocked == '1')
						{
							if ($blocked_mode == '1')
							{
								$checkedblocked = ' checked="checked" ';
								$valueblocked   = 'true';
								$disabledblocked = '';
							}
							else
							{
								$checkedblocked = ' checked="checked" ';
								$valueblocked   = 'true';
								$disabledblocked = ' disabled="disabled" ';
							}
						}
						else
						{
							$checkedblocked = '';
							$valueblocked   = 'false';

							if ($blocked_mode == '1')
							{
								$disabledblocked = '';
							}
							else
							{
								$disabledblocked = ' disabled="disabled" ';
							}
						}
				}

			$onclickuntranslatable = "javascript:document.id(
							'" . $hiddediduntranslatable . "'
							)
							.set(
							'value', document.getElementById('" . $visibleiduntranslatable . "' ).checked
							);
							if (document.id('" . $hiddediduntranslatable . "').get('value')=='true')
							{
							document.id('"
							. $this->id . "').set('class','width-45 untranslatable');
							document.getElementById('"
							. $visibleidblocked . "').setAttribute('disabled', 'disabled');
							}
							else if (document.id('" . $hiddediduntranslatable . "').get('value')=='false')
							{
							document.getElementById('"
							. $visibleidblocked . "').removeAttribute('disabled');

								if (document.id('" . $this->id . "').get('value')=='"
								. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'))
								. "')
								{
								document.id('" . $this->id . "').set('class','width-45 unchanged');
								}
								else
								{
								document.id('" . $this->id . "').set('class','width-45 translated');
								}
							}";

			$onclickblocked = "javascript:document.id(
							'" . $hiddedidblocked . "'
							)
							.set(
							'value', document.getElementById('" . $visibleidblocked . "' ).checked
							);
							if (document.id('" . $hiddedidblocked . "').get('value')=='true')
							{
							document.id('"
							. $this->id . "').set('class','width-45 blocked');
							document.getElementById('"
							. $visibleiduntranslatable . "').setAttribute('disabled', 'disabled');
							document.id('" . $this->id . "').setAttribute('disabled', 'disabled');
							}
							else if (document.id('" . $hiddedidblocked . "').get('value')=='false')
							{
							document.getElementById('" . $this->id . "').removeAttribute('disabled');

								if (document.id('" . $this->id . "').get('value')=='"
								. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'))
								. "')
								{
								document.getElementById('"
								. $visibleiduntranslatable . "').removeAttribute('disabled');
								document.id('" . $this->id . "').set('class','width-45 unchanged');
								}
								else
								{
								document.id('" . $this->id . "').set('class','width-45 translated');
								}
							}";

			$checkboxuntranslatable = '<b>Untranslatable</b><input style="max-width:5%; min-width:5%;" id="
						' . $visibleiduntranslatable . '
						" type="checkbox" ' . $disableduntranslatable . ' name=
						"jform[untranslatablecheckbox][]" value="
						' . $this->element['name'] . '" title="Untranslatable" onclick="
						' . $onclickuntranslatable . '" ' . $checkeduntranslatable . '>
						</input><input id="
						' . $hiddediduntranslatable . '" type="hidden" name=
						"jform[untranslatables][' . $this->element['name'] . ']" value="
						' . $valueuntranslatable . '" ></input>';

			$checkboxblocked = '<b>Protected</b><input style="max-width:5%; min-width:5%;" id="
					' . $visibleidblocked . '" type=
					"checkbox" ' . $disabledblocked . ' name=
					"jform[blockedcheckbox][]" value=
					"' . $this->element['name'] . '" title=
					"Blocked" onclick="' . $onclickblocked . '" ' . $checkedblocked . '></input>
					<input id="' . $hiddedidblocked . '" type=
					"hidden" name="
					jform[blockeds][' . $this->element['name'] . ']" value=
					"' . $valueblocked . '" ></input>';

			return '<div><label id="' . $this->id . '-lbl" for=
					"' . $this->id . '">' . $this->element['label'] . '
					<br />' . $checkboxuntranslatable . '
					<br />' . $checkboxblocked . '</label></div>';
			}
		}
		else
		{
			return '<label id="' . $this->id . '-lbl" for="' . $this->id . '">'
						. $this->element['label']
					. '</label>';
		}
	}

	/**
	 * Method to get the field input.
	 *
	 * @return  string    The field input.
	 */
	protected function getInput()
	{
		// Set the class for the label.
		$class = !empty($this->descText) ? 'key-label hasTooltip fltrt' : 'key-label fltrt';

		$istranslation = (int) $this->element['istranslation'];
		$status = (string) $this->element['status'];

		$visibleiduntranslatable = "visible_untranslatable_" . $this->element['name'];
		$hiddediduntranslatable = "hidded_untranslatable_" . $this->element['name'];

		$visibleidblocked = "visible_blocked_" . $this->element['name'];
		$hiddedidblocked = "hidded_blocked_" . $this->element['name'];

		$visibleidextra = "visible_extra_" . $this->element['name'];
		$hiddedidextra = "hidded_extra_" . $this->element['name'];

		$isuntranslatable = (int) $this->element['isuntranslatable'];
		$isblocked        = (int) $this->element['isblocked'];
		$iskeytokeep      = (int) $this->element['iskeytokeep'];
		$iskeytodelete    = (int) $this->element['iskeytodelete'];
		$istranslated     = (int) $this->element['istranslated'];
		$isuntranslated   = (int) $this->element['isuntranslated'];
		$isunchanged      = (int) $this->element['isunchanged'];

		if ($istranslation == '1')
		{
		$params = JComponentHelper::getParams('com_localise');
		$user = JFactory::getUser();
		$untranslatable_mode = $this->element['untranslatable_mode'];
		$blocked_mode = $this->element['blocked_mode'];
		$keep_mode = $this->element['keep_mode'];

			// If a description is specified, use it to build a tooltip.
			if (!empty($this->descText))
			{
				$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '" title="'
						. htmlspecialchars(htmlspecialchars('::' . str_replace("\n", "\\n", $this->descText), ENT_QUOTES, 'UTF-8')) . '">';
			}
			else
			{
				$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '">';
			}

			JText::script('COM_LOCALISE_LABEL_TRANSLATION_GOOGLE_ERROR');
			$label .= $this->element['label'] . '<br />' . $this->element['description'];
			$label .= '</label>';
			$onclick = '';
			$button  = '';

			$onclick2 = '';
			$button2  = '';

			if ($status == 'keytodelete' || $status == 'extra')
			{
				$onclick = '';
				$button  = '<span style="width:5%;">'
						. JHtml::_('image', 'com_localise/icon-16-arrow-gray.png', '', array('class' => 'pointer'), true) . '</span>';

				$onclick2 = '';
				$button2  = '<span style="width:5%;">'
						. JHtml::_('image', 'com_localise/icon-16-bing-gray.png', '', array('class' => 'pointer'), true) . '</span>';
			}
			else
			{
				$onclick = "javascript:
							if (document.id('" . $hiddedidblocked . "').get('value')=='false')
							{
							document.id(
							'" . $this->id . "'
							)
							.set(
							'value','"
							. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . "'
								);
								if (document.id('" . $hiddediduntranslatable . "').get('value')=='true')
								{
								document.id('" . $this->id . "').set('class','width-45 untranslatable');
								}
								else
								{
								document.id('" . $this->id . "').set('class','width-45 untranslated');
								}
								document.getElementById('" . $visibleiduntranslatable . "').removeAttribute('disabled');
							}
							else
							{
							window.alert('Protected');
							}";

				$button  = '<i class="icon-reset hasTooltip return pointer" title="' . JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_INSERT')
							. '" onclick="' . $onclick . '"></i>';
				$token    = JSession::getFormToken();
				$onclick2 = "javascript:
							if (document.id('" . $hiddedidblocked . "').get('value')=='false')
							{
							AzureTranslator(this, [], 0, '$token');
							}
							else
							{
							window.alert('Protected');
							}";
				$button2  = '<input type="hidden" id="' . $this->id . 'text" value=\''
							. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . '\' />';
				$button2 .= '<i class="icon-translate-bing hasTooltip translate pointer" title="'
							. JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_AZURE') . '" onclick="' . $onclick2 . '" rel="' . $this->id . '"></i>';
			}

			if ($status == 'keytodelete' || $status == 'extra')
			{
				$input = '<textarea name="' . $this->name
				. '" id="' . $this->id . '" class="width-45 ' . $status . ' ">'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
			}
			else
			{
				if ($untranslatable_mode == '1')
				{
					$um_conditions = "document.getElementById('" . $visibleiduntranslatable . "').removeAttribute('disabled');
							this.set('class','width-45 unchanged');";
				}
				else
				{
					$um_conditions = "document.id('" . $hiddediduntranslatable . "').set('value', 'false');
						document.getElementById('" . $visibleiduntranslatable . "').setAttribute('disabled', 'disabled');
						this.set('class','width-45 unchanged');";
				}

				if ($blocked_mode == '1')
				{
					$bm_conditions = "document.getElementById('" . $visibleidblocked . "').removeAttribute('disabled');";
				}
				else
				{
					$bm_conditions = "";
				}

			$onkeyup = "javascript:";
			$onkeyup .= "if (this.get('value')=='')
					{
					this.set('class','width-45 untranslated');
					}
					else if (this.get('value')=='"
					. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'))
					. "')
					{" . $um_conditions . "}
					else
					{
					this.set('class','width-45 translated');
					document.getElementById('" . $visibleiduntranslatable . "').checked = false;
					document.id('" . $hiddediduntranslatable . "').set('value', 'false');
					document.getElementById('" . $visibleiduntranslatable . "').setAttribute('disabled', 'disabled');"
					. $bm_conditions . "
					}";

			$onfocus = "javascript:";
			$onfocus .= "this.select();
					if (document.id('" . $hiddedidblocked . "').get('value')=='true')
					{
					document.getElementById('" . $this->id . "').setAttribute('disabled', 'disabled');
					}";
			$input = '<textarea name="' . $this->name . '" id="' . $this->id . '" onfocus="' . $onfocus . '"
						class="width-45 ' . $status . '" onkeyup="'
						. $onkeyup . '">' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
			}
		}
		else
		{
			// Set the class for the label.
			$class = !empty($this->descText) ? 'key-label hasTooltip fltrt' : 'key-label fltrt';

			// If a description is specified, use it to build a tooltip.
			if (!empty($this->descText))
			{
				$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '" title="'
						. htmlspecialchars(htmlspecialchars('::' . str_replace("\n", "\\n", $this->descText), ENT_QUOTES, 'UTF-8')) . '">';
			}
			else
			{
				$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '">';
			}

			JText::script('COM_LOCALISE_LABEL_TRANSLATION_GOOGLE_ERROR');
			$label .= $this->element['label'] . 'br />' . $this->element['description'];
			$label .= '</label>';
			$status = (string) $this->element['status'];

			if ($status == 'extra')
			{
				$onclick = '';
				$button  = '<span style="width:5%;">'
							. JHtml::_('image', 'com_localise/icon-16-arrow-gray.png', '', array('class' => 'pointer'), true) . '</span>';

				$onclick2 = '';
				$button2  = '<span style="width:5%;">'
							. JHtml::_('image', 'com_localise/icon-16-bing-gray.png', '', array('class' => 'pointer'), true) . '</span>';
			}
			else
			{
				$onclick = "javascript:document.id(
							'" . $this->id . "'
							)
							.set(
							'value','" . addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . "'
							);
							if (document.id('" . $this->id . "').get('value')=='') {document.id('" . $this->id . "').set('class','width-45 untranslated');}
							else {document.id('" . $this->id . "').set('class','width-45 " . ($status == 'untranslated' ? 'unchanged' : $status) . "');}";
				$button  = '<i class="icon-reset hasTooltip return pointer" title="' . JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_INSERT')
							. '" onclick="' . $onclick . '"></i>';
				/* $onclick2 = "javascript:if (typeof(google) !== 'undefined') {
				var translation='" . addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . "';
					translation=translation.replace('%s','___s');translation=translation.replace('%d','___d');
					translation=translation.replace(/%([0-9]+)\\\$s/,'___\$1');google.language.translate(translation,
					Localise.language_src, Localise.language_dest, function(result) {if (result.translation) {
				  translation = result.translation;
				  translation = translation.replace('___s','%s');
				  translation = translation.replace('___d','%d');
				  translation = translation.replace(/___([0-9]+)/,'%$1\$s');
				  document.id('" . $this->id . "').set('value',translation);
				  if (document.id('" . $this->id . "').get('value')=='" . addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'))
					. "') document.id('" . $this->id . "').set('class','width-45 unchanged');
					else document.id('" . $this->id . "').set('class','width-45 translated');}
					else alert(Joomla.JText._('COM_LOCALISE_LABEL_TRANSLATION_GOOGLE_ERROR'));});}
					else alert(Joomla.JText._('COM_LOCALISE_LABEL_TRANSLATION_GOOGLE_ERROR'));";
				  $button2 = '<span style="width:5%;">' . JHtml::_('image', 'com_localise/icon-16-google.png', '',
					array('title' => JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_GOOGLE'), 'class' => 'hasTooltip pointer',
					'onclick' => $onclick2), true) . '</span>';
				  */
				$token    = JSession::getFormToken();
				$onclick2 = "javascript:AzureTranslator(this, [], 0, '$token');";
				$button2  = '<input type="hidden" id="' . $this->id . 'text" value=\''
							. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . '\' />';
				$button2 .= '<i class="icon-translate-bing hasTooltip translate pointer" title="'
							. JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_AZURE') . '" onclick="' . $onclick2 . '" rel="' . $this->id . '"></i>';
			}

			$onkeyup = "javascript:";
			$onkeyup .= "if (this.get('value')=='') {this.set('class','width-45 untranslated');}
						else {if (this.get('value')=='" . addslashes(htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'))
						. "') this.set('class','width-45 " . $status . "');
						" . ($status == 'extra' ? "else this.set('class','width-45 extra');}" : "else this.set('class','width-45 translated');}");
			$input = '<textarea name="' . $this->name . '" id=
				"' . $this->id . '" onfocus=
				"this.select()" class=
				"width-45 ' . ($this->value == '' ? 'untranslated' :
				($this->value == $this->element['description'] ? $status : 'translated')) . '" onkeyup=
				"' . $onkeyup . '">
				' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		}

		return $button . $button2 . $input;
	}
}
