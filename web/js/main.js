nr_mail=0;
dom_id=0;
myrecord='';
isc.ClassFactory.defineClass("TabsPanel", isc.VLayout);

isc.TabsPanel.addProperties({
	width: "83%",
	height: "100%",
	align:"center",
	layoutTopMargin:10,
	layoutRightMargin:10,
	initWidget: function() {
		this.Super("initWidget", arguments);
		
		this.listGrid = isc.ListGrid.create({
			ID: "listGrid",
			container: this,
			width: "100%",
			height:"30%",
			alternateRecordStyles:true,
			dataSource: isc.DS.get("domains"),
			dataProperties: {resultSize: 150}, // see ListGrid.dataProperties and ResultSet.resultSize
			autoFetchData: true,
			showResizeBar: true,
			recordClick: function (viewer, record) {
				isc.RPCManager.sendRequest({
					containsCredentials: false,
					actionURL: "MipanelHttpd.php",
					useSimpleHttp: true,
					evalResult: true,
					showPrompt: false,
					params: {
						operationType: "status",
						domain_id: record.domain_id
					},
					callback: function (rpcResponse) {
						if (rpcResponse.data.status == isc.RPCResponse.STATUS_FAILURE) {
							isc.RPCManager.handleError(rpcResponse.data, null);
							return;
						}
						var text = "Web server is ";
						var icon = "[SKIN]/actions/";
						if (rpcResponse.data.httpdStatus) {
							text += "started";
							icon += "approve.png";
							op_type = "stop";
						}
						else {
							text += "stopped";
							icon += "remove.png";
							op_type = "start";
						}
						viewer.container.label3.setContents(text);
						viewer.container.label3.setIcon(icon);
						viewer.container.label3Button.setTitle("[ " + op_type + " ]");
						viewer.container.label3Button.operationType = op_type;
						viewer.container.label3Button.domainId = record.domain_id;
					}
				});
				stack1.setSectionTitle(2,record.domain.toUpperCase());
				stack1.showSection(2);
				this.container.loadRecord(record);
				mailboxForm.clearErrors(true);
				fwForm.clearErrors(true);
				aliasForm.clearErrors(true);
				aliasToForm.clearErrors(true);
				settForm.clearErrors(true);
				dnsForm.clearErrors(true);
				formDNS.clearErrors(true);
				//pentru a pastra numele domeniului 
				settForm.editRecord(record);
				settForm.setValue("password","");
				dnsForm.editRecord(record);
				enableMail.editRecord(record);
				enableWebService.editRecord(record);
				enableWebAcces.editRecord(record);
				this.container.summary.show();
				this.container.formLayout.hide();
				this.container.wizardV.hide();
				this.container.wizardFTPV.hide();
				this.container.tabWeb.hide();
				this.container.tabFTP.hide();
				this.container.tabMail.hide();
				this.container.tabDNS.hide();
				this.container.setLabelContents(record);
				formDNS.hide();
				//face clear la listgrid
				listGrid_FwMailboxes.setData([]);
				listGrid_AliasTo.setData([]);
				
			},
				fields: [
					{name: "domain", title: "Domain",required:"true",filterOnKeypress:true},
					{name: "web", title: "Web", align:"center",  width:100, type:"image", canFilter:false},
		      			{name: "ftp", title: "FTP", align:"center",  width:100, type:"image",canFilter:false},
                       			{name: "dns", title: "DNS", align:"center",  width:100, type:"image", canFilter:false},
                       			{name: "mail", title: "Mail", align:"center",  width:100, type:"image",canFilter:false}
					],  
		  		autoFitData: "vertical",
		  		showFilterEditor: true,
		  		autoFitMaxRecords: 10
		});

		this.TabMailboxes = isc.TabMailboxes.create();
		this.TabAliases = isc.TabAliases.create();
		this.TabFTPSett = isc.TabFTPSett.create();
		this.TabDNSSett = isc.TabDNSSett.create();
		this.TabWebDomain = isc.TabWebDomain.create();
		this.TabWebAliases = isc.TabWebAliases.create();
		
		this.tabWeb=isc.TabSet.create({
			container:this,
                        ID:"tabWeb",
			visibility:"hidden",
			tabs:[	
				{title:"Settings", pane:this.TabWebDomain, ID:"settWebTab"},
				{title:"Aliases", pane:this.TabWebAliases, ID:"aliasesWebTab"}//,<-----Versiuni viitoare
				/*{title:"VHOSTS", pane:label2, ID:"vhostWebTab"},
				{title:"Statistics", pane:label3, ID:"statWebTab"},
				{title:"Logs", pane:label4, ID:"logsWebTab"}*/
			]
              	});

		this.tabMail=isc.TabSet.create({
			container:this,
			ID:"tabMail",
			visibility:"hidden",
			tabs:[
				{title:"Mailboxes", pane:this.TabMailboxes, ID:"mailboxMailTab"},
				{title:"Global Aliases", pane:this.TabAliases, ID:"fwMailTab"}/*, <-----Versiuni viitoare
				{title:"Catch-all", pane:label10, ID:"catchMailTab"},
				{title:"Auto-Reply", pane:label11, ID:"autoMailTab"},
				{title:"Logs", pane:label12, ID:"logsMailTab"}*/
			]
		});
	

		this.tabFTP=isc.TabSet.create({
			ID:"tabFTP",
			visibility:"hidden",
			container:this,
			tabs:[
				{title:"Settings", pane:this.TabFTPSett, ID:"settFTPTab"}/*,<------ Versiuni viitoare
				{title:"SmartAuth", pane:label6, ID:"smartFTPTab"},
				{title:"Logs", pane:label7, ID:"logsFTPTab"}*/
			]
		});

		this.tabDNS=isc.TabSet.create({
			ID:"tabDNS",
			visibility:"hidden",
			container:this,
			tabs:[
				{title:"Settings", pane:this.TabDNSSett, ID:"settDNSTab"}
			]
		});

		
		this.label1=isc.Label.create({
			container:this,
			ID:"label1",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"Web service is not enabled"
		});
		this.label1Button=isc.Button.create({
			container:this,
			title:"[ configure...]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			click: function() 
				{
					summary.hide();
					tabWeb.selectTab(0);
					if (this.container.listGrid.getSelectedRecord() && this.container.listGrid.getSelectedRecord().site_id!='')
					{						
						tabWeb.show();		
						tabWeb.enableTab(1);			
						configV.show();
						unconfigV.hide();
					}
					else 
					{
						tabWeb.show();
						tabWeb.disableTab(1);
						configV.hide();
						unconfigV.show();
					}
				}		
		});
		
		this.webservice=isc.HLayout.create({
			container:this,
			width:300,
			height:10,
			members:[this.label1,this.label1Button]		
		});
		this.label2=isc.Label.create({
			container:this,
			ID:"label2",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"Web access is not enabled"
		});
		this.label2Button=isc.Button.create({
			container:this,
			title:"[ configure...]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			click: function() 
				{
					summary.hide();
					tabWeb.selectTab(0);
					if (this.container.listGrid.getSelectedRecord().site_id!='')
					{
						tabWeb.show();					
						tabWeb.enableTab(1);
						configV.show();
						unconfigV.hide();
					}
					else 
					{
						tabWeb.show();
						tabWeb.disableTab(1);
						configV.hide();
						unconfigV.show();
					}
				}		
		});
		
		this.webaccess=isc.HLayout.create({
			container:this,
			width:300,
			height:10,
			members:[this.label2,this.label2Button]		
		});
		this.label3=isc.Label.create({
			container:this,
			ID:"label3",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"Web server is stopped"
		});
		this.label3Button=isc.Button.create({
			container:this,
			title:"[ start ]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			operationType: "start",
			domainId: 0,
			click: function() {
				var label3Button = this;
				isc.RPCManager.sendRequest({
					containsCredentials: false,
					actionURL: "MipanelHttpd.php",
					useSimpleHttp: true,
					evalResult: true,
					showPrompt: false,
					params: {
						operationType: this.operationType,
						domain_id: this.domainId
					},
					callback: function (rpcResponse) {
						if (rpcResponse.data.status == isc.RPCResponse.STATUS_FAILURE) {
							isc.RPCManager.handleError(rpcResponse.data, null);
							return;
						}
						var label3 = label3Button.container.label3;
						var text = "Web server is ";
						var icon = "[SKIN]/actions/";
						if (label3Button.operationType == "start") {
							text += "started";
							icon += "approve.png";
							op_type = "stop";
						}
						else {
							text += "stopped";
							icon += "remove.png";
							op_type = "start";
						}
						label3.setContents(text);
						label3.setIcon(icon);
						label3Button.setTitle("[ " + op_type + " ]");
						label3Button.operationType = op_type;
					}
				});
			}
		});
		this.webcontrol=isc.HLayout.create({
			container: this,
			width: 300,
			height: 10,
			members: [this.label3, this.label3Button]
		});
		
		this.label23V=isc.VLayout.create({
			container:this,
		    	layoutLeftMargin: 50,
			height:40,
		    	membersMargin: 10,
				members: [this.webaccess,this.webcontrol]
		});

		this.label4=isc.Label.create({
			container:this,
			ID:"label4",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"FTP service is not enabled"
		});
		this.label4Button=isc.Button.create({
			container:this,
			title:"[ configure...]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			click: function() 
				{
					summary.hide();
					tabFTP.show();
				}		
		});
		
		this.ftp=isc.HLayout.create({
			container:this,
			width:300,
			height:10,
			members:[this.label4,this.label4Button]		
		});	
		this.label5=isc.Label.create({
			container:this,
			ID:"label5",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"Mail service is not enabled"
		});
		this.label5Button=isc.Button.create({
			container:this,
			title:"[ configure...]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			click: function() 
				{
					summary.hide();
					tabMail.show();
				}		
		});
		
		this.mail=isc.HLayout.create({
			container:this,
			width:300,
			height:10,
			members:[this.label5,this.label5Button]		
		});

		this.label6=isc.Label.create({
			container:this,
			ID:"label6",
			autoDraw: false,
			width:"100%",
			height:10,
			setLabelContents : function (content) {
      				this.setContents(content + " Mailboxes"); 
   			}
		});
		this.label7=isc.Label.create({
			container:this,
			ID:"label7",
			autoDraw: false,
			width:"100%",
			height:10,
		   	setLabelContents : function (content) {
      				this.setContents(content + " Global Aliases"); 
   			}
		});
		this.label67V=isc.VLayout.create({
			container:this,
		    	layoutLeftMargin: 50,
			height:40,
			visibility:"hidden",
		    	membersMargin: 10,
		    	members: [this.label6,this.label7]
		});
		this.label8=isc.Label.create({
			container:this,
			ID:"label8",
			autoDraw: false,
			width:"100%",
			height:10,
			icon:"[SKIN]/actions/remove.png",
		   	contents:"DNS service is not enabled"
		});
		this.label8Button=isc.Button.create({
			ID:"label8Button",
			container:this,
			title:"[ configure...]",
			width:80,
			height:14,
			baseStyle: "cssButton",
			click: function() 
				{
					summary.hide();
					this.container.tabDNS.show();
					if (this.container.listGrid.getSelectedRecord()==null)			
					{
						
						for (i=0; i<listGrid.getTotalRows();i++)
						{
							if (myrecord.domain_id==listGrid.data.localData[i].domain_id)
							{
								myrecord.soa_id=listGrid.data.localData[i].soa_id;
								listGrid.selectSingleRecord(i);
								listGrid_dns.fetchData({zone:listGrid.data.localData[i].soa_id});
								formToolbar.show();
							}
						}
					}
					else
					{
						if (this.container.listGrid.getSelectedRecord().soa_id!='')
						{
							formToolbar.show();
							listGrid_dns.fetchData({zone:listGrid.getSelectedRecord().soa_id});
							label_dns.hide();
							saveButtonDNS.hide();
						}
						else 
						{
							formToolbar.hide();
							label_dns.show();
							saveButtonDNS.show();
						}
					}
				}		
		});
		this.dns=isc.HLayout.create({
			container:this,
			width:300,
			height:10,
			members:[this.label8,this.label8Button]		
		});

		this.summary=isc.VLayout.create({
			container:this,
			ID:"summary",
			visibility:"hidden",
			layoutLeftMargin:80,
			layoutTopMargin:50,
			membersMargin:15,
			members:[this.webservice,this.label23V,this.ftp,this.mail,this.label67V,this.dns]
		});
		this.dynamForm=isc.DynamicForm.create({
			ID:"dynamForm",
			container: this,
			dataSource: isc.DS.get("domains"),
			autoDraw: false,
			height: 48,
		       	padding:4,
			fields: [
			{name: "domain", type: "text", title: "Domain",required:"true"}
			]
		});
	
		this.saveButton = isc.IButton.create({
			container: this,
			title: "Save",
			icon: "[SKIN]/actions/save.png",
			click: "this.container.saveRecord();"
		});

		this.formLayout=isc.VLayout.create({
			container:this,
			ID:"formLayout",
			width:300,
			height:200,
			padding:50,
			align:"center",
			visibility:"hidden",
			members: [this.dynamForm, this.saveButton]
		});
		this.wizardForm=isc.DynamicForm.create({
			container:this,
			ID:"wizardForm",
			width:50,
			container: this,
			fields: [{name: "domain", type: "text", title: "Name",required:"true"}]
		});
		this.label=isc.HTMLFlow.create({
			container:this,
		   	ID:"statusReport",
		    	contents :"Select services to setup & enable " 
		 
		});
		this.wizardForm2=isc.DynamicForm.create({
			ID:"wizardForm2",
			container: this,
			fields: [			
			{name: "enable_web", type: "checkbox", title: "Web"},
			{name: "enable_ftp", type: "checkbox", title: "FTP"},
			{name: "enable_mail", type: "checkbox", title: "Mail"},
			{name: "enable_dns", type: "checkbox", title: "DNS"}
			]
		});
		
		this.wizardFormFTP=isc.DynamicForm.create({
			ID:"wizardFormFTP",
			container:this,
		    	fields: [
				{name: "username",title:"Username",height:"20",type:"text",required:true},
				{name: "password",title:"Password",type: "password",required:true},
				{name: "password_confirm", title: "Confirm", type: "password", required:true,
			 		length: 20, validators: [{
			   	  		type: "matchesField",
			     			otherField: "password",
			     			errorMessage: "Passwords do not match"
					 }]
				}
				]
		});
		this.createButtonFTP = isc.IButton.create({
			container: this,
			title: "Create Domain",
			width:150,
			icon: "[SKIN]/actions/approve.png",
			click: function () 
			{ 
				if (wizardFormFTP.validate()) 
				{ 
					valuesmanager_wizardForm.addMember(wizardFormFTP);
					wizardFTPV.hide();
					this.container.createDomain();
				}
			}
		});

		this.wizardFTPV=isc.VLayout.create({
			container:this,
			ID:"wizardFTPV",
			visibility:"hidden",
			membersMargin:10,
			layoutLeftMargin:30,
			layoutTopMargin:30,
			members:[this.wizardFormFTP,this.createButtonFTP]	
		});
		this.valuesmanager_wizardForm=isc.ValuesManager.create({
			ID:"valuesmanager_wizardForm",
			container:this,
			dataSource: isc.DS.get("domains"),
			members:[this.wizardForm,this.wizardForm2]		
		});
		
		this.createButton = isc.IButton.create({
			container: this,
			title: "Create Domain",
			width:150,
			icon: "[SKIN]/actions/approve.png",
			click: function () 
			{ 
				if (wizardForm.validate())
				{
				if (wizardForm2.items[1]._value==true)
				{
					this.container.wizardV.hide();					
					this.container.wizardFTPV.show();
				}
				else this.container.createDomain();
				}
			}
		});
		this.wizardV=isc.VLayout.create({
			container:this,
			ID:"wizardV",
			visibility:"hidden",
			membersMargin:10,
			layoutLeftMargin:30,
			layoutTopMargin:30,
			members:[this.wizardForm,this.label, this.wizardForm2,this.createButton]
		});
		this.hLayout=isc.HLayout.create({
			container:this,
			ID:"hLayout",
			width:"100%",
			height:"70%",
			border:"1px solid gray",
			members: [this.tabWeb,this.tabMail, this.tabFTP,this.tabDNS,this.formLayout,this.summary,this.wizardV,this.wizardFTPV]
		});	
		
		this.addMember(this.listGrid);
		this.addMember(this.hLayout);
		
	},
	loadRecord: function(record) {
		this.TabMailboxes.setDomainId(record.domain_id);
		this.TabAliases.setDomainId(record.domain_id);
		this.TabWebDomain.setDomainId(record.domain_id);
		this.TabWebDomain.setSiteId(record.site_id);
		this.TabDNSSett.setDomainId(record.domain_id);
		this.TabDNSSett.setSoaId(record.soa_id);
		
		this.TabWebDomain.setRecord(record);
		this.TabWebAliases.setRecord(record);
		this.TabWebAliases.setSiteId(record.site_id);		

		this.TabDNSSett.setRecord(record);
		this.TabFTPSett.setRecord(record);

		valuesManager.editNewRecord(record);
		aliasForm.editNewRecord(record);
	},
	setLabelContents: function(record){
		if (record.site_id!='') 
		{
			this.label1.setContents("Web service is enabled");
			this.label1.setIcon("[SKIN]/actions/approve.png");	
			if (record.enabled==1)
			{ 
				this.label2.setContents("Web access is enabled");
				this.label2.setIcon("[SKIN]/actions/approve.png");
			}		
			else 
			{
				this.label2.setContents("Web access is not enabled");
				this.label2.setIcon("[SKIN]/actions/remove.png");
			}
		}
		else 
		{
			this.label1.setContents("Web service is not enabled");
			this.label1.setIcon("[SKIN]/actions/remove.png");	
			this.label2.setContents("Web access is not enabled");
			this.label2.setIcon("[SKIN]/actions/remove.png");	
		}	
		if (record.enable_ftp==true) 
		{
			this.label4.setContents("FTP service is enabled");
			this.label4.setIcon("[SKIN]/actions/approve.png");	
		}	
		else 
		{
			this.label4.setContents("FTP service is not enabled");
			this.label4.setIcon("[SKIN]/actions/remove.png");
		}		
		if (record.enable_mail==true) 
		{
			this.label5.setContents("Mail service is enabled");
			this.label5.setIcon("[SKIN]/actions/approve.png");	
			this.label67V.show();
			
			if (listGrid_Mailboxes.getTotalRows()==0 || listGrid_Mailboxes.getTotalRows()==1000)
			{
				nr_mail=record.nr_mailboxes;
				this.label6.setLabelContents(nr_mail); 
			}
			else 
			{
				this.label6.setLabelContents(listGrid_Mailboxes.getTotalRows());
			}
			if (listGrid_Aliases.getTotalRows()==0 || listGrid_Aliases.getTotalRows()==1000)
				this.label7.setLabelContents(record.nr_aliases); 
			else this.label7.setLabelContents(listGrid_Aliases.getTotalRows());
		}
		else
		{	
			this.label5.setContents("Mail service is not enabled");
			this.label5.setIcon("[SKIN]/actions/remove.png");
			this.label67V.hide();	
		}
		if (record.soa_id!='') 
		{
			this.label8.setContents("DNS service is enabled");
			this.label8.setIcon("[SKIN]/actions/approve.png");	
		}	
		else 
		{
			this.label8.setContents("DNS service is not enabled");
			this.label8.setIcon("[SKIN]/actions/remove.png");
		}	
	},
	saveRecord: function() {
		this.dynamForm.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update"); 
			this.container.dynamForm.editNewRecord();
		});
		this.dynamForm.editNewRecord();
	},
	createDomain: function() {
		this.valuesmanager_wizardForm.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update"); 
			this.container.valuesmanager_wizardForm.editNewRecord();
		});
		this.valuesmanager_wizardForm.editNewRecord();
	}
});
isc.ClassFactory.defineClass("TabWebDomain", isc.VLayout);

