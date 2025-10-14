<?php
/**
 * Mapping Template
 *
 * @package SIT\Search
 */

$zoho = new SIT\Search\Services\Zoho();

$university_fields = $zoho->get_fields('Accounts') ?? [];
$program_fields = $zoho->get_fields('Products') ?? [];

$university_enabled_fields = get_option('university_enabled_fields', []);
$program_enabled_fields = get_option('program_enabled_fields', []);

$zoho_configured = !empty($university_fields) || !empty($program_fields);

if (isset($_POST['sync_fields'])) {
    $university_enabled_fields = $_POST['university_enabled_fields'];
    $program_enabled_fields = $_POST['program_enabled_fields'];

    update_option('university_enabled_fields', $university_enabled_fields);
    update_option('program_enabled_fields', $program_enabled_fields);
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Fields Mapping</h1>
    <a href="<?= admin_url('admin.php?page=sit-search-mapping') ?>" class="page-title-action">Refresh Fields</a>
    
    <?php if (!$zoho_configured): ?>
        <div class="notice notice-warning">
            <p><strong>⚠️ Zoho API Not Configured</strong></p>
            <p>To fetch fields from Zoho CRM, you need to:</p>
            <ol>
                <li>Install and activate <strong>Advanced Custom Fields (ACF)</strong> plugin</li>
                <li>Configure Zoho API credentials in ACF Options</li>
                <li>Refresh this page to load fields</li>
            </ol>
            <p>For now, the plugin will work with Spain customizations but won't sync data from Zoho.</p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tbody>
            <tr class="user-rich-editing-wrap">
                <th scope="row">University Fields</th>
                <td class="flex-fields">
                    <?php if (empty($university_fields)): ?>
                        <p class="description">No fields available. Configure Zoho API credentials first.</p>
                    <?php else: ?>
                    <?php
                    foreach ($university_fields as $field) {
                        ?>
                        <label for="<?= $field['field_name'] ?>">
                            <input name="university_enabled_fields[]" type="checkbox" id="<?= $field['field_name'] ?>" value="<?= $field['field_name'] ?>"
                                <?= $university_enabled_fields ? checked(in_array($field['field_name'], $university_enabled_fields), true) : '' ?>>
                            <?php echo $field['field_label']; ?>
                        </label>
                        <span>|</span>
                        <?php
                    }
                    ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="user-rich-editing-wrap">
                <th scope="row">Program Fields</th>
                <td class="flex-fields">
                    <?php if (empty($program_fields)): ?>
                        <p class="description">No fields available. Configure Zoho API credentials first.</p>
                    <?php else: ?>
                    <?php
                    foreach ($program_fields as $field) {
                        ?>
                        <label for="<?= $field['field_name'] ?>">
                            <input name="program_enabled_fields[]" type="checkbox" id="<?= $field['field_name'] ?>" value="<?= $field['field_name'] ?>"
                                <?= !empty($program_enabled_fields) ? checked(in_array($field['field_name'], $program_enabled_fields), true) : '' ?> />
                            <?php echo $field['field_label']; ?>
                        </label>
                        <span>|</span>
                        <?php
                    }
                    ?>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="sync_fields" id="submit" class="button button-primary" value="Sync Fields" <?= !$zoho_configured ? 'disabled' : '' ?>>
        </p>
    </form>
</div>