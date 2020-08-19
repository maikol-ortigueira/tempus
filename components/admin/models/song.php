<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Filesystem\File;
use \Joomla\CMS\Filesystem\Path;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Table\Table;
use \Joomla\Registry\Registry;

JLoader::register('DropboxHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/dropbox.php');

/**
 * Tempus model.
 *
 * @since  1.6
 */
class TempusModelSong extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TEMPUS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tempus.song';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Song', $prefix = 'TempusTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
     *
     * @throws
	 */
	public function getForm($data = array(), $loadData = true)
	{
            // Initialise variables.
            $app = Factory::getApplication();

            // Get the form.
            $form = $this->loadForm(
                    'com_tempus.song', 'song',
                    array('control' => 'jform',
                            'load_data' => $loadData
                    )
            );



            if (empty($form))
            {
                return false;
            }

            return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
     *
     * @throws
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tempus.edit.song.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;

		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{

            if ($item = parent::getItem($pk))
            {
				$registry = new Registry($item->documents);
				$item->documents = $registry->toArray();
            }

            return $item;

	}

	/**
	 * Method to duplicate an Song
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getUser();

		// Access checks.
		if (!$user->authorise('core.create', 'com_tempus'))
		{
			throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$dispatcher = JEventDispatcher::getInstance();
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{

			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check())
				{
					throw new Exception($table->getError());
				}


				// Trigger the before save event.
				$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

				if (in_array(false, $result, true) || !$table->store())
				{
					throw new Exception($table->getError());
				}

				// Trigger the after save event.
				$dispatcher->trigger($this->event_after_save, array($context, &$table, true));
			}
			else
			{
				throw new Exception($table->getError());
			}

		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__tempus_songs');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	public function save($data)
	{
		$input = Factory::getApplication()->input;

		// Bring the files
		if ($this->myUploads($input, $data))
		{
			return parent::save($data);
		}

	}

	/**
	 * Works out an installation package from a HTTP upload.
	 *
	 * @return package definition or false on failure.
	 */
	protected function myUploads($input, &$data)
	{

        // Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See JInputFiles::get.
		$userfiles = $input->files->get('jform', null, 'array');

		// Make sure that file uploads are enabled in php.
		if (!(bool) ini_get('file_uploads'))
		{
			JError::raiseWarning('', Text::_('COM_TEMPUS_FILE_WARNINGFILE_MSG'));

			return false;
		}

		// Recorrer cada uno de los posibles ficheros
		$userfiles = $userfiles['documents'];
		// Recorrer cada subform
		foreach ($userfiles as $subforms => $subform)
		{
			// Recorrer cada línea del subform
			foreach ($subform as $line => $value)
			{
				$userfile = $value['userfile'];

				// If there is no uploaded file, we have a problem...
				if (!is_array($userfile))
				{
					JError::raiseWarning('', Text::_('COM_TEMPUS_FILE_NO_FILE_SELECTED_MSG'));

					return false;
				}

				// Is the PHP tmp directory missing?
				if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR))
				{
					JError::raiseWarning(
						'',
						Text::_('COM_TEMPUS_FILE_WARNING_FILE_UPLOAD_ERROR_MSG') . '<br />' . Text::_('COM_TEMPUS_WARNINGS_TMP_UPLOAD_FOLDER_NOT_SET_MSG')
					);

					return false;
				}

				// Is the max upload size too small in php.ini?
				if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE))
				{
					JError::raiseWarning(
						'',
						Text::_('COM_TEMPUS_FILE_WARNING_FILE_UPLOAD_ERROR_MSG') . '<br />' . Text::_('COM_TEMPUS_FILE_WARNINGS_SMALL_UPLOAD_SIZE_MSG')
					);

					return false;
				}

				// Check if there was a different problem uploading the file.
				if ($userfile['error'] || $userfile['size'] < 1)
				{
					if ($userfile['error'] != 4)
					{
						JError::raiseWarning('', Text::_('COM_TEMPUS_FILE_WARNING_FILE_UPLOAD_ERROR_MSG'));
					}
				}
				else
				{
					// Upload the file
					$tmp_src  = $userfile['tmp_name'];

					// Check the file extension
					if ($this->checkUpload($userfile['name']))
					{
						$folderName = trim(OutputFilter::stringURLSafe($data['title'] . '-' . $data['author']), '-');
						$fileData = $this->uploadUserfile($tmp_src, $userfile['name'], $folderName, $subforms);
						foreach ($fileData as $key => $value)
						{
							$data['documents'][$subforms][$line][$key] = $value;
						}
					}
				}

			}
		}

		return true;
	}


    protected function checkUpload($archivename)
    {
        // La primera verificación se deberá realizar en el archivo form.xml
        // Al campo "file" debemos colocar correctamente el accept="audio/*,video/*,image/*,application/excel,application/msword"
        // Esto permitirá aceptar solamente los tipos de archivo indicados en el campo accept, independientemente de la extensión del archivo cargado

        // get the file format
        $fileFormat = strtolower(pathinfo($archivename, PATHINFO_EXTENSION));

        // get the fileFormat key
        $allowedFormats = array('jpg', 'png', 'jpeg', 'mp3', 'pdf');

        // Podemos incluir un parámetro en la configuración del componente para utilizar aquí
        //if(in_array($fileFormat, $this->formats[$this->formatType . '_formats']))
        //{
        // get allowed formats
        //   $allowedFormats = (array) $this->app_params->get($this->formatTypes . '_formats', null);
        //}


        // check the extension
        if (!in_array($fileFormat, $allowedFormats))
        {
            // Cleanup the import files
            $this->removeFile($archivename);
            $this->errorMessage = Text::_('COM_TEMPUS_DOES_NOT_HAVE_A_VALID_FILE_TYPE');
            return false;
        }

        return true;
    }

    /**
     * Clean up temporary uploaded file
     *
     * @param string $filename   Name of the uploaded file
     *
     * @return boolean True on success
     */
    protected function removeFile($filename)
    {
        // Is the filename a valid file?
        if (is_file($filename))
        {
            File::delete($filename);
        }
        elseif (is_file(Path::clean($filename)))
        {
            // It might also be just a base filename
            File::delete(Path::clean($filename));
        }
	}

	protected function uploadUserfile($tmp_src, $filename, $folderName, $subforms)
	{
		// Get the root folder from params
		$params = ComponentHelper::getParams('com_tempus');
		$dest_path = trim($params->get('files_folder'), '/');

		// Set the folderName and type subfolder
		$subforms = Text::_('COM_TEMPUS_SONG_FIELD_' . strtoupper($subforms) . '_LBL');
		$dest_path = $dest_path . "/" . $folderName . "/" . $subforms;
		$fileData['src_server'] = $params->get('file_server');
		$fileData['fullpath'] = $dest_path . '/' . $filename;
		$fileData['filename'] = $filename;

		// Move uploaded file.
		$this->{'uploadTo' . ucfirst($fileData['src_server'])}($tmp_src, $fileData['fullpath']);

		return $fileData;
	}

	protected function uploadToLocal($tmp_src, $dest_path)
	{
		$dest_path = JPATH_ROOT . "/" . $dest_path;

		File::upload($tmp_src, $dest_path, false);
	}

	protected function uploadToDropbox($tmp_src, $dest_path)
	{
		// TODO Deberíamos comprobar si el usuario tiene permiso para subir $canDo = $TempusHelper::getActions()

		$app = Factory::getApplication();

		$params = ComponentHelper::getParams('com_tempus');

		$token = $params->get('oauth2Token_dropbox');

		if (empty($token))
		{
			$errorMessage = Text::_('No dispones de token para Dropbox. Debes configurarlo en las opciones del componente.');
			$app->enqueueMessage($errorMessage, 'error');
			$app->redirect(Route::_('index.php?option=com_config&view=component&component=com_tempus#file_system'));
			return;
		}

		// Opciones de subida
		$options = array();
		$options['mode'] = 'add';
		$options['autorename'] = true;

		// if ($condicion == 'sobreescribir')
		//{
		//	$options['mode'] = 'overwrite';
		//	$options['autorename'] = false;
		//}

		//$options['mute'] = true o false

		$options['path'] = '/' . trim($dest_path, '/');

		DropboxHelper::filesUpload($token, $tmp_src, $options);

		return;
	}

	protected function uploadToAs3($tmp_src, $dest_path)
	{
		// all my code
	}
}