isc.TabWebDomain.addProperties({
	width: "100%",
	height: "100%",
	align:"center",
	padding:30,
	showResizeBar:false,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.label_unconf=isc.Label.create({
			container:this,
			ID:"label_unconf",
			autoDraw: false,
			width:"100%",
			align:"center",
		   	contents:"Web service for this domain is not configured. Click below to configure ..."
		});
		this.enableWebService=isc.DynamicForm.create({
			container:this,
   			ID: "enableWebService",
    			dataSource: isc.DS.get("domainsWS"),
			fields: [
				{name: "domain_id",showIf:"false"},
				]
		});

		this.enableWebAcces=isc.DynamicForm.create({
			container:this,
   			ID: "enableWebAcces",
    			dataSource: isc.DS.get("sites"),
			width:100,
			fields: [
				{name: "site_id",showIf:"false"},
				{name: "enabled",type:"checkbox", title:"Enable web access",valueMap:{1:"true",0:"false"}, align:"left"}
				]
		});
		this.enableWebAccesButton=isc.IButton.create({
			ID:"enableWebAccesButton",
			container: this,
			title: "Save",
			width:100,
			//visibility:"hidden",
			icon: "[SKIN]/actions/approve.png",
			click: function () 
			{
				this.container.saveAccessWeb();
				label2.setContents("Web access is enabled");
				label2.setIcon("[SKIN]/actions/approve.png");
			}
		});
		var ok=false;
		this.configButton=isc.IButton.create({
			ID:"configButton",
			container: this,
			title: "Enable Web Service",
			width:150,
			icon: "[SKIN]/actions/approve.png",
			click: function (){
			
				this.container.saveconfigWeb();
				var record = listGrid.getSelectedRecord();
				listGrid.setData([]);
				listGrid.fetchData({},
                                function (dsResponse, data, dsRequest) {
          				for (i=0; i < listGrid.data.totalRows; i++)
					{
						if (listGrid.data.localData[i].domain_id == record.domain_id)
						{
							record = isc.addProperties(record,{site_id:listGrid.data.localData[i].site_id});
							enableWebAcces.editRecord(record);
							listGrid_WAliases.fetchData({site_id: listGrid.data.localData[i].site_id});
						}
					}                              
                                });
				tabWeb.enableTab(1);
				newButtonWebAliases.setDisabled(false);
				label1.setContents("Web service is enabled");
				label1.setIcon("[SKIN]/actions/approve.png"); 
				
				}
		});
		
		this.unconfigButtonV=isc.VLayout.create({
			container:this,
			align:"center",
			width:"50%",
			height:"100%",
			layoutLeftMargin:400,
			members:[this.enableWebService,this.configButton]		
		});

		this.unconfigV=isc.VLayout.create({
			ID:"unconfigV",
			container:this,
			visibility:"hidden",
			align:"center",
			members:[this.label_unconf,this.unconfigButtonV]		
		});
		
		this.configV=isc.VLayout.create({
			ID:"configV",
			container:this,
			visibility:"hidden",
			layoutTopMargin:100,
			layoutLeftMargin:100,
			membersMargin:20,
			align:"left",
			members:[this.enableWebAcces,this.enableWebAccesButton]		
		});

		this.addMember(this.configV);
		this.addMember(this.unconfigV);

	},

	setSiteId: function(siteId) {
		this.siteId = siteId;
		
	},
	setDomainId: function(domainId) {
		this.domainId = domainId;
	},
	
	saveconfigWeb: function()
	{ 
		this.enableWebService.saveData(
		function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return ;
			else this.container.config2();
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
		});

	},
	saveconfigWebCallback: function() 
	{
		listGrid.setData([]);
		listGrid.fetchData();
	},
	config2: function()
	{
		unconfigV.hide();
		configV.show();
		enableWebAccesButton.show();
	},
		
	saveAccessWeb: function()
	{ 
		this.enableWebAcces.saveData({ target: this, methodName: "saveAccessWebCallback" },
		function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
		});
	},
	saveAccessWebCallback: function()
	{ 
		listGrid.setData([]);
		listGrid.fetchData(); 
		
	},
	setRecord: function(record) {
		this.record = record;
	},
	domainId:null,
	siteId:null,
	record:null
});

