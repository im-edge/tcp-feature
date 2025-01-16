%define revision 1
%define git_version %( git describe --tags | cut -c2- | tr -s '-' '+')
%define git_hash %( git rev-parse --short HEAD )
%define basedir         %{_datadir}/imedge-features/tcp
%define bindir          %{_bindir}
%undefine __brp_mangle_shebangs

Name:           imedge-feature-tcp
Version:        %{git_version}
Release:        %{revision}%{?dist}
Summary:        IMEdge TCP Feature
Group:          Applications/System
License:        MIT
URL:            https://github.com/im-edge/tcp-feature
Source0:        https://github.com/im-edge/tcp-feature/archive/%{git_hash}.tar.gz
BuildArch:      noarch
BuildRoot:      %{_tmppath}/%{name}-%{git_version}-%{release}
Packager:       Thomas Gelf <thomas@gelf.net>
Requires:       imedge-node

%description
IMEdge TCP Feature, implemented as a very simple sample feature.

%prep

%build

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}
mkdir -p %{buildroot}%{basedir}
cd - # ???
cp -pr src vendor feature.php %{buildroot}%{basedir}/

%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root)
%{basedir}

%changelog
* Mon Jan 13 2025 Thomas Gelf <thomas@gelf.net> 0.0.0
- Initial packaging
