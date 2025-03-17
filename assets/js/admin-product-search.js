// assets/js/admin-product-search.js

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap4.min.css';

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Tom Select sur le champ de nom de produit
    const productNameField = document.querySelector('#chimicalproduct_nameChimicalproduct');

    if (productNameField) {
        // Sélectionner les autres champs du formulaire pour les remplir automatiquement
        const formulaField = document.querySelector('#chimicalproduct_formula');
        const casNumberField = document.querySelector('#chimicalproduct_casnumber');

        // Indicateur pour savoir si un produit existe déjà
        let productExists = false;
        let warningElement = null;

        // Création de l'élément d'avertissement
        function createWarning() {
            if (!warningElement) {
                warningElement = document.createElement('div');
                warningElement.className = 'callout callout-warning mt-2';
                warningElement.style.display = 'none';
                warningElement.innerHTML = 'Ce produit existe déjà dans la base de données.';
                productNameField.parentNode.appendChild(warningElement);
            }
        }

        createWarning();

        // Stocker la valeur originale avant l'ouverture du dropdown
        let originalValue = '';

        // Initialiser Tom Select
        const tomSelect = new TomSelect(productNameField, {
            valueField: 'text',
            labelField: 'text',
            searchField: ['text'],
            create: true,
            maxItems: 1,
            closeAfterSelect: true,
            hideSelected: true,

            // Configuration normale pour les suggestions
            load: function(query, callback) {
                if (!query.length || query.length < 2) return callback();

                // Effectuer la requête AJAX
                fetch(`/api/search/products?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            },

            // Garder trace de ce que l'utilisateur tape avant l'ouverture du dropdown
            onFocus: function() {
                originalValue = this.items[0] || '';
            },

            onType: function(str) {
                // Réinitialiser le message d'avertissement lors de la saisie
                if (warningElement) {
                    warningElement.style.display = 'none';
                }
                productExists = false;

                // Enregistrer la valeur actuellement tapée
                originalValue = str;
            },

            onDropdownOpen: function(dropdown) {
                // Désactiver la sélection automatique de la première option
                this.setActiveOption(null);
            },

            onChange: function(value) {
                // Si la valeur est vide, ne rien faire
                if (!value) return;

                // Obtenir l'option sélectionnée
                const selectedOption = this.options[value];

                if (selectedOption && selectedOption.exists) {
                    // Produit existant sélectionné
                    productExists = true;

                    // Afficher l'avertissement
                    if (warningElement) {
                        warningElement.style.display = 'block';
                    }

                    // Remplir automatiquement les autres champs si disponibles
                    if (formulaField && selectedOption.formula) {
                        formulaField.value = selectedOption.formula;
                    }

                    if (casNumberField && selectedOption.casnumber) {
                        casNumberField.value = selectedOption.casnumber;
                    }
                } else {
                    // Nouveau produit
                    productExists = false;

                    // Cacher l'avertissement
                    if (warningElement) {
                        warningElement.style.display = 'none';
                    }

                    // Vider les champs automatiquement remplis
                    if (formulaField) {
                        formulaField.value = '';
                    }

                    if (casNumberField) {
                        casNumberField.value = '';
                    }
                }
            },

            render: {
                option: function(item, escape) {
                    return `<div class="py-2 px-3">
                        <div class="mb-1 font-weight-bold">${escape(item.text)}</div>
                        ${item.formula ? `<div class="small">${escape(item.formula)}</div>` : ''}
                        ${item.casnumber ? `<div class="small text-muted">CAS: ${escape(item.casnumber)}</div>` : ''}
                    </div>`;
                },
                item: function(item, escape) {
                    return `<div>${escape(item.text)}</div>`;
                }
            }
        });

        // Intercepter l'événement keydown sur le champ d'entrée
        tomSelect.control_input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && tomSelect.isOpen) {
                // Empêcher Tom Select de traiter la touche Entrée
                e.preventDefault();
                e.stopPropagation();

                // Fermer le dropdown manuellement
                tomSelect.close();

                // Vérifier d'abord si la valeur exacte existe dans les options
                const inputValue = tomSelect.control_input.value || originalValue;
                let foundExact = false;
                let exactOption = null;

                // Parcourir les options disponibles pour vérifier si le texte exact existe
                for (const key in tomSelect.options) {
                    const option = tomSelect.options[key];
                    if (option.text.toLowerCase() === inputValue.toLowerCase()) {
                        foundExact = true;
                        exactOption = option;
                        break;
                    }
                }

                if (foundExact && exactOption) {
                    // Si on trouve une correspondance exacte, utiliser cette option
                    tomSelect.clear();
                    tomSelect.addItem(exactOption.text);

                    // Mettre à jour l'état pour refléter qu'un produit existant a été sélectionné
                    productExists = true;
                    if (warningElement) {
                        warningElement.style.display = 'block';
                    }

                    // Remplir les champs associés
                    if (formulaField && exactOption.formula) {
                        formulaField.value = exactOption.formula;
                    }

                    if (casNumberField && exactOption.casnumber) {
                        casNumberField.value = exactOption.casnumber;
                    }
                } else if (inputValue) {
                    // Si pas de correspondance exacte, créer un nouvel élément
                    tomSelect.clear();
                    tomSelect.createItem(inputValue, true);
                }

                // Déplacer le focus au champ suivant si nécessaire
                if (formulaField) {
                    formulaField.focus();
                }
            }
        }, true);

        // Vérifier la soumission du formulaire
        const form = productNameField.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Si le champ est vide après avoir appuyé sur Entrée, utiliser la valeur originale
                if (!tomSelect.items.length && originalValue) {
                    tomSelect.createItem(originalValue, true);
                }

                if (productExists) {
                    // En mode admin, nous permettons la modification de produits existants sans confirmation
                    // car c'est une fonctionnalité attendue de l'interface d'administration
                }
            });
        }
    }

    // Configurer les sélecteurs multiples select2 existants avec Tom Select
    document.querySelectorAll('.select2').forEach(function(element) {
        new TomSelect(element, {
            plugins: ['remove_button'],
            create: false
        });
    });

    // Fonctionnalité pour la gestion des produits CMR
    function setupCMRAutoSelection() {
        // Récupérer les éléments radio pour CMR
        const cmrRadios = document.querySelectorAll('input[name="chimicalproduct[iscmr]"]');

        // Récupérer les sélecteurs pour les symboles de danger et types de produit
        const dangerSymbolSelect = document.querySelector('#chimicalproduct_idDangersymbol');
        const productTypeSelect = document.querySelector('#chimicalproduct_idType');

        // Vérifier que tous les éléments nécessaires sont présents
        if (!cmrRadios.length || !dangerSymbolSelect || !productTypeSelect) {
            console.error("Éléments du formulaire manquants pour la configuration CMR");
            return;
        }

        // Récupérer les instances Tom Select
        const dangerSymbolTomSelect = dangerSymbolSelect.tomselect;
        const productTypeTomSelect = productTypeSelect.tomselect;

        // S'assurer que les instances Tom Select sont disponibles
        if (!dangerSymbolTomSelect || !productTypeTomSelect) {
            console.error("Instances Tom Select non disponibles");
            return;
        }

        // Définir la fonction de mise à jour basée sur la sélection CMR
        function updateSelectionsBasedOnCMR(isCMR) {
            const cmrDangerSymbolId = "8"; // ID pour SGH08
            const cmrProductTypeId = "8";  // ID pour "Sensibilisant, cancérogène, mutagène, reprotoxique"

            if (isCMR) {
                // Ajouter SGH08 s'il n'est pas déjà sélectionné
                if (!dangerSymbolTomSelect.items.includes(cmrDangerSymbolId)) {
                    dangerSymbolTomSelect.addItem(cmrDangerSymbolId);
                }

                // Ajouter le type CMR s'il n'est pas déjà sélectionné
                if (!productTypeTomSelect.items.includes(cmrProductTypeId)) {
                    productTypeTomSelect.addItem(cmrProductTypeId);
                }
            }
            // Note: On ne supprime pas automatiquement les symboles si CMR est décochée,
            // car il peut y avoir d'autres raisons de les garder
        }

        // Ajouter les écouteurs d'événements aux boutons radio
        cmrRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Si la valeur est "1", c'est CMR Oui
                updateSelectionsBasedOnCMR(this.value === "1");
            });

            // Appliquer immédiatement si la case "Oui" est déjà cochée lors du chargement
            if (radio.checked && radio.value === "1") {
                updateSelectionsBasedOnCMR(true);
            }
        });
    }

    // Exécuter la configuration après que toutes les instances Tom Select sont initialisées
    setTimeout(setupCMRAutoSelection, 500);
});