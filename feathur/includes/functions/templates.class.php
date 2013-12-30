<?php

class Template extends CPHPDatabaseRecord
{

    public $table_name = "templates";
    public $id_field = "id";
    public $fill_query = "SELECT * FROM templates WHERE `id` = :Id";
    public $verify_query = "SELECT * FROM templates WHERE `id` = :Id";
    public $query_cache = 1;

    public $prototype = array(
        'string' => array(
            'Name' => "name",
            'Path' => "path",
            'Type' => "type",
        )
    );
}