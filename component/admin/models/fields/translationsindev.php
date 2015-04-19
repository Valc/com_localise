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
class JFormFieldTranslationsindev extends JFormFieldGroupedList
{
	/**
	 * The field type.
	 *
	 * @var    string
	 */
	protected $type = 'Translationsindev';

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

		$package = (string) $this->element['package'];
		$groups  = array('Site' => array(), 'Administrator' => array(), 'Installation' => array());
		$githubpaths  = array();

		$allowdev = $this->form->getValue('allowdev');

		foreach (array('Site', 'Administrator', 'Installation') as $client)
		{
		$gh_data = array();
		$lowerclient = strtolower($client);
		$clientpath = 'github' . $lowerclient . 'languagepath';
		$githublanguagepath[$lowerclient] = trim($this->form->getValue($clientpath), " /\t\n\r");

			if ($allowdev == '1' && !empty($githublanguagepath[$lowerclient]))
			{
			$githubuser = $this->form->getValue('githubuser');
			$githubproject = $this->form->getValue('githubproject');
			$devtrunk = $this->form->getValue('devtrunk');
			$githubtoken = $this->form->getValue('githubtoken');
			$githubclientpath =	$githubuser
						. '/'
						. $githubproject
						. '/'
						. $devtrunk
						. '/'
						. $githublanguagepath[$lowerclient];

			$clientpathparts = explode("/", $githubclientpath);

			$language_tag = end($clientpathparts);

			$is_tag    = LocaliseHelper::isTag($language_tag);
			$taskrootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/task_in_dev/github');
			$rootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/in_dev/github');
			$basepath  = JFolder::makeSafe(JPATH_ROOT . "/media/com_localise/in_dev/github/$githubuser/$githubproject/$devtrunk");

			$root_folder_exists = JFolder::exists($rootpath);

			$gh_data['github_client']     = $lowerclient;
			$gh_data['github_user']       = $githubuser;
			$gh_data['github_project']    = $githubproject;
			$gh_data['dev_trunk']         = $devtrunk;
			$gh_data['task_root_path']    = $taskrootpath;
			$gh_data['root_path']         = $rootpath;
			$gh_data['base_path']         = $basepath;
			$gh_data['client_path']       = $githublanguagepath[$lowerclient];
			$gh_data['client_path_parts'] = $clientpathparts;
			$gh_data['github_tag']        = $language_tag;
			$gh_data['github_token']      = $githubtoken;
			$in_dev_files = array();

				if ($root_folder_exists == '1' && $is_tag == 'true')
				{
				$full_client_path = JFolder::makeSafe($rootpath . '/' . $githubclientpath);
				$client_folder_exists = JFolder::exists($full_client_path);
				$gh_data['dev_name'] = implode('_', $clientpathparts);
				$gh_data['sha_path'] = JFolder::makeSafe(JPATH_COMPONENT_ADMINISTRATOR . '/packages/in_dev/' . $gh_data['dev_name'] . '.txt');
				$gh_data['full_client_path'] = $full_client_path;

					if ($client_folder_exists == '1')
					{
					$gh_data['sha_files_list'] = LocaliseHelper::getShafileslist($gh_data);
					$gh_data['stored_dev_ini_files'] = LocaliseHelper::getInifilesindevlist($gh_data);
					$gh_data['stored_dev_files'] = LocaliseHelper::getFilesindevlist($gh_data);
					$githubfiles  = LocaliseHelper::getGithubfiles($gh_data);

						if ($githubfiles == 'true')
						{
						$in_dev_files = LocaliseHelper::getInifilesindevlist($gh_data);
						}
					}
					else
					{
					$have_folders = 1;
					$gh_data['path_parts'] = '';
					$gh_data['sha_files_list'] = array();
					$gh_data['stored_dev_files'] = array();

						foreach ($clientpathparts as $clientpathpart)
						{
						$gh_data['path_parts'] .= $clientpathpart . '/';
						$gh_data['last_part']   = $clientpathpart;

						$createfolders = LocaliseHelper::createFolder($gh_data, $index = 'true');

							if ($createfolders == 0)
							{
							$have_folders = 0;
							break;
							}
						}

						if ($have_folders == 1)
						{
						$githubfiles  = LocaliseHelper::getGithubfiles($gh_data);

							if ($githubfiles == 'true')
							{
							$in_dev_files = LocaliseHelper::getInifilesindevlist($gh_data);
							}
						}
					}

					if (!empty($in_dev_files))
					{
						foreach ($in_dev_files as $file)
						{
							$basename = substr($file, strlen($language_tag) + 1);

							if ($basename == 'ini')
							{
								$key      = 'joomla';
								$value    = JText::_('COM_LOCALISE_TEXT_TRANSLATIONS_JOOMLA');
							}
							else
							{
								$key      = substr($basename, 0, strlen($basename) - 4);
								$value    = $key;
							}

							$groups[$client][$key] = JHtml::_('select.option', strtolower($client) . '_' . $key, $value, 'value', 'text', false);
						}
					}
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