isc.ClassFactory.defineClass("TabWebAliases", isc.VLayout);
isc.TabWebAliases.addProperties({
        width: "100%",
        height: "100%",
        align:"center",
        padding:30,
        showResizeBar:false,
        initWidget: function() {
                this.Super("initWidget", arguments);

		this.listGrid_WAliases=isc.ListGrid.create({
                        ID:"listGrid_WAliases",
                        container:this,
                        width:"100%",
                        height:"100%",
                        alternateRecordStyles:true,
                        dataSource: isc.DS.get("site_aliases"),
                        autoFetchData: false,
                        showResizeBar: false,
                        recordClick: function (viewer, record){
                                this.container.deleteButton.setDisabled(false);
				//this.container.web_aliasForm.editRecord(record);
                                //this.container.loadToRecord(record);
                                this.container.newButton.setDisabled(false);
                        },
                                fields: [
                                        {name: "site_id",showIf:"false"},
                                        {name: "name", title: "Alias",required:"true",canEdit:false}
                                        ],
                                autoFitData: "vertical",
                                showFilterEditor: false,
                                autoFitMaxRecords: 10
                });

                this.deleteButton = isc.IButton.create({
                        container: this,
                        title: "Remove",
                        icon: "[SKIN]/TabSet/close.png",
                        showDisabledIcon: false,
                        disabled: true,
                        click: function () { this.container.deleteRecord();}
                });
                this.web_aliasForm=isc.DynamicForm.create({
                        ID:"web_aliasForm",
                        container: this,
                        dataSource: isc.DS.get("site_aliases"),
                        fields: [
			{name: "site_id",showIf:"false"},
                        {name: "name", title: "Name",type: "text", required:"true"}],
                });
                this.newButton = isc.IButton.create({
			ID:"newButtonWebAliases",
                        container: this,
                        title: "New",
                        icon: "[SKIN]/actions/add.png",
                        showDisabledIcon: false,
                        click: function ()
			{
				this.container.saveRecord();
			}
		});
		this.WAliasGridH = isc.HLayout.create({
                        container:this,
                        showResizeBar:false,
                        membersMargin:10,
                        height:"100%",
                        layoutTopMargin:30,
                        members: [this.listGrid_WAliases, this.deleteButton]
                });
                this.WAliasAddH = isc.HLayout.create({
                        container:this,
                        showResizeBar:false,
                        membersMargin:10,
                        layoutTopMargin:10,
                        members: [this.web_aliasForm, this.newButton]
                });

                this.WAlias=isc.VLayout.create({
                        container:this,
                        showResizeBar:false,
                        layoutLeftMargin:30,
                        width:"80%",
                        height:"100%",
                        members: [this.WAliasGridH, this.WAliasAddH]
                });

		this.addMember(this.WAlias);
	},
	saveRecord: function() {
                this.web_aliasForm.saveData(function(dsResponse, data, dsRequest) {
                        if (dsResponse.status != 0)
                                return;
                        if (!this.isNewRecord())
                                return;
                        this.setSaveOperationType("update");
                });
        },
	deleteRecord: function() {
                if (listGrid_WAliases.getSelectedRecord()==null) isc.say("Select a alias first!");
                else isc.ask("Really delete this alias? ", function (value) {
                        if (value)
                        {
                                this.container.__deleteRecord();
                        }

                }, {container: this});
        },

        __deleteRecord: function() {
                this.listGrid_WAliases.dataSource.removeData(this.listGrid_WAliases.getSelectedRecord(), { target: this, methodName: "deleteRecordCallback" });
        },
        deleteRecordCallback: function(dsResponse, data, dsRequest) {
                if (dsResponse.status != 0)
                        return;
                this.web_aliasForm.editNewRecord();
	},
	setSiteId: function(siteId) {
                this.siteId = siteId;
		if (siteId != '')
		{
			this.newButton.setDisabled(false);
			this.web_aliasForm.setValue("site_id",siteId);
			this.listGrid_WAliases.fetchData({site_id: siteId});
		}
		else 
		{ 
			this.newButton.setDisabled(true);
			this.listGrid_WAliases.setData([]);
		}
        },
        setRecord: function(record) {
                this.record = record;
        },
 	siteId:null,
        record:null
});

