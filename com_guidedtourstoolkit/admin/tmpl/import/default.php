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
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/** @var  \Joomla\Component\Guidedtourstoolkit\Administrator\View\Import\HtmlView  $this */

try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    die('Failed to get app');
}

$user = $app->getIdentity();
?>
<?php if ($user->authorise('core.create', 'com_guidedtours')) : ?>
  <form action="<?php echo Route::_('index.php?option=com_guidedtourstoolkit&view=import'); ?>"
        method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="card mb-3">
          <h2 class="card-header"><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_TOURS_IMPORT_JSON'); ?></h2>
          <div class="card-body">
            <?php echo $this->form->renderField('importjsonfile'); ?>
            <div class="control-group">
              <div class="control-label"></div>
              <div class="controls">
                <button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('tours.importjson');return false;">
                  <?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="card mb-3">
          <h2 class="card-header"><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSV'); ?></h2>
          <div class="card-body">
            <?php echo $this->form->renderField('tourid'); ?>
            <?php echo $this->form->renderField('importcsvfile'); ?>
            <div class="control-group">
              <div class="control-label"></div>
              <div class="controls">
                <button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('steps.importcsv');return false;">
                  <?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
                </button>
              </div>
            </div>
            <fieldset>
              <?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_DESC'); ?>
              <table class="table">
                <thead>
                <tr>
                  <th><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_COLUMN'); ?></th>
                  <th><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_VALUE'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <th>tour_id</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_TOURID_DESC'); ?></td>
                </tr>
                <tr>
                  <th>title</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_TITLE_DESC'); ?></td>
                </tr>
                <tr>
                  <th>description</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_DESCRIPTION_DESC'); ?></td>
                </tr>
                <tr>
                  <th>position</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_POSITION_DESC'); ?></td>
                </tr>
                <tr>
                  <th>target</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_TARGET_DESC'); ?></td>
                </tr>
                <tr>
                  <th>type</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_TYPE_DESC'); ?></td>
                </tr>
                <tr>
                  <th>interactive_type</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_INTERACTIVETYPE_DESC'); ?></td>
                </tr>
                <tr>
                  <th>url</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_URL_DESC'); ?></td>
                </tr>
                <tr>
                  <th>required</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_REQUIRED_DESC'); ?></td>
                </tr>
                <tr>
                  <th>requiredvalue</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_REQUIREDVALUE_DESC'); ?></td>
                </tr>
                <tr>
                  <th>published</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_PUBLISHED_DESC'); ?></td>
                </tr>
                <tr>
                  <th>note</th>
                  <td><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_NOTE_DESC'); ?></td>
                </tr>
                </tbody>
              </table>
              <a class="btn btn-sm btn-info" href="<?php echo Uri::root() ; ?>media/com_guidedtourstoolkit/samples/sample_steps_import_no_tour_id.csv" download="sample_steps_import_no_tour_id.csv"><?php echo Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSVFILE_SAMPLE'); ?></a>
            </fieldset>
          </div>
          <!-- TODO make sample.csv file available -->
        </div>
      </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
  </form>
<?php endif; ?>
