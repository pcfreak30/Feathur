<?php
include('./includes/loader.php');
session_write_close();

$uTemplate = $_GET['template'];

if ($sTemplate = $database->CachedQuery("SELECT * FROM `templates` WHERE `path` = :Template", array("Template" => $uTemplate))) {
    $sTemplate = new Template($sTemplate->data[0]["id"]);
    if (!file_exists("/var/feathur/data/templates/kvm/{$sTemplate->uPath}.iso")) {
        die("Missing template file.");
    }
    header('Content-type: application/iso');
    header('Content-Disposition: attachment; filename="' . $sTemplate->uPath . '.iso"');
    header("Content-Disposition:  X-Accel-Redirect: /os_templates/{$sTemplate->uPath}.iso");
} else {
    die("Invalid template name.");
}