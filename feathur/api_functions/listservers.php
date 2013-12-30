<?php

class ListServers extends APICommandBase {
    public function run()
    {
        $this->output = VPS::array_servers();
    }
} 