<?php
/**
 * @package     Com_Localise
 * @subpackage  views
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$dev_files_report =  $this->devfilesreport;
$stared_tabs = array();
$created_tabs = array();
$i = 0;

	if(!empty($dev_files_report))
	{
		echo "* reporting changes in dev for the selected files.";

		foreach ($dev_files_report as $gh_project => $project_data)
		{
			$started_tab = $gh_project;
			$started_1 = 0;

			if(!in_array($started_tab, $stared_tabs))
			{
				$stared_tabs[] = $started_tab;
				$started_1 = 1;

				echo JHtml::_('bootstrap.startAccordion', 'myTab'.$started_tab);

			}

			$created_tab = $gh_project;
			$created_1 = 0;

			if(!in_array($created_tab, $created_tabs))
			{
				$created_tabs[] = $created_tab;
				$created_1 = 1;

				echo JHtml::_('bootstrap.addSlide', 'myTab'.$created_tab, strtoupper($created_tab), 'collapse' . $i++);
			}		

			foreach ($project_data as $gh_trunk => $trunk_data)
			{
				foreach ($trunk_data as $gh_user => $user_data)
				{
				$started_tab = $gh_project.$gh_user.$gh_trunk;
				$started_2 = 0;

					if(!in_array($started_tab, $stared_tabs))
					{
						$started_2 = 1;
						$stared_tabs[] = $started_tab;
						echo JHtml::_('bootstrap.startAccordion', 'myTab'.$started_tab);
					}

				$tab_name = 'PROJECT: ' . strtoupper($gh_project)
					. ' - USER: ' . strtoupper($gh_user)
					. ' - TRUNK: ' . strtoupper($gh_trunk);

				$created_tab = $gh_project.$gh_user.$gh_trunk;
				$created_2 = 0;

					if(!in_array($created_tab, $created_tabs))
					{
						$created_tabs[] = $created_tab;
						$created_2 = 1;

						echo JHtml::_('bootstrap.addSlide', 'myTab'.$created_tab, strtoupper($tab_name), 'collapse' . $i++);
					}

					foreach ($user_data as $gh_filename => $filename_data)
					{
					echo '<p class="new_file">' . $gh_filename . ' ' . $filename_data['link'] . '</p>';

						if (isset($filename_data['extra_keys_in_dev']))
						{
						echo '<p class="keys_to_add">Detailing extra keys in dev</p>';

							foreach ($filename_data['extra_keys_in_dev'] as $key => $extra_keys_in_dev)
							{
								echo '<p class="key_to_add">' . $key . '="'. $extra_keys_in_dev .'"</p>';
							}

						}

						if (isset($filename_data['text_changes']))
						{
						echo '<p class="keys_to_revise">Detailing text changes</p>';

							foreach ($filename_data['text_changes'] as $key => $textchange)
							{
								echo '<p class="key_to_revise">' . $key . '</p><p class="text_changes_report">' . $textchange . '</p>';
							}
						}
					}

					if ($created_2 == 1)
					{
						echo JHtml::_('bootstrap.endSlide');
						$created_2 = 0;
					}

					if ($started_2 == 1)
					{
						echo JHtml::_('bootstrap.endAccordion');
						$started_2 = 0;
					}
				}
			}

			if ($created_1 == 1)
			{
				echo JHtml::_('bootstrap.endSlide');
				$created_1 = 0;
			}

			if ($started_1 == 1)
			{
				echo JHtml::_('bootstrap.endAccordion');
				$started_1 = 0;
			}
		}
	}
	else
	{
		echo "No matches in dev to show within the selected files.";
	}
?>
