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
use Joomla\CMS\Factory;

/**
 * Supports a custom SQL select list
 *
 * @since  1.7.0
 */
class JFormFieldSingers extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $type = 'Singers';

	/**
	 * The keyField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $keyField;

	/**
	 * The valueField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $valueField;

	/**
	 * The translate.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $translate = false;

	/**
	 * The query.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $query;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			// Check if its using the old way
			$this->query = (string) $this->element['query'];

			if (empty($this->query))
			{
				// Get the query from the form
				$query    = array();
                $defaults = array();
                
                $query['range'] = (string) $this->element['range'];
                $query['userGroup'] = '10';

				// Process the query
				$this->query = $this->processQuery($query);
			}

			$this->keyField   = (string) $this->element['key_field'] ?: 'id';
			$this->valueField = (string) $this->element['value_field'] ?: 'title';
			$this->translate  = (string) $this->element['translate'] ?: false;
			$this->header     = (string) $this->element['header'] ?: false;
		}

		return $return;
	}

	/**
	 * Method to process the query from form.
	 *
	 * @param   array   $conditions  The conditions from the form.
	 * @param   string  $filters     The columns to filter.
	 * @param   array   $defaults    The defaults value to set if condition is empty.
	 *
	 * @return  JDatabaseQuery  The query object.
	 *
	 * @since   3.5
	 */
	protected function processQuery($conditions)
	{
		$range = json_encode($conditions['range']);

		// Get the database object.
		$db = Factory::getDbo();

		// Get the query object
		$query = $db->getQuery(true);

		// Select fields
		$query->select($db->quoteName('u.*'));

		// From selected table
		$query->from($db->quoteName('#__users') . 'AS u');

		$query->join('RIGHT', $db->quoteName('#__user_usergroup_map') . 'AS g ON ' . $db->quoteName('g.user_id') . ' = ' . $db->quoteName('u.id'));
		$query->join('RIGHT', $db->quoteName('#__user_profiles') . 'AS p ON ' . $db->quoteName('p.user_id') . ' = ' . $db->quoteName('u.id'));

		// Where condition
		$query->where($db->quoteName('g.group_id') . ' = ' . $db->quote($conditions['userGroup']));
		$query->where($db->quoteName('p.profile_key') . ' = ' . $db->quote('tempus.range'));
		$query->where($db->quoteName('p.profile_value') . ' = ' . $db->quote($range));
		

		$queryString = $db->replacePrefix((string) $query);

		return $query;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		if ($this->query)
		{
			// Get the database object.
			$db = Factory::getDbo();

			// Set the query and get the result list.
			$db->setQuery($this->query);

			try
			{
				$items = $db->loadObjectlist();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				Factory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}
		}

		// Add header.
		if (!empty($header))
		{
			$header_title = JText::_($header);
			$options[] = JHtml::_('select.option', '', $header_title);
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
                $item = $this->getValue($item);

				if ($this->translate == true)
				{
					$options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
    }
    
    protected function getValue($item)
    {
        // Get the database object
        $db = Factory::getDbo();

        // Get the query object
        $query = $db->getQuery(true);

        // Select
        $query->select('p.profile_key, p.profile_value');

        $query->from($db->quoteName('#__user_profiles') . ' AS p');

        $query->where($db->quoteName('p.user_id') . ' = ' . $db->quote($item->id));

        $db->setQuery($query);

        try
        {
            $profileValues = $db->loadObjectlist();
        }
        catch (JDatabaseExceptionExecuting $e)
        {
            Factory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        if (count($profileValues) > 0)
        {
			$array = array();
            foreach ($profileValues as $value) {
				$newValue = explode('.',$value->profile_key);
				$array[$newValue[1]] = json_decode($value->profile_value);
            }
        }

        $item->title = $array['alias'] !== '' ? $array['alias'] : trim($item->name . ' ' . $array['lastname']);

        return $item;
    }
}
