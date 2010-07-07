create table mailbox_forwards(mailbox_forward_id serial not null primary key, mailbox_id integer not null, address varchar not null);
alter table mailbox_forwards add foreign key (mailbox_id) references mailboxes(mailbox_id);

alter table mailboxes add column copy_on_forward boolean not null default false;

