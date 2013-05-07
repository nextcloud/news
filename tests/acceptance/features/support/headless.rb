# only run when export HEADLESS=true
if ENV['HEADLESS'] == 'true'
	
	require 'headless'

	headless = Headless.new
	headless.start

	at_exit do
		headless.destroy
	end

	Before do
		#headless.video.start_capture
	end

	After do |scenario|
		# for demo purpose: always record ;-)
		#video= video_path(scenario)
		#puts "Writing video to #{File.expand_path(video)}"
		#headless.video.stop_and_save(video)

		#  if scenario.failed?
		#    headless.video.stop_and_save(video_path(scenario))
		#  else
		#    headless.video.stop_and_discard
		#  end
	end

	def video_path(scenario)
		"#{scenario.name.split.join("_")}.mov"
	end

end