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

jimport('joomla.application.component.view');

use \Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * View class for a list of Tempus.
 *
 * @since  1.6
 */
class TempusViewSingers extends \Joomla\CMS\MVC\View\HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($this->getLayout() !== 'modal')
		{
			TempusHelper::addSubmenu('singers');

			$this->addToolbar();

			$this->sidebar = JHtmlSidebar::render();
		}

		$tpl = !$this->getPlugin() ? 'noplugin' : $tpl;

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = TempusHelper::getActions();

		JToolBarHelper::title(Text::_('COM_TEMPUS_TITLE_SINGERS'), 'singers.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/singer';


		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tempus');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_tempus&view=singers');
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'u.`id`' => JText::_('JGRID_HEADING_ID'),
			'a.`ordering`' => JText::_('JGRID_HEADING_ORDERING'),
			'u.`block`' => JText::_('JSTATUS'),
		);
	}

    /**
     * Check if state is set
     *
     * @param   mixed  $state  State
     *
     * @return bool
     */
    public function getState($state)
    {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
	}
	
	/**
	 * Check if user profile plugin is enabled
	 *
	 * @return void
	 */
	protected function getPlugin()
	{
		if (PluginHelper::isEnabled('user', 'tempus'))
		{
			return true;
		}

		return false;
	}
}
