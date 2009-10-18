--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: mipanel
--

CREATE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO mipanel;

SET search_path = public, pg_catalog;

--
-- Name: mailbox_properties; Type: TYPE; Schema: public; Owner: mipanel
--

CREATE TYPE mailbox_properties AS (
	path character varying,
	uid integer,
	gid integer
);


ALTER TYPE public.mailbox_properties OWNER TO mipanel;

--
-- Name: get_mailbox_properties(character varying, character varying); Type: FUNCTION; Schema: public; Owner: mipanel
--

CREATE FUNCTION get_mailbox_properties(character varying, character varying) RETURNS SETOF mailbox_properties
    AS $_$
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
$_$
    LANGUAGE plpgsql;


ALTER FUNCTION public.get_mailbox_properties(character varying, character varying) OWNER TO mipanel;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: domains; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE domains (
    domain_id integer NOT NULL,
    domain character varying NOT NULL,
    mail_uid integer,
    mail_gid integer,
    username character varying(64)
);


ALTER TABLE public.domains OWNER TO mipanel;

--
-- Name: domains_domain_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE domains_domain_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.domains_domain_id_seq OWNER TO mipanel;

--
-- Name: domains_domain_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE domains_domain_id_seq OWNED BY domains.domain_id;


--
-- Name: limits; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE limits (
    limit_id integer NOT NULL,
    user_id integer NOT NULL,
    key character varying(32) NOT NULL,
    "limit" bigint NOT NULL,
    usage bigint DEFAULT 0 NOT NULL
);


ALTER TABLE public.limits OWNER TO mipanel;

--
-- Name: limits_limit_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE limits_limit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.limits_limit_id_seq OWNER TO mipanel;

--
-- Name: limits_limit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE limits_limit_id_seq OWNED BY limits.limit_id;


--
-- Name: mailboxes; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE mailboxes (
    mailbox_id integer NOT NULL,
    domain_id integer NOT NULL,
    mailbox character varying NOT NULL,
    uid integer,
    gid integer,
    password character varying(64)
);


ALTER TABLE public.mailboxes OWNER TO mipanel;

--
-- Name: mailboxes_mailbox_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE mailboxes_mailbox_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.mailboxes_mailbox_id_seq OWNER TO mipanel;

--
-- Name: mailboxes_mailbox_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE mailboxes_mailbox_id_seq OWNED BY mailboxes.mailbox_id;


--
-- Name: settings; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE settings (
    setting_id integer NOT NULL,
    key character varying(32) NOT NULL,
    value text
);


ALTER TABLE public.settings OWNER TO mipanel;

--
-- Name: settings_setting_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE settings_setting_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.settings_setting_id_seq OWNER TO mipanel;

--
-- Name: settings_setting_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE settings_setting_id_seq OWNED BY settings.setting_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE users (
    user_id integer NOT NULL,
    username character varying(64) NOT NULL,
    password character(40) NOT NULL,
    parent_id integer
);


ALTER TABLE public.users OWNER TO mipanel;

--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_user_id_seq OWNER TO mipanel;

--
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE users_user_id_seq OWNED BY users.user_id;


--
-- Name: domain_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE domains ALTER COLUMN domain_id SET DEFAULT nextval('domains_domain_id_seq'::regclass);


--
-- Name: limit_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE limits ALTER COLUMN limit_id SET DEFAULT nextval('limits_limit_id_seq'::regclass);


--
-- Name: mailbox_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE mailboxes ALTER COLUMN mailbox_id SET DEFAULT nextval('mailboxes_mailbox_id_seq'::regclass);


--
-- Name: setting_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE settings ALTER COLUMN setting_id SET DEFAULT nextval('settings_setting_id_seq'::regclass);


--
-- Name: user_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE users ALTER COLUMN user_id SET DEFAULT nextval('users_user_id_seq'::regclass);


--
-- Name: domains_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_pkey PRIMARY KEY (domain_id);


--
-- Name: limits_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY limits
    ADD CONSTRAINT limits_pkey PRIMARY KEY (limit_id);


--
-- Name: mailboxes_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_pkey PRIMARY KEY (mailbox_id);


--
-- Name: settings_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (setting_id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: domains_domain_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX domains_domain_idx ON domains USING btree (domain);


--
-- Name: domains_username_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX domains_username_idx ON domains USING btree (username);


--
-- Name: limits_user_id_key_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX limits_user_id_key_idx ON limits USING btree (user_id, key);


--
-- Name: mailboxes_domain_id_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX mailboxes_domain_id_idx ON mailboxes USING btree (domain_id);


--
-- Name: mailboxes_mailbox_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX mailboxes_mailbox_idx ON mailboxes USING btree (mailbox);


--
-- Name: settings_key_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX settings_key_idx ON settings USING btree (key);


--
-- Name: users_username_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX users_username_idx ON users USING btree (username);


--
-- Name: limits_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY limits
    ADD CONSTRAINT limits_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: mailboxes_domain_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES domains(domain_id);


--
-- Name: users_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES users(user_id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: domains; Type: ACL; Schema: public; Owner: mipanel
--

REVOKE ALL ON TABLE domains FROM PUBLIC;
REVOKE ALL ON TABLE domains FROM mipanel;
GRANT ALL ON TABLE domains TO mipanel;
GRANT SELECT ON TABLE domains TO vmail;


--
-- Name: mailboxes; Type: ACL; Schema: public; Owner: mipanel
--

REVOKE ALL ON TABLE mailboxes FROM PUBLIC;
REVOKE ALL ON TABLE mailboxes FROM mipanel;
GRANT ALL ON TABLE mailboxes TO mipanel;
GRANT SELECT ON TABLE mailboxes TO vmail;


--
-- PostgreSQL database dump complete
--

