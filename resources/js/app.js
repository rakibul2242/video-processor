import videojs from 'video.js';
import 'video.js/dist/video-js.css';

const player = videojs('videoPlayer');

function playHls(url) {
    player.src({
        src: url,
        type: 'application/x-mpegURL'
    });

    player.load();
    player.play();
}

window.playHls = playHls;