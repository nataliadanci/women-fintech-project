// main.js
import { toggleTheme } from './theme.js';

document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    if (themeToggle) {
        // Verifică preferința salvată
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';
        }

        // Adaugă funcția toggleTheme la click-ul pe buton
        themeToggle.addEventListener('click', () => {
            console.log('Toggling theme...');
            toggleTheme(themeToggle, body);
        });
    }
});

// Funcție pentru validarea email-ului
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Funcție pentru validarea URL-ului LinkedIn
function validateLinkedIn(url) {
    return url.includes('linkedin.com/');
}
