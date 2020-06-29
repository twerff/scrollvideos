<?php
/**
* Plugin Name: Scrollvideos
* Version: 1.0.1
* Plugin URI: http://www.twerff.nl/scrollvideos-plugin
* Description: Make videos on your website play automatically as you scroll past them. It is awesome!
* Author: Thomas van de Werff
* Author URI: http://www.twerff.nl/
**/


add_action( 'wp_head', function () { ?>
	<style>
		.scrollvideo {
			position:relative;
		}
		.scrollvideo .scrolltip{
			position:absolute;
			top:0px;

			width:calc(100% - 20px);
			border-radius:25px;
			margin:10px;
			padding:10px;

			color:white;
			text-align:center;
			background:#00000055;

			transition: opacity 0.5s ease-in-out;
		}
	</style>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
 	<?php
});

add_action( 'wp_footer', function () { ?>

	<script>
		var scrollVideos = [];
		var videoLengths = [];
		var scrolltipTimeoutHandle;
	
		var debug = true;
		function echo(string){
			if(debug) console.log("viewport.js: " + string);
		}

		window.addEventListener('DOMContentLoaded', initScrollVideoScript, false);

		function initScrollVideoScript(){
			//check for scrollvideos
			scrollVideos = document.getElementsByClassName('scrollvideo');
			echo("found " + scrollVideos.length + " scrollvideos");
			
			//stop script if there are none
			if(scrollVideos.length == 0) return;
			
			//get scrollvideo lengths
			for (var i = 0; i< scrollVideos.length ;i++) {
				getScrollVideoLengthByIndex(i);
				removeVideoControls(scrollVideos[i]);
				addTooltipToScrollVideo(scrollVideos[i]);
			}
						
			window.addEventListener('scroll', checkScrollVideo, false);
			window.addEventListener('resize', checkScrollVideo, false);
		}
		
		function getScrollVideoLengthByIndex(i){
			var video = getVideoElementFrom(scrollVideos[i]);
			videoLengths.push(0);

			let promise = new Promise(function(resolve, reject) {
				video.addEventListener("loadedmetadata", function() {
					resolve(video.duration);
					videoLengths[i] = video.duration;
					echo(i + " " + videoLengths[i]);
				});
				video.addEventListener("error", function() {
					reject(video.error.message + "(" + video.error.code + ")");
					echo(video.error.code);
				});
			});			
		}
		
		function getVideoElementFrom(el) {
			return el.getElementsByTagName("video")[0];
		}
		
		function removeVideoControls(v){
			try {
				getVideoElementFrom(v).removeAttribute("controls");
			}
			catch (error) {};
		}
		
		function addTooltipToScrollVideo(v){
			var scrolltip = document.createElement("div");
			scrolltip.innerHTML = "Scroll to play the video";
			scrolltip.className += "scrolltip";
			v.appendChild(scrolltip);
		}
		
		function checkScrollVideo(){
			for (var i = 0; i<scrollVideos.length; i++){
				el = scrollVideos[i];
				var rect = el.getBoundingClientRect();

				if (isElementInViewport(el)){
					setScrollVideoFrame(el, rect, i);
					hideTooltip(el);
				}
			}
		}
		
		function hideTooltip(el){
			var scrolltip = el.getElementsByClassName("scrolltip")[0];
			scrolltip.style.opacity = "0";

			window.clearTimeout(scrolltipTimeoutHandle);
			scrolltipTimeoutHandle = window.setTimeout(function() {
				scrolltip.style.opacity = "1";
			}, 1000);
		}
		
		function setScrollVideoFrame(el, rect, i){
			var videoPercentage = 1 - (rect.top / (window.innerHeight-el.offsetHeight));
			var frameNumber = videoPercentage * videoLengths[i];
							
			window.requestAnimationFrame(function(){
				getVideoElementFrom(el).currentTime = frameNumber;
			});
		}
		
		function isElementInViewport(el) {
			var rect = el.getBoundingClientRect();
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document. documentElement.clientHeight) &&
				rect.right <= (window.innerWidth || document. documentElement.clientWidth)
			);
		}
	
	</script>

<?php
});