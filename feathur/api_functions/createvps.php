<?php

class CreateVPS extends APICommandBase
{
    public function run()
    {
        // Get server information
        if (!is_numeric($this->args["POST"]["server"])) {
            if ($sServers = $this->db->CachedQuery("SELECT * FROM servers WHERE `ip_address` = :ServerIP", array(':ServerIP' => $_POST['server']))) {
                $sServer = new Server($sServers->data[0]["id"]);
                $this->args["POST"]["server"] = $sServers->data[0]["id"];
            } else {
                $this->output = array("result" => "Unfortunatly no server matches your query.");
            }
        }

        // Get user information
        if ($sCheckUsers = $this->db->CachedQuery("SELECT * FROM accounts WHERE `email_address` = :UserEmail", array(':UserEmail' => $_POST['useremail']))) {
            $sActionUser = new User($sCheckUsers->data[0]["id"]);
            $this->args["POST"]["user"] = $sActionUser->sId;
        } else {
            $sActionUser = User::generate_user($_POST['useremail'], $_POST['username'], 1);
            if (is_array($sActionUser)) {
                $this->output = $sActionUser;
                return;
            }
            $this->args["POST"]["user"] = $sActionUser->sId;
        }

        // Get template info.
        if ($sTemplates = $this->db->CachedQuery("SELECT * FROM templates WHERE `name` = :Template AND `type` = :Type", array(':Template' => $_POST['template'], ':Type' => $sServer->sType))) {
            $this->args["POST"]["template"] = $sTemplates->data[0]["id"];
        } else {
            if ($sTemplates = $this->db->CachedQuery("SELECT * FROM templates WHERE `type` = :Type ORDER BY id ASC", array(':Type' => $sServer->sType))) {
                $this->args["POST"]["template"] = $sTemplates->data[0]["id"];
            } else {
                $this->output = array("result" => "Unfortunatly no templates exist, vps creation failed!");
                return;
            }
        }

        $sServerType = new $sServer->sType;
        $sMethod = "database_{$sServer->sType}_create";
        $sSecond = "{$sServer->sType}_create";
        $sCreate = $sServerType->$sMethod($this->user, $this->args);
        if (is_array($sCreate)) {
            $this->output = $sCreate;
            return;
        }
        $sFinish = $sServerType->$sSecond($this->user, $this->args);
        if (is_array($sFinish)) {
            $this->output = $sFinish;
            return;
        }
    }
} 