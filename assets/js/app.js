document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        const today = new Date();
        const offset = new Date(today.getTime() - (today.getTimezoneOffset() * 60000));
        dateInput.min = offset.toISOString().split('T')[0];
    }
});
