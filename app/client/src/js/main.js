document.addEventListener("DOMContentLoaded", function (event) {

    const menu_button = document.querySelector('[data-behaviour="toggle-menu"]');

    menu_button.addEventListener('click', () => {
        document.body.classList.toggle('body--show');
    })
});
