DROP FUNCTION get_virtual_mail(character varying);

CREATE FUNCTION get_virtual_mail(character varying) RETURNS SETOF text AS $_$
DECLARE
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
$_$ LANGUAGE plpgsql;
