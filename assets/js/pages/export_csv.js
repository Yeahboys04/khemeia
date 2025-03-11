import '../../styles/pages/export.scss';
class ExportManager {
    constructor() {
        // Éléments principaux du formulaire
        this.defaultExportCheckbox = document.getElementById('use-default-export');
        this.formDefaultExport = document.querySelector('input[name$="[defaultExport]"]'); // Trouve l'élément par son attribut name qui se termine par [defaultExport]
        this.customExportContainer = document.getElementById('custom-export-container');
        this.defaultExportOption = document.getElementById('default-export-option');
        this.allSitesCheckbox = document.querySelector('input[name$="[allSites]"]');
        this.siteSelector = document.getElementById('site-selector-container');
        this.siteSelect = document.querySelector('#site-selector-container select');

        // Éléments pour les selects améliorés
        this.productSelect = document.querySelector('.product-select');

        // Initialisation
        this.init();
    }

    init() {
        // Vérifier que les éléments requis sont présents
        if (!this.defaultExportCheckbox || !this.formDefaultExport || !this.customExportContainer) {
            console.warn('Éléments de formulaire requis non trouvés');
            return;
        }

        // Configuration des événements
        this.setupEventListeners();

        // État initial
        this.toggleExportOptions();
        this.toggleSiteSelector();

        // Améliorer les select natifs
        this.enhanceSelects();
    }

    setupEventListeners() {
        // Basculer entre export par défaut et personnalisé
        this.defaultExportCheckbox.addEventListener('change', () => this.toggleExportOptions());

        // Gérer l'option "tous les sites"
        if (this.allSitesCheckbox) {
            this.allSitesCheckbox.addEventListener('change', () => this.toggleSiteSelector());
        }

        // Ajouter la validation avant soumission du formulaire
        const form = document.getElementById('export-form');
        if (form) {
            form.addEventListener('submit', (e) => this.validateForm(e));
        }
    }

    toggleExportOptions() {
        if (this.defaultExportCheckbox.checked) {
            this.formDefaultExport.checked = true;
            this.customExportContainer.style.display = 'none';
            this.defaultExportOption.classList.add('active');
        } else {
            this.formDefaultExport.checked = false;
            this.customExportContainer.style.display = 'block';
            this.defaultExportOption.classList.remove('active');

            // Animation subtile pour l'apparition du contenu personnalisé
            this.customExportContainer.style.opacity = '0';
            setTimeout(() => {
                this.customExportContainer.style.transition = 'opacity 0.3s ease';
                this.customExportContainer.style.opacity = '1';
            }, 10);
        }
    }

    toggleSiteSelector() {
        if (!this.allSitesCheckbox || !this.siteSelector || !this.siteSelect) return;

        if (this.allSitesCheckbox.checked) {
            this.siteSelect.disabled = true;
            this.siteSelector.classList.add('disabled-container');
        } else {
            this.siteSelect.disabled = false;
            this.siteSelector.classList.remove('disabled-container');
        }
    }

