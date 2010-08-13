--
--  Table structure for table 'soa' (zones of authority)
--
CREATE TABLE soa (
  id      SERIAL NOT NULL PRIMARY KEY,
  origin  VARCHAR(255) NOT NULL,
  ns      VARCHAR(255) NOT NULL,
  mbox    VARCHAR(255) NOT NULL,
  serial  INTEGER NOT NULL default 1,
  refresh INTEGER NOT NULL default 28800,
  retry   INTEGER NOT NULL default 7200,
  expire  INTEGER NOT NULL default 604800,
  minimum INTEGER NOT NULL default 86400,
  ttl     INTEGER NOT NULL default 86400,
  active  VARCHAR(1) NOT NULL CHECK (active='Y' OR active='N'),
  xfer    CHAR(255) DEFAULT NULL,
  UNIQUE  (origin)
);

--
--  Table structure for table 'rr' (resource records)
--
CREATE TABLE rr (
  id     SERIAL NOT NULL PRIMARY KEY,
  zone   INTEGER NOT NULL,
  name   VARCHAR(64) NOT NULL,
  data   BYTEA NOT NULL,
  aux    INTEGER NOT NULL default 0,
  ttl    INTEGER NOT NULL default 86400,
  type   VARCHAR(5) NOT NULL CHECK (type='A' OR type='AAAA' OR type='ALIAS' OR type='CNAME' OR type='HINFO' OR type='MX' OR type='NAPTR' OR type='NS' OR type='PTR' OR type='RP' OR type='SRV' OR type='TXT'),
  UNIQUE (zone,name,type,data),
  FOREIGN KEY (zone) REFERENCES soa (id) ON DELETE CASCADE
);

alter table domains add soa_id int;
alter table domains add foreign key (soa_id) references soa(id) on update cascade on delete set null;
