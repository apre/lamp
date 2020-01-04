<?php 
/**
 * A tile-fetcher that have a local (server-side) cache
 * It validates the tiles and download them again if the cached-version is corrupted.
 * 
 * Don't forget that osm tile server are NOT free to use. see https://operations.osmfoundation.org/policies/tiles/
 * If you want to use mapbox tiles you must edit the $CONFIG array to add your mapobox API key
 */

/**
 * Checks if a folder exist and return canonicalized absolute pathname (long version)
 * @param string $folder the path being checked.
 * @return mixed returns the canonicalized absolute pathname on success otherwise FALSE is returned
 */
function folder_exist($folder)
{
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    if($path !== false AND is_dir($path))
    {
        // Return canonicalized absolute pathname
        return $path;
    }

    // Path/folder does not exist
    return false;
}


$id = "0/0/0";
$cfgToUse="mapbox";    # default configuration

if (isset($_GET['id'])) {
    #echo $_GET['id'];
    $id =  $_GET['id'];
} else {
    // Fallback behaviour goes here
    echo "missing id param";
    die();
}

if (isset($_GET['cfg'])) {
    #echo $_GET['id'];
    $cfgToUse =  $_GET['cfg'];
} 

if (isset($_GET['debug'])) {
    #echo $_GET['id'];
    $DEBUG=true;
} else {
    $DEBUG=false;
    error_reporting(0); # turn off error reporting
    header('Content-type: image/png'); # default mode: return a png image
}

list($TILE_Z, $TILE_X, $TILE_Y) =   explode("/", $id, 3);


$CONFIG = [
    "osm" => [ "folder" =>"osm" , "url" => "https://c.tile.openstreetmap.org/$TILE_Z/$TILE_X/$TILE_Y.png"],
    "osmfr" => [ "folder" =>"osmfr" , "url" => "https://c.tile.openstreetmap.fr/osmfr/$TILE_Z/$TILE_X/$TILE_Y.png"],
    "mapbox" => [ "folder" =>"mapbox" , "url" => "https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/$TILE_Z/$TILE_X/$TILE_Y?access_token=YOUR_MAPBOX_TOKEN_HERE"]
 ];

# check that $cfg is defined in $CONFIG

if (array_key_exists($cfgToUse,$CONFIG))
{
$cfg = $cfgToUse;
} else
{
    $cfg = "osm";
}


$TILE_URL = $CONFIG[$cfg]["url"];
$TILE_FOLDER = $CONFIG[$cfg]["folder"];

if ($DEBUG)
{
echo "<br>$TILE_Z $TILE_X $TILE_Y<br/>$TILE_URL<br/>";
}


$cached="cache/$TILE_FOLDER/$TILE_Z/$TILE_X-$TILE_Y.png";

if (!folder_exist("cache"))
{
    mkdir("cache");
}

if (!folder_exist("cache/$TILE_FOLDER"))
{
    mkdir("cache/$TILE_FOLDER");
}

if (!folder_exist("cache/$TILE_FOLDER/$TILE_Z"))
{
    mkdir("cache/$TILE_FOLDER/$TILE_Z");
}

$download_tile = true; # default: download, unless valid tile in cache

if (file_exists($cached))
{
    if ($DEBUG)
    {
        echo "<br> tile $cached exists<br>";
    }

    $img = imagecreatefrompng($cached);
    if(!$img)
    {
        unlink($cached);
        $download_tile = true;
        if ($DEBUG)
            {
                echo "deleted invalid tile $cached<br>";
            }
    } 
    else {
        imagepng($img);
        imagedestroy($img);
        exit(0);
    }
}  
 

if ($download_tile)
{
    # download and cache the tile
    if ($DEBUG){echo "download tile from $TILE_URL<br>";}

    $remote_tile = file_get_contents($TILE_URL);
    if ( $remote_tile===false)
    {
        if ($DEBUG){echo "download error for $TILE_URL<br>";}
        exit(1); # download error
    }

    if(connection_aborted()){
        if ($DEBUG){echo "connection_aborted for $TILE_URL<br>";}
        echo  $remote_tile;
        exit(0); # do NOT save, potentialy corrupted content
    }

    if (strlen($remote_tile))
    {  # only save if valid content
        ignore_user_abort(true); # prevent save to be interrupted
        set_time_limit(10);
        file_put_contents($cached, $remote_tile);

        { 
            # reload saved file (check if valid, delete it if NOT)
        $img = imagecreatefrompng($cached);
        if(!$img)
        {
            if ($DEBUG){echo "png load error for $cached<br>";}
            unlink($cached);
        } 
        else {
            imagepng($img);
            imagedestroy($img);
        }
    }
}
    
}



?>