isc.ClassFactory.defineClass("TabMailboxes", isc.VLayout);

isc.TabMailboxes.addProperties({
	width: "100%",
	height: "100%",
	align:"center",
	showResizeBar:false,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.listGrid_Mailboxes=isc.ListGrid.create({
			ID:"listGrid_Mailboxes",
			container:this,
			width:"100%",
			height:"100%",
			alternateRecordStyles:true,
			dataSource: isc.DS.get("mailboxes"),
			autoFetchData: false,
			showResizeBar: false,
			recordClick: function (viewer, record){
				this.container.deleteButton.setDisabled(false);
				this.container.saveButton.setDisabled(false);
				this.container.tabset_Mailboxes.show();
				this.container.valuesManager.editRecord(record);
				this.container.loadFwRecord(record);
				aliasForm.clearErrors(true);
				aliasToForm.clearErrors(true);
				this.container.addButton.setDisabled(false);
				this.container.tabset_Mailboxes.enableTab(1);
				if (record.nr_forwards==0 || listGrid_FwMailboxes.getTotalRows()==0) fwCheckForm.items[0].disable();
					else fwCheckForm.items[0].enable();
				
			},
			fields: [
				{name: "domain_id",showIf:"false"},
				{name: "mailbox_id",showIf:"false"},
				{name: "mailbox", title: "Mailbox",required:"true",canEdit:false},
				{name: "addressmail", title: "Address", align:"center",canFilter:false,canEdit:false}
				],  
		  	autoFitData: "vertical",
		  	showFilterEditor: false,
		  	autoFitMaxRecords: 10
		});
		this.enableMail=isc.DynamicForm.create({
			ID:"enableMail",
			container: this,
			height:20,
			dataSource: isc.DS.get("domains"),
			fields:[
				{name: "enable_mail", type: "checkbox", title: "Enable local mail delivery"}
			]
		});
		this.applyButton=isc.IButton.create({
			container: this,
			width:110,
			title: "Apply",
			icon: "[SKIN]/actions/approve.png",
			click: "this.container.saveApplyRecord();"
		});
		this.enableMailH=isc.HLayout.create({
			container:this,
			showResizeBar:false,
			layoutLeftMargin:20,
			layoutTopMargin:20,
			width:"80%",
			height:20,
			membersMargin:500,
			members: [this.enableMail, this.applyButton]
		});
		this.mailboxForm=isc.DynamicForm.create({
			ID:"mailboxForm",
			width:"100%",
			height:"100%",
			align:"center",
			layoutTopMargin:30,
			container: this,
			dataSource: isc.DS.get("mailboxes"),
			fields: [
				{name: "mailbox", type: "text", title: "Mailbox",required:"true"},
				{name: "password", type: "text", title: "Password",required:"true"}
			]
		});
		this.mailboxFormH=isc.HLayout.create({
			container:this,
			layoutTopMargin:30,
			members:[this.mailboxForm]
		});
		this.newButton = isc.IButton.create({
			container: this,
			width:110,
			title: "New Mailbox",
			icon: "[SKIN]/actions/add.png",
			showDisabledIcon: false,
			click: function ()
				{
					this.container.newRecord();
					this.container.mailboxForm.clearErrors(true);
					this.container.tabset_Mailboxes.show();
					this.container.saveButton.setDisabled(false);
					this.container.listGrid_Mailboxes.deselectAllRecords();
					this.container.addButton.setDisabled(true);
					listGrid_FwMailboxes.setData([]);
					listGrid_AliasTo.setData([]);
					this.container.tabset_Mailboxes.disableTab(1);
				}
		});
		
		this.saveButton = isc.IButton.create({
			container: this,
			width:110,
			title: "Save",
			icon: "[SKIN]/actions/save.png",
			click: function () { this.container.saveRecord();},
			disabled: true,
			showDisabledIcon: false
		});
		
		this.deleteButton = isc.IButton.create({
			container: this,
			width:110,
			title: "Delete",
			icon: "[SKIN]/TabSet/close.png",
			showDisabledIcon: false,
			disabled: true,
			click: "this.container.deleteRecord();"
		});
		
		this.toolbar = isc.VLayout.create({
			
			members: [this.newButton, this.saveButton, this.deleteButton]
		});

		this.tabH=isc.HLayout.create({
			container:this,
			showResizeBar:true,
			layoutLeftMargin:30,
			layoutTopMargin:10,
			membersMargin:10,
			width:"80%",
			height:"30%",
			members: [this.listGrid_Mailboxes, this.toolbar]
		});
		
		this.listGrid_FwMailboxes=isc.ListGrid.create({
			ID:"listGrid_FwMailboxes",
			container:this,
			width:"100%",
			alternateRecordStyles:true,
			dataSource: isc.DS.get("forwards"),
			autoFetchData: false,
			showResizeBar: false,
			recordClick: function (viewer, record){
				this.container.removeButton.setDisabled(false);
				this.container.addButton.setDisabled(false);
			},
			fields: [
				{name: "address", title: "Address", align:"center"}
				],  
		  	autoFitData: "vertical",
		  	autoFitMaxRecords: 5
		});
		this.fwForm=isc.DynamicForm.create({
			ID:"fwForm",
			container: this,
			dataSource: isc.DS.get("forwards"),
			fields: [
				{name: "address", type: "text", required:"true"}]
		});
		this.fwCheckForm=isc.DynamicForm.create({
			ID:"fwCheckForm",
			container: this,
			align:"left",
			width:50,
			height:20,
			dataSource: isc.DS.get("mailboxes"),
			fields: [
				{name: "copy_on_forward", type: "checkbox", title:"Also keep a local copy"}
			]
		});
		
		this.valuesManager = isc.ValuesManager.create({
			ID:"valuesManager",
			layoutLeftMargin:30,
			container: this,
			dataSource: isc.DS.get("mailboxes"),
			members: [
				this.mailboxForm,
				this.fwCheckForm
			]
		});
		
		this.addButton = isc.IButton.create({
			container: this,
			title: "Add",
			disabled:false,
			icon: "[SKIN]/actions/add.png",
			showDisabledIcon: false,
			click: "this.container.saveFwRecord();"
		});
		
		this.removeButton = isc.IButton.create({
			container: this,
			title: "Remove",
			icon: "[SKIN]/TabSet/close.png",
			showDisabledIcon: false,
			disabled: true,
			click: "this.container.deleteFwRecord();"
		});
		
		this.fwGridH=isc.HLayout.create({
			container:this,
			showResizeBar:false,
			width:"80%",
			height:"60%",
			layoutTopMargin:30,
			layoutLeftMargin:10,
			membersMargin:10,
			members: [this.listGrid_FwMailboxes, this.removeButton]
		});

		this.fwAddH = isc.HLayout.create({  
			container:this,
			showResizeBar:false,
			width:"80%",
			height:20,
			membersMargin:10,
			layoutLeftMargin:10,
			members: [this.fwForm, this.addButton]
		});
		
		this.fwTab=isc.VLayout.create({
			container:this,
			showResizeBar:false,
			layoutLeftMargin:15,
			membersMargin:10,
			members: [this.fwGridH, this.fwAddH,this.fwCheckForm]
		});

		this.tabset_Mailboxes=isc.TabSet.create({
			ID:"tabset_Mailboxes",
			visibility:"hidden",
			height:"70%",
			width:"100%",
			container:this,
			tabs:[
				{title:"Mailbox", pane:this.mailboxFormH, ID:"tab0"},
				{title:"Forwards", pane:this.fwTab, ID:"tab1"}
			]
		});
		this.addMember(this.enableMailH);
		this.addMember(this.tabH);
		this.addMember(this.tabset_Mailboxes);
		this.newRecord();
		
	},
	
	loadFwRecord: function(record) {
		this.setMailboxId(record.mailbox_id);
		this.fwForm.editNewRecord({mailbox_id:record.mailbox_id});
		
	},
	newRecord: function() {
		this.valuesManager.editNewRecord({domain_id: this.domainId});
		
	},
	setDomainId: function(domainId) {
		this.domainId = domainId;
		this.listGrid_Mailboxes.fetchData({domain_id: domainId});
		this.newButton.setDisabled(false);
		this.deleteButton.setDisabled(true);
	},
	setMailboxId: function(mailId) {
		this.mailId = mailId;
		this.listGrid_FwMailboxes.fetchData({mailbox_id: mailId});
		this.removeButton.setDisabled(true);
	},
	saveRecord: function() {
		this.valuesManager.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
			this.container.valuesManager.editNewRecord({domain_id: this.container.domainId});
			nr_mail=this.container.listGrid_Mailboxes.getTotalRows();
			dom_id=this.domainId;
			label6.setLabelContents(nr_mail);
		
		});
		nr_mail=this.listGrid_Mailboxes.getTotalRows()
		label6.setLabelContents(this.listGrid_Mailboxes.getTotalRows());
		
	},
	saveFwRecord: function() {
		fwForm.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
			this.container.fwForm.editNewRecord({mailbox_id:this.container.mailId});
			fwCheckForm.items[0].enable();
		});
	},
	saveApplyRecord: function() {
		this.enableMail.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
		});
	},
	deleteRecord: function() {
		if (listGrid_Mailboxes.getSelectedRecord()==null) isc.say("Select a mailbox first!");
		else isc.ask("Really delete this mailbox? The associate forwards addresses will be deleted too.", function (value) {
			if (value)
			{		
				this.container.__deleteRecord();
			}
			
		}, {container: this});
	},

	__deleteRecord: function() {
		this.listGrid_Mailboxes.dataSource.removeData(this.listGrid_Mailboxes.getSelectedRecord(), { target: this, methodName: "deleteRecordCallback" });
	},

	deleteRecordCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		this.newRecord();
		nr_mail=this.listGrid_Mailboxes.getTotalRows();
		//dom_id=this.domainId;
		label6.setLabelContents(nr_mail);
		listGrid_FwMailboxes.setData([]);
		fwCheckForm.items[0].disable();		
	},
	deleteFwRecord: function() {
		
		isc.ask("Really delete?", function (value) {
			if (value)
				this.container.__deleteFwRecord();
		}, {container: this});
	},

	__deleteFwRecord: function() {
		this.listGrid_FwMailboxes.dataSource.removeData(this.listGrid_FwMailboxes.getSelectedRecord(), { target: this, methodName: "deleteFwRecordCallback" });
	},

	deleteFwRecordCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		this.fwForm.editNewRecord({mailbox_id:listGrid_FwMailboxes.container.mailId});
		if (this.listGrid_FwMailboxes.getTotalRows()==0) fwCheckForm.items[0].disable();
	},
	domainId: null,
	mailId:null
});

