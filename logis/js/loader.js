// js/loader.js

// Loader Controller
const Loader = {
    show: function(type = 'bouncing') {
        const loader = document.getElementById('loader');
        if (loader) {
            // Clear existing loader content
            loader.innerHTML = '';
            
            // Create new loader based on type
            switch(type) {
                case 'bouncing':
                    loader.innerHTML = '<div class="bouncing-dots"><div></div><div></div><div></div></div>';
                    break;
                case 'spinner':
                    loader.innerHTML = '<div class="spinner"></div>';
                    break;
                case 'pulse':
                    loader.innerHTML = '<div class="pulse"></div>';
                    break;
                case 'progress':
                    loader.innerHTML = '<div class="progress-loader"></div>';
                    break;
                case 'typing':
                    loader.innerHTML = '<div class="typing-loader"><span></span><span></span><span></span></div>';
                    break;
                default:
                    loader.innerHTML = '<div class="bouncing-dots"><div></div><div></div><div></div></div>';
            }
            
            loader.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },
    
    hide: function() {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.remove('active');
            document.body.style.overflow = '';
        }
    },
    
    showWithMessage: function(message, type = 'bouncing') {
        this.show(type);
        const loader = document.getElementById('loader');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'loader-message mt-3 text-center';
        messageDiv.textContent = message;
        loader.appendChild(messageDiv);
    }
};

// Global loader functions
function showLoader(type = 'bouncing') {
    Loader.show(type);
}

function hideLoader() {
    Loader.hide();
}

// Auto-hide loader when page is fully loaded
window.addEventListener('load', function() {
    hideLoader();
});

// Show loader on AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Intercept fetch requests
    const originalFetch = window.fetch;
    window.fetch = function() {
        showLoader('spinner');
        return originalFetch.apply(this, arguments)
            .then(response => {
                hideLoader();
                return response;
            })
            .catch(error => {
                hideLoader();
                throw error;
            });
    };
    
    // Intercept XMLHttpRequest
    const XHR = XMLHttpRequest.prototype;
    const originalOpen = XHR.open;
    const originalSend = XHR.send;
    
    XHR.open = function() {
        this._url = arguments[1];
        return originalOpen.apply(this, arguments);
    };
    
    XHR.send = function() {
        this.addEventListener('loadstart', function() {
            showLoader('spinner');
        });
        
        this.addEventListener('loadend', function() {
            hideLoader();
        });
        
        this.addEventListener('error', function() {
            hideLoader();
        });
        
        return originalSend.apply(this, arguments);
    };
});