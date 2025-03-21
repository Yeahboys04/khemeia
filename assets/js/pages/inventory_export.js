// Import required styles
import '../../styles/pages/inventoryExport.scss';

document.addEventListener('DOMContentLoaded', function() {
    const defaultFilterCheckbox = document.getElementById('use-default-filter');
    const formDefaultFilter = document.querySelector('input[name$="[defaultFilter]"]');
    const customExportContainer = document.getElementById('custom-export-container');
    const defaultExportOption = document.getElementById('default-export-option');

    // Fonction pour basculer l'affichage
    function toggleExportOptions() {
        if (defaultFilterCheckbox.checked) {
            formDefaultFilter.checked = true;
            customExportContainer.style.display = 'none';
            defaultExportOption.classList.add('active');
        } else {
            formDefaultFilter.checked = false;
            customExportContainer.style.display = 'block';
            defaultExportOption.classList.remove('active');

            // Animation pour l'apparition du contenu personnalisé
            customExportContainer.style.opacity = 0;
            setTimeout(() => {
                customExportContainer.style.transition = 'opacity 0.3s ease';
                customExportContainer.style.opacity = 1;
            }, 10);
        }
    }

    // Initialisation des états
    toggleExportOptions();

    // Configuration des écouteurs d'événements
    defaultFilterCheckbox.addEventListener('change', toggleExportOptions);

    // Initialiser Select2 s'il est disponible
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.select2').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true
        });
    }

    // Gestion du formulaire à la soumission
    const form = document.getElementById('inventory-export-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Vérifier si l'export personnalisé est activé et qu'au moins une colonne est sélectionnée
            if (!defaultFilterCheckbox.checked) {
                const displayOptions = [
                    document.getElementById('inventory_export_filter_showLocation'),
                    document.getElementById('inventory_export_filter_showQuantity'),
                    document.getElementById('inventory_export_filter_showExpiration'),
                    document.getElementById('inventory_export_filter_showCMR'),
                    document.getElementById('inventory_export_filter_showSupplier'),
                    document.getElementById('inventory_export_filter_showSymbols')
                ];

                const hasChecked = displayOptions.some(checkbox => checkbox && checkbox.checked);

                if (!hasChecked) {
                    event.preventDefault();
                    alert('Veuillez sélectionner au moins une colonne à afficher dans le rapport.');
                    return false;
                }
            }
            return true;
        });
    }
});