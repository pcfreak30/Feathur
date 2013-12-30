<?php

/**
 * Created by PhpStorm.
 * User: derrick
 * Date: 12/29/13
 * Time: 8:26 PM
 */
class SuspendVPS extends APICommandBase
{
    public function run()
    {
        if ($sCheckUser = $this->db->CachedQuery("SELECT * FROM accounts WHERE `email_address` = :UserEmail", array(':UserEmail' => $_POST['useremail']))) {
            $sActionUser = new User($sCheckUser->data[0]["id"]);
            if ($sVPS = $this->db->CachedQuery("SELECT * FROM vps WHERE `user_id` = :UserId AND `id` = :Id", array(':UserId' => $sActionUser->sId, ':Id' => $_POST['vpsid']))) {
                $sVPS = new VPS($sVPS->data["0"]["id"]);
                $sServer = new Server($sVPS->sServerId);
                $sServerType = new $sServer->sType;
                $sMethod = "database_{$sServer->sType}_suspend";
                $sSecond = "{$sServer->sType}_suspend";
                $sSuspend = $sServerType->$sMethod($this->user, $sVPS, $this->args);
                if (is_array($sSuspend)) {
                    echo json_encode($sSuspend);
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
            $this->output = array("result" => "Invalid user email, manual suspension required.");
            return;
        }
    }
} 