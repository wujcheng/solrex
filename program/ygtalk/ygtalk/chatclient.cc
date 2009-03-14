/* YGtalk: Yet another Gtalk.
   Copyright (C) 2009 Solrex Yang <http://solrex.cn>

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>. */

#include "chatclient.h"

#include "talk/xmpp/constants.h"

#include <iostream>


namespace buzz {
bool ChatClient::HandleStanza(const XmlElement * stanza)
{
    if (stanza->Attr(QN_TYPE) != "chat")
    {
        return true;
    }

    if (!stanza->HasAttr(QN_FROM))
    {
        return true;
    }

    if (!stanza->FirstNamed(QN_BODY))
    {
	return true;
    }
	
    SignalTexteRecu("", stanza->Attr(QN_FROM), stanza->FirstNamed(QN_BODY)->BodyText());
    return false;
}

void ChatClient::envoyerTexte(const std::string& str_to, const std::string& str_text)
{
	XmlElement* presence = new XmlElement(QN_MESSAGE);
	presence->AddAttr(QN_TO, str_to);
	presence->AddAttr(QN_TYPE, "chat");

	XmlElement* body = presence->FindOrAddNamedChild(QN_BODY);
	body->SetBodyText(str_text);

	SendStanza(presence);
	delete presence;
}

int ChatClient::ProcessStart()
{
	return STATE_RESPONSE;
}


}

