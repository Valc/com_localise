<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_localise
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication('administrator');
$params = JComponentHelper::getParams('com_localise');
$reference = $params->get('reference', 'en-GB');
$packages = LocaliseHelper::getPackages();
$files_in_dev = LocaliseHelper::scanFilesindev();
$user = JFactory::getUser();
$userId = $user->get('id');
$allowed_groups = (array) $params->get('allowed_groups', null);
$user_groups = $user->get('groups');
$have_raw_mode = 1;

	if (!empty($allowed_groups) && !empty($user_groups))
	{
		if (!array_intersect($allowed_groups, $user_groups))
		{
		$have_raw_mode = 0;
		}
	}

$max_vars = ini_get('max_input_vars');

$lang = JFactory::getLanguage();
$dev_files_report = array();
?>
<?php foreach ($this->items as $i => $item) : ?>
<?php

	$textchangesindevs = $item->textchangesindev;
	$extrakeysindevs = $item->extrakeysindev;
	$revisedtextchanges = $item->revisedtextchanges;
	$revisedextrasindev = $item->revisedextrasindev;
	$canEdit = $user->authorise('localise.edit', 'com_localise' . (isset($item->id) ? ('.' . $item->id) : ''));
	$limit = 0;

	if ($max_vars > 0 && $item->total > $max_vars)
	{
		$limit = 1;
	}

	if (!empty($textchangesindevs))
	{
		$gh_filename = "$reference" . "." . $item->filename . ".ini";

		foreach ($textchangesindevs as $target_dev => $textchangesindev)
		{
		$ghparts = explode('|', $target_dev);
		$gh_user = $ghparts[0];
		$gh_project = $ghparts[1];
		$gh_trunk = $ghparts[2];

			if ($item->writable && !$item->error && $canEdit)
			{
				if ($limit == 0)
				{
					$dev_files_report[$gh_project][$gh_trunk][$gh_user][$gh_filename]['link'] =
						'<a class="hasTooltip" href="' . JRoute::_('index.php?option=com_localise&task=translation.edit&client=' . $item->client . '&tag=' . $item->tag . '&filename=' . $item->filename . '&storage=' . $item->storage . '&id=' . LocaliseHelper::getFileId(LocaliseHelper::getTranslationPath($item->client, $item->tag,  $item->filename, $item->storage))) . '" title="'. JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_' . ($item->state=='unexisting' ? 'NEW' : 'EDIT')) . '">[Revise it]</a>';
				}
				else
				{
					$dev_files_report[$gh_project][$gh_trunk][$gh_user][$gh_filename]['link'] = '';
				}
			}

			foreach ($textchangesindev as $key => $textchange)
			{
				$dev_files_report[$gh_project][$gh_trunk][$gh_user][$gh_filename]['text_changes'][$key] = $textchange;
			}
		}
	}

	if (!empty($extrakeysindevs))
	{
		$gh_filename = "$reference" . "." . $item->filename . ".ini";

		foreach ($extrakeysindevs as $target_dev => $extrakeysindev)
		{
		$ghparts = explode('|', $target_dev);
		$gh_user = $ghparts[0];
		$gh_project = $ghparts[1];
		$gh_trunk = $ghparts[2];

			foreach ($extrakeysindev as $key => $extrakey)
			{
				$dev_files_report[$gh_project][$gh_trunk][$gh_user][$gh_filename]['extra_keys_in_dev'][$key] = $extrakey;
			}
		}
	}
