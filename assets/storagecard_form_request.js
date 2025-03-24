document.addEventListener('DOMContentLoaded', function() {
    console.log('Document fdjlfdsnloaded');
    // Assurez-vous que les champs restent désactivés même si l'utilisateur tente de les modifier
    const productField = document.querySelector('#{{ form.idChimicalproduct.vars.id }}');
    const locationField = document.querySelector('#{{ form.idShelvingunit.vars.id }}');

    if (productField) {
        productField.disabled = true;
    }

    if (locationField) {
        locationField.disabled = true;
    }

});