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
document.getElementById('togglePassword').addEventListener('click', function() {
    togglePasswordVisibility('password', 'togglePassword');
});

