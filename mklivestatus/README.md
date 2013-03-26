PHP Class for mklivestatus
==========================

mklivestatus [1] is a cool broker for Nagios data, which enables you to query
Nagios data extremely fast. Downside is that I couldn't find a nice and easy
way to query if from other boxes. So I wrote these two files.

[1]: http://mathias-kettner.de/checkmk_livestatus.html

index.py
========

You should place this file on the Nagios-box. It accepts queries and relays
them to mklivestatus via the unix-socket. I configured Apache2 like this:
    ScriptAlias /nagios3/live    /usr/lib/cgi-bin/livestatus

and placed index.py in /usr/lib/cgi-bin/livestatus. Make sure livestatus.py
(comes with mklivestatus) is loadable.

Column explanation
==================

If you visit index.py?table=columns, you will see a list of all the tables and
columns and their descriptions available in mklivestatus.

mklive.inc.php
==============

You should include this file in the php where you want to query Nagios from.
Please note that you need to change the url to query and you might need to fix
the username and password for authentication. Use the class like this:

```php
 $ls = new livestatus;
 $ls->table_set("hosts");
 $ls->filter_add("contact_groups >= $username");
 $ls->filter_add("state != 0");
 $ls->column_add("name");
 $ls->fetch();
 if (isset($ls->error) && $ls->error != "") {
     print $ls->error;
     return FALSE;
 }

 return $ls->result;
```

This would query the hosts-table for all hosts with contact_groups containing
$username and a state other than OK.


