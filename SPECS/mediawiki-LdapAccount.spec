%define extname LdapAccount
Name:           mediawiki-%{extname}
Version:        0.1
Release:        1%{?dist}
Summary:        Use LDAP as account source for medaiwiki

Group:          Development/Tools
License:        WTFPLv2 
URL:            http://github.com/stahnma/mediawiki-%{extname}
Source0:        http://github.com/stahnma/mediawiki-%{extname}/
Source1:        README.%{extname}
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch:      noarch

Requires:       mediawiki >= 1.13, php-ldap

%description
Restrict mediawiki to using LDAP accounts only, creates account based on 
LDAP information and authenticates using LDAP.

%prep
cp -p -f %{SOURCE1} ./README
cp -p -f %{SOURCE0} . 


%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT%{_datadir}/mediawiki/extensions/%{extname}
install -p  -m644 *.php $RPM_BUILD_ROOT%{_datadir}/mediawiki/extensions/%{extname}

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
%doc README
%{_datadir}/mediawiki/extensions/%{extname}

%changelog
* Tue Dec 12 2009 <stahnma@websages.com>  - 0.1-1
- Initial Package SPEC
