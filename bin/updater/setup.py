from sys import exit
from xml.etree import ElementTree

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
    name = 'owncloud-news-updater',
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
    entry_points = {
        'console_scripts': [
            'owncloud-news-updater = owncloud_news.application:main'
        ]
    }
)