    validateForm(e) {
        // Si l'export personnalisé est activé, vérifier qu'au moins un champ est sélectionné
        if (!this.defaultExportCheckbox.checked) {
            const fieldCheckboxes = document.querySelectorAll('.field-item input[type="checkbox"]');
            const hasCheckedField = Array.from(fieldCheckboxes).some(checkbox => checkbox.checked);

            if (!hasCheckedField) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un champ à inclure dans l\'export.');
                return false;
            }
        }

        // Afficher un indicateur de chargement
        this.showLoadingIndicator();
        return true;
    }

    showLoadingIndicator() {
        // Créer un overlay de chargement
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;

        const loadingContent = document.createElement('div');
        loadingContent.style.cssText = `
            text-align: center;
        `;

        const spinner = document.createElement('div');
        spinner.style.cssText = `
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3c8dbc;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        `;

        const loadingText = document.createElement('p');
        loadingText.textContent = 'Préparation de l\'export CSV...';
        loadingText.style.cssText = `
            font-size: 18px;
            color: #333;
            font-weight: 600;
        `;

        // Ajouter l'animation de rotation
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;

        document.head.appendChild(styleElement);
        loadingContent.appendChild(spinner);
        loadingContent.appendChild(loadingText);
        loadingOverlay.appendChild(loadingContent);
        document.body.appendChild(loadingOverlay);
    }

    enhanceSelects() {
        // Améliorer l'apparence et le comportement de tous les selects natifs
        const selects = document.querySelectorAll('.native-select');
        selects.forEach(select => {
            // Ajouter des styles hover et focus pour une meilleure expérience utilisateur
            select.addEventListener('mouseover', () => {
                select.style.borderColor = '#5aa9d6';
            });

            select.addEventListener('mouseout', () => {
                if (document.activeElement !== select) {
                    select.style.borderColor = '';
                }
            });

            select.addEventListener('focus', () => {
                select.style.borderColor = '#3c8dbc';
                select.style.boxShadow = '0 0 0 0.2rem rgba(60, 141, 188, 0.25)';
            });

            select.addEventListener('blur', () => {
                select.style.borderColor = '';
                select.style.boxShadow = '';
            });

            // Pour le select des produits, ajouter une fonctionnalité de recherche si nécessaire
            if (select.classList.contains('product-select') && select.options.length > 10) {
                this.createSearchableDropdown(select);
            }
        });
    }

    createSearchableDropdown(originalSelect) {
        // Cette méthode crée un dropdown personnalisé avec recherche pour remplacer le select natif des produits
        if (!originalSelect) return;

        // Créer le conteneur et masquer le select original
        const container = document.getElementById('product-dropdown-container');
        originalSelect.style.display = 'none';

        // Créer l'élément qui affiche l'option sélectionnée
        const selectedDisplay = document.createElement('div');
        selectedDisplay.className = 'selected-option';
        selectedDisplay.textContent = originalSelect.options[originalSelect.selectedIndex]?.text || 'Sélectionnez un produit';
        selectedDisplay.setAttribute('tabindex', '0');

        // Créer le dropdown des options
        const dropdownContainer = document.createElement('div');
        dropdownContainer.className = 'dropdown-options';

        // Ajouter une boîte de recherche
        const searchBox = document.createElement('div');
        searchBox.className = 'search-box';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Rechercher un produit...';
        searchBox.appendChild(searchInput);
        dropdownContainer.appendChild(searchBox);

        // Ajouter les options
        Array.from(originalSelect.options).forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.className = 'dropdown-option';
            optionElement.textContent = option.text;
            optionElement.dataset.value = option.value;

            optionElement.addEventListener('click', () => {
                selectedDisplay.textContent = option.text;
                originalSelect.value = option.value;

                // Déclencher l'événement change sur le select original
                const event = new Event('change', { bubbles: true });
                originalSelect.dispatchEvent(event);

                dropdownContainer.classList.remove('show');
            });

            dropdownContainer.appendChild(optionElement);
        });

        // Ajouter les éléments au DOM
        container.appendChild(selectedDisplay);
        container.appendChild(dropdownContainer);

        // Gérer l'ouverture/fermeture du dropdown
        selectedDisplay.addEventListener('click', () => {
            dropdownContainer.classList.toggle('show');
            searchInput.focus();
        });

        // Gérer la recherche
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const options = dropdownContainer.querySelectorAll('.dropdown-option');

            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdownContainer.classList.remove('show');
            }
        });

        // Support clavier
        selectedDisplay.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                dropdownContainer.classList.toggle('show');
                searchInput.focus();
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdownContainer.classList.remove('show');
                selectedDisplay.focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const visibleOptions = Array.from(dropdownContainer.querySelectorAll('.dropdown-option'))
                    .filter(opt => opt.style.display !== 'none');
                if (visibleOptions.length > 0) {
                    visibleOptions[0].focus();
                }
            }
        });

        // Navigation dans les options avec le clavier
        dropdownContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdownContainer.classList.remove('show');
                selectedDisplay.focus();
                return;
            }

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();

                const visibleOptions = Array.from(dropdownContainer.querySelectorAll('.dropdown-option'))
                    .filter(opt => opt.style.display !== 'none');

                const currentIndex = visibleOptions.indexOf(document.activeElement);
                let nextIndex;

                if (e.key === 'ArrowDown') {
                    nextIndex = currentIndex < visibleOptions.length - 1 ? currentIndex + 1 : 0;
                } else {
                    nextIndex = currentIndex > 0 ? currentIndex - 1 : visibleOptions.length - 1;
                }

                visibleOptions[nextIndex].focus();
            }

            if (e.key === 'Enter' && document.activeElement.classList.contains('dropdown-option')) {
                document.activeElement.click();
            }
        });
    }
}

// Fonctions pour animer les transitions entre les états
function fadeIn(element, duration = 300) {
    element.style.opacity = 0;
    element.style.display = 'block';

    let start = null;

    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;

        element.style.opacity = Math.min(progress / duration, 1);

        if (progress < duration) {
            window.requestAnimationFrame(animate);
        }
    }

    window.requestAnimationFrame(animate);
}

function fadeOut(element, duration = 300) {
    let start = null;

    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;

        element.style.opacity = Math.max(1 - progress / duration, 0);

        if (progress < duration) {
            window.requestAnimationFrame(animate);
        } else {
            element.style.display = 'none';
        }
    }

    window.requestAnimationFrame(animate);
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const exportManager = new ExportManager();

    // Exposer l'instance pour le débogage et l'utilisation externe si nécessaire
    window.exportManager = exportManager;
});