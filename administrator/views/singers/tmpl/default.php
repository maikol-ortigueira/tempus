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

<form action="<?php echo Route::_('index.php?option=com_tempus&view=singers'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>

            <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
			<table class="table table-striped" id="singerList">
				<thead>
					<tr>
						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th><!-- Cabecera para la ordenación de los elementos -->
						<?php endif; ?>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
								title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th><!-- Cabecera para los checkboxes - selectores de filas -->
						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
						</th><!-- Cabecera para la publicación de los elementos -->
						<?php endif; ?>
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_TEMPUS_TITLE_SINGERS', 'a.`name`', $listDirn, $listOrder); ?>
						</th><!-- Cabecera para id de registros -->
						<th class='left'>
							<?php echo JText::_('JGLOBAL_EMAIL'); ?>
						</th><!-- Cabecera para id de registros -->
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_TEMPUS_SINGER_FIELD_RANGE_LBL', 'a.`range`', $listDirn, $listOrder); ?>
						</th><!-- Cabecera para id de registros -->
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_TEMPUS_SINGERS_ID', 'a.`id`', $listDirn, $listOrder); ?>
						</th><!-- Cabecera para id de registros -->
					</tr><!-- Fin de Títulos de cabecera de la tabla -->
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td><!-- Paginación -->
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_tempus');
					$canEdit    = $user->authorise('core.edit', 'com_tempus');
					$canCheckin = $user->authorise('core.manage', 'com_tempus');
					$canChange  = $user->authorise('core.edit.state', 'com_tempus');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder) :
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
										  title="<?php echo $disabledLabel ?>">
										<i class="icon-menu"></i>
									</span>
									<input type="text" style="display:none" name="order[]" size="5"
										   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive">
									<i class="icon-menu"></i>
									</span>
								<?php endif; ?>
							</td><!-- Selectores para el drag and drop de cada fila -->
						<?php endif; ?>
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td><!-- checkboxes de cada fila -->
						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'singers.', $canChange, 'cb'); ?>
							</td><!-- radio de publish o unpublish de cada fila -->
						<?php endif; ?>
						<td><!-- Columna nombre -->
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'singers.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit || $canEditOwn) : ?>
								<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_tempus&task=singer.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->name) . ' ' . $this->escape($item->surname); ?></a>
							<?php else : ?>
								<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->name); ?></span>
							<?php endif; ?>
							<br><span class="small break-word">
								<?php if (empty($item->note)) : ?>
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								<?php else : ?>
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
								<?php endif; ?>
							</span>
						</td><!-- Fin columna nombre -->
						<td>
							<?php echo $item->email; ?>
						</td><!-- el email de elemento de cada fila -->
						<td>
							<?php echo TempusHelper::getVoices()[$item->range]; ?>
						</td><!-- el email de elemento de cada fila -->
						<td>
							<?php echo $item->id; ?>
						</td><!-- el id de elemento de cada fila -->
					</tr>
				<?php endforeach; ?>
				</tbody><!-- Fin del cuerpo de la tabla -->
			</table>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
<script>
    window.toggleField = function (id, task, field) {
        var f = document.adminForm, i = 0, cbx, cb = f[ id ];
        if (!cb) return false;
        while (true) {
            cbx = f[ 'cb' + i ];
            if (!cbx) break;
            cbx.checked = false;
            i++;
        }
        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);
        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>