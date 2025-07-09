// ===== VARIABLES ET CONFIGURATION =====
const CONFIG = {
    DEMO_USERNAME: 'demo',
    DEMO_PASSWORD: 'demo',
    ANIMATION_DURATION: 300,
    NOTIFICATION_DURATION: 8000,
    MESSAGES: {
        CONNECTING: 'Mifandray...',
        SUCCESS: 'Tafiditra soa aman-tsara!',
        FILL_FIELDS: 'Fenoy ny saha rehetra azafady',
        WRONG_CREDENTIALS: 'Anaran\'ny mpampiasa na teny miafina diso',
        UNEXPECTED_ERROR: 'Hadisoana tsy nampoizina nitranga',
        DEMO_INFO: 'Demo: anaran\'ny mpampiasa = "demo", teny miafina = "demo"',
        WELCOME: 'Tongasoa eto amin\'ny Fianatra',
        LOGIN_TITLE: 'Fidirana amin\'ny Fyanatra',
        USERNAME_LABEL: 'Anaran\'ny mpampiasa',
        PASSWORD_LABEL: 'Teny miafina',
        LOGIN_BUTTON: 'Miditra',
        USERNAME_PLACEHOLDER: 'Ampidiro ny anaran\'ny mpampiasa',
        PASSWORD_PLACEHOLDER: 'Ampidiro ny teny miafina',
        SHOW_PASSWORD: 'Aseho ny teny miafina',
        HIDE_PASSWORD: 'Afeno ny teny miafina'
    }
};

// ===== VARIABLES D'ANIMATION =====
let logoAnimationInterval;
let backgroundAnimationFrame;

// ===== FONCTIONS UTILITAIRES =====
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (!passwordInput || !toggleIcon) return;
    
    // Animation de transition
    toggleIcon.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
            toggleIcon.setAttribute('aria-label', CONFIG.MESSAGES.HIDE_PASSWORD);
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
            toggleIcon.setAttribute('aria-label', CONFIG.MESSAGES.SHOW_PASSWORD);
        }
        
        // Retour à la taille normale
        toggleIcon.style.transform = 'scale(1)';
    }, 100);
}

// ===== ANIMATIONS DU LOGO AMÉLIORÉES =====
function initializeLogoAnimations() {
    const logoIcon = document.querySelector('.logo i');
    const logoText = document.querySelector('.logo');
    
    if (!logoIcon || !logoText) return;
    
    // Animation d'entrée spectaculaire
    logoText.style.opacity = '0';
    logoText.style.transform = 'translateY(-50px) scale(0.5) rotate(-180deg)';
    
    setTimeout(() => {
        logoText.style.transition = 'all 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        logoText.style.opacity = '1';
        logoText.style.transform = 'translateY(0) scale(1) rotate(0deg)';
    }, 200);
    
    // Animation de pulsation continue avec variation
    let pulseDirection = 1;
    let pulseScale = 1;
    
    logoAnimationInterval = setInterval(() => {
        pulseScale += (0.02 * pulseDirection);
        
        if (pulseScale >= 1.15) {
            pulseDirection = -1;
        } else if (pulseScale <= 1) {
            pulseDirection = 1;
        }
        
        logoIcon.style.transform = `scale(${pulseScale})`;
        logoIcon.style.filter = `drop-shadow(0 ${2 + (pulseScale - 1) * 10}px ${8 + (pulseScale - 1) * 20}px rgba(0, 122, 255, ${0.3 + (pulseScale - 1) * 0.3}))`;
    }, 50);
    
    // Animation de brillance périodique
    setInterval(() => {
        logoIcon.style.animation = 'logoIconPulse 3s infinite ease-in-out, logoIconRotate 20s infinite linear, logoIconBounce 2s infinite ease-in-out, logoShine 2s ease-in-out';
        
        setTimeout(() => {
            logoIcon.style.animation = 'logoIconPulse 3s infinite ease-in-out, logoIconRotate 20s infinite linear, logoIconBounce 2s infinite ease-in-out';
        }, 2000);
    }, 8000);
}

// ===== GESTION DES ERREURS AMÉLIORÉE =====
function handleLoginError(errorType, errorMessage) {
    switch(errorType) {
        case 'ldap_connection':
            showNotification(errorMessage, 'error', true);
            break;
        case 'invalid_credentials':
            showCredentialsError(errorMessage);
            break;
        case 'no_group':
            showNotification(errorMessage, 'warning', false);
            break;
        default:
            showNotification(errorMessage, 'error', true);
    }
}

function showCredentialsError(message) {
    const errorContainer = document.getElementById('error-container');
    if (!errorContainer) {
        // Créer le conteneur d'erreur s'il n'existe pas
        const newErrorContainer = document.createElement('div');
        newErrorContainer.id = 'error-container';
        newErrorContainer.className = 'error-container';
        
        const form = document.querySelector('.login-form');
        if (form) {
            form.parentNode.insertBefore(newErrorContainer, form);
        }
    }
    
    const container = document.getElementById('error-container');
    container.innerHTML = `
        <div class="credentials-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
        </div>
    `;
    container.style.display = 'block';
    
    // Animation d'apparition
    const errorDiv = container.querySelector('.credentials-error');
    errorDiv.style.opacity = '0';
    errorDiv.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        errorDiv.style.transition = 'all 0.3s ease';
        errorDiv.style.opacity = '1';
        errorDiv.style.transform = 'translateY(0)';
    }, 10);
    
    // Ajouter la classe d'erreur aux champs
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    if (usernameField) {
        usernameField.classList.add('error');
        usernameField.addEventListener('input', clearCredentialsError, { once: true });
    }
    if (passwordField) {
        passwordField.classList.add('error');
        passwordField.addEventListener('input', clearCredentialsError, { once: true });
    }
    
    // Vibration sur mobile si supportée
    if (navigator.vibrate) {
        navigator.vibrate([100, 50, 100]);
    }
}

function clearCredentialsError() {
    const errorContainer = document.getElementById('error-container');
    if (errorContainer) {
        const errorDiv = errorContainer.querySelector('.credentials-error');
        if (errorDiv) {
            errorDiv.style.transition = 'all 0.3s ease';
            errorDiv.style.opacity = '0';
            errorDiv.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 300);
        }
    }
    
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    if (usernameField) {
        usernameField.classList.remove('error');
    }
    if (passwordField) {
        passwordField.classList.remove('error');
    }
}

// ===== SYSTÈME DE NOTIFICATIONS AMÉLIORÉ =====
function showNotification(message, type = 'info', persistent = false) {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    });
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        ${!persistent ? '<button class="notification-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' : ''}
    `;

    // Styles pour la notification avec adaptation mobile
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 280px;
        max-width: 90vw;
        animation: slideInRight 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    `;

    document.body.appendChild(notification);

    // Suppression automatique
    if (!persistent) {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), CONFIG.ANIMATION_DURATION);
            }
        }, CONFIG.NOTIFICATION_DURATION);
    }
    
    // Vibration pour les notifications importantes
    if (type === 'error' && navigator.vibrate) {
        navigator.vibrate([200, 100, 200]);
    } else if (type === 'success' && navigator.vibrate) {
        navigator.vibrate(100);
    }
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        success: '#34C759',
        error: '#FF3B30',
        warning: '#FF9500',
        info: '#007AFF'
    };
    return colors[type] || '#007AFF';
}

// ===== VALIDATION DU FORMULAIRE AMÉLIORÉE =====
function validateForm() {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    
    if (!usernameInput || !passwordInput || !loginBtn) return;
    
    const isValid = usernameInput.value.trim().length > 0 && passwordInput.value.length > 0;
    
    // Animation fluide du bouton
    loginBtn.style.transition = 'all 0.3s ease';
    
    if (isValid) {
        loginBtn.style.opacity = '1';
        loginBtn.style.transform = 'translateY(0)';
        loginBtn.disabled = false;
        loginBtn.style.cursor = 'pointer';
    } else {
        loginBtn.style.opacity = '0.6';
        loginBtn.style.transform = 'translateY(1px)';
        loginBtn.disabled = true;
        loginBtn.style.cursor = 'not-allowed';
    }
}

// ===== GESTION DU FORMULAIRE AMÉLIORÉE =====
function handleFormSubmit(event) {
    event.preventDefault();
    
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    
    if (!usernameInput || !passwordInput || !loginBtn) return;
    
    // Effacer les erreurs précédentes
    clearCredentialsError();
    
    const username = usernameInput.value.trim();
    const password = passwordInput.value;
    
    // Validation côté client
    if (!username || !password) {
        showCredentialsError(CONFIG.MESSAGES.FILL_FIELDS);
        return;
    }
    
    // État de chargement avec animation
    loginBtn.classList.add('loading');
    loginBtn.innerHTML = `<div class="spinner"></div> ${CONFIG.MESSAGES.CONNECTING}`;
    loginBtn.disabled = true;
    
    // Animation du formulaire pendant le chargement
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        setTimeout(() => {
            group.style.opacity = '0.7';
        }, index * 100);
    });
    
    // Simulation de connexion
    setTimeout(() => {
        if (username === CONFIG.DEMO_USERNAME && password === CONFIG.DEMO_PASSWORD) {
            showNotification(CONFIG.MESSAGES.SUCCESS, 'success');
            
            // Animation de succès
            loginBtn.style.background = 'linear-gradient(135deg, #34C759 0%, #30D158 100%)';
            loginBtn.innerHTML = '<i class="fas fa-check"></i> ' + CONFIG.MESSAGES.SUCCESS;
            
            // Redirection après succès
            setTimeout(() => {
                window.location.href = 'mpampianatra.php';
            }, 1500);
        } else {
            showCredentialsError(CONFIG.MESSAGES.WRONG_CREDENTIALS);
            resetLoginButton();
            
            // Restaurer l'opacité du formulaire
            formGroups.forEach(group => {
                group.style.opacity = '1';
            });
        }
    }, 1500);
}

function resetLoginButton() {
    const loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.classList.remove('loading');
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> ' + CONFIG.MESSAGES.LOGIN_BUTTON;
        loginBtn.disabled = false;
        loginBtn.style.background = 'linear-gradient(135deg, var(--primary-blue) 0%, var(--success-green) 100%)';
        validateForm();
    }
}

// ===== ANIMATIONS ET EFFETS VISUELS AMÉLIORÉS =====
function initializeAnimations() {
    // Animation séquentielle des éléments
    const elements = [
        { selector: '.logo', delay: 200 },
        { selector: '.subtitle', delay: 400 },
        { selector: 'h2', delay: 600 },
        { selector: '.form-group:nth-child(1)', delay: 800 },
        { selector: '.form-group:nth-child(2)', delay: 1000 },
        { selector: '.login-btn', delay: 1200 },
        { selector: '.demo-info', delay: 1400 }
    ];
    
    elements.forEach(({ selector, delay }) => {
        const element = document.querySelector(selector);
        if (element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, delay);
        }
    });
    
    // Initialiser les animations du logo
    initializeLogoAnimations();
}

function setupInputEffects() {
    const inputs = document.querySelectorAll('input');
    
    inputs.forEach(input => {
        // Effet de focus amélioré
        input.addEventListener('focus', function() {
            this.parentElement.parentElement.classList.add('focused');
            
            // Animation de l'icône
            const icon = this.nextElementSibling;
            if (icon && icon.classList.contains('input-icon')) {
                icon.style.transform = 'translateY(-50%) scale(1.1)';
                icon.style.color = 'var(--primary-blue)';
            }
            
            // Vibration légère sur mobile
            if (navigator.vibrate) {
                navigator.vibrate(30);
            }
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.parentElement.classList.remove('focused');
            
            // Restaurer l'icône
            const icon = this.nextElementSibling;
            if (icon && icon.classList.contains('input-icon')) {
                icon.style.transform = 'translateY(-50%) scale(1)';
                icon.style.color = 'var(--text-secondary)';
            }
        });
        
        // Effet pendant la saisie
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.classList.add('has-content');
            } else {
                this.classList.remove('has-content');
            }
            
            // Validation en temps réel
            validateForm();
            
            // Effet de frappe
            this.style.transform = 'scale(1.01)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
        
        // Effet au survol sur desktop
        input.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                this.style.transform = 'translateY(-1px)';
            }
        });
        
        input.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                this.style.transform = 'translateY(0)';
            }
        });
    });
}

// ===== SUPPORT DU MODE SOMBRE AMÉLIORÉ =====
function initializeDarkMode() {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (prefersDark) {
        document.body.classList.add('dark-mode');
        
        // Ajuster les couleurs du gradient de fond
        const loginPage = document.querySelector('.login-page');
        if (loginPage) {
            loginPage.style.background = 'linear-gradient(135deg, #1a237e 0%, #4a148c 100%)';
        }
    }
    
    // Écouter les changements de mode
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (e.matches) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        });
    }
}

// ===== GESTION RESPONSIVE AMÉLIORÉE =====
function handleResponsiveChanges() {
    const handleResize = () => {
        const isMobile = window.innerWidth <= 768;
        const loginContainer = document.querySelector('.login-container');
        
        if (loginContainer) {
            if (isMobile) {
                loginContainer.style.maxWidth = '100%';
                loginContainer.style.margin = '0';
            } else {
                loginContainer.style.maxWidth = '380px';
                loginContainer.style.margin = 'auto';
            }
        }
        
        // Ajuster les animations selon la taille d'écran
        const logo = document.querySelector('.logo');
        if (logo && isMobile) {
            logo.style.fontSize = '2rem';
        } else if (logo) {
            logo.style.fontSize = '2.2rem';
        }
    };
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Appel initial
}

// ===== AMÉLIORATION DE L'ACCESSIBILITÉ =====
function enhanceAccessibility() {
    // Ajouter des labels ARIA
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle');
    
    if (usernameInput) {
        usernameInput.setAttribute('aria-label', CONFIG.MESSAGES.USERNAME_LABEL);
        usernameInput.setAttribute('aria-required', 'true');
    }
    
    if (passwordInput) {
        passwordInput.setAttribute('aria-label', CONFIG.MESSAGES.PASSWORD_LABEL);
        passwordInput.setAttribute('aria-required', 'true');
    }
    
    if (toggleButton) {
        toggleButton.setAttribute('aria-label', CONFIG.MESSAGES.SHOW_PASSWORD);
        toggleButton.setAttribute('role', 'button');
    }
    
    // Gestion du clavier
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const form = document.querySelector('.login-form');
            if (form && document.activeElement && form.contains(document.activeElement)) {
                e.preventDefault();
                handleFormSubmit(e);
            }
        }
    });
}

// ===== INITIALISATION COMPLÈTE =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser toutes les fonctionnalités
    initializeAnimations();
    setupInputEffects();
    initializeDarkMode();
    handleResponsiveChanges();
    enhanceAccessibility();
    
    // Configurer le formulaire
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Validation initiale
    validateForm();
    
    // Créer et afficher les informations de démo
    const demoInfo = document.createElement('div');
    demoInfo.className = 'demo-info';
    demoInfo.innerHTML = `<p>${CONFIG.MESSAGES.DEMO_INFO}</p>`;
    
    const loginContainer = document.querySelector('.login-container');
    if (loginContainer) {
        loginContainer.appendChild(demoInfo);
    }
    
    // Message de bienvenue initial
    setTimeout(() => {
        showNotification(CONFIG.MESSAGES.WELCOME, 'info', false);
    }, 2000);
});

// ===== GESTION DES ERREURS GLOBALES =====
window.addEventListener('error', function(event) {
    console.error('Hadisoana JavaScript:', event.error);
    showNotification(CONFIG.MESSAGES.UNEXPECTED_ERROR, 'error', true);
});

// ===== NETTOYAGE À LA FERMETURE =====
window.addEventListener('beforeunload', function() {
    if (logoAnimationInterval) {
        clearInterval(logoAnimationInterval);
    }
    if (backgroundAnimationFrame) {
        cancelAnimationFrame(backgroundAnimationFrame);
    }
});

// ===== FONCTIONS EXPORTÉES POUR COMPATIBILITÉ =====
window.togglePassword = togglePassword;
window.handleLoginError = handleLoginError;
window.showNotification = showNotification;
window.CONFIG = CONFIG;