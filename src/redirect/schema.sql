--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: site_aliases; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE site_aliases (
    site_alias_id integer NOT NULL,
    name character varying(255) NOT NULL,
    site_id integer NOT NULL
);


ALTER TABLE public.site_aliases OWNER TO mipanel;

--
-- Name: site_aliases_site_alias_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE site_aliases_site_alias_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.site_aliases_site_alias_id_seq OWNER TO mipanel;

--
-- Name: site_aliases_site_alias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE site_aliases_site_alias_id_seq OWNED BY site_aliases.site_alias_id;


--
-- Name: site_rewrites; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE site_rewrites (
    site_rewrite_id integer NOT NULL,
    pattern character varying NOT NULL,
    replacement character varying NOT NULL,
    prio smallint NOT NULL,
    continue smallint NOT NULL,
    site_id integer
);


ALTER TABLE public.site_rewrites OWNER TO mipanel;

--
-- Name: site_rewrites_site_rewrite_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE site_rewrites_site_rewrite_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.site_rewrites_site_rewrite_id_seq OWNER TO mipanel;

--
-- Name: site_rewrites_site_rewrite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE site_rewrites_site_rewrite_id_seq OWNED BY site_rewrites.site_rewrite_id;


--
-- Name: sites; Type: TABLE; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE TABLE sites (
    site_id integer NOT NULL,
    name character varying(255) NOT NULL,
    server_ip character varying(15) NOT NULL,
    server_port integer DEFAULT 80 NOT NULL,
    enabled smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.sites OWNER TO mipanel;

--
-- Name: sites_site_id_seq; Type: SEQUENCE; Schema: public; Owner: mipanel
--

CREATE SEQUENCE sites_site_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.sites_site_id_seq OWNER TO mipanel;

--
-- Name: sites_site_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mipanel
--

ALTER SEQUENCE sites_site_id_seq OWNED BY sites.site_id;


--
-- Name: site_alias_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE site_aliases ALTER COLUMN site_alias_id SET DEFAULT nextval('site_aliases_site_alias_id_seq'::regclass);


--
-- Name: site_rewrite_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE site_rewrites ALTER COLUMN site_rewrite_id SET DEFAULT nextval('site_rewrites_site_rewrite_id_seq'::regclass);


--
-- Name: site_id; Type: DEFAULT; Schema: public; Owner: mipanel
--

ALTER TABLE sites ALTER COLUMN site_id SET DEFAULT nextval('sites_site_id_seq'::regclass);


--
-- Name: site_aliases_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY site_aliases
    ADD CONSTRAINT site_aliases_pkey PRIMARY KEY (site_alias_id);


--
-- Name: site_rewrites_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY site_rewrites
    ADD CONSTRAINT site_rewrites_pkey PRIMARY KEY (site_rewrite_id);


--
-- Name: sites_pkey; Type: CONSTRAINT; Schema: public; Owner: mipanel; Tablespace: 
--

ALTER TABLE ONLY sites
    ADD CONSTRAINT sites_pkey PRIMARY KEY (site_id);


--
-- Name: site_aliases_name_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX site_aliases_name_idx ON site_aliases USING btree (name);


--
-- Name: sites_name_idx; Type: INDEX; Schema: public; Owner: mipanel; Tablespace: 
--

CREATE UNIQUE INDEX sites_name_idx ON sites USING btree (name);


--
-- Name: site_aliases_site_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY site_aliases
    ADD CONSTRAINT site_aliases_site_id_fkey FOREIGN KEY (site_id) REFERENCES sites(site_id);


--
-- Name: site_rewrites_site_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: mipanel
--

ALTER TABLE ONLY site_rewrites
    ADD CONSTRAINT site_rewrites_site_id_fkey FOREIGN KEY (site_id) REFERENCES sites(site_id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

