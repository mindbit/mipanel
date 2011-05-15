--@version: 1

CREATE USER mipanel;
CREATE USER vmail;
CREATE USER proftpd;

CREATE TABLE acl_items (
    acl_item_id integer NOT NULL,
    acl_id integer NOT NULL,
    prio integer NOT NULL,
    match cidr NOT NULL,
    action integer NOT NULL,
    expires bigint
);

ALTER TABLE public.acl_items OWNER TO mipanel;

CREATE SEQUENCE acl_items_acl_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.acl_items_acl_item_id_seq OWNER TO mipanel;
ALTER SEQUENCE acl_items_acl_item_id_seq OWNED BY acl_items.acl_item_id;
ALTER TABLE acl_items ALTER COLUMN acl_item_id SET DEFAULT nextval('acl_items_acl_item_id_seq'::regclass);
ALTER TABLE ONLY acl_items
    ADD CONSTRAINT acl_items_pkey PRIMARY KEY (acl_item_id);

CREATE TABLE acls (
    acl_id integer NOT NULL,
    action integer NOT NULL
);

ALTER TABLE public.acls OWNER TO mipanel;

CREATE SEQUENCE acls_acl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.acls_acl_id_seq OWNER TO mipanel;
ALTER SEQUENCE acls_acl_id_seq OWNED BY acls.acl_id;
ALTER TABLE acls ALTER COLUMN acl_id SET DEFAULT nextval('acls_acl_id_seq'::regclass);
ALTER TABLE ONLY acls
    ADD CONSTRAINT acls_pkey PRIMARY KEY (acl_id);

CREATE TABLE domain_catch_all (
    domain_catch_all_id integer NOT NULL,
    domain_id integer NOT NULL,
    address character varying NOT NULL
);

ALTER TABLE public.domain_catch_all OWNER TO mipanel;

CREATE SEQUENCE domain_catch_all_domain_catch_all_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.domain_catch_all_domain_catch_all_id_seq OWNER TO mipanel;
ALTER SEQUENCE domain_catch_all_domain_catch_all_id_seq OWNED BY domain_catch_all.domain_catch_all_id;
ALTER TABLE domain_catch_all ALTER COLUMN domain_catch_all_id SET DEFAULT nextval('domain_catch_all_domain_catch_all_id_seq'::regclass);
ALTER TABLE ONLY domain_catch_all
    ADD CONSTRAINT domain_catch_all_pkey PRIMARY KEY (domain_catch_all_id);

CREATE TABLE domains (
    domain_id integer NOT NULL,
    domain character varying NOT NULL,
    mail_uid integer,
    mail_gid integer,
    username character varying(64),
    password character varying(64),
    salt character(16),
    enable_ftp boolean DEFAULT false NOT NULL,
    enable_mail boolean DEFAULT false NOT NULL,
    site_id integer,
    soa_id integer
);

ALTER TABLE public.domains OWNER TO mipanel;

CREATE SEQUENCE domains_domain_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.domains_domain_id_seq OWNER TO mipanel;
ALTER SEQUENCE domains_domain_id_seq OWNED BY domains.domain_id;
ALTER TABLE domains ALTER COLUMN domain_id SET DEFAULT nextval('domains_domain_id_seq'::regclass);
ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_pkey PRIMARY KEY (domain_id);

CREATE TABLE global_mail_alias_to (
    global_mail_alias_to_id integer NOT NULL,
    global_mail_alias_id integer NOT NULL,
    address character varying NOT NULL
);

ALTER TABLE public.global_mail_alias_to OWNER TO mipanel;

CREATE SEQUENCE global_mail_alias_to_global_mail_alias_to_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.global_mail_alias_to_global_mail_alias_to_id_seq OWNER TO mipanel;
ALTER SEQUENCE global_mail_alias_to_global_mail_alias_to_id_seq OWNED BY global_mail_alias_to.global_mail_alias_to_id;
ALTER TABLE global_mail_alias_to ALTER COLUMN global_mail_alias_to_id SET DEFAULT nextval('global_mail_alias_to_global_mail_alias_to_id_seq'::regclass);
ALTER TABLE ONLY global_mail_alias_to
    ADD CONSTRAINT global_mail_alias_to_pkey PRIMARY KEY (global_mail_alias_to_id);

