<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of integers with specified first, last and step values.
 *
 * @since  1.7.0
 */
class JFormFieldMinuteList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'MinuteList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.

		$step = (int) $this->element['step'];

		// Sanity checks.
		if ($step <= 0)
		{
			// Step of 0 will create an endless loop.
			return $options;
		}
		else
		{
			// Build the options array.
			for ($i = 0; $i <= 59; $i += $step)
			{
				$min = $i === 0 ? '00' : (string) $i;
				$options[] = JHtml::_('select.option', $min);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
