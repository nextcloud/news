When (/^I hover over the add new button$/) do |selector|
	page.execute_script("$('.add-new').trigger('mouseover')")
end

Then (/^I should see a "([^"]*)" caption on the add new button"$/) do |caption|
	page.find(:xpath, "//*[@class='add-new']/a/span").should have_content(caption)
end