DROP FUNCTION get_mailbox_properties(character varying, character varying);
DROP TYPE mailbox_properties;

CREATE TYPE mailbox_properties AS (
	path character varying,
	uid integer,
	gid integer
);

CREATE FUNCTION get_mailbox_properties(character varying, character varying) RETURNS SETOF mailbox_properties AS $_$
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
$_$ LANGUAGE plpgsql;
