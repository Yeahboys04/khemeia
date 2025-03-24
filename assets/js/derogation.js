document.addEventListener('DOMContentLoaded', function() {
    // Si nous sommes en mode dérogation
    // Trouver les selects originaux
    const productSelect = document.querySelector('[name="storagecard_resp[idChimicalproduct]"]');
    const shelvingunitSelect = document.querySelector('[name="storagecard_resp[idShelvingunit]"]');

    // Désactiver les selects d'origine
    if (productSelect) productSelect.disabled = true;
    if (shelvingunitSelect) shelvingunitSelect.disabled = true;

    // Traiter les composants EnhancedSelect créés
    const enhancedSelects = document.querySelectorAll('.khemeia-select');
    enhancedSelects.forEach(select => {
        // Trouver le header qui contrôle le dropdown
        const header = select.querySelector('.khemeia-select-header');
        if (header) {
            // Ajouter une classe de désactivation
            header.classList.add('disabled');

            // Supprimer tous les écouteurs d'événements en remplaçant l'élément
            const newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);

            // Désactiver les éléments d'interaction comme les boutons et les inputs
            const interactiveElements = select.querySelectorAll('button, input');
            interactiveElements.forEach(el => {
                el.disabled = true;
            });

            // Ajouter un style visuel pour indiquer qu'il est désactivé
            select.style.opacity = '0.7';
            select.style.pointerEvents = 'none';
        }
    });

    // Ajouter un message d'information
    const productContainer = productSelect?.closest('.form-group');
    const shelvingunitContainer = shelvingunitSelect?.closest('.form-group');

    if (productContainer) {
        const helpText = document.createElement('div');
        helpText.className = 'help-block text-info';
        helpText.textContent = 'Ce champ est verrouillé dans le cadre d\'une dérogation.';
        productContainer.appendChild(helpText);
    }

    if (shelvingunitContainer) {
        const helpText = document.createElement('div');
        helpText.className = 'help-block text-info';
        helpText.textContent = 'Ce champ est verrouillé dans le cadre d\'une dérogation.';
        shelvingunitContainer.appendChild(helpText);
    }

});