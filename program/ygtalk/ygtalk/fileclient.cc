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

#include "fileclient.h"
#include "stdlib.h"
#include <stdio.h>


  FileShareClient::FileShareClient(buzz::XmppClient *xmppclient, const buzz::Jid &send_to, cricket::FileShareManifest *manifest, std::string root_dir) :
    xmpp_client_(xmppclient),
    root_dir_(root_dir),
    send_to_jid_(send_to),
    waiting_for_file_(send_to == buzz::JID_EMPTY),  
    manifest_(manifest) {
      
  }

  bool FileShareClient::isFileSender()
  {
    return session_->is_sender();
  }

  void FileShareClient::SendFile(/*const std::string& to,*/ /*const std::string& file*/)
  {
 //   buzz::Jid sendto_jid = buzz::Jid(to);
//    OnSignon();
    cricket::FileShareSession* share = file_share_session_client_->CreateFileShareSession();
//    std::cout<<" share"<<std::endl;
    share->Share(send_to_jid_, const_cast<cricket::FileShareManifest*>(manifest_));
//    std::cout<<"after share"<<std::endl;
  }

  void FileShareClient::acceptFile()
  {
    	session_->Accept();
//	waiting_for_file_ = false;
  }

  void FileShareClient::Cancel()
  {
    session_->Cancel();
  }

  void FileShareClient::OnStateChange(buzz::XmppEngine::State state) {

//    std::cout << "on state changed "<<std::endl;
    switch (state) {
    case buzz::XmppEngine::STATE_START:
//      std::cout << "Connecting..." << std::endl;
      break;
    case buzz::XmppEngine::STATE_OPENING:
//      std::cout << "Logging in. " << std::endl;
      break;
    case buzz::XmppEngine::STATE_OPEN:
//      std::cout << "Logged in as " << xmpp_client_->jid().Str() << std::endl;
//      if (!waiting_for_file_)
//        std::cout << "Waiting for " << send_to_jid_.Str() << "to receive file" <<std::endl;
//      OnSignon();
      break;
    case buzz::XmppEngine::STATE_CLOSED:
//      std::cout << "Logged out." << std::endl;
      break;
    }
  }

  void FileShareClient::OnJingleInfo(const std::string & relay_token,
                    const std::vector<std::string> &relay_addresses,
                    const std::vector<talk_base::SocketAddress> &stun_addresses) {
    port_allocator_->SetStunHosts(stun_addresses);
    port_allocator_->SetRelayHosts(relay_addresses);
    port_allocator_->SetRelayToken(relay_token);
  }
							
  
  void FileShareClient::OnStatusUpdate(const buzz::Status &status) {
    if (status.available() && status.fileshare_capability()) {

      // A contact's status has changed. If the person we're looking for is online and able to receive
      // files, send it.
      if (send_to_jid_.BareEquals(status.jid())) {
//	std::cout << send_to_jid_.Str() << " has signed on." << std::endl;
//	cricket::FileShareSession* share = file_share_session_client_->CreateFileShareSession();
//	share->Share(status.jid(), const_cast<cricket::FileShareManifest*>(manifest_));
//	std::cout<<"shared..."<<std::endl;
//	send_to_jid_ = buzz::Jid("");
      }
      
    }
  }
  
  void FileShareClient::OnMessage(talk_base::Message *m) {
    ASSERT(m->message_id == MSG_STOP);
    talk_base::Thread *thread = talk_base::ThreadManager::CurrentThread();
    delete session_;
    thread->Stop();
  }

  std::string FileShareClient::filesize_to_string(unsigned int size) {
    double size_display;
    std::string format;
    std::stringstream ret;

    // the comparisons to 1000 * (2^(n10)) are intentional
    // it's so you don't see something like "1023 bytes",
    // instead you'll see ".9 KB"

    if (size < 1000) {
      format = "Bytes";
      size_display = size;
    } else if (size < 1000 * 1024) {
      format = "KiB";
      size_display = (double)size / 1024.0;
    } else if (size < 1000 * 1024 * 1024) {
      format = "MiB";
      size_display = (double)size / (1024.0 * 1024.0);
    } else {
      format = "GiB";
      size_display = (double)size / (1024.0 * 1024.0 * 1024.0);
    }
    
    ret << std::setprecision(1) << std::setiosflags(std::ios::fixed) << size_display << " " << format;    
    return ret.str();
  }
  
  void FileShareClient::OnRequestSignaling()
  {
    session_manager_->OnSignalingReady();
  }

  void FileShareClient::OnSessionState(cricket::FileShareState state) {
    talk_base::Thread *thread = talk_base::ThreadManager::CurrentThread();
    std::stringstream manifest_description;
	    
    std::string type;
    if(this->isFileSender())
      type = "sender";
    else
      type = "receiver";

    switch(state) {
    case cricket::FS_OFFER:

      // The offer has been made; print a summary of it and, if it's an incoming transfer, accept it

      if (manifest_ != NULL && manifest_->size() == 1)
        manifest_description <<  session_->manifest()->item(0).name;
      else if (session_->manifest()->GetFileCount() && session_->manifest()->GetFolderCount())
        manifest_description <<  session_->manifest()->GetFileCount() << " files and " <<
    	           session_->manifest()->GetFolderCount() << " directories";
      else if (session_->manifest()->GetFileCount() > 0)
        manifest_description <<  session_->manifest()->GetFileCount() << " files";
      else
        manifest_description <<  session_->manifest()->GetFolderCount() << " directories"; 

      size_t filesize;
      if (!session_->GetTotalSize(filesize)) {
        manifest_description << " (Unknown size)";
      } else {
        manifest_description << " (" << filesize_to_string(filesize) << ")";
      }    
      if (session_->is_sender()) {
        std::cout << "Offering " << manifest_description.str()  << " to " << send_to_jid_.Str() << std::endl;
      } else/* if (waiting_for_file_) */{
	std::cout << "Receiving " << manifest_description.str() << " from " << session_->jid().BareJid().Str() << std::endl;
	
	std::string message = "incomingfile###";
	message += session_->jid().Str();
	message +="###";
	message += manifest_description.str();
	message += "\n";
	console_->Send(message);

	SignalFileReceived(session_->jid().BareJid().Str(), manifest_description.str(), "");
// 	session_->Accept();
 	waiting_for_file_ = false;
	
	// If this were a graphical client, we might want to go through the manifest, look for images,
	// and request previews. There are two ways to go about this:
	//
	// If we want to display the preview in a web browser (like the embedded IE control in Google Talk), we could call
	// GetImagePreviewUrl on the session, with the image's index in the manifest, the size, and a pointer to the URL.
	// This will cause the session to listen for HTTP requests on localhost, and set url to a localhost URL that any
	// web browser can use to get the image preview:
	//
	//      std::string url;
	//      session_->GetImagePreviewUrl(0, 100, 100, &url);
	//      url = std::string("firefox \"") + url + "\"";
	//      system(url.c_str());
	//
	// Alternately, you could use libjingle's own HTTP code with the FileShareSession's SocketPool interface to
	// write the image preview directly into a StreamInterface:
	//
	//	talk_base::HttpClient *client = new talk_base::HttpClient("pcp", session_);
	//	std::string path;
	//	session_->GetItemNetworkPath(0,1,&path);
	//	
	//	client->request().verb = talk_base::HV_GET;
	//	client->request().path = path + "?width=100&height=100";
	//	talk_base::FileStream *file = new talk_base::FileStream;
	//	file->Open("/home/username/foo.jpg", "wb");
	//	client->response().document.reset(file);
	//	client->start();
      }
      break;
    case cricket::FS_TRANSFER:
//      std::cout << "File transfer started." << std::endl;
      this->SignalFileTransferStatue(type, "started", "");
      console_->Send("filetransferstarted###\n");
      waiting_for_file_ = false;
      break;
    case cricket::FS_COMPLETE:
//      thread->Post(this, MSG_STOP);
//      std::cout << std::endl << "File transfer completed." << std::endl;
      console_->Send("filetransfercompleted###\n");
      waiting_for_file_ = true;
      this->SignalFileTransferStatue(type, "completed", "");
//      delete this->manifest_;
//      this->manifest_ = NULL;
      /**peut etre ici on doit indiquer que le manifest_ est null car apparament il le supprime automatiquement 
      ** après la termine de transfer. Je ne vois pas que le manifest_ devient null, mais c'est vrais que je ne 
      ** peux plus l'utiliser... bug...
      **/
      break;
    case cricket::FS_LOCAL_CANCEL:
    case cricket::FS_REMOTE_CANCEL:
      //std::cout << std::endl << "File transfer cancelled." << std::endl;
      console_->Send("filetransferfailed###\n");
      waiting_for_file_ = true;
      this->SignalFileTransferStatue(type, "canceled", "");
      
//      thread->Post(this, MSG_STOP);
      break;
    case cricket::FS_FAILURE:
      //std::cout << std::endl << "File transfer failed." << std::endl;
      console_->Send("filetransferfailed###\n");
      waiting_for_file_ = true;
      this->SignalFileTransferStatue(type, "failed", "");
//      thread->Post(this, MSG_STOP);
      break;
    }
  }
    
  void FileShareClient::OnUpdateProgress(cricket::FileShareSession *sess) {
    // Progress has occured on the transfer; update the UI
    
    size_t totalsize, progress;
    std::string itemname;
    unsigned int width = 79;

#ifndef WIN32
    struct winsize ws; 
    if ((ioctl(STDOUT_FILENO, TIOCGWINSZ, &ws) == 0))
      width = ws.ws_col;
#endif

    if(sess->GetTotalSize(totalsize) && sess->GetProgress(progress) && sess->GetCurrentItemName(&itemname)) {
      float percent = (float)progress / totalsize;
    
      int value = percent * 100;
      char buffer[30];
      sprintf(buffer, "%d", value);
      std::string message= "progress###";
      message += buffer;
      message += "\n";
      console_->Send(message);
      unsigned int progressbar_width = (width * 4) / 5;
      
      const char *filename = itemname.c_str();
      std::cout.put('\r');
      for (unsigned int l = 0; l < width; l++) {
        if (l < percent * progressbar_width)
	  std::cout.put('#');
      	else if (l > progressbar_width && l < progressbar_width + 1 + strlen(filename))
      	  std::cout.put(filename[l-(progressbar_width + 1)]);
      	else
      	  std::cout.put(' ');
      }
      std::cout.flush();
    }
  }

  void FileShareClient::OnResampleImage(std::string path, int width, int height, talk_base::HttpTransaction *trans) {  

    // The other side has requested an image preview. This is an asynchronous request. We should resize
    // the image to the requested size,and send that to ResampleComplete(). For simplicity, here, we
    // send back the original sized image. Note that because we don't recognize images in our manifest
    // this will never be called in pcp

    // Even if you don't resize images, you should implement this method and connect to the 
    // SignalResampleImage signal, just to return an error.    

    talk_base::FileStream *s = new talk_base::FileStream();
    if (s->Open(path.c_str(), "rb"))
      session_->ResampleComplete(s, trans, true);  
    else {
      delete s;
      session_->ResampleComplete(NULL, trans, false);
    }
  }
    
  void FileShareClient::OnFileShareSessionCreate(cricket::FileShareSession *sess) {

//    std::cout<<"OnFileShareSessionCreate"<<std::endl;
    session_ = sess;
    sess->SignalState.connect(this, &FileShareClient::OnSessionState);
    sess->SignalNextFile.connect(this, &FileShareClient::OnUpdateProgress);
    sess->SignalUpdateProgress.connect(this, &FileShareClient::OnUpdateProgress);
    sess->SignalResampleImage.connect(this, &FileShareClient::OnResampleImage);
    sess->SetLocalFolder(root_dir_);
  }
  
  void FileShareClient::OnSignon(cricket::HttpPortAllocator* port_allocator, 
				 cricket::SessionManager* session_manager,
				 buzz::JingleInfoTask* jingle_info_task,/*pas utile*/
				 cricket::SessionManagerTask* task) {
    std::string client_unique = xmpp_client_->jid().Str();
    cricket::InitRandom(client_unique.c_str(), client_unique.size());
/*
    buzz::PresencePushTask *presence_push_ = new buzz::PresencePushTask(xmpp_client_);
    presence_push_->SignalStatusUpdate.connect(this, &FileShareClient::OnStatusUpdate);
    presence_push_->Start();
    
    buzz::Status my_status;
    my_status.set_jid(xmpp_client_->jid());
    my_status.set_available(true);
    my_status.set_show(buzz::Status::SHOW_ONLINE);
    my_status.set_priority(0);
    my_status.set_know_capabilities(true);
    my_status.set_phone_capability(true);
    my_status.set_fileshare_capability(true);
    my_status.set_is_google_client(true);
    my_status.set_version("1.0.0.66");

    buzz::PresenceOutTask* presence_out_ =
      new buzz::PresenceOutTask(xmpp_client_);
    presence_out_->Send(my_status);
    presence_out_->Start();
*/    
//    port_allocator_.reset(new cricket::HttpPortAllocator(&network_manager_, "pcp"));
    port_allocator_.reset(port_allocator);
 //   talk_base::Thread* worker_thread_ = new talk_base::Thread();
//    session_manager_.reset(new cricket::SessionManager(port_allocator_.get(), NULL));
//    session_manager_->SignalRequestSignaling.connect(
//      this, &FileShareClient::OnRequestSignaling);
//    session_manager_->OnSignalingReady();
//    port_allocator_ = port_allocator;
    session_manager_.reset( session_manager);
//    cricket::SessionManagerTask * session_manager_task = new cricket::SessionManagerTask(xmpp_client_, session_manager_.get());
    cricket::SessionManagerTask *session_manager_task = task;
//    session_manager_task->EnableOutgoingMessages();
//    session_manager_task->Start();
/*    
    buzz::JingleInfoTask *jingle_info_task_ = new buzz::JingleInfoTask(xmpp_client_);
    jingle_info_task_->RefreshJingleInfoNow();
    jingle_info_task_->SignalJingleInfo.connect(this, &FileShareClient::OnJingleInfo);
    jingle_info_task_->Start();
*/
    file_share_session_client_.reset(new cricket::FileShareSessionClient(session_manager_.get(), xmpp_client_->jid(), "pcp"));
    file_share_session_client_->SignalFileShareSessionCreate.connect(this, &FileShareClient::OnFileShareSessionCreate);
    session_manager_->AddClient(NS_GOOGLE_SHARE, file_share_session_client_.get());

 //   worker_thread_->Start();
  }
  


static unsigned int get_dir_size(const char *directory);
