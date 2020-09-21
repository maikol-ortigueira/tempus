<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Language\Text;

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_tempus');
$this->item->catid = $this->item->catid !== '' ? TempusHelpersTempus::getField($this->item->catid, '#__categories', 'title')[0] : '';

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_tempus'))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_tempus/css/list.css');
?>

<div class="item-fields">
	<h1><?php echo $this->item->title; ?></h1>
	<div class="item_headers">
		<table class="table">
			<?php $fields = ['author','catid','song_note']; ?>
			<?php foreach ($fields as $field) : ?>
				<tr>
					<td style="width: 50%;">
						<strong><?php echo Text::_('COM_TEMPUS_SONG_'.$field.'_LBL'); ?></strong>
					</td>
					<td style="width: 50%; text-align:center;"><?php echo $this->item->{$field}; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<?php foreach ($this->item->documents as $key => $rows) : ?>
		<div <?php echo 'class="item_' . $key . 's"'; ?>>
			<h3><?php echo Text::_('COM_TEMPUS_SONG_TAB_' . $key . 'S_LBL'); ?></h2>
			<table class="table">
				<?php foreach ($rows as $data) : ?>
					<tr>
						<td style="width: 50%"><?php echo strtoupper(TempusHelpersTempus::getVoices(true)[$data['instrument']]) . $data['divisi']; ?></td>
						<td style="width: 50%; text-align:center;">
							<?php if ($key === 'sheet') : ?>
								<a href="<?php echo $data['fullpath']; ?>" download><span class="icon-file-2 x-icon icon-green"></span></a>
								<?php elseif ($key === 'audio'): ?>
								<audio controls="controls">
									<source src="<?php echo $data['fullpath']; ?>" type="audio/mpeg">
								</audio>
								<?php else: ?>
									<a href="<?php echo $data['video_doc']; ?>" target="_blank"><span class="icon-youtube xx-icon icon-youred"></span></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	<?php endforeach; ?>
</div>

<?php if($canEdit && $this->item->checked_out == 0): ?>
	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_tempus&task=song.edit&id='.$this->item->id); ?>">
		<?php echo JText::_("COM_TEMPUS_EDIT_ITEM"); ?>
	</a>
<?php endif; ?>

<?php if (JFactory::getUser()->authorise('core.delete','com_tempus.song.'.$this->item->id)) : ?>
	<a class="btn btn-danger" href="#deleteModal" role="button" data-toggle="modal">
		<?php echo JText::_("COM_TEMPUS_DELETE_ITEM"); ?>
	</a>
	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_TEMPUS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo JText::sprintf('COM_TEMPUS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo JRoute::_('index.php?option=com_tempus&task=song.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo JText::_('COM_TEMPUS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>
<?php endif; ?>