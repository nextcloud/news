require 'bundler/setup'
require 'capybara/cucumber'
require 'selenium-webdriver'

Encoding.default_external = Encoding::UTF_8
Encoding.default_internal = Encoding::UTF_8

Capybara.register_driver :selenium do |app|
	http_client = Selenium::WebDriver::Remote::Http::Default.new
	http_client.timeout = 200
	Capybara::Selenium::Driver.new(app, :browser => :firefox, :http_client => http_client)
end

#
# app and app_host are set via command line parameter on cucumber call:
#   cucumber HOST=33.33.33.10
#
host = ENV['HOST']
host ||= '33.33.33.10'
Capybara.app = host
Capybara.run_server = false
Capybara.app_host = "http://#{host}"
Capybara.default_selector = :css
Capybara.default_driver = :selenium
