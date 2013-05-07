When (/^I hover over the add new button$/) do
	# FIXME: get this working with hover action
	page.execute_script("$('.list-title span').show()")
end

When (/^I hover out of the add new button$/) do
	# FIXME: get this working with hover action
	page.execute_script("$('.list-title span').hide()")
end

Then (/^I should see an "([^"]+)" caption on the add new button$/) do |caption|
	button = page.find(:xpath, "//li[contains(@class,\"add-new\")]/a/span[1]")
	button.should have_content(caption)
end

Then (/^I should not see an "([^"]+)" caption on the add new button$/) do |caption|
	# if its not visible the selector wont find it
	page.should have_no_selector(:xpath, "//li[contains(@class,\"add-new\")]/a/span[1]")
end