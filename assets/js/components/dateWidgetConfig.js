document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les champs datepicker
    document.querySelectorAll('.datepicker').forEach(function(input) {
        // Créer un input de type date
        var dateInput = document.createElement('input');
        dateInput.type = 'date';
        dateInput.className = input.className;
        dateInput.name = input.name;
        dateInput.id = input.id;
        dateInput.required = input.required;

        // Remplacer l'input original par l'input date
        input.parentNode.replaceChild(dateInput, input);
    });
});