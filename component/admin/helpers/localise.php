<?php
/**
 * @package     Com_Localise
 * @subpackage  helper
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.stream');
jimport('joomla.filesystem.path');

/**
 * Localise Helper class
 *
 * @package     Extensions.Components
 * @subpackage  Localise
 * @since       4.0
 */
abstract class LocaliseHelper
{
	/**
	 * Array containing the origin information
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected static $origins = array('site' => null, 'administrator' => null, 'installation' => null);

	/**
	 * Array containing the package information
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected static $packages = array();

	/**
	 * Prepares the component submenu
	 *
	 * @param   string  $vName  Name of the active view
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_LOCALISE_SUBMENU_LANGUAGES'),
			'index.php?option=com_localise&view=languages',
			$vName == 'languages'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_LOCALISE_SUBMENU_TRANSLATIONS'),
			'index.php?option=com_localise&view=translations',
			$vName == 'translations'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_LOCALISE_SUBMENU_PACKAGES'),
			'index.php?option=com_localise&view=packages',
			$vName == 'packages'
		);
	}

	/**
	 * Determines if a given path is writable in the current environment
	 *
	 * @param   string  $path  Path to check
	 *
	 * @return  boolean  True if writable
	 *
	 * @since   4.0
	 */
	public static function isWritable($path)
	{
		if (JFactory::getConfig()->get('config.ftp_enable'))
		{
			return true;
		}
		else
		{
			while (!file_exists($path))
			{
				$path = dirname($path);
			}

			return is_writable($path) || JPath::isOwner($path) || JPath::canChmod($path);
		}
	}

	/**
	 * Check if the installation path exists
	 *
	 * @return  boolean  True if the installation path exists
	 *
	 * @since   4.0
	 */
	public static function hasInstallation()
	{
		return is_dir(LOCALISEPATH_INSTALLATION);
	}

	/**
	 * Retrieve the packages array
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public static function getPackages()
	{
		if (empty(static::$packages))
		{
			static::scanPackages();
		}

		return static::$packages;
	}

	/**
	 * Scans the filesystem for language files in each package
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected static function scanPackages()
	{
		$model         = JModelLegacy::getInstance('Packages', 'LocaliseModel', array('ignore_request' => true));
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$packages       = $model->getItems();

		foreach ($packages as $package)
		{
			static::$packages[$package->name] = $package;

			foreach ($package->administrator as $file)
			{
				static::$origins['administrator'][$file] = $package->name;
			}

			foreach ($package->site as $file)
			{
				static::$origins['site'][$file] = $package->name;
			}
		}
	}

	/**
	 * Retrieves the origin information
	 *
	 * @param   string  $filename  The filename to check
	 * @param   string  $client    The client to check
	 *
	 * @return  string  Origin data
	 *
	 * @since   4.0
	 */
	public static function getOrigin($filename, $client)
	{
		if ($filename == 'override')
		{
			return '_override';
		}

		// If the $origins array doesn't contain data, fill it
		if (empty(static::$origins['site']))
		{
			static::scanPackages();
		}

		if (isset(static::$origins[$client][$filename]))
		{
			return static::$origins[$client][$filename];
		}
		else
		{
			return '_thirdparty';
		}
	}

	/**
	 * Scans the filesystem
	 *
	 * @param   string  $client  The client to scan
	 * @param   string  $type    The extension type to scan
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public static function getScans($client = '', $type = '')
	{
		$params   = JComponentHelper::getParams('com_localise');
		$suffixes = explode(',', $params->get('suffixes', '.sys'));

		$filter_type   = $type ? $type : '.';
		$filter_client = $client ? $client : '.';
		$scans         = array();

		// Scan installation folders
		if (preg_match("/$filter_client/", 'installation'))
		{
			// TODO ;-)
		}

		// Scan administrator folders
		if (preg_match("/$filter_client/", 'administrator'))
		{
			// Scan administrator components folders
			if (preg_match("/$filter_type/", 'component'))
			{
				$scans[] = array(
					'prefix' => '',
					'suffix' => '',
					'type'   => 'component',
					'client' => 'administrator',
					'path'   => LOCALISEPATH_ADMINISTRATOR . '/components/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => '',
						'suffix' => $suffix,
						'type'   => 'component',
						'client' => 'administrator',
						'path'   => LOCALISEPATH_ADMINISTRATOR . '/components/',
						'folder' => ''
					);
				}
			}

			// Scan administrator modules folders
			if (preg_match("/$filter_type/", 'module'))
			{
				$scans[] = array(
					'prefix' => '',
					'suffix' => '',
					'type'   => 'module',
					'client' => 'administrator',
					'path'   => LOCALISEPATH_ADMINISTRATOR . '/modules/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => '',
						'suffix' => $suffix,
						'type'   => 'module',
						'client' => 'administrator',
						'path'   => LOCALISEPATH_ADMINISTRATOR . '/modules/',
						'folder' => ''
					);
				}
			}

			// Scan administrator templates folders
			if (preg_match("/$filter_type/", 'template'))
			{
				$scans[] = array(
					'prefix' => 'tpl_',
					'suffix' => '',
					'type'   => 'template',
					'client' => 'administrator',
					'path'   => LOCALISEPATH_ADMINISTRATOR . '/templates/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => 'tpl_',
						'suffix' => $suffix,
						'type'   => 'template',
						'client' => 'administrator',
						'path'   => LOCALISEPATH_ADMINISTRATOR . '/templates/',
						'folder' => ''
					);
				}
			}

			// Scan plugins folders
			if (preg_match("/$filter_type/", 'plugin'))
			{
				$plugin_types = JFolder::folders(JPATH_PLUGINS);

				foreach ($plugin_types as $plugin_type)
				{
					// Scan administrator language folders as this is where plugin languages are installed
					$scans[] = array(
						'prefix' => 'plg_' . $plugin_type . '_',
						'suffix' => '',
						'type'   => 'plugin',
						'client' => 'administrator',
						'path'   => JPATH_PLUGINS . "/$plugin_type/",
						'folder' => ''
					);

					foreach ($suffixes as $suffix)
					{
						$scans[] = array(
							'prefix' => 'plg_' . $plugin_type . '_',
							'suffix' => $suffix,
							'type'   => 'plugin',
							'client' => 'administrator',
							'path'   => JPATH_PLUGINS . "/$plugin_type/",
							'folder' => ''
						);
					}
				}
			}
		}

		// Scan site folders
		if (preg_match("/$filter_client/", 'site'))
		{
			// Scan site components folders
			if (preg_match("/$filter_type/", 'component'))
			{
				$scans[] = array(
					'prefix' => '',
					'suffix' => '',
					'type'   => 'component',
					'client' => 'site',
					'path'   => LOCALISEPATH_SITE . '/components/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => '',
						'suffix' => $suffix,
						'type'   => 'component',
						'client' => 'site',
						'path'   => LOCALISEPATH_SITE . '/components/',
						'folder' => ''
					);
				}
			}

			// Scan site modules folders
			if (preg_match("/$filter_type/", 'module'))
			{
				$scans[] = array(
					'prefix' => '',
					'suffix' => '',
					'type'   => 'module',
					'client' => 'site',
					'path'   => LOCALISEPATH_SITE . '/modules/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => '',
						'suffix' => $suffix,
						'type'   => 'module',
						'client' => 'site',
						'path'   => LOCALISEPATH_SITE . '/modules/',
						'folder' => ''
					);
				}
			}

			// Scan site templates folders
			if (preg_match("/$filter_type/", 'template'))
			{
				$scans[] = array(
					'prefix' => 'tpl_',
					'suffix' => '',
					'type'   => 'template',
					'client' => 'site',
					'path'   => LOCALISEPATH_SITE . '/templates/',
					'folder' => ''
				);

				foreach ($suffixes as $suffix)
				{
					$scans[] = array(
						'prefix' => 'tpl_',
						'suffix' => $suffix,
						'type'   => 'template',
						'client' => 'site',
						'path'   => LOCALISEPATH_SITE . '/templates/',
						'folder' => ''
					);
				}
			}
		}

		return $scans;
	}

	/**
	 * Get file ID in the database for the given file path
	 *
	 * @param   string  $path  Path to lookup
	 *
	 * @return  integer  File ID
	 *
	 * @since   4.0
	 */
	public static function getFileId($path)
	{
		static $fileIds = null;

		if (!isset($fileIds))
		{
			$db = JFactory::getDbo();

			$db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName(array('id', 'path')))
					->from($db->quoteName('#__localise'))
			);

