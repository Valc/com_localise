<?php
/**
 * @package     Com_Localise
 * @subpackage  model
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.stream');
jimport('joomla.client.helper');
jimport('joomla.access.rules');

/**
 * Translation Model class for the Localise component
 *
 * @since  1.0
 */
class LocaliseModelTranslation extends JModelAdmin
{
	protected $item;

	protected $contents;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$input = JFactory::getApplication()->input;

		// Get the infos
		$client   = $input->getCmd('client', '');
		$tag      = $input->getCmd('tag', '');
		$filename = $input->getCmd('filename', '');
		$storage  = $input->getCmd('storage', '');
		$tab      = $input->getCmd('tab', '');

		$this->setState('translation.client', !empty($client) ? $client : 'site');
		$this->setState('translation.tag', $tag);
		$this->setState('translation.filename', $filename);
		$this->setState('translation.storage', $storage);
		$this->setState('translation.tab', !empty($tab) ? $tab : 'released');

		// Get the id
		$id = $input->getInt('id', '0');
		$this->setState('translation.id', $id);

		// Get the layout
		$layout = $input->getCmd('layout', 'edit');
		$this->setState('translation.layout', $layout);

		// Get the parameters
		$params = JComponentHelper::getParams('com_localise');

		// Get the reference tag
		$ref = $params->get('reference', 'en-GB');
		$this->setState('translation.reference', $ref);

		// Get the paths
		$path = LocaliseHelper::getTranslationPath($client, $tag, $filename, $storage);

		if ($filename == 'lib_joomla')
		{
			$refpath = LocaliseHelper::findTranslationPath('administrator', $ref, $filename);

			if (!JFile::exists($path))
			{
				$path2 = LocaliseHelper::getTranslationPath($client == 'administrator' ? 'site' : 'administrator', $tag, $filename, $storage);

				if (JFile::exists($path2))
				{
					$path = $path2;
				}
			}
		}
		else
		{
			$refpath = LocaliseHelper::findTranslationPath($client, $ref, $filename);
		}

