<?php

abstract class APICommandBase
{
    protected $args = array();
    protected $user;
    protected $db = array();
    protected $output;
    public function __construct(CachedPDO $db)
    {
        global $sRequested, $sUser; //Fix Me: Global's hack
        global $sUser; //Fix Me: Global's hack
        $this->args = $sRequested;
        $this->user = $sUser;
    }

    public function run()
    {
        return;
    }
}