			$fileIds = $db->loadObjectList('path');
		}

		if (is_file($path) || preg_match('/.ini$/', $path))
		{
			if (!array_key_exists($path, $fileIds))
			{
				JTable::addIncludePath(JPATH_COMPONENT . '/tables');

				/* @type  LocaliseTableLocalise  $table */
				$table       = JTable::getInstance('Localise', 'LocaliseTable');
				$table->path = $path;
				$table->store();

				$fileIds[$path] = new stdClass;
				$fileIds[$path]->id = $table->id;
			}

			return $fileIds[$path]->id;
		}
		else
		{
			$id = 0;
		}

		return $id;
	}

	/**
	 * Get file path in the database for the given file id
	 *
	 * @param   integer  $id  Id to lookup
	 *
	 * @return  string   File Path
	 *
	 * @since   4.0
	 */
	public static function getFilePath($id)
	{
		static $filePaths = null;

		if (!isset($filePaths))
		{
			$db = JFactory::getDbo();

			$db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName(array('id', 'path')))
					->from($db->quoteName('#__localise'))
			);

			$filePaths = $db->loadObjectList('id');
		}

		return array_key_exists("$id", $filePaths) ?
		$filePaths["$id"]->path : '';
	}

	/**
	 * Determine if a package at given path is core or not.
	 *
	 * @param   string  $path  Path to lookup
	 *
	 * @return  mixed  null if file is invalid | True if core else false.
	 *
	 * @since   4.0
	 */
	public static function isCorePackage($path)
	{
		if (is_file($path) || preg_match('/.ini$/', $path))
		{
			$xml = simplexml_load_file($path);

			return ((string) $xml->attributes()->core) == 'true';
		}
	}

	/**
	 * Find a translation file
	 *
	 * @param   string  $client    Client to lookup
	 * @param   string  $tag       Language tag to lookup
	 * @param   string  $filename  Filename to lookup
	 *
	 * @return  string  Path to the requested file
	 *
	 * @since   4.0
	 */
	public static function findTranslationPath($client, $tag, $filename)
	{
		$params = JComponentHelper::getParams('com_localise');
		$priority = $params->get('priority', '0') == '0' ? 'global' : 'local';
		$path = static::getTranslationPath($client, $tag, $filename, $priority);

		if (!is_file($path))
		{
			$priority = $params->get('priority', '0') == '0' ? 'local' : 'global';
			$path = static::getTranslationPath($client, $tag, $filename, $priority);
		}

		return $path;
	}

	/**
	 * Get a translation path
	 *
	 * @param   string  $client    Client to lookup
	 * @param   string  $tag       Language tag to lookup
	 * @param   string  $filename  Filename to lookup
	 * @param   string  $storage   Storage location to check
	 *
	 * @return  string  Path to the requested file
	 *
	 * @since   4.0
	 */
	public static function getTranslationPath($client, $tag, $filename, $storage)
	{
		if ($filename == 'override')
		{
			$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/language/overrides/$tag.override.ini";
		}
		elseif ($filename == 'joomla')
		{
			$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/language/$tag/$tag.ini";
		}
		elseif ($storage == 'global')
		{
			$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/language/$tag/$tag.$filename.ini";
		}
		else
		{
			$parts     = explode('.', $filename);
			$extension = $parts[0];

			switch (substr($extension, 0, 3))
			{
				case 'com':
					$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/components/$extension/language/$tag/$tag.$filename.ini";

					break;

				case 'mod':
					$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/modules/$extension/language/$tag/$tag.$filename.ini";

					break;

				case 'plg':
					$parts  = explode('_', $extension);
					$group  = $parts[1];
					$parts  = explode('.', $filename);
					$pluginname = $parts[0];
					$plugin = substr($pluginname, 5 + strlen($group));
					$path   = JPATH_PLUGINS . "/$group/$plugin/language/$tag/$tag.$filename.ini";

					break;

				case 'tpl':
					$template = substr($extension, 4);
					$path     = constant('LOCALISEPATH_' . strtoupper($client)) . "/templates/$template/language/$tag/$tag.$filename.ini";

					break;

				case 'lib':
					$path = constant('LOCALISEPATH_' . strtoupper($client)) . "/language/$tag/$tag.$filename.ini";

					if (!is_file($path))
					{
						$path = $client == 'administrator' ? LOCALISEPATH_SITE : LOCALISEPATH_ADMINISTRATOR . "/language/$tag/$tag.$filename.ini";
					}

					break;

				default   :
					$path = '';

					break;
			}
		}

		return $path;
	}

	/**
	 * Load a language file for translating the package name
	 *
	 * @param   string  $extension  The extension to load
	 * @param   string  $client     The client from where to load the file
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public static function loadLanguage($extension, $client)
	{
		$extension = strtolower($extension);
		$lang      = JFactory::getLanguage();
		$prefix    = substr($extension, 0, 3);

		switch ($prefix)
		{
			case 'com':
				$lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)), null, false, true)
					|| $lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)) . "/components/$extension/", null, false, true);

				break;

			case 'mod':
				$lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)), null, false, true)
					|| $lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)) . "/modules/$extension/", null, false, true);

				break;

			case 'plg':
				$lang->load($extension, LOCALISEPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load($extension, LOCALISEPATH_ADMINISTRATOR . "/components/$extension/", null, false, true);

				break;

			case 'tpl':
				$template = substr($extension, 4);
				$lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)), null, false, true)
					|| $lang->load($extension, constant('LOCALISEPATH_' . strtoupper($client)) . "/templates/$template/", null, false, true);

				break;

			case 'lib':
			case 'fil':
			case 'pkg':
				$lang->load($extension, JPATH_ROOT, null, false, true);

				break;
		}
	}

	/**
	 * Parses the sections of a language file
	 *
	 * @param   string  $filename  The filename to parse
	 *
	 * @return  array  Array containing the file data
	 *
	 * @since   4.0
	 */
	public static function parseSections($filename)
	{
		static $sections = array();

		if (!array_key_exists($filename, $sections))
		{
			if (file_exists($filename))
			{
				$error = '';

				if (!defined('_QQ_'))
				{
					define('_QQ_', '"');
				}

				ini_set('track_errors', '1');

				$contents = file_get_contents($filename);
				$contents = str_replace('_QQ_', '"\""', $contents);
				$strings  = @parse_ini_string($contents, true);

				if (!empty($php_errormsg))
				{
					$error = "Error parsing " . basename($filename) . ": $php_errormsg";
				}

				ini_restore('track_errors');

				if ($strings !== false)
				{
					$default = array();

					foreach ($strings as $key => $value)
					{
						if (is_string($value))
						{
							$default[$key] = $value;

							unset($strings[$key]);
						}
						else
						{
							break;
						}
					}

					if (!empty($default))
					{
						$strings = array_merge(array('Default' => $default), $strings);
					}

					$keys = array();

					foreach ($strings as $section => $value)
					{
						foreach ($value as $key => $string)
						{
							$keys[$key] = $strings[$section][$key];
						}
					}
				}
				else
				{
					$keys = false;
				}

				$sections[$filename] = array('sections' => $strings, 'keys' => $keys, 'error' => $error);
			}
			else
			{
				$sections[$filename] = array('sections' => array(), 'keys' => array(), 'error' => '');
			}
		}

		if (!empty($sections[$filename]['error']))
		{
			$model = JModelLegacy::getInstance('Translation', 'LocaliseModel');
			$model->setError($sections[$filename]['error']);
		}

		return $sections[$filename];
	}

	/**
	 * Create the required folders for develop
	 *
	 * @param   array   $gh_data  Array with the data
	 * @param   string  $index    If true, allow to create an index.html file
	 *
	 * @return  bolean
	 *
	 * @since   4.11
	 */
	public static function createFolder ($gh_data = array(), $index = 'true')
	{
		if (!empty ($gh_data) && isset($gh_data['path_parts']))
		{
		$path_parts = $gh_data['path_parts'];
		$full_path = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/in_dev/github/' . $gh_data['path_parts']);
		$task_full_path = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/task_in_dev/github/' . $gh_data['path_parts']);

			if (!JFolder::create($full_path))
			{
			}

			if ($gh_data['github_tag'] != $gh_data['last_part'])
			{
				if (!JFolder::create($task_full_path))
				{
				}
			}

			if (JFolder::exists($full_path))
			{
				if ($index == 'true')
				{
				$cretate_folder = self::createIndex($full_path);

					if ($gh_data['github_tag'] != $gh_data['last_part'])
					{
						if (JFolder::exists($task_full_path))
						{
							$cretate_task_folder = self::createIndex($task_full_path);
						}
					}
					else
					{
						$cretate_task_folder = 1;
					}

					if ($cretate_folder == 1 && $cretate_task_folder == 1)
					{
						return true;
					}

				return false;
				}

			return true;
			}
			else
			{
			return false;
			}
		}

	return false;
	}

	/**
	 * Creates an index.html file within folders for develop
	 *
	 * @param   string  $full_path  The full path.
	 *
	 * @return  bolean
	 *
	 * @since   4.11
	 */
	public static function createIndex($full_path = '')
	{
		if (!empty($full_path))
		{
		$path = JFolder::makeSafe($full_path . '/index.html');

		$index_content = '<!DOCTYPE html><title></title>';

			if (!JFile::exists($path))
			{
				JFile::write($path, $index_content);
			}

			if (!JFile::exists($path))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

	return false;
	}

	/**
	 * Gets the list of ini files in develop
	 *
	 * @param   array  $dev_data  Array with the data to the client path
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getInifilesindevlist($dev_data = array())
	{
		if (!empty($dev_data['full_client_path']))
		{
			$files = JFolder::files($dev_data['full_client_path'], ".ini$");

			return $files;
		}

	return array();
	}

	/**
	 * Gets the list of all type of files in develop
	 *
	 * @param   array  $dev_data  Array with the data to the client path
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getFilesindevlist($dev_data = array())
	{
		if (!empty($dev_data['full_client_path']))
		{
			$files = JFolder::files($dev_data['full_client_path']);

			return $files;
		}

	return array();
	}

	/**
	 * Gets from zero or keept updated the files in develop from Github
	 *
	 * @param   array  $gh_data  Array with the required data
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getGithubfiles($gh_data = array())
	{
		if (!empty($gh_data))
		{
			$gh_client = $gh_data['github_client'];
			$gh_user = $gh_data['github_user'];
			$gh_project = $gh_data['github_project'];
			$gh_trunk = $gh_data['dev_trunk'];
			$task_root_path = $gh_data['task_root_path'];
			$root_path = $gh_data['root_path'];
			$base_path = $gh_data['base_path'];
			$gh_client_path = $gh_data['client_path'];
			$gh_client_path_parts = $gh_data['client_path_parts'];
			$gh_tag = $gh_data['github_tag'];
			$gh_token = $gh_data['github_token'];
			$gh_full_client_path = $gh_data['full_client_path'];

			$options = new JRegistry;

			if (!empty($gh_token))
			{
				$options->set('gh.token', $gh_token);
				$gh_token = '';
				$github = new JGithub($options);
			}
			else
			{
				$github = new JGithub;
			}

			try
			{
				$repostoryfiles = $github->repositories->contents->get($gh_user, $gh_project, $gh_client_path, $gh_trunk);
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage($e);

				return false;
			}

			$full_client_path = JFolder::makeSafe($gh_full_client_path);
			$client_folder_exists = JFolder::exists($full_client_path);
			$stored_dev_files = $gh_data['stored_dev_files'];
			$sha_files_list = $gh_data['sha_files_list'];

			if ($client_folder_exists == '0')
			{
				return false;
			}

			$sha = '';
			$files_to_include = array();

			foreach ($repostoryfiles as $repostoryfile)
			{
				$file_path = JFolder::makeSafe($base_path . '/' . $repostoryfile->path);
				$file_to_include = $repostoryfile->name;

				if ((array_key_exists($file_to_include, $sha_files_list) && ($sha_files_list[$file_to_include] != $repostoryfile->sha)) || !JFile::exists($file_path))
				{
					$in_dev_file = $github->repositories->contents->get($gh_user, $gh_project, $repostoryfile->path, $gh_trunk);
				}
				else
				{
				$in_dev_file = '';
				}

				$files_to_include[] = $file_to_include;
				$dev_name = $gh_data['dev_name'];
				$sha_path = JFolder::makeSafe(JPATH_COMPONENT_ADMINISTRATOR . '/packages/in_dev/' . $dev_name . '.txt');

				if (!empty($in_dev_file) && isset($in_dev_file->content))
				{
					$file_to_include = $repostoryfile->name;
					$file_contents = base64_decode($in_dev_file->content);
					JFile::write($file_path, $file_contents);

						if (!JFile::exists($file_path))
						{
							return false;
						}
				}

				// Saved for each time due few times get all the github files at same time can crash.
				$sha .= $repostoryfile->name . "::" . $repostoryfile->sha . "\n";
				JFile::write($sha_path, $sha);

				if (!JFile::exists($sha_path))
				{
					return false;
				}
			}

			if (!empty($stored_dev_files) && !empty($files_to_include))
			{
				// For files not present in dev yet.
				$files_to_delete = array_diff($stored_dev_files, $files_to_include);

				if (!empty($files_to_delete))
				{
					foreach ($files_to_delete as $file_to_delete)
					{
						if ($file_to_delete != 'index.html')
						{
						$file_path = JFolder::makeSafe($gh_data['full_client_path'] . "/" . $file_to_delete);
						JFile::delete($file_path);

							if (JFile::exists($file_path))
							{
								return false;
							}
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Gets the info for files in dev
	 *
	 * @param   string  $tag  The language tag.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function scanFilesindev($tag = '')
	{
		$params = JComponentHelper::getParams('com_localise');
		$ref = $params->get('reference', 'en-GB');
		$have_tag = self::isTag($tag);
		$path = JFolder::makeSafe(JPATH_COMPONENT_ADMINISTRATOR . '/packages');
		$files = JFolder::files($path, ".xml$");
		$filesindev = array();
		$filesindev['site'] = array();
		$filesindev['administrator'] = array();
		$filesindev['installation'] = array();
		$have_task = 0;

		if ($have_tag == 'true' && ($tag != $ref))
		{
			$have_task = 1;
		}

		if (!empty($files))
		{
			foreach ($files as $file)
			{
			$xml = simplexml_load_file("$path/$file");

				if ($xml->allowdev == '1')
				{
					foreach (array('site', 'administrator', 'installation') as $client)
					{
					$gh_client_path = 'github' . $client . 'languagepath';
					$client_in_dev  = $client . 'indev';
					$taskrootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/task_in_dev/github');
					$rootpath       = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/in_dev/github');

					$client_path    = $xml->githubuser
							. '/'
							. $xml->githubproject
							. '/'
							. $xml->devtrunk
							. '/'
							. $xml->$gh_client_path;

					$clientpathparts = explode("/", $client_path);

					$language_tag = end($clientpathparts);
					$is_tag = self::isTag($language_tag);

					$full_client_path = JFolder::makeSafe($rootpath . '/' . $client_path);
					$full_client_task_path = '';

					$client_task_path = '';

						if ($is_tag == 'true' && $have_task == 1)
						{
							foreach ($clientpathparts as $clientpathpart)
							{
								if ($clientpathpart == $language_tag)
								{
								$client_task_path .= $tag;
								}
								else
								{
								$client_task_path .= $clientpathpart . '/';
								}
							}

							$full_client_task_path = JFolder::makeSafe($taskrootpath . '/' . $client_task_path);
						}

					$full_client_path_exists = JFolder::exists($full_client_path);

						if (!empty($xml->$gh_client_path) && $is_tag == 'true' && $full_client_path_exists == '1')
						{
						$fcp = $full_client_path;
						$filesindev[$client][$fcp] = array();
						$filesindev[$client][$fcp]['task_root_path'] = $taskrootpath;
						$filesindev[$client][$fcp]['root_path'] = $rootpath;
						$filesindev[$client][$fcp]['client_task_path'] = $client_task_path;
						$filesindev[$client][$fcp]['full_client_task_path'] = $full_client_task_path;
						$filesindev[$client][$fcp]['client_path'] = $client_path;
						$filesindev[$client][$fcp]['language_tag'] = $language_tag;
						$filesindev[$client][$fcp]['allow_dev'] = $xml->allowdev;
						$filesindev[$client][$fcp]['keep_customised_files'] = $xml->keepcustomisedfiles;
						$filesindev[$client][$fcp]['github_user'] = $xml->githubuser;
						$filesindev[$client][$fcp]['github_project'] = $xml->githubproject;
						$filesindev[$client][$fcp]['dev_trunk'] = $xml->devtrunk;
						$filesindev[$client][$fcp]['github_' . $client . '_language_path'] = $xml->$gh_client_path;
						$filesindev[$client][$fcp]['github_token'] = $xml->githubtoken;

							if ($xml->$client_in_dev)
							{
								foreach ($xml->$client_in_dev->children() as $file)
								{
									if (!empty($file) && $is_tag == 'true')
									{
										if ($file == 'joomla')
										{
										$name = "$language_tag.ini";
										$filesindev[$client][$fcp]['selected_files'][] = $name;
										}
										else
										{
										$name = "$language_tag.$file";
										$filesindev[$client][$fcp]['selected_files'][] = $name;
										}
									}
								}
							}
							else
							{
								$filesindev[$client][$fcp]['selected_files'] = array();
							}

						$dev_data = array();
						$dev_data['full_client_path'] = $full_client_path;
						$ini_files_list = self::getInifilesindevlist($dev_data);
						$filesindev[$client][$fcp]['ini_files_in_dev_list'] = $ini_files_list;
						$files_list = self::getFilesindevlist($dev_data);
						$filesindev[$client][$fcp]['files_in_dev_list'] = $files_list;
						}
					}
				}
			}
		}

	return $filesindev;
	}

	/**
	 * Gets the reference path for a file in develop from the file name.
	 *
	 * @param   string  $client      The client.
	 * @param   string  $gh_user     The Github user.
	 * @param   string  $gh_project  The Github project.
	 * @param   string  $gh_trunk    The Github trunk.
	 * @param   string  $fileindev   The file name.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getReffilepathindev($client = '', $gh_user = '', $gh_project = '', $gh_trunk = '', $fileindev = '')
	{
		$params = JComponentHelper::getParams('com_localise');
		$ref = $params->get('reference', 'en-GB');
		$path = JFolder::makeSafe(JPATH_COMPONENT_ADMINISTRATOR . '/packages');
		$files = JFolder::files($path, ".xml$");
		$allowed_clients = array('site', 'administrator', 'installation');
		$filesindev = array();
		$path = '';

		if (!empty($files) && in_array($client, $allowed_clients))
		{
			foreach ($files as $file)
			{
			$xml = simplexml_load_file("$path/$file");

				if ($xml->allowdev == '1' && $xml->githubuser == $gh_user && $xml->githubproject == $gh_project && $xml->devtrunk == $gh_trunk)
				{
					$gh_client_path = 'github' . $client . 'languagepath';
					$rootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/in_dev/github');

					$client_path    = $xml->githubuser . '/' . $xml->githubproject . '/' . $xml->devtrunk . '/' . $xml->$gh_client_path;

					$clientpathparts = explode("/", $client_path);
					$language_tag = end($clientpathparts);
					$is_tag = self::isTag($language_tag);
					$full_client_path = '';
					$client_path = '';

					if ($is_tag == 'true')
					{
						foreach ($clientpathparts as $clientpathpart)
						{
							$client_task_path .= $clientpathpart . '/';
						}

					$full_client_path = JFolder::makeSafe($rootpath . '/' . $client_path);
					}

					$full_client_path_exists = JFolder::exists($full_client_path);

					if (!empty($xml->$gh_client_path) && $is_tag == 'true' && $full_client_path_exists == '1')
					{
						$path = $full_client_path . '/' . $fileindev;

						if (!JFile::exists($path))
						{
								$path = '';
						}
					}
				}
			}
		}

	return $path;
	}

	/**
	 * Gets the develop path from the client path
	 *
	 * @param   string  $gh_user         The Github user.
	 * @param   string  $gh_project      The Github project.
	 * @param   string  $gh_trunk        The Github trunk.
	 * @param   string  $gh_client_path  The client path name.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getDevpath($gh_user = '', $gh_project = '', $gh_trunk = '', $gh_client_path = '')
	{
		$params = JComponentHelper::getParams('com_localise');
		$ref = $params->get('reference', 'en-GB');

		$path = '';

		$rootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/in_dev/github');

		$client_path    = $gh_user
				. '/'
				. $gh_project
				. '/'
				. $gh_trunk
				. '/'
				. $gh_client_path;

		$clientpathparts = explode("/", $client_path);
		$path_tag = end($clientpathparts);
		$is_tag = self::isTag($path_tag);
		$full_client_path = '';
		$client_path = '';

		if ($is_tag == 'true' && $path_tag == $ref)
		{
			foreach ($clientpathparts as $clientpathpart)
			{
				$client_path .= $clientpathpart . '/';
			}

		$full_client_path = JFolder::makeSafe($rootpath . '/' . $client_path);
		}

		$full_client_path_exists = JFolder::exists($full_client_path);

		if ($full_client_path_exists == '1')
		{
			$path = $full_client_path;
		}

	return $path;
	}

	/**
	 * Gets the task path from the language tag
	 *
	 * @param   string  $tag             The language tag.
	 * @param   string  $gh_user         The Github user.
	 * @param   string  $gh_project      The Github project.
	 * @param   string  $gh_trunk        The Github trunk.
	 * @param   string  $gh_client_path  The client path.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getTaskpath($tag = '', $gh_user = '', $gh_project = '', $gh_trunk = '', $gh_client_path = '')
	{
		$params = JComponentHelper::getParams('com_localise');
		$ref = $params->get('reference', 'en-GB');

		$have_task = 0;
		$have_tag = self::isTag($tag);

		if ($have_tag == 'true' && ($tag != $ref))
		{
			$have_task = 1;
		}

		$path = '';

		$rootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/task_in_dev/github');

		$client_path    = $gh_user
				. '/'
				. $gh_project
				. '/'
				. $gh_trunk
				. '/'
				. $gh_client_path;

		$clientpathparts = explode("/", $client_path);
		$path_tag = end($clientpathparts);
		$is_tag = self::isTag($path_tag);
		$full_client_path = '';
		$client_path = '';

		if ($is_tag == 'true' && $path_tag == $ref)
		{
			$replaced = 0;

			foreach ($clientpathparts as $clientpathpart)
			{
				if ($clientpathpart == $path_tag && $have_tag == 1)
				{
					$client_path .= $tag;
					$replaced = 1;
					break;
				}
				else
				{
					$client_path .= $clientpathpart . '/';
				}
			}

			if ($replaced == '1')
			{
				$full_client_path = JFolder::makeSafe($rootpath . '/' . $client_path);
			}
		}

		$full_client_path_exists = JFolder::exists($full_client_path);

		if ($full_client_path_exists == '1')
		{
			$path = $full_client_path;
		}

	return $path;
	}

	/**
	 * Gets the task path from the file name
	 *
	 * @param   string  $client      The client.
	 * @param   string  $tag         The language tag.
	 * @param   string  $gh_user     The Github user.
	 * @param   string  $gh_project  The Github project.
	 * @param   string  $gh_trunk    The Github trunk.
	 * @param   string  $fileintask  The file with translation in dev task.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getTaskfilepath($client = '', $tag = '', $gh_user = '', $gh_project = '', $gh_trunk = '', $fileintask = '')
	{
		$params          = JComponentHelper::getParams('com_localise');
		$ref             = $params->get('reference', 'en-GB');
		$have_tag        = self::isTag($tag);
		$path            = JFolder::makeSafe(JPATH_COMPONENT_ADMINISTRATOR . '/packages');
		$files           = JFolder::files($path, ".xml$");
		$allowed_clients = array('site', 'administrator', 'installation');
		$filesindev      = array();
		$taskpath        = '';
		$have_task       = 0;

		if ($have_tag == 'true' && ($tag != $ref))
		{
			$have_task = 1;
		}

		if (!empty($files) && $have_task == 1 && in_array($client, $allowed_clients))
		{
			foreach ($files as $file)
			{
			$xml = simplexml_load_file("$path/$file");

				if ($xml->allowdev == '1' && $xml->githubuser == $gh_user && $xml->githubproject == $gh_project && $xml->devtrunk == $gh_trunk)
				{
					$gh_client_path = 'github' . $client . 'languagepath';
					$taskrootpath  = JFolder::makeSafe(JPATH_ROOT . '/media/com_localise/task_in_dev/github');

					$client_path    = $xml->githubuser . '/' . $xml->githubproject . '/' . $xml->devtrunk . '/' . $xml->$gh_client_path;

					$clientpathparts = explode("/", $client_path);
					$language_tag = end($clientpathparts);
					$is_tag = self::isTag($language_tag);
					$full_client_task_path = '';
					$client_task_path = '';

					if ($is_tag == 'true')
					{
						foreach ($clientpathparts as $clientpathpart)
						{
							if ($clientpathpart == $language_tag)
							{
							$client_task_path .= $tag;
							}
							else
							{
							$client_task_path .= $clientpathpart . '/';
							}
						}

					$full_client_task_path = JFolder::makeSafe($taskrootpath . '/' . $client_task_path);
					}

					$full_client_path_exists = JFolder::exists($full_client_task_path);

					if (!empty($xml->$gh_client_path) && $is_tag == 'true' && $full_client_path_exists == '1')
					{
						$taskpath = $full_client_task_path . '/' . $fileintask;

						if (!JFile::exists($taskpath))
						{
								$taskpath = '';
						}
					}
				}
			}
		}

	return $taskpath;
	}

	/**
	 * Gets the ini file headers from a path
	 *
	 * @param   string  $path  The language tag.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getfileheaders($path = '')
	{
	$file_headers = array();
	$file_headers['svn'] = '';
	$file_headers['date'] = '';
	$file_headers['autor'] = '';
	$file_headers['copyright'] = array();
	$file_headers['note'] = array();
	$file_headers['headers'] = "";

		if (!empty($path) && JFile::exists($path))
		{
			$stream             = new JStream;
			$stream->open($path);
			$stream->seek(0);

			while (!$stream->eof())
			{
				$line = $stream->gets();

				if ($line[0] == ';')
				{
					if (preg_match('/^(;).*(\$Id.*\$)/', $line, $matches))
					{
						$file_headers['svn'] = $matches[2];
					}
					elseif (preg_match('/(;)\s*@?(\pL+):?.*/', $line, $matches))
					{
						switch (strtolower($matches[2]))
						{
							case 'date':
								preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
								$file_headers['date'] = $matches2[3];
								break;
							case 'author':
								preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
								$file_headers['author'] = $matches2[3];
								break;
							case 'copyright':
								preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
								$file_headers['copyright'][] = $matches2[3];
								break;
							case 'license':
								preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
								$file_headers['license'] = $matches2[3];
								break;
							case 'note':
								preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
								$file_headers['note'][] = $matches2[3];
								break;
							default:
								break;
						}
					}
				}
			}

			$stream->close();
		}

		if (!empty($file_headers['svn']))
		{
			$file_headers['headers'] .= "; " . $file_headers['svn'] . "\n";
		}

		if (!empty($file_headers['date']))
		{
			$file_headers['headers'] .= "; @date        " . $file_headers['date'] . "\n";
		}

		if (!empty($file_headers['author']))
		{
			$file_headers['headers'] .= "; @author      " . $file_headers['author'] . "\n";
		}

		if (!empty($file_headers['copyright']))
		{
			foreach ($file_headers['copyright'] as $copyright)
			{
			$file_headers['headers'] .= "; @copyright   " . $copyright . "\n";
			}
		}

		if (!empty($file_headers['license']))
		{
			$file_headers['headers'] .= "; @license     " . $file_headers['license'] . "\n";
		}

		if (!empty($file_headers['note']))
		{
			foreach ($file_headers['note'] as $note)
			{
				$file_headers['headers'] .= "; @note        " . $note . "\n";
			}
		}

	return $file_headers;
	}

	/**
	 * Gets file content from the involved ini files
	 *
	 * @param   string  $tag            The language tag.
	 * @param   string  $headers_path   The translated file path to get the headers.
	 * @param   string  $ref_keys_path  The path to reference keys file.
	 * @param   array   $frozenref      The keys in frozen reference.
	 * @param   array   $frozentask     The keys in translated frozen task.
	 * @param   array   $devref         The keys in develop reference.
	 * @param   array   $devtask        The keys in develop task.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getDevcontents($tag = '', $headers_path = '', $ref_keys_path = '', $frozenref = array(), $frozentask = array(), $devref = array(), $devtask = array())
	{
		$file_data = array();
		$file_data['stringsindev'] = array();
		$file_data['keys_to_add'] = array();
		$file_data['keys_to_delete'] = array();
		$file_data['headers'] = '';
		$file_data['contents'] = '';
		$keys_to_add_in_dev = array();
		$keys_to_add = array();
		$keys_to_delete = array();
		$keys_in_dev_task = array();
		$keys_to_keep = self::getKeystokeep($tag);

		if (!empty($devref))
		{
			$keys_in_dev_ref = array_keys($devref);

			if (!empty($frozenref))
			{
				$keys_in_frozen_ref = array_keys($frozenref);
				$keys_to_delete = array_diff($keys_in_frozen_ref, $keys_in_dev_ref);
				$keys_to_add_in_dev = array_diff($keys_in_dev_ref, $keys_in_frozen_ref);
			}

			if (!empty($frozentask))
			{
				$keys_in_frozen_task = array_keys($frozentask);
				$keys_to_add = array_diff($keys_in_frozen_task, $keys_in_dev_ref);
			}

			if (!empty($devtask))
			{
				$keys_in_dev_task = array_keys($devtask);
			}

			// Revising all keys in dev
			foreach ($devref as $dev_ref_key => $dev_ref_string)
			{
				if (isset($devtask[$dev_ref_key]))
				{
					// If exists in developed task, set the string in task.
					$file_data['stringindev'][$dev_ref_key] = $devtask[$dev_ref_key];
				}
				elseif (isset($frozentask[$dev_ref_key]))
				{
					// If exists in frozen a translated value, set the translated string.
					$file_data['stringindev'][$dev_ref_key] = $frozentask[$dev_ref_key];
				}
				else
				{
					// If not, keep the string in dev.
					$file_data['stringindev'][$dev_ref_key] = $dev_ref_string;
				}
			}

			if (!empty($keys_to_delete))
			{
				foreach ($keys_to_delete as $key_to_delete => $string_to_delete)
				{
					if (!empty($keys_to_add))
					{
						if (isset($keys_to_add[$key_to_delete]))
						{
							// Also present in frozen task but with translated value.
							$file_data['keys_to_delete'][$key_to_delete] = $keys_to_add[$key_to_delete];
							unset($keys_to_add[$key_to_delete]);
						}
						else
						{
							$file_data['keys_to_delete'][$key_to_delete] = $string_to_delete;
						}
					}
				}
			}

			// Keys only presents in frozen task
			if (!empty($keys_to_add))
			{
				foreach ($keys_to_add as $key_to_add => $string_to_add)
				{
					// Also become to delete due not present in dev.
					$file_data['keys_to_delete'][$key_to_add] = $string_to_add;
				}
			}

			// Determine what is key to add or extra.
			if (!empty($file_data['keys_to_delete']))
			{
				foreach ($file_data['keys_to_delete'] as $key_to_delete => $string_to_delete)
				{
					$line = $key_to_delete . '="' . $string_to_delete . '"';

					if (in_array($line, $keys_to_keep))
					{
						// Now is sure that is extra.
						$file_data['keys_to_add'][$key_to_delete] = $string_to_delete;
						unset($file_data['keys_to_delete'][$key_to_delete]);
					}
				}
			}
		}
		elseif (!empty($frozenref)) // Maybe is a selected file not present in dev.
		{
			$keys_in_frozen_ref = array_keys($frozenref);

			if (!empty($frozentask))
			{
				$keys_in_frozen_task = array_keys($frozentask);
				$keys_to_delete = array_diff($keys_in_frozen_task, $keys_in_frozen_ref);
				$keys_to_add = array_diff($keys_in_frozen_ref, $keys_in_frozen_task);
			}

			// Revising all keys in frozen ref.
			foreach ($frozenref as $frozen_ref_key => $frozen_ref_string)
			{
				if (isset($keys_in_frozen_task[$frozen_ref_key]))
				{
					// If exists in frozen task, set the string in task.
					$file_data['stringindev'][$frozen_ref_key] = $keys_in_frozen_task[$frozen_ref_key];
				}
				else
				{
					// If not, keep the string in frozen ref.
					$file_data['stringindev'][$dev_ref_key] = $dev_ref_string;
				}
			}

			if (!empty($keys_to_delete))
			{
				foreach ($keys_to_delete as $key_to_delete => $string_to_delete)
				{
					if (!empty($keys_to_add))
					{
						if (isset($keys_to_add[$key_to_delete]))
						{
							// Also present in frozen task but with translated value.
							$file_data['keys_to_delete'][$key_to_delete] = $keys_to_add[$key_to_delete];
							unset($keys_to_add[$key_to_delete]);
						}
						else
						{
							$file_data['keys_to_delete'][$key_to_delete] = $string_to_delete;
						}
					}
				}
			}

			// Keys only presents in frozen task
			if (!empty($keys_to_add))
			{
				foreach ($keys_to_add as $key_to_add => $string_to_add)
				{
					// Also become to delete due not present frozen dev.
					$file_data['keys_to_delete'][$key_to_add] = $string_to_add;
				}
			}

			// Determine what is key to add or extra.
			if (!empty($file_data['keys_to_delete']))
			{
				foreach ($file_data['keys_to_delete'] as $key_to_delete => $string_to_delete)
				{
					$line = $key_to_delete . '="' . $string_to_delete . '"';

					if (in_array($line, $keys_to_keep))
					{
						// Now is sure that is extra.
						$file_data['keys_to_add'][$key_to_delete] = $string_to_delete;
						unset($file_data['keys_to_delete'][$key_to_delete]);
					}
				}
			}
		}
		elseif (!empty($frozentask)) // Maybe is a selected file only present in frozen task, without dev or ref.
		{
			$file_data['stringindev'] = $frozentask;
			$file_data['keys_to_add'] = array();
			$file_data['keys_to_delete'] = array();
			$file_data['contents'] = file_get_contents($ref_keys_path);
		}

		if (!empty($headers_path) && JFile::exists($headers_path) && !empty($file_data['stringindev']) && empty($file_data['contents']))
		{
			// Headers for the translated file
			$headers = self::getfileheaders($headers_path);
			$file_data['headers'] = $headers['headers'];
			$file_data['contents'] = self::getRevisedcontent($file_data, $ref_keys_path);
		}

	return $file_data;
	}

	/**
	 * Gets a mounted file with the content comming from the involved ini files
	 *
	 * @param   array   $file_data      The array whith the required data.
	 * @param   string  $ref_keys_path  The path to the file with the reference keys.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getRevisedcontent($file_data = array(), $ref_keys_path = '')
	{
		$contents = array();
		$contents_header = $file_data['headers'] . "\n";

		if (!empty($file_data['stringindev']) && !empty($ref_keys_path) && JFile::exists($ref_keys_path))
		{
			$stream   = new JStream;
			$stream->open($ref_keys_path);

			$strings = $file_data['stringindev'];
			$keys_to_add = $file_data['keys_to_add'];
			$keys_to_delete = $file_data['keys_to_delete'];
			$after_header = 0;

			while (!$stream->eof())
			{
				$line = $stream->gets();

				if (preg_match('/^([A-Z][A-Z0-9_\-\.]*)\s*=/', $line, $matches))
				{
					$after_header = 1;
					$key = $matches[1];

					if (isset($strings[$key]))
					{
						$contents[] = $key . '="' . str_replace('"', '"_QQ_"', $strings[$key]) . "\"\n";
					}
				}
				else
				{
					if ($after_header == 1)
					{
					$contents[] = $line;
					}
				}
			}

			$stream->close();
			$contents = implode($contents);
			$contents = $contents_header . $contents;

			if (!empty($keys_to_add))
			{
				$contents .= "\n[Keys to keep in target]\n\n";
				$contents .= ";The next keys are not present in en-GB language but are used as extra in this language
							(extra plural cases, custom CAPTCHA translations, etc).\n\n";

				foreach ($keys_to_add as $key => $string)
				{
					$contents .= $key . '="' . str_replace('"', '"_QQ_"', $string) . "\"\n";
				}
			}

			if (!empty($keys_to_delete))
			{
				$contents .= "\n[Keys to delete]\n\n";
				$contents .= ";This keys are not used in en-GB language and are not required in this language.\n\n";

				foreach ($keys_to_delete as $key => $string)
				{
					$contents .= $key . '="' . str_replace('"', '"_QQ_"', $string) . "\"\n";
				}
			}
		}

	return $contents;
	}

	/**
	 * Gets the keys to keep in target from the selected language tag.
	 *
	 * @param   string  $tag  The language tag.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function getKeystokeep($tag = '')
	{
		$params                       = JComponentHelper::getParams('com_localise');
		$target_tag                   = preg_quote($tag, '-');
		$regex_syntax                 = '/\[' . $target_tag . '\](.*?)\[\/' . $target_tag . '\]/s';
		$regex_lines                  = '/\r\n|\r|\n/';
		$global_keys_to_keep          = array();
		$keys_to_keep                 = array();

		$global_keys_to_keep = $params->get('keystokeep', '');

		if (preg_match($regex_syntax, $global_keys_to_keep))
		{
			preg_match_all($regex_syntax, $global_keys_to_keep, $preg_result, PREG_SET_ORDER);

			$keys_to_keep = preg_split($regex_lines, $preg_result[0][1]);
		}
		else
		{
			$keys_to_keep = array();
		}

	return $keys_to_keep;
	}

	/**
	 * Gets the stored SHA id for the files in develop.
	 *
	 * @param   array  $gh_data  The required data.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getShafileslist($gh_data = array())
	{
	$sha_files = array();

		if (!empty($gh_data['sha_path']) && is_file(basename($gh_data['sha_path'])))
		{
			$file_contents = file_get_contents($gh_data['sha_path']);
			$lines = preg_split("/\\r\\n|\\r|\\n/", $file_contents);

			if (!empty($lines))
			{
				foreach ($lines as $line)
				{
					list($filename, $sha) = explode('::', $line, 2);

					if (!empty($filename) && !empty($sha))
					{
						$sha_files[$filename] = $sha;
					}
				}
			}
		}

	return $sha_files;
	}

	/**
	 * Gets if is a valid tag format or no.
	 *
	 * @param   string  $language_tag  The language tag.
	 *
	 * @return  bolean
	 *
	 * @since   4.11
	 */
	public static function isTag($language_tag = '')
	{
		if (strpos($language_tag, "-") && strlen($language_tag) >= '5' && strlen($language_tag) <= '6')
		{
			list($left_side, $right_side) = explode('-', $language_tag, 2);

			if ((is_string($left_side) && is_string($right_side)) && (strtolower($left_side) == $left_side && strtoupper($right_side) == $right_side))
			{
				if ((strlen($left_side) == '2' || strlen($left_side) == '3') && strlen($right_side) == '2')
				{
					return true;
				}
			}
		}

	return false;
	}

	/**
	 * Gets an array with the diff in develop.
	 *
	 * @param   array  $extrakeysindev    The keys to add in develop.
	 * @param   array  $textchangesindev  The text changes in develop.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getSectionsindev ($extrakeysindev = array(), $textchangesindev = array())
	{
		$dev_files_sections = array();

		if (!empty($textchangesindev))
		{
			foreach ($textchangesindev as $target_dev => $textchangesindev)
			{
				foreach ($textchangesindev as $key => $textchange)
				{
					$dev_files_sections[$target_dev]['text_changes'][$key] = $textchange;
				}
			}
		}

		if (!empty($extrakeysindev))
		{
			foreach ($extrakeysindev as $target_dev => $extrakeysindev)
			{
				foreach ($extrakeysindev as $key => $extrakey)
				{
					$dev_files_sections[$target_dev]['extra_keys_in_dev'][$key] = $extrakey;
				}
			}
		}

	return $dev_files_sections;
	}

	/**
	 * Gets the default value and status for one string based on the involved ones.
	 *
	 * @param   string  $string_in_dev          The string in reference develop.
	 * @param   string  $string_in_ref          The string in reference frozen.
	 * @param   string  $string_in_translation  The string in frozen translation.
	 * @param   string  $string_in_task         The string in develop translation.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getDefaultvalue($string_in_dev = '', $string_in_ref = '', $string_in_translation = '', $string_in_task = '')
	{
		if (!empty($string_in_task) && ($string_in_task != $string_in_dev) && ($string_in_task != $string_in_translation) && ($string_in_task != $string_in_ref))
		{
			return array ('default' => $string_in_task, 'status' => 'translated');
		}
		elseif (!empty($string_in_translation))
		{
			return array ('default' => $string_in_translation, 'status' => 'untranslated');
		}
		elseif (!empty($string_in_dev))
		{
			return array ('default' => $string_in_dev, 'status' => 'untranslated');
		}

	return array ('default' => $string_in_ref, 'status' => 'untranslated');
	}

	/**
	 * Gets the text changes.
	 *
	 * @param   array  $old  The string parts in reference.
	 * @param   array  $new  The string parts in develop.
	 *
	 * @return  array
	 *
	 * @since   4.11
	 */
	public static function getTextchanges($old, $new)
	{
		$maxlen = 0;
		$ret = '';

		foreach ($old as $oindex => $ovalue)
		{
		$nkeys = array_keys($new, $ovalue);

			foreach ($nkeys as $nindex)
			{
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;

				if ($matrix[$oindex][$nindex] > $maxlen)
				{
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex + 1 - $maxlen;
				$nmax = $nindex + 1 - $maxlen;
				}

			unset ($nkeys, $nindex);
			}

		unset ($oindex, $ovalue);
		}

		if ($maxlen == 0)
		{
		return array(array ('d' => $old, 'i' => $new));
		}

		return array_merge(self::getTextchanges(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)), array_slice($new, $nmax, $maxlen), self::getTextchanges(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
	}

	/**
	 * Gets the html text changes.
	 *
	 * @param   string  $old  The string in reference.
	 * @param   string  $new  The string parts in develop.
	 *
	 * @return  string
	 *
	 * @since   4.11
	 */
	public static function htmlgetTextchanges($old, $new)
	{
		$text_changes = '';

		if ($old == $new)
		{
			return $text_changes;
		}

		$old = str_replace('  ', ' LOCALISEDOUBLESPACES', $old);
		$new = str_replace('  ', ' LOCALISEDOUBLESPACES', $new);

		$diff = self::getTextchanges(explode(' ', $old), explode(' ', $new));

		foreach ($diff as $k)
		{
			if (is_array($k))
			{
			$text_changes .= (!empty ($k['d'])?"LOCALISEDELSTART" . implode(' ', $k['d']) . "LOCALISEDELSTOP ":'') . (!empty($k['i']) ? "LOCALISEINSSTART" . implode(' ', $k['i']) . "LOCALISEINSSTOP " : '');
			}
			else
			{
			$text_changes .= $k . ' ';
			}

		unset ($k);
		}

		$text_changes = htmlspecialchars($text_changes);
		$text_changes = preg_replace('/LOCALISEINSSTART/', "<ins class='diff_ins'>", $text_changes);
		$text_changes = preg_replace('/LOCALISEINSSTOP/', "</ins>", $text_changes);
		$text_changes = preg_replace('/LOCALISEDELSTART/', "<del class='diff_del'>", $text_changes);
		$text_changes = preg_replace('/LOCALISEDELSTOP/', "</del>", $text_changes);
		$double_spaces = '<span class="red-space"><font color="red">XX</font></span>';
		$text_changes = preg_replace('/ LOCALISEDOUBLESPACES/', $double_spaces, $text_changes);

	return $text_changes;
	}
}
