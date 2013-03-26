#!/usr/bin/env python

# Written by Mark Schouten <mark@tuxis.nl>
# March 2013, Ede, NL
# © Mark Schouten
# Released as GPL

import cgi
import json
import sys
import livestatus
import cgitb
cgitb.enable()

socket_path = "unix:/var/lib/nagios3/rw/live"

def asknagios(t, q = []):
    try:
        conn = livestatus.SingleSiteConnection(socket_path)
    except Exception, e:
        print "ERR: Livestatus error: %s" % str(e)
        sys.exit(1)

    try:
        ret = conn.query_table_assoc("GET %s\n%s" % (t, '\n'.join(q)) )
        return ret
    except Exception, e:
        print "ERR: Livestatus: %s" % str(e)
        sys.exit(1)

print "Content-Type: text/html"     # HTML is following
print                               # blank line, end of headers

arguments = cgi.FieldStorage()

t = ""
f = ""
c = ""
qstring = []

try:
    t = arguments['table'].value
except:
    print "table is a required value"
    sys.exit(1)

try:
    if isinstance(arguments['filter'], list):
        for v in arguments['filter']:
            qstring.append("Filter: %s" % ( v.value ))
    else:
        f = arguments['filter'].value
        qstring.append("Filter: %s" % ( f ))
except:
    True

try:
    if isinstance(arguments['column'], list):
        c = ""
        for v in arguments['column']:
            c += " %s" % ( v.value )
        qstring.append("Columns: %s" % ( c ))
    else:
        qstring.append("Columns: %s" % ( arguments['column'].value ))
        
except:
    True

if arguments['table'].value == "columns":
    print "<table>"
    for i in asknagios(arguments['table'].value):
        print "<tr>"
        print "<td>%s</td><td>%s</td><td>%s</td>" % (i['table'], i['name'], i['description'])
        print "</tr>"
    print "</table>"
    sys.exit(0);

ret = asknagios(arguments['table'].value, qstring);
if len(ret) == 1:
    print json.dumps(ret[0])
else:
    print json.dumps(ret)

