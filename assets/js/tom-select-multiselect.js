// assets/js/tom-select-multiselect.js
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap4.min.css';
import '../styles/components/tom-select-multiselect.scss';

// Fonction qui s'assurera que le DOM est chargé et que TomSelect est disponible
function initializeTomSelect() {
    // Vérifier si TomSelect est disponible
    if (typeof TomSelect === 'undefined') {
        console.error("TomSelect n'est pas disponible. Assurez-vous que la bibliothèque est chargée.");
        return;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Ajoutons un délai pour s'assurer que tous les éléments sont bien chargés et disponibles
        setTimeout(function() {
            // Ajout d'indications visuelles aux champs de sélection multiples
            const multiSelects = [
                '#chimicalproduct_idDangersymbol',
                '#chimicalproduct_idDangernote',
                '#chimicalproduct_idCautionaryadvice',
                '#chimicalproduct_idType'
            ];

            multiSelects.forEach(selectId => {
                const selectElement = document.querySelector(selectId);
                if (!selectElement) return;

                // Ajouter une classe pour indiquer que c'est un sélecteur multiple
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

            // Créer le plugin personnalisé pour le comportement des checkboxes
            TomSelect.define('checkbox_options', function() {
                return {
                    setup: function() {
                        const self = this;

                        // Gérer les clics sur les options
                        this.hook('after', 'setupTemplates', function() {
                            self.on('click', '.option-container', function(evt) {
                                // Empêcher la fermeture du dropdown
                                evt.stopPropagation();
                                evt.preventDefault();

                                // Récupérer la valeur de l'option
                                const value = this.getAttribute('data-value');
                                const checkbox = this.querySelector('input[type="checkbox"]');

                                // Inverser l'état de sélection
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

                        // Mettre à jour l'état des checkboxes lors de l'ajout d'un élément
                        this.on('item_add', function(value) {
                            const checkbox = self.dropdown.querySelector(`.option-container[data-value="${CSS.escape(value)}"] input[type="checkbox"]`);
                            if (checkbox) checkbox.checked = true;
                        });

                        // Mettre à jour l'état des checkboxes lors de la suppression d'un élément
                        this.on('item_remove', function(value) {
                            const checkbox = self.dropdown.querySelector(`.option-container[data-value="${CSS.escape(value)}"] input[type="checkbox"]`);
                            if (checkbox) checkbox.checked = false;
                        });
                    }
                };
            });

            // Pour les autres champs Tom Select standards (non multiples)
            // Version plus sûre: sélectionner explicitement les éléments select qui ont la classe tom-select
            const standardSelectElements = document.querySelectorAll('select.tom-select:not(#chimicalproduct_idDangersymbol):not(#chimicalproduct_idDangernote):not(#chimicalproduct_idCautionaryadvice):not(#chimicalproduct_idType)');

            standardSelectElements.forEach(function(element) {
                // Vérifier si l'élément existe et n'a pas déjà été initialisé par TomSelect
                if (element && !element.tomselect) {
                    try {
                        new TomSelect(element, {
                            plugins: ['remove_button'],
                            placeholder: element.dataset.placeholder || 'Sélectionnez...',
                            allowEmptyOption: true
                        });
                    } catch (e) {
                        console.error("Erreur lors de l'initialisation de TomSelect sur l'élément:", element, e);
                    }
                }
            });

            // Fonctionnalité pour la gestion des produits CMR
            setupCMRAutoSelection();
        }, 100); // Petit délai pour s'assurer que le DOM est complètement chargé
    });
}

function setupCMRAutoSelection() {
    // Déclaration des variables en dehors du bloc try pour qu'elles soient accessibles partout
    let cmrRadios, dangerSymbolSelect, productTypeSelect, dangerSymbolTomSelect, productTypeTomSelect;

    try {
        // Récupérer les éléments radio pour CMR
        cmrRadios = document.querySelectorAll('input[name="chimicalproduct[iscmr]"]');

        // Récupérer les sélecteurs pour les symboles de danger et types de produit
        dangerSymbolSelect = document.querySelector('#chimicalproduct_idDangersymbol');
        productTypeSelect = document.querySelector('#chimicalproduct_idType');

        // Vérifier que tous les éléments nécessaires sont présents
        if (!cmrRadios.length || !dangerSymbolSelect || !productTypeSelect) {
            console.warn("Éléments du formulaire manquants pour la configuration CMR - vérifiez les IDs des éléments");
            return;
        }

        // Récupérer les instances Tom Select
        dangerSymbolTomSelect = dangerSymbolSelect.tomselect;
        productTypeTomSelect = productTypeSelect.tomselect;

        // S'assurer que les instances Tom Select sont disponibles
        if (!dangerSymbolTomSelect || !productTypeTomSelect) {
            console.warn("Instances Tom Select non disponibles - assurez-vous que TomSelect a été initialisé correctement sur ces éléments");
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