<?

class HttpdConf {
	protected $name;
	protected $port;
	protected $basePath = "/var/www/sites";
	protected $serverRoot = null;
	protected $documentRoot = "web";
	protected $allModules = array(
			"auth_basic_module" => "modules/mod_auth_basic.so",
			"auth_digest_module" => "modules/mod_auth_digest.so",
			"authn_file_module" => "modules/mod_authn_file.so",
			"authn_alias_module" => "modules/mod_authn_alias.so",
			"authn_anon_module" => "modules/mod_authn_anon.so",
			"authn_dbm_module" => "modules/mod_authn_dbm.so",
			"authn_default_module" => "modules/mod_authn_default.so",
			"authz_host_module" => "modules/mod_authz_host.so",
			"authz_user_module" => "modules/mod_authz_user.so",
			"authz_owner_module" => "modules/mod_authz_owner.so",
			"authz_groupfile_module" => "modules/mod_authz_groupfile.so",
			"authz_dbm_module" => "modules/mod_authz_dbm.so",
			"authz_default_module" => "modules/mod_authz_default.so",
			"ldap_module" => "modules/mod_ldap.so",
			"authnz_ldap_module" => "modules/mod_authnz_ldap.so",
			"include_module" => "modules/mod_include.so",
			"log_config_module" => "modules/mod_log_config.so",
			"logio_module" => "modules/mod_logio.so",
			"env_module" => "modules/mod_env.so",
			"ext_filter_module" => "modules/mod_ext_filter.so",
			"mime_magic_module" => "modules/mod_mime_magic.so",
			"expires_module" => "modules/mod_expires.so",
			"deflate_module" => "modules/mod_deflate.so",
			"headers_module" => "modules/mod_headers.so",
			"usertrack_module" => "modules/mod_usertrack.so",
			"setenvif_module" => "modules/mod_setenvif.so",
			"mime_module" => "modules/mod_mime.so",
			"dav_module" => "modules/mod_dav.so",
			"status_module" => "modules/mod_status.so",
			"autoindex_module" => "modules/mod_autoindex.so",
			"info_module" => "modules/mod_info.so",
			"dav_fs_module" => "modules/mod_dav_fs.so",
			"vhost_alias_module" => "modules/mod_vhost_alias.so",
			"negotiation_module" => "modules/mod_negotiation.so",
			"dir_module" => "modules/mod_dir.so",
			"actions_module" => "modules/mod_actions.so",
			"speling_module" => "modules/mod_speling.so",
			"userdir_module" => "modules/mod_userdir.so",
			"alias_module" => "modules/mod_alias.so",
			"rewrite_module" => "modules/mod_rewrite.so",
			"proxy_module" => "modules/mod_proxy.so",
			"proxy_balancer_module" => "modules/mod_proxy_balancer.so",
			"proxy_ftp_module" => "modules/mod_proxy_ftp.so",
			"proxy_http_module" => "modules/mod_proxy_http.so",
			"proxy_connect_module" => "modules/mod_proxy_connect.so",
			"cache_module" => "modules/mod_cache.so",
			"suexec_module" => "modules/mod_suexec.so",
			"disk_cache_module" => "modules/mod_disk_cache.so",
			"file_cache_module" => "modules/mod_file_cache.so",
			"mem_cache_module" => "modules/mod_mem_cache.so",
			"cgi_module" => "modules/mod_cgi.so",
			"version_module" => "modules/mod_version.so"
				);

	function setName($name) {
		$this->name = $name;
	}

	function setPort($port) {
		$this->port = $port;
	}

	function getServerRoot() {
		return $this->serverRoot !== null ? $this->serverRoot:
			$this->basePath . "/" . $this->name;
	}

	function prefork() {
		return
			"StartServers 8\n" .
			"MinSpareServers 5\n" .
			"MaxSpareServers 20\n" .
			"ServerLimit 256\n" .
			"MaxClients 256\n" .
			"MaxRequestsPerChild 4000\n"
			;
	}

	function modules() {
		$modules = array(
				"dir_module",
				"mime_module",
				"log_config_module",
				"proxy_module",
				"proxy_http_module",
				"extract_forwarded_module",
				"rewrite_module",
				"php5_module"
				);
		$ret = "";
		foreach ($modules as $module)
			$ret .= "LoadModule " . $module . " " . $this->allModules[$module] . "\n";
		return $ret;
	}

	function php() {
		return
			"AddHandler php5-script .php\n" .
			"AddType text/html .php\n" .
			"php_value session.save_path " . $this->getServerRoot() . "/tmp\n"
			;
	}

	function webmail() {
		return
			'RewriteRule ^/webmail$ http://' . $this->name . "/webmail/ [R,L]\n" .
			'RewriteRule ^/webmail/(.*)$ http://127.0.0.1:8000/webmail/$1 [P]' . "\n"
			;
	}

	function create() {
		return
			"ServerTokens OS\n" .
			"ServerRoot \"" . $this->getServerRoot() . "\"\n" .
			"PidFile run/httpd.pid\n" .
			"Timeout 120\n" .
			"KeepAlive Off\n" .
			"\n" .
			$this->prefork() .
			"\n" .
			"Listen 8016\n" .
			"\n" .
			$this->modules() .
			"\n" .
			"ServerAdmin webadm@mindbit.ro\n" .
			"UseCanonicalName Off\n" .
			"DocumentRoot \"" . $this->documentRoot . "\"\n" .
			"DirectoryIndex index.html index.php\n" .
			"AccessFileName .htaccess\n" .
			"TypesConfig /etc/mime.types\n" .
			"HostnameLookups Off\n" .
			"ErrorLog logs/error.log\n" .
			"LogLevel warn\n" .
			'LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined' . "\n" .
			"CustomLog logs/access.log combined\n" .
			"ServerSignature On\n" .
			"\n" .
			$this->php() .
			"\n" .
			"MEForder refuse,accept\n" .
			"MEFrefuse all\n" .
			"MEFaccept 127.0.0.1\n" .
			"\n" .
			"RewriteEngine on\n" .
			$this->webmail();
	}

}

?>

