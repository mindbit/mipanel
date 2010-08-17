
isc.RestDataSource.create({
    ID:"domains",
    dataFormat:"json",
    dataURL:"ds-domains.php",
    transformResponse : function (dsResponse, dsRequest, data) {        
    		var dsResponse = this.Super("transformResponse", arguments);
		for (i=0; i<dsResponse.totalRows; i++)
		{
			if (dsResponse.data[i].enable_ftp!=true) 
				dsResponse.data[i].ftp = "[SKIN]/actions/remove.png";
			else dsResponse.data[i].ftp = "[SKIN]/actions/approve.png";
                     	if (dsResponse.data[i].enable_mail!=true)
				dsResponse.data[i].mail = "[SKIN]/actions/remove.png";
			else dsResponse.data[i].mail = "[SKIN]/actions/approve.png";
			if (dsResponse.data[i].site_id=='')
				dsResponse.data[i].web = "[SKIN]/actions/remove.png";
			else
				dsResponse.data[i].web = "[SKIN]/actions/approve.png";
			dsResponse.data[i].dns = "[SKIN]/actions/remove.png";
		}
     		return dsResponse;
		},
    fields:[
        {type: "sequence", name: "domain_id", primaryKey: true},
	{name: "domain_id"},
        {title:"Domain", name:"domain", type: "text",  required: true},
	{name: "web", title: "Web", type:"image"},
	{name: "enable_web", type:"checkbox"},   
	{name: "enable_dns", type:"checkbox"},     
	{name: "enable_ftp", type:"checkbox"},
	{name: "ftp", title: "FTP", type:"image"},
        {name: "dns", title: "DNS", type:"image"},
        {name: "enable_mail", type:"checkbox"},
	{name: "mail", title: "Mail",type:"image"},
	{name: "site_id"},
	{name: "enabled"},
	{name: "mail_gid"},
	{name: "mail_uid"},
	{name: "username"},
	{name:"nr_mailboxes"},
	{name:"nr_aliases"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
}); 

isc.RestDataSource.create({
    ID:"domainsWS",
    dataFormat:"json",
    dataURL:"ds-domainsWS.php",
   
    fields:[
        {type: "sequence", name: "domain_id", primaryKey: true},
	{name: "domain_id"},
        {title:"Domain", name:"domain", type: "text",  required: true},
	{name: "site_id"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
}); 

isc.RestDataSource.create({
    ID:"mailboxes",
    dataFormat:"json",
    dataURL:"ds-mailboxes.php",
    fields:[
        {type: "sequence", name: "mailbox_id", primaryKey: true},
	{name: "mailbox_id"},
	{name: "domain_id"},
	{name: "domain"},
        {title:"Mailbox", name:"mailbox", type: "text",  required: true},
	{name: "addressmail"},
	{name: "copy_on_forward",type:"checkbox"},
	{name: "nr_forwards"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});

isc.RestDataSource.create({
    ID:"forwards",
    dataFormat:"json",
    dataURL:"ds-forwards.php",
    fields:[
        {type: "sequence", name: "mailbox_forward_id", primaryKey: true},
	{name: "mailbox_forward_id"},
	{name: "mailbox_id"},
	{name: "address",align:"center"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});

isc.RestDataSource.create({
    ID:"aliases",
    dataFormat:"json",
    dataURL:"ds-aliases.php",
    fields:[
        {type: "sequence", name: "global_mail_alias_id", primaryKey: true},
	{name: "global_mail_alias_id"},
	{name: "domain_id"},
        {name:"name", type: "text",  required: true},
	{name: "addressalias", align:"center", canFilter:false}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});

isc.RestDataSource.create({
    ID:"aliasesto",
    dataFormat:"json",
    dataURL:"ds-aliasesto.php",
    fields:[
        {type: "sequence", name: "global_mail_alias_to_id", primaryKey: true},
	{name: "global_mail_alias_to_id"},
	{name: "global_mail_alias_id"},
	{name: "address",align:"center"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});
isc.RestDataSource.create({
    ID:"users",
    dataFormat:"json",
    dataURL:"ds-users.php",
    fields:[
        {type: "sequence", name: "user_id", primaryKey: true},
	{name: "user_id"},
	{name: "parent_id"},
	{name: "username"},
	{name: "password"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"}, 
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});

isc.RestDataSource.create({
    ID:"sites",
    dataFormat:"json",
    dataURL:"ds-sites.php",
   
    fields:[
        {type: "sequence", name: "site_id", primaryKey: true},
	{name: "site_id"},
	{name: "name"},
	{name: "server_id"},
	{name: "server_port"},
	{name: "enabled",type:"checkbox"}
    ],
    operationBindings:[
       {operationType:"fetch", dataProtocol:"postMessage"},
       {operationType:"add", dataProtocol:"postMessage"},
       {operationType:"remove", dataProtocol:"postMessage"},
       {operationType:"update", dataProtocol:"postMessage"}
    ],
});

