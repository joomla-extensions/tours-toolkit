<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/** @var  \Joomla\Component\Guidedtourstoolkit\Administrator\View\Info\HtmlView  $this */

try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    die('Failed to get app');
}

$user = $app->getIdentity();
?>
<form action="<?php echo Route::_('index.php?option=com_guidedtourstoolkit&view=info'); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <div class="row">
        <div class="col-md-12">
            <?php if ($user->authorise('core.create', 'com_guidedtours')) : ?>
                <div class="card mb-3">
                    <h2 class="card-header"><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_TOURS_IMPORT_LABEL'); ?></h2>
                    <div class="card-body">
                        <div class="control-group">
                            <div class="control-label">
                                <label for="importfile"><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_TOURS_IMPORT_FILE_LABEL'); ?></label>
                            </div>
                            <div class="controls">
                                <input type="file" id="importfile" name="importfile" accept=".json,application/json" class="form-control" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('tours.import');return false;">
                            <?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