		$this->setState('translation.path', $path);
		$this->setState('translation.refpath', $refpath);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 */
	public function getTable($type = 'Localise', $prefix = 'LocaliseTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get contents
	 *
	 * @return string
	 */
	public function getContents()
	{
		if (!isset($this->contents))
		{
			$path = $this->getState('translation.path');

			if (JFile::exists($path))
			{
				$this->contents = file_get_contents($path);
			}
			else
			{
				$this->contents = '';
			}
		}

		return $this->contents;
	}

	/**
	 * Get a translation
	 *
	 * @param   integer  $pk  The id of the primary key (Note unused by the function).
	 *
	 * @return  JObject|null  Object on success, null on failure.
	 */
	public function getItem($pk = null)
	{
		if (!isset($this->item))
		{
			$conf    = JFactory::getConfig();
			$caching = $conf->get('caching') >= 1;

			if ($caching)
			{
				$keycache   = $this->getState('translation.client') . '.' . $this->getState('translation.tag') . '.' .
					$this->getState('translation.filename') . '.' . 'translation';
				$cache      = JFactory::getCache('com_localise', '');
				$this->item = $cache->get($keycache);

				if ($this->item && $this->item->reference != $this->getState('translation.reference'))
				{
					$this->item = null;
				}
			}
			else
			{
				$this->item = null;
			}

			if (!$this->item)
			{
				$path = JFile::exists($this->getState('translation.path'))
					? $this->getState('translation.path')
					: $this->getState('translation.refpath');

				$tag                 = $this->getState('translation.tag');
				$reftag              = $this->getState('translation.reference');
				$scans_in_dev        = LocaliseHelper::scanFilesindev($tag);

				$task_file           = basename($this->getState('translation.path'));
				$ref_file            = basename($this->getState('translation.refpath'));
				$client_in_dev       = $this->getState('translation.client');
				$client_files_in_dev = array();
				$client_data_in_dev  = array();
				$istranslation       = 0;

				if (!empty($tag) && $tag != $reftag)
				{
					$istranslation = 1;
				}

				if (!empty($scans_in_dev[$client_in_dev]))
				{
					foreach ($scans_in_dev[$client_in_dev] as $ref_path_in_dev => $data_in_dev)
					{
						if ($data_in_dev['language_tag'] == $reftag && isset($data_in_dev['files_in_dev_list']))
						{
							foreach ($data_in_dev['files_in_dev_list'] as $file_in_dev)
							{
								$client_files_in_dev[$file_in_dev][] = $ref_path_in_dev;
								$client_data_in_dev[$ref_path_in_dev]['github_user'] = $data_in_dev['github_user'];
								$client_data_in_dev[$ref_path_in_dev]['github_project'] = $data_in_dev['github_project'];
								$client_data_in_dev[$ref_path_in_dev]['dev_trunk'] = $data_in_dev['dev_trunk'];

								$client_data_in_dev[$ref_path_in_dev]['full_client_task_path'] = $data_in_dev['full_client_task_path'];
							}
						}
					}
				}

				$have_dev              = 0;
				$devs_amount           = 0;
				$dev_paths             = array();
				$task_dev_paths        = array();
				$github_users          = array();
				$github_projects       = array();
				$github_dev_trunks     = array();
				$github_task_dev_paths = array();

					if (array_key_exists($ref_file, $client_files_in_dev))
					{
						$have_dev = 1;

						if (count($client_files_in_dev[$ref_file]) >= 1)
						{
							$devs_amount = count($client_files_in_dev[$ref_file]);
							$dev_paths = $client_files_in_dev[$ref_file];

							foreach ($dev_paths as $dev_path)
							{
							$github_users[$dev_path] = $client_data_in_dev[$dev_path]['github_user'];
							$github_projects[$dev_path] = $client_data_in_dev[$dev_path]['github_project'];
							$github_dev_trunks[$dev_path] = $client_data_in_dev[$dev_path]['dev_trunk'];
							$github_task_dev_paths[$dev_path] = $client_data_in_dev[$dev_path]['full_client_task_path'];
							}
						}
					}

				$this->setState('translation.havedev', $have_dev);
				$this->setState('translation.devsamount', $devs_amount);
				$this->setState('translation.devpaths', $dev_paths);
				$this->setState('translation.githubusers', $github_users);
				$this->setState('translation.githubprojects', $github_projects);
				$this->setState('translation.githubdevtrunks', $github_dev_trunks);
				$this->setState('translation.taskdevpaths', $github_task_dev_paths);

				$have_dev = $this->getState('translation.havedev');
				$devs_amount = $this->getState('translation.devsamount');
				$dev_paths = $this->getState('translation.devpaths');
				$github_users = $this->getState('translation.githubusers');
				$github_projects = $this->getState('translation.githubprojects');
				$github_dev_trunks = $this->getState('translation.githubdevtrunks');
				$task_dev_paths = $this->getState('translation.taskdevpaths');

				// Get Special keys cases.
				$params                       = JComponentHelper::getParams('com_localise');
				$tag                          = $this->getState('translation.tag');
				$target_tag                   = preg_quote($tag, '-');
				$special_keys_types           = array ('untranslatablestrings', 'blockedstrings', 'keystokeep');
				$regex_syntax                 = '/\[' . $target_tag . '\](.*?)\[\/' . $target_tag . '\]/s';
				$regex_lines                  = '/\r\n|\r|\n/';
				$global_special_keys          = array();
				$special_keys                 = array();

				foreach ($special_keys_types as $special_keys_case)
				{
					$global_special_keys[$special_keys_case] = $params->get($special_keys_case, '');

					if (preg_match($regex_syntax, $global_special_keys[$special_keys_case]))
					{
						preg_match_all($regex_syntax, $global_special_keys[$special_keys_case], $preg_result, PREG_SET_ORDER);

						$special_keys[$special_keys_case] = preg_split($regex_lines, $preg_result[0][1]);
					}
					else
					{
						$special_keys[$special_keys_case] = array();
					}

					$this->setState('translation.' . $special_keys_case, (array) $special_keys[$special_keys_case]);
				}

				$untranslatablestrings = $special_keys['untranslatablestrings'];
				$blockedstrings        = $special_keys['blockedstrings'];
				$keystokeep            = $special_keys['keystokeep'];

				$this->setState('translation.translatedkeys', array());
				$this->setState('translation.untranslatedkeys', array());
				$this->setState('translation.blockedkeys', array());
				$this->setState('translation.untranslatablekeys', array());
				$this->setState('translation.extrakeysindev', array());
				$this->setState('translation.textchangesindev', array());
				$this->setState('translation.textchangesref', array());
				$this->setState('translation.textchangesdefault', array());
				$this->setState('translation.stringsintasks', array());

				$translatedkeys     = $this->getState('translation.translatedkeys');
				$untranslatedkeys   = $this->getState('translation.untranslatedkeys');
				$blockedkeys        = $this->getState('translation.blockedkeys');
				$untranslatablekeys = $this->getState('translation.untranslatablekeys');
				$extrakeysindev     = $this->getState('translation.extrakeysindev');
				$textchangesindev   = $this->getState('translation.textchangesindev');
				$textchangesref     = $this->getState('translation.textchangesref');
				$textchangesdefault = $this->getState('translation.textchangesdefault');
				$stringsintasks     = $this->getState('translation.stringsintasks');

				$this->item = new JObject(
									array
										(
										'reference'             => $this->getState('translation.reference'),
										'bom'                   => 'UTF-8',
										'svn'                   => '',
										'version'               => '',
										'description'           => '',
										'textchange '           => '',
										'creationdate'          => '',
										'author'                => '',
										'maincopyright'         => '',
										'additionalcopyright'   => array(),
										'license'               => '',
										'exists'                => JFile::exists($this->getState('translation.path')),
										'tab'                   => $this->getState('translation.tab'),
										'istranslation'         => $istranslation,
										'havedev'               => $have_dev,
										'devsamount'            => $devs_amount,
										'devpaths'              => $dev_paths,
										'taskdevpaths'          => $task_dev_paths,
										'stringsintasks'        => $stringsintasks,
										'githubusers'           => $github_users,
										'githubprojects'        => $github_projects,
										'githubdevtrunks'       => $github_dev_trunks,
										'extrakeysindev'        => (array) $extrakeysindev,
										'textchangesref'        => (array) $textchangesref,
										'textchangesdefault'        => (array) $textchangesdefault,
										'textchangesindev'      => (array) $textchangesindev,
										'translatedkeys'        => (array) $translatedkeys,
										'untranslatedkeys'      => (array) $untranslatedkeys,
										'blockedkeys'           => (array) $blockedkeys,
										'untranslatablekeys'    => (array) $untranslatablekeys,
										'translated'            => 0,
										'untranslated'          => 0,
										'untranslatable'        => 0,
										'blocked'               => 0,
										'extra'                 => 0,
										'extraindev'            => 0,
										'keytodelete'           => 0,
										'textchange'            => 0,
										'sourcestrings'         => 0,
										'revisedtextchanges'    => 0,
										'revisedextrasindev'    => 0,
										'total'                 => 0,
										'source'                => '',
										'untranslatablestrings' => (array) $untranslatablestrings,
										'blockedstrings'        => (array) $blockedstrings,
										'keystokeep'            => (array) $keystokeep,
										'error'                 => array()
										)
				);

				if (JFile::exists($path))
				{
					$this->item->source = file_get_contents($path);
					$stream             = new JStream;
					$stream->open($path);
					$begin = $stream->read(4);
					$bom   = strtolower(bin2hex($begin));

					if ($bom == '0000feff')
					{
						$this->item->bom = 'UTF-32 BE';
					}
					else
					{
						if ($bom == 'feff0000')
						{
							$this->item->bom = 'UTF-32 LE';
						}
						else
						{
							if (substr($bom, 0, 4) == 'feff')
							{
								$this->item->bom = 'UTF-16 BE';
							}
							else
							{
								if (substr($bom, 0, 4) == 'fffe')
								{
									$this->item->bom = 'UTF-16 LE';
								}
							}
						}
					}

					$stream->seek(0);
					$continue           = true;
					$lineNumber         = 0;
					$params             = JComponentHelper::getParams('com_localise');
					$isTranslationsView = JFactory::getApplication()->input->get('view') == 'translations';

					while (!$stream->eof())
					{
						$line = $stream->gets();
						$lineNumber++;

						if ($line[0] == '#')
						{
							$this->item->error[] = $lineNumber;
						}
						elseif ($line[0] == ';')
						{
							if (preg_match('/^(;).*(\$Id.*\$)/', $line, $matches))
							{
								$this->item->svn = $matches[2];
							}
							elseif (preg_match('/(;)\s*@?(\pL+):?.*/', $line, $matches))
							{
								switch (strtolower($matches[2]))
								{
									case 'version':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
										$this->item->version = $matches2[3];
										break;
									case 'desc':
									case 'description':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
										$this->item->description = $matches2[3];
										break;
									case 'date':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
										$this->item->creationdate = $matches2[3];
										break;
									case 'author':
										if ($params->get('author') && !$isTranslationsView)
										{
											$this->item->author = $params->get('author');
										}
										else
										{
											preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
											$this->item->author = $matches2[3];
										}
										break;
									case 'copyright':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);

										if (empty($this->item->maincopyright))
										{
											if ($params->get('copyright') && !$isTranslationsView)
											{
												$this->item->maincopyright = $params->get('copyright');
											}
											else
											{
												$this->item->maincopyright = $matches2[3];
											}
										}

										if (empty($this->item->additionalcopyright))
										{
											if ($params->get('additionalcopyright') && !$isTranslationsView)
											{
												$this->item->additionalcopyright[] = $params->get('additionalcopyright');
											}
											else
											{
												$this->item->additionalcopyright[] = $matches2[3];
											}
										}
										break;
									case 'license':
										if ($params->get('license') && !$isTranslationsView)
										{
											$this->item->license = $params->get('license');
										}
										else
										{
											preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
											$this->item->license = $matches2[3];
										}
										break;
									case 'package':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
										$this->item->package = $matches2[3];
										break;
									case 'subpackage':
										preg_match('/(;)\s*@?(\pL+):?\s+(.*)/', $line, $matches2);
										$this->item->subpackage = $matches2[3];
										break;
									case 'link':
										break;
									default:
										if (empty($this->item->author))
										{
											if ($params->get('author') && !$isTranslationsView)
											{
												$this->item->author = $params->get('author');
											}
											else
											{
												preg_match('/(;)\s*(.*)/', $line, $matches2);
												$this->item->author = $matches2[2];
											}
										}
										break;
								}
							}
						}
						else
						{
							break;
						}
					}

					if (empty($this->item->author) && $params->get('author') && !$isTranslationsView)
					{
						$this->item->author = $params->get('author');
					}

					if (empty($this->item->license) && $params->get('license') && !$isTranslationsView)
					{
						$this->item->license = $params->get('license');
					}

					if (empty($this->item->maincopyright) && $params->get('copyright') && !$isTranslationsView)
					{
						$this->item->maincopyright = $params->get('copyright');
					}

					if (empty($this->item->additionalcopyright) && $params->get('additionalcopyright') && !$isTranslationsView)
					{
						$this->item->additionalcopyright[] = $params->get('additionalcopyright');
					}

					while (!$stream->eof())
					{
						$line = $stream->gets();
						$lineNumber++;

						if (!preg_match('/^(|(\[[^\]]*\])|([A-Z][A-Z0-9_\-\.]*\s*=(\s*(("[^"]*")|(_QQ_)))+))\s*(;.*)?$/', $line))
						{
							$this->item->error[] = $lineNumber;
						}
					}

					$stream->close();
				}

				$this->item->additionalcopyright = implode("\n", $this->item->additionalcopyright);

				if ($this->getState('translation.layout') != 'raw' && empty($this->item->error))
				{
					$sections         = LocaliseHelper::parseSections($this->getState('translation.path'));
					$refsections      = LocaliseHelper::parseSections($this->getState('translation.refpath'));
					$refsectionsindev = array();
					$keys_in_devs     = array();
					$strings_in_devs  = array();
					$keys_in_tasks    = array();
					$dataintasks      = array();
					$extra_in_devs    = array();

					if ($have_dev == '1')
					{
						foreach ($dev_paths as $dev_path)
						{
						$refsectionsindev[$dev_path] = LocaliseHelper::parseSections("$dev_path/$ref_file");

							if (!empty($refsectionsindev[$dev_path]['keys']))
							{
							$keys_in_devs[$dev_path] = array_keys($refsectionsindev[$dev_path]['keys']);
							$strings_in_devs[$dev_path] = $refsectionsindev[$dev_path]['keys'];

								if (!empty($task_dev_paths[$dev_path]) && $istranslation == 1)
								{
									$task_dev_path = $task_dev_paths[$dev_path];
									$short_name    = $github_users[$dev_path]
											. '|'
											. $github_projects[$dev_path]
											. '|'
											. $github_dev_trunks[$dev_path];

									if (!JFile::exists("$task_dev_path/$task_file"))
									{
										$comment = ";CREATED AS EMPTY FILE\n";
										JFile::write("$task_dev_path/$task_file", $comment);
									}

									if (JFile::exists("$task_dev_path/$task_file"))
									{
										$dataintasks[$dev_path] = LocaliseHelper::parseSections("$task_dev_path/$task_file");
										$keys_in_tasks[$dev_path] = array_keys($dataintasks[$dev_path]['keys']);
										$stringsintasks[$short_name] = $dataintasks[$dev_path]['keys'];
									}
								}
							}
						}
					}

					if (!empty($refsections['keys']))
					{
					$keys_in_ref = array_keys($refsections['keys']);

						if (!empty($keys_in_devs))
						{
							// $dp means dev path.
							foreach ($keys_in_devs as $dp => $keys_in_dev)
							{
								$extra_in_dev = array_diff($keys_in_dev, $keys_in_ref);
								$extra_in_devs[$dp] = $extra_in_dev;

								if (!empty($extra_in_devs[$dp]))
								{
									foreach ($extra_in_devs[$dp] as $extra_in_dev)
									{
										$this->item->extraindev++;

										$target_dev = $github_users[$dp]
											. '|'
											. $github_projects[$dp]
											. '|'
											. $github_dev_trunks[$dp];

										$extrakeysindev[$target_dev][$extra_in_dev] = $refsectionsindev[$dp]['keys'][$extra_in_dev];

										if (isset($stringsintasks[$target_dev][$extra_in_dev]))
										{
											$sit = $stringsintasks[$target_dev][$extra_in_dev];
											$sid = $extrakeysindev[$target_dev][$extra_in_dev];

											if ($sit != $sid)
											{
												$this->item->revisedextrasindev++;
											}
										}
									}
								}
							}
						}

						foreach ($refsections['keys'] as $key => $string)
						{
							$this->item->total++;
							$full_line = $key . '="' . $string . '"';

							foreach ($strings_in_devs as $dp => $strings_in_dev)
							{
								$short_name    = $github_users[$dp]
										. '|'
										. $github_projects[$dp]
										. '|'
										. $github_dev_trunks[$dp];

								if (!empty($strings_in_devs[$dp]) && array_key_exists($key, $strings_in_dev))
								{
								$text_changes = localiseHelper::htmlgetTextchanges($string, $strings_in_dev[$key]);

									if (!empty($text_changes))
									{
										$string_in_ref = $string;
										$string_in_dev = $strings_in_dev[$key];
										$target_dev = $github_users[$dp]
											. '|'
											. $github_projects[$dp]
											. '|'
											. $github_dev_trunks[$dp];

										$this->item->textchange++;
										$textchangesindev[$target_dev][$key] = $text_changes;
										$textchangesref[$target_dev][$key] = $strings_in_dev[$key];
										$string_in_translation = '';

										if (!empty($sections['keys']) && array_key_exists($key, $sections['keys']))
										{
											$string_in_translation = $sections['keys'][$key];
										}

										$string_in_task = '';

										if (isset($stringsintasks[$short_name][$key]))
										{
											$string_in_task = $stringsintasks[$short_name][$key];
										}

										$default_textchange = localiseHelper::getDefaultvalue($string_in_dev, $string_in_ref, $string_in_translation, $string_in_task);
										$textchangesdefault[$target_dev][$key] = $default_textchange;

										if ($textchangesdefault[$target_dev][$key]['status'] == 'translated')
										{
											$this->item->revisedtextchanges++;
										}
									}
								}
							}

							if (!empty($sections['keys']) && array_key_exists($key, $sections['keys']))
							{
								if (in_array($full_line, $blockedstrings))
								{
									// Blocked keys with untranslated value.
									$this->item->translated++;
									$this->item->blocked++;
									$blockedkeys[] = $key;
								}
								elseif (in_array($full_line, $untranslatablestrings))
								{
									$this->item->translated++;
									$this->item->untranslatable++;
									$untranslatablekeys[] = $key;
								}
								elseif ($sections['keys'][$key] != $string)
								{
									$translated_line = $key . '="' . $sections['keys'][$key] . '"';

									// Blocked keys with translated value.
									if (in_array($translated_line, $blockedstrings))
									{
										$this->item->translated++;
										$this->item->blocked++;
										$blockedkeys[] = $key;
									}
									else
									{
										$this->item->translated++;
										$translatedkeys[] = $key;
									}
								}
								elseif ($this->getState('translation.path') == $this->getState('translation.refpath'))
								{
									$this->item->sourcestrings++;
								}
								else
								{
									$this->item->untranslated++;
									$untranslatedkeys[] = $key;
								}
							}
							elseif (!array_key_exists($key, $sections['keys']))
							{
								if (in_array($full_line, $blockedstrings))
								{
									$this->item->translated++;
									$this->item->blocked++;
									$blockedkeys[] = $key;
								}
								elseif (in_array($full_line, $untranslatablestrings))
								{
									$this->item->translated++;
									$this->item->untranslatable++;
									$untranslatablekeys[] = $key;
								}
								else
								{
									$this->item->untranslated++;
									$untranslatedkeys[] = $key;
								}
							}
						}
					}

					if (!empty($sections['keys']))
					{
						foreach ($sections['keys'] as $key => $string)
						{
							$full_line = $key . '="' . $string . '"';

							if (empty($refsections['keys']) || !array_key_exists($key, $refsections['keys']))
							{
								if (in_array($full_line, $blockedstrings))
								{
									$this->item->blocked++;
									$blockedkeys[] = $key;
								}

								if (in_array($key, $keystokeep))
								{
									$this->item->extra++;
								}
								else
								{
									$this->item->keytodelete++;
								}
							}
						}
					}

					$this->item->translatedkeys     = $translatedkeys;
					$this->item->untranslatedkeys   = $untranslatedkeys;
					$this->item->blockedkeys        = $blockedkeys;
					$this->item->untranslatablekeys = $untranslatablekeys;
					$this->item->textchangesindev   = $textchangesindev;
					$this->item->textchangesref     = $textchangesref;
					$this->item->textchangesdefault = $textchangesdefault;
					$this->item->extrakeysindev     = $extrakeysindev;
					$this->item->stringsintasks     = $stringsintasks;

					$this->setState('translation.translatedkeys', $translatedkeys);
					$this->setState('translation.untranslatedkeys', $untranslatedkeys);
					$this->setState('translation.blockedkeys', $blockedkeys);
					$this->setState('translation.untranslatablekeys', $untranslatablekeys);
					$this->setState('translation.textchangesindev', $textchangesindev);
					$this->setState('translation.textchangesinref', $textchangesref);
					$this->setState('translation.textchangesdefault', $textchangesdefault);
					$this->setState('translation.extrakeysindev', $extrakeysindev);
					$this->setState('translation.stringsintasks', $stringsintasks);
				}

				if ($this->getState('translation.id'))
				{
					$table = $this->getTable();
					$table->load($this->getState('translation.id'));
					$user = JFactory::getUser($table->checked_out);
					$this->item->setProperties($table->getProperties());

					if ($this->item->checked_out == JFactory::getUser()->id)
					{
						$this->item->checked_out = 0;
					}

					$this->item->editor = JText::sprintf('COM_LOCALISE_TEXT_TRANSLATION_EDITOR', $user->name, $user->username);
				}

				if ($caching)
				{
					$cache->store($this->item, $keycache);
				}
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_localise.translation', 'translation', array('control'   => 'jform', 'load_data' => $loadData));

		$params = JComponentHelper::getParams('com_localise');

		// Set fields readonly if localise global params exist
		if ($params->get('author'))
		{
			$form->setFieldAttribute('author', 'readonly', 'true');
		}

		if ($params->get('copyright'))
		{
			$form->setFieldAttribute('maincopyright', 'readonly', 'true');
		}

		if ($params->get('additionalcopyright'))
		{
			$form->setFieldAttribute('additionalcopyright', 'readonly', 'true');
		}

		if ($params->get('license'))
		{
			$form->setFieldAttribute('license', 'readonly', 'true');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 */
	protected function loadFormData()
	{
		return $this->getItem();
	}

	/**
	 * Method to get the ftp form.
	 *
	 * @return  mixed  A JForm object on success, false on failure or not ftp
	 */
	public function getFormFtp()
	{
		// Get the form.
		$form = $this->loadForm('com_localise.ftp', 'ftp');

		if (empty($form))
		{
			return false;
		}

		// Check for an error.
		if (JError::isError($form))
		{
			$this->setError($form->getMessage());

			return false;
		}

		return $form;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $item   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @throws  Exception if there is an error in the form event.
	 * @return  JForm
	 */
	protected function preprocessForm(JForm $form, $item, $group = 'content')
	{
		// Initialize variables.
		$filename              = $this->getState('translation.filename');
		$client                = $this->getState('translation.client');
		$tag                   = $this->getState('translation.tag');
		$origin                = LocaliseHelper::getOrigin($filename, $client);
		$app                   = JFactory::getApplication();
		$false                 = false;
		$untranslatablestrings = (array) $this->getState('translation.untranslatablestrings');
		$blockedstrings        = (array) $this->getState('translation.blockedstrings');
		$keystokeep            = (array) $this->getState('translation.keystokeep');

		// Compute all known languages
		static $languages = array();
		jimport('joomla.language.language');

		if (!array_key_exists($client, $languages))
		{
			$languages[$client] = JLanguage::getKnownLanguages(constant('LOCALISEPATH_' . strtoupper($client)));
		}

		if (is_object($item))
		{
			$form->setFieldAttribute('legend', 'translated', $item->translated, 'legend');
			$form->setFieldAttribute('legend', 'untranslatable', $item->untranslatable, 'legend');
			$form->setFieldAttribute('legend', 'blocked', $item->blocked, 'legend');
			$form->setFieldAttribute('legend', 'untranslated', $item->total - $item->translated, 'legend');
			$form->setFieldAttribute('legend', 'extra', $item->extra, 'legend');
			$form->setFieldAttribute('legend', 'extraindev', $item->extraindev, 'legend');
			$form->setFieldAttribute('legend', 'keytodelete', $item->keytodelete, 'legend');
			$form->setFieldAttribute('legend', 'textchange', $item->textchange, 'legend');

			$extrakeysindev        = (array) $item->extrakeysindev;
			$textchangesindev      = (array) $item->textchangesindev;
			$textchangesref        = (array) $item->textchangesref;
			$textchangesdefault    = (array) $item->textchangesdefault;
			$stringsintasks        = (array) $item->stringsintasks;
			$sectionsindev         = LocaliseHelper::getSectionsindev($extrakeysindev, $textchangesindev);
		}

		if ($this->getState('translation.layout') != 'raw')
		{
			$path          = $this->getState('translation.path');
			$refpath       = $this->getState('translation.refpath');
			$istranslation = 0;
			$sections      = LocaliseHelper::parseSections($path);
			$refsections   = LocaliseHelper::parseSections($refpath);
			$addform       = new SimpleXMLElement('<form />');

			$group = $addform->addChild('fields');
			$group->addAttribute('name', 'strings');

			$fieldset = $group->addChild('fieldset');
			$fieldset->addAttribute('name', 'Default');
			$fieldset->addAttribute('label', 'Default');

			if ($this->getState('translation.path') != $this->getState('translation.refpath'))
			{
			$istranslation = 1;
			}

			if (JFile::exists($refpath))
			{
				$stream = new JStream;
				$stream->open($refpath);
				$header     = true;
				$lineNumber = 0;
				$full_line  = '';

				while (!$stream->eof())
				{
					$line = $stream->gets();
					$lineNumber++;

					// Blank lines
					if (preg_match('/^\s*$/', $line))
					{
						$header = true;
						$field  = $fieldset->addChild('field');
						$field->addAttribute('label', '');
						$field->addAttribute('type', 'spacer');
						$field->addAttribute('class', 'text');
						continue;
					}
					// Section lines
					elseif (preg_match('/^\[([^\]]*)\]\s*$/', $line, $matches))
					{
						$header = false;
						$form->load($addform, false);
						$section = $matches[1];
						$addform = new SimpleXMLElement('<form />');
						$group   = $addform->addChild('fields');
						$group->addAttribute('name', 'strings');
						$fieldset = $group->addChild('fieldset');
						$fieldset->addAttribute('name', $section);
						$fieldset->addAttribute('label', $section);
						continue;
					}
					// Comment lines
					elseif (!$header && preg_match('/^;(.*)$/', $line, $matches))
					{
						$key   = $matches[1];
						$field = $fieldset->addChild('field');
						$field->addAttribute('label', $key);
						$field->addAttribute('type', 'spacer');
						$field->addAttribute('class', 'text');
						continue;
					}
					// Key lines
					elseif (preg_match('/^([A-Z][A-Z0-9_\-\.]*)\s*=/', $line, $matches))
					{
						$header    = false;
						$key       = $matches[1];
						$field     = $fieldset->addChild('field');
						$string    = $refsections['keys'][$key];
						$full_line = $key . '="' . $string . '"';

						if ($istranslation == '1')
						{
							if (in_array($full_line, $blockedstrings))
							{
								$status = 'blocked';
								$default = $string;
								$field->addAttribute('default', $default);
							}
							elseif (in_array($full_line, $untranslatablestrings))
							{
								$status = "untranslatable";
								$default = $string;
								$field->addAttribute('default', $default);
							}
							elseif (isset($sections['keys'][$key])
							&& ($sections['keys'][$key] != $refsections['keys'][$key]))
							{
								$translated_line = $key . '="' . $sections['keys'][$key] . '"';

								if (in_array($translated_line, $blockedstrings))
								{
									$status = 'blocked';
									$default = $sections['keys'][$key];
									$field->addAttribute('default', $default);
								}
								else
								{
									$status = 'translated';
									$default = $sections['keys'][$key];
									$field->addAttribute('default', $default);
								}
							}
							elseif (!isset($sections['keys'][$key]))
							{
								$status = "untranslated";
								$default = $string;
								$field->addAttribute('default', $default);
							}
							elseif ($sections['keys'][$key] == $refsections['keys'][$key])
							{
								$status = "untranslated";
								$default = $string;
								$field->addAttribute('default', $default);
							}
						}
						else
						{
							$status  = 'sourcestrings';
							$default = $string;
							$field->addAttribute('default', $default);
						}

						$label      = '<b>' . $key . '</b><br />' . htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
						$field->addAttribute('status', $status);
						$field->addAttribute('textchange', '');
						$field->addAttribute('description', $string);
						$field->addAttribute('label', $label);
						$field->addAttribute('name', $key);
						$field->addAttribute('istranslation', $istranslation);
						$field->addAttribute('istextchange', 0);
						$field->addAttribute('isextraindev', 0);
						$field->addAttribute('type', 'key');
						$field->addAttribute('filter', 'raw');
						continue;
					}
					elseif (!preg_match('/^(|(\[[^\]]*\])|([A-Z][A-Z0-9_\-\.]*\s*=(\s*(("[^"]*")|(_QQ_)))+))\s*(;.*)?$/', $line))
					{
						$this->item->error[] = $lineNumber;
					}
				}

				$stream->close();
				$newstrings      = false;
				$todeletestrings = false;
				$keystodelete    = array();

				if (!empty($sections['keys']))
				{
					foreach ($sections['keys'] as $key => $string)
					{
						$full_line = $key . '="' . $string . '"';

						if (!isset($refsections['keys'][$key]))
						{
							if (in_array($key, $keystokeep))
							{
								if (!$newstrings)
								{
									$newstrings = true;
									$form->load($addform, false);
									$section = 'Keys to keep as extra';
									$addform = new SimpleXMLElement('<form />');
									$group   = $addform->addChild('fields');
									$group->addAttribute('name', 'strings');
									$fieldset = $group->addChild('fieldset');
									$fieldset->addAttribute('name', $section);
									$fieldset->addAttribute('label', $section);
								}

								if (in_array($full_line, $blockedstrings))
								{
									$status = 'blocked';
								}
								elseif (in_array($full_line, $untranslatablestrings))
								{
									$status = "untranslatable";
								}
								else
								{
									$status  = 'extra';
								}

								$field   = $fieldset->addChild('field');
								$default = $string;
								$label   = '<b>' . $key . '</b>';
								$field->addAttribute('istranslation', $istranslation);
								$field->addAttribute('istextchange', 0);
								$field->addAttribute('isextraindev', 0);
								$field->addAttribute('status', $status);
								$field->addAttribute('textchange', '');
								$field->addAttribute('description', $string);
								$field->addAttribute('default', $default);
								$field->addAttribute('label', $label);
								$field->addAttribute('name', $key);
								$field->addAttribute('type', 'key');
								$field->addAttribute('filter', 'raw');
							}
							else
							{
								$keystodelete[$key] = $string;
							}
						}
					}

					if (!empty($keystodelete))
					{
						foreach ($keystodelete as $key => $string)
						{
						$full_line = $key . '="' . $string . '"';

							if (!$todeletestrings)
							{
								$todeletestrings = true;
								$form->load($addform, false);
								$section = 'Keys to delete';
								$addform = new SimpleXMLElement('<form />');
								$group   = $addform->addChild('fields');
								$group->addAttribute('name', 'strings');
								$fieldset = $group->addChild('fieldset');
								$fieldset->addAttribute('name', $section);
								$fieldset->addAttribute('label', $section);
							}

							if (in_array($full_line, $blockedstrings))
							{
								$status = 'blocked';
							}
							elseif (in_array($full_line, $untranslatablestrings))
							{
								$status = "untranslatable";
							}
							else
							{
								$status  = 'keytodelete';
							}

							$field   = $fieldset->addChild('field');
							$default = $string;
							$label   = '<b>' . $key . '</b>';
							$field->addAttribute('istranslation', $istranslation);
							$field->addAttribute('istextchange', 0);
							$field->addAttribute('isextraindev', 0);
							$field->addAttribute('status', $status);
							$field->addAttribute('textchange', '');
							$field->addAttribute('description', $string);
							$field->addAttribute('default', $default);
							$field->addAttribute('label', $label);
							$field->addAttribute('name', $key);
							$field->addAttribute('type', 'key');
							$field->addAttribute('filter', 'raw');
						}
					}

					if (!empty($sectionsindev))
					{
						$i = 0;

						foreach ($sectionsindev as $sectionname => $sectionindevdata)
						{
							if (!isset($sectionindevdata['extra_keys_in_dev']) && !isset($sectionindevdata['text_changes']))
							{
								continue;
							}

							$ghparts = explode('|', $sectionname);
							$gh_user = $ghparts[0];
							$gh_project = $ghparts[1];
							$gh_trunk = $ghparts[2];

							$section_title = 'DEVELOP | PROJECT: ' . strtoupper($gh_project)
									. ' - USER: ' . strtoupper($gh_user)
									. ' - TRUNK: ' . strtoupper($gh_trunk);

							$dev_name = $gh_project . 'LOCSEP' . $gh_user . 'LOCSEP' . $gh_trunk;
							$short_name = $gh_user . '|' . $gh_project . '|' . $gh_trunk;

							$devname = "develop";
							$devname .= $i++;

							$form->load($addform, false);

							$addform = new SimpleXMLElement('<form />');
							$group   = $addform->addChild('fields');
							$group->addAttribute('name', 'stringsindev');
							$fieldset = $group->addChild('fieldset');
							$fieldset->addAttribute('name', $sectionname);
							$fieldset->addAttribute('label', $section_title);

							if (isset($sectionindevdata['extra_keys_in_dev']))
							{
								$extrasindev = $sectionindevdata['extra_keys_in_dev'];

								foreach ($extrasindev as $key => $string)
								{
								$field   = $fieldset->addChild('field');
								$default = $string;
								$status  = "untranslated";

									if (isset($stringsintasks[$short_name][$key]))
									{
										if ($string != $stringsintasks[$short_name][$key])
										{
											$default = $stringsintasks[$short_name][$key];
											$status  = "translated";
										}
									}

								$label   = '<b>' . $key . '</b><br />' . $string;
								$field->addAttribute('istranslation', $istranslation);
								$field->addAttribute('istextchange', 0);
								$field->addAttribute('isextraindev', 1);

									if ($istranslation == 1)
									{
										$field->addAttribute('status', $status);
									}
									else
									{
										$field->addAttribute('status', 'sourcestrings');
									}

								$field->addAttribute('textchange', '');
								$field->addAttribute('description', $string);
								$field->addAttribute('default', $default);
								$field->addAttribute('label', $label);
								$field->addAttribute('name', $key);
								$field->addAttribute('key', $key);
								$field->addAttribute('devname', $dev_name);
								$field->addAttribute('type', 'key');
								$field->addAttribute('filter', 'raw');
								}
							}

							if (isset($sectionindevdata['text_changes']))
							{
								$changesindev = $sectionindevdata['text_changes'];
								$tc_refs = $textchangesref[$sectionname];
								$tc_defaults = $textchangesdefault[$sectionname];

								foreach ($changesindev as $key => $change)
								{
								$field        = $fieldset->addChild('field');
								$default      = $tc_defaults[$key]['default'];
								$status       = $tc_defaults[$key]['status'];
								$frozen_task  = $tc_defaults[$key]['frozen_task'];
								$description  = $tc_refs[$key];
								$label   = '<b>' . $key
									. '</b><br /><p class="text_changes">'
									. $change . '</p>';
								$field->addAttribute('istranslation', $istranslation);
								$field->addAttribute('istextchange', 1);
								$field->addAttribute('isextraindev', 0);

									if ($istranslation == 1)
									{
										$field->addAttribute('status', $status);
									}
									else
									{
										$field->addAttribute('status', 'sourcestrings');
									}

								$field->addAttribute('frozen_task', $frozen_task);
								$field->addAttribute('textchange', $change);
								$field->addAttribute('description', $description);
								$field->addAttribute('default', $default);
								$field->addAttribute('label', $label);
								$field->addAttribute('name', $key);
								$field->addAttribute('key', $key);
								$field->addAttribute('devname', $dev_name);
								$field->addAttribute('type', 'key');
								$field->addAttribute('filter', 'raw');
								}
							}
						}
					}
				}
			}

			$form->load($addform, false);
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_localise.edit.translation.data', array());

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		if ($origin != '_thirdparty' && $origin != '_override')
		{
			$packages = LocaliseHelper::getPackages();
			$package  = $packages[$origin];

			if (!empty($package->author))
			{
				$form->setValue('author', $package->author);
				$form->setFieldAttribute('author', 'readonly', 'true');
			}

			if (!empty($package->copyright))
			{
				$form->setValue('maincopyright', $package->copyright);
				$form->setFieldAttribute('maincopyright', 'readonly', 'true');
			}

			if (!empty($package->license))
			{
				$form->setValue('license', $package->license);
				$form->setFieldAttribute('license', 'readonly', 'true');
			}
		}

		if ($form->getValue('description') == '' && array_key_exists($tag, $languages[$client]))
		{
			$form->setValue('description', $filename . ' ' . $languages[$client][$tag]['name']);
		}

		return $form;
	}

	/**
	 * Save a file
	 *
	 * @param   array  $data  Array that represents a file
	 *
	 * @return bool
	 */
	public function saveFile($data)
	{
		$path       = $this->getState('translation.path');
		$refpath    = $this->getState('translation.refpath');
		$exists     = JFile::exists($path);
		$refexists  = JFile::exists($refpath);
		$client     = $this->getState('translation.client');
		$keystokeep = (array) $this->getState('translation.keystokeep');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		// Try to make the file writeable.
		if ($exists && !$ftp['enabled'] && JPath::isOwner($path) && !JPath::setPermissions($path, '0644'))
		{
			$this->setError(JText::sprintf('COM_LOCALISE_ERROR_TRANSLATION_WRITABLE', $path));

			return false;
		}

		if (array_key_exists('source', $data))
		{
			$contents = $data['source'];
		}
		else
		{
			$data['description']  = str_replace(array("\r\n", "\n", "\r"), " ", $data['description']);
			$additionalcopyrights = trim($data['additionalcopyright']);

			if (empty($additionalcopyrights))
			{
				$additionalcopyrights = array();
			}
			else
			{
				$additionalcopyrights = explode("\n", $additionalcopyrights);
			}

			$contents2 = '';

			if (!empty($data['svn']))
			{
				$contents2 .= "; " . $data['svn'] . "\n;\n";
			}

			if (!empty($data['package']))
			{
				$contents2 .= "; @package     " . $data['package'] . "\n";
			}

			if (!empty($data['subpackage']))
			{
				$contents2 .= "; @subpackage  " . $data['subpackage'] . "\n";
			}

			if (!empty($data['description']) && $data['description'] != '[Description] [Name of language]([Country code])')
			{
				$contents2 .= "; @description " . $data['description'] . "\n";
			}

			if (!empty($data['version']))
			{
				$contents2 .= "; @version     " . $data['version'] . "\n";
			}

			if (!empty($data['creationdate']))
			{
				$contents2 .= "; @date        " . $data['creationdate'] . "\n";
			}

			if (!empty($data['author']))
			{
				$contents2 .= "; @author      " . $data['author'] . "\n";
			}

			if (!empty($data['maincopyright']))
			{
				$contents2 .= "; @copyright   " . $data['maincopyright'] . "\n";
			}

			foreach ($additionalcopyrights as $copyright)
			{
				$contents2 .= "; @copyright   " . $copyright . "\n";
			}

			if (!empty($data['license']))
			{
				$contents2 .= "; @license     " . $data['license'] . "\n";
			}

			$contents2 .= "; @note        Client " . ucfirst($client) . "\n";
			$contents2 .= "; @note        All ini files need to be saved as UTF-8\n\n";

			$contents = array();
			$stream   = new JStream;

			if ($exists)
			{
				$stream->open($path);

				while (!$stream->eof())
				{
					$line = $stream->gets();

					// Comment lines
					if (preg_match('/^(;.*)$/', $line, $matches))
					{
						// $contents[] = $matches[1]."\n";
					}
					else
					{
						break;
					}
				}

				if ($refexists)
				{
					$stream->close();
					$stream->open($refpath);

					while (!$stream->eof())
					{
						$line = $stream->gets();

						// Comment lines
						if (!preg_match('/^(;.*)$/', $line, $matches))
						{
							break;
						}
					}
				}
			}
			else
			{
				$stream->open($refpath);

				while (!$stream->eof())
				{
					$line = $stream->gets();

					// Comment lines
					if (preg_match('/^(;.*)$/', $line, $matches))
					{
						$contents[] = $matches[1] . "\n";
					}
					else
					{
						break;
					}
				}
			}

			$strings = $data['strings'];

			while (!$stream->eof())
			{
				if (preg_match('/^([A-Z][A-Z0-9_\-\.]*)\s*=/', $line, $matches))
				{
					$key = $matches[1];

					if (isset($strings[$key]))
					{
						$contents[] = $key . '="' . str_replace('"', '"_QQ_"', $strings[$key]) . "\"\n";
						unset($strings[$key]);
					}
				}
				else
				{
					$contents[] = $line;
				}

				$line = $stream->gets();
			}

			if (!empty($strings))
			{
				$contents_to_add = array();
				$contents_to_delete = array();

				foreach ($strings as $key => $string)
				{
					if (in_array($key, $keystokeep))
					{
						$contents_to_add[] = $key . '="' . str_replace('"', '"_QQ_"', $string) . "\"\n";
					}
					else
					{
						$contents_to_delete[] = $key . '="' . str_replace('"', '"_QQ_"', $string) . "\"\n";
					}
				}
			}

			$stream->close();
			$contents = implode($contents);
			$contents = $contents2 . $contents;

			if (!empty($contents_to_add))
			{
				$contents .= "\n[Keys to keep in target]\n\n";
				$contents .= ";The next keys are not present in en-GB language but are used as extra in this language";
				$contents .= "(extra plural cases, custom CAPTCHA translations, etc).\n\n";
				$contents_to_add = implode($contents_to_add);
				$contents .= $contents_to_add;
			}

			if (!empty($contents_to_delete))
			{
				$contents .= "\n[Keys to delete]\n\n";
				$contents .= ";This keys are not used in en-GB language and are not required in this language.\n\n";
				$contents_to_delete = implode($contents_to_delete);
				$contents .= $contents_to_delete;
			}
		}

		$return = JFile::write($path, $contents);

		if (!empty($data['stringsindev']))
		{
			$frozenref  = LocaliseHelper::parseSections($refpath);
			$frozenref = $frozenref['keys'];

			$frozentask = LocaliseHelper::parseSections($path);
			$frozentask = $frozentask['keys'];

			foreach ($data['stringsindev'] as $short_name => $stringstosave)
			{
				$taskpath = $data[$short_name]['taskpathindev'];
				$refindev = $data[$short_name]['refpathindev'];

				$contentsindev = array();

				if (JFile::exists($taskpath))
				{
					$storedtaskindev = LocaliseHelper::parseSections($taskpath);
					$storedtaskindev = $storedtaskindev['keys'];

					$refindev = LocaliseHelper::parseSections($refindev);
					$refindev = $refindev['keys'];

					foreach ($stringstosave as $keytosave => $stringtosave)
					{
						$stringinref = '';
						$frozenstring = '';
						$stringindev = '';

						if (isset($frozenref[$keytosave]))
						{
							$stringinref = $frozenref[$keytosave];

							// Unset the task if equal than ref.
							if (	isset($storedtaskindev[$keytosave])
								&& $storedtaskindev[$keytosave] == $stringinref)
							{
								unset($storedtaskindev[$keytosave]);
							}
						}

						if (isset($frozentask[$keytosave]))
						{
							$frozenstring = $frozentask[$keytosave];

							// Unset the task if equal than frozen.
							// This can help when new language pack after a new joomla release have included the task in dev.
							if (	isset($storedtaskindev[$keytosave])
								&& $storedtaskindev[$keytosave] == $frozenstring)
							{
								unset($storedtaskindev[$keytosave]);
							}
						}

						if (isset($refindev[$keytosave]))
						{
							$stringindev = $refindev[$keytosave];

							// Unset the task if equal than in dev.
							if (isset($storedtaskindev[$keytosave]) && $storedtaskindev[$keytosave] == $stringindev)
							{
								unset($storedtaskindev[$keytosave]);
							}
						}

						// Only the real changes in task are saved as task.
						// This will help to keep the stored values alrready translared for other users.
						if (	$stringtosave != $stringinref
							&& $stringtosave != $frozenstring
							&& $stringtosave != $stringindev)
						{
							if (!isset($storedtaskindev[$keytosave]))
							{
							$storedtaskindev[] = $keytosave;
							}

							$storedtaskindev[$keytosave] = $stringtosave;
						}
					}

					if (!empty($storedtaskindev))
					{
						foreach ($storedtaskindev as $keyindev => $stringindev)
						{
						$contentsindev[] = $keyindev . '="' . str_replace('"', '"_QQ_"', $stringindev) . "\"\n";
						}

						$contentsindev = implode($contentsindev);
						JFile::write($taskpath, $contentsindev);
					}
				}
			}
		}

		// Try to make the template file unwriteable.

		// Get the parameters
		$coparams = JComponentHelper::getParams('com_localise');

		// Get the file save permission
		$fsper = $coparams->get('filesavepermission', '0644');

		if (!$ftp['enabled'] && JPath::isOwner($path) && !JPath::setPermissions($path, $fsper))
		{
			$this->setError(JText::sprintf('COM_LOCALISE_ERROR_TRANSLATION_UNWRITABLE', $path));

			return false;
		}
		else
		{
			if (!$return)
			{
				$this->setError(JText::sprintf('COM_LOCALISE_ERROR_TRANSLATION_FILESAVE', $path));

				return false;
			}
		}

		// Remove the cache
		$conf    = JFactory::getConfig();
		$caching = $conf->get('caching') >= 1;

		if ($caching)
		{
			$keycache = $this->getState('translation.client') . '.'
				. $this->getState('translation.tag') . '.'
				. $this->getState('translation.filename') . '.' . 'translation';
			$cache    = JFactory::getCache('com_localise', '');
			$cache->remove($keycache);
		}
	}

	/**
	 * Saves a translation
	 *
	 * @param   array  $data  translation to be saved
	 *
	 * @return bool
	 */
	public function save($data)
	{
		// Fix DOT saving issue
		$input    = JFactory::getApplication()->input;
		$formData = $input->get('jform', array(), 'ARRAY');
		$tag = $this->getState('translation.tag');
		$client = $this->getState('translation.client');
		$fileindev    = basename($this->getState('translation.refpath'));
		$fileintask = basename($this->getState('translation.path'));

		if (!empty($formData['strings']))
		{
			$data['strings'] = $formData['strings'];
		}

		if (!empty($formData['stringsindev']))
		{
			$data['stringsindev'] = $formData['stringsindev'];
		}

		foreach ($data['stringsindev'] as $short_name => $stringsindev)
		{
			$ghparts = explode('LOCSEP', $short_name);
			$gh_user = $ghparts[1];
			$gh_project = $ghparts[0];
			$gh_trunk = $ghparts[2];
			$taskpath = LocaliseHelper::getTaskfilepath($client, $tag, $gh_user, $gh_project, $gh_trunk, $fileintask);
			$refpathindev = LocaliseHelper::getReffilepathindev($client, $gh_user, $gh_project, $gh_trunk, $fileindev);

			$data[$short_name]['taskpathindev'] = $taskpath;
			$data[$short_name]['refpathindev'] = $refpathindev;
		}

		// Special case for lib_joomla
		if ($this->getState('translation.filename') == 'lib_joomla')
		{
			if (JFolder::exists(JPATH_SITE . "/language/$tag"))
			{
				$this->setState('translation.client', 'site');
				$this->setState('translation.path', JPATH_SITE . "/language/$tag/$tag.lib_joomla.ini");
				$this->saveFile($data);
			}

			if (JFolder::exists(JPATH_ADMINISTRATOR . "/language/$tag"))
			{
				$this->setState('translation.client', 'administrator');
				$this->setState('translation.path', JPATH_ADMINISTRATOR . "/language/$tag/$tag.lib_joomla.ini");
				$this->saveFile($data);
			}
		}
		else
		{
			$this->saveFile($data);
		}

		// Bind the rules.
		$table = $this->getTable();
		$table->load($data['id']);

		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);
			$table->setRules($rules);
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