?>
	<?php $canEdit = $user->authorise('localise.edit', 'com_localise' . (isset($item->id) ? ('.' . $item->id) : '')); ?>
	<tr class="<?php echo $item->state; ?> row<?php echo $i % 2; ?>">
		<td width="20" class="center hidden-phone"><?php echo $i + 1; ?></td>
		<td width="120" class="center hidden-phone">
			<?php
			echo JHtml::_(
				'jgrid.action',
				$i,
				'',
				array(
					'tip'            => true,
					'inactive_title' => JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_STORAGE_' . $item->storage),
					'inactive_class' => '16-' . $item->storage,
					'enabled'        => false,
					'translate'      => false
				)
			); ?>
			<?php if ($item->origin == '_thirdparty') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip' => true, 'inactive_title' => JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_ORIGIN_THIRDPARTY'), 'inactive_class' => '16-thirdparty', 'enabled' => false, 'translate' => false)); ?>
			<?php elseif ($item->origin == '_override') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip' => true, 'inactive_title' => JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_ORIGIN_OVERRIDE'), 'inactive_class' => '16-override', 'enabled' => false, 'translate' => false)); ?>
			<?php else : ?>
				<?php if ($item->origin == 'core') : ?>
					<?php $icon = 'core'; ?>
				<?php else : ?>
					<?php $icon = 'other'; ?>
				<?php endif; ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip' => true, 'inactive_title' => JText::_($packages[$item->origin]->title) . '::' . JText::_($packages[$item->origin]->description), 'inactive_class' => '16-' . $icon, 'enabled' => false, 'translate' => false)); ?>
			<?php endif; ?>
			<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_STATE_'.$item->state, $item->translated, $item->total, $item->extra, $item->blocked, $item->untranslatable), 'inactive_class'=>'16-'.$item->state, 'enabled' => false, 'translate'=>false)); ?>
			<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_TYPE_'.$item->type), 'inactive_class'=>'16-'.$item->type, 'enabled' => false, 'translate'=>false)); ?>
			<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_CLIENT_'.$item->client), 'inactive_class'=>'16-'.$item->client, 'enabled' => false, 'translate'=>false)); ?>
			<?php if ($item->tag == $reference && $item->type != 'override') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_REFERENCE'), 'inactive_class'=>'16-reference', 'enabled' => false, 'translate'=>false)); ?>
			<?php endif; ?>
		</td>
		<td dir="ltr" class="center"><?php echo $item->tag; ?></td>
		<td dir="ltr" class="center"><?php echo $item->client ?></td>
		<td dir="ltr">
			<?php if ($item->checked_out) : ?>
				<?php $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0; ?>
				<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'translations.', $canCheckin); ?>
				<input type="checkbox" id="cb<?php echo $i; ?>" class="hidden" name="cid[]" value="<?php echo $item->id; ?>">
			<?php endif; ?>
			<?php if ($item->writable && !$item->error && $canEdit) : ?>
				<?php if ($limit == 0) : ?>
					<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_localise&task=translation.edit&client='.$item->client.'&tag='.$item->tag.'&filename='.$item->filename.'&storage='.$item->storage.'&id='.LocaliseHelper::getFileId(LocaliseHelper::getTranslationPath($item->client,$item->tag, $item->filename, $item->storage))); ?>" title="<?php echo JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_' . ($item->state=='unexisting' ? 'NEW' : 'EDIT')); ?>">
					<?php echo $item->name; ?>.ini
					</a>
				<?php else : ?>
					<?php echo "<font color=\"red\">" . $item->name . ".ini (Strings protection.)</font>"; ?>
					<?php $app->enqueueMessage("The PHP directve 'max_input_vars' value is minor than the required one to edit the file: " . $item->tag . "." . $item->name . ".ini", 'warning'); ?>
				<?php endif; ?>
			<?php elseif (!$canEdit) : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_NOTEDITABLE', substr($item->path, strlen(JPATH_ROOT))), 'inactive_class'=>'16-error', 'enabled' => false, 'translate'=>false)); ?>
				<?php echo $item->name; ?>.ini
			<?php elseif (!$item->writable) : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_NOTWRITABLE', substr($item->path, strlen(JPATH_ROOT))), 'inactive_class'=>'16-error', 'enabled' => false, 'translate'=>false)); ?>
				<?php echo $item->name; ?>.ini
			<?php elseif ($item->filename=='override') : ?>
				<?php echo $item->name; ?>.ini
			<?php else : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_ERROR', substr($item->path, strlen(JPATH_ROOT)) , implode(', ',$item->error)), 'inactive_class'=>'16-error', 'enabled' => false, 'translate'=>false)); ?>
				<?php echo $item->name; ?>.ini
			<?php endif; ?>
			<?php if ($item->writable && $canEdit && $have_raw_mode) : ?>
				(<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_localise&task=translation.edit&client=' . $item->client . '&tag=' . $item->tag . '&filename=' . $item->filename . '&storage=' . $item->storage . '&id=' . LocaliseHelper::getFileId(LocaliseHelper::getTranslationPath($item->client,$item->tag, $item->filename, $item->storage)) . '&layout=raw'); ?>" title="<?php echo JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_' . ($item->state=='unexisting' ? 'NEWRAW' : 'EDITRAW')); ?>"><?php echo JText::_('COM_LOCALISE_TEXT_TRANSLATIONS_SOURCE'); ?></a>)
			<?php else : ?>
				<?php echo substr($item->path,strlen(JPATH_ROOT)); ?>
			<?php endif; ?>
			<div class="small">
				<?php echo substr($item->path, strlen(JPATH_ROOT)); ?>
			</div>
			<?php if ($item->havedev) : ?>
			<div class="small">
				<?php echo "<b>Have dev</b>"; ?>
			</div>
			<?php endif; ?>
		</td>
		<td width="100" class="center" dir="ltr">
			<?php if ($item->bom != 'UTF-8') : ?>
				<a class="jgrid hasTooltip" href="http://en.wikipedia.org/wiki/UTF-8" title="<?php echo addslashes(htmlspecialchars(JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_UTF8'), ENT_COMPAT, 'UTF-8')); ?>">
				<span class="state icon-16-error"></span>
				<span class="text"></span>
				</a>
			<?php elseif ($item->state == 'error') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_ERROR',substr($item->path,strlen(JPATH_ROOT)) , implode(', ',$item->error)), 'inactive_class'=>'16-error', 'enabled' => false, 'translate'=>false)); ?>
			<?php elseif ($item->type == 'override') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_TYPE_OVERRIDE'), 'inactive_class'=>'16-override', 'enabled' => false, 'translate'=>false)); ?>
			<?php elseif ($item->state == 'notinreference') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_STATE_NOTINREFERENCE'), 'inactive_class'=>'16-notinreference', 'enabled' => false, 'translate'=>false)); ?>
			<?php elseif ($item->state == 'unexisting') : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_STATE_UNEXISTING', $item->translated, $item->total, $item->extra, $item->keytodelete, $item->blocked, $item->untranslatable), 'inactive_class'=>'16-unexisting', 'enabled' => false, 'translate'=>false)); ?>
			<?php elseif ($item->tag == $reference) : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_REFERENCE'), 'inactive_class'=>'16-reference', 'enabled' => false, 'translate'=>false)); ?>
			<?php elseif ($item->translated == $item->total) : ?>
				<?php echo JHtml::_('jgrid.action', $i, '', array('tip'=>true, 'inactive_title'=>JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_COMPLETE', $item->total, $item->extra, $item->keytodelete, $item->blocked, $item->untranslatable), 'inactive_class'=>'16-complete', 'enabled' => false, 'translate'=>false)); ?>
			<?php else : ?>
				<span class="hasTooltip" title="<?php echo $item->translated == 0 ? JText::_('COM_LOCALISE_TOOLTIP_TRANSLATIONS_NOTSTARTED') : JText::sprintf('COM_LOCALISE_TOOLTIP_TRANSLATIONS_INPROGRESS', $item->translated, $item->untranslated, $item->total, $item->extra, $item->keytodelete, $item->blocked, $item->untranslatable); ?>">
				<?php $translated =  $item->total ? intval(100 * $item->translated / $item->total) : 0; ?>
					<?php echo $translated; ?> %
					<div style="text-align:left;border:solid silver 1px;width:100px;height:4px;">
						<div class="pull-left" style="height:100%; width:<?php echo $translated; ?>% ;background:green;">
						</div>
						<div class="pull-left" style="height:100%; width:<?php echo 100-$translated; ?>% ;background:red;">
						</div>
					</div>
					<div class="clr"></div>
				</span>
			<?php endif; ?>
		</td>
		<td dir="ltr" class="center">
			<?php if ($item->state != 'error') : ?>
				<?php if ($item->state == 'notinreference') : ?>
					<?php echo $item->extra; ?>
				<?php elseif ($item->type == 'override') : ?>
				<?php
				elseif ($item->tag == $reference) : ?>
					<?php echo $item->sourcestrings
					. ($item->extraindev ? "<br /><b>Extra in dev: </b>" . $item->extraindev : '')
					. ($item->textchange ? "<br /><b>Text changes: </b>" . $item->textchange : ''); ?>
				<?php
				else : ?>
					<?php echo $item->translated . "/" . $item->total
					. ($item->extra ? "<br /><b>Extra keys: </b>" . $item->extra : '')
					. ($item->keytodelete ? "<br /><b>Keys to delete: </b>" . $item->keytodelete : '')
					. ($item->extraindev ? "<br /><b>Extra in dev:</b><br /> Revised " . $revisedextrasindev . " of " . $item->extraindev : '')
					. ($item->textchange ? "<br /><b>Text changes:</b><br /> Revised " . $revisedtextchanges . " of " . $item->textchange : ''); ?>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td class="hidden-phone">
			<?php if ($item->state != 'unexisting') : ?>
				<?php $description = ($item->maincopyright ? ($item->maincopyright . '<br/>') : '') . ($item->additionalcopyright ? (str_replace("\n", '<br/>', $item->additionalcopyright) . '<br/>') : '') . ($item->description ? ($item->description . '<br/>') : '') . ($item->version ? ($item->version . '<br/>') : '') . ($item->creationdate ? $item->creationdate : ''); ?>
				<?php if ($description || $item->author) : ?>
					<?php $author = $item->author ? $item->author : JText::_('COM_LOCALISE_TEXT_TRANSLATIONS_AUTHOR'); ?>
					<span class="hasTooltip" title="<?php echo htmlspecialchars($description, ENT_COMPAT, 'UTF-8'); ?>">
					<?php echo $author; ?>
				</span>
				<?php endif; ?>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
<?php $this->set('devfilesreport', $dev_files_report); ?>
