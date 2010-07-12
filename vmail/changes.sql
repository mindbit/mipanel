create table mailbox_forwards(mailbox_forward_id serial not null primary key, mailbox_id integer not null, address varchar not null);
alter table mailbox_forwards add foreign key (mailbox_id) references mailboxes(mailbox_id);

alter table mailboxes add column copy_on_forward boolean not null default false;


create table global_mail_aliases(global_mail_alias_id serial not null primary key, domain_id int not null, name varchar not null);
alter table global_mail_aliases add foreign key(domain_id) references domains(domain_id);
create table global_mail_alias_to(global_mail_alias_to_id serial not null primary key, global_mail_alias_id int not null, address varchar not null);
alter table global_mail_alias_to add foreign key(global_mail_alias_id) references global_mail_aliases(global_mail_alias_id);

alter table domains add enable_mail boolean not null default false;

alter table mailboxes drop constraint mailboxes_domain_id_fkey;
alter table mailboxes add foreign key (domain_id) references domains(domain_id) on update cascade on delete cascade;
alter table mailbox_forwards drop constraint mailbox_forwards_mailbox_id_fkey;
alter table mailbox_forwards add foreign key (mailbox_id) references mailboxes(mailbox_id) on update cascade on delete cascade;
alter table global_mail_aliases drop constraint global_mail_aliases_domain_id_fkey;
alter table global_mail_aliases add FOREIGN KEY (domain_id) REFERENCES domains(domain_id) on update cascade on delete cascade;
alter table global_mail_alias_to drop constraint global_mail_alias_to_global_mail_alias_id_fkey;
alter table global_mail_alias_to add FOREIGN KEY (global_mail_alias_id) REFERENCES global_mail_aliases(global_mail_alias_id) on update cascade on delete cascade;

