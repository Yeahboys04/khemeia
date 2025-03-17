// assets/js/tom-select.js
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.min.css';
import '../../styles/components/tom-select.scss';

// Initialisation de Tom Select sur tous les éléments avec la classe 'tom-select'
document.addEventListener('DOMContentLoaded', function() {
    const selectElements = document.querySelectorAll('.tom-select');

    selectElements.forEach(function(element) {
        new TomSelect(element, {
            plugins: ['remove_button'],
            maxItems: null, // Pour les sélections multiples
            placeholder: element.dataset.placeholder || 'Sélectionnez...',
            allowEmptyOption: true,
            hidePlaceholder: false,
            closeAfterSelect: true,
            render: {
                // Personnalisation de l'affichage (optionnel)
                option: function(data, escape) {
                    return '<div class="option">' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div class="item">' + escape(data.text) + '</div>';
                }
            }
        });
    });
});