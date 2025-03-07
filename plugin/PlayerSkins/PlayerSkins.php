<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

class PlayerSkins extends PluginAbstract {

    static public $hasMarks = false;

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription() {
        global $global;
        $desc = "Customize your playes Skin<br>The Skis options are: ";
        $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
        $names = array();
        foreach (glob($dir . '*.css') as $file) {
            $path_parts = pathinfo($file);
            $names[] = $path_parts['filename'];
        }
        $desc .= $desc . "<code>" . implode("</code> or <code>", $names) . "</code>";
        
        $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/epg.php';
        $desc .= "<br>crontab for auto generate cache for EPG links <code>0 * * * * php {$dir}</code>";
        
        return $desc;
    }

    public function getName() {
        return "PlayerSkins";
    }

    public function getUUID() {
        return "e9a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->skin = "avideo";
        $obj->playbackRates = "[0.5, 1, 1.5, 2]";
        $obj->playerCustomDataSetup = "";
        $obj->showSocialShareOnEmbed = true;
        $obj->showLoopButton = true;
        $obj->showLogo = false;
        $obj->showShareSocial = true;
        $obj->showShareAutoplay = true;
        $obj->forceAlwaysAutoplay = false;
        $obj->showLogoOnEmbed = false;
        $obj->showLogoAdjustScale = "0.4";
        $obj->showLogoAdjustLeft = "-74px";
        $obj->showLogoAdjustTop = "-22px;";
        $obj->disableEmbedTopInfo = false;
        $obj->contextMenuDisableEmbedOnly = false;
        $obj->contextMenuLoop = true;
        $obj->contextMenuCopyVideoURL = true;
        $obj->contextMenuCopyVideoURLCurrentTime = true;
        $obj->contextMenuCopyEmbedCode = true;
        $obj->contextMenuShare = true;
        $obj->playerFullHeight = false;
        $obj->playsinline = true;

        return $obj;
    }
    
    static function getPlaysinline(){
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        if($obj->playsinline){
            return ' playsinline webkit-playsinline="webkit-playsinline" ';
        }
        return '';
    }

