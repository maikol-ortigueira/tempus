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

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
	});

	Joomla.submitbutton = function (task) {
		if (task == 'song.cancel') {
			Joomla.submitform(task, document.getElementById('song-form'));
		}
		else {
			if (task != 'song.cancel' && document.formvalidator.isValid(document.id('song-form'))) {
				Joomla.submitform(task, document.getElementById('song-form'));
			}
			else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_tempus&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="song-form" class="form-validate form-horizontal">

	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<!-- Inicio de pestañas -->
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
	<!-- Primera pestaña -->
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_TEMPUS_SONG_TAB_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php echo $this->form->renderField('author'); ?>
				<?php echo $this->form->renderField('song_note'); ?>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<!-- Fin de primera pestaña -->
	<!-- Segunda pestaña -->
	<?php $fieldSets=['sheets', 'audios', 'videos']; ?>
	<?php foreach($fieldSets as $fieldset) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset, JText::_('COM_TEMPUS_SONG_TAB_' . strtoupper($fieldset) . '_LBL', true)); ?>
		<div class="row-fluid">
			<div class="adminform form-horizontal">
				<?php echo $this->form->renderFieldset($fieldset); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<!-- Fin de segunda pestaña -->
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<!-- Cierre de pestañas -->
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
