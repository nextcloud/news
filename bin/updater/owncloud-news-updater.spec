# Copyright (c) 2014 Bernhard Posselt <dev@bernhard-posselt.com>
Name:           owncloud-news-updater
Version:        4.3.2
Release:        0
Url:            https://github.com/owncloud/news/tree/master/bin/updater
Summary:        A fast, deadlock free ownCloud News feed updater
License:        AGPL-3.0
Group:          Productivity/Networking/Web/Utilities
Source:         https://github.com/owncloud/news/tree/master/bin/updater
BuildRoot:      %{_tmppath}/%{name}-%{version}-build
BuildRequires:  python-devel
BuildRequires:  python-setuptools
Requires:       python > 3.2
Requires:       python-requests
Requires:       python-argparse
Requires:       python-xml
BuildArch:      noarch

%description
ownCloud does not require people to install threading or multiprocessing libraries. Because the feed update process is mainly limited by I/O, parallell fetching of RSS feed updates can speed up the updating process significantly. In addition the cronjob can get into a deadlock (https://github.com/owncloud/core/issues/3221) which will cause the updater to get stuck resulting in your feeds not to being updated anymore. This can be solved by using a script that uses the updater API: https://github.com/owncloud/news/wiki/Cron-1.2

%prep
%setup -q -n %{name}-%{version}

%build
python setup.py build

%install
python setup.py install --prefix=%{_prefix} --install-scripts=/usr/bin --root=%{buildroot}
mkdir -p /etc/owncloud/news
install -D -m 0644 %{buildroot}/example-config.ini /etc/owncloud/news/updater.ini
install -D -m 0644 %{buildroot}/systemd/owncloud-news-updater.service /etc/systemd/system/

%pre
%service_add_pre %{name}.service

%post
%service_add_post %{name}.service

%preun
%service_del_preun %{name}.service

%postun
%service_del_postun %{name}.service

%files
%config %attr(0644,root,root) /etc/owncloud/news/updater.ini


