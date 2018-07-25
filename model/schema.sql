-- MySQL >= 5.7.7

CREATE VIEW domains AS
  SELECT domain, SUM(dns) AS dns, SUM(mail) AS mail FROM (
    SELECT IF(RIGHT(origin, 1) = '.', LEFT(origin, LENGTH(origin) - 1), origin) AS domain, 1 AS dns, 0 AS mail FROM dns_zones
    UNION ALL SELECT domain, 0 AS dns, 1 AS mail FROM mail_domains
  ) AS _sub GROUP BY domain;


-- MySQL < 5.7.7

CREATE VIEW _domains_union AS
  SELECT IF(RIGHT(origin, 1) = '.', LEFT(origin, LENGTH(origin) - 1), origin) AS domain, 1 AS dns, 0 AS mail FROM dns_zones
  UNION ALL SELECT domain, 0 AS dns, 1 AS mail FROM mail_domains;

CREATE VIEW domains AS
  SELECT domain, SUM(dns) AS dns, SUM(mail) AS mail FROM _domains_union GROUP BY domain;
