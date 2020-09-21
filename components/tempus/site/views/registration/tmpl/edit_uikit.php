<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// No direct access
defined('_JEXEC') or die;

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
        <form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>" method="post">
            <div class="uk-margin">
                <input class="uk-input uk-form-width-large" id="registration_password" type="password" placeholder="<?php echo Text::_('Contraseña'); ?>">
            </div>
            <div class="uk-modal-footer uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close" tydive="buttouk-modal-footer n"><?php echo Text::_('Cancelar'); ?></button>
                <button class="uk-button uk-button-primary" type="submit"><?php echo Text::_('Continuar'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    UIkit.modal('#modal-example').show();
</script>
