plexVera
=======

Program to integrate Plex with Vera.

Based on the work done by bstascavage https://github.com/bstascavage/plexHue


## Introduction
This program runs scenes from Vera based on Plex playback.  It runs a user defined scene whenever a movie starts, paused or stopped.


## Prerequisites
1.  Ruby installed (at least version 1.9.3) and ruby-dev.
2.  Your Vera Hub up and configured.
3.  A PlexPass membership (the required API only works when you have PlexPass)

    
## Config file

##### plex
`server` - IP address of your Plex server.  Defaults to `localhost`.  Optional.

`machineIdentifier` - Unique identifier of your Plex client.  You can find this by starting up a video on your device and then running `bin/getMachineID.rb` and finding your device in the output.  Required.

`api_key` - Your Plex API key.  This can be found by searching for your device here (it is the 'token' field): https://plex.tv/devices.xml.  Required.

##### hue
`hub_ip` - IP addres of your Philips Hue Hub.  You can get this by visiting http://www.meethue.com/api/nupnp while on the same network as your hub.  Required.

`scene_start` - The scene number# you want to run when a video starts.

`scene_pause` - The scene number# you want to run when a video is paused.

`scene_stop` - The scene number# you want to run when a video stops.
