Summary:        Mindbit Webhosting Platform
Name:           mipanel
Version:        0.1
Release:        1
License:        AGPL
BuildArch:		i386
Packager:       Radu Rendec
Group:			Applications/Internet
Vendor:         Mindbit SRL
Source:			%{name}-%{version}.tar.gz
BuildRoot:      %{_tmppath}/%{name}-%{version}-root

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

pushd backend/model
propel-gen
popd

pushd redirect
%make
popd

%install
rm -rf $RPM_BUILD_ROOT

find backend/model/build \
	sql/schema.sql \
	sql/changelog.sql \
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{_libdir}/mipanel/\{\} \;

find backend \
	web \
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{_libdir}/mipanel/\{\} \;

install -m 755 -D redirect/redirect ${RPM_BUILD_ROOT}%{_bindir}/redirect

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
	-type f -exec install -m 644 -D \{\} ${RPM_BUILD_ROOT}%{_libdir}/mipanel/templates/\{\} \;
install -m 644 -D templates/httpd/ssl.conf ${RPM_BUILD_ROOT}%{_libdir}/mipanel/templates/httpd/ssl.conf
popd

%clean
rm -rf $RPM_BUILD_ROOT
rm -rf $RPM_BUILD_DIR/%{name}-%{version}

%files
%defattr(-,root,root)
%config(noreplace) %{_sysconfdir}/httpd/conf/mipanel.conf
%{_libdir}/mipanel/backend/model/build/classes
%{_libdir}/mipanel/backend/model/build/conf/classmap-mipanel-conf.php
%{_libdir}/mipanel/backend/model/build/sql
%{_libdir}/mipanel/backend/install.php
%{_libdir}/mipanel/backend/HttpdConf.php
%{_libdir}/mipanel/backend/SrvCtl.php
%{_libdir}/mipanel/sql
%{_libdir}/mipanel/templates
%{_libdir}/mipanel/web

%defattr(-,root,squid)
%config(noreplace) %{_sysconfdir}/mipanel/squid-in/squid.conf
%config(noreplace) %{_sysconfdir}/mipanel/squid-out/squid.conf

%defattr(-,squid,squid)
%dir %{_var}/spool/mipanel/squid-$dir
%dir %{_var}/log/mipanel/squid-$dir

# %doc scripts/sql/schema.sql
# %doc scripts/sql/update-db.sql

%defattr(-,root,apache)
%config(noreplace) %{_sysconfdir}/redirect.conf

%changelog
* Tue Oct  5 2010 Radu Rendec <radu.rendec@mindbit.ro> - 0.1-1
- Created spec file
