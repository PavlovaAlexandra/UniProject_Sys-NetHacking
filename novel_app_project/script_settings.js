// Функция для переключения видимости пароля
function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleButton = document.getElementById(toggleButtonId);

    if (passwordField.type === "password") {
        passwordField.type = "text";  // Показываем пароль
        toggleButton.textContent = "🙈";  // Меняем иконку на "скрыть"
    } else {
        passwordField.type = "password";  // Скрываем пароль
        toggleButton.textContent = "👁";  // Меняем иконку на "показать"
    }
}

// Привязываем событие клика к кнопке togglePassword
document.getElementById('toggleOldPassword').addEventListener('click', function() {
    togglePasswordVisibility('old_password', 'toggleOldPassword');
});

// Привязываем событие клика к кнопке togglePassword
document.getElementById('toggleNewPassword').addEventListener('click', function() {
    togglePasswordVisibility('new_password', 'toggleNewPassword');
});

// Привязываем событие клика к кнопке togglePassword
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');
});

