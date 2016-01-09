#!/usr/bin/ruby
require 'rubygems'
require 'json'
require 'httparty'

class Vera
    include HTTParty

    def initialize(config)
        $config = config
    end

    format :json

    def transition(state)
        if state == 'playing'
          self.class.get("http://#{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=#{$config['vera']['scene_start']}")
        elsif state == 'paused'
          self.class.get("http://#{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=#{$config['vera']['scene_pause']}")
        elsif state == 'stopped'
          self.class.get("http://#{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=#{$config['vera']['scene_stop']}")
        end
    end

end
