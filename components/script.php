<?php
/**
 * @version    3.2
 * @package    com_tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2019 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use \Joomla\CMS\Table\Nested;
 use \Joomla\CMS\Table\Table;
 use \Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die;

/**
 * Class TempusInstallerScript
 *
 * @since 1.0
 */
class com_tempusInstallerScript
{
	/**
	 * Method to create a user Fields Group + some user fields
	 *
	 * @param 	string 	$parent 	Parent is the class calling this method
	 *
	 * @return void
	 */
	public function install($parent)
	{
		// Create User Field group

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/tables');
		$group = Nested::getInstance('Group', 'FieldsTable');

		// If default group does not exist, create it and change permissions
		if (!$group->load(array('context' => 'com_users.user', 'title' => 'Otros datos')))
		{
			$group->context = 'com_user.user';
			$group->title 	= 'Otros datos';
			$group->state 	= 1;
			$group->created_by = '0';
			$group->params	= '{"display_readonly":"1"}';
			$group->language = '*';
			$group->access = '1';

			// Set the location in the tree
			$group->setLocation(1, 'last-child');

			// Check to make sure our data is valid
			if (!$group->check())
			{
				Factory::getApplication()->enqueueMessage(Text::_sprintf('Error', $group->getError()));
				return;
			}

			// Now store the group
			if (!$group->store(true))
			{
				Factory::getApplication()->enqueueMessage(Text::_sprintf('Error', $group->getError()));
				return;
			}

			// Build the path for our group
			$group->rebuildPath($group->id);

		}
	}

	/**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
        echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}