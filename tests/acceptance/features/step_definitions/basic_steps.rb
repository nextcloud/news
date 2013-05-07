Given (/^I am logged in$/) do

  # be sure to use the right browser session
  Capybara.session_name = 'test'

  # logout - just to be sure
  visit '/index.php?logout=true'
  visit '/'
  fill_in 'user', with: 'test'
  fill_in 'password', with: "test"
  click_button 'submit'

  #save_page
  page.should have_selector('a#logout')
end

When (/^I am in the "([^"]*)" app$/) do |app|
  visit "/index.php/apps/#{app}"
  page.should have_selector('#logout')
end

When (/^I go to "([^"]*)"$/) do |path|
  visit "#{path}"
  page.should have_selector('#logout')
end