CREATE TABLE global_mail_aliases (
    global_mail_alias_id integer NOT NULL,
    domain_id integer NOT NULL,
    name character varying NOT NULL
);

ALTER TABLE public.global_mail_aliases OWNER TO mipanel;

CREATE SEQUENCE global_mail_aliases_global_mail_alias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.global_mail_aliases_global_mail_alias_id_seq OWNER TO mipanel;
ALTER SEQUENCE global_mail_aliases_global_mail_alias_id_seq OWNED BY global_mail_aliases.global_mail_alias_id;
ALTER TABLE global_mail_aliases ALTER COLUMN global_mail_alias_id SET DEFAULT nextval('global_mail_aliases_global_mail_alias_id_seq'::regclass);
ALTER TABLE ONLY global_mail_aliases
    ADD CONSTRAINT global_mail_aliases_pkey PRIMARY KEY (global_mail_alias_id);

CREATE TABLE limits (
    limit_id integer NOT NULL,
    user_id integer NOT NULL,
    key character varying(32) NOT NULL,
    "limit" bigint NOT NULL,
    usage bigint DEFAULT 0 NOT NULL
);

ALTER TABLE public.limits OWNER TO mipanel;

CREATE SEQUENCE limits_limit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.limits_limit_id_seq OWNER TO mipanel;
ALTER SEQUENCE limits_limit_id_seq OWNED BY limits.limit_id;
ALTER TABLE limits ALTER COLUMN limit_id SET DEFAULT nextval('limits_limit_id_seq'::regclass);
ALTER TABLE ONLY limits
    ADD CONSTRAINT limits_pkey PRIMARY KEY (limit_id);

CREATE TABLE mailbox_forwards (
    mailbox_forward_id integer NOT NULL,
    mailbox_id integer NOT NULL,
    address character varying NOT NULL
);

ALTER TABLE public.mailbox_forwards OWNER TO mipanel;

CREATE SEQUENCE mailbox_forwards_mailbox_forward_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.mailbox_forwards_mailbox_forward_id_seq OWNER TO mipanel;
ALTER SEQUENCE mailbox_forwards_mailbox_forward_id_seq OWNED BY mailbox_forwards.mailbox_forward_id;
ALTER TABLE mailbox_forwards ALTER COLUMN mailbox_forward_id SET DEFAULT nextval('mailbox_forwards_mailbox_forward_id_seq'::regclass);
ALTER TABLE ONLY mailbox_forwards
    ADD CONSTRAINT mailbox_forwards_pkey PRIMARY KEY (mailbox_forward_id);

CREATE TABLE mailboxes (
    mailbox_id integer NOT NULL,
    domain_id integer NOT NULL,
    mailbox character varying NOT NULL,
    uid integer,
    gid integer,
    password character varying(64),
    copy_on_forward boolean DEFAULT false NOT NULL
);
ALTER TABLE public.mailboxes OWNER TO mipanel;

CREATE SEQUENCE mailboxes_mailbox_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.mailboxes_mailbox_id_seq OWNER TO mipanel;
ALTER SEQUENCE mailboxes_mailbox_id_seq OWNED BY mailboxes.mailbox_id;
ALTER TABLE mailboxes ALTER COLUMN mailbox_id SET DEFAULT nextval('mailboxes_mailbox_id_seq'::regclass);
ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_pkey PRIMARY KEY (mailbox_id);

CREATE TABLE rr (
    id integer NOT NULL,
    zone integer NOT NULL,
    name character varying(64) NOT NULL,
    data bytea NOT NULL,
    aux integer DEFAULT 0 NOT NULL,
    ttl integer DEFAULT 86400 NOT NULL,
    type character varying(5) NOT NULL,
    CONSTRAINT rr_type_check CHECK ((((((((((((((type)::text = 'A'::text) OR ((type)::text = 'AAAA'::text)) OR ((type)::text = 'ALIAS'::text)) OR ((type)::text = 'CNAME'::text)) OR ((type)::text = 'HINFO'::text)) OR ((type)::text = 'MX'::text)) OR ((type)::text = 'NAPTR'::text)) OR ((type)::text = 'NS'::text)) OR ((type)::text = 'PTR'::text)) OR ((type)::text = 'RP'::text)) OR ((type)::text = 'SRV'::text)) OR ((type)::text = 'TXT'::text)))
);

ALTER TABLE public.rr OWNER TO mipanel;

CREATE SEQUENCE rr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.rr_id_seq OWNER TO mipanel;
ALTER SEQUENCE rr_id_seq OWNED BY rr.id;
ALTER TABLE rr ALTER COLUMN id SET DEFAULT nextval('rr_id_seq'::regclass);
ALTER TABLE ONLY rr
    ADD CONSTRAINT rr_pkey PRIMARY KEY (id);
ALTER TABLE ONLY rr
    ADD CONSTRAINT rr_zone_key UNIQUE (zone, name, type, data);

CREATE TABLE settings (
    setting_id integer NOT NULL,
    key character varying(32) NOT NULL,
    value text
);

ALTER TABLE public.settings OWNER TO mipanel;

CREATE SEQUENCE settings_setting_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.settings_setting_id_seq OWNER TO mipanel;
ALTER SEQUENCE settings_setting_id_seq OWNED BY settings.setting_id;
ALTER TABLE settings ALTER COLUMN setting_id SET DEFAULT nextval('settings_setting_id_seq'::regclass);
ALTER TABLE ONLY settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (setting_id);

CREATE TABLE site_aliases (
    site_alias_id integer NOT NULL,
    name character varying(255) NOT NULL,
    site_id integer NOT NULL
);

ALTER TABLE public.site_aliases OWNER TO mipanel;

CREATE SEQUENCE site_aliases_site_alias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.site_aliases_site_alias_id_seq OWNER TO mipanel;
ALTER SEQUENCE site_aliases_site_alias_id_seq OWNED BY site_aliases.site_alias_id;
ALTER TABLE site_aliases ALTER COLUMN site_alias_id SET DEFAULT nextval('site_aliases_site_alias_id_seq'::regclass);
ALTER TABLE ONLY site_aliases
    ADD CONSTRAINT site_aliases_pkey PRIMARY KEY (site_alias_id);

CREATE TABLE site_rewrites (
    site_rewrite_id integer NOT NULL,
    pattern character varying NOT NULL,
    replacement character varying NOT NULL,
    prio smallint NOT NULL,
    continue smallint NOT NULL,
    site_id integer
);


ALTER TABLE public.site_rewrites OWNER TO mipanel;

CREATE SEQUENCE site_rewrites_site_rewrite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.site_rewrites_site_rewrite_id_seq OWNER TO mipanel;
ALTER SEQUENCE site_rewrites_site_rewrite_id_seq OWNED BY site_rewrites.site_rewrite_id;
ALTER TABLE site_rewrites ALTER COLUMN site_rewrite_id SET DEFAULT nextval('site_rewrites_site_rewrite_id_seq'::regclass);
ALTER TABLE ONLY site_rewrites
    ADD CONSTRAINT site_rewrites_pkey PRIMARY KEY (site_rewrite_id);

CREATE TABLE sites (
    site_id integer NOT NULL,
    name character varying(255) NOT NULL,
    server_ip character varying(15) NOT NULL,
    server_port integer DEFAULT 80 NOT NULL,
    enabled smallint DEFAULT 1 NOT NULL
);

ALTER TABLE public.sites OWNER TO mipanel;

CREATE SEQUENCE sites_site_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.sites_site_id_seq OWNER TO mipanel;
ALTER SEQUENCE sites_site_id_seq OWNED BY sites.site_id;
ALTER TABLE sites ALTER COLUMN site_id SET DEFAULT nextval('sites_site_id_seq'::regclass);
ALTER TABLE ONLY sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (site_id);

