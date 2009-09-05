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
	SELECT INTO _domain_id, _ret.uid, _ret.gid domain_id, uid, gid FROM domains WHERE domain = _domain;
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
    uid integer NOT NULL,
    gid integer NOT NULL
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
-- Name: mailboxes; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE mailboxes (
    mailbox_id integer NOT NULL,
    domain_id integer NOT NULL,
    mailbox character varying NOT NULL,
    uid integer,
    gid integer
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
-- Name: domain_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE domains ALTER COLUMN domain_id SET DEFAULT nextval('domains_domain_id_seq'::regclass);


--
-- Name: mailbox_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE mailboxes ALTER COLUMN mailbox_id SET DEFAULT nextval('mailboxes_mailbox_id_seq'::regclass);


--
-- Name: domains_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_pkey PRIMARY KEY (domain_id);


--
-- Name: mailboxes_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_pkey PRIMARY KEY (mailbox_id);


--
-- Name: domains_domain_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX domains_domain_idx ON domains USING btree (domain);


--
-- Name: mailboxes_domain_id_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX mailboxes_domain_id_idx ON mailboxes USING btree (domain_id);


--
-- Name: mailboxes_mailbox_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE INDEX mailboxes_mailbox_idx ON mailboxes USING btree (mailbox);


--
-- Name: mailboxes_domain_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY mailboxes
    ADD CONSTRAINT mailboxes_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES domains(domain_id);


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

