function switchTab(type) {
    const signupForm = document.getElementById('signup-form');
    const loginForm = document.getElementById('login-form');
    const tabs = document.querySelectorAll('.tab-btn');

    if (type === 'signup') {
        signupForm.classList.add('active');
        loginForm.classList.remove('active');
        tabs[0].classList.add('active');
        tabs[1].classList.remove('active');
    } else {
        loginForm.classList.add('active');
        signupForm.classList.remove('active');
        tabs[1].classList.add('active');
        tabs[0].classList.remove('active');
    }
}