CREATE TABLE smtp_transaction_recipients (
    smtp_transaction_recipient_id integer NOT NULL,
    smtp_transaction_id integer NOT NULL,
    recipient text
);

ALTER TABLE public.smtp_transaction_recipients OWNER TO mipanel;

CREATE SEQUENCE smtp_transaction_recipients_smtp_transaction_recipient_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.smtp_transaction_recipients_smtp_transaction_recipient_id_seq OWNER TO mipanel;
ALTER SEQUENCE smtp_transaction_recipients_smtp_transaction_recipient_id_seq OWNED BY smtp_transaction_recipients.smtp_transaction_recipient_id;
ALTER TABLE smtp_transaction_recipients ALTER COLUMN smtp_transaction_recipient_id SET DEFAULT nextval('smtp_transaction_recipients_smtp_transaction_recipient_id_seq'::regclass);
ALTER TABLE ONLY smtp_transaction_recipients
    ADD CONSTRAINT smtp_transaction_recipients_pkey PRIMARY KEY (smtp_transaction_recipient_id);

CREATE TABLE smtp_transactions (
    smtp_transaction_id integer NOT NULL,
    remote_addr inet,
    remote_port integer,
    envelope_sender text,
    "time" timestamp without time zone,
    subject text,
    smtp_status_code integer,
    smtp_status_message text,
    module character varying(30)
);

ALTER TABLE public.smtp_transactions OWNER TO mipanel;

CREATE SEQUENCE smtp_transactions_smtp_transaction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.smtp_transactions_smtp_transaction_id_seq OWNER TO mipanel;
ALTER SEQUENCE smtp_transactions_smtp_transaction_id_seq OWNED BY smtp_transactions.smtp_transaction_id;
ALTER TABLE smtp_transactions ALTER COLUMN smtp_transaction_id SET DEFAULT nextval('smtp_transactions_smtp_transaction_id_seq'::regclass);
ALTER TABLE ONLY smtp_transactions
    ADD CONSTRAINT smtp_transactions_pkey PRIMARY KEY (smtp_transaction_id);

CREATE TABLE soa (
    id integer NOT NULL,
    origin character varying(255) NOT NULL,
    ns character varying(255) NOT NULL,
    mbox character varying(255) NOT NULL,
    serial integer DEFAULT 1 NOT NULL,
    refresh integer DEFAULT 28800 NOT NULL,
    retry integer DEFAULT 7200 NOT NULL,
    expire integer DEFAULT 604800 NOT NULL,
    minimum integer DEFAULT 86400 NOT NULL,
    ttl integer DEFAULT 86400 NOT NULL,
    active character varying(1) NOT NULL,
    xfer character(255) DEFAULT NULL::bpchar,
    CONSTRAINT soa_active_check CHECK ((((active)::text = 'Y'::text) OR ((active)::text = 'N'::text)))
);

ALTER TABLE public.soa OWNER TO mipanel;

CREATE SEQUENCE soa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.soa_id_seq OWNER TO mipanel;
ALTER SEQUENCE soa_id_seq OWNED BY soa.id;
ALTER TABLE soa ALTER COLUMN id SET DEFAULT nextval('soa_id_seq'::regclass);
ALTER TABLE ONLY soa
    ADD CONSTRAINT soa_origin_key UNIQUE (origin);
ALTER TABLE ONLY soa
    ADD CONSTRAINT soa_pkey PRIMARY KEY (id);

CREATE TABLE users (
    user_id integer NOT NULL,
    username character varying(64) NOT NULL,
    password character(40) NOT NULL,
    parent_id integer
);

ALTER TABLE public.users OWNER TO mipanel;

CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.users_user_id_seq OWNER TO mipanel;
ALTER SEQUENCE users_user_id_seq OWNED BY users.user_id;
ALTER TABLE users ALTER COLUMN user_id SET DEFAULT nextval('users_user_id_seq'::regclass);
ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);

CREATE UNIQUE INDEX acl_items_acl_id_prio_idx ON acl_items USING btree (acl_id, prio);
CREATE INDEX domains_domain_idx ON domains USING btree (domain);
CREATE UNIQUE INDEX domains_username_idx ON domains USING btree (username);
CREATE UNIQUE INDEX limits_user_id_key_idx ON limits USING btree (user_id, key);
CREATE INDEX mailboxes_domain_id_idx ON mailboxes USING btree (domain_id);
CREATE INDEX mailboxes_mailbox_idx ON mailboxes USING btree (mailbox);
CREATE UNIQUE INDEX settings_key_idx ON settings USING btree (key);
CREATE UNIQUE INDEX site_aliases_name_idx ON site_aliases USING btree (name);
CREATE UNIQUE INDEX sites_name_idx ON sites USING btree (name);
CREATE UNIQUE INDEX users_username_idx ON users USING btree (username);
ALTER TABLE ONLY acl_items
    ADD CONSTRAINT acl_items_acl_id_fkey FOREIGN KEY (acl_id) REFERENCES acls(acl_id);
ALTER TABLE ONLY domain_catch_all
    ADD CONSTRAINT domain_catch_all_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES domains(domain_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_site_id_fkey FOREIGN KEY (site_id) REFERENCES sites(site_id);
ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_soa_id_fkey FOREIGN KEY (soa_id) REFERENCES soa(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE ONLY global_mail_alias_to
    ADD CONSTRAINT global_mail_alias_to_global_mail_alias_id_fkey FOREIGN KEY (global_mail_alias_id) REFERENCES global_mail_aliases(global_mail_alias_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY global_mail_aliases
    ADD CONSTRAINT global_mail_aliases_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES domains(domain_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY limits
    ADD CONSTRAINT limits_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY mailbox_forwards
    ADD CONSTRAINT mailbox_forwards_mailbox_id_fkey FOREIGN KEY (mailbox_id) REFERENCES mailboxes(mailbox_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES domains(domain_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY rr
    ADD CONSTRAINT rr_zone_fkey FOREIGN KEY (zone) REFERENCES soa(id) ON DELETE CASCADE;
ALTER TABLE ONLY site_aliases
    ADD CONSTRAINT site_aliases_site_id_fkey FOREIGN KEY (site_id) REFERENCES sites(site_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY site_rewrites
    ADD CONSTRAINT site_rewrites_site_id_fkey FOREIGN KEY (site_id) REFERENCES sites(site_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY smtp_transaction_recipients
    ADD CONSTRAINT smtp_transaction_recipients_smtp_transaction_id_fkey FOREIGN KEY (smtp_transaction_id) REFERENCES smtp_transactions(smtp_transaction_id);
ALTER TABLE ONLY users
    ADD CONSTRAINT users_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES users(user_id);
GRANT SELECT ON TABLE domain_catch_all TO vmail;
GRANT SELECT ON TABLE domains TO vmail;
GRANT SELECT ON TABLE domains TO proftpd;
GRANT SELECT ON TABLE global_mail_alias_to TO vmail;
GRANT SELECT ON TABLE global_mail_aliases TO vmail;
GRANT SELECT ON TABLE mailbox_forwards TO vmail;
GRANT SELECT ON TABLE mailboxes TO vmail;

CREATE PROCEDURAL LANGUAGE plpgsql;
ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO mipanel;

CREATE TYPE mailbox_properties AS (
	path character varying,
	uid integer,
	gid integer
);

ALTER TYPE public.mailbox_properties OWNER TO mipanel;

CREATE FUNCTION ftp_auth(character varying, character varying) RETURNS SETOF character varying
    LANGUAGE plpgsql
    AS $_$
--@obfuscate
DECLARE
	_username ALIAS FOR $1;
	_host ALIAS FOR $2;

	_domain domains%ROWTYPE;
	_domain_id integer;
BEGIN --{
	SELECT INTO _domain * FROM domains WHERE username = _username;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	IF NOT _domain.enable_ftp THEN --{
		RETURN;
	END IF; --}

	-- TODO check for allowed hosts

	RETURN NEXT _domain.password;
END; --}
--@end
$_$;

ALTER FUNCTION public.ftp_auth(character varying, character varying) OWNER TO mipanel;

CREATE FUNCTION get_mailbox_properties(character varying, character varying) RETURNS SETOF mailbox_properties
    LANGUAGE plpgsql
    AS $_$
--@obfuscate
DECLARE
	_user ALIAS FOR $1;
	_domain ALIAS FOR $2;

	_ret mailbox_properties;
	_domain_id integer;
BEGIN --{
	SELECT INTO _domain_id, _ret.uid, _ret.gid domain_id, mail_uid, mail_gid FROM domains WHERE domain = _domain;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	SELECT INTO _ret.uid, _ret.gid COALESCE(uid, _ret.uid), COALESCE(gid, _ret.gid) FROM mailboxes WHERE domain_id = _domain_id AND mailbox = _user;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	_ret.path := _domain || '/' || _user || '/Maildir/';
	RETURN NEXT _ret;
END; --}
--@end
$_$;

ALTER FUNCTION public.get_mailbox_properties(character varying, character varying) OWNER TO mipanel;

CREATE FUNCTION get_virtual_mail(character varying) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
DECLARE
--@obfuscate
	_query ALIAS FOR $1;

	_user text;
	_domain text;
	_ret text;
	_addr text;
	_glue text;
	_domain_id integer;
	_aux_id integer;
	_cof boolean;
BEGIN --{
	_user := split_part(_query, '@', 1);
	_domain := split_part(_query, '@', 2);

	SELECT INTO _domain_id domain_id FROM domains WHERE domain = _domain;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	_ret := '';
	_glue := '';

	-- if _user is empty, look for catch-all
	IF _user = '' THEN --{
		FOR _addr IN
			SELECT address FROM domain_catch_all WHERE domain_id = _domain_id
		LOOP --{
			_ret := _ret || _glue || _addr;
			_glue := ',';
		END LOOP; --}
		IF _ret <> '' THEN --{
			RETURN NEXT _ret;
		END IF; --}
		RETURN;
	END IF; --}

	-- check for global aliases
	SELECT INTO _aux_id global_mail_alias_id FROM global_mail_aliases WHERE domain_id = _domain_id AND name = _user;
	IF FOUND THEN --{
		FOR _addr IN
			SELECT address FROM global_mail_alias_to WHERE global_mail_alias_id = _aux_id
		LOOP --{
			_ret := _ret || _glue || _addr;
			_glue := ',';
		END LOOP; --}
		IF _ret <> '' THEN --{
			RETURN NEXT _ret;
		END IF; --}
		RETURN;
	END IF; --}

	-- check mailbox
	SELECT INTO _aux_id, _cof mailbox_id, copy_on_forward FROM mailboxes WHERE domain_id = _domain_id AND mailbox = _user;
	IF FOUND THEN --{
		FOR _addr IN
			SELECT address FROM mailbox_forwards WHERE mailbox_id = _aux_id
		LOOP --{
			_ret := _ret || _glue || _addr;
			_glue := ',';
		END LOOP; --}
		-- always respond with mailbox if exists; otherwise catch-all -to- mailbox would
		-- cause an endless loop
		IF _ret = '' OR _cof THEN --{
			_ret := _ret || _glue || _user || '@' || _domain;
		END IF; --}
		RETURN NEXT _ret;
		RETURN;
	END IF; --}

END; --}
--@end
$_$;

ALTER FUNCTION public.get_virtual_mail(character varying) OWNER TO mipanel;

--@version: 2
CREATE UNIQUE INDEX sites_server_ip_server_port_idx ON sites(server_ip, server_port);

--@version: 3
DROP FUNCTION get_mailbox_properties(character varying, character varying);
CREATE FUNCTION get_mailbox_properties(character varying, character varying) RETURNS SETOF mailbox_properties AS $_$
DECLARE
--@obfuscate
	_user ALIAS FOR $1;
	_domain ALIAS FOR $2;

	_ret mailbox_properties;
	_domain_id integer;
BEGIN --{
	SELECT INTO _domain_id, _ret.uid, _ret.gid domain_id, mail_uid, mail_gid FROM domains WHERE domain = _domain AND enable_mail;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	SELECT INTO _ret.uid, _ret.gid COALESCE(uid, _ret.uid), COALESCE(gid, _ret.gid) FROM mailboxes WHERE domain_id = _domain_id AND mailbox = _user;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	_ret.path := _domain || '/' || _user || '/Maildir/';
	RETURN NEXT _ret;
END; --}
--@end
$_$ LANGUAGE plpgsql;
ALTER FUNCTION public.get_mailbox_properties(character varying, character varying) OWNER TO mipanel;

DROP FUNCTION get_virtual_mail(character varying);
CREATE FUNCTION get_virtual_mail(character varying) RETURNS SETOF text AS $_$
--@obfuscate
DECLARE
	_query ALIAS FOR $1;

	_user text;
	_domain text;
	_ret text;
	_glue text;
	_domain_id integer;
	_aux_id integer;
	_cof boolean;
	_row record;
BEGIN --{
	_user := split_part(_query, '@', 1);
	_domain := split_part(_query, '@', 2);

	SELECT INTO _domain_id domain_id FROM domains WHERE domain = _domain AND enable_mail;
	IF NOT FOUND THEN --{
		RETURN;
	END IF; --}

	_ret := '';
	_glue := '';

	-- if _user is empty, look for catch-all
	IF _user = '' THEN --{
		FOR _row IN
			SELECT address FROM domain_catch_all WHERE domain_id = _domain_id
		LOOP --{
			_ret := _ret || _glue || _row.address;
			_glue := ',';
		END LOOP; --}
		IF _ret <> '' THEN --{
			RETURN NEXT _ret;
		END IF; --}
		RETURN;
	END IF; --}

	-- check for global aliases
	SELECT INTO _aux_id global_mail_alias_id FROM global_mail_aliases WHERE domain_id = _domain_id AND name = _user;
	IF FOUND THEN --{
		FOR _row IN
			SELECT address FROM global_mail_alias_to WHERE global_mail_alias_id = _aux_id
		LOOP --{
			_ret := _ret || _glue || _row.address;
			_glue := ',';
		END LOOP; --}
		IF _ret <> '' THEN --{
			RETURN NEXT _ret;
		END IF; --}
		RETURN;
	END IF; --}

	-- check mailbox
	SELECT INTO _aux_id, _cof mailbox_id, copy_on_forward FROM mailboxes WHERE domain_id = _domain_id AND mailbox = _user;
	IF FOUND THEN --{
		FOR _row IN
			SELECT address FROM mailbox_forwards WHERE mailbox_id = _aux_id
		LOOP --{
			_ret := _ret || _glue || _row.address;
			_glue := ',';
		END LOOP; --}
		-- always respond with mailbox if exists; otherwise catch-all -to- mailbox would
		-- cause an endless loop
		IF _ret = '' OR _cof THEN --{
			_ret := _ret || _glue || _user || '@' || _domain;
		END IF; --}
		RETURN NEXT _ret;
		RETURN;
	END IF; --}

END; --}
--@end
$_$ LANGUAGE plpgsql;
ALTER FUNCTION public.get_virtual_mail(character varying) OWNER TO mipanel;

--@version: 4
ALTER TABLE mailboxes add enable_autoresponder boolean not null default false;
ALTER TABLE mailboxes add autoresponder_text text not null default '';

create table customers(customer_id serial primary key, name varchar(50) not null, parent_id int);
alter table customers add foreign key(parent_id) references customers(customer_id);
insert into customers(name) values ('Administrator');

alter table domains add customer_id int;
update domains set customer_id = (select customer_id from customers where parent_id is null limit 1);
alter table domains alter column customer_id set not null;
alter table domains add foreign key(customer_id) references customers(customer_id);
