--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: blog_bits_bit_rid_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE blog_bits_bit_rid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blog_bits_bit_rid_seq OWNER TO labtrove;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: blog_bits; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_bits (
    bit_id integer DEFAULT 0 NOT NULL,
    bit_rid integer DEFAULT nextval('blog_bits_bit_rid_seq'::regclass) NOT NULL,
    bit_user character varying(255) NOT NULL,
    bit_title character varying(255) NOT NULL,
    bit_content text NOT NULL,
    bit_meta text NOT NULL,
    bit_datestamp timestamp without time zone NOT NULL,
    bit_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    bit_group character varying(50) NOT NULL,
    bit_blog integer DEFAULT 0 NOT NULL,
    bit_md5 character varying(50) NOT NULL,
    bit_edit integer DEFAULT 0,
    bit_edituser character varying(255),
    bit_editwhy text,
    bit_uri text,
    bit_cache text NOT NULL
);


ALTER TABLE public.blog_bits OWNER TO labtrove;

--
-- Name: blog_blogs_blog_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE blog_blogs_blog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blog_blogs_blog_id_seq OWNER TO labtrove;

--
-- Name: blog_blogs; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_blogs (
    blog_id integer DEFAULT nextval('blog_blogs_blog_id_seq'::regclass) NOT NULL,
    blog_name character varying(127) DEFAULT ''::character varying NOT NULL,
    blog_sname character varying(64) NOT NULL,
    blog_desc text NOT NULL,
    blog_user character varying(255) NOT NULL,
    blog_zone integer DEFAULT 0 NOT NULL,
    blog_del integer DEFAULT 0 NOT NULL,
    blog_type integer DEFAULT 0 NOT NULL,
    blog_redirect text NOT NULL,
    blog_infocache text NOT NULL,
    blog_about text NOT NULL
);


ALTER TABLE public.blog_blogs OWNER TO labtrove;

--
-- Name: blog_com_com_rid_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE blog_com_com_rid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blog_com_com_rid_seq OWNER TO labtrove;

--
-- Name: blog_com; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_com (
    com_id integer DEFAULT 0 NOT NULL,
    com_rid integer DEFAULT nextval('blog_com_com_rid_seq'::regclass) NOT NULL,
    com_bit integer DEFAULT 0 NOT NULL,
    com_user character varying(255) NOT NULL,
    com_title character varying(255) NOT NULL,
    com_cont text NOT NULL,
    com_datetime timestamp without time zone NOT NULL,
    com_del integer DEFAULT 0 NOT NULL,
    com_edit integer DEFAULT 0 NOT NULL,
    com_edituser character varying(255),
    com_editwhy text
);


ALTER TABLE public.blog_com OWNER TO labtrove;

--
-- Name: blog_data_data_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE blog_data_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blog_data_data_id_seq OWNER TO labtrove;

--
-- Name: blog_data; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_data (
    data_id integer DEFAULT nextval('blog_data_data_id_seq'::regclass) NOT NULL,
    data_post integer NOT NULL,
    data_datetime timestamp without time zone NOT NULL,
    data_type character varying(10) DEFAULT ''::character varying NOT NULL,
    data_data text NOT NULL,
    mode character varying(10),
    filesize integer,
    filepath character varying(1024),
    checksum character varying(60)
);


ALTER TABLE public.blog_data OWNER TO labtrove;

--
-- Name: blog_sub; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_sub (
    sub_username character varying(255) NOT NULL,
    sub_blog integer NOT NULL
);


ALTER TABLE public.blog_sub OWNER TO labtrove;

--
-- Name: blog_types_type_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE blog_types_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.blog_types_type_id_seq OWNER TO labtrove;

--
-- Name: blog_types; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_types (
    type_id integer DEFAULT nextval('blog_types_type_id_seq'::regclass) NOT NULL,
    type_name character varying(127) DEFAULT ''::character varying NOT NULL,
    type_desc text NOT NULL,
    type_order integer NOT NULL
);


ALTER TABLE public.blog_types OWNER TO labtrove;

--
-- Name: blog_users; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_users (
    u_name character varying(64) NOT NULL,
    u_emailsub smallint NOT NULL,
    u_sortsub smallint NOT NULL,
    u_proflocate smallint NOT NULL
);


ALTER TABLE public.blog_users OWNER TO labtrove;

--
-- Name: blog_zone; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE blog_zone (
    zone_id integer DEFAULT 0 NOT NULL,
    zone_name character varying(127) DEFAULT ''::character varying NOT NULL,
    zone_type character varying(127) DEFAULT ''::character varying NOT NULL,
    zone_res character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.blog_zone OWNER TO labtrove;

--
-- Name: claddier_citations_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE claddier_citations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.claddier_citations_id_seq OWNER TO labtrove;

--
-- Name: claddier_citations; Type: TABLE; Schema: public; Owner: labtrove; Tablespace:
--

CREATE TABLE claddier_citations (
  id integer DEFAULT nextval('claddier_citations_id_seq'::regclass) NOT NULL,
  title text NOT NULL,
  url text NOT NULL,
  ping_url text NOT NULL,
  excerpt text NOT NULL,
  blog_name text NOT NULL,
  sent_at timestamp without time zone NOT NULL,
  received_at timestamp without time zone NOT NULL,
  metadata text NOT NULL,
  metadata_format text NOT NULL,
  type character varying(8) NOT NULL, check (type in ('cites', 'cited-by', 'copy')),
  host_ip text NOT NULL,
  blog_bit_id integer NOT NULL,
  blog_bit_rid integer NOT NULL
);

ALTER TABLE public.claddier_citations OWNER TO labtrove;

--
-- Name: claddier_whitelist_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE claddier_whitelist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.claddier_whitelist_id_seq OWNER TO labtrove;

--
-- Name: claddier_whitelist; Type: TABLE; Schema: public; Owner: labtrove; Tablespace:
--

CREATE TABLE claddier_whitelist (
  id integer DEFAULT nextval('claddier_whitelist_id_seq'::regclass) NOT NULL,
  repository_name text NOT NULL,
  repository_url text NOT NULL,
  repository_ip_address character varying(45) NOT NULL,
  registered_at timestamp without time zone NOT NULL
);

ALTER TABLE public.claddier_whitelist OWNER TO labtrove;

INSERT INTO claddier_whitelist (repository_name, repository_url, repository_ip_address, registered_at) VALUES ('Localhost', 'localhost', '127.0.1.1', NOW());

--
-- Name: messages_mess_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE messages_mess_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.messages_mess_id_seq OWNER TO labtrove;

--
-- Name: messages; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE messages (
    mess_id integer DEFAULT nextval('messages_mess_id_seq'::regclass) NOT NULL,
    mess_subject text NOT NULL,
    mess_body text NOT NULL,
    mess_html text NOT NULL,
    mess_to character varying(64) NOT NULL,
    mess_email smallint NOT NULL,
    mess_proflocate integer NOT NULL,
    mess_key character varying(32) NOT NULL,
    mess_pri smallint NOT NULL,
    mess_datetime timestamp without time zone NOT NULL
);


ALTER TABLE public.messages OWNER TO labtrove;

--
-- Name: printers_print_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE printers_print_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.printers_print_id_seq OWNER TO labtrove;

--
-- Name: printers; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE printers (
    print_id integer DEFAULT nextval('printers_print_id_seq'::regclass) NOT NULL,
    print_name character varying(127) DEFAULT ''::character varying NOT NULL,
    print_desc character varying(127) DEFAULT ''::character varying NOT NULL,
    print_uri character varying(255) DEFAULT ''::character varying NOT NULL,
    print_size character varying(32) DEFAULT ''::character varying NOT NULL,
    print_local integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.printers OWNER TO labtrove;

--
-- Name: uri_uri_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE uri_uri_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.uri_uri_id_seq OWNER TO labtrove;

--
-- Name: uri; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE uri (
    uri_id integer DEFAULT nextval('uri_uri_id_seq'::regclass) NOT NULL,
    uri_url text NOT NULL
);


ALTER TABLE public.uri OWNER TO labtrove;

--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: labtrove
--

CREATE SEQUENCE users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_user_id_seq OWNER TO labtrove;

--
-- Name: users; Type: TABLE; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE TABLE users (
    user_id integer DEFAULT nextval('users_user_id_seq'::regclass) NOT NULL,
    user_name character varying(255) NOT NULL,
    user_openid character varying(255) NOT NULL,
    user_fname character varying(255) NOT NULL,
    user_email character varying(255) NOT NULL,
    user_image text NOT NULL,
    user_type integer DEFAULT 0 NOT NULL,
    user_enabled integer DEFAULT 0 NOT NULL,
    user_uid character varying(32) NOT NULL,
    user_notes text NOT NULL
);


ALTER TABLE public.users OWNER TO labtrove;

--
-- Name: blog_bits_bit_rid_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY blog_bits
    ADD CONSTRAINT blog_bits_bit_rid_pkey PRIMARY KEY (bit_rid);


--
-- Name: blog_blogs_blog_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY blog_blogs
    ADD CONSTRAINT blog_blogs_blog_id_pkey PRIMARY KEY (blog_id);


--
-- Name: blog_com_com_rid_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY blog_com
    ADD CONSTRAINT blog_com_com_rid_pkey PRIMARY KEY (com_rid);


--
-- Name: blog_data_data_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY blog_data
    ADD CONSTRAINT blog_data_data_id_pkey PRIMARY KEY (data_id);


--
-- Name: blog_types_type_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY blog_types
    ADD CONSTRAINT blog_types_type_id_pkey PRIMARY KEY (type_id);


--
-- Name: messages_mess_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT messages_mess_id_pkey PRIMARY KEY (mess_id);


--
-- Name: printers_print_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY printers
    ADD CONSTRAINT printers_print_id_pkey PRIMARY KEY (print_id);


--
-- Name: uri_uri_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY uri
    ADD CONSTRAINT uri_uri_id_pkey PRIMARY KEY (uri_id);


--
-- Name: users_user_id_pkey; Type: CONSTRAINT; Schema: public; Owner: labtrove; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_user_id_pkey PRIMARY KEY (user_id);


--
-- Name: blog_bits_bit_content_bit_title; Type: INDEX; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE INDEX blog_bits_bit_content_bit_title ON blog_bits USING btree (bit_content, bit_title);


--
-- Name: blog_bits_bit_id; Type: INDEX; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE INDEX blog_bits_bit_id ON blog_bits USING btree (bit_id);


--
-- Name: blog_com_com_cont_com_title; Type: INDEX; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE INDEX blog_com_com_cont_com_title ON blog_com USING btree (com_cont, com_title);


--
-- Name: blog_com_com_id; Type: INDEX; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE INDEX blog_com_com_id ON blog_com USING btree (com_id);


--
-- Name: blog_users_u_name; Type: INDEX; Schema: public; Owner: labtrove; Tablespace: 
--

CREATE UNIQUE INDEX blog_users_u_name ON blog_users USING btree (u_name);


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

