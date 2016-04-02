from platform import python_version
from sys import exit, version_info
from xml.etree import ElementTree

if version_info < (3, ):
    print('Error: Python 3 required but found %s' % python_version())
    exit(1)

try:
    from setuptools import setup, find_packages
except ImportError as e:
    print('Could not find setuptools. Did you install the package?')
    exit(1)

with open('requirements.txt', 'r') as infile:
    install_requires = infile.read().split('\n')

with open('README.rst', 'r') as infile:
    long_description = infile.read()

# parse version from info.xml
tree = ElementTree.parse('../../appinfo/info.xml')
for element in tree.findall('version'):
    version = element.text

setup (
    name = 'owncloud_news_updater',
    version = version,
    description = 'ownCloud news updater',
    long_description = long_description,
    author = 'Bernhard Posselt',
    author_email = 'dev@bernhard-posselt.com',
    url = 'https://github.com/owncloud/news',
    packages = find_packages(),
    include_package_data = True,
    license = 'AGPL',
    install_requires = install_requires,
    keywords = ['owncloud', 'news', 'updater'],
    classifiers = [
        'Intended Audience :: System Administrators',
        'Environment :: Console',
        'License :: OSI Approved :: GNU General Public License v3 or later (GPLv3+)',
        'Operating System :: POSIX :: Linux',
        'Programming Language :: Python :: 3 :: Only',
        'Topic :: Utilities'
    ],
    entry_points = {
        'console_scripts': [
            'owncloud-news-updater = owncloud_news_updater.application:main'
        ]
    }
)
