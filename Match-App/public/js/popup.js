document.addEventListener('DOMContentLoaded', function() {
   
    const popupOverlay = document.createElement('div');
    popupOverlay.className = 'popup-overlay';
    
    const popupContainer = document.createElement('div');
    popupContainer.className = 'popup-container';
    
    const closeButton = document.createElement('button');
    closeButton.className = 'popup-close';
    closeButton.innerHTML = '&times;';
    closeButton.setAttribute('aria-label', 'Fermer');
    
    const popupContent = document.createElement('div');
    popupContent.className = 'popup-content';
    
    popupContainer.appendChild(closeButton);
    popupContainer.appendChild(popupContent);
    popupOverlay.appendChild(popupContainer);
    document.body.appendChild(popupOverlay);
    
 
    function openPopup(content) {
        popupContent.innerHTML = content;
        popupOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
 
    function closePopup() {
        popupOverlay.classList.remove('active');
        document.body.style.overflow = ''; 
        setTimeout(() => {
            popupContent.innerHTML = ''; 
        }, 300);
    }
    
    
    closeButton.addEventListener('click', closePopup);
    
   
    popupOverlay.addEventListener('click', function(e) {
        if (e.target === popupOverlay) {
            closePopup();
        }
    });
    
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popupOverlay.classList.contains('active')) {
            closePopup();
        }
    });
    
    
    document.addEventListener('click', function(e) {
        
        const profileLink = e.target.closest('a[href^="/profile/"]');
        
        if (profileLink && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            
            const url = profileLink.getAttribute('href');
            
            
            popupContent.innerHTML = '<div class="popup-loading"></div>';
            popupOverlay.classList.add('active');
            
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                openPopup(html);
                
                
                const closePopupBtns = popupContent.querySelectorAll('.close-popup');
                if (closePopupBtns.length > 0) {
                    closePopupBtns.forEach(btn => {
                        btn.addEventListener('click', closePopup);
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement du profil:', error);
                popupContent.innerHTML = '<div class="error-message">Erreur lors du chargement du profil. Veuillez réessayer.</div>';
            });
        }
    });
});
