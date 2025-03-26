// assets/js/chemical-product-tom-select.js
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap4.min.css';
import '../styles/components/tom-select-multiselect.scss';
import '../styles/pages/product.scss';

// Fonction principale d'initialisation de TomSelect
function initializeTomSelect() {
    if (typeof TomSelect === 'undefined') {
        console.error("TomSelect n'est pas disponible. Assurez-vous que la bibliothèque est chargée.");
        return;
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            // Initialisation des sélecteurs multiples avec des symboles de danger, etc.
            const multiSelects = [
                '#chimicalproduct_idDangersymbol',
                '#chimicalproduct_idDangernote',
                '#chimicalproduct_idCautionaryadvice',
                '#chimicalproduct_idType'
            ];

            multiSelects.forEach(selectId => {
                const selectElement = document.querySelector(selectId);
                if (!selectElement) return;

                // Ajout d'indications visuelles
                const formGroup = selectElement.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.add('multiple-select-group');

                    // Ajouter un indicateur de sélection multiple au label
                    const label = formGroup.querySelector('.form-label');
                    if (label && !label.querySelector('.multiple-indicator')) {
                        const indicator = document.createElement('span');
                        indicator.className = 'multiple-indicator';
                        label.appendChild(indicator);
                    }

                    // Ajouter une instruction sous le champ
                    const instruction = document.createElement('div');
                    instruction.className = 'multiple-instruction';
                    instruction.innerHTML = '<small class="text-muted"><i class="fas fa-info-circle"></i> Cliquez pour afficher les options et sélectionnez-en plusieurs si nécessaire</small>';
                    formGroup.appendChild(instruction);
                }

                // Initialiser TomSelect avec le style checkbox
                try {
                    new TomSelect(selectElement, {
                        plugins: ['remove_button', 'checkbox_options'],
                        maxItems: null,
                        hideSelected: false,
                        closeAfterSelect: false,
                        placeholder: selectElement.dataset.placeholder || 'Sélectionnez...',
                        allowEmptyOption: true,
                        render: {
                            option: function(data, escape) {
                                return `<div class="option-container" data-value="${escape(data.value)}">
                                    <label class="custom-checkbox">
                                        <span class="checkbox-label">${escape(data.text)}</span>
                                    </label>
                                </div>`;
                            }
                        }
                    });
                } catch (error) {
                    console.error(`Erreur lors de l'initialisation de TomSelect pour ${selectId}:`, error);
                }
            });

            // Plugin personnalisé pour le comportement des checkboxes
            TomSelect.define('checkbox_options', function() {
                return {
                    setup: function() {
                        const self = this;

                        // Gérer les clics sur les options
                        this.hook('after', 'setupTemplates', function() {
                            self.on('click', '.option-container', function(evt) {
                                evt.stopPropagation();
                                evt.preventDefault();

                                const value = this.getAttribute('data-value');
                                const checkbox = this.querySelector('input[type="checkbox"]');

                                if (self.items.includes(value)) {
                                    self.removeItem(value);
                                    if (checkbox) checkbox.checked = false;
                                } else {
                                    self.addItem(value);
                                    if (checkbox) checkbox.checked = true;
                                }

                                return false;
                            });
                        });

                        // Mettre à jour l'état des checkboxes à l'ouverture du dropdown
                        this.on('dropdown_open', function() {
                            setTimeout(function() {
                                const checkboxes = self.dropdown.querySelectorAll('.option-checkbox');
                                checkboxes.forEach(checkbox => {
                                    const container = checkbox.closest('.option-container');
                                    if (container) {
                                        const value = container.getAttribute('data-value');
                                        checkbox.checked = self.items.includes(value);
                                    }
                                });
                            }, 10);
                        });

                        // Mettre à jour l'état des checkboxes lors de l'ajout/suppression d'éléments
                        this.on('item_add', function(value) {
                            const checkbox = self.dropdown.querySelector(`.option-container[data-value="${CSS.escape(value)}"] input[type="checkbox"]`);
                            if (checkbox) checkbox.checked = true;
                        });

                        this.on('item_remove', function(value) {
                            const checkbox = self.dropdown.querySelector(`.option-container[data-value="${CSS.escape(value)}"] input[type="checkbox"]`);
                            if (checkbox) checkbox.checked = false;
                        });
                    }
                };
            });

            // Initialisation des autres champs Tom Select standards
            const standardSelectElements = document.querySelectorAll('select.tom-select:not(#chimicalproduct_idDangersymbol):not(#chimicalproduct_idDangernote):not(#chimicalproduct_idCautionaryadvice):not(#chimicalproduct_idType)');

            standardSelectElements.forEach(function(element) {
                if (element && !element.tomselect) {
                    try {
                        new TomSelect(element, {
                            plugins: ['remove_button'],
                            create: false,
                            placeholder: element.dataset.placeholder || 'Sélectionnez...',
                            allowEmptyOption: true
                        });
                    } catch (e) {
                        console.error("Erreur lors de l'initialisation de TomSelect sur l'élément:", element, e);
                    }
                }
            });

            // Initialisation du champ de recherche de produit
            const productNameField = document.querySelector('#chimicalproduct_nameChimicalproduct');

            if (productNameField) {
                const formulaField = document.querySelector('#chimicalproduct_formula');
                const casNumberField = document.querySelector('#chimicalproduct_casnumber');

                let productExists = false;
                let warningElement = null;

                // Création de l'élément d'avertissement
                function createWarning() {
                    if (!warningElement) {
                        warningElement = document.createElement('div');
                        warningElement.className = 'alert alert-warning mt-2';
                        warningElement.style.display = 'none';
                        warningElement.innerHTML = 'Ce produit existe déjà dans la base de données.';
                        productNameField.parentNode.appendChild(warningElement);
                    }
                }

                createWarning();

                // Stocker la valeur originale avant l'ouverture du dropdown
                let originalValue = '';

                // Initialiser Tom Select pour le champ de nom de produit
                const tomSelect = new TomSelect(productNameField, {
                    valueField: 'text',
                    labelField: 'text',
                    searchField: ['text'],
                    create: true,
                    maxItems: 1,
                    closeAfterSelect: true,
                    hideSelected: true,

                    // Configuration pour les suggestions
                    load: function(query, callback) {
                        if (!query.length || query.length < 2) return callback();

                        fetch(`/api/search/products?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(json => {
                                callback(json);
                            })
                            .catch(() => {
                                callback();
                            });
                    },

                    onFocus: function() {
                        originalValue = this.items[0] || '';
                    },

                    onType: function(str) {
                        if (warningElement) {
                            warningElement.style.display = 'none';
                        }
                        productExists = false;
                        originalValue = str;
                    },

                    onDropdownOpen: function(dropdown) {
                        this.setActiveOption(null);
                    },

                    onChange: function(value) {
                        if (!value) return;

                        const selectedOption = this.options[value];

                        if (selectedOption && selectedOption.exists) {
                            productExists = true;

                            if (warningElement) {
                                warningElement.style.display = 'block';
                            }

                            if (formulaField && selectedOption.formula) {
                                formulaField.value = selectedOption.formula;
                            }

                            if (casNumberField && selectedOption.casnumber) {
                                casNumberField.value = selectedOption.casnumber;
                            }
                        } else {
                            productExists = false;

                            if (warningElement) {
                                warningElement.style.display = 'none';
                            }

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

                // Gestion de la touche Entrée
                tomSelect.control_input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && tomSelect.isOpen) {
                        e.preventDefault();
                        e.stopPropagation();
                        tomSelect.close();

                        const inputValue = tomSelect.control_input.value || originalValue;
                        let foundExact = false;
                        let exactOption = null;

                        for (const key in tomSelect.options) {
                            const option = tomSelect.options[key];
                            if (option.text.toLowerCase() === inputValue.toLowerCase()) {
                                foundExact = true;
                                exactOption = option;
                                break;
                            }
                        }

                        if (foundExact && exactOption) {
                            tomSelect.clear();
                            tomSelect.addItem(exactOption.text);

                            productExists = true;
                            if (warningElement) {
                                warningElement.style.display = 'block';
                            }

                            if (formulaField && exactOption.formula) {
                                formulaField.value = exactOption.formula;
                            }

                            if (casNumberField && exactOption.casnumber) {
                                casNumberField.value = exactOption.casnumber;
                            }
                        } else if (inputValue) {
                            tomSelect.clear();
                            tomSelect.createItem(inputValue, true);
                        }

                        if (formulaField) {
                            formulaField.focus();
                        }
                    }
                }, true);

                // Vérification de la soumission du formulaire
                const form = productNameField.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (!tomSelect.items.length && originalValue) {
                            tomSelect.createItem(originalValue, true);
                        }

                        if (productExists) {
                            if (!confirm('Ce produit existe déjà dans la base de données. Voulez-vous vraiment créer un doublon?')) {
                                e.preventDefault();
                            }
                        }
                    });
                }
            }

            // Configuration des produits CMR
            setupCMRAutoSelection();
        }, 100);
    });
}

// Fonction pour la gestion des produits CMR
function setupCMRAutoSelection() {
    try {
        // Récupérer les éléments radio pour CMR
        const cmrRadios = document.querySelectorAll('input[name="chimicalproduct[iscmr]"]');

        // Récupérer les sélecteurs pour les symboles de danger et types de produit
        const dangerSymbolSelect = document.querySelector('#chimicalproduct_idDangersymbol');
        const productTypeSelect = document.querySelector('#chimicalproduct_idType');

        // Vérifier que tous les éléments nécessaires sont présents
        if (!cmrRadios.length || !dangerSymbolSelect || !productTypeSelect) {
            console.warn("Éléments du formulaire manquants pour la configuration CMR");
            return;
        }

        // Récupérer les instances Tom Select
        const dangerSymbolTomSelect = dangerSymbolSelect.tomselect;
        const productTypeTomSelect = productTypeSelect.tomselect;

        // S'assurer que les instances Tom Select sont disponibles
        if (!dangerSymbolTomSelect || !productTypeTomSelect) {
            console.warn("Instances Tom Select non disponibles");
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
    } catch (error) {
        console.error("Erreur lors de la configuration CMR:", error);
    }
}

// Appeler la fonction d'initialisation
initializeTomSelect();