<?php

class UnSuspendVPS extends APICommandBase
{
    public function run()
    {
        if ($sCheckUser = $this->db->CachedQuery("SELECT * FROM accounts WHERE `email_address` = :UserEmail", array(':UserEmail' => $_POST['useremail']))) {
            $sActionUser = new User($sCheckUser->data[0]["id"]);
            if ($sVPS = $this->db->CachedQuery("SELECT * FROM vps WHERE `user_id` = :UserId AND `id` = :Id", array(':UserId' => $sActionUser->sId, ':Id' => $_POST['vpsid']))) {
                $sVPS = new VPS($sVPS->data["0"]["id"]);
                $sServer = new Server($sVPS->sServerId);
                $sServerType = new $sServer->sType;
                $sMethod = "database_{$sServer->sType}_unsuspend";
                $sSecond = "{$sServer->sType}_unsuspend";
                $sUnsuspend = $sServerType->$sMethod($this->user, $sVPS, $this->args);
                if (is_array($sUnsuspend)) {
                    echo json_encode($sUnsuspend);
                    return;
                }
                $sFinish = $sServerType->$sSecond($this->user, $sVPS, $this->args);
                if (is_array($sFinish)) {
                    $this->output = $sFinish;
                    return;
                }
            } else {
                $this->output = array("result" => "The VPS Id is either invalid or does not belong to this user.");
                return;
            }
        } else {
            $this->output = array("result" => "Invalid user email, manual unsuspension required.");
            return;
        }
    }
} 