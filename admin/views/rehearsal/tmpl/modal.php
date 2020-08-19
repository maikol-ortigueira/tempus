<?php
/**
 * @version    1.0.0
 * @package    com_tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use \Joomla\CMS\Factory;
 use \Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function  = Factory::getApplication()->input->getCmd('function', 'jEditRehearsal_' . (int) $this->item->id);

// Function to update input title when changed
Factory::getDocument()->addScriptDeclaration('
	function jEditRehearsalModal() {
		if (window.parent && document.formvalidator.isValid(document.getElementById("item-form"))) {
			return window.parent.' . $this->escape($function) . '(document.getElementById("jform_title").value);
		}
	}
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('rehearsal.apply'); jEditRehearsalModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('rehearsal.save'); jEditRehearsalModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('rehearsal.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
