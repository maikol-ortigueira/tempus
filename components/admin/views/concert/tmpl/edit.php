<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'media/com_tempus/css/form.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});
	Joomla.submitbutton = function (task) {
		if (task == 'concert.cancel') {
			Joomla.submitform(task, document.getElementById('concert-form'));
		}
		else {
			if (task != 'concert.cancel' && document.formvalidator.isValid(document.id('concert-form'))) {
				Joomla.submitform(task, document.getElementById('concert-form'));
			}
			else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<?php
	// In case of modal
	$input = Factory::getApplication()->input;
	$isModal = $input->get('layout') == 'modal' ? true : false;
	$layout  = $isModal ? 'modal' : 'edit';
	$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form
	action="<?php echo Route::_('index.php?option=com_tempus&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="concert-form" class="form-validate form-horizontal">


	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<!-- Control de versiones -->
	<?php if ($this->state->params->get('save_history', 1)) : ?>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
		</div>
	<?php endif; ?>
	<!-- fin del campo de control de versiones -->
	<!-- Inicio de pestañas -->
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
		<!-- Primera pestaña -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_TEMPUS_TAB_DETAILS', true)); ?>
			<div class="row-fluid">
				<div class="span5">
					<fieldset class="adminform">
						<?php echo $this->form->renderField('concert_date'); ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('start_hour'); ?>
							</div>
							<div class="controls controls-row">
								<?php echo $this->form->getInput('start_hour'); ?>
								<?php echo $this->form->getInput('start_minute'); ?>
								<?php echo $this->form->getInput('start_ampm'); ?>
							</div>
						</div>
						<?php echo $this->form->renderField('songs_id'); ?>
						<?php echo $this->form->renderField('extended_note'); ?>
					</fieldset>
				</div>
				<div class="span4">
					<div id="rehearsals">
						<?php echo $this->form->getInput('rehearsal_id'); ?>
					</div>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<!-- Fin de primera pestaña -->
		<!-- Segunda pestaña -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'location', Text::_('COM_TEMPUS_TAB_LOCATION', true)); ?>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="adminform">
						<?php echo $this->form->renderFieldset('location'); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<!-- Fin de segunda pestaña -->
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	<!-- Cierre de pestañas -->
	<input type="hidden" name="task" value=""/>
	<input type="hidden" id="thisId" value="<?php echo $this->item->id; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
(function() {
	//'use strict';+
	debbuger;
	var id = jQuery('#thisId').val();

	if (id === '') {
		jQuery('#rehearsals').hide();
	}
})();
</script>
