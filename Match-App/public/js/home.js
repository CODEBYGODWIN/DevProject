window.homeInitialized = false;
window.homeInitializationInProgress = false;

function initHome() {
    if (window.homeInitialized || window.homeInitializationInProgress) {
        return;
    }
    
    console.log('Initializing home...');
    window.homeInitializationInProgress = true;
    
    if (window.homeEventSource) {
        window.homeEventSource.close();
        window.homeEventSource = null;
    }
    
    const currentUserId = homeData.currentUserId;
    const mercureUrl = new URL(homeData.mercurePublicUrl);
    
    if (homeData.chats.length > 0) {
        homeData.chats.forEach(chatId => {
            mercureUrl.searchParams.append('topic', 'chat/' + chatId);
        });
        
        window.homeEventSource = new EventSource(mercureUrl);
        
        window.homeEventSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                let chatId = data.chatId;
                
                if (!chatId && event.lastEventId) {
                    const match = event.lastEventId.match(/chat\/(\d+)/);
                    if (match && match[1]) {
                        chatId = match[1];
                    }
                }
                
                if (!chatId) {
                    console.error('Chat ID not found in event data', data, event);
                    return;
                }
                
                const unreadCounter = document.getElementById('unread-' + chatId);
                
                if (!unreadCounter) {
                    console.error('Unread counter element not found for chat', chatId);
                    return;
                }
                
                if (data.action === 'delete') {
                    if (data.wasUnread === true && data.sender && data.sender !== currentUserId) {
                        const currentCount = parseInt(unreadCounter.textContent) || 0;
                        if (currentCount > 0) {
                            unreadCounter.textContent = currentCount - 1;
                            unreadCounter.style.display = currentCount > 1 ? 'flex' : 'none';
                        }
                    }
                } else if (data.action === 'read_all' && data.reader === currentUserId) {
                    unreadCounter.textContent = '0';
                    unreadCounter.style.display = 'none';
                } else if (data.sender && data.sender.id !== currentUserId) {
                    unreadCounter.style.display = 'flex';
                    unreadCounter.textContent = (parseInt(unreadCounter.textContent) || 0) + 1;
                }
            } catch (error) {
                console.error('Error processing message:', error);
            }
        };
        
        window.homeEventSource.onerror = function(error) {
            console.error('EventSource error:', error);
            window.homeInitialized = false;
            window.homeInitializationInProgress = false;
            
            if (window.homeEventSource) {
                window.homeEventSource.close();
                window.homeEventSource = null;
            }
            
            setTimeout(() => {
                if (!window.homeInitialized && !window.homeInitializationInProgress && document.querySelector('.container')) {
                    initHome();
                }
            }, 3000);
        };
    }
    
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            const chatId = this.getAttribute('href').split('/').pop();
            const unreadCounter = document.getElementById('unread-' + chatId);
            if (unreadCounter) {
                unreadCounter.style.display = 'none';
                unreadCounter.textContent = '0';
            }
        });
    });
    
    window.homeInitialized = true;
    window.homeInitializationInProgress = false;
}

function resetHome() {
    console.log('Resetting home state...');
    if (window.homeEventSource) {
        window.homeEventSource.close();
        window.homeEventSource = null;
    }
    window.homeInitialized = false;
    window.homeInitializationInProgress = false;
}

document.addEventListener('DOMContentLoaded', function() {
    if (!window.homeInitialized && !window.homeInitializationInProgress) {
        initHome();
    }
});

document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible' && document.querySelector('.container') && !window.homeInitialized) {
        console.log('Reinitializing home after returning to page');
        initHome();
    }
});

window.addEventListener('beforeunload', function() {
    resetHome();
});

document.querySelectorAll('a[href="/profile"]').forEach(link => {
    link.addEventListener('click', function() {
        resetHome();
    });
});

if (!window.homeInitialized && !window.homeInitializationInProgress) {
    initHome();
}
