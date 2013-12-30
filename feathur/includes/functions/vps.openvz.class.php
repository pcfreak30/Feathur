<?php

class openvz
{

    public function database_openvz_create($sUser, $sRequested, $sAPI = 0)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            global $database;
            global $sRequested;
            $uServer = $sRequested["POST"]["server"];
            $uUser = $sRequested["POST"]["user"];
            $uTemplate = $sRequested["POST"]["template"];
            $uRAM = $sRequested["POST"]["ram"];
            $uSWAP = $sRequested["POST"]["swap"];
            $uDisk = $sRequested["POST"]["disk"];
            $uInodes = $sRequested["POST"]["inodes"];
            $uNumProc = $sRequested["POST"]["numproc"];
            $uNumIPTent = $sRequested["POST"]["numiptent"];
            $uIPAddresses = $sRequested["POST"]["ipaddresses"];
            $uHostname = $sRequested["POST"]["hostname"];
            $uNameserver = $sRequested["POST"]["nameserver"];
            $uPassword = $sRequested["POST"]["password"];
            $uCPUUnits = $sRequested["POST"]["cpuunits"];
            $uCPULimit = $sRequested["POST"]["cpulimit"];
            $uBandwidthLimit = $sRequested["POST"]["bandwidthlimit"];
            if ((!empty($uServer)) && (is_numeric($uServer))) {
                if ((!empty($uUser)) && (is_numeric($uUser))) {
                    if ((!empty($uTemplate)) && (is_numeric($uTemplate))) {
                        if ((!empty($uRAM)) && (is_numeric($uRAM))) {
                            if ((!empty($uSWAP)) && (is_numeric($uSWAP))) {
                                if ((!empty($uDisk)) && (is_numeric($uDisk))) {
                                    if ((!empty($uInodes)) && (is_numeric($uInodes))) {
                                        if ((!empty($uNumProc)) && (is_numeric($uNumProc))) {
                                            if ((!empty($uNumIPTent)) && (is_numeric($uNumIPTent))) {
                                                if ((!empty($uInodes)) && (is_numeric($uInodes))) {
                                                    if ((!empty($uCPUUnits)) && (is_numeric($uCPUUnits))) {
                                                        if ((!empty($uCPULimit)) && (is_numeric($uCPULimit))) {
                                                            if ((!empty($uBandwidthLimit)) && (is_numeric($uBandwidthLimit))) {
                                                                $sServer = new Server($uServer);
                                                                $sOwner = new User($uUser);
                                                                $sTemplate = new Template($uTemplate);

                                                                if ($uIPAddressess > 0) {
                                                                    $sIPCheck = VPS::check_ipspace($sServer->sId, $uIPAddresses);
                                                                    if (is_array($sIPCheck)) {
                                                                        return $sIPCheck;
                                                                    }
                                                                }

                                                                if (!is_array($sSSH)) {
                                                                    if (empty($uHostname)) {
                                                                        $uHostname = "vps.example.com";
                                                                    }

                                                                    if (empty($uNameserver)) {
                                                                        $uNameserver = "8.8.8.8";
                                                                    }

                                                                    // VPS Database setup
                                                                    $sVPSId = Core::GetSetting('container_id');
                                                                    $sUpdate = Core::UpdateSetting('container_id', ($sVPSId->sValue + 1));
                                                                    $sVPS = new VPS(0);
                                                                    $sVPS->uType = $sServer->sType;
                                                                    $sVPS->uHostname = $uHostname;
                                                                    $sVPS->uNameserver = $uNameserver;
                                                                    $sVPS->uUserId = $sOwner->sId;
                                                                    $sVPS->uServerId = $sServer->sId;
                                                                    $sVPS->uContainerId = $sVPSId->sValue;
                                                                    $sVPS->uNumIPTent = $uNumIPTent;
                                                                    $sVPS->uNumProc = $uNumProc;
                                                                    $sVPS->uInodes = $uInodes;
                                                                    $sVPS->uRAM = $uRAM;
                                                                    $sVPS->uSWAP = $uSWAP;
                                                                    $sVPS->uDisk = $uDisk;
                                                                    $sVPS->uCPUUnits = $uCPUUnits;
                                                                    $sVPS->uCPULimit = $uCPULimit;
                                                                    $sVPS->uTemplateId = $sTemplate->sId;
                                                                    $sVPS->uBandwidthLimit = $uBandwidthLimit;
                                                                    $sVPS->InsertIntoDatabase();

                                                                    if ($uIPAddresses > 0) {
                                                                        if ($sBlocks = $database->CachedQuery("SELECT * FROM server_blocks WHERE `server_id` = :ServerId", array('ServerId' => $sServer->sId))) {
                                                                            foreach ($sBlocks->data as $key => $value) {
                                                                                if ($sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `block_id` = :BlockId AND `vps_id` = 0", array('BlockId' => $value["block_id"]))) {
                                                                                    foreach ($sIPs->data as $subvalue) {
                                                                                        if ($sCurrentIPs < $uIPAddresses) {
                                                                                            $sIPList[] = array("id" => $subvalue["id"], "ip_address" => $subvalue["ip_address"]);
                                                                                            $sUpdate = $database->CachedQuery("UPDATE ipaddresses SET `vps_id` = :VPSId WHERE `id` = :Id", array('VPSId' => $sVPS->sId, 'Id' => $subvalue["id"]));
                                                                                            if (empty($sFirst)) {
                                                                                                $sVPS->uPrimaryIP = $subvalue["ip_address"];
                                                                                                $sVPS->InsertIntoDatabase();
                                                                                                $sFirst = 1;
                                                                                            }
                                                                                            $sCurrentIPs++;
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        $sRequested["POST"]["IPList"] = $sIPList;
                                                                    }

                                                                    $sRequested["POST"]["VPS"] = $sVPS->sId;
                                                                    $sRequested["POST"]["IPList"] = $sIPList;

                                                                    if (!empty($sAPI)) {
                                                                        return $sVPS->sId;
                                                                    }
                                                                    return true;
                                                                } else {
                                                                    return $sArray = array("json" => 1, "type" => "error", "result" => "Unable to connect to the node selected!");
                                                                }
                                                            } else {
                                                                return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input a bandwidth limit!");
                                                            }
                                                        } else {
                                                            return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the CPU limit!");
                                                        }
                                                    } else {
                                                        return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the number of CPU units!");
                                                    }
                                                } else {
                                                    return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the number of inodes");
                                                }
                                            } else {
                                                return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum number of connections to create a VPS!");
                                            }
                                        } else {
                                            return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum number of processes to create a VPS!");
                                        }
                                    } else {
                                        return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum inodes to create a VPS!");
                                    }
                                } else {
                                    return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum disk to create a VPS!");
                                }
                            } else {
                                return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum SWAP to create a VPS!");
                            }
                        } else {
                            return $sArray = array("json" => 1, "type" => "caution", "result" => "You must input the maximum RAM to create a VPS!");
                        }
                    } else {
                        return $sArray = array("json" => 1, "type" => "caution", "result" => "You must select a template to create a VPS!");
                    }
                } else {
                    return $sArray = array("json" => 1, "type" => "caution", "result" => "You must specify a user to create a VPS!");
                }
            } else {
                return $sArray = array("json" => 1, "type" => "caution", "result" => "You must specify a server to create a VPS!");
            }
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Permissions invalid for selected action.");
        }
    }

    public function openvz_create($sUser, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sVPS = new VPS($sRequested["POST"]["VPS"]);
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sTemplate = new Template($sVPS->sTemplateId);
            $sPassword = escapeshellarg($sRequested["POST"]["password"]);
            $sIPList = $sRequested["POST"]["IPList"];
            $sHighDisk = $sVPS->sDisk + 1;

            $sCommandList .= "vzctl create {$sVPS->sContainerId} --ostemplate {$sTemplate->sPath};";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --onboot yes --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --ram {$sVPS->sRAM}M --swap {$sVPS->sSWAP}M --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpuunits {$sVPS->sCPUUnits} --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpulimit {$sVPS->sCPULimit} --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpus {$sCPUs} --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --diskspace {$sVPS->sDisk}G:{$sHighDisk}G --save;";
            $sCommandList .= "vzctl start {$sVPS->sContainerId}";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --nameserver {$sVPS->sNameserver} --save;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --hostname {$sVPS->sHostname} --save;";
            $sCommandList .= "modprobe tun;vzctl set {$sVPS->sContainerId} --devnodes net/tun:rw --save;vzctl set {$sVPS->sContainerId} --devices c:10:200:rw --save;vzctl set {$sVPS->sContainerId} --capability net_admin:on --save;vzctl exec {$sVPS->sContainerId} mkdir -p /dev/net;vzctl exec {$sVPS->sContainerId} mknod /dev/net/tun c 10 200;";
            $sCommandList .= "modprobe iptables_module;modprobe ipt_helper;modprobe ipt_REDIRECT;modprobe ipt_mark;modprobe ipt_MARK;modprobe ipt_TCPMSS;modprobe ipt_LOG;modprobe ipt_TOS;modprobe iptable_nat;modprobe ipt_length;modprobe ipt_tcpmss;modprobe iptable_mangle;modprobe ipt_tos;modprobe iptable_filter;modprobe ipt_helper;modprobe ipt_tos;modprobe ipt_ttl;modprobe ipt_SAME;modprobe ipt_REJECT;modprobe ipt_helper;modprobe ipt_owner;modprobe ip_tables;modprobe ipt_MASQUERADE;modprobe ipt_multiport/xt_multiport;modprobe ipt_state/xt_state;modprobe ipt_limit/xt_limit;modprobe ipt_recent;modprobe xt_connlimit;modprobe ipt_owner/xt_owner;modprobe iptable_nat/ipt_DNAT;modprobe iptable_nat/ipt_REDIRECT;";
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --iptables ipt_REJECT --iptables ipt_tos --iptables ipt_TOS --iptables ipt_LOG --iptables ip_conntrack --iptables ipt_limit --iptables ipt_multiport --iptables iptable_filter --iptables iptable_mangle --iptables ipt_TCPMSS --iptables ipt_tcpmss --iptables ipt_ttl --iptables ipt_length --iptables ipt_state --iptables iptable_nat --iptables ip_nat_ftp --save;";

            if (!empty($sPassword)) {
                $sCommandList .= "vzctl set {$sVPS->sContainerId} --userpasswd root:{$sPassword} --save;";
            }
            if (!empty($sIPList)) {
                foreach ($sIPList as $key => $value) {
                    $sCommandList .= "vzctl set {$sVPS->sContainerId} --ipadd {$value['ip_address']} --save;";
                }
            }

            $sCommandList .= "vzctl stop {$sVPS->sContainerId};";
            $sCommandList .= "vzctl start {$sVPS->sContainerId};";

            $sLog[] = array("command" => str_replace($sPassword, "obfuscated", $sCommandList), "result" => $sSSH->exec($sCommandList));

            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS has been created!", "reload" => 1, "vps" => $sVPS->sId);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Permissions invalid for selected action.");
        }
    }

    public function database_openvz_boot($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_boot($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl start {$sVPS->sContainerId};", "result" => $sSSH->exec("vzctl start {$sVPS->sContainerId};"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        if (strpos($sLog[0]["result"], 'Container is already running') !== false) {
            return $sArray = array("json" => 1, "type" => "caution", "result" => "VPS is already running!");
        } elseif (strpos($sLog[0]["result"], 'Container start disabled') !== false) {
            return $sArray = array("json" => 1, "type" => "error", "result" => "VPS is disabled, contact support!");
        } elseif (strpos($sLog[0]["result"], 'inode_hard_limit') !== false) {
            return $sArray = array("json" => 1, "type" => "error", "result" => "VPS is out of inodes, contact support!");
        } elseif (strpos($sLog[0]["result"], 'Container start in progress...') !== false) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS is currently starting up...");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "An unknown error occured, contact support!");
        }
    }

    public function database_openvz_shutdown($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_shutdown($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl stop {$sVPS->sContainerId};", "result" => $sSSH->exec("vzctl stop {$sVPS->sContainerId};"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        if (strpos($sLog[0]["result"], 'Container was stopped') !== false) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS is currently shutting down...");
        } elseif (strpos($sLog[0]["result"], 'container is not running') !== false) {
            return $sArray = array("json" => 1, "type" => "caution", "result" => "VPS is already shutdown!");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "An unknown error occured, contact support!");
        }
    }

    public function database_openvz_reboot($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_reboot($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl restart {$sVPS->sContainerId};", "result" => $sSSH->exec("vzctl restart {$sVPS->sContainerId};"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        if (strpos($sLog[0]["result"], 'Container is already running') !== false) {
            return $sArray = array("json" => 1, "type" => "caution", "result" => "VPS is already running!");
        } elseif (strpos($sLog[0]["result"], 'Container start disabled') !== false) {
            return $sArray = array("json" => 1, "type" => "error", "result" => "VPS is disabled, contact support!");
        } elseif (strpos($sLog[0]["result"], 'inode_hard_limit') !== false) {
            return $sArray = array("json" => 1, "type" => "error", "result" => "VPS is out of inodes, contact support!");
        } elseif (strpos($sLog[0]["result"], 'Starting container...') !== false) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS is currently rebooting...");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "An unknown error occured, contact support!");
        }
    }

    public function database_openvz_kill($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_kill($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl stop {$sVPS->sContainerId} --fast;", "result" => $sSSH->exec("vzctl stop {$sVPS->sContainerId} --fast;"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        if (strpos($sLog[0]["result"], 'Container was stopped') !== false) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS is being forcefully shutdown...");
        } elseif (strpos($sLog[0]["result"], 'container is not running') !== false) {
            return $sArray = array("json" => 1, "type" => "caution", "result" => "VPS is already shutdown!");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "An unknown error occured, contact support!");
        }
    }

    public function database_openvz_suspend($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sVPS->uSuspended = 1;
            $sVPS->uSuspendingAdmin = $sUser->sId;
            $sVPS->InsertIntoDatabase();
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Permissions invalid for selected action.");
        }
    }

    public function openvz_suspend($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "vzctl stop {$sVPS->sContainerId} --fast", "result" => $sSSH->exec("vzctl stop {$sVPS->sContainerId} --fast"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --disabled yes --save", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --disabled yes --save"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "User's VPS has been suspended!", "reload" => 1);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "You might want to try a different profession.", "reload" => 1);
        }
    }

    public function database_openvz_unsuspend($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sVPS->uSuspended = 0;
            $sVPS->uSuspendingAdmin = $sUser->sId;
            $sVPS->InsertIntoDatabase();
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Permissions invalid for selected action.");
        }
    }

    public function openvz_unsuspend($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --disabled no --save", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --disabled no --save"));
            $sLog[] = array("command" => "vzctl start {$sVPS->sContainerId}", "result" => $sSSH->exec("vzctl start {$sVPS->sContainerId}"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "User's VPS has been unsuspended!", "reload" => 1);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "You might want to try a different profession.", "reload" => 1);
        }
    }

    public function database_openvz_password($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_password($sUser, $sVPS, $sRequested)
    {
        if (!empty($sRequested["POST"]["password"])) {
            $sPassword = escapeshellarg($sRequested["POST"]["password"]);
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --userpasswd root:obfuscated;", "result" => $sSSH->exec(escapeshellcmd("vzctl set {$sVPS->sContainerId} --userpasswd root:{$sPassword} --save")));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "Your VPS password has been updated.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Password can not be blank!");
        }
    }

    public function database_openvz_primaryip($sUser, $sVPS, $sRequested)
    {
        $sIP = new IP($sRequested["GET"]["ip"]);
        if ($sIP->sVPSId == $sVPS->sId) {
            $sVPS->uPrimaryIP = $sIP->sIPAddress;
            $sVPS->InsertIntoDatabase();
            return $sArray = array("json" => 1, "type" => "success", "result" => "{$sIP->sIPAddress} is now your primary IP.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "That IP does not belong to you.");
        }
    }

    public function openvz_primaryip($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_getrdns($sUser, $sVPS, $sRequested)
    {
        $sIP = new IP($sRequested["GET"]["ip"]);
        if ($sIP->sVPSId == $sVPS->sId) {
            return $sArray = RDNS::pull_rdns($sIP);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "That IP does not belong to you.");
        }
    }

    public function openvz_getrdns($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_setrdns($sUser, $sVPS, $sRequested)
    {
        $sIP = new IP($sRequested["GET"]["ip"]);
        if ($sIP->sVPSId == $sVPS->sId) {
            return RDNS::add_rdns($sIP, $sRequested["GET"]["hostname"]);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "That IP does not belong to you.");
        }
    }

    public function openvz_setrdns($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_tuntap($sUser, $sVPS, $sRequested)
    {
        if (is_numeric($sRequested["GET"]["setting"])) {
            $sVPS->uTunTap = $sRequested["GET"]["setting"];
            $sVPS->InsertIntoDatabase();
            return true;
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function openvz_tuntap($sUser, $sVPS, $sRequested)
    {
        $sSetting = $sRequested["GET"]["setting"];
        if ($sSetting == 0) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "TUN/TAP has been disabled.");
        } elseif ($sSetting == 1) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "modprobe tun;vzctl set {$sVPS->sContainerId} --devnodes net/tun:rw --save;vzctl set {$sVPS->sContainerId} --devices c:10:200:rw --save;vzctl set {$sVPS->sContainerId} --capability net_admin:on --save;vzctl exec {$sVPS->sContainerId} mkdir -p /dev/net;vzctl exec {$sVPS->sContainerId} mknod /dev/net/tun c 10 200;", "result" => $sSSH->exec("modprobe tun;vzctl set {$sVPS->sContainerId} --devnodes net/tun:rw --save;vzctl set {$sVPS->sContainerId} --devices c:10:200:rw --save;vzctl set {$sVPS->sContainerId} --capability net_admin:on --save;vzctl exec {$sVPS->sContainerId} mkdir -p /dev/net;vzctl exec {$sVPS->sContainerId} mknod /dev/net/tun c 10 200;"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "TUN/TAP has been enabled.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function database_openvz_ppp($sUser, $sVPS, $sRequested)
    {
        if (is_numeric($sRequested["GET"]["setting"])) {
            $sVPS->uPPP = $sRequested["GET"]["setting"];
            $sVPS->InsertIntoDatabase();
            return true;
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function openvz_ppp($sUser, $sVPS, $sRequested)
    {
        $sSetting = $sRequested["GET"]["setting"];
        if ($sSetting == 0) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "PPP has been disabled.");
        } elseif ($sSetting == 1) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "modprobe ppp_async;modprobe ppp_deflate;modprobe ppp_mppe;vzctl stop {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --features ppp:on --save;vzctl start {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --devices c:108:0:rw --save;vzctl exec {$sVPS->sContainerId} mknod /dev/ppp c 108 0;vzctl exec {$sVPS->sContainerId} chmod 600 /dev/ppp;", "result" => $sSSH->exec("modprobe ppp_async;modprobe ppp_deflate;modprobe ppp_mppe;vzctl stop {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --features ppp:on --save;vzctl start {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --devices c:108:0:rw --save;vzctl exec {$sVPS->sContainerId} mknod /dev/ppp c 108 0;vzctl exec {$sVPS->sContainerId} chmod 600 /dev/ppp;"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "PPP has been enabled.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function database_openvz_iptables($sUser, $sVPS, $sRequested)
    {
        if (is_numeric($sRequested["GET"]["setting"])) {
            $sVPS->uIPTables = $sRequested["GET"]["setting"];
            $sVPS->InsertIntoDatabase();
            return true;
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function openvz_iptables($sUser, $sVPS, $sRequested)
    {
        $sSetting = $sRequested["GET"]["setting"];
        if ($sSetting == 0) {
            return $sArray = array("json" => 1, "type" => "success", "result" => "IP Tables have been disabled.");
        } elseif ($sSetting == 1) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "modprobe iptables_module;modprobe ipt_mark;modprobe ipt_MARK;modprobe ipt_helper;modprobe ipt_REDIRECT;modprobe ipt_TCPMSS;modprobe ipt_LOG;modprobe ipt_TOS;modprobe iptable_nat;modprobe ipt_length;modprobe ipt_tcpmss;modprobe iptable_mangle;modprobe ipt_tos;modprobe iptable_filter;modprobe ipt_helper;modprobe ipt_tos;modprobe ipt_ttl;modprobe ipt_SAME;modprobe ipt_REJECT;modprobe ipt_helper;modprobe ipt_owner;modprobe ip_tables;modprobe ipt_MASQUERADE;modprobe ipt_multiport/xt_multiport;modprobe ipt_state/xt_state;modprobe ipt_limit/xt_limit;modprobe ipt_recent;modprobe xt_connlimit;modprobe ipt_owner/xt_owner;modprobe iptable_nat/ipt_DNAT;modprobe iptable_nat/ipt_REDIRECT;", "result" => $sSSH->exec("modprobe iptables_module;modprobe ipt_helper;modprobe ipt_REDIRECT;modprobe ipt_TCPMSS;modprobe ipt_LOG;modprobe ipt_TOS;modprobe iptable_nat;modprobe ipt_length;modprobe ipt_tcpmss;modprobe iptable_mangle;modprobe ipt_tos;modprobe iptable_filter;modprobe ipt_helper;modprobe ipt_tos;modprobe ipt_ttl;modprobe ipt_SAME;modprobe ipt_REJECT;modprobe ipt_helper;modprobe ipt_owner;modprobe ip_tables;modprobe ipt_MASQUERADE;modprobe ipt_multiport/xt_multiport;modprobe ipt_state/xt_state;modprobe ipt_limit/xt_limit;modprobe ipt_recent;modprobe xt_connlimit;modprobe ipt_owner/xt_owner;modprobe iptable_nat/ipt_DNAT;modprobe iptable_nat/ipt_REDIRECT;"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --iptables ipt_REJECT --iptables ipt_tos --iptables ipt_TOS --iptables ipt_LOG --iptables ip_conntrack --iptables ipt_limit --iptables ipt_multiport --iptables iptable_filter --iptables iptable_mangle --iptables ipt_TCPMSS --iptables ipt_tcpmss --iptables ipt_ttl --iptables ipt_length --iptables ipt_state --iptables iptable_nat --iptables ip_nat_ftp --save", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --iptables ipt_REJECT --iptables ipt_tos --iptables ipt_TOS --iptables ipt_LOG --iptables ip_conntrack --iptables ipt_limit --iptables ipt_multiport --iptables iptable_filter --iptables iptable_mangle --iptables ipt_TCPMSS --iptables ipt_tcpmss --iptables ipt_ttl --iptables ipt_length --iptables ipt_state --iptables iptable_nat --iptables ip_nat_ftp --save"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "IP Tables have been enabled.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid setting modifier.");
        }
    }

    public function database_openvz_hostname($sUser, $sVPS, $sRequested)
    {
        $sHostname = preg_replace('/[^A-Za-z0-9-.]/', '', $sRequested["GET"]["hostname"]);
        if (!empty($sHostname)) {
            $sVPS->uHostname = $sHostname;
            $sVPS->InsertIntoDatabase();
            return true;
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Hostname can not be blank!");
        }
    }

    public function openvz_hostname($sUser, $sVPS, $sRequested)
    {
        $sHostname = escapeshellarg($sVPS->sHostname);
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --hostname {$sHostname} --save", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --hostname {$sHostname} --save"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        return $sArray = array("json" => 1, "type" => "success", "result" => "Hostname has been updated successfully.");
    }

    public function database_openvz_rebuild($sUser, $sVPS, $sRequested)
    {
        if (is_numeric($sRequested["GET"]["template"])) {
            $sVPS->uTemplateId = $sRequested["GET"]["template"];
            $sVPS->uRebuilding = 1;
            $sVPS->InsertIntoDatabase();
            return true;
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Invalid template selected, please try again.");
        }
    }

    public function openvz_rebuild($sUser, $sVPS, $sRequested)
    {
        global $database;
        global $locale;
        global $sTemplate;
        $sOpenVZTemplate = new Template($sVPS->sTemplateId);
        $sHighDisk = $sVPS->sDisk + 1;
        $sCPUs = (($sVPS->sCPULimit) / 100);
        $sCommandList .= "vzctl stop {$sVPS->sContainerId} --fast;";
        $sCommandList .= "vzctl destroy {$sVPS->sContainerId};";
        $sCommandList .= "vzctl create {$sVPS->sContainerId} --ostemplate {$sOpenVZTemplate->sPath};";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --onboot yes --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --ram {$sVPS->sRAM}M --swap {$sVPS->sSWAP}M --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpuunits {$sVPS->sCPUUnits} --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpus {$sCPUs} --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --diskinodes {$sVPS->sInodes}:{$sVPS->sInodes} --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --cpulimit {$sVPS->sCPULimit} --save;";
        $sCommandList .= "modprobe tun;vzctl set {$sVPS->sContainerId} --devnodes net/tun:rw --save;vzctl set {$sVPS->sContainerId} --devices c:10:200:rw --save;vzctl set {$sVPS->sContainerId} --capability net_admin:on --save;vzctl exec {$sVPS->sContainerId} mkdir -p /dev/net;vzctl exec {$sVPS->sContainerId} mknod /dev/net/tun c 10 200;";
        $sCommandList .= "modprobe ppp_async;modprobe ppp_deflate;modprobe ppp_mppe;vzctl stop {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --features ppp:on --save;vzctl start {$sVPS->sContainerId};vzctl set {$sVPS->sContainerId} --devices c:108:0:rw --save;vzctl exec {$sVPS->sContainerId} mknod /dev/ppp c 108 0;vzctl exec {$sVPS->sContainerId} chmod 600 /dev/ppp;";
        $sCommandList .= "modprobe iptables_module;modprobe ipt_helper;modprobe ipt_REDIRECT;modprobe ipt_TCPMSS;modprobe ipt_LOG;modprobe ipt_TOS;modprobe iptable_nat;modprobe ipt_length;modprobe ipt_tcpmss;modprobe iptable_mangle;modprobe ipt_mark;modprobe ipt_MARK;modprobe ipt_tos;modprobe iptable_filter;modprobe ipt_helper;modprobe ipt_tos;modprobe ipt_ttl;modprobe ipt_SAME;modprobe ipt_REJECT;modprobe ipt_helper;modprobe ipt_owner;modprobe ip_tables;modprobe ipt_MASQUERADE;modprobe ipt_multiport/xt_multiport;modprobe ipt_state/xt_state;modprobe ipt_limit/xt_limit;modprobe ipt_recent;modprobe xt_connlimit;modprobe ipt_owner/xt_owner;modprobe iptable_nat/ipt_DNAT;modprobe iptable_nat/ipt_REDIRECT;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --iptables ipt_REJECT --iptables ipt_tos --iptables ipt_TOS --iptables ipt_LOG --iptables ip_conntrack --iptables ipt_limit --iptables ipt_multiport --iptables iptable_filter --iptables iptable_mangle --iptables ipt_TCPMSS --iptables ipt_tcpmss --iptables ipt_ttl --iptables ipt_length --iptables ipt_state --iptables iptable_nat --iptables ip_nat_ftp --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --nameserver {$sVPS->sNameserver} --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --diskspace {$sVPS->sDisk}G:{$sHighDisk}G --save;";
        $sCommandList .= "vzctl set {$sVPS->sContainerId} --hostname {$sVPS->sHostname} --save;";

        $sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
        foreach ($sIPs->data as $key => $value) {
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --ipadd {$value['ip_address']} --save;";
        }

        if (!empty($sPassword)) {
            $sPassword = escapeshellarg($sRequested["POST"]["password"]);
            $sCommandList .= "vzctl set {$sVPS->sContainerId} --userpasswd root:{$sPassword} --save;";
        }

        $sCommandList .= "vzctl stop {$sVPS->sContainerId};";
        $sCommandList .= "vzctl start {$sVPS->sContainerId};";
        $sCommandList .= "mkdir /vz/feathur_tmp/;echo \"{$sVPS->sId}\" > /vz/feathur_tmp/{$sVPS->sContainerId}.finished";

        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sCommandList = escapeshellarg($sCommandList);
        $sLog[] = array("command" => "Screened Rebuild", "result" => $sSSH->exec("screen -dm -S {$sVPS->sContainerId} bash -c {$sCommandList};"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        $sUserView .= Templater::AdvancedParse($sTemplate->sValue . '/rebuild', $locale->strings, array("VPS" => array("data" => $sVPS->uData)));
        return $sArray = array("json" => 1, "type" => "success", "result" => $sUserView);
    }

    public function database_openvz_rebuildcheck($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "Screened Rebuild Check", "result" => $sSSH->exec("cat /vz/feathur_tmp/{$sVPS->sContainerId}.finished"));
        if (strpos($sLog[0]["result"], $sVPS->sId) !== false) {
            $sVPS->uRebuilding = 0;
            $sVPS->InsertIntoDatabase();
            $sLog[] = array("command" => "Screened Rebuild Check", "result" => $sSSH->exec("rm -rf /vz/feathur_tmp/{$sVPS->sContainerId}.finished"));
            return $sArray = array("json" => 1, "type" => "success", "reload" => 1, "result" => "Rebuild Completed!");
        } else {
            return $sArray = array("json" => 1, "type" => "pending", "reload" => 0, "result" => "Rebuild Pending!");
        }
    }

    public function database_openvz_console($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_console($sUser, $sVPS, $sRequested)
    {
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sCommand = escapeshellarg($sRequested["GET"]["command"]);
        $sLog[] = array("command" => "vzctl exec {$sVPS->sContainerId} {$sCommand}", "result" => $sSSH->exec("vzctl exec {$sVPS->sContainerId} {$sCommand}"));
        $sSave = VPS::save_vps_logs($sLog, $sVPS);
        return $sArray = array("result" => nl2br($sLog[0]["result"]), "type" => "success", "json" => 1);
    }

    public function openvz_rebuildcheck($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_update($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sRAM = $sRequested["GET"]["ram"];
            $sSWAP = $sRequested["GET"]["swap"];
            $sDisk = $sRequested["GET"]["disk"];
            $sCPUUnits = $sRequested["GET"]["cpuunits"];
            $sCPULimit = $sRequested["GET"]["cpulimit"];
            $sBandwidth = $sRequested["GET"]["bandwidth"];
            $sInodes = $sRequested["GET"]["inodes"];
            if (is_numeric($sRAM)) {
                if (is_numeric($sDisk)) {
                    if (is_numeric($sCPUUnits)) {
                        if (is_numeric($sCPULimit)) {
                            if (is_numeric($sSWAP)) {
                                if (is_numeric($sBandwidth)) {
                                    if (is_numeric($sInodes)) {
                                        $sVPS->uRAM = $sRAM;
                                        $sVPS->uSWAP = $sSWAP;
                                        $sVPS->uDisk = $sDisk;
                                        $sVPS->uCPUUnits = $sCPUUnits;
                                        $sVPS->uBandwidthLimit = $sBandwidth;
                                        $sVPS->uCPULimit = $sCPULimit;
                                        $sVPS->uInodes = $sInodes;
                                        $sVPS->InsertIntoDatabase();
                                        return true;
                                    } else {
                                        return $sArray = array("json" => 1, "type" => "error", "result" => "Inodes must be numeric.");
                                    }
                                } else {
                                    return $sArray = array("json" => 1, "type" => "error", "result" => "Bandwidth must be numeric.");
                                }
                            } else {
                                return $sArray = array("json" => 1, "type" => "error", "result" => "SWAP must be numeric.");
                            }
                        } else {
                            return $sArray = array("json" => 1, "type" => "error", "result" => "CPU limit must be numeric.");
                        }
                    } else {
                        return $sArray = array("json" => 1, "type" => "error", "result" => "CPU units must be numeric.");
                    }
                } else {
                    return $sArray = array("json" => 1, "type" => "error", "result" => "Disk must be numeric.");
                }
            } else {
                return $sArray = array("json" => 1, "type" => "error", "result" => "RAM must be numeric.");
            }
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function openvz_update($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sHighDisk = $sVPS->sDisk + 1;
            $sCPUs = (($sVPS->sCPULimit) / 100);

            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --ram {$sVPS->sRAM}M --swap {$sVPS->sSWAP}M --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --ram {$sVPS->sRAM}M --swap {$sVPS->sSWAP}M --save;"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --cpuunits {$sVPS->sCPUUnits} --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --cpuunits {$sVPS->sCPUUnits} --save;"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --cpulimit {$sVPS->sCPULimit} --cpus {$sCPUs} --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --cpulimit {$sVPS->sCPULimit} --cpus {$sCPUs} --save;"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --diskspace {$sVPS->sDisk}G:{$sHighDisk}G --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --diskspace {$sVPS->sDisk}G:{$sHighDisk}G --save;"));
            $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --diskinodes {$sVPS->sInodes}:{$sVPS->sInodes} --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --diskinodes {$sVPS->sInodes}:{$sVPS->sInodes} --save;"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS specifications updated.");
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function database_openvz_addip($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sIPs = $sRequested["GET"]["ip"];
            global $database;
            if ($sBlocks = $database->CachedQuery("SELECT * FROM server_blocks WHERE `server_id` = :ServerId", array('ServerId' => $sVPS->sServerId))) {
                foreach ($sBlocks->data as $key => $value) {
                    if ($sIP = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `block_id` = :BlockId AND `vps_id` = 0", array('BlockId' => $value["block_id"]))) {
                        foreach ($sIP->data as $subvalue) {
                            if ($sCurrentIPs < $sIPs) {
                                $sUpdate = $database->CachedQuery("UPDATE ipaddresses SET `vps_id` = :VPSId WHERE `id` = :Id", array('VPSId' => $sVPS->sId, 'Id' => $subvalue["id"]));
                                if ((empty($sVPS->sPrimaryIP)) || (!$sData = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId AND `ip_address` = :PrimaryIP", array('VPSId' => $sVPS->sId, 'PrimaryIP' => $sVPS->sPrimaryIP)))) {
                                    $sVPS->uPrimaryIP = $subvalue["ip_address"];
                                    $sVPS->InsertIntoDatabase();
                                }
                                $sCurrentIPs++;
                            }
                        }
                    }
                }
                if (empty($sCurrentIPs)) {
                    return $sArray = array("json" => 1, "type" => "error", "result" => "Unfortunatly there are 0 free IPs!");
                }
                return true;
            } else {
                return $sResult = array("result" => "Unfortunatly there are no blocks assigned to this server?", "json" => 1, "type" => "error");
            }
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function openvz_addip($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            global $database;
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
            foreach ($sIPs->data as $subvalue) {
                $sCommand .= "vzctl set {$sVPS->sContainerId} --ipadd {$subvalue["ip_address"]} --save;";
                $sTotal++;
            }
            $sLog[] = array("command" => $sCommand, "result" => $sSSH->exec($sCommand));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            return $sArray = array("json" => 1, "type" => "success", "result" => "VPS now has: {$sTotal} IPv4", "reload" => 1);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function database_openvz_removeip($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sRemove = $sRequested["GET"]["ip"];
            global $database;
            if ($sIP = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `id` = :IPId AND `vps_id` = :VPSId", array('IPId' => $sRemove, 'VPSId' => $sVPS->sId))) {
                $sServer = new Server($sVPS->sServerId);
                $sSSH = Server::server_connect($sServer);
                if ($sVPS->sPrimaryIP == $sIP->data[0]["id"]) {
                    $sVPS->sPrimaryIP = "";
                    $sVPS->InsertIntoDatabase();
                }
                $sUpdateIPs = $database->CachedQuery("UPDATE ipaddresses SET `vps_id` = 0 WHERE `id` = :IPId", array('IPId' => $sIP->data[0]["id"]));
                $sLog[] = array("command" => "vzctl set {$sVPS->sContainerId} --ipdel {$sIP->data[0]["ip_address"]} --save;", "result" => $sSSH->exec("vzctl set {$sVPS->sContainerId} --ipdel {$sIP->data[0]["ip_address"]} --save;"));
                $sSave = VPS::save_vps_logs($sLog, $sVPS);
                return $sArray = array("json" => 1, "type" => "success", "result" => "The IP {$sIP->data[0]["ip_address"]} has been removed.", "reload" => 1);
            } else {
                return $sArray = array("json" => 1, "type" => "error", "result" => "That IP is not assigned to this VPS.");
            }
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function openvz_removeip($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_assignip($sUser, $sVPS, $sRequested)
    {
        $sUserPermissions = $sUser->sPermissions;
        $sVPSUser = new User($sVPS->sUserId);
        if ($sUserPermissions == 7) {
            global $database;
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sIP = $sRequested["GET"]["ip"];
            $sCheckExisting = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `ip_address` = :IPAddress", array('IPAddress' => $sIP));
            if (!empty($sCheckExisting)) {
                foreach ($sCheckExisting->data as $value) {
                    if (empty($value["vps_id"])) {
                        $sIPList[] = array("id" => $value["id"], "ip_address" => $value["ip_address"]);
                    }
                    $sTotalIPs++;
                }
            }

            $sAvailableIPs = count($sIPList);
            if ($sAvailableIPs == 1) {
                $sUpdate = $database->CachedQuery("UPDATE ipaddresses SET `vps_id` = :VPSId WHERE `id` = :Id", array('VPSId' => $sVPS->sId, 'Id' => $sIPList[0]["id"]));
                $sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
                foreach ($sIPs->data as $subvalue) {
                    $sCommand .= "vzctl set {$sVPS->sContainerId} --ipadd {$subvalue["ip_address"]} --save;";
                    $sTotal++;
                }
                $sLog[] = array("command" => $sCommand, "result" => $sSSH->exec($sCommand));
                $sVariable = array("ip" => urlencode($sIP));
                return $sArray = array("json" => 1, "type" => "success", "result" => "The IP {$sIP} was added to this VPS.", "reload" => 1);
            } elseif ((empty($sTotalIPs)) && (empty($sAvailableIPs))) {
                $sIPExpand = explode(".", $sIP);
                if ($sFindBlock = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `ip_address` LIKE :IPAddress", array('IPAddress' => "%{$sIPExpand[0]}.{$sIPExpand[1]}.{$sIPExpand[2]}%"))) {
                    $sIPAdd = new IP(0);
                    $sIPAdd->uIPAddress = $sIP;
                    $sIPAdd->uVPSId = $sVPS->sId;
                    $sIPAdd->uBlockId = $sFindBlock->data[0]["block_id"];
                    $sIPAdd->InsertIntoDatabase();
                    $sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
                    foreach ($sIPs->data as $subvalue) {
                        $sCommand .= "vzctl set {$sVPS->sContainerId} --ipadd {$subvalue["ip_address"]} --save;";
                        $sTotal++;
                    }
                    $sLog[] = array("command" => $sCommand, "result" => $sSSH->exec($sCommand));
                    $sVariable = array("ip" => urlencode($sIP));
                    return $sArray = array("json" => 1, "type" => "success", "result" => "The IP {$sIP} was added to this VPS.", "reload" => 1);
                } else {
                    $sIPAdd = new IP(0);
                    $sIPAdd->uIPAddress = $sIP;
                    $sIPAdd->uVPSId = $sVPS->sId;
                    $sIPAdd->uBlockId = 0;
                    $sIPAdd->InsertIntoDatabase();
                    $sIPs = $database->CachedQuery("SELECT * FROM ipaddresses WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
                    foreach ($sIPs->data as $subvalue) {
                        $sCommand .= "vzctl set {$sVPS->sContainerId} --ipadd {$subvalue["ip_address"]} --save;";
                        $sTotal++;
                    }
                    $sLog[] = array("command" => $sCommand, "result" => $sSSH->exec($sCommand));
                    $sVariable = array("ip" => urlencode($sIP));
                    return $sArray = array("json" => 1, "type" => "success", "result" => "The IP {$sIP} was added to this VPS.", "reload" => 1);
                }
            } elseif ((!empty($sTotalIPs)) && ($sAvailableIPs != 1)) {
                return $sArray = array("json" => 1, "type" => "error", "result" => "The IP you gave is assigned to a VPS!");
            } else {
                return $sArray = array("json" => 1, "type" => "error", "result" => "An unknown error occured!");
            }
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.");
        }
    }

    public function openvz_assignip($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_terminate($sUser, $sVPS, $sRequested)
    {
        global $database;
        $sUserPermissions = $sUser->sPermissions;
        if ($sUserPermissions == 7) {
            $sServer = new Server($sVPS->sServerId);
            $sSSH = Server::server_connect($sServer);
            $sLog[] = array("command" => "vzctl stop {$sVPS->sContainerId}", "result" => $sSSH->exec("vzctl stop {$sVPS->sContainerId}"));
            $sLog[] = array("command" => "vzctl destroy {$sVPS->sContainerId}", "result" => $sSSH->exec("vzctl destroy {$sVPS->sContainerId}"));
            $sSave = VPS::save_vps_logs($sLog, $sVPS);
            $sTerminate = $database->CachedQuery("DELETE FROM vps WHERE `id` = :VPSId", array('VPSId' => $sVPS->sId));
            $sCleanIPs = $database->CachedQuery("UPDATE ipaddresses SET `vps_id` = 0 WHERE `vps_id` = :VPSId", array('VPSId' => $sVPS->sId));
            return $sArray = array("json" => 1, "type" => "error", "result" => "This VPS has been terminated.", "reload" => 1);
        } else {
            return $sArray = array("json" => 1, "type" => "error", "result" => "Insufficient permissions for this action.", "reload" => 1);
        }
    }

    public function openvz_terminate($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function database_openvz_statistics($sUser, $sVPS, $sRequested)
    {
        return true;
    }

    public function openvz_statistics($sUser, $sVPS, $sRequested)
    {
        global $sTemplate;
        global $database;
        global $locale;
        $sServer = new Server($sVPS->sServerId);
        $sSSH = Server::server_connect($sServer);
        $sLog[] = array("command" => "vzctl status {$sVPS->sContainerId}", "result" => $sSSH->exec("vzctl status {$sVPS->sContainerId}"));
        if (strpos($sLog[0]["result"], 'running') === false) {
            return $sArray = array("json" => 1, "type" => "status", "result" => "offline", "hostname" => $sVPS->sHostname);
        } else {
            $sUptime = explode(' ', $sSSH->exec("vzctl exec {$sVPS->sContainerId} cat /proc/uptime"));
            $sRAM = explode('kB', $sSSH->exec("vzctl exec {$sVPS->sContainerId} cat /proc/meminfo"));
            $sUsedRAM = preg_replace("/[^0-9]/", "", $sRAM[0]) - preg_replace("/[^0-9]/", "", $sRAM[1]);
            $sTotalRAM = preg_replace("/[^0-9]/", "", $sRAM[0]);
            $sUsedSWAP = ((preg_replace("/[^0-9]/", "", $sRAM[11])) - (preg_replace("/[^0-9]/", "", $sRAM[12])));
            if ($sUsedSWAP < 0) {
                $sUsedSWAP = ((preg_replace("/[^0-9]/", "", $sRAM[12])) - (preg_replace("/[^0-9]/", "", $sRAM[13])));
            }
            $sTotalSWAP = $sVPS->sSWAP;
            $sDisk = $sSSH->exec("vzctl exec {$sVPS->sContainerId} df");
            $sDisk = explode("\n", trim($sDisk));
            array_shift($sDisk);
            foreach ($sDisk as $sValue) {
                $sValue = explode(" ", preg_replace("/\s+/", " ", $sValue));
                if (is_numeric($sValue[2])) {
                    $sDiskUsed = $sDiskUsed + $sValue[2];
                }
            }
            $sDiskUsed = $sDiskUsed / 1048576;
            $sDiskTotal = $sVPS->sDisk;
            $sCPU = explode(' ', $sSSH->exec("vzctl exec {$sVPS->sContainerId} cat /proc/loadavg"));

            if ($sUsedRAM > 0) {
                $sUsedRAM = round(($sUsedRAM / 1024), 0);
            }

            if ($sTotalRAM > 0) {
                $sTotalRAM = round(($sTotalRAM / 1024), 0);
            }

            if (($sUsedRAM > 0) && ($sTotalRAM > 0)) {
                $sPercentRAM = round((100 / $sTotalRAM) * $sUsedRAM, 0);
            } else {
                $sPercentRAM = 0;
            }

            if ($sUsedSWAP > 0) {
                $sUsedSWAP = round(($sUsedSWAP / 1024), 0);
            }

            if (($sUsedSWAP > 0) && ($sTotalSWAP > 0)) {
                $sPercentSWAP = round((100 / $sTotalSWAP) * $sUsedSWAP, 0);
            } else {
                $sPercentSWAP = 0;
            }

            if (($sDiskUsed > 0) && ($sDiskTotal > 0)) {
                $sPercentDisk = round((100 / $sDiskTotal) * $sDiskUsed, 0);
            } else {
                $sPercentDisk = 0;
            }

            if (($sCPU[0] > 0) && ($sVPS->sCPULimit > 0)) {
                $sPercentCPU = round((100 / $sVPS->sCPULimit) * ($sCPU[0] * 100), 0);
            } else {
                $sPercentCPU = 0;
            }

            if ($sVPS->sCPULimit > 0) {
                $sCores = round(($sVPS->sCPULimit / 100), 0);
            } else {
                $sCores = 0;
            }

            if ($sDiskUsed < 1) {
                $sDiskUsed = round($sDiskUsed, 2);
            } else {
                $sDiskUsed = round($sDiskUsed, 0);
            }

            if ($sTemplates = $database->CachedQuery("SELECT * FROM templates WHERE `id` = :TemplateId", array('TemplateId' => $sVPS->sTemplateId))) {
                $sVPSTemplate = new Template($sVPS->sTemplateId);
                $sTemplateName = $sVPSTemplate->sName;
            } else {
                $sTemplateName = "N/A";
            }

            if ($sVPS->sBandwidthUsage > 0) {
                $sBandwidthUsage = FormatBytes($sVPS->sBandwidthUsage, 2);
            } else {
                $sBandwidthUsage = "0 MB";
            }

            $sPercentBandwidth = round(((100 / ($sVPS->sBandwidthLimit * 1024)) * $sVPS->sBandwidthUsage), 0);
            if (empty($sPercentBandwidth)) {
                $sPercentBandwidth = 0;
            }

            $sStatistics = array("info" => array("uptime" => ConvertTime($sUptime[0]),
                "used_ram" => $sUsedRAM,
                "total_ram" => $sTotalRAM,
                "percent_ram" => $sPercentRAM,
                "used_swap" => $sUsedSWAP,
                "total_swap" => $sTotalSWAP,
                "percent_swap" => $sPercentSWAP,
                "used_disk" => $sDiskUsed,
                "total_disk" => round($sDiskTotal, 0),
                "percent_disk" => $sPercentDisk,
                "total_cpu" => $sVPS->sCPULimit,
                "total_cores" => $sCores,
                "used_cores" => $sCPU[0],
                "used_cpu" => ($sCPU[0] * 100),
                "percent_cpu" => $sPercentCPU,
                "load_average" => "$sCPU[0] $sCPU[1] $sCPU[2]",
                "bandwidth_usage" => $sBandwidthUsage,
                "bandwidth_limit" => FormatBytes($sVPS->sBandwidthLimit * 1024, 0),
                "percent_bandwidth" => round(((100 / ($sVPS->sBandwidthLimit * 1024)) * $sVPS->sBandwidthUsage), 0),
                "operating_system" => $sTemplateName,
                "hostname" => $sVPS->sHostname,
                "primary_ip" => $sVPS->sPrimaryIP
            ));

            $sStatistics = Templater::AdvancedParse($sTemplate->sValue . '/' . $sVPS->sType . '.statistics', $locale->strings, array("Statistics" => $sStatistics));
            return $sArray = array("json" => 1, "type" => "status", "result" => "online", "content" => $sStatistics, "hostname" => $sVPS->sHostname);
        }
    }
}