<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.tempus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('TempusHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_tempus/helpers' . DIRECTORY_SEPARATOR . 'tempus.php');

/**
 * An example custom tempus plugin.
 *
 * @since  1.6
 */
class PlgUserTempus extends JPlugin
{
	/**
	 * Date of birth.
	 *
	 * @var    string
	 * @since  3.1
	 */
	private $date = '';

	/**
	 * New User group
	 *
	 * @var string
	 */
	private $group = '';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		FormHelper::addFieldPath(__DIR__ . '/field');


		$lang = Factory::getLanguage();
		$extension = 'com_tempus';
		$base_dir = JPATH_ADMINISTRATOR;
		$lang->load($extension, $base_dir);
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile', 'com_tempus.singer')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) && $userId > 0)
			{
				// Load the profile data from the database.
				$db = Factory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles'
						. ' WHERE user_id = ' . (int) $userId . " AND profile_key LIKE 'profile.%'"
						. ' ORDER BY ordering'
				);

				try
				{
					$results = $db->loadRowList();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());

					return false;
				}

				// Merge the profile data.
				$data->profile = array();

				foreach ($results as $v)
				{
					$k = str_replace('profile.', '', $v[0]);
					$data->profile[$k] = json_decode($v[1], true);

					if ($data->profile[$k] === null)
					{
						$data->profile[$k] = $v[1];
					}
				}
			}

			if (!HTMLHelper::isRegistered('users.calendar'))
			{
				HTMLHelper::register('users.calendar', array(__CLASS__, 'calendar'));
			}

			if (!HTMLHelper::isRegistered('users.dob'))
			{
				HTMLHelper::register('users.dob', array(__CLASS__, 'dob'));
			}
		}

		return true;
	}

	/**
	 * Returns html markup showing a date picker
	 *
	 * @param   string  $value  valid date string
	 *
	 * @return  mixed
	 */
	public static function calendar($value)
	{
		if (empty($value))
		{
			return HTMLHelper::_('users.value', $value);
		}
		else
		{
			return HTMLHelper::_('date', $value, null, null);
		}
	}

	/**
	 * Returns the date of birth formatted and calculated using server timezone.
	 *
	 * @param   string  $value  valid date string
	 *
	 * @return  mixed
	 */
	public static function dob($value)
	{
		if (!$value)
		{
			return '';
		}

		return HTMLHelper::_('date', $value, Text::_('DATE_FORMAT_LC1'), false);
	}

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Check we are manipulating a valid form.
		$name = $form->getName();
		$user = Factory::getUser();
		$app = Factory::getApplication();
		$passw = $app->input->get('passw', null, 'string');
		$layout = $app->input->get('layout', null, 'string');
		$comParams = ComponentHelper::getParams('com_tempus');

		if ($name == 'com_users.registration' && is_object(($data)))
		{
			if (is_null($passw) && is_null($layout))
			{
				$this->renderPasswordForm();
			}
			elseif ($passw === $comParams->get('musician_password') || $passw === $comParams->get('director_password'))
			{
				$group = $passw === $comParams->get('musician_password') ? $comParams->get('musician_group') : $comParams->get('director_group');
			}
			elseif (is_null($layout))
			{
				// Throw an exception if wrong password
				$app->enqueueMessage(Text::_('PLG_USER_TEMPUS_INVALID_PASSWORD_LBL'), 'error');
				$app->redirect(Route::_('index.php?option=com_users&view=login'));
			}
		}

		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration', 'com_tempus.singer')))
		{
			return true;
		}

		// Add the registration fields to the form.
		Form::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('tempus');

		$fields = array(
			'range',
			'address1',
			'address2',
			'city',
			'region',
			'country',
			'postal_code',
			'phone',
			'favoritebook',
			'aboutme',
			'dob',
			'photo',
		);

		// Change fields description when displayed in frontend or backend profile editing
		$app = Factory::getApplication();

		if ($app->isClient('site') || $name === 'com_users.user' || $name === 'com_admin.profile')
		{
			$form->setFieldAttribute('address1', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('address2', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('city', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('region', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('country', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('postal_code', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('phone', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('favoritebook', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('aboutme', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('dob', 'description', 'PLG_USER_TEMPUS_FILL_FIELD_DESC_SITE', 'profile');
		}

		if ($name === 'com_tempus.singer')
		{
			$form->setFieldAttribute('address1', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('address2', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('city', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('region', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('country', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('postal_code', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('phone', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('favoritebook', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('aboutme', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('dob', 'readonly', 'true', 'profile');
			$form->setFieldAttribute('dob', 'info', '', 'profile');
			$form->removeField('photo_file', 'profile');
		}

		if ($name === 'com_users.registration')
		{
			$form->setFieldAttribute('group', 'default', $group, 'profile');
		}

		foreach ($fields as $field)
		{
			// Case using the users manager in admin
			if ($name === 'com_users.user')
			{
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				// Remove the field if it is disabled in registration and profile
				elseif ($this->params->get('register-require_' . $field, 1) == 0
					&& $this->params->get('profile-require_' . $field, 1) == 0)
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case registration
			elseif ($name === 'com_users.registration')
			{
				// Toggle whether the field is required.
				if ($this->params->get('register-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case profile in site or admin
			elseif ($name === 'com_users.profile' || $name === 'com_admin.profile' || $name === 'com_tempus.singer')
			{
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
					$form->removeField('pass_note', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
		}

		// Drop the profile form entirely if there aren't any fields to display.
		$remainingfields = $form->getGroup('profile');

		if (!count($remainingfields))
		{
			$form->removeGroup('profile');
		}

		return true;
	}

	/**
	 * Method is called before user data is stored in the database
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 * @throws  InvalidArgumentException on invalid date.
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{
		// Check that the date is valid.
		if (!empty($data['profile']['dob']))
		{
			try
			{
				$date = new Date($data['profile']['dob']);
				$this->date = $date->format('Y-m-d H:i:s');
			}
			catch (Exception $e)
			{
				// Throw an exception if date is not valid.
				throw new InvalidArgumentException(Text::_('PLG_USER_PROFILE_ERROR_INVALID_DOB'));
			}

			if (Date::getInstance('now') < $date)
			{
				// Throw an exception if dob is greater than now.
				throw new InvalidArgumentException(Text::_('PLG_USER_PROFILE_ERROR_INVALID_DOB_FUTURE_DATE'));
			}
		}

		return true;
	}

	/**
	 * Saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return  boolean
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		// Get the uploaded file information.
		$input    = JFactory::getApplication()->input;

		// Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See JInputFiles::get.
		$photofile = $input->files->get('jform', null, 'raw')['profile']['photo_file'];
		if (is_array($photofile) && $photofile['name'] !== '')
		{
			$data['profile']['photo'] = $this->savePhotoFile($photofile);
		}
		$userGroup = $data['profile']['group'];
		

		if ($userId && $result && isset($data['profile']) && count($data['profile']))
		{
			try
			{
				$db = Factory::getDbo();

				// Sanitize the date
				if (!empty($data['profile']['dob']))
				{
					$data['profile']['dob'] = $this->date;
				}

				$keys = array_keys($data['profile']);

				foreach ($keys as &$key)
				{
					$key = 'profile.' . $key;
					$key = $db->quote($key);
				}

				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId)
					->where($db->quoteName('profile_key') . ' IN (' . implode(',', $keys) . ')');
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->select($db->quoteName('ordering'))
					->from($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId);
				$db->setQuery($query);
				$usedOrdering = $db->loadColumn();

				$tuples = array();
				$order = 1;

				foreach ($data['profile'] as $k => $v)
				{
					while (in_array($order, $usedOrdering))
					{
						$order++;
					}

					$tuples[] = '(' . $userId . ', ' . $db->quote('profile.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		if ($isNew && $userGroup !== '')
		{
			$user = Factory::getUser($userId);
			$user->groups = Array($userGroup);
			
			$user->save();
		}


		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = Factory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId
						. " AND profile_key LIKE 'tempus.%'"
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	protected function savePhotoFile ($photofile)
	{
		// Make sure that file uploads are enabled in php.
		if (!(bool) ini_get('file_uploads'))
		{
			JError::raiseWarning('', Text::_('PLG_USER_TEMPUS_UPLOAD_WARNINUPLOADFILE_MSG'));

			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($photofile))
		{
			JError::raiseWarning('', Text::_('PLG_USER_TEMPUS_UPLOAD_NO_FILE_SELECTED_MSG'));

			return false;
		}

		// Is the PHP tmp directory missing?
		if ($photofile['error'] && ($photofile['error'] == UPLOAD_ERR_NO_TMP_DIR))
		{
			JError::raiseWarning(
				'',
				Text::_('PLG_USER_TEMPUS_UPLOAD_WARNUPLOADERROR_MSG') . '<br />' . Text::_('PLG_USER_TEMPUS_WARNINGS_PHPUPLOADNOTSET_MSG')
			);

			return false;
		}

		// Is the max upload size too small in php.ini?
		if ($photofile['error'] && ($photofile['error'] == UPLOAD_ERR_INI_SIZE))
		{
			JError::raiseWarning(
				'',
				Text::_('PLG_USER_TEMPUS_UPLOAD_WARNUPLOADERROR_MSG') . '<br />' . Text::_('PLG_USER_TEMPUS_WARNINGS_SMALLUPLOADSIZE_MSG')
			);

			return false;
		}

		// Check if there was a different problem uploading the file.
		if ($photofile['error'] || $photofile['size'] < 1)
		{
			JError::raiseWarning('', Text::_('PLG_USER_TEMPUS_UPLOAD_WARNUPLOADERROR_MSG'));

			return false;
		}

		if (!$this->checkFile($photofile))
		{
			return '';
		}

		// Build the appropriate paths.
		$photoPath = JPATH_ROOT . '/images/com_tempus/images/photos/';
		$photofullname = '/images/com_tempus/images/photos/' . $photofile['name'];
		$photo_dest = $photoPath . $photofile['name'];
		$tmp_src  = $photofile['tmp_name'];

		// Move uploaded file.
		File::upload($tmp_src, $photo_dest, false, true);
		

		return $photofullname;
	}

	protected function checkFile ($file)
	{
		return true;
	}

	protected function renderPasswordForm()
	{
		?>
			<!-- This is the modal -->
			<div id="modal-example" uk-modal>
				<div class="uk-modal-dialog uk-modal-body">
					<div class="uk-modal-header">
						<h2 class="uk-h4"><?php echo Text::_('Registro de Miembros del Grupo'); ?></h2>
					</div>
					<div class="uk-alert-primary" uk-alert>
						<a class="uk-alert-close" uk-close></a>
						<p><?php echo Text::_('Texto de la alert'); ?></p>
					</div>
					<form name="passForm" action="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>" method="post">
						<div class="uk-margin">
							<input class="uk-input uk-form-width-large" form="passForm" id="registration_password" type="text" placeholder="<?php echo Text::_('Contraseña'); ?>">
						</div>
						<div class="uk-modal-footer uk-text-right">
							<input class="uk-button uk-button-default" type="button"  value="<?php echo Text::_('Cancelar'); ?>"/>
							<input class="uk-button uk-button-primary" type="button" onclick="formSubmit()" value="<?php echo Text::_('Continuar'); ?>"/>
						</div>
					</form>
				</div>
			</div>

			<script>
				UIkit.modal('#modal-example').show();

				function formSubmit() {
					var password = jQuery('#registration_password').val();
					var link = '<?php echo Route::_('index.php?option=com_users&view=registration'); ?>';
					jQuery('form[name=passForm]').attr('action', `${link}&passw=${password}`);

					document.forms['passForm'].submit();
				}
			</script>
		<?php

	}
}
