#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# Usage: ./gtsent.py "somebody@gmail.com" "Message"

import xmpp
import sys

login = 'USER' # without @gmail.com
pwd   = 'PASS'

cnx = xmpp.Client('gmail.com', debug=[])
cnx.connect( server=('talk.google.com', 5223) )
cnx.auth(login, pwd, 'python')

cnx.send(xmpp.Message(sys.argv[1], sys.argv[2]))

