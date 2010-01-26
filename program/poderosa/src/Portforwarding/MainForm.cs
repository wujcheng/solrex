/*
 Copyright (c) 2005 Poderosa Project, All Rights Reserved.

 $Id: MainForm.cs,v 1.2 2005/04/20 09:06:03 okajima Exp $
*/
using System;
using System.Diagnostics;
using System.Drawing;
using System.Collections;
using System.ComponentModel;
using System.Windows.Forms;
using System.Net.Sockets;

using Poderosa.UI;

namespace Poderosa.PortForwarding
{
	/// <summary>
	/// MainForm の概要の説明です。
	/// </summary>
	internal class MainForm : System.Windows.Forms.Form
	{
		private Hashtable _menuMap;

		private System.Windows.Forms.ListView _list;
		private System.Windows.Forms.ColumnHeader _sshHostColumn;
		private System.Windows.Forms.ColumnHeader _accountColumn;
		private System.Windows.Forms.ColumnHeader _typeColumn;
		private System.Windows.Forms.ColumnHeader _destinationHostColumn;
		private System.Windows.Forms.ColumnHeader _destinationPortColumn;
		private System.Windows.Forms.ColumnHeader _listenPortColumn;
		private System.Windows.Forms.ColumnHeader _statusColumn;
		private System.Windows.Forms.MainMenu _mainMenu;
		private GMainMenuItem _menuFile;
		private GMenuItem _menuNewProfile;
		private GMenuItem _menuFileBar1;
		private GMenuItem _menuTaskTray;
		private GMenuItem _menuExit;
		private GMainMenuItem _menuProfile;
		private GMenuItem _menuProfileProperty;
		private GMenuItem _menuProfileRemove;
		private GMenuItem _menuProfileBar1;
		private GMenuItem _menuProfileUp;
		private GMenuItem _menuProfileDown;
		private GMenuItem _menuProfileBar2;
		private GMenuItem _menuProfileConnect;
		private GMenuItem _menuProfileDisconnect;
		private GMainMenuItem _menuAllProfile;
		private GMenuItem _menuAllProfileConnect;
		private GMenuItem _menuAllProfileDisconnect;
		private GMainMenuItem _menuTool;
		private GMenuItem _menuOption;
		private GMainMenuItem _menuHelp;
		private GMenuItem _menuAboutBox;
		private NotifyIcon _taskTrayIcon;
		private ContextMenu _listViewContextMenu;
		private ContextMenu _iconContextMenu;
		private System.ComponentModel.IContainer components;

		public MainForm()
		{
			//
			// Windows フォーム デザイナ サポートに必要です。
			//
			InitializeComponent();
			InitializeText();

			//
			// TODO: InitializeComponent 呼び出しの後に、コンストラクタ コードを追加してください。
			//
			InitMenuShortcut();

			InitContextMenu();
		}

		/// <summary>
		/// 使用されているリソースに後処理を実行します。
		/// </summary>
		protected override void Dispose( bool disposing )
		{
			if( disposing )
			{
				if(components != null)
				{
					components.Dispose();
				}
			}
			base.Dispose( disposing );
		}

