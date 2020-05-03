<?php
/**
 * @version    1.0.0
 * @package    com_tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

JLoader::register('TempusHelperRoute', JPATH_ROOT . '/components/com_tempus/helpers/route.php');

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.polyfill', array('event'), 'lt IE 9');
HTMLHelper::_('script', 'com_tempus/admin-rehearsals-modal.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
HTMLHelper::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_CATEGORY')));
HTMLHelper::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_ACCESS')));
HTMLHelper::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_AUTHOR')));
HTMLHelper::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
HTMLHelper::_('bootstrap.tooltip', '#filter_search', array('title' => Text::_($searchFilterDesc), 'placement' => 'bottom'));

$function  = $app->input->getCmd('function', 'jSelectRehearsal');
//$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	Factory::getDocument()->addScriptOptions('xtd-rehearsals', array('editor' => $editor));
	$onclick = "jSelectRehearsal";
}
?>
<div class="container-popup">

<form action="<?php echo Route::_('index.php?option=com_tempus&view=rehearsals&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken()/* . '=1&editor=' . $editor*/); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th width="1%" class="center nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				$iconStates = array(
					-2 => 'icon-trash',
					0  => 'icon-unpublish',
					1  => 'icon-publish',
					2  => 'icon-archive',
				);
				?>
				<?php foreach ($this->items as $i => $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
						</td>
						<td>
							<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
								. ' data-id="' . $item->id . '"'
								. ' data-title="' . $this->escape($item->title) . '"'
								. ' data-uri="' . $this->escape(TempusHelperRoute::getRehearsalRoute($item->id)) . '"';
							?>
							<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
								<?php echo $this->escape($item->title); ?></a>
							<span class="small break-word">
								<?php if (empty($item->note)) : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								<?php else : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
								<?php endif; ?>
 							</span>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
