window.chatInitialized = false;
window.chatInitializationInProgress = false;

function initChat() {
    if (window.chatInitialized || window.chatInitializationInProgress) {
        return;
    }
    
    window.chatInitializationInProgress = true;
    
    if (window.chatEventSource) {
        window.chatEventSource.close();
        window.chatEventSource = null;
    }
    
    const chatBox = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const audioButton = document.getElementById('audio-button');
    const chatId = chatConfig.chatId;
    const currentUserId = chatConfig.currentUserId;
    const baseUploadPath = chatConfig.baseUploadPath;
    const defaultImagePath = chatConfig.defaultImagePath;
    const mercureUrl = chatConfig.mercureUrl;

    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    scrollToBottom();

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function createMessageElement(message) {
        if (!message || !message.id) {
            return null;
        }

        const sender = message.sender || { id: 'unknown' };
        const isCurrentUser = sender.id === currentUserId;

        const messageContainer = document.createElement('div');
        messageContainer.className = `message-container ${isCurrentUser ? 'current-user' : ''}`;
        messageContainer.dataset.messageId = message.id;

        if (!isCurrentUser) {
            const avatar = document.createElement('div');
            avatar.className = 'avatar me-2';
            avatar.style.width = '32px';
            avatar.style.height = '32px';

            const imgSrc = sender.picture 
                ? `${baseUploadPath}/uploads/${sender.picture}` 
                : defaultImagePath;

            const img = document.createElement('img');
            img.src = imgSrc;
            img.alt = sender.username || 'Utilisateur';

            avatar.appendChild(img);
            messageContainer.appendChild(avatar);
        }

        const messageBubble = document.createElement('div');
        messageBubble.className = `message-bubble ${isCurrentUser ? 'current-user' : 'partner'}`;

        const messageText = document.createElement('div');
        messageText.className = 'message-text';
        
        if (message.type === 'audio' && message.audioUrl) {
            const audioContainer = document.createElement('div');
            audioContainer.className = 'audio-message';
            
            const audio = document.createElement('audio');
            audio.controls = true;
            
            const source = document.createElement('source');
            source.src = baseUploadPath + (message.audioUrl.startsWith('/') ? message.audioUrl : '/' + message.audioUrl);
            source.type = 'audio/webm';
            
            audio.appendChild(source);
            audioContainer.appendChild(audio);
            messageText.appendChild(audioContainer);
        } else {
            messageText.textContent = message.content || '';
        }

        const messageTime = document.createElement('div');
        messageTime.className = 'message-time';
        
        // Correction du bug d'affichage de date
        let timestamp = '';
        if (message.timestamp) {
            try {
                const date = new Date(message.timestamp);
                // Vérifier si la date est valide
                if (!isNaN(date.getTime())) {
                    timestamp = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } else {
                    // Utiliser l'heure actuelle si la date est invalide
                    timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
            } catch (e) {
                // En cas d'erreur, utiliser l'heure actuelle
                timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
        } else {
            // Si pas de timestamp, utiliser l'heure actuelle
            timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        
        messageTime.textContent = timestamp;

        if (isCurrentUser) {
            const readIndicator = document.createElement('span');
            readIndicator.className = 'read-indicator';
            readIndicator.innerHTML = '<i class="bi bi-check2-all"></i>';
            messageTime.appendChild(readIndicator);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'delete-message';
            deleteButton.dataset.messageId = message.id;
            deleteButton.innerHTML = '<i class="bi bi-trash text-danger" style="font-size: 12px;"></i>';

            deleteButton.addEventListener('click', function() {
                if (confirm('Supprimer ce message ?')) {
                    deleteMessage(message.id);
                }
            });

            messageBubble.appendChild(deleteButton);
        }

        messageBubble.appendChild(messageText);
        messageBubble.appendChild(messageTime);
        messageContainer.appendChild(messageBubble);

        return messageContainer;
    }

    function sendMessage(type = 'text', audioData = null) {
        const content = messageInput.value.trim();
        if (type === 'text' && !content) return;

        const messageData = {
            type: type,
            content: type === 'text' ? content : 'Message audio'
        };

        if (type === 'audio' && audioData) {
            messageData.audioData = audioData;
        }

        fetch(`/chat/${chatId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(messageData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error || 'Network response was not ok'); });
            }
            return response.json();
        })
        .then(data => {
            messageInput.value = '';
            messageInput.focus();
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Erreur lors de l\'envoi du message. Veuillez réessayer.');
        });
    }

    function deleteMessage(messageId) {
        fetch(`/message/${messageId}/delete`, {
            method: 'DELETE'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error deleting message:', error);
            alert('Erreur lors de la suppression du message. Veuillez réessayer.');
        });
    }
    
    function markMessagesAsRead() {
        fetch(`/chat/${chatId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error marking messages as read:', error);
        });
    }

    function initAudioRecording() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);
                    
                    mediaRecorder.ondataavailable = function(e) {
                        audioChunks.push(e.data);
                    };
                    
                    mediaRecorder.onstop = function() {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const reader = new FileReader();
                        reader.readAsDataURL(audioBlob);
                        reader.onloadend = function() {
                            const base64data = reader.result;
                            sendMessage('audio', base64data);
                            audioChunks = [];
                        };
                    };
                })
                .catch(error => {
                    console.error('Microphone access error:', error);
                    alert("Impossible d'accéder au microphone. Veuillez vérifier vos paramètres.");
                });
        } else {
            console.warn('Audio recording not supported in this browser');
            alert("Votre navigateur ne supporte pas l'enregistrement audio.");
            audioButton.style.display = 'none';
        }
    }

    audioButton.addEventListener('click', function() {
        if (!isRecording) {
            if (!mediaRecorder) {
                alert("Le système d'enregistrement n'est pas prêt. Veuillez réessayer.");
                return;
            }
            
            audioChunks = [];
            mediaRecorder.start();
            isRecording = true;
            audioButton.innerHTML = '<i class="bi bi-stop-fill"></i>';
            audioButton.classList.add('recording');
            messageInput.placeholder = 'Enregistrement en cours...';
            messageInput.disabled = true;
        } else {
            mediaRecorder.stop();
            isRecording = false;
            audioButton.innerHTML = '<i class="bi bi-mic-fill"></i>';
            audioButton.classList.remove('recording');
            messageInput.placeholder = 'Écrivez votre message...';
            messageInput.disabled = false;
        }
    });

    if (window.sendButtonHandler) {
        sendButton.removeEventListener('click', window.sendButtonHandler);
    }
    if (window.messageInputHandler) {
        messageInput.removeEventListener('keypress', window.messageInputHandler);
    }

    window.sendButtonHandler = function() {
        sendMessage('text');
    };
    window.messageInputHandler = function(event) {
        if (event.key === 'Enter') {
            sendMessage('text');
        }
    };

    sendButton.addEventListener('click', window.sendButtonHandler);
    messageInput.addEventListener('keypress', window.messageInputHandler);
    document.querySelectorAll('.delete-message').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Supprimer ce message ?')) {
                deleteMessage(this.dataset.messageId);
            }
        });
    });

    const mercureEventUrl = new URL(mercureUrl);
    mercureEventUrl.searchParams.append('topic', `chat/${chatId}`);
    window.chatEventSource = new EventSource(mercureEventUrl);
    
    window.chatEventSource.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);

            if (data.action === 'delete') {
                const messageElement = document.querySelector(`[data-message-id="${data.messageId}"]`);
                if (messageElement) {
                    messageElement.remove();
                }
                return;
            }
            
            if (data.sender && data.sender.id !== currentUserId) {
                markMessagesAsRead();
            }

            if (!data || !data.id) {
                console.error('Invalid message data', data);
                return;
            }

            const messageElement = createMessageElement(data);
            
            if (messageElement) {
                chatBox.appendChild(messageElement);

                const emptyChat = document.querySelector('.empty-chat');
                if (emptyChat) {
                    emptyChat.remove();
                }

                scrollToBottom();
            }
        } catch (error) {
            console.error('Error processing message event:', error);
        }
    };

    window.chatEventSource.onerror = function(error) {
        console.error('EventSource error:', error);
        window.chatInitialized = false;
        window.chatInitializationInProgress = false;
        
        if (window.chatEventSource) {
            window.chatEventSource.close();
            window.chatEventSource = null;
        }
        
        setTimeout(() => {
            if (!window.chatInitialized && !window.chatInitializationInProgress && document.querySelector('.chat-container')) {
                console.log('Attempting to reconnect Mercure...');
                initChat();
            }
        }, 3000);
    };

    markMessagesAsRead();
    initAudioRecording();
    
    window.chatInitialized = true;
    window.chatInitializationInProgress = false;
}

document.addEventListener('DOMContentLoaded', function() {
    if (!window.chatInitialized && !window.chatInitializationInProgress) {
        initChat();
    }
});

document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible' && document.querySelector('.chat-container')) {
        if(!window.chatInitialized) {
            initChat();
        } else {
            const chatId = chatConfig.chatId;
            fetch(`/chat/${chatId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                console.error('Error marking messages as read on visibility change:', error);
            });
        }
    }
});

window.addEventListener('beforeunload', function() {
    if (window.chatEventSource) {
        window.chatEventSource.close();
        window.chatEventSource = null;
    }
    
    window.chatInitialized = false;
    window.chatInitializationInProgress = false;
});

if (!window.chatInitialized && !window.chatInitializationInProgress) {
    initChat();
}