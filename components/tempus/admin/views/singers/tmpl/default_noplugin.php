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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'administrator/components/com_tempus/assets/css/tempus.css');
$document->addStyleSheet(Uri::root() . 'media/com_tempus/css/list.css');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tempus');
$saveOrder = $listOrder == 'a.`ordering`';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tempus&task=singers.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'singerList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
        <div class="alert alert-warning">
            <?php $pluginLink = 'index.php?option=com_plugins&view=plugins&filter[folder]=user&filter[element]=tempus'; ?>
            <h3><?php echo Text::_('COM_TEMPUS_ADMIN_PLUGIN_USER_PROFILE_DISABLED_LBL'); ?></h3>
            <p><?php echo Text::sprintf('COM_TEMPUS_ADMIN_PLUGIN_USER_PROFILE_DISABLED_DESC', $pluginLink) ?></p>
        </div>
	</div>