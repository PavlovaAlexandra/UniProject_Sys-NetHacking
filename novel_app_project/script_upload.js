document.addEventListener('DOMContentLoaded', function() {
    // Привязываем события через JavaScript
    document.getElementById('type').addEventListener('change', toggleFields);
    document.getElementById('content').addEventListener('input', updateCharacterCount);
    
    // Инициализируем начальное состояние
    toggleFields();
    updateCharacterCount();
});

function toggleFields() {
    var type = document.getElementById('type').value;
    var txtFields = document.getElementById('txtFields');
    var pdfFields = document.getElementById('pdfFields');
    var pdfInput = document.getElementById('pdf_file');
    var contentInput = document.getElementById('content');

    // Управление классами 'hidden' вместо использования display
    if (type === 'txt') {
        txtFields.style.display = 'block';  // Показываем блок с текстом
        pdfFields.classList.add('hidden');  // Скрываем блок с PDF
    } else if (type === 'pdf') {
        pdfFields.classList.remove('hidden');  // Показываем блок с PDF
        txtFields.style.display = 'none';  // Скрываем блок с текстом
    }

    // Убираем или добавляем атрибут required
    contentInput.required = (type === 'txt');
    pdfInput.required = (type === 'pdf');
}

function updateCharacterCount() {
    var content = document.getElementById('content');
    var charRemaining = document.getElementById('charRemaining');
    
    var currentLength = content.value.length;
    var remaining = 15000 - currentLength;
    
    charRemaining.textContent = remaining >= 0 ? remaining : 0;

    if (remaining < 0) {
        content.value = content.value.substring(0, 15000);
    }
}
