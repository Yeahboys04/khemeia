import "../components/select";
import "../../styles/components/select.scss";
document.addEventListener('DOMContentLoaded', function() {
    // Mise en cache des sélecteurs DOM - minimise les requêtes DOM
    const productSearchField = document.querySelector('.product-search-field');
    const casSearchField = document.querySelector('.cas-search-field');
    const searchTypeInputs = document.querySelectorAll('input[name="search[searchType]"]');
    const searchAllInputs = document.querySelectorAll('input[name="search[searchAll]"]');
    const siteField = document.getElementById('site-field');
    const siteSelect = document.querySelector('#search_idSite');
    const idChimicalproduct = document.querySelector('#search_idChimicalproduct');
    const casSearch = document.querySelector('#search_casSearch');
    const printButton = document.getElementById("btnPrint");
    const resultsTable = document.querySelector('.results-table');

    // Application des styles de transition une seule fois
    if (productSearchField && casSearchField) {
        productSearchField.style.transition = 'opacity 0.3s ease';
        casSearchField.style.transition = 'opacity 0.3s ease';

        // Bascule entre les champs de recherche - optimisé avec requestAnimationFrame
        const toggleSearchFields = (type) => {
            if (type === 'product') {
                casSearchField.style.opacity = '0';

                setTimeout(() => {
                    casSearchField.style.display = 'none';
                    productSearchField.style.display = 'block';

                    requestAnimationFrame(() => {
                        productSearchField.style.opacity = '1';
                    });

                    // Basculement des attributs requis
                    idChimicalproduct?.setAttribute('required', 'required');
                    casSearch?.removeAttribute('required');
                }, 300);
            } else {
                productSearchField.style.opacity = '0';

                setTimeout(() => {
                    productSearchField.style.display = 'none';
                    casSearchField.style.display = 'block';

                    requestAnimationFrame(() => {
                        casSearchField.style.opacity = '1';
                    });

                    // Basculement des attributs requis
                    casSearch?.setAttribute('required', 'required');
                    idChimicalproduct?.removeAttribute('required');
                }, 300);
            }
        };

        // Utilisation d'écouteurs d'événements passifs pour de meilleures performances
        searchTypeInputs.forEach(input => {
            input.addEventListener('change', () => toggleSearchFields(input.value), { passive: true });
        });

        // Initialisation de l'état du formulaire - exécuté une seule fois au démarrage
        const initializeFormState = () => {
            const checkedInput = document.querySelector('input[name="search[searchType]"]:checked');
            if (checkedInput) {
                // Configuration de l'affichage initial sans animations
                if (checkedInput.value === 'cas') {
                    productSearchField.style.opacity = '0';
                    productSearchField.style.display = 'none';
                    casSearchField.style.display = 'block';
                    casSearchField.style.opacity = '1';
                    casSearch?.setAttribute('required', 'required');
                    idChimicalproduct?.removeAttribute('required');
                } else {
                    casSearchField.style.opacity = '0';
                    casSearchField.style.display = 'none';
                    productSearchField.style.display = 'block';
                    productSearchField.style.opacity = '1';
                    idChimicalproduct?.setAttribute('required', 'required');
                    casSearch?.removeAttribute('required');
                }
            }
        };

        initializeFormState();
    }

    // Fonction de basculement du champ de site
    if (siteField && siteSelect) {
        const toggleSiteField = (value) => {
            if (value === 'oui') {
                // IMPORTANT: Utiliser notre nouvelle classe spécifique
                // au lieu de la classe 'hidden' générique
                siteField.classList.add('site-field-disabled');
                siteSelect.setAttribute('disabled', 'disabled');
                siteSelect.removeAttribute('required');

                // Effet visuel pour indiquer le changement
                siteField.classList.add('pulse-effect');
                setTimeout(() => {
                    siteField.classList.remove('pulse-effect');
                }, 600);
            } else {
                siteField.classList.remove('site-field-disabled');
                siteSelect.removeAttribute('disabled');
                siteSelect.setAttribute('required', 'required');

                // Effet visuel pour indiquer le changement
                siteField.classList.add('pulse-effect');
                setTimeout(() => {
                    siteField.classList.remove('pulse-effect');
                }, 600);
            }
        };

        // Utilisation de la délégation d'événements et d'événements passifs
        searchAllInputs.forEach(input => {
            input.addEventListener('change', () => toggleSiteField(input.value), { passive: true });
        });

        // Initialisation de l'état du champ de site - exécuté une seule fois
        const checkedSearchAllInput = document.querySelector('input[name="search[searchAll]"]:checked');
        if (checkedSearchAllInput) {
            toggleSiteField(checkedSearchAllInput.value);
        }
    }

    // Initialisation des animations pour la table de résultats si présente
    if (resultsTable) {
        requestAnimationFrame(() => {
            resultsTable.style.animation = 'fadeIn 0.5s ease';
        });
    }

    // Ajout de la fonctionnalité d'impression si le bouton existe
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
});