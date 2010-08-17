alter table domains add enable_ftp boolean not null default false;
alter table domains add site_id integer default null;
alter table domains add foreign key (site_id) references sites(site_id);



alter table site_aliases drop constraint site_aliases_site_id_fkey;
alter table site_aliases add FOREIGN KEY (site_id) REFERENCES sites(site_id) on update cascade on delete cascade;

alter table site_rewrites drop constraint site_rewrites_site_id_fkey;
alter table site_rewrites add FOREIGN KEY (site_id) REFERENCES sites(site_id) on update cascade on delete cascade;