    static function getMediaTag($filename, $htmlMediaTag = false) {
        global $autoPlayURL, $global, $config, $isVideoTypeEmbed, $advancedCustom;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        $html = '';
        if (empty($htmlMediaTag)) {
            $video = Video::getVideoFromFileName($filename, true);
            $vType = Video::getIncludeType($video);
            $_GET['isMediaPlaySite'] = $video['id'];
            if (is_object($video['externalOptions'])) {
                if (!empty($video['externalOptions']->videoStartSeconds)) {
                    $video['externalOptions']->videoStartSeconds = parseDurationToSeconds($video['externalOptions']->videoStartSeconds);
                } else {
                    $video['externalOptions']->videoStartSeconds = 0;
                }
            } else {
                //_error_log('externalOptions Error '.$video['externalOptions'], AVideoLog::$WARNING);
                $video['externalOptions'] = new stdClass();
                $video['externalOptions']->videoStartSeconds = 0;
            }
            $images = Video::getImageFromFilename($filename);
            if ($vType == 'video') {
                $htmlMediaTag = '<video '.self::getPlaysinline()
                        . 'preload="auto" poster="' . $images->poster . '" controls 
                        class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered vjs-16-9" id="mainVideo">';
                if ($video['type'] == "video") {
                    $htmlMediaTag .= "<!-- Video {$video['title']} {$video['filename']} -->" . getSources($video['filename']);
                } else { // video link
                    $htmlMediaTag .= "<!-- Video Link {$video['title']} {$video['filename']} --><source src='{$video['videoLink']}' type='" . ((strpos($video['videoLink'], 'm3u8') !== false) ? "application/x-mpegURL" : "video/mp4") . "' >";
                    $html .= "<script>$(document).ready(function () {\$('time.duration').hide();});</script>";
                }
                /*
                  if (AVideoPlugin::isEnabledByName('SubtitleSwitcher') && function_exists('getVTTTracks')) {
                  $htmlMediaTag .= "<!-- getVTTTracks 1 -->";
                  $htmlMediaTag .= getVTTTracks($video['filename']);
                  }else{
                  if(!AVideoPlugin::isEnabledByName('SubtitleSwitcher')){
                  $htmlMediaTag .= "<!-- SubtitleSwitcher disabled -->";
                  }
                  if(!function_exists('getVTTTracks')){
                  $htmlMediaTag .= "<!-- getVTTTracks not found -->";
                  }
                  }
                 * 
                 */
                $htmlMediaTag .= '<p>' . __("If you can't view this video, your browser does not support HTML5 videos") . '</p><p class="vjs-no-js">' . __("To view this video please enable JavaScript, and consider upgrading to a web browser that") . '<a href="http://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer">supports HTML5 video</a></p></video>';
            } else if ($vType == 'audio') {
                $htmlMediaTag = '<audio '.self::getPlaysinline().'
                       preload="auto"
                       poster="' . $images->poster . '" controls class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" id="mainVideo">';
                if ($video['type'] == "audio" || Video::forceAudio()) {
                    $htmlMediaTag .= "<!-- Audio {$video['title']} {$video['filename']} -->" . getSources($video['filename']);
                } else { // audio link
                    if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".ogg")) {
                        $type = "audio/ogg";
                    } else {
                        $type = "audio/mpeg";
                    }
                    $htmlMediaTag .= "<!-- Audio Link {$video['title']} {$video['filename']} --><source src='{$video['audioLink']}' type='" . $type . "' >";
                    $html .= "<script>$(document).ready(function () {\$('time.duration').hide();});</script>";
                }
                $htmlMediaTag .= '</audio>';
            } else if ($vType == 'embed') {
                $disableYoutubeIntegration = false;
                if (!empty($advancedCustom->disableYoutubePlayerIntegration) || isMobile()) {
                    $disableYoutubeIntegration = true;
                }
                $_GET['isEmbedded'] = "";
                if (
                    ($disableYoutubeIntegration) 
                    || 
                    (
                        (strpos($video['videoLink'], "youtu.be") == false) 
                        && (strpos($video['videoLink'], "youtube.com") == false) 
                        //&& (strpos($video['videoLink'], "vimeo.com") == false)
                    )
                    ) {
                    $_GET['isEmbedded'] = "e";
                    $isVideoTypeEmbed = 1;
                    $url = parseVideos($video['videoLink']);
                    if ($config->getAutoplay()) {
                        $url = addQueryStringParameter($url, 'autoplay', 1);
                    }
                    $htmlMediaTag = "<!-- Embed Link 1 {$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= '<video '.self::getPlaysinline().' id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
                    $htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
                    $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" '.Video::$iframeAllowAttributes.' src="' . $url . '"></iframe>';
                    $htmlMediaTag .= '<script>$(document).ready(function () {addView(' . intval($video['id']) . ', 0);});</script>';
                    $htmlMediaTag .= '</div>';
                } else {
                    // youtube!
                    if ((stripos($video['videoLink'], "youtube.com") != false) || (stripos($video['videoLink'], "youtu.be") != false)) {
                        $_GET['isEmbedded'] = "y";
                    } else if ((stripos($video['videoLink'], "vimeo.com") != false)) {
                        $_GET['isEmbedded'] = "v";
                    }
                    $_GET['isMediaPlaySite'] = $video['id'];
                    PlayerSkins::playerJSCodeOnLoad($video['id'], @$video['url']);
                    $htmlMediaTag = "<!-- Embed Link 2 YoutubeIntegration {$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= '<video '.self::getPlaysinline().' id="mainVideo" class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" controls></video>';
                    $htmlMediaTag .= '<script>var player;mediaId = ' . $video['id'] . ';$(document).ready(function () {$(".vjs-control-bar").css("opacity: 1; visibility: visible;");});</script>';
                }
            } else if ($vType == 'serie') {
                $isVideoTypeEmbed = 1;
                $link = "{$global['webSiteRootURL']}plugin/PlayLists/embed.php";
                $link = addQueryStringParameter($link, 'playlists_id', $video['serie_playlists_id']);
                $link = addQueryStringParameter($link, 'autoplay', $config->getAutoplay());
                $link = addQueryStringParameter($link, 'playlist_index', @$_REQUEST['playlist_index']);

                $htmlMediaTag = "<!-- Serie {$video['title']} {$video['filename']} -->";
                $htmlMediaTag .= '<video '.self::getPlaysinline().' id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
                $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" '.Video::$iframeAllowAttributes.' src="' . $link . '"></iframe>';
                $htmlMediaTag .= '<script>$(document).ready(function () {addView(' . intval($video['id']) . ', 0);});</script>';
            }

            $html .= "<script>mediaId = '{$video['id']}';var player;" . self::playerJSCodeOnLoad($video['id'], @$autoPlayURL) . '</script>';
        }

        $col1Classes = 'col-md-2 firstC';
        $col2Classes = 'col-md-8 secC';
        $col3Classes = 'col-md-2 thirdC';
        if ($obj->playerFullHeight) {
            $col2Classes .= ' text-center playerFullHeight';
        }

        $html .= '
<div class="row main-video" id="mvideo">
    <div class="' . $col1Classes . '"></div>
    <div class="' . $col2Classes . '">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs"
                        onclick="closeFloatVideo(); floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">' . $htmlMediaTag . '</div>';

        $html .= showCloseButton() . '</div></div><div class="' . $col3Classes . '"></div></div>';

        return $html;
    }

    public function getHeadCode() {
        if (isWebRTC()) {
            return '';
        }
        global $global, $config, $video;
        $obj = $this->getDataObject();
        $css = "";
        $js = "";
        $js .= "<script>var _adWasPlayed = 0;</script>";
        if (isLive()) {
            $js .= "<script>var isLive = true;</script>";
        }
        if (isVideo() || !empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            if (!empty($_REQUEST['autoplay']) || !empty($obj->forceAlwaysAutoplay)) {
                $js .= "<script>var autoplay = true;var forceautoplay = true;</script>";
            } else if (self::isAutoplayEnabled()) {
                $js .= "<script>var autoplay = true;</script>";
            } else {
                $js .= "<script>var autoplay = false;</script>";
            }
            $js .= "<script>var playNextURL = '';</script>";
            if (!empty($obj->skin)) {
                $url = "plugin/PlayerSkins/skins/{$obj->skin}.css";
                $css .= "<link href=\"" . getURL($url) . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if ($obj->showLoopButton && isVideoPlayerHasProgressBar()) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/loopbutton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            $css .= "<link href=\"" . getURL('plugin/PlayerSkins/player.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            $css .= "<script src=\"" . getURL('plugin/PlayerSkins/player.js') . "\"></script>";
            if ($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo) {
                $logo = "{$global['webSiteRootURL']}" . $config->getLogo(true);
                $css .= "<style>"
                        . ".player-logo{
  outline: none;
  filter: grayscale(100%);
  width:100px !important;
}
.player-logo:hover{
  filter: none;
  -webkit-filter: drop-shadow(1px 1px 1px rgba(255, 255, 255, 0.5));
  filter: drop-shadow(1px 1px 1px rgba(255, 255, 255, 0.5));
}
.player-logo:before {
    display: inline-block;
    content: url({$logo});
    transform: scale({$obj->showLogoAdjustScale});
  position: relative;
  left:{$obj->showLogoAdjustLeft};
  top:{$obj->showLogoAdjustTop};
    
}"
                        . "</style>";
            }

            if ($obj->showShareSocial && CustomizeUser::canShareVideosFromVideo(@$video['id'])) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/shareButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if ($obj->showShareAutoplay && isVideoPlayerHasProgressBar() && empty($obj->forceAlwaysAutoplay) && empty($_REQUEST['hideAutoplaySwitch'])) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/autoplayButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
        }
        $videos_id = getVideos_id();
        if (!empty($videos_id) && Video::getEPG($videos_id)) {
            $css .= "<link href=\"" . getURL('plugin/PlayerSkins/epgButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
        }

        $url = urlencode(getSelfURI());
        $oembed = '<link href="' . getCDN() . 'oembed/?format=json&url=' . $url . '" rel="alternate" type="application/json+oembed" />';
        $oembed .= '<link href="' . getCDN() . 'oembed/?format=xml&url=' . $url . '" rel="alternate" type="application/xml+oembed" />';

        return $js . $css . $oembed;
    }

    public function getFooterCode() {
        if (isWebRTC()) {
            return '';
        }
        global $global, $config, $getStartPlayerJSWasRequested, $video, $url, $title;
        $js = "<!-- playerSkin -->";
        $obj = $this->getDataObject();
        if (!empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            if (empty($obj->showLoopButton) && empty($obj->contextMenuLoop)) {
                $js .= "<script>setPlayerLoop(false);</script>";
            }
            if ($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo) {
                $title = $config->getWebSiteTitle();
                //$url = "{$global['webSiteRootURL']}{$config->getLogo(true)}";
                $js .= "<script>var PlayerSkinLogoTitle = '{$title}';</script>";
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/logo.js"));
                //$js .= "<script src=\"".getCDN()."plugin/PlayerSkins/logo.js\"></script>";
            }

            if ($obj->showShareSocial && CustomizeUser::canShareVideosFromVideo(@$video['id'])) {
                $social = getSocialModal(@$video['id'], @$url, @$title);
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/shareButton.js"));
                //$js .= "<script src=\"".getCDN()."plugin/PlayerSkins/shareButton.js\"></script>";
                $js .= $social['html'];
                $js .= "<script>function tooglePlayersocial(){showSharing{$social['id']}();}</script>";
            }

            if (!isLive() && $obj->showShareAutoplay && isVideoPlayerHasProgressBar() && empty($obj->forceAlwaysAutoplay) && empty($_REQUEST['hideAutoplaySwitch'])) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/autoplayButton.js"));
            } else {
                if (isLive()) {
                    $js .= "<!-- PlayerSkins is live, do not show autoplay -->";
                }
                if ($obj->showShareAutoplay) {
                    $js .= "<!-- PlayerSkins showShareAutoplay -->";
                }
                if (isVideoPlayerHasProgressBar()) {
                    $js .= "<!-- PlayerSkins isVideoPlayerHasProgressBar -->";
                }
                if (empty($obj->forceAlwaysAutoplay)) {
                    $js .= "<!-- PlayerSkins empty(\$obj->forceAlwaysAutoplay) -->";
                }
                if (empty($_REQUEST['hideAutoplaySwitch'])) {
                    $js .= "<!-- PlayerSkins empty(\$_REQUEST['hideAutoplaySwitch']) -->";
                }
            }
            $videos_id = getVideos_id();
            
            $event = "updateMediaSessionMetadata();"; 
            PlayerSkins::getStartPlayerJS($event);
            if (!empty($videos_id) && Video::getEPG($videos_id)) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/epgButton.js"));
            }
        }
        if (isAudio()) {
            $videos_id = getVideos_id();
            $video = Video::getVideoLight($videos_id);
            $spectrumSource = Video::getSourceFile($video['filename'], "_spectrum.jpg");
            if(empty($spectrumSource["path"])){
                if(AVideoPlugin::isEnabledByName('MP4ThumbsAndGif') && method_exists('MP4ThumbsAndGif', 'getSpectrum')){
                    if(MP4ThumbsAndGif::getSpectrum($videos_id)){
                        $spectrumSource = Video::getSourceFile($video['filename'], "_spectrum.jpg");
                    }
                }
            }
            if (!empty($spectrumSource["path"])) {
                $onPlayerReady = "startAudioSpectrumProgress('{$spectrumSource["url"]}');";
                self::prepareStartPlayerJS($onPlayerReady);
            }
        }
        if (empty($global['doNotLoadPlayer']) && !empty($getStartPlayerJSWasRequested) || isVideo()) {
            $js .= "<script src=\"" . getURL('view/js/videojs-persistvolume/videojs.persistvolume.js') . "\"></script>";
            $js .= "<script>" . self::getStartPlayerJSCode() . "</script>";
        }

        include $global['systemRootPath'] . 'plugin/PlayerSkins/mediaSession.php';
        PlayerSkins::addOnPlayerReady('if(typeof updateMediaSessionMetadata === "function"){updateMediaSessionMetadata();}');

        if (self::$hasMarks) {
            $js .= '<link href="' . getURL('plugin/AD_Server/videojs-markers/videojs.markers.css') . '" rel="stylesheet" type="text/css"/>';
            $js .= '<script src="' . getURL('plugin/AD_Server/videojs-markers/videojs-markers.js') . '"></script>';
        }

        return $js;
    }

    static function getDataSetup($str = "") {
        global $video, $disableYoutubeIntegration, $global;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');

        $dataSetup = array();

        //$dataSetup[] = "inactivityTimeout: 0";
        $dataSetup[] = "errorDisplay: false";
        if (isVideoPlayerHasProgressBar() && !empty($obj->playbackRates)) {
            $dataSetup[] = "'playbackRates':{$obj->playbackRates}";
        }
        if (isVideoPlayerHasProgressBar() && (isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false) && !empty($video['videoLink'])) {
            if ($_GET['isEmbedded'] == "y") {
                $dataSetup[] = "techOrder:[\"youtube\"]";
                $dataSetup[] = "sources:[{type: \"video/youtube\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "youtube:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            } else if ($_GET['isEmbedded'] == "v") {
                $dataSetup[] = "techOrder:[\"vimeo\"]";
                $dataSetup[] = "sources:[{type: \"video/vimeo\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "vimeo:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            }
        }

        $pluginsDataSetup = AVideoPlugin::dataSetup();
        if (!empty($pluginsDataSetup)) {
            $dataSetup[] = $pluginsDataSetup;
        }
        if (!empty($dataSetup)) {
            return ",{" . implode(",", $dataSetup) . "{$str}{$obj->playerCustomDataSetup}}";
        }

        return "";
    }

    // this function was modified, maybe removed in the future
    static function getStartPlayerJS($onPlayerReady = "", $getDataSetup = "", $noReadyFunction = false) {
        global $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup;
        global $getStartPlayerJSWasRequested;
        self::prepareStartPlayerJS($onPlayerReady, $getDataSetup);
        //var_dump('getStartPlayerJSWasRequested', debug_backtrace());
        $getStartPlayerJSWasRequested = true;
        //return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '", $onPlayerReady = "' . $onPlayerReady . '", $getDataSetup = "' . $getDataSetup . '" */';
        return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '" */';
    }

    static function addOnPlayerReady($onPlayerReady) {
        return self::getStartPlayerJS($onPlayerReady);
    }

    static function getStartPlayerJSCode($noReadyFunction = false, $currentTime = 0) {
        if (isWebRTC()) {
            return '';
        }
        global $config, $global, $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup, $IMAADTag;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        $js = "";
        if (empty($currentTime) && isVideoPlayerHasProgressBar()) {
            $currentTime = self::getCurrentTime();
        }

        if (!empty($global['doNotLoadPlayer'])) {
            return '';
        }

        if (empty($prepareStartPlayerJS_onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady = array();
        }
        if (empty($prepareStartPlayerJS_getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup = array();
        }
        if (empty($noReadyFunction)) {
            $js .= "var originalVideo;
                var adTagOptions;
            var _adTagUrl = '{$IMAADTag}'; var player; "
                    . "$(document).ready(function () {";
        }
        $js .= "
        originalVideo = $('#mainVideo').clone();
        /* prepareStartPlayerJS_onPlayerReady = " . count($prepareStartPlayerJS_onPlayerReady) . ", prepareStartPlayerJS_getDataSetup = " . count($prepareStartPlayerJS_getDataSetup) . " */
        if (typeof player === 'undefined' && $('#mainVideo').length) {
            player = videojs('mainVideo'" . (self::getDataSetup(implode(" ", $prepareStartPlayerJS_getDataSetup))) . ");
            ";
        if (!empty($IMAADTag) && isVideoPlayerHasProgressBar()) {
            $js .= "adTagOptions = {"
                    . "id: 'mainVideo', "
                    . "adTagUrl: '{$IMAADTag}', "
                    . "debug: true, "
                    . "/*useStyledLinearAds: false,*/"
                    . "/*useStyledNonLinearAds: true,*/"
                    . "forceNonLinearFullSlot: true, "
                    . "/*adLabel: 'Advertisement',*/ "
                    . "/*autoPlayAdBreaks:false,*/"
                    . "}; "
                    . "player.ima(adTagOptions);";
            $js .= "setInterval(function(){ fixAdSize(); }, 300);
                // first time it's clicked.
                var startEvent = 'click';";
            if (isMobile()) {
                $js .= "// Remove controls from the player on iPad to stop native controls from stealing
                // our click
                var contentPlayer = document.getElementById('content_video_html5_api');
                if (contentPlayer && (navigator.userAgent.match(/iPad/i) ||
                        navigator.userAgent.match(/Android/i)) &&
                        contentPlayer.hasAttribute('controls')) {
                    contentPlayer.removeAttribute('controls');
                }

                // Initialize the ad container when the video player is clicked, but only the
                if (navigator.userAgent.match(/iPhone/i) ||
                        navigator.userAgent.match(/iPad/i) ||
                        navigator.userAgent.match(/Android/i)) {
                    startEvent = 'touchend';
                }";
            }

            $js .= "
                player.on('adsready', function () {
                    console.log('adsready');
                        player.ima.setAdBreakReadyListener(function(e) {
                            if(!_adWasPlayed){
                                console.log('ADs !_adWasPlayed player.ima.playAdBreak();',e);
                                //player.ima.requestAds();
                                player.on('play', function () {
                                    if(!_adWasPlayed){
                                        player.ima.playAdBreak();
                                        _adWasPlayed = 1;
                                    }
                                });
                            }else{
                                console.log('ADs _adWasPlayed player.ima.playAdBreak();',e);
                                player.ima.playAdBreak();
                            }
                        });
                });player.on('ads-ad-started', function () {
                    console.log('ads-ad-started');
                });player.on('ads-manager', function (a) {
                    console.log('ads-manager', a);
                });player.on('ads-loader', function (a) {
                    console.log('ads-loader', a);
                });player.on('ads-request', function (a) {
                    console.log('ads-request', a);
                });player.one(startEvent, function () {player.ima.initializeAdDisplayContainer();});";
        }

        $js .= "}
        player.ready(function () {";

        $js .= "player.on('error', () => {
            AvideoJSError(player.error().code);
        });";

        // this is here because for some reason videos on the storage only works if it loads dinamically on android devices only
        if (isMobile()) {
            $js .= "player.src(player.currentSources());";
        }
        if (empty($_REQUEST['mute'])) {
            $play = "playerPlayIfAutoPlay({$currentTime});";

            $js .= "
            player.persistvolume({
                namespace: 'AVideo'
            });";
        } else {
            $play = "player.volume(0);player.muted(true);playerPlayMutedIfAutoPlay({$currentTime});";
        }

        $js .= "var err = this.error();
            if (err && err.code) {
                $('.vjs-error-display').hide();
                $('#mainVideo').find('.vjs-poster').css({'background-image': 'url({$global['webSiteRootURL']}plugin/Live/view/Offline.jpg)'});
            }
            " . implode(PHP_EOL, $prepareStartPlayerJS_onPlayerReady) . "
            {$play}
        });";

        if ($obj->showLoopButton && isVideoPlayerHasProgressBar()) {
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/loopbutton.js');
        }


        $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/fixCurrentSources.js');
        if (empty($noReadyFunction)) {
            $js .= "});";
        }
        //var_dump('getStartPlayerJSWasRequested', debug_backtrace());
        $getStartPlayerJSWasRequested = true;
        return $js;
    }

    static private function getCurrentTime() {
        $currentTime = 0;
        if (isset($_GET['t'])) {
            $currentTime = intval($_GET['t']);
        } else {
            $videos_id = getVideos_id();
            if (!empty($videos_id)) {
                $video = Video::getVideoLight($videos_id);
                if(!empty($video)){
                    $progress = Video::getVideoPogressPercent($videos_id);
                    if (!empty($progress) && !empty($progress['lastVideoTime'])) {
                        $currentTime = intval($progress['lastVideoTime']);
                    } else if (!empty($video['externalOptions'])) {
                        $json = _json_decode($video['externalOptions']);
                        if (!empty($json->videoStartSeconds)) {
                            $currentTime = intval(parseDurationToSeconds($json->videoStartSeconds));
                        } else {
                            $currentTime = 0;
                        }
                    }
                    $maxCurrentTime = parseDurationToSeconds($video['duration']);
                    if ($maxCurrentTime <= $currentTime + 5) {
                        $currentTime = 0;
                    }
                }else{
                    return 0;
                }
            }
        }
        return $currentTime;
    }

    static function setIMAADTag($tag) {
        global $IMAADTag;
        $IMAADTag = $tag;
    }

    static function playerJSCodeOnLoad($videos_id, $nextURL = "") {
        $js = "";
        $videos_id = intval($videos_id);
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video("", "", $videos_id);
        if (!empty($video) && empty($nextURL)) {
            if (!empty($video->getNext_videos_id())) {
                $next_video = Video::getVideo($video->getNext_videos_id());
                if (!empty($next_video['id'])) {
                    $nextURL = Video::getURLFriendly($next_video['id'], isEmbed());
                }
            } else {
                $catName = @$_REQUEST['catName'];
                $cat = new Category($video->getCategories_id());
                $_REQUEST['catName'] = $cat->getClean_name();
                $next_video = Video::getVideo('', 'viewable', false, true);
                $_REQUEST['catName'] = $catName;
                if (!empty($next_video['id'])) {
                    $nextURL = Video::getURLFriendly($next_video['id'], isEmbed());
                }
            }
        }
        $url = Video::getURLFriendly($videos_id);
        $js .= "
        player.on('play', function () {
            addView({$videos_id}, this.currentTime());
            _addViewBeaconAdded = false;
        });
        player.on('timeupdate', function () {
            var time = Math.round(this.currentTime());
            playerCurrentTime = time;
            var url = '{$url}';
            if (url.indexOf('?') > -1) {
            url += '&t=' + time;
            } else {
            url += '?t=' + time;
            }
            $('#linkCurrentTime, .linkCurrentTime').val(url);
            if (time >= 5 && time % 1 === 0) {
                addView({$videos_id}, time);
            }else{
                addViewFromCookie();
                addViewSetCookie(PHPSESSID, {$videos_id}, time, seconds_watching_video);
            }
        });
        player.on('ended', function () {
            var time = Math.round(this.currentTime());
            addView({$videos_id}, time);
        });";

        if (!empty($nextURL)) {
            $js .= "playNextURL = '{$nextURL}';";
            $js .= "player.on('ended', function () {setTimeout(function(){if(playNextURL){playNext(playNextURL);}},playerHasAds()?10000:500);});";
        }
        self::getStartPlayerJS($js);
        return true;
    }

    static private function prepareStartPlayerJS($onPlayerReady = "", $getDataSetup = "") {
        global $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup;

        if (empty($prepareStartPlayerJS_onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady = array();
        }
        if (empty($prepareStartPlayerJS_getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup = array();
        }

        if (!empty($onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady[] = $onPlayerReady;
        }
        if (!empty($getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup[] = $getDataSetup;
        }
    }

    static function isAutoplayEnabled() {
        global $config;
        if (isLive()) {
            return true;
        }
        if (!empty($_COOKIE['autoplay'])) {
            if (strtolower($_COOKIE['autoplay']) === 'false') {
                return false;
            } else {
                return true;
            }
        }
        return $config->getAutoplay();
    }

    public static function getVideoTags($videos_id) {
        if (empty($videos_id)) {
            return array();
        }
        $name = "PlayeSkins_getVideoTags{$videos_id}";
        $tags = ObjectYPT::getCache($name, 0);
        if (empty($tags)) {
            //_error_log("Cache not found $name");
            $video = new Video("", "", $videos_id);
            $fileName = $video->getFilename();
            //_error_log("getVideoTags($videos_id) $fileName ".$video->getType());
            $resolution = Video::getHigestResolution($fileName);
            $obj = new stdClass();
            if (empty($resolution) || empty($resolution['resolution_text'])) {
                $obj->label = '';
                $obj->type = "";
                $obj->text = "";
            } else {
                $obj->label = 'Plugin';
                $obj->type = "danger";
                $obj->text = $resolution['resolution_text'];
                $obj->tooltip = $resolution['resolution'] . 'p';
            }
            $tags = $obj;
            ObjectYPT::setCache($name, $tags);
        }
        return array($tags);
    }

    /**
     * 
     * @param array $markersList array(array('timeInSeconds'=>10,'name'=>'abc'),array('timeInSeconds'=>20,'name'=>'abc20'),array('timeInSeconds'=>25,'name'=>'abc25')....);
     * @param int $width
     * @param string $color
     */
    public static function createMarker($markersList, $width = 10, $color = 'yellow') {
        global $global;

        $bt = debug_backtrace();
        $file = str_replace($global['systemRootPath'], '', $bt[0]['file']);
        $onPlayerReady = '';
        $onPlayerReady .= " /* {$file} */
                player.markers({markerStyle: {
                    'width': '{$width}px',
                    'background-color': '{$color}'
                },
                markerTip: {
                    display: true,
                    text: function (marker) {
                        return marker.text;
                    }
                },
                markers: ";
        $markers = array();
        $addedSomething = false;
        foreach ($markersList as $value) {
            $obj = new stdClass();
            $obj->time = $value['timeInSeconds'];
            $obj->text = $value['name'];
            if (empty($obj->text)) {
                continue;
            }
            $addedSomething = true;
            $markers[] = $obj;
        }

        $onPlayerReady .= json_encode($markers);
        $onPlayerReady .= "});";
        if ($addedSomething) {
            self::$hasMarks = true;
            PlayerSkins::getStartPlayerJS($onPlayerReady);
        }
    }
    
    
    
    public function getWatchActionButton($videos_id) {
        global $global, $video;
        include $global['systemRootPath'] . 'plugin/PlayerSkins/actionButton.php';
    }
    
    public function getGalleryActionButton($videos_id) {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayerSkins/actionButtonGallery.php';
    }

}
