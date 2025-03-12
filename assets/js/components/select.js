// assets/js/components/select.js
import '../../styles/components/select.scss';

class EnhancedSelect {
    constructor(selector, options = {}) {
        this.select = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.select || this.select.tagName !== 'SELECT') return;

        this.options = {
            placeholder: options.placeholder || this.select.dataset.placeholder || 'Sélectionner...',
            searchable: options.searchable !== undefined ? options.searchable : true,
            clearable: options.clearable !== undefined ? options.clearable : true,
            multiple: this.select.multiple,
            ...options
        };

        this.isOpen = false;
        this.selectedOptions = [];

        this.init();
    }

    init() {
        // Cacher le select original
        this.select.style.display = 'none';

        // Créer le conteneur
        this.container = document.createElement('div');
        this.container.className = 'khemeia-select';
        this.select.parentNode.insertBefore(this.container, this.select);

        // Créer la partie visible du select
        this.createSelectHeader();

        // Créer le dropdown
        this.createDropdown();

        // Ajouter les événements
        this.addEventListeners();

        // Initialiser avec les valeurs sélectionnées
        this.initSelectedValues();
    }

    createSelectHeader() {
        this.header = document.createElement('div');
        this.header.className = 'khemeia-select-header';

        // Zone de sélection
        this.selectionArea = document.createElement('div');
        this.selectionArea.className = 'khemeia-select-selection';

        if (this.options.multiple) {
            // Container pour les tags en mode multiple
            this.tagContainer = document.createElement('div');
            this.tagContainer.className = 'khemeia-select-tags';
            this.selectionArea.appendChild(this.tagContainer);

            // Input de recherche en mode multiple
            if (this.options.searchable) {
                this.searchInput = document.createElement('input');
                this.searchInput.type = 'text';
                this.searchInput.className = 'khemeia-select-search';
                this.searchInput.placeholder = this.options.placeholder;
                this.selectionArea.appendChild(this.searchInput);
            } else {
                // Placeholder en mode multiple sans recherche
                this.placeholder = document.createElement('div');
                this.placeholder.className = 'khemeia-select-placeholder';
                this.placeholder.textContent = this.options.placeholder;
                this.selectionArea.appendChild(this.placeholder);
            }
        } else {
// Placeholder et sélection en mode simple
            this.singleSelection = document.createElement('div');
            this.singleSelection.className = 'khemeia-select-single-value placeholder';
            this.singleSelection.textContent = this.options.placeholder;
            this.selectionArea.appendChild(this.singleSelection);
        }

        this.header.appendChild(this.selectionArea);

        // Boutons (effacer, flèche)
        const actions = document.createElement('div');
        actions.className = 'khemeia-select-actions';

        if (this.options.clearable) {
            this.clearBtn = document.createElement('button');
            this.clearBtn.type = 'button';
            this.clearBtn.className = 'khemeia-select-clear';
            this.clearBtn.innerHTML = '&times;';
            this.clearBtn.style.display = 'none';
            this.clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.clear();
            });
            actions.appendChild(this.clearBtn);
        }

        this.arrowBtn = document.createElement('button');
        this.arrowBtn.type = 'button';
        this.arrowBtn.className = 'khemeia-select-arrow';
        this.arrowBtn.innerHTML = '&#9662;';
        actions.appendChild(this.arrowBtn);

        this.header.appendChild(actions);
        this.container.appendChild(this.header);
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'khemeia-select-dropdown';
        this.dropdown.style.display = 'none';

        // Barre de recherche en mode simple
        if (this.options.searchable && !this.options.multiple) {
            const searchContainer = document.createElement('div');
            searchContainer.className = 'khemeia-select-search-container';

            this.searchInput = document.createElement('input');
            this.searchInput.type = 'text';
            this.searchInput.className = 'khemeia-select-search';
            this.searchInput.placeholder = 'Rechercher...';

            searchContainer.appendChild(this.searchInput);
            this.dropdown.appendChild(searchContainer);
        }

        // Liste des options
        this.optionsList = document.createElement('div');
        this.optionsList.className = 'khemeia-select-options';

        // Créer les options
        Array.from(this.select.options).forEach(option => {
            const optionEl = document.createElement('div');
            optionEl.className = 'khemeia-select-option';
            optionEl.dataset.value = option.value;
            optionEl.textContent = option.textContent;

            if (option.disabled) {
                optionEl.classList.add('disabled');
            }

            // Ajouter la case à cocher pour le mode multiple
            if (this.options.multiple) {
                const checkbox = document.createElement('span');
                checkbox.className = 'khemeia-select-checkbox';
                optionEl.insertBefore(checkbox, optionEl.firstChild);
            }

            optionEl.addEventListener('click', () => {
                if (!option.disabled) {
                    this.selectOption(option.value, option.textContent);
                }
            });

            this.optionsList.appendChild(optionEl);
        });

        this.dropdown.appendChild(this.optionsList);
        this.container.appendChild(this.dropdown);
    }

    addEventListeners() {
        // Ouvrir/fermer le dropdown au clic sur le header
        this.header.addEventListener('click', () => this.toggle());

        // Fermer au clic à l'extérieur
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });

        // Recherche
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => this.filterOptions());

            // Empêcher la fermeture du dropdown au clic dans l'input
            this.searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // En mode multiple, ouvrir au focus de l'input
        if (this.options.multiple && this.searchInput) {
            this.searchInput.addEventListener('focus', () => this.open());
        }
    }

    initSelectedValues() {
        const selectedOptions = Array.from(this.select.selectedOptions);

        if (selectedOptions.length > 0) {
            selectedOptions.forEach(option => {
                this.selectOption(option.value, option.textContent, false);
            });

            if (this.options.clearable) {
                this.clearBtn.style.display = 'block';
            }
        }
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
        this.header.classList.add('active');
        this.arrowBtn.innerHTML = '&#9652;';

        // Positionner le dropdown
        const headerRect = this.header.getBoundingClientRect();
        this.dropdown.style.width = `${headerRect.width}px`;

        // Focus sur l'input de recherche si présent
        if (this.searchInput && this.isOpen) {
            setTimeout(() => this.searchInput.focus(), 0);
        }
    }

    close() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
        this.header.classList.remove('active');
        this.arrowBtn.innerHTML = '&#9662;';

        // Réinitialiser la recherche
        if (this.searchInput) {
            this.searchInput.value = '';
            this.filterOptions();
        }
    }

    selectOption(value, text, closeAfter = true) {
        // Mettre à jour le select original
        const option = Array.from(this.select.options).find(opt => opt.value === value);

        if (this.options.multiple) {
            // En mode multiple, ajouter ou supprimer de la sélection
            const index = this.selectedOptions.findIndex(opt => opt.value === value);

            if (index !== -1) {
                // Désélectionner
                this.selectedOptions.splice(index, 1);
                option.selected = false;

                // Supprimer le tag
                const tag = this.tagContainer.querySelector(`[data-value="${value}"]`);
                if (tag) {
                    this.tagContainer.removeChild(tag);
                }
            } else {
                // Sélectionner
                this.selectedOptions.push({ value, text });
                option.selected = true;

                // Ajouter un tag
                this.addTag(value, text);
            }

            // Mettre à jour la case à cocher dans la liste
            const optionEl = this.optionsList.querySelector(`[data-value="${value}"]`);
            if (optionEl) {
                optionEl.classList.toggle('selected');
            }

            // Réinitialiser le placeholder
            if (this.options.searchable) {
                this.searchInput.placeholder = this.selectedOptions.length > 0 ? '' : this.options.placeholder;
            } else if (this.placeholder) {
                this.placeholder.textContent = this.selectedOptions.length > 0 ? '' : this.options.placeholder;
                this.placeholder.style.display = this.selectedOptions.length > 0 ? 'none' : 'block';
            }
        } else {
            // En mode simple, remplacer la sélection
            this.selectedOptions = [{ value, text }];

            // Mettre à jour le select original
            Array.from(this.select.options).forEach(opt => {
                opt.selected = opt.value === value;
            });

            // Mettre à jour l'affichage
            this.singleSelection.textContent = text;
            this.singleSelection.classList.add('selected');
            this.singleSelection.classList.remove('placeholder');

            // Mettre à jour la classe selected dans la liste
            const options = this.optionsList.querySelectorAll('.khemeia-select-option');
            options.forEach(opt => {
                opt.classList.toggle('selected', opt.dataset.value === value);
            });

            // Fermer le dropdown
            if (closeAfter) {
                this.close();
            }
        }

        // Afficher le bouton clear si nécessaire
        if (this.options.clearable) {
            this.clearBtn.style.display = this.selectedOptions.length > 0 ? 'block' : 'none';
        }

        // Déclencher l'événement change
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
    }

    addTag(value, text) {
        const tag = document.createElement('div');
        tag.className = 'khemeia-select-tag';
        tag.dataset.value = value;

        const tagText = document.createElement('span');
        tagText.textContent = text;
        tag.appendChild(tagText);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'khemeia-select-tag-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectOption(value, text, false);
        });

        tag.appendChild(removeBtn);
        this.tagContainer.appendChild(tag);
    }

    clear() {
        // Désélectionner toutes les options
        Array.from(this.select.options).forEach(opt => {
            opt.selected = false;
        });

        this.selectedOptions = [];

        if (this.options.multiple) {
            // Vider les tags
            this.tagContainer.innerHTML = '';

            // Réinitialiser le placeholder
            if (this.options.searchable) {
                this.searchInput.placeholder = this.options.placeholder;
            } else if (this.placeholder) {
                this.placeholder.textContent = this.options.placeholder;
                this.placeholder.style.display = 'block';
            }
        } else {
            // Réinitialiser l'affichage en mode simple
            this.singleSelection.textContent = this.options.placeholder;
            this.singleSelection.classList.remove('selected');
            this.singleSelection.classList.add('placeholder');
        }

        // Masquer le bouton clear
        if (this.options.clearable) {
            this.clearBtn.style.display = 'none';
        }

        // Mettre à jour les classes dans la liste
        const options = this.optionsList.querySelectorAll('.khemeia-select-option');
        options.forEach(opt => {
            opt.classList.remove('selected');
        });

        // Déclencher l'événement change
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
    }

    filterOptions() {
        if (!this.searchInput) return;

        const searchText = this.searchInput.value.toLowerCase();
        const options = this.optionsList.querySelectorAll('.khemeia-select-option');

        let hasResults = false;

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            const isMatch = text.includes(searchText);
            option.style.display = isMatch ? 'flex' : 'none';

            if (isMatch) {
                hasResults = true;
            }
        });

        // Afficher un message si aucun résultat
        let noResults = this.optionsList.querySelector('.khemeia-select-no-results');

        if (!hasResults) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.className = 'khemeia-select-no-results';
                noResults.textContent = 'Aucun résultat trouvé';
                this.optionsList.appendChild(noResults);
            }
        } else if (noResults) {
            this.optionsList.removeChild(noResults);
        }
    }
}

// Export la classe pour l'utiliser ailleurs
export default EnhancedSelect;

// Initialisation automatique pour les éléments avec classe .select2
document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.select2');
    selects.forEach(select => {
        new EnhancedSelect(select, {
            placeholder: select.dataset.placeholder,
            searchable: select.dataset.searchable !== 'false',
            clearable: select.dataset.clearable !== 'false'
        });
    });
});