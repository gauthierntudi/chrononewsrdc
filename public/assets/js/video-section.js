// Load YouTube IFrame API asynchronously
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
// Playlist videos
let videoPlaylist = [
    'bjby6F5Qogk', // Tutoriel illico cash
    'RHUU0v_gzpU', // Carte virtuelle MPesa Visa
    'kbCA4SlizsI'  // Comment effectuer un retrait GAB
];

// Si une liste de vidéos est configurée via PHP, on l'utilise en priorité
if (window.homeVideoIds && Array.isArray(window.homeVideoIds) && window.homeVideoIds.length > 0) {
    videoPlaylist = window.homeVideoIds;
} else if (window.homeVideoId) {
    // Rétrocompatibilité (au cas où)
    videoPlaylist = [window.homeVideoId];
}

let currentVideoIndex = 0;
let isFloatingClosed = false; // Flag to check if user manually closed the floating player

function onYouTubeIframeAPIReady() {
    player = new YT.Player('youtube-player', {
        height: '100%',
        width: '100%',
        videoId: videoPlaylist[currentVideoIndex],
        playerVars: {
            'playsinline': 1,
            'autoplay': 1,
            'mute': 1,
            'rel': 0, // Don't show related videos from other channels
            'controls': 1,
            'loop': 1, // Enable native loop
            'playlist': videoPlaylist.join(',') // Required for loop to work (even with single video)
        },
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerReady(event) {
    // Attempt to play and mute (autoplay policy usually requires mute)
    event.target.mute();
    event.target.playVideo();
    
    // Initialize Scroll Listener for Floating Video
    initFloatingVideo();
}

function onPlayerStateChange(event) {
    // When video ends (state=0)
    // Avec loop=1 et playlist définis, YouTube gère la boucle nativement.
    // On met juste à jour l'index si besoin pour info
    if (event.data === YT.PlayerState.ENDED) {
        // Logique manuelle désactivée pour laisser la priorité à la boucle native
        // currentVideoIndex++;
        // if (currentVideoIndex >= videoPlaylist.length) currentVideoIndex = 0;
        // player.loadVideoById(videoPlaylist[currentVideoIndex]);
    }
}

// Floating Video Logic - Timer Based (Restored)
let videoOriginalParent = null;
let videoOriginalNextSibling = null;
let videoElement = null;
let sectionWrapper = null;

function initFloatingVideo() {
    // Initialisation des références DOM
    if (!videoElement) {
        videoElement = document.querySelector('.main-video-area');
    }
    
    if (!sectionWrapper) {
        // Le wrapper global qui contient toute la section (titre + vidéo)
        sectionWrapper = document.getElementById('video-section-wrapper');
    }
    
    if (videoElement && videoElement.parentElement && videoElement.parentElement.tagName !== 'BODY') {
        videoOriginalParent = videoElement.parentElement;
        videoOriginalNextSibling = videoElement.nextSibling;
    }

    // Démarrer le timer si demandé (ou attendre appel externe)
    // Ici on ATTEND l'appel externe depuis index.php (gestion modale)
    // startFloatingVideoTimer();
}

let isTimerStarted = false;

// Nouvelle fonction pour déclencher la vidéo flottante après 5 secondes
window.startFloatingVideoTimer = function() {
    if (isFloatingClosed || isTimerStarted) return;
    
    isTimerStarted = true;
    console.log("Starting floating video timer (5s)...");
    setTimeout(() => {
        if (!isFloatingClosed) {
            activateFloatingVideo();
        }
    }, 5000); // 5 secondes
};

function activateFloatingVideo() {
    if (!videoElement) return;
    if (document.body.classList.contains('is-floating-video')) return; // Déjà actif
    
    // Déplacer vers body pour éviter les contraintes de layout
    document.body.appendChild(videoElement);
    document.body.classList.add('is-floating-video');
    
    // Si on veut cacher le wrapper original (laissant un trou ou non)
    sectionWrapper.style.display = 'none'; 
    
    console.log("Floating video activated and moved to body.");
}

// Global function to toggle/close floating video
window.toggleFloatingVideo = function(forceClose) {
    if (forceClose === false) {
        // Désactiver le mode flottant
        document.body.classList.remove('is-floating-video');
        isFloatingClosed = true; // User manually closed it
        
        // Restaurer la vidéo à sa place
        if (videoElement && videoOriginalParent) {
            if (videoOriginalNextSibling) {
                videoOriginalParent.insertBefore(videoElement, videoOriginalNextSibling);
            } else {
                videoOriginalParent.appendChild(videoElement);
            }
        }
        
        // Réafficher le conteneur original si caché
        if (sectionWrapper) {
            sectionWrapper.style.display = ''; // Revenir au style CSS par défaut
        }
    }
};
