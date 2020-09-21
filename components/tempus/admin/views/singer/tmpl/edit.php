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
// Get the fieldsets
$fieldsets = $this->form->getFieldsets();

// Get the member's photo
$photo = $this->getForm()->getValue('photo', 'profile');
$alias = $this->getForm()->getValue('alias', 'tempus');

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

?>

<form
	action="<?php echo Route::_('index.php?option=com_tempus&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="singer-form" class="form-validate form-horizontal">
	<div class="">
		<div class="row-fluid">
			<div class="span6">
				<h2>
					<?php if ($alias !== '' && $alias !== null) : ?>
						<?php echo $alias ;?>
					<?php else: ?>
						<?php echo $this->getForm()->getValue('name'); ?>
					<?php endif; ?>
				</h2>
				<h4><?php echo $this->getForm()->getValue('email'); ?></h4>
			</div>
			<div class="span6">
				<?php if ($photo !== '' && $photo !== null) : ?>
					<img class="singer-photo admin-photo" src="<?php echo $photo; ?>" alt="<?php $this->getForm()->getValue('name'); ?>">
				<?php endif; ?>
			</div>
		</div>
		
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'profile')); ?><!-- Start tab set -->
		<?php if (isset($fieldsets['profile'])) : ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'profile', Text::_('Datos de Perfil', true)); ?><!-- Start basic tab -->
			<div class="row-fluid">
				<div class="span6 form-horizontal">
					<?php echo $this->form->renderFieldset('tempus'); ?>
				</div>
				<div class="span6 form-horizontal">
					<?php echo $this->form->renderFieldset('profile'); ?>
				</div>
			</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?><!-- End of basic tab -->
			<?php endif; ?>
			
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?><!--End of tab set -->
			
			<input type="hidden" name="task" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>

</form>
<script type="text/javascript">

	// function setValues(userId) {
	// 	var profiles = getProfileValues(userId);
	// 	console.log(JSON.parse(profiles));
	// }
</script>