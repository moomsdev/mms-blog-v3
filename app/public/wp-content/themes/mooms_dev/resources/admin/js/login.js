document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('user_login').setAttribute('placeholder', 'Username or Email Address');
    document.getElementById('user_pass').setAttribute('placeholder', 'Password');

    // create div class welcome
    var welcomeDiv = document.createElement('div');
    welcomeDiv.className = 'welcome';
    welcomeDiv.textContent = 'Welcome to our website';

    // insert after logo
    var loginForm = document.getElementById('login');
    var logo = document.querySelector('#login h1');
    if (logo) {
        logo.insertAdjacentElement('afterend', welcomeDiv);
    }
});
