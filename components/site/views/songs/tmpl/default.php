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
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_tempus') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'songform.xml');
$canEdit    = $user->authorise('core.edit', 'com_tempus') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'songform.xml');
$canCheckin = $user->authorise('core.manage', 'com_tempus');
$canChange  = $user->authorise('core.edit.state', 'com_tempus');
$canDelete  = $user->authorise('core.delete', 'com_tempus');
$download = true;
$pdfDownload = $download ? ' download' : ' target="_blank"';

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'media/com_tempus/css/list.css');
$document->addStyleSheet(Uri::root() . 'media/com_tempus/css/tablesaw.css');
// Import Javascript
$document->addScript(Uri::root() . 'media/com_tempus/js/tablesaw.jquery.js');
$document->addScript(Uri::root() . 'media/com_tempus/js/tablesaw-init.js');

// Get the instruments
$instruments = TempusHelpersTempus::getVoices(true);
?>
<script>
	TablesawConfig = {
	i18n: {
		modeStack: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_STACK'); ?>',
		modeSwipe: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_SWIPE'); ?>',
		modeToggle: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_TOGGLE'); ?>',
		modeSwitchColumnsAbbreviated: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_COLS'); ?>',
		modeSwitchColumns: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_COLUMNS'); ?>',
		columnToggleButton: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_COLUMNS'); ?>',
		columnToggleError: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_COLUMN_TOGGLE_ERROR'); ?>',
		sort: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_SORT'); ?>',
		swipePreviousColumn: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_SWIPE_PREVIOUS_COLUMN'); ?>',
		swipeNextColumn: '<?php echo Text::_('COM_TEMPUS_TABLE_SAW_SWIPE_NEXT_COLUMN'); ?>'
	}
};
</script>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<div class="details">
		<h1 class="description-container"><?php echo JText::_('COM_TEMPUS_SONGS_LIST_HEADER'); ?></span></h1>
	</div>
	<div>
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	</div>
	<div class="docs-main tablesaw-overflow">
		<table class="tablesaw tablesaw-sack tablesaw-row-border tablesaw-row-zebra" data-tablesaw-mode="columntoggle" data-tablesaw-minimap data-tablesaw-sortable data-tablesaw-sortable-switch>
			<thead>
				<tr>
					<?php if (isset($this->items[0]->state) && $canChange): ?>
						<th scope="col" data-tablesaw-priority="4" rowspan="2">
							<?php echo Text::_('JPUBLISHED'); ?>
						</th>
					<?php endif; ?>
					<?php if ($canEdit || $canDelete): ?>
						<th scope="col" data-tablesaw-priority="4" rowspan="2">
							<?php echo Text::_('COM_TEMPUS_SONGS_HEADER_TITLE_ACTIONS'); ?>
						</th>
					<?php endif; ?>
					<th scope="col" data-tablesaw-priority="persist" data-tablesaw-sortable-col data-tablesaw-sortable-default-col rowspan="2">
						<?php echo Text::_('COM_TEMPUS_PLURAL_HEADER_TITLE_TITLE'); ?>
					</th>
					<th scope="col" data-tablesaw-priority="2" data-tablesaw-sortable-col rowspan="2">
						<?php echo Text::_('COM_TEMPUS_PLURAL_HEADER_TITLE_AUTHOR'); ?>
					</th>
					<th scope="col" data-tablesaw-priority="4" data-tablesaw-sortable-col rowspan="2">
						<?php echo Text::_('COM_TEMPUS_PLURAL_HEADER_TITLE_CATEGORY'); ?>
					</th>
					<?php foreach ($instruments as $index => $instrument) : ?>
						<?php $priority = '0'; ?>
						<?php $priority = $instrument === 'satb' ? 'persist' : $priority; ?>
						<th scope="col" data-tablesaw-priority="<?php echo $priority; ?>" colspan="3">
							<?php echo Text::_('COM_TEMPUS_VOICES_' . $instrument); ?>
						</th>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php $arr_length = count($instruments); ?>
					<?php for($i=0;$i<$arr_length;$i++) : ?>
						<th>
							<?php echo Text::_('COM_TEMPUS_SONG_TAB_SHEETS_LBL'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_TEMPUS_SONG_TAB_AUDIOS_LBL'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_TEMPUS_SONG_TAB_VIDEOS_LBL'); ?>
						</th>
					<?php endfor; ?>
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
			<?php foreach ($this->items as $i => $item) : ?>
					<?php $canEdit = $user->authorise('core.edit', 'com_tempus'); ?>
					<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_tempus')): ?>
						<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
					<?php endif; ?>
					<?php $category = $item->catid !== '' ? TempusHelpersTempus::getFiles($item->catid, '#__categories', 'title')[0] : ''; ?>
					<tr class="row<?php echo $i % 2; ?>">
						<?php if (isset($this->items[0]->state) && $canChange) : ?>
							<td class="center">
								<a class="btn btn-micro" href="<?php echo JRoute::_('index.php?option=com_tempus&task=song.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2); ?>">
								<?php if ($item->state == 1): ?>
									<i class="icon-publish"></i>
								<?php else: ?>
									<i class="icon-unpublish"></i>
								<?php endif; ?>
								</a>
							</td>
						<?php endif; ?>
						<?php if ($canEdit || $canDelete): ?>
							<td>
								<?php if ($canEdit): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_tempus&task=songform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
								<?php endif; ?>
								<?php if ($canDelete): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_tempus&task=songform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_tempus&task=song.default&id=' . $item->id, false, 2); ?>"><?php echo $item->title; ?></a>
						</td>
						<td>
							<?php echo $item->author; ?>
						</td>
						<td>
							<?php echo $category; ?>
						</td>
						<?php foreach ($instruments as $key => $instrument) : ?>
							<?php foreach ($item->documents as $type => $rows) : ?>
								<td>
									<?php foreach ($rows as $i => $row) : ?>
										<?php if (isset($row['instrument']) && $row['instrument'] == $key) : ?>
											<?php if ($type === 'video') : ?>
												<a href="<?php echo $row['video_doc']; ?>" target="_blank">
												<?php if ($instrument === 'satb') : ?>
													<span class="icon-youtube icon-youred x-icon"></span></a>
												<?php else : ?>
													<?php echo Text::_('COM_TEMPUS_VOICES_' . $instrument) . $row['divisi']; ?></a><br>
												<?php endif; ?>
											<?php else : ?>
												<?php if ($download || $type === 'sheet') : ?>
													<a href="<?php echo $row['fullpath']; ?>" <?php echo $pdfDownload ; ?>>
												<?php endif; ?>
												<?php if ($instrument === 'satb') : ?>
													<?php if ($type === 'audio' && $download) : ?>
														<span class="icon-music x-icon"></span></a>
													<?php elseif ($type === 'audio' && !$download) : ?>
														<audio controls>
															<source src="<?php echo $row['fullpath']; ?>" type="audio/mpeg">
														</audio>
													<?php elseif ($type === 'sheet') : ?>
														<span class="icon-file-2 x-icon icon-green"></span></a>
													<?php endif; ?>
												<?php else : ?>
													<?php if ($download) : ?>
														<?php echo Text::_('COM_TEMPUS_VOICES_' . $instrument) . $row['divisi']; ?></a><br>
													<?php else : ?>
														<?php echo Text::_('COM_TEMPUS_VOICES_' . $instrument) . $row['divisi']; ?><br>
														<audio controls>
															<source src="<?php echo $row['fullpath']; ?>" type="audio/mpeg">
														</audio>
													<?php endif; ?>
												<?php endif; ?>
											<?php endif; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</td>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
	<script type="text/javascript">

		jQuery(document).ready(function () {
			jQuery('.delete-button').click(deleteItem);
		});

		function deleteItem() {
			if (!confirm("<?php echo Text::_('COM_TEMPUS_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>

