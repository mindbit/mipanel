alter table domains add enable_ftp boolean not null default false;
alter table domains add site_id integer default null;
alter table domains add foreign key (site_id) references sites(site_id);

