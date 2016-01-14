<?php

define('LOCK_FILE', __FILE__ . ".pid");

function initialize() 
{
	
	if (isLocked())
		die("Already running.\n");
	
	register_shutdown_function('terminate');
	pcntl_signal(SIGTERM, "terminate");
	
	try {
		$config_path = dirname(__FILE__) . '/../config.ini';
		if(!file_exists($config_path)) {
			throw new Exception("File not found.\n");
		}
		$config = parse_ini_file($config_path, true);
		$GLOBALS['config'] = $config; 
		
	} catch (Exception $e) {
		exit("Configuration file not found.  Exiting...\n");
	}
	echo "Starting up PlexVera...\n";
}


function main() 
{
	$config = $GLOBALS['config'];
	$vera = new Vera($config);
	$plex = new Plex($config);
	$state = 'stopped';
	$pauseTime = 0;

	while(1) {
		$nowPlaying = $plex->get('status/sessions');
		$isPlaying = false;
		
    if ($nowPlaying['size'] == 1) {
      $client = $nowPlaying->Video;
      
      //if ($client->Player['machineIdentifier'] == '') {
      //    exit("No [machineIdentifier] available. Exiting...");
      //}

      if ($client->Player['machineIdentifier'] == $config['plex']['machineIdentifier']) {
        if ($client->Player['state'] == 'playing') {
          $pauseTime = 0;
          $isPlaying = true;

          if ($state != 'playing') {
            $state = 'playing';
            //$logger.info("Device: {$client->Player['title']}.  Video has started.  Dimming lights.");
            print "Device: {$client->Player['title']}.  Video has started.  Dimming lights.\n";
            $vera->transition($state);
          }
        } else if ($client->Player['state'] == 'paused') {
          if ($pauseTime == 0) {
            $pauseTime = time();
          }

          if ((time() - $pauseTime) > 1) {
            $isPlaying = true;

            if ($state != 'paused') {
              $state = 'paused';
              //$logger.info("Device: {$client->Player['title']}.  Video is paused.  Restoring lights.");
              print "Device: {$client->Player['title']}.  Video is paused.  Restoring lights.\n";
              $vera->transition($state);
            }
          }
        }
      }
    } else if ($nowPlaying['size'] >= 2) {
    	
    	foreach ($nowPlaying as $client) {
	      if ($client->Player['machineIdentifier'] == $config['plex']['machineIdentifier']) {
	        if ($client->Player['state'] == 'playing') {
	          $pauseTime = 0;
	          $isPlaying = true;
	
	          if ($state != 'playing') {
	            $state = 'playing';
	            //$logger.info("Device: {$client->Player['title']}.  Video has started.  Dimming lights.");
	            print "Device: {$client->Player['title']}.  Video has started.  Dimming lights.\n";
	            $vera->transition($state);
	          }
	        } else if ($client->Player['state'] == 'paused') {
	          if ($pauseTime == 0) {
	            $pauseTime = time();
	          }
	
	          if ((time() - $pauseTime) > 1) {
	            $isPlaying = true;
	
	            if ($state != 'paused') {
	              $state = 'paused';
	              //$logger.info("Device: {$client->Player['title']}.  Video is paused.  Restoring lights.");
	              print "Device: {$client->Player['title']}.  Video is paused.  Restoring lights.\n";
	              $vera->transition($state);
	            }
	          }
	        }
	      }
  		}
    } else if ($isPlaying == false && $state != 'stopped') {
			$state = 'stopped';
			$isPlaying = false;
			
			sleep(1);
			//$logger.info("Device: {$client->Player['title']}.  Video is stopped.  Turning lights back on.")
			print "Device: {$client->Player['title']}.  Video is stopped.  Turning lights back on.\n";
			$vera->transition($state);
    }
		
		sleep(2); 

	}

}


function isLocked() 
{ 
    // If lock file exists, check if stale.  If exists and is not stale, return TRUE 
    // Else, create lock file and return FALSE. 

    if( file_exists( LOCK_FILE ) ) 
    { 
        // check if it's stale 
        $lockingPID = trim( file_get_contents( LOCK_FILE ) ); 

       // Get all active PIDs. 
        $pids = explode( "\n", trim( `ps -e | awk '{print $1}'` ) ); 

        // If PID is still active, return true 
        if( in_array( $lockingPID, $pids ) )  return true; 

        // Lock-file is stale, so kill it.  Then move on to re-creating it. 
        echo "Removing stale lock file.\n"; 
        unlink( LOCK_FILE ); 
    }
    
    file_put_contents( LOCK_FILE, getmypid() . "\n" );
    return false; 
}


function terminate()
{
    echo "Terminated.\n";
    unlink(LOCK_FILE);
    exit();
}

initialize();
main();



echo "Finished.\n";


class Plex 
{
	
	public function __construct($config) 
	{
		$GLOBALS['config'] = $config;
	}
	
	public function get($query) 
	{
		$config = $GLOBALS['config'];
		$url = $config['plex']['server'] . ":32400/" . $query;
		
		$ch = curl_init();
		$headers = array(
			'Accept: application/xml',
			'Content-Type: application/xml',
			'X-Plex-Token: ' . $config['plex']['api_key']
		);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $curl = curl_exec($ch);
    curl_close($ch);

    $response = simplexml_load_string($curl);
		
		return $response;
	}
}


class Vera 
{
	
	public function __construct($config) 
	{
		$GLOBALS['config'] = $config;
	}
	
	public function transition($state) 
	{
		$config = $GLOBALS['config'];
		
    if ($state == 'playing') {
      $url = "http://{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum={$config['vera']['scene_start']}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_exec($ch);
			curl_close($ch);
    } else if ($state == 'paused') {
      $url = "http://{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum={$config['vera']['scene_pause']}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_exec($ch);
			curl_close($ch);
    } else if ($state == 'stopped') {
      $url = "http://{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum={$config['vera']['scene_stop']}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_exec($ch);
			curl_close($ch);
    } else if ($state == 'wake') {
      $url = "http://{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum={$config['vera']['scene_wake']}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_exec($ch);
			curl_close($ch);
    } else if ($state == 'standby') {
      $url = "http://{$config['vera']['hub_ip']}:3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum={$config['vera']['scene_sleep']}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_exec($ch);
			curl_close($ch);
    }
		
		return;
	}
}

?>
