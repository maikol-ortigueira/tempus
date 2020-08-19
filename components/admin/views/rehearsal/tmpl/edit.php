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
		if (task == 'rehearsal.cancel') {
			Joomla.submitform(task, document.getElementById('rehearsal-form'));
		}
		else {
			if (task != 'rehearsal.cancel' && document.formvalidator.isValid(document.id('rehearsal-form'))) {
				Joomla.submitform(task, document.getElementById('rehearsal-form'));
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
	method="post" enctype="multipart/form-data" name="adminForm" id="rehearsal-form" class="form-validate form-vertical">


	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<!-- Inicio de pestañas -->
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
		<!-- Primera pestaña -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_TEMPUS_TAB_DETAILS', true)); ?>
			<div class="row-fluid">
				<div class="span5">
					<fieldset class="adminform">
						<?php echo $this->form->renderField('rehearsal_date'); ?>
						<div class="controls control-group controls-row">
							<?php echo $this->form->getLabel('start_hour'); ?>
							<?php echo $this->form->getInput('start_hour'); ?>
							<?php echo $this->form->getInput('start_minute'); ?>
							<?php echo $this->form->getInput('start_ampm'); ?>
						</div>
						<div class="controls control-group controls-row">
							<?php echo $this->form->getLabel('end_hour'); ?>
							<?php echo $this->form->getInput('end_hour'); ?>
							<?php echo $this->form->getInput('end_minute'); ?>
							<?php echo $this->form->getInput('end_ampm'); ?>
						</div>
					</fieldset>
				</div>
				<div class="span4">
					<fieldset class="adminform">
						<?php echo $this->form->renderField('concert_id'); ?>
						<?php echo $this->form->renderField('songs_id'); ?>
						<?php echo $this->form->renderField('extended_note'); ?>
					</fieldset>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<!-- Fin de primera pestaña -->
		<!-- Segunda pestaña -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'convocation', Text::_('COM_TEMPUS_TAB_CONVOCATION', true)); ?>
			<div class="row-fluid">
				<div class="span12">
						<legend><?php echo Text::_('COM_TEMPUS_TAB_CONVOCATION'); ?></legend>
						<div class="row-fluid">
						<?php
							$voices = TempusHelper::getVoices();

							foreach ($voices as $voice) :
								?>
								<div class="span3">
									<legend><?php echo Text::_('COM_TEMPUS_VOICES_PLURAL_' . $voice); ?></legend>
									<div class="well">
										<div style="margin-bottom:10px;">
											<?php echo $this->form->getInput($voice.'_ids', 'convocation'); ?>
										</div>
										<div>
											<?php echo $this->form->getInput($voice.'_button'); ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
				</div>
			</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<!-- Fin de segunda pestaña -->
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	<!-- Cierre de pestañas -->
	<input type="hidden" name="return" value="<?php echo $this->return; ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
<script>

function selectAll(params) {
	params = params.split(/[_]/);
	var mySelectionBox = params[0]+'_convocation_'+params[1]+'_ids';
	console.log(mySelectionBox);
	// Select All
	jQuery('#'+mySelectionBox+' option').prop('selected', true).trigger('liszt:updated');
}

</script>