isc.ClassFactory.defineClass("TabAliases", isc.VLayout);

isc.TabAliases.addProperties({
	width: "100%",
	height: "100%",
	align:"center",
	showResizeBar:false,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.listGrid_Aliases=isc.ListGrid.create({
			ID:"listGrid_Aliases",
			container:this,
			width:"100%",
			height:"100%",
			alternateRecordStyles:true,
			dataSource: isc.DS.get("aliases"),
			autoFetchData: false,
			showResizeBar: false,
			recordClick: function (viewer, record){
				this.container.deleteButton.setDisabled(false);
				this.container.aliasToForm.editNewRecord({global_mail_alias_id:record.global_mail_alias_id});
				this.container.loadToRecord(record);
				this.container.addButton.setDisabled(false);
				this.container.newButton.setDisabled(false);
				this.container.AliasTo.show();
			},
				fields: [
					{name: "domain_id",showIf:"false"},
					{name: "global_mail_alias_id",showIf:"false"},
					{name: "name", title: "Alias",required:"true",canEdit:false},
					{name: "addressalias", title: "Address", align:"center",canFilter:false,canEdit:false}
					],  
		  		autoFitData: "vertical",
		  		showFilterEditor: false,
		  		autoFitMaxRecords: 10
		});
		
		this.deleteButton = isc.IButton.create({
			container: this,
			title: "Remove",
			icon: "[SKIN]/TabSet/close.png",
			showDisabledIcon: false,
			disabled: true,
			click: function () { this.container.deleteRecord();}
		});
		this.aliasForm=isc.DynamicForm.create({
			ID:"aliasForm",
			container: this,
			dataSource: isc.DS.get("aliases"),
			fields: [
			{name: "name",type: "text", required:"true"}],
		});
		this.newButton = isc.IButton.create({
			container: this,
			title: "New",
			icon: "[SKIN]/actions/add.png",
			showDisabledIcon: false,
			click: function ()
				{	
					this.container.saveRecord();
				}
		});
		this.AliasGridH = isc.HLayout.create({
			container:this,
			showResizeBar:false,
			membersMargin:10,
			height:"100%",
			layoutTopMargin:30,
			members: [this.listGrid_Aliases, this.deleteButton]
		});
		this.AliasAddH = isc.HLayout.create({
			container:this,
			showResizeBar:false,
			membersMargin:10,
			layoutTopMargin:10,
			members: [this.aliasForm, this.newButton]
		});

		this.Alias=isc.VLayout.create({
			container:this,
			showResizeBar:true,
			layoutLeftMargin:30,
			width:"80%",
			height:"50%",
			members: [this.AliasGridH, this.AliasAddH]
		});

		this.listGrid_AliasTo=isc.ListGrid.create({
			ID:"listGrid_AliasTo",
			container:this,
			width:"100%",
			alternateRecordStyles:true,
			dataSource: isc.DS.get("aliasesto"),
			autoFetchData: false,
			showResizeBar: false,
			recordClick: function (viewer, record){
				this.container.removeButton.setDisabled(false);
				this.container.addButton.setDisabled(false);
			},
			fields: [
				{name: "address", align:"center"}
				],  
		  	autoFitData: "vertical",
		  	autoFitMaxRecords: 5
		});
		
		this.aliasToForm=isc.DynamicForm.create({
			ID:"aliasToForm",
			container: this,
			dataSource: isc.DS.get("aliasesto"),
			fields: [
			{name: "address", type: "text", required:"true"}]
		});

		this.addButton = isc.IButton.create({
			container: this,
			title: "Add",
			showDisabledIcon: false,
			disabled:true,
			icon: "[SKIN]/actions/add.png",
			click: function () {this.container.saveToRecord();}
		});
		this.removeButton = isc.IButton.create({
			container: this,
			title: "Remove",
			icon: "[SKIN]/TabSet/close.png",
			showDisabledIcon: false,
			disabled: true,
			click: "this.container.deleteToRecord();"
		});
		
		this.AliasToGridH=isc.HLayout.create({
			container:this,
			showResizeBar:false,
			width:"80%",
			height:"50%",
			layoutLeftMargin:30,
			membersMargin:10,
			members: [this.listGrid_AliasTo, this.removeButton]
		});

		this.AliasToAddH = isc.HLayout.create({
			container:this,
			showResizeBar:false,
			width:"80%",
			height:"40%",
			layoutLeftMargin:30,
			layoutTopMargin:10,
			membersMargin:10,
			members: [this.aliasToForm, this.addButton]
		});
		
		this.AliasTo=isc.VLayout.create({
			container:this,
			showResizeBar:false,
			height:"50%",
			visibility:"hidden",
			members: [this.AliasToGridH, this.AliasToAddH]
		});

		this.addMember(this.Alias);
		this.addMember(this.AliasTo);
	},
	setDomainId: function(domainId) {
		this.domainId = domainId;
		this.listGrid_Aliases.fetchData({domain_id: domainId});
		this.newButton.setDisabled(false);
		this.deleteButton.setDisabled(true);
	},
	loadToRecord: function(record) {
		this.setAliasId(record.global_mail_alias_id);
		aliasToForm.editNewRecord(record);
	},
	newRecord: function() {
		this.alisForm.editNewRecord({domain_id:listGrid_Aliases.container.domainId});
	},
	setAliasId: function(aliasId) {
		this.aliasId = aliasId;
		this.listGrid_AliasTo.fetchData({global_mail_alias_id: aliasId});
		this.removeButton.setDisabled(true);
	},
	saveRecord: function() {
		this.aliasForm.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
			this.container.aliasForm.editNewRecord({domain_id:this.container.domainId});
			label7.setLabelContents(this.container.listGrid_Aliases.getTotalRows());
		});
	label7.setLabelContents(this.listGrid_Aliases.getTotalRows());
	},
	saveToRecord: function() {
		this.aliasToForm.saveData(function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
			this.container.aliasToForm.editNewRecord({global_mail_alias_id:this.container.aliasId});
		});
	
	listGrid.setData([]);
	listGrid.fetchData(); 

	},
	deleteRecord: function() {
		if (listGrid_Aliases.getSelectedRecord()==null) isc.say("Select a alias first!");
		else isc.ask("Really delete? The associate addresses will be deleted too.", function (value) {
			if (value)
				this.container.__deleteRecord();
			this.container.listGrid_AliasTo.fetchData(this.container.aliasId);
			
		}, {container: this});
	},

	__deleteRecord: function() {
		this.listGrid_Aliases.dataSource.removeData(this.listGrid_Aliases.getSelectedRecord(), { target: this, methodName: "deleteRecordCallback" });
	},

	deleteRecordCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		label7.setLabelContents(this.listGrid_Aliases.getTotalRows());
		listGrid_AliasTo.setData([]);
	},
	deleteToRecord: function() {
		
		isc.ask("Really delete?", function (value) {
			if (value)
				this.container.__deleteToRecord();
		}, {container: this});
	},

	__deleteToRecord: function() {
		this.listGrid_AliasTo.dataSource.removeData(this.listGrid_AliasTo.getSelectedRecord(), { target: this, methodName: "deleteToRecordCallback" });
	},

	deleteToRecordCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		this.aliasToForm.editNewRecord({global_mail_alias_id:listGrid_AliasTo.container.aliasId});
		
	},
	domainId: null,
	aliasId:null
});

isc.ClassFactory.defineClass("TabFTPSett", isc.VLayout);

isc.TabFTPSett.addProperties({
	width: "100%",
	height: "100%",
	padding:30,
	align:"center",
	showResizeBar:false,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.settForm=isc.DynamicForm.create({
			container:this,
		    	ID: "settForm",
		    	dataSource: isc.DS.get("domains"),
		    	fields: [
				{name: "username",canEdit:false,width:50,height:"20",type:"text"},
				{name: "password",type: "password",    
						validators : [{
            					type: "requiredIf",
            					expression: "myrecord.password == ''",
            					errorMessage: "Please set a password"
         			}]},
				{name: "password_confirm", title: "Confirm", type: "password", defaultValue:"",
			 		length: 20, validators:
						[{
				   	  		type: "matchesField",
							expression: "settForm.getValue('password') != ''",
				     			otherField: "password",
				     			errorMessage: "Passwords do not match"
						 },{
            					type: "requiredIf",
            					expression: "myrecord.password == ''",
            					errorMessage: "Please set a password and confirm"
         			}]
				},
				{name:"enable_ftp", type:"checkbox", title:"Enable FTP Access"}]
		});
		this.saveButton = isc.IButton.create({
			container: this,
			title: "Save",
			icon: "[SKIN]/actions/save.png",
			click: function () 
			{	settForm.validate();
				this.container.saveRecord();
			},
		});
		this.TabSettB=isc.VLayout.create({
			container:this,
			layoutLeftMargin:100,
			members:[this.saveButton]		
		});
		this.TabSett=isc.VLayout.create({
			container:this,
			membersMargin:20,
			members:[this.settForm, this.TabSettB]		
		});
		this.addMember(this.TabSett);
	},
	saveRecord: function() {
		this.settForm.saveData(function(dsResponse, data, dsRequest) {
			if (data.enable_ftp==false)
			{
				this.container.settForm.setValue("enable_ftp",false);
				label4.setContents("FTP service is not enabled");
				label4.setIcon("[SKIN]/actions/remove.png");
			}
			else
			{
				this.container.settForm.setValue("enable_ftp",true);
				label4.setContents("FTP service is enabled");
				label4.setIcon("[SKIN]/actions/approve.png");
			}
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.container.settForm.editRecord(myrecord);
			this.container.settForm.setValue("password","");
			
			this.setSaveOperationType("update");
		});
		this.settForm.editRecord(myrecord);
		this.settForm.setValue("password","");

		listGrid.setData([]);
		listGrid.fetchData(); 

	},
	setDomainId: function(domainId) {
		this.domainId = domainId;
	},
	setRecord: function(record) {
		this.myrecord = record;
		myrecord = record;
	},
	domainId:null,
	myrecord:null
});

isc.ClassFactory.defineClass("TabDNSSett", isc.VLayout);

isc.TabDNSSett.addProperties({
	width: "100%",
	height: "100%",
	padding:30,
	align:"center",
	showResizeBar:false,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.label_dns=isc.Label.create({
			container:this,
			ID:"label_dns",
			autoDraw: false,
			width:"100%",
			align:"center",
		   	contents:"DNS service for this domain is not configured. Click below to configure ..."
		});
		
		this.listGrid_dns = isc.ListGrid.create({
			ID:"listGrid_dns",
			container:this,
			width:"100%",
			height:"200",
			dataSource: isc.DS.get('rr'),
			autoFetchData:false,
			recordClick: function(viewer,record)
			{
				formDNS.show();
				formDNS.editRecord(record);
				editButtonDns.setDisabled(false);
				deleteButtonDns.setDisabled(false);
			},
			fields:[
				{name: "id", showIf:"false"},
				{name: "name"},
				{name: "type"},
				{name: "aux"},
				{name: "data"}
			]
		});

		this.dnsForm=isc.DynamicForm.create({
			container:this,
   			ID: "dnsForm",
			
    			dataSource: isc.DS.get("domainsDNS"),
			fields: [
				{name: "soa_id",showIf:"false"},
				{name: "domain_id",showIf:"false"},
				]
		});

		this.saveButtonDNS = isc.IButton.create({
			ID:"saveButtonDNS",
			container: this,
			width:150,
			title: "Enable DNS Service",
			icon: "[SKIN]/actions/approve.png",
			click: function () {
				this.container.saveRecord();

				label_dns.hide();
				//formToolbar.show();
				summary.show();
				tabDNS.hide();
				saveButtonDNS.hide();
				
			
				label8.setContents("DNS service is enabled");
				label8.setIcon("[SKIN]/actions/approve.png");
			}
		});

		this.TabDNS=isc.VLayout.create({
			container:this,
			layoutLeftMargin:400,
			members:[this.dnsForm, this.saveButtonDNS]		
		});

		this.newButtonDns = isc.IButton.create({
			ID:"newButtonDNS",
			container: this,
			width:110,
			title: "New",
			icon: "[SKIN]/actions/add.png",
			showDisabledIcon: false,
			click: function ()
				{
					this.container.newDnsRecord();
				}
		});
		
		this.editButtonDns = isc.IButton.create({
			container: this,
			ID:"editButtonDns",
			width:110,
			title: "Save",
			icon: "[SKIN]/actions/save.png",
			click: function () { formDNS.setValue("zone",listGrid.getSelectedRecord().soa_id);this.container.saveDnsRecord();},
			disabled: true,
			showDisabledIcon: false
		});
		
		this.deleteButtonDns = isc.IButton.create({
			ID:"deleteButtonDns",			
			container: this,
			width:110,
			title: "Delete",
			icon: "[SKIN]/TabSet/close.png",
			showDisabledIcon: false,
			disabled: true,
			click: "this.container.deleteDnsRecord();"
		});
		
		this.toolbarDNS = isc.VLayout.create({
			ID:"toolbarDNS",
			container:this,
			width:100,
			
			members: [this.newButtonDns, this.editButtonDns, this.deleteButtonDns]
		});		

		this.listGridToolbar = isc.HLayout.create({
			ID:"listGridToolbar",
			container:this,
			width:"100%",
			showResizeBar:true,
			membersMargin:20,
			layoutBottomMargin:20,
			members:[this.listGrid_dns, this.toolbarDNS]
		});

		this.formDNS = isc.DynamicForm.create({
			ID:"formDNS",
			container:this,
			visibility:"hidden",
			dataSource:isc.DS.get("rr"),
			items:[
				{name: "zone",title:"Zone",showIf:"false"},
				{name: "name", title:"Name",type:"text", width:"200",required:true},
				{name: "type", title:"Type",type:"select", required:true, redrawOnChange:true,
					/*changed:function(){if (this.form.getValue("type")=='TXT') 
						formDNS.items[4].type = "textArea";
					},*/ valueMap:["A","AAAA","CNAME","MX","NS","TXT"],width:"200"},
				{name: "aux", title:"Priority", type:"text",width:"200",showIf:"form.getValue('type')=='MX'",validators:
						[{
            					type: "requiredIf",
            					expression: "formDNS.getValue('type')=='MX'",
            					errorMessage: "Field is required"
         			}]},
				{name: "data", title:"Data",type:"textArea",width:"200",showIf:"form.getValue('type')=='TXT'", validators:
						[{
            					type: "requiredIf",
            					expression: "formDNS.getValue('type')=='TXT'",
            					errorMessage: "Field is required"
         			}]},
				{name: "data", title:"Data",width:"200",showIf:"form.getValue('type')!='TXT'",validators:
						[{
            					type: "requiredIf",
            					expression: "formDNS.getValue('type')!='TXT'",
            					errorMessage: "Field is required"
         			}]},
				{name: "ttl", title:"TTL", type:"text",width:"200",defaultValue:86400, required:true}
			]
		});
		
		this.formToolbar = isc.VLayout.create({
			ID:"formToolbar",
			visibility:"hidden",
			container:this,
			members:[this.listGridToolbar, this.formDNS]
		});

		
		this.addMember(this.label_dns);
		this.addMember(this.formToolbar);
		this.addMember(this.TabDNS);
	},
	saveRecord: function() {
		this.dnsForm.saveData({ target: this, methodName: "saveRecordCallback" },
		function(dsResponse, data, dsRequest) {
			
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
		});
	},
	saveRecordCallback: function() {
		
		listGrid.setData([]);
		listGrid.fetchData(); 
	},
	saveDnsRecord: function() {
		this.formDNS.saveData({ target: this, methodName: "saveDnsRecordCallback" },
		function(dsResponse, data, dsRequest) {
			if (dsResponse.status != 0)
				return;
			if (!this.isNewRecord())
				return;
			this.setSaveOperationType("update");
		});
	},
	saveDnsRecordCallback: function() {
		formDNS.editNewRecord();
	},
	newDnsRecord: function() {
		formDNS.show();
		formDNS.clearErrors();
		editButtonDns.setDisabled(false);
		deleteButtonDns.setDisabled(false);
		formDNS.editNewRecord();
	},
	deleteDnsRecord: function() {
		isc.ask("Really delete?", function (value) {
			if (value)
				this.container.__deleteDnsRecord();
		}, {container: this});
	},

	__deleteDnsRecord: function() {
		listGrid_dns.dataSource.removeData(listGrid_dns.getSelectedRecord(), { target: this, methodName: "deleteRecordDnsCallback" });
		
	},

	deleteRecordDnsCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		listGrid_dns.fetchData({zone:myrecord.soa_id});
		formDNS.editNewRecord();
	},
	setDomainId: function(domainId) {
		this.domainId = domainId;
	},
	setSoaId: function(soaId) {
		this.soaId = soaId;
		
	},
	setRecord: function(record)
	{
		this.myrecord=record;
		myrecord = record;
	},
	myrecord:null
});

isc.ClassFactory.defineClass("MenuPanel", isc.VLayout);

isc.MenuPanel.addProperties({
	width: "17%",
	height: "100%",
	align:"center",
	showResizeBar:true,
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.menu11=isc.Button.create({
			ID:"menu11",
			container:this,
			title:"Domains",
			align:"left",
			//width:148,
			height:20,
			icon:"[SKINIMG]/SchemaViewer/complexType.gif",
			baseStyle: "myMenuButton",
			click: function() 
				{
					
				}		
		});
		this.menu12=isc.Button.create({
			container:this,
			title:"Databases",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/DatabaseBrowser/data.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
				
				}		
		});
		this.menu13=isc.Button.create({
			container:this,
			title:"System",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/Window/headerIcon.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
				
				}		
		});
		this.menu14=isc.Button.create({
			container:this,
			title:"Logout",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/Window/headerIcon.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					isc.MplAuthenticator.logout(function () {
						document.location.href = document.location.href;
					});
				
				}		
		});
		this.treegrid1=isc.VLayout.create({
			container:this,
			ID:"treegrid1",
			//width:150,
			height:80,
			layoutTopMargin:10,
			layoutBottomMargin:10,
			border:"1px solid gray",
			members:[this.menu11,this.menu12,this.menu13,this.menu14]
		});
		this.menu21=isc.Button.create({
			container:this,
			title:"New domain",
			align:"left",
			//width:148,
			height:20,
			icon:"[SKINIMG]/actions/add.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.hide();
					tabMail.hide();
					summary.hide();
					formLayout.show();
					wizardV.hide();
					wizardFTPV.hide();	
					tabDNS.hide();
				}		
		});
		this.menu22=isc.Button.create({
			container:this,
			title:"Wizard",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/RichTextEditor/link_new.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.hide();
					tabMail.hide();
					summary.hide();
					formLayout.hide();
					wizardV.show();
					wizardFTPV.hide();
					tabDNS.hide();
				}		
		});
		this.menu23=isc.Button.create({
			container:this,
			title:"Remove",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/actions/remove.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.hide();
					tabMail.hide();
					formLayout.hide();
					summary.hide();
					wizardFTPV.hide();
					tabDNS.hide();
					if (listGrid.getSelectedRecord() != null) this.container.deleteRecord(listGrid.getSelectedRecord().domain); 
						else isc.say('Select a domain first!');
				}		
		});
		this.treegrid2=isc.VLayout.create({
			container:this,
			ID:"treegrid2",
			//width:150,
			height:60,
			layoutTopMargin:10,
			layoutBottomMargin:10,
			align:"center",
			border:"1px solid gray",
			members:[this.menu21,this.menu22,this.menu23]
		});
		
		this.menu31=isc.Button.create({
			container:this,
			title:"Summary",
			align:"left",
			//width:148,
			height:20,
			icon:"[SKINIMG]/FileBrowser/file.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.hide();
					tabMail.hide();
					formLayout.hide();
					wizardV.hide();
					wizardFTPV.hide();
					tabDNS.hide();
					
					if (!listGrid.getSelectedRecord())
					{
						record = myrecord;
					}
					
					for (i=0; i<listGrid.getTotalRows();i++)
					{
						if (myrecord.domain_id==listGrid.data.localData[i].domain_id)
						{
							myrecord.enable_mail=listGrid.data.localData[i].enable_mail;
							listGrid.selectSingleRecord(i);
							listGrid.recordClick(listGrid,listGrid.data.localData[i]);
						}
					}
					summary.show();
				}		
		});
		this.menu32=isc.Button.create({
			container:this,
			title:"Web",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/Window/headerIcon.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.show();
					tabFTP.hide();
					tabMail.hide();
					formLayout.hide();
					summary.hide();
		                        tabWeb.selectTab(0);
					wizardV.hide();
					wizardFTPV.hide();
					tabDNS.hide();

					if (!listGrid.getSelectedRecord())
					{
						record = myrecord;
					}
					else record = listGrid.getSelectedRecord();
					if (record.site_id!='')
					{
						configV.show();
						unconfigV.hide();
						tabWeb.enableTab(1);
					}
					else 
					{
						configV.hide();
						enableWebAccesButton.show();
						unconfigV.show();
						tabWeb.disableTab(1);
					}
				}		
		});
		this.menu33=isc.Button.create({
			container:this,
			title:"FTP",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/FileBrowser/folder.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.show();
					tabMail.hide();
					formLayout.hide();
					summary.hide();
		                        tabFTP.selectTab(0);
					tabDNS.hide();
					wizardV.hide();
					wizardFTPV.hide();
				}		
		});
		this.menu34=isc.Button.create({
			container:this,
			title:"Mail",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/Window/headerIcon.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide(); 
					tabFTP.hide();
					tabMail.show();
					formLayout.hide();  
					summary.hide();   	
					tabMail.selectTab(0);
					wizardV.hide();
					tabDNS.hide();
				}		
		});
		this.menu35=isc.Button.create({
			container:this,
			ID:"menu35",
			title:"DNS",
			//width:148,
			height:20,
			align:"left",
			icon:"[SKINIMG]/Window/headerIcon.png",
			baseStyle: "myMenuButton",
			click: function() 
				{
					tabWeb.hide();
					tabFTP.hide();
					tabMail.hide();
					summary.hide();
					tabDNS.show();
					if (!listGrid.getSelectedRecord())
					{
						record = myrecord;
					}
					else record = listGrid.getSelectedRecord();


					if (record.soa_id!='')
					{
						formToolbar.show();
						listGrid_dns.fetchData({zone:record.soa_id});
						label_dns.hide();
						saveButtonDNS.hide();
					}
					else 
					{
						formToolbar.hide();
						label_dns.show();
						saveButtonDNS.show();
					}
					
					formLayout.hide();
					summary.hide();
					wizardV.hide();
					wizardFTPV.hide();
				}		
		});
		this.treegrid3=isc.VLayout.create({
			container:this,
			ID:"treegrid3",
			//width:150,
			height:100,
			align:"center",
			layoutTopMargin:10,
			layoutBottomMargin:10,
			border:"1px solid gray",
			members:[this.menu31,this.menu32,this.menu33,this.menu34,this.menu35]
		});
		
		this.stack1=isc.SectionStack.create({
		   	ID: "stack1",
			container:this,
			visibilityMode: "multiple",
		     //	width:162,
			height: 400,
			layoutLeftMargin:10,
			layoutTopMargin:10,
		    	sections: [
			{title: "MENU", expanded: true,/*width: 152,*/align:"center", items: [this.treegrid1]},
			{title: "ACTIONS", expanded: true, /*width: 152,*/items: [this.treegrid2]},
			{title: "d", expanded: true, /*width: 152,*/items: [this.treegrid3]}
		    ]
		});
		this.addMember(this.stack1);

	},

	deleteRecord: function(domain) {
		isc.ask('Really delete the domain "'+domain +'" ?', function (value) {
			if (value)
				this.container.__deleteRecord();
		}, {container: this});
	},

	__deleteRecord: function() {
		listGrid.dataSource.removeData(listGrid.getSelectedRecord(), { target: this, methodName: "deleteRecordCallback" });
		
	},

	deleteRecordCallback: function(dsResponse, data, dsRequest) {
		if (dsResponse.status != 0)
			return;
		listGrid.fetchData();
		stack1.hideSection(2);
	},
	setRecord: function(record) {
		this.record = record;
	},
	record:null
});
isc.ClassFactory.defineClass("PanelView", isc.HLayout);

isc.PanelView.addProperties({
	width: "100%",
	height: "100%",
	align:"center",
	initWidget: function() {
		this.Super("initWidget", arguments);
		this.MenuPanel=isc.MenuPanel.create();
		this.TabsPanel=isc.TabsPanel.create();
		this.addMember(this.MenuPanel);
		this.addMember(this.TabsPanel);
		this.loadinit();
	},

        loadinit: function()
        {
             if (listGrid.getSelectedRecord() == null) stack1.hideSection(2); 
        }
});

function showInterface() {
	isc.PanelView.create();
}
