DROP FUNCTION ftp_auth(character varying, character varying);

CREATE FUNCTION ftp_auth(character varying, character varying) RETURNS SETOF character varying AS $_$
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
$_$ LANGUAGE plpgsql;
