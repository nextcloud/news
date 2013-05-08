# use this to turn off animations to check for visibility
def turn_off_animations
	page.execute_script("$.fx.off=true")
end


When (/^I hover over the add new button$/) do
	# FIXME: get this working with hover action
	page.execute_script("$('.list-title span').show()")
end

When (/^I hover out off the add new button$/) do
	# FIXME: get this working with hover action
	page.execute_script("$('.list-title span').hide()")
end

Then (/^I should (not )?see an "([^"]+)" caption on the add new button$/) do |shouldNot, caption|
	selector = "//li[contains(@class,\"add-new\")]/a/span[1]"
	if shouldNot
		page.should have_no_selector(:xpath, selector)
	else
		page.find(:xpath, selector).should have_content(caption)
	end
end


When (/^I click on the add new button$/) do
	turn_off_animations()
	click_link 'Add Website'
end

When (/^I click somewhere else$/) do
	page.execute_script("$('#app-content').trigger('click')")
end


Then (/^I should (not )?see a form to add feeds and folders$/) do |shouldNot|
	selector = "//*[contains(@class,\"add-new-popup\")]"

	if shouldNot
		page.should have_no_selector(:xpath, selector)
	else
		page.should have_selector(:xpath, selector)
	end
end
