<?php

# Written by Mark Schouten <mark@tuxis.nl>
# March 2013, Ede, NL
# © Mark Schouten
# Released as GPL

class livestatus {
    protected $self = array();

    public function __construct($table = "", $filter = array(), $column = array()) {
        $args = func_get_args();
        $this->self['column'] = array();
        $this->self['filter'] = array();
        $this->self['table']  = "";

        if (isset($table) and $table != "") {
            $this->table_set($table);
        }

        if (isset($filter)) {
            for( $i=0, $n=count($filter); $i<$n; $i++ ) {
                $this->filter_add($filter[$i]);
            }
        }
        if (isset($column)) {
            for( $i=0, $n=count($column); $i<$n; $i++ ) {
                $this->column_add($column[$i]);
            }
        }

        $ch = curl_init();
        /* curl_setopt($ch, CURLOPT_USERPWD, "user:password");  ### OPTIONAL AUTHENTICATION ### */
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $this->self['curl'] = $ch;
    }

    public function __get($name = null) {
        if (isset($this->self[$name])) {
            return $this->self[$name];
        } else {
            return FALSE;
        }
    }

    public function rset() {
        $this->self['column'] = array();
        $this->self['filter'] = array();
        $this->self['table']  = "";
    }
    public function table_set ($table) {
        if (!isset($table) or $table == "") {
            die("You need to enter the table you want to query");
        } else {
            $this->self['table'] = $table;
        }
    }
    public function column_add ($column) {
        if (!isset($column)) {
            die("No use in calling this function without a column");
        } else {
            array_push($this->self['column'], $column);
        }
    }

    public function filter_add ($filter) {
        if (!isset($filter)) {
            die("No use in calling this function without a filter");
        } else {
            array_push($this->self['filter'], $filter);
        }
    }

    public function fetch() {
        $options = "table=".$this->self['table'];
        foreach ($this->self['filter'] as $filter) {
            $options .= '&filter='.urlencode("$filter");
        }
        foreach ($this->self['column'] as $column) {
            $options .= '&column='.urlencode("$column");
        }

        /* CHANGE LINE BELOW */
        curl_setopt($this->self['curl'], CURLOPT_URL, "https://<URL TO INDEX.PY>/index.py?$options");
        $result = curl_exec($this->self['curl']);
        if (preg_match("/^ERR:/", $result) == 1) {
            $this->self['error'] = $result;
            $this->rset();
        } else {
            $this->self['result'] = json_decode($result, TRUE);
            $this->rset();
        }
    }

}

?>
