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
	$saveOrderingUrl = 'index.php?option=com_tempus&task=rehearsals.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'rehearsalList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<form action="<?php echo Route::_('index.php?option=com_tempus&view=rehearsals'); ?>" method="post"
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
			<table class="table table-striped" id="rehearsalList">
				<thead>
					<tr>
						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone"><!-- Cabecera orden de items -->
								<?php echo HTMLHelper::_('searchtools.sort', '', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th><!-- Fin cabecera orden de items -->
						<?php endif; ?>
						<th width="1%" class="hidden-phone"><!-- Cabecera selectores de items -->
							<input type="checkbox" name="checkall-toggle" value=""
								title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th><!-- Fin cabecera selectores de items -->
						<?php if (isset($this->items[0]->state)): ?><!-- Cabecera estado de items -->
							<th width="1%" class="nowrap center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
							</th><!-- Fin cabecera estado de items -->
						<?php endif; ?>
						<th class='left'><!-- Cabecera id de items -->
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_TEMPUS_ID', 'a.`id`', $listDirn, $listOrder); ?>
						</th><!-- Fin cabecera id de items -->
						<th class='left'><!-- Cabecera nombre de items -->
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_TEMPUS_TITLE_REHEARSALS', 'a.`title`', $listDirn, $listOrder); ?>
						</th><!-- Fin cabecera nombre de items -->
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_tempus');
					$canEdit    = $user->authorise('core.edit', 'com_tempus');
					$canEditOwn = $user->authorise('core.edit.own', 'com_tempus');
					$canCheckin = $user->authorise('core.manage', 'com_tempus');
					$canChange  = $user->authorise('core.edit.state', 'com_tempus');
					?>
						<tr class="row<?php echo $i % 2; ?>">
							<?php if (isset($this->items[0]->ordering)) : ?>
								<td class="order nowrap center hidden-phone"><!-- Columna orden -->
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
								</td><!-- Fin columna orden -->
							<?php endif; ?>
							<td class="hidden-phone"><!-- Columna cuadro selector -->
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td><!-- Fin columna cuadro selector -->
							<?php if (isset($this->items[0]->state)): ?>
								<td class="center"><!-- Columna estado -->
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'rehearsals.', $canChange, 'cb'); ?>
								</td><!-- Fin columna orden estado -->
							<?php endif; ?>
							<td><!-- Columna id -->
								<?php echo $item->id; ?>
							</td><!-- Fin columna id -->
							<td><!-- Columna nombre -->
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'rehearsals.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_tempus&task=rehearsal.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<span class="small break-word">
									<?php if (empty($item->note)) : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									<?php else : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
									<?php endif; ?>
								</span>
							</td><!-- Fin columna nombre -->
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table><!-- Fin de la tabla -->
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