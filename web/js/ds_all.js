isc.MplDataSource.create({
    ID:"domains",
    dataFormat:"json",
    dataURL:"ds_domains.php",
    transformResponse : function (dsResponse, dsRequest, data) {        
    		var dsResponse = this.Super("transformResponse", arguments);
		for (i=0; i<dsResponse.totalRows; i++)
		{
			if (dsResponse.data[i])
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
			if (dsResponse.data[i].soa_id=='')
				dsResponse.data[i].dns = "[SKIN]/actions/remove.png";
			else
				dsResponse.data[i].dns = "[SKIN]/actions/approve.png";
			}
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
	{name: "username",type: "text"},
	{name: "password"},
	{name:"nr_mailboxes"},
	{name:"nr_aliases"},
	{name:"soa_id"}
    ]
}); 

isc.MplDataSource.create({
    ID:"domainsWS",
    dataFormat:"json",
    dataURL:"ds_domainsWS.php",
   
    fields:[
        {type: "sequence", name: "domain_id", primaryKey: true},
	{name: "domain_id"},
        {title:"Domain", name:"domain", type: "text",  required: true},
	{name: "site_id"}
    ]
}); 

isc.MplDataSource.create({
    ID:"domainsDNS",
    dataFormat:"json",
    dataURL:"ds_domainsDNS.php",
   
    fields:[
        {type: "sequence", name: "domain_id", primaryKey: true},
	{name: "domain_id"},
        {title:"Domain", name:"domain", type: "text",  required: true},
	{name: "soa_id"}
    ]
}); 

isc.MplDataSource.create({
    ID:"mailboxes",
    dataFormat:"json",
    dataURL:"ds_mailboxes.php",
    fields:[
        {type: "sequence", name: "mailbox_id", primaryKey: true},
	{name: "mailbox_id"},
	{name: "domain_id"},
	{name: "domain"},
        {title:"Mailbox", name:"mailbox", type: "text",  required: true},
	{name: "addressmail"},
	{name: "copy_on_forward",type:"checkbox"},
	{name: "nr_forwards"}
    ]
});

isc.MplDataSource.create({
    ID:"forwards",
    dataFormat:"json",
    dataURL:"ds_forwards.php",
    fields:[
        {type: "sequence", name: "mailbox_forward_id", primaryKey: true},
	{name: "mailbox_forward_id"},
	{name: "mailbox_id"},
	{name: "address",align:"center"}
    ]
});

isc.MplDataSource.create({
    ID:"aliases",
    dataFormat:"json",
    dataURL:"ds_aliases.php",
    fields:[
        {type: "sequence", name: "global_mail_alias_id", primaryKey: true},
	{name: "global_mail_alias_id"},
	{name: "domain_id"},
        {name:"name", type: "text",  required: true},
	{name: "addressalias", align:"center", canFilter:false}
    ]
});

isc.MplDataSource.create({
    ID:"aliasesto",
    dataFormat:"json",
    dataURL:"ds_aliasesto.php",
    fields:[
        {type: "sequence", name: "global_mail_alias_to_id", primaryKey: true},
	{name: "global_mail_alias_to_id"},
	{name: "global_mail_alias_id"},
	{name: "address",align:"center"}
    ]
});

isc.MplDataSource.create({
    ID:"users",
    dataFormat:"json",
    dataURL:"ds_users.php",
    fields:[
        {type: "sequence", name: "user_id", primaryKey: true},
	{name: "user_id"},
	{name: "parent_id"},
	{name: "username"},
	{name: "password"}
    ]
});

isc.MplDataSource.create({
    ID:"rr",
    dataFormat:"json",
    dataURL:"ds_rr.php",
    fields:[
        {type: "sequence", name: "id", primaryKey: true},
	{name: "zone"},
	{name: "name"},
	{name: "data"},
	{name: "aux"},
	{name: "ttl"},
	{name: "type"}
    ]
});
