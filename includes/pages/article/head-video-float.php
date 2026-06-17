<style>
#main-youtube-video.floating {
    position: fixed !important;
    bottom: 20px;
    right: 20px;
    width: 400px !important;
    height: 225px !important;
    z-index: 9999;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    border-radius: 12px;
    transition: all 0.3s ease;
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

#video-close-btn {
    display: none;
    position: fixed;
    z-index: 10000;
    width: 32px!important;
    height: 32px!important;
    background: rgba(0,0,0,0.8);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background 0.2s;
}

#video-close-btn.show {
    display: flex;
    bottom: calc(20px + 225px - 12px);
    right: 30px;
}

#video-close-btn:hover {
    background: rgba(255,0,0,0.9);
}

@media (max-width: 768px) {
    #main-youtube-video.floating {
        width: calc(100vw - 40px) !important;
        height: calc((100vw - 40px) * 0.5625) !important;
        right: 20px;
        bottom: 20px;
    }

    #video-close-btn.show {
        bottom: calc(20px + ((100vw - 40px) * 0.5625) - 42px);
    }
}
</style>
