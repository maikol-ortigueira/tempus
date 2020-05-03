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

$input = Factory::getApplication()->input;

?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		if (task == 'singer.cancel') {
			Joomla.submitform(task, document.getElementById('singer-form'));
		}
		else {
			if (task != 'singer.cancel' && document.formvalidator.isValid(document.id('singer-form'))) {
				Joomla.submitform(task, document.getElementById('singer-form'));
			}
			else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<?php
// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form
	action="<?php echo JRoute::_('index.php?option=com_tempus&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="singer-form" class="form-validate form-horizontal">

	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?><!-- Start tab set -->
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_TEMPUS_TAB_DETAILS', true)); ?><!-- Start basic tab -->
	<div class="row-fluid">
		<div class="span9 form-horizontal">
			<?php echo $this->form->renderFieldset('details'); ?>
		</div>
		<div class="span3">
			<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?><!-- End of basic tab -->
	<?php echo JHtml::_('bootstrap.endTabSet'); ?><!--End of tab set -->

	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>

</form>
<script type="text/javascript">

	function setValues(userId) {
		var profiles = getProfileValues(userId);
		console.log(JSON.parse(profiles));
	}
</script>