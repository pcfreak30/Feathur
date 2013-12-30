<?php
include('./includes/loader.php');

$sCurrentVersion = Core::GetSetting('current_version');

if (version_compare($sCurrentVersion->sValue, '0.6.1.9', '<')) {
    $sAdd = $database->prepare("ALTER TABLE `servers` ADD `qemu_path` VARCHAR(130);");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `virtio_network` INT(2)");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `virtio_disk` INT(2)");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `mac` TEXT");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `vnc_port` INT(16)");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `boot_order` VARCHAR(65)");
    $sAdd->execute();

    $sAdd = $database->prepare("CREATE TABLE IF NOT EXISTS `attempts` (`id` INT( 16 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , `ip_address` VARCHAR( 65 ) NOT NULL , `timestamp` INT( 16 ) NOT NULL , `type` VARCHAR( 65 ) NOT NULL) ENGINE = MYISAM ;");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `blocks` CHANGE `netblock` `netmask` VARCHAR(65)");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `rebuilding` INT(2)");
    $sAdd->execute();
}

if (version_compare($sCurrentVersion->sValue, '0.6.2.0', '<')) {
    $sAdd = $database->prepare("ALTER TABLE `vps` CHANGE `virtio_disk` `disk_driver` VARCHAR(65)");
    $sAdd->execute();

    $sAdd = $database->prepare("ALTER TABLE `vps` CHANGE `virtio_network` `network_driver` VARCHAR(65)");
    $sAdd->execute();

    if (!$sFindSetting = $database->CachedQuery("SELECT * FROM settings WHERE `setting_name` LIKE :Setting", array('Setting' => "refresh_time"))) {
        $sAdd = $database->prepare("INSERT INTO settings(setting_name, setting_value, setting_group) VALUES('refresh_time', '10', 'site_settings')");
        $sAdd->execute();
    }

    $sAdd = $database->prepare("ALTER TABLE `vps` ADD `private_network` INT(2);");
    $sAdd->execute();

    if (!$sFindSetting = $database->CachedQuery("SELECT * FROM settings WHERE `setting_name` LIKE :Setting", array('Setting' => "panel_mode"))) {
        $sAdd = $database->prepare("INSERT INTO settings(setting_name, setting_value, setting_group) VALUES('panel_mode', 'https://', 'site_settings')");
        $sAdd->execute();
    }
}

if (version_compare($sCurrentVersion->sValue, '0.6.3.0', '<')) {
    $sAdd = $database->prepare("UPDATE `settings` SET `setting_name` = 'mail' WHERE `setting_name` = 'sendgrid'");
    $sAdd->execute();

    $sAdd = $database->prepare("UPDATE `settings` SET `setting_name` = 'mail_username' WHERE `setting_name` = 'sendgrid_username'");
    $sAdd->execute();

    $sAdd = $database->prepare("UPDATE `settings` SET `setting_name` = 'mail_password' WHERE `setting_name` = 'sendgrid_password'");
    $sAdd->execute();
}

$sAdd = $database->prepare("ALTER TABLE `vps` ADD `last_bandwidth` decimal(65,4)");
$sAdd->execute();

$sAdd = $database->prepare("ALTER TABLE `blocks` CHANGE `bandwidth_usage` `bandwidth_usage` decimal(65,4)");
$sAdd->execute();