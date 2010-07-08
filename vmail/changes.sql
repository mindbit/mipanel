create table mailbox_forwards(mailbox_forward_id serial not null primary key, mailbox_id integer not null, address varchar not null);
alter table mailbox_forwards add foreign key (mailbox_id) references mailboxes(mailbox_id);

alter table mailboxes add column copy_on_forward boolean not null default false;


create table global_mail_aliases(global_mail_alias_id serial not null primary key, domain_id int not null, name varchar not null);
alter table global_mail_aliases add foreign key(domain_id) references domains(domain_id);
create table global_mail_alias_to(global_mail_alias_to_id serial not null primary key, global_mail_alias_id int not null, address varchar not null);
alter table global_mail_alias_to add foreign key(global_mail_alias_id) references global_mail_aliases(global_mail_alias_id);
