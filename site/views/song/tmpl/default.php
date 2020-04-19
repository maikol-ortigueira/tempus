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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_tempus');

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_tempus'))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

	</table>

</div>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_tempus&task=song.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_TEMPUS_EDIT_ITEM"); ?></a>

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