plexVera
=======

Program to integrate Plex with Vera (also doubles as an unraid plugin).

Based on the work done by bstascavage https://github.com/bstascavage/plexHue.


## Introduction
This program runs scenes from Vera based on Plex playback.  It runs a user defined scene whenever a movie starts, paused or stopped.


## Prerequisites
1.  Your Vera Hub up and configured.
2.  A PlexPass membership (the required API only works when you have PlexPass).
3.  Unraid or ruby (for standalone mode).


## Install on Unraid
1.  Download unraid/plexvera.plg and install on unraid /boot/config/plugins (or use UI to install plg file).


## Config file

##### plex
`server` - IP address of your Plex server.  Defaults to `localhost`.  Optional.

`machineIdentifier` - Unique identifier of your Plex client.  You can find this looking at <plexserver_ip>:32400/status/sessions.  Required.

`api_key` - Your Plex API key.  This can be found by searching for your device here (it is the 'token' field): https://plex.tv/devices.xml.  Required.

##### hue
`hub_ip` - IP addres of your Philips Hue Hub.  You can get this by visiting http://www.meethue.com/api/nupnp while on the same network as your hub.  Required.

`scene_start` - The scene number# you want to run when a video starts.

`scene_pause` - The scene number# you want to run when a video is paused.

`scene_stop` - The scene number# you want to run when a video stops.


## Docker HELP

Build image:
$ docker build -t <name>/plex-vera .

Run image:
$ docker run --net=host -p 51826:51826 <name>/plex-vera

One liner to stop / remove all of Docker containers:
$ docker stop $(docker ps -a -q)
$ docker rm $(docker ps -a -q)

And similar for all images:
$  docker rmi $(docker images -q)
