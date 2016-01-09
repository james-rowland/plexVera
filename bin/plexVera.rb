#!/usr/bin/ruby
require 'rubygems'
require 'bundler/setup'
require 'json'
require 'bundler/setup'
require 'httparty'
require 'logger'
require 'optparse'

require_relative 'plex'
require_relative 'vera'

class PlexVera
  $options = {
      :verbose => false
  }

  OptionParser.new do |opts|
    opts.banner = "PlexVera: a script for syncing your lights with your Plex video playback\nUsage: PlexVera.rb [$options]"

    opts.on("-v", "--verbose", "Enable verbose debug logging") do |opt|
      $options[:verbose] = true
    end
  end.parse!

  def initialize
    begin
      $config = YAML.load_file(File.join(File.expand_path(File.dirname(__FILE__)), '../config.yaml'))
    rescue Errno::ENOENT => e
      abort('Configuration file not found.  Exiting...')
    end

    begin
      $logging_path = File.join(File.expand_path(File.dirname(__FILE__)), '../plexVera.log')
      $logger = Logger.new($logging_path)

      if $options[:verbose]
        $logger.level = Logger::DEBUG
      else
        $logger.level = Logger::INFO
      end
    rescue
      abort('Log file not found.  Exiting...')
    end

    $logger.info("Starting up PlexVera")
    print "Starting up PlexVera\n"
  end

  def main
    vera = Vera.new($config)
    plex = Plex.new($config)
    $state = 'stopped'
    $pauseTime = 0

    while begin
      nowPlaying = plex.get('status/sessions')['MediaContainer']
    rescue
      next
    end
      isPlaying = false

      if nowPlaying['size'].to_i == 1
        client = nowPlaying['Video']

        if client['Player']['machineIdentifier'].empty?
          client['Player']['machineIdentifier'] = ''
        end

        if client['Player']['machineIdentifier'] == $config['plex']['machineIdentifier']
          if client['Player']['state'] == 'playing'
            $pauseTime = 0
            isPlaying = true

            if $state != 'playing'
              $state = 'playing'
              $logger.info("Device: #{client['Player']['title']}.  Video has started.  Dimming lights.")
              print "Device: #{client['Player']['title']}.  Video has started.  Dimming lights.\n"
              vera.transition($state)
            end
          elsif client['Player']['state'] == 'paused'
            if $pauseTime == 0
              $pauseTime = Time.now
            end

            if (Time.now - $pauseTime) > 1
              isPlaying = true

              if $state != 'paused'
                $state = 'paused'
                $logger.info("Device: #{client['Player']['title']}.  Video is paused.  Restoring lights.")
                print "Device: #{client['Player']['title']}.  Video is paused.  Restoring lights.\n"
                vera.transition($state)
              end
            end
          end
        end
      elsif nowPlaying['size'].to_i > 1
        nowPlaying['Video'].each do |client|
          if client['Player']['machineIdentifier'].empty?
            client['Player']['machineIdentifier'] = ''
          end

          if client['Player']['machineIdentifier'] == $config['plex']['machineIdentifier']
            if client['Player']['state'] == 'playing'
              $pauseTime = 0
              isPlaying = true

              if $state != 'playing'
                $state = 'playing'
                $logger.info("Device: #{client['Player']['title']}.  Video has started.  Dimming lights.")
                print "Device: #{client['Player']['title']}.  Video has started.  Dimming lights.\n"
                vera.transition($state)
              end
            elsif client['Player']['state'] == 'paused'
              if $pauseTime == 0
                $pauseTime = Time.now
              end

              if (Time.now - $pauseTime) > 1
                isPlaying = true

                if $state != 'paused'
                  $state = 'paused'
                  $logger.info("Device: #{client['Player']['title']}.  Video is paused.  Restoring lights.")
                  print "Device: #{client['Player']['title']}.  Video is paused.  Restoring lights.\n"
                  vera.transition($state)
                end
              end
            end
          end
        end

      elsif (!isPlaying && $state != 'stopped')
        $state = 'stopped'
        isPlaying = false

        sleep 1
        $logger.info("Device: #{client['Player']['title']}.  Video is stopped.  Turning lights back on.")
        print "Device: #{client['Player']['title']}.  Video is stopped.  Turning lights back on.\n"
        vera.transition($state)
      end
      sleep 1
    end
  end
end

plexVera = PlexVera.new
plexVera.main
