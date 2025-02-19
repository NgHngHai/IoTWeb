function toggleButton(button) {
    let buttons = button.parentElement.children;
    for (let btn of buttons) {
        btn.classList.remove('current');
    }
    button.classList.add('current');
}