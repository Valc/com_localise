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
		return '<label id="' . $this->id . '-lbl" for="' . $this->id . '">'
					. $this->element['label']
				. '</label>';
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

		if ($istranslation == '1')
		{

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
				$input  = '';
				$input .= '<textarea name="' . $this->name;
				$input .= '" id="' . $this->id . '" class="width-45 ' . $status . ' ">';
				$input .= htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
			}
			else
			{
				$token    = JSession::getFormToken();

				if ($status == 'blocked')
				{
					$onclick  = "javascript:window.alert('Protected');";
					$onclick2 = "javascript:window.alert('Protected');";

					$button   = '';
					$button  .= '<i class="icon-reset hasTooltip return pointer" title="';
					$button  .= JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_INSERT');
					$button  .= '" onclick="' . $onclick . '"></i>';

					$button2   = '';
					$button2  .= '<i class="icon-translate-bing hasTooltip translate pointer" title="';
					$button2  .= JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_AZURE');
					$button2  .= '" onclick="' . $onclick2 . '" rel="' . $this->id . '"></i>';

					$onfocus = "javascript:this.select();";

					$input  = '';
					$input .= '<textarea name="' . $this->name . '" id="' . $this->id . '" onfocus="' . $onfocus;
					$input .= '" class="width-45 ' . $status . '"  readonly="readonly">';
					$input .= htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
				}
				else
				{
					$onclick  = "";
					$onclick .= "javascript:document.id('" . $this->id . "').set('value','";
					$onclick .= addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'));
					$onclick .= "');";

					if ($status == 'untranslatable')
					{
						$onclick .= "document.id('" . $this->id . "').set('class','width-45 untranslatable');";
						$onclick2 = "javascript:window.alert('Untranslatable');";
					}
					else
					{
						$onclick .= "document.id('" . $this->id . "').set('class','width-45 untranslated');";
						$onclick2 = "javascript:AzureTranslator(this, [], 0, '$token');";
					}

				$button   = '';
				$button  .= '<i class="icon-reset hasTooltip return pointer" title="';
				$button  .= JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_INSERT');
				$button  .= '" onclick="' . $onclick . '"></i>';

				$button2   = '';
				$button2  .= '<input type="hidden" id="' . $this->id . 'text" value=\'';
				$button2  .= addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8')) . '\' />';
				$button2  .= '<i class="icon-translate-bing hasTooltip translate pointer" title="';
				$button2  .= JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_AZURE');
				$button2  .= '" onclick="' . $onclick2 . '" rel="' . $this->id . '"></i>';

				$onkeyup = "javascript:";
				$onkeyup .= "if (this.get('value')=='')
						{
						this.set('class','width-45 untranslated');
						}
						else if (this.get('value')=='"
						. addslashes(htmlspecialchars($this->element['description'], ENT_COMPAT, 'UTF-8'))
						. "')
						{
						this.set('class','width-45 untranslated');
						}
						else
						{
						this.set('class','width-45 translated');
						}";

				$onfocus = "javascript:this.select();";

				$input  = '';
				$input .= '<textarea name="' . $this->name . '" id="' . $this->id . '" onfocus="' . $onfocus;
				$input .= '" class="width-45 ' . $status . '" onkeyup="';
				$input .= $onkeyup . '">' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
				}

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
							else {document.id('" . $this->id . "').set('class','width-45 " . $status . "');}";
				$button  = '<i class="icon-reset hasTooltip return pointer" title="' . JText::_('COM_LOCALISE_TOOLTIP_TRANSLATION_INSERT')
							. '" onclick="' . $onclick . '"></i>';

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
			$input  = '';
			$input .= '<textarea name="' . $this->name . '" id="';
			$input .= $this->id . '" onfocus="this.select()" class="width-45 ';
			$input .= ($this->value == '' ? 'untranslated' : ($this->value == $this->element['description'] ? $status : 'translated'));
			$input .= '" onkeyup="' . $onkeyup . '">' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
			$input .= '</textarea>';
		}

		return $button . $button2 . $input;
	}
}