		#region Windows フォーム デザイナで生成されたコード 
		/// <summary>
		/// デザイナ サポートに必要なメソッドです。このメソッドの内容を
		/// コード エディタで変更しないでください。
		/// </summary>
		private void InitializeComponent()
		{
			this.components = new System.ComponentModel.Container();
			System.Resources.ResourceManager resources = new System.Resources.ResourceManager(typeof(MainForm));
			this._list = new System.Windows.Forms.ListView();
			this._sshHostColumn = new System.Windows.Forms.ColumnHeader();
			this._accountColumn = new System.Windows.Forms.ColumnHeader();
			this._typeColumn = new System.Windows.Forms.ColumnHeader();
			this._listenPortColumn = new System.Windows.Forms.ColumnHeader();
			this._destinationHostColumn = new System.Windows.Forms.ColumnHeader();
			this._destinationPortColumn = new System.Windows.Forms.ColumnHeader();
			this._statusColumn = new System.Windows.Forms.ColumnHeader();
			this._mainMenu = new System.Windows.Forms.MainMenu();
			this._menuFile = new GMainMenuItem();
			this._menuNewProfile = new GMenuItem();
			this._menuFileBar1 = new GMenuItem();
			this._menuTaskTray = new GMenuItem();
			this._menuExit = new GMenuItem();
			this._menuProfile = new GMainMenuItem();
			this._menuProfileProperty = new GMenuItem();
			this._menuProfileRemove = new GMenuItem();
			this._menuProfileBar1 = new GMenuItem();
			this._menuProfileUp = new GMenuItem();
			this._menuProfileDown = new GMenuItem();
			this._menuProfileBar2 = new GMenuItem();
			this._menuProfileConnect = new GMenuItem();
			this._menuProfileDisconnect = new GMenuItem();
			this._menuAllProfile = new GMainMenuItem();
			this._menuAllProfileConnect = new GMenuItem();
			this._menuAllProfileDisconnect = new GMenuItem();
			this._menuTool = new GMainMenuItem();
			this._menuOption = new GMenuItem();
			this._menuHelp = new GMainMenuItem();
			this._menuAboutBox = new GMenuItem();
			this._taskTrayIcon = new System.Windows.Forms.NotifyIcon(this.components);
			this.SuspendLayout();
			// 
			// _list
			// 
			this._list.Columns.AddRange(new System.Windows.Forms.ColumnHeader[] {
																					this._sshHostColumn,
																					this._accountColumn,
																					this._typeColumn,
																					this._listenPortColumn,
																					this._destinationHostColumn,
																					this._destinationPortColumn,
																					this._statusColumn});
			this._list.Dock = System.Windows.Forms.DockStyle.Fill;
			this._list.FullRowSelect = true;
			this._list.GridLines = true;
			this._list.Location = new System.Drawing.Point(0, 0);
			this._list.MultiSelect = false;
			this._list.Name = "_list";
			this._list.Size = new System.Drawing.Size(504, 345);
			this._list.TabIndex = 0;
			this._list.View = System.Windows.Forms.View.Details;
			this._list.KeyPress += new System.Windows.Forms.KeyPressEventHandler(this.OnListViewKeyPress);
			this._list.DoubleClick += new System.EventHandler(this.OnListViewDoubleClicked);
			this._list.MouseUp += new System.Windows.Forms.MouseEventHandler(this.OnListViewMouseUp);
			this._list.SelectedIndexChanged += new System.EventHandler(this.OnSelectedIndexChanged);
			// 
			// _sshHostColumn
			// 
			this._sshHostColumn.Width = 69;
			// 
			// _accountColumn
			// 
			// 
			// _typeColumn
			// 
			// 
			// _listenPortColumn
			// 
			this._listenPortColumn.Width = 75;
			// 
			// _destinationHostColumn
			// 
			this._destinationHostColumn.Width = 100;
			// 
			// _destinationPortColumn
			// 
			this._destinationPortColumn.Width = 75;
			// 
			// _statusColumn
			// 
			// 
			// _mainMenu
			// 
			this._mainMenu.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																					  this._menuFile,
																					  this._menuProfile,
																					  this._menuAllProfile,
																					  this._menuTool,
																					  this._menuHelp});
			// 
			// _menuFile
			// 
			this._menuFile.Index = 0;
			this._menuFile.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																					  this._menuNewProfile,
																					  this._menuFileBar1,
																					  this._menuTaskTray,
																					  this._menuExit});
			// 
			// _menuNewProfile
			// 
			this._menuNewProfile.Index = 0;
			this._menuNewProfile.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuFileBar1
			// 
			this._menuFileBar1.Index = 1;
			this._menuFileBar1.Text = "-";
			// 
			// _menuTaskTray
			// 
			this._menuTaskTray.Index = 2;
			this._menuTaskTray.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuExit
			// 
			this._menuExit.Index = 3;
			this._menuExit.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfile
			// 
			this._menuProfile.Index = 1;
			this._menuProfile.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																						 this._menuProfileProperty,
																						 this._menuProfileRemove,
																						 this._menuProfileBar1,
																						 this._menuProfileUp,
																						 this._menuProfileDown,
																						 this._menuProfileBar2,
																						 this._menuProfileConnect,
																						 this._menuProfileDisconnect});
			this._menuProfile.Popup += new System.EventHandler(this.OnProfileMenuClicked);
			// 
			// _menuProfileProperty
			// 
			this._menuProfileProperty.Index = 0;
			this._menuProfileProperty.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfileRemove
			// 
			this._menuProfileRemove.Index = 1;
			this._menuProfileRemove.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfileBar1
			// 
			this._menuProfileBar1.Index = 2;
			this._menuProfileBar1.Text = "-";
			// 
			// _menuProfileUp
			// 
			this._menuProfileUp.Index = 3;
			this._menuProfileUp.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfileDown
			// 
			this._menuProfileDown.Index = 4;
			this._menuProfileDown.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfileBar2
			// 
			this._menuProfileBar2.Index = 5;
			this._menuProfileBar2.Text = "-";
			// 
			// _menuProfileConnect
			// 
			this._menuProfileConnect.Index = 6;
			this._menuProfileConnect.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuProfileDisconnect
			// 
			this._menuProfileDisconnect.Index = 7;
			this._menuProfileDisconnect.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuAllProfile
			// 
			this._menuAllProfile.Index = 2;
			this._menuAllProfile.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																							this._menuAllProfileConnect,
																							this._menuAllProfileDisconnect});
			// 
			// _menuAllProfileConnect
			// 
			this._menuAllProfileConnect.Index = 0;
			this._menuAllProfileConnect.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuAllProfileDisconnect
			// 
			this._menuAllProfileDisconnect.Index = 1;
			this._menuAllProfileDisconnect.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuTool
			// 
			this._menuTool.Index = 3;
			this._menuTool.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																					  this._menuOption});
			// 
			// _menuOption
			// 
			this._menuOption.Index = 0;
			this._menuOption.Click += new System.EventHandler(this.OnMenu);
			// 
			// _menuHelp
			// 
			this._menuHelp.Index = 4;
			this._menuHelp.MenuItems.AddRange(new System.Windows.Forms.MenuItem[] {
																					  this._menuAboutBox});
			// 
			// _menuAboutBox
			// 
			this._menuAboutBox.Index = 0;
			this._menuAboutBox.Click += new System.EventHandler(this.OnMenu);
			// 
			// _taskTrayIcon
			// 
			this._taskTrayIcon.Icon = ((System.Drawing.Icon)(resources.GetObject("_taskTrayIcon.Icon")));
			this._taskTrayIcon.Text = "Poderosa SSH Portforwarding Gateway";
			this._taskTrayIcon.Visible = true;
			this._taskTrayIcon.DoubleClick += new System.EventHandler(this.OnTaskTrayIconDoubleClicked);
			// 
			// MainForm
			// 
			this.AutoScaleBaseSize = new System.Drawing.Size(5, 12);
			this.ClientSize = new System.Drawing.Size(504, 345);
			this.Controls.Add(this._list);
			this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
			this.MaximizeBox = false;
			this.Menu = this._mainMenu;
			this.Name = "MainForm";
			this.StartPosition = System.Windows.Forms.FormStartPosition.Manual;
			this.Text = Env.WindowTitle;
			this.ResumeLayout(false);

		}
		#endregion

		protected override void WndProc(ref Message m) {
			base.WndProc(ref m);
			if(m.Msg==Win32.WM_USER)
				Util.CallbackWM_USER(m.WParam, m.LParam);
		}
		private void InitializeText() {
			this._sshHostColumn.Text = Env.Strings.GetString("Form.MainForm._sshHostColumn");
			this._accountColumn.Text = Env.Strings.GetString("Form.MainForm._accountColumn");
			this._typeColumn.Text = Env.Strings.GetString("Form.MainForm._typeColumn");
			this._listenPortColumn.Text = Env.Strings.GetString("Form.MainForm._listenPortColumn");
			this._destinationHostColumn.Text = Env.Strings.GetString("Form.MainForm._destinationHostColumn");
			this._destinationPortColumn.Text = Env.Strings.GetString("Form.MainForm._destinationPortColumn");
			this._statusColumn.Text = Env.Strings.GetString("Form.MainForm._statusColumn");
			this._menuFile.Text = Env.Strings.GetString("Menu._menuFile");
			this._menuNewProfile.Text = Env.Strings.GetString("Menu._menuNewProfile");
			this._menuTaskTray.Text = Env.Strings.GetString("Menu._menuTaskTray");
			this._menuExit.Text = Env.Strings.GetString("Menu._menuExit");
			this._menuProfile.Text = Env.Strings.GetString("Menu._menuProfile");
			this._menuProfileProperty.Text = Env.Strings.GetString("Menu._menuProfileProperty");
			this._menuProfileRemove.Text = Env.Strings.GetString("Menu._menuProfileRemove");
			this._menuProfileUp.Text = Env.Strings.GetString("Menu._menuProfileUp");
			this._menuProfileDown.Text = Env.Strings.GetString("Menu._menuProfileDown");
			this._menuProfileConnect.Text = Env.Strings.GetString("Menu._menuProfileConnect");
			this._menuProfileDisconnect.Text = Env.Strings.GetString("Menu._menuProfileDisconnect");
			this._menuAllProfile.Text = Env.Strings.GetString("Menu._menuAllProfile");
			this._menuAllProfileConnect.Text = Env.Strings.GetString("Menu._menuAllProfileConnect");
			this._menuAllProfileDisconnect.Text = Env.Strings.GetString("Menu._menuAllProfileDisconnect");
			this._menuTool.Text = Env.Strings.GetString("Menu._menuTool");
			this._menuOption.Text = Env.Strings.GetString("Menu._menuOption");
			this._menuHelp.Text = Env.Strings.GetString("Menu._menuHelp");
			this._menuAboutBox.Text = Env.Strings.GetString("Menu._menuAboutBox");
		}
		public void ReloadLanguage() {
			InitializeText();
			//こうすることでメニュー幅が調整される
			MainMenu mm = new MainMenu();
			while(_mainMenu.MenuItems.Count>0) {
				mm.MenuItems.Add(_mainMenu.MenuItems[0]);
			}
			_mainMenu = mm;
			this.Menu = mm;
			InitContextMenu();
			RefreshAllProfiles();
		}

		private void InitMenuShortcut() {
			_menuNewProfile.ShortcutKey = (Keys.Control|Keys.N);
			_menuProfileUp.ShortcutKey = (Keys.Control|Keys.K);
			_menuProfileDown.ShortcutKey = (Keys.Control|Keys.J);
			_menuAllProfileConnect.ShortcutKey = (Keys.Control|Keys.A);
			_menuAllProfileDisconnect.ShortcutKey = (Keys.Control|Keys.D);
			_menuOption.ShortcutKey = (Keys.Control|Keys.T);
		}

		private void InitContextMenu() {
			_menuMap = new Hashtable();

			_listViewContextMenu = new ContextMenu();
			foreach(GMenuItem item in _menuProfile.MenuItems) {
				GMenuItem cloned = (GMenuItem)item.CloneMenu();
				_menuMap.Add(cloned, item);
				_listViewContextMenu.MenuItems.Add(cloned);
			}

			_iconContextMenu = new ContextMenu();
			AddIconContextMenu(_menuAllProfileConnect, Env.Strings.GetString("Menu._menuAllProfileConnect"));
			AddIconContextMenu(_menuAllProfileDisconnect, Env.Strings.GetString("Menu._menuAllProfileDisconnect"));
			AddIconContextMenu(_menuFileBar1, "-"); //これはBARならなんでもよし
			AddIconContextMenu(_menuExit, Env.Strings.GetString("Menu._menuExit"));
			_taskTrayIcon.ContextMenu = _iconContextMenu;
		}
		private void AddIconContextMenu(GMenuItem basemenu, string text) {
			MenuItem mi = new MenuItem();
			mi.Text = text;
			mi.Click += new EventHandler(OnMenu);
			mi.Index = _iconContextMenu.MenuItems.Count;
			_iconContextMenu.MenuItems.Add(mi);
			_menuMap.Add(mi, basemenu);
		}

		private void OnTaskTrayIconDoubleClicked(object sender, EventArgs args) {
			if(this.WindowState==FormWindowState.Minimized) {
				this.Visible = true;
				this.WindowState = FormWindowState.Normal;
				this.ShowInTaskbar = Env.Options.ShowInTaskBar;
			}
			this.Activate();
		}
		private void OnListViewDoubleClicked(object sender, EventArgs args) {
			ChannelProfile prof = GetSelectedProfile();
			if(prof==null) return;

			Env.Commands.ConnectProfile(prof);
		}
		private void OnSelectedIndexChanged(object sender, EventArgs args) {
			if(GetSelectedProfile()!=null) _menuProfile.Enabled = true;
		}
		private void OnListViewKeyPress(object sender, KeyPressEventArgs args) {
			if(args.KeyChar=='\r') {
				ChannelProfile prof = GetSelectedProfile();
				if(prof!=null) {
					if(Control.ModifierKeys==Keys.Control)
						Env.Commands.EditProfile(prof);
					else if(!Env.Connections.IsConnected(prof))
						Env.Commands.ConnectProfile(prof);
				}
			}
		}
		private void OnListViewMouseUp(object sender, MouseEventArgs args) {
			ChannelProfile prof = GetSelectedProfile();
			if(args.Button!=MouseButtons.Right || prof==null) return;

			AdjustMenu(_listViewContextMenu.MenuItems, prof);
			_listViewContextMenu.Show(this, new Point(args.X, args.Y));
		}
		private void OnProfileMenuClicked(object sender, EventArgs args) {
			AdjustMenu(_menuProfile.MenuItems, GetSelectedProfile());
		}
		

		private void OnMenu(object sender, EventArgs args) {
			//MenuMapにあれば変換。これでContext Menuを処理
			object t = _menuMap[sender];
			if(t!=null) sender = t;

			Commands cmd = Env.Commands;
			if(sender==_menuNewProfile)
				cmd.CreateNewProfile();
			else if(sender==_menuTaskTray) {
				this.WindowState = FormWindowState.Minimized;
				this.Visible = false;
				this.ShowInTaskbar = false;
			}
			else if(sender==_menuExit)
				this.Close();
			else if(sender==_menuProfileProperty)
				cmd.EditProfile(GetSelectedProfile());
			else if(sender==_menuProfileRemove)
				cmd.RemoveProfile(GetSelectedProfile());
			else if(sender==_menuProfileUp)
				cmd.MoveProfileUp(GetSelectedProfile());
			else if(sender==_menuProfileDown)
				cmd.MoveProfileDown(GetSelectedProfile());
			else if(sender==_menuProfileConnect)
				cmd.ConnectProfile(GetSelectedProfile());
			else if(sender==_menuProfileDisconnect)
				cmd.DisconnectProfile(GetSelectedProfile());
			else if(sender==_menuAllProfileConnect)
				cmd.ConnectAllProfiles();
			else if(sender==_menuAllProfileDisconnect)
				cmd.DisconnectAllProfiles();
			else if(sender==_menuOption)
				cmd.ShowOptionDialog();
			else if(sender==_menuAboutBox)
				cmd.ShowAboutBox();
			else
				Debug.WriteLine("not implemented!");
		}

		protected override void OnClosing(CancelEventArgs e) {
			if(Env.Connections.HasConnection && Env.Options.WarningOnExit && Util.AskUserYesNo(this, Env.Strings.GetString("Message.MainForm.AskDisconnect"))==DialogResult.No)
				e.Cancel = true;
			else {
				Env.Connections.CloseAll();
				Env.Options.FrameState = this.WindowState;
				Env.Options.FramePosition = this.DesktopBounds;
			}
			base.OnClosing(e);
		}



		public void RefreshAllProfiles() {
			_list.Items.Clear();
			foreach(ChannelProfile prof in Env.Profiles) {
				string port_postfix = prof.ProtocolType==ProtocolType.Udp? "(UDP)" : "";
				ListViewItem li = new ListViewItem();
				li.Text = prof.SSHHost;
				li.SubItems.Add(prof.SSHAccount);
				li.SubItems.Add(Util.GetProfileTypeString(prof));
				li.SubItems.Add(prof.ListenPort.ToString() + port_postfix);
				li.SubItems.Add(prof.DestinationHost);
				li.SubItems.Add(prof.DestinationPort.ToString() + port_postfix);
				li.SubItems.Add(Util.GetProfileStatusString(prof));

				li.Tag = prof;
				_list.Items.Add(li);
			}

			_menuAllProfile.Enabled = _list.Items.Count>0;
			_menuProfile.Enabled = _list.Items.Count>0;

		}

		public void RefreshProfileStatus(ChannelProfile prof) {
			ListViewItem li = FindItem(prof);
			Debug.Assert(li!=null);
			li.SubItems[6].Text = Util.GetProfileStatusString(prof);
		}

		private ListViewItem FindItem(ChannelProfile prof) {
			foreach(ListViewItem li in _list.Items) {
				if(li.Tag==prof) return li;
			}
			return null;
		}


		private void AdjustMenu(Menu.MenuItemCollection items, ChannelProfile prof) {
			bool connected = prof!=null && Env.Connections.IsConnected(prof);
			items[_menuProfileConnect.Index].Enabled = prof!=null && !connected;
			items[_menuProfileDisconnect.Index].Enabled = prof!=null && connected;
			items[_menuProfileRemove.Index].Enabled = prof!=null && !connected;
			items[_menuProfileProperty.Index].Enabled = prof!=null && !connected;

			int index = prof==null? -1 : Env.Profiles.IndexOf(prof);
			items[_menuProfileUp.Index].Enabled = prof!=null && index>0;
			items[_menuProfileDown.Index].Enabled = prof!=null && index<Env.Profiles.Count-1;
		}



		public ChannelProfile GetSelectedProfile() {
			if(_list.SelectedItems.Count==0) return null;
			return (ChannelProfile)_list.SelectedItems[0].Tag;
		}
		public int GetSelectedIndex() {
			ListView.SelectedIndexCollection indices = _list.SelectedIndices;
			return indices.Count>0? indices[0] : -1;
		}
		public void SetSelectedIndex(int index) {
			_list.Items[index].Selected = true;
		}

		protected override void OnActivated(EventArgs e) {
			base.OnActivated (e);
			//外部からShowWindowでアクティブにするとなぜかリストビューがおかしくなる
			this.Visible = true;
			this.WindowState = FormWindowState.Normal;
			this.ShowInTaskbar = Env.Options.ShowInTaskBar;
		}


	}
}
