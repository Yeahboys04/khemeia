import './styles/app.scss';
import './styles/pages/exportPdf.scss';

document.addEventListener('DOMContentLoaded', function() {
    const defaultFilterCheckbox = document.getElementById('use-default-filter');
    const formDefaultFilter = document.querySelector('input[name$="[defaultFilter]"]');
    const customExportContainer = document.getElementById('custom-export-container');
    const defaultExportOption = document.getElementById('default-export-option');
    const dateRangeSelector = document.getElementById('date-range-selector');
    const customDateRangeSection = document.getElementById('custom-date-range');

    // Éléments administrateur si présents
    const showAllUsersCheckbox = document.getElementById('export_pdf_showAllUsers');
    const userSelectionContainer = document.getElementById('user-selection-container');
    const userSelect = userSelectionContainer?.querySelector('select');

    // Fonction pour basculer l'affichage de la sélection utilisateur
    function toggleUserSelection() {
        if (!showAllUsersCheckbox || !userSelectionContainer) return;

        if (showAllUsersCheckbox.checked) {
            userSelectionContainer.classList.add('disabled');
            if (userSelect) {
                userSelect.value = ''; // Réinitialiser la sélection
            }
        } else {
            userSelectionContainer.classList.remove('disabled');
        }
    }

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

    // Fonction pour gérer l'affichage des dates personnalisées
    function toggleCustomDateRange() {
        if (!dateRangeSelector) return;

        if (dateRangeSelector.value === 'custom') {
            customDateRangeSection.style.display = 'block';
        } else {
            customDateRangeSection.style.display = 'none';
        }
    }

    // Initialisation des états
    toggleExportOptions();
    toggleCustomDateRange();
    toggleUserSelection();

    // Configuration des écouteurs d'événements
    defaultFilterCheckbox.addEventListener('change', toggleExportOptions);

    if (dateRangeSelector) {
        dateRangeSelector.addEventListener('change', toggleCustomDateRange);
    }

    if (showAllUsersCheckbox) {
        showAllUsersCheckbox.addEventListener('change', toggleUserSelection);
    }

    // Initialiser Select2 s'il est disponible
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.select2').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true
        });
    }

    // Gestion du formulaire à la soumission
    const form = document.getElementById('export-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Vérifier si l'export personnalisé est activé et qu'au moins une colonne est sélectionnée
            if (!defaultFilterCheckbox.checked) {
                const checkboxes = document.querySelectorAll('.export-option input[type="checkbox"]:not(#use-default-filter):not([name$="[filterByCMR]"]):not([name$="[showAllUsers]"])');
                const hasChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

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