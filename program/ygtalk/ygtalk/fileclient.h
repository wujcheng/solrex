/*
 * Jingle call example
 * Copyright 2004--2005, Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Tempe Place, Suite 330, Boston, MA  02111-1307  USA
 */

#ifndef FILE_CLIENT_H
#define FILE_CLIENT_H

#include <iomanip>
#include <time.h>

#ifndef WIN32
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <iomanip>
#include <sys/ioctl.h>
#include <sys/types.h>
#include <sys/wait.h>
#else
#include <direct.h>
//typedef _getcwd getcwd;
#include "talk/base/win32.h"
#endif

#include "talk/base/fileutils.h"
#include "talk/base/pathutils.h"
#include "talk/base/helpers.h"
#include "talk/base/httpclient.h"
#include "talk/base/logging.h"
#include "talk/base/physicalsocketserver.h"
#include "talk/base/ssladapter.h"
#include "talk/xmpp/xmppclientsettings.h"
#include "talk/examples/login/xmppthread.h"
#include "talk/examples/login/xmppauth.h"
#include "talk/p2p/client/httpportallocator.h"
#include "talk/p2p/client/sessionmanagertask.h"
#include "talk/session/fileshare/fileshare.h"
#include "talk/examples/login/presencepushtask.h"
#include "talk/examples/login/presenceouttask.h"
#include "talk/examples/login/jingleinfotask.h"
#include "console.h"

#if defined(_MSC_VER) && (_MSC_VER < 1400)
// The following are necessary to properly link when compiling STL without
// /EHsc, otherwise known as C++ exceptions.
void __cdecl std::_Throw(const std::exception &) {}
std::_Prhand std::_Raise_handler = 0;
#endif

class FileShareClient : public sigslot::has_slots<>, public talk_base::MessageHandler {
 public:
  FileShareClient(buzz::XmppClient *xmppclient, const buzz::Jid &send_to, cricket::FileShareManifest *manifest, std::string root_dir);
  bool isFileSender();

  void SendFile(/*const std::string& to, const std::string& file cricket::FileShareManifest *manifest*/);
  void acceptFile();
  cricket::FileShareManifest* getFileShareManifest()
  {
    return manifest_;
  }
  void Cancel();
  void OnStateChange(buzz::XmppEngine::State state); 
  void OnStatusUpdate(const buzz::Status &status);
  void OnJingleInfo(const std::string & relay_token,
                    const std::vector<std::string> &relay_addresses,
                    const std::vector<talk_base::SocketAddress> &stun_addresses);
  void setRootDir(const std::string& str){
    root_dir_ = str;
  }
  void setSendToJid(const buzz::Jid & send_to){
    send_to_jid_ = send_to;
  }
  void setSender(bool isSender){
    waiting_for_file_ = !isSender;
//    if(!isSender)
//      OnSignon();
  }

  void setManifest(cricket::FileShareManifest *manifest){
//      if(manifest_ != NULL)
//	delete manifest_;
      manifest_ = manifest;
  }

  void setConsole(Console* console){
    console_ = console;
  }
  
  void OnSignon(cricket::HttpPortAllocator* port_allocator, 
				 cricket::SessionManager* session_manager,
				 buzz::JingleInfoTask* jingle_info_task,/*pas utile*/
				 cricket::SessionManagerTask* tast);

  private:

  enum {
    MSG_STOP,
  };
 
  void OnMessage(talk_base::Message *m);
  std::string filesize_to_string(unsigned int size);
  void OnSessionState(cricket::FileShareState state); 
  void OnUpdateProgress(cricket::FileShareSession *sess); 
  void OnResampleImage(std::string path, int width, int height, talk_base::HttpTransaction *trans);
  void OnFileShareSessionCreate(cricket::FileShareSession *sess);
  void OnRequestSignaling();


  Console* console_;
  talk_base::NetworkManager network_manager_;
  talk_base::scoped_ptr<cricket::HttpPortAllocator> port_allocator_;
  talk_base::scoped_ptr<cricket::SessionManager> session_manager_;
  talk_base::scoped_ptr<cricket::FileShareSessionClient> file_share_session_client_;
  buzz::XmppClient *xmpp_client_;
  buzz::Jid send_to_jid_;
  cricket::FileShareManifest *manifest_;
  cricket::FileShareSession *session_;
  bool waiting_for_file_;
  std::string root_dir_;
public:
  sigslot::signal3<const std::string&, //par 
		   const std::string&,//filename
		   const std::string&> /*pas utile*/ SignalFileReceived;
  sigslot::signal3<const std::string&, //sender or receiver
		   const std::string&, //statue
		   const std::string&> /*pas utile*/SignalFileTransferStatue;
  
};

static unsigned int get_dir_size(const char *directory){
  unsigned int total = 0;
  talk_base::DirectoryIterator iter;
  talk_base::Pathname path;
  path.AppendFolder(directory);
  iter.Iterate(path.pathname());
  while (iter.Next())  {
    if (iter.Name() == "." || iter.Name() == "..")
      continue;
    if (iter.IsDirectory()) {
      path.AppendPathname(iter.Name());
      total += get_dir_size(path.pathname().c_str());
    }
    else
      total += iter.FileSize();
  }
  return total;
}

#endif
