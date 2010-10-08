%define mipanel_root %{_prefix}/lib/mipanel

Summary:        Mindbit Webhosting Platform
Name:           mipanel
Version:        0.1
Release:        1
License:        AGPL
Packager:       Radu Rendec
Group:			Applications/Internet
Vendor:         Mindbit SRL
Source:			%{name}-%{version}.tar.gz
BuildRoot:      %{_tmppath}/%{name}-%{version}-root
BuildRequires:	libconfig-devel

Requires:		php >= 5.2.0
Requires:		php-mysql php-ldap
Requires:		mpl
Requires:		dovecot squid postgresql-server mydns

%description
Mipanel is an integrated system for the administration of web servers
allowing for the unitary management of all services: HTTP, SMTP/POP/IMAP,
DNS, FTP, databases, etc. The system has been developed having in mind
security, performance and resource distribution across multiple servers
as basic priorities.

%prep
%setup -q -n %{name}

%build

sh autogen.sh
%configure
make

pushd backend/model
mv build.properties.default build.properties
mv runtime-conf.xml.default runtime-conf.xml
propel-gen
popd

%install
rm -rf $RPM_BUILD_ROOT

find \
	backend/HttpdConf.php \
	backend/SrvCtl.php \
	backend/SrvCtlRmiServer.php \
	backend/install.php \
	backend/model/build \
	sql/schema.sql \
	sql/changelog.sql \
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{_libdir}/mipanel/\{\} \;

find backend \
	web \
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{mipanel_root}/\{\} \;

install -m 755 -D src/redirect/redirect ${RPM_BUILD_ROOT}%{_bindir}/redirect
install -m 640 -D src/redirect/redirect.conf.default ${RPM_BUILD_ROOT}%{_sysconfdir}/mipanel/redirect.conf

for dir in "in" "out"; do
	install -m 640 -D templates/squid-$dir/squid.conf ${RPM_BUILD_ROOT}%{_sysconfdir}/mipanel/squid-$dir/squid.conf
	install -m 750 -d ${RPM_BUILD_ROOT}%{_var}/spool/mipanel/squid-$dir
	install -m 750 -d ${RPM_BUILD_ROOT}%{_var}/log/mipanel/squid-$dir
done

install -m 644 -D templates/httpd/mipanel.conf ${RPM_BUILD_ROOT}%{_sysconfdir}/httpd/conf/mipanel.conf

pushd templates
find dovecot \
	mydns \
	pam.d \
	pam-pgsql \
	postfix \
	redirect \
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{mipanel_root}/templates/\{\} \;
install -m 644 -D httpd/ssl.conf ${RPM_BUILD_ROOT}%{mipanel_root}/templates/httpd/ssl.conf
popd

%clean
rm -rf $RPM_BUILD_ROOT
rm -rf $RPM_BUILD_DIR/%{name}-%{version}

%files
%defattr(-,root,root)
%config(noreplace) %{_sysconfdir}/httpd/conf/mipanel.conf
%config(noreplace) %{mipanel_root}/backend/model/build/conf/mipanel-conf.php
%{_bindir}/redirect
%{mipanel_root}/backend/model/build/classes
%{mipanel_root}/backend/model/build/conf/classmap-mipanel-conf.php
%{mipanel_root}/backend/model/build/sql
%{mipanel_root}/backend/install.php
%{mipanel_root}/backend/HttpdConf.php
%{mipanel_root}/backend/SrvCtl.php
%{mipanel_root}/backend/SrvCtlRmiServer.php
%{mipanel_root}/sql
%{mipanel_root}/templates
%{mipanel_root}/web

%defattr(-,root,squid)
%config(noreplace) %{_sysconfdir}/mipanel/squid-in/squid.conf
%config(noreplace) %{_sysconfdir}/mipanel/squid-out/squid.conf
%config(noreplace) %{_sysconfdir}/mipanel/redirect.conf

%defattr(-,squid,squid)
%dir %{_var}/spool/mipanel/squid-in
%dir %{_var}/log/mipanel/squid-out

# %doc scripts/sql/schema.sql
# %doc scripts/sql/update-db.sql

%defattr(-,root,apache)

%changelog
* Tue Oct  5 2010 Radu Rendec <radu.rendec@mindbit.ro> - 0.1-1
- Created spec file
