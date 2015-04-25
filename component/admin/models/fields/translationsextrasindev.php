<?php
/**
 * @package     Com_Localise
 * @subpackage  models
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('groupedlist');

/**
 * Form Field Translationsindev class.
 *
 * @package     Extensions.Components
 * @subpackage  Localise
 *
 * @since       1.0
 */
class JFormFieldTranslationsextrasindev extends JFormFieldGroupedList
{
	/**
	 * The field type.
	 *
	 * @var    string
	 */
	protected $type = 'Translationsextrasindev';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of JHtml options.
	 */
	protected function getGroups()
	{
		// Remove '.ini' from values
		if (is_array($this->value))
		{
			foreach ($this->value as $key => $val)
			{
				$this->value[$key] = substr($val, 0, -4);
			}
		}

		$groups  = array('Site' => array(), 'Administrator' => array());
		$allowdev = $this->form->getValue('allowdev');
		$translations_list = $this->form->getValue('translations');
		$translationsindev_list = $this->form->getValue('translationsindev');
		$files_to_add = array();

			if (!empty($translations_list) && !empty($translationsindev_list) && $allowdev == 1)
			{
				$files_to_add = array_diff($translations_list, $translationsindev_list);
			}

			if (!empty($files_to_add))
			{
				foreach ($files_to_add as $file_to_add)
				{
					if (preg_match('/^site_(.*)$/', $file_to_add, $matches))
					{
						$key      = substr($matches[1], 0, strlen($matches[1]) - 4);
						$value    = $key;

						if ($key == 'joomla')
						{
							$value    = JText::_('COM_LOCALISE_TEXT_TRANSLATIONS_JOOMLA');
						}

						$groups['Site'][$key] = JHtml::_('select.option', 'site_' . $key, $value, 'value', 'text', false);
					}

					if (preg_match('/^administrator_(.*)$/', $file_to_add, $matches))
					{
						$key      = substr($matches[1], 0, strlen($matches[1]) - 4);
						$value    = $key;

						if ($key == 'joomla')
						{
							$value    = JText::_('COM_LOCALISE_TEXT_TRANSLATIONS_JOOMLA');
						}

						$groups['Administrator'][$key] = JHtml::_('select.option', 'administrator_' . $key, $value, 'value', 'text', false);
					}
				}
			}

		foreach ($groups as $client => $extensions)
		{
			if (count($groups[$client]) == 0)
			{
				$groups[$client][] = JHtml::_('select.option', '',  JText::_('COM_LOCALISE_NOTRANSLATION'), 'value', 'text', true);
			}
			else
			{
				JArrayHelper::sortObjects($groups[$client], 'text');
			}
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
