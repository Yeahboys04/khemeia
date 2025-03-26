/**
 * StorageCard Modern Script
 * - Provides enhanced UI interactions
 * - Handles state transitions and animations
 * - Manages form validation and submission
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM selectors for performance
    const form = document.getElementById('storage-form');
    const formGroups = document.querySelectorAll('.form-group');
    const stateOptions = document.querySelectorAll('.state-option input');
    const stockUnitsSelect = document.getElementById('storagecard_resp_stockUnit');
    const capacityUnitsSelect = document.getElementById('storagecard_resp_capacityUnit');
    const fileInputs = document.querySelectorAll('.file-upload input');
    const sections = document.querySelectorAll('.form-section');

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (window.bootstrap) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Initialize and configure Select2 for enhanced dropdowns
     */
    function initSelect2() {
        if (window.jQuery && $.fn.select2) {
            $('.select2').select2({
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Aucun résultat trouvé";
                    },
                    searching: function() {
                        return "Recherche en cours...";
                    }
                },
            });

            // Enhance search experience with auto-focus
            $(document).on('select2:open', () => {
                setTimeout(() => {
                    document.querySelector('.select2-search__field')?.focus();
                }, 100);
            });
        }
    }

    /**
     * Initialize date pickers with enhanced UI
     */
    function initDatePickers() {
        if (window.jQuery && $.fn.datepicker) {
            $('.datepicker').datepicker({
                format: "dd/mm/yyyy",
                language: "fr",
                todayHighlight: true,
                autoclose: true,
                clearBtn: true,
                templates: {
                    leftArrow: '<i class="fas fa-chevron-left"></i>',
                    rightArrow: '<i class="fas fa-chevron-right"></i>'
                }
            }).on('show', function() {
                // Add animation class to the datepicker
                $('.datepicker-dropdown').addClass('animated fadeIn');
            });
        }
    }

    /**
     * Animate form elements entrance
     */
    function animateFormElements() {
        formGroups.forEach((group, index) => {
            setTimeout(() => {
                group.classList.add('visible');
            }, 100 * index);
        });
    }

    /**
     * Update unit options based on the selected state (solid or liquid)
     * Adapté pour fonctionner avec le formulaire Symfony
     */
    function updateUnitOptions() {
        // Récupérer la valeur sélectionnée du type d'état
        const stateType = document.querySelector('input[name="storagecard_resp[stateType]"]:checked')?.value;

        if (!stateType || !stockUnitsSelect || !capacityUnitsSelect) return;

        // Clear existing options
        while (stockUnitsSelect.firstChild) {
            stockUnitsSelect.removeChild(stockUnitsSelect.firstChild);
        }

        while (capacityUnitsSelect.firstChild) {
            capacityUnitsSelect.removeChild(capacityUnitsSelect.firstChild);
        }

        // Définir les options selon l'état physique
        let units = [];
        if (stateType === 'solid') {
            units = [
                { value: 'g', text: 'grammes (g)' },
                { value: 'mg', text: 'milligrammes (mg)' },
                { value: 'kg', text: 'kilogrammes (kg)' }
            ];
        } else {
            units = [
                { value: 'ml', text: 'millilitres (ml)' },
                { value: 'cl', text: 'centilitres (cl)' },
                { value: 'L', text: 'litres (L)' }
            ];
        }

        // Ajouter les options aux sélecteurs
        units.forEach(unit => {
            const stockOption = document.createElement('option');
            stockOption.value = unit.value;
            stockOption.textContent = unit.value; // Juste l'unité (g, ml, etc.) au lieu du texte long
            stockUnitsSelect.appendChild(stockOption);

            const capacityOption = document.createElement('option');
            capacityOption.value = unit.value;
            capacityOption.textContent = unit.value; // Juste l'unité
            capacityUnitsSelect.appendChild(capacityOption);
        });

        // Sélectionner la première option par défaut
        if (units.length > 0) {
            stockUnitsSelect.value = stateType === 'solid' ? 'g' : 'ml';
            capacityUnitsSelect.value = stateType === 'solid' ? 'g' : 'ml';
        }

        // Refresh Select2 instances if they exist
        if (window.jQuery && $.fn.select2) {
            $(stockUnitsSelect).trigger('change');
            $(capacityUnitsSelect).trigger('change');
        }
    }

    /**
     * Handle file input changes with visual feedback
     */
    function handleFileInputs() {
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const fileUpload = this.parentElement;
                const fileNameSpan = fileUpload.querySelector('.file-name');

                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    fileNameSpan.textContent = fileName;
                    fileUpload.classList.add('has-file');

                    // Add animation
                    fileNameSpan.classList.add('animated');
                    fileNameSpan.classList.add('fadeIn');

                    // Show success icon
                    const icon = fileUpload.querySelector('i');
                    icon.className = 'fas fa-check-circle text-success';
                } else {
                    fileUpload.classList.remove('has-file');
                    // Reset icon
                    const icon = fileUpload.querySelector('i');
                    icon.className = 'fas fa-cloud-upload-alt';
                }
            });
        });
    }



    /**
     * Show visual feedback for fields with validation errors
     */
    function handleValidation() {
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            // Handle the input validation state change
            input.addEventListener('invalid', function(e) {
                // Prevent default behavior
                e.preventDefault();

                // Add error class
                input.classList.add('is-invalid');
                const formGroup = input.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.add('has-error');

                    // Create error message if it doesn't exist
                    let errorMessage = formGroup.querySelector('.error-message');
                    if (!errorMessage) {
                        errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message text-danger mt-1 animated fadeIn';
                        formGroup.appendChild(errorMessage);
                    }

                    // Set error message
                    errorMessage.textContent = input.validationMessage || 'Ce champ est requis';

                    // Scroll to the first error
                    if (document.querySelector('.has-error')) {
                        document.querySelector('.has-error').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });

            // Clear error when input changes
            input.addEventListener('input', function() {
                input.classList.remove('is-invalid');
                const formGroup = input.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.remove('has-error');

                    const errorMessage = formGroup.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.textContent = '';
                    }
                }

            });
        });
    }

    /**
     * Add interactive effects for form sections
     */
    function enhanceSections() {
        sections.forEach((section, index) => {
            // Create a section number indicator
            const indicator = document.createElement('div');
            indicator.className = 'section-indicator';
            indicator.innerHTML = `<span>${index + 1}</span>`;

            // Check if the .section-header exists before appending
            const header = section.querySelector('.section-header');
            if (header) {
                header.prepend(indicator);
            }

            // Add intersection observer for animation when scrolling
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            section.classList.add('in-view');
                            observer.unobserve(section);
                        }
                    });
                }, { threshold: 0.1 });

                observer.observe(section);
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                section.classList.add('in-view');
            }
        });
    }

    /**
     * Add ripple effect to buttons
     */
    function addButtonEffects() {
        const buttons = document.querySelectorAll('.btn');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const rect = button.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;

                button.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple effect if it doesn't exist
        if (!document.getElementById('ripple-style')) {
            const style = document.createElement('style');
            style.id = 'ripple-style';
            style.textContent = `
        .btn {
          position: relative;
          overflow: hidden;
        }
        .ripple-effect {
          position: absolute;
          border-radius: 50%;
          background-color: rgba(255, 255, 255, 0.7);
          width: 100px;
          height: 100px;
          margin-top: -50px;
          margin-left: -50px;
          animation: ripple 0.6s;
          opacity: 0;
        }
        @keyframes ripple {
          from {
            transform: scale(0);
            opacity: 1;
          }
          to {
            transform: scale(3);
            opacity: 0;
          }
        }
      `;
            document.head.appendChild(style);
        }
    }

    /**
     * Handle form submission with animation
     */
    function handleFormSubmit() {
        form.addEventListener('submit', function(e) {
            // Validate form
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                // Trigger validation UI for all fields
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    const event = new Event('invalid', { cancelable: true });
                    field.dispatchEvent(event);
                });

                return false;
            }

            // Add loading state
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
                submitButton.disabled = true;
            }

            // Add a subtle loading overlay to the form
            const overlay = document.createElement('div');
            overlay.className = 'form-loading-overlay';
            overlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        backdrop-filter: blur(2px);
      `;

            const spinner = document.createElement('div');
            spinner.className = 'spinner';
            spinner.style.cssText = `
        width: 50px;
        height: 50px;
        border: 5px solid rgba(79, 70, 229, 0.2);
        border-radius: 50%;
        border-top-color: rgba(79, 70, 229, 1);
        animation: spin 1s infinite linear;
      `;

            const keyframes = document.createElement('style');
            keyframes.textContent = `
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
      `;
            document.head.appendChild(keyframes);

            overlay.appendChild(spinner);
            form.appendChild(overlay);

            // Let the form submit naturally
            return true;
        });
    }

    /**
     * Add drag and drop support for file inputs
     */
    function enhanceFileUploads() {
        const fileUploads = document.querySelectorAll('.file-upload');

        fileUploads.forEach(upload => {
            // Highlight drop area when file is dragged over
            upload.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            upload.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            upload.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');

                const fileInput = this.querySelector('input[type="file"]');
                const dt = e.dataTransfer;

                if (dt.files && dt.files.length) {
                    fileInput.files = dt.files;

                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(event);
                }
            });
        });

        // Add CSS for drag and drop
        if (!document.getElementById('file-upload-style')) {
            const style = document.createElement('style');
            style.id = 'file-upload-style';
            style.textContent = `
        .file-upload.dragover {
          background-color: rgba(79, 70, 229, 0.1);
          border-color: rgba(79, 70, 229, 0.5);
          transform: scale(1.02);
        }
      `;
            document.head.appendChild(style);
        }
    }

    /**
     * Style radio buttons and state selectors when using Symfony generated form
     */
    function enhanceRadioButtons() {
        // Style pour les sélecteurs d'état (Solide/Liquide)
        const stateLabels = document.querySelectorAll('.state-option label');
        const stateInputs = document.querySelectorAll('.state-option input');

        stateInputs.forEach((input, index) => {
            // Ajouter des styles d'interaction
            input.addEventListener('change', function() {
                // Retirer la classe active de tous les labels
                stateLabels.forEach(label => {
                    label.parentElement.classList.remove('active');
                });

                // Ajouter la classe active au label correspondant à l'input sélectionné
                if (this.checked) {
                    this.parentElement.classList.add('active');
                }

                // Mettre à jour les options d'unité quand le type d'état change
                updateUnitOptions();
            });

            // Initialiser les états actifs
            if (input.checked) {
                input.parentElement.classList.add('active');
            }
        });

        // Style pour les boutons radio standards
        const radioCards = document.querySelectorAll('.radio-card');

        radioCards.forEach(card => {
            const input = card.querySelector('input');

            if (input) {
                input.addEventListener('change', function() {
                    // Supprimer la classe active de tous les cartes du même groupe
                    const name = this.name;
                    document.querySelectorAll(`input[name="${name}"]`).forEach(groupInput => {
                        groupInput.closest('.radio-card').classList.remove('active');
                    });

                    // Ajouter la classe active à la carte actuelle
                    if (this.checked) {
                        card.classList.add('active');
                    }
                });

                // Initialiser les états actifs
                if (input.checked) {
                    card.classList.add('active');
                }
            }
        });
    }

    /**
     * Optimiser les sélecteurs d'unités
     */
    function optimizeUnitSelectors() {
        // Si Select2 est disponible
        if (window.jQuery && $.fn.select2) {
            // Configurer select2 pour les sélecteurs d'unité
            $('.unit-selector').select2({
                minimumResultsForSearch: Infinity, // Désactive la recherche
                dropdownAutoWidth: true,
                templateResult: formatUnit,
                templateSelection: formatUnit
            });

            // Fonction pour formater les options des unités
            function formatUnit(unit) {
                if (!unit.id) return unit.text;

                let unitText = unit.text;
                // Ajouter des classes ou styles selon le type d'unité
                return $('<span class="unit-option">' + unitText + '</span>');
            }
        }
        // S'assurer que les sélecteurs standards sont aussi optimisés
        const unitSelectors = document.querySelectorAll('.unit-selector');
        unitSelectors.forEach(selector => {
            // Ajouter des attributs pour améliorer l'apparence
            selector.setAttribute('data-dropdown-auto-width', 'true');
        });
    }
    /**
     * Vérifie et définit l'état physique par défaut (solide/liquide)
     */
    function setDefaultStateType() {
        // Vérifie si un état est déjà sélectionné
        const stateOptions = document.querySelectorAll('.state-option input');
        const anySelected = Array.from(stateOptions).some(option => option.checked);

        if (!anySelected) {
            // Si aucun état n'est sélectionné, sélectionner solide par défaut
            const solidOption = document.querySelector('input[name="storagecard_resp[stateType]"][value="solid"]');
            if (solidOption) {
                solidOption.checked = true;
                solidOption.parentElement.classList.add('active');

                // Déclencher l'événement change pour mettre à jour les unités
                const event = new Event('change');
                solidOption.dispatchEvent(event);
            }
        }
    }





    /**
     * Initialize all features
     */
    function init() {
        // Safety check for each initialization
        try {
            // Initialize UI components
            initSelect2();
            optimizeUnitSelectors();
            initDatePickers();
            animateFormElements();
            handleFileInputs();
            enhanceFileUploads();
            handleValidation();
            enhanceSections();
            enhanceRadioButtons();
            setDefaultStateType();

            // Add button effects
            try { addButtonEffects(); } catch (e) { console.log('Button effects not supported'); }

            handleFormSubmit();

            // Setup state type change event
            stateOptions.forEach(option => {
                option.addEventListener('change', updateUnitOptions);
            });

            // Initial setup for unit options
            updateUnitOptions();


        } catch (error) {
            console.error('Error initializing form:', error);
        }
    }

    // Initialize everything when DOM is ready
    init();
});

document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('.file-input');

    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const maxSize = this.getAttribute('data-max-size') || 3000000; // 3 Mo par défaut
            const fileSize = this.files[0]?.size || 0;
            const fileContainer = this.closest('.file-upload');
            const errorMessage = fileContainer.querySelector('.file-error') ||
                document.createElement('div');

            // Supprimer tout message d'erreur précédent
            if (fileContainer.querySelector('.file-error')) {
                fileContainer.querySelector('.file-error').remove();
            }

            if (fileSize > maxSize && fileSize > 0) {
                errorMessage.className = 'file-error text-danger';
                errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> Le fichier est trop volumineux (${(fileSize/1000000).toFixed(2)} Mo). La taille maximale autorisée est 3 Mo.`;
                fileContainer.appendChild(errorMessage);
                this.value = ''; // Vider l'input pour empêcher l'envoi du fichier

                // Masquer le nom du fichier
                const fileNameElement = fileContainer.querySelector('.file-name');
                if (fileNameElement) {
                    fileNameElement.textContent = '';
                }
                fileContainer.querySelector('.file-selected').style.display = 'none';
            }
        });
    });
});