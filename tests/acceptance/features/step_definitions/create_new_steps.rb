When (/^I hover over the add new button$/) do
	# FIXME: get this working with hover action
	page.execute_script("$('.list-title span').show()")
end

Then (/^I should see a "([^"]+)" caption on the add new button$/) do |caption|
	button = page.find(:xpath, "//li[contains(@class,\"add-new\")]/a/span[1]")
	button.should have_content(caption)
end