{
    let yesRadioButtonElement = document.getElementById('quantity_ismoved_0')
    let noRadioButtonElement = document.getElementById('quantity_ismoved_1')
    let localisationElement = document.getElementById('localisation');
    let yesOpenRadioButtonElement = document.getElementById('quantity_isopened_0')
    let noOpenRadioButtonElement = document.getElementById('quantity_isopened_1')
    let openLocalisationElement = document.getElementById('open');
    let expLocalisationElement = document.getElementById('expiration');

    window.addEventListener("DOMContentLoaded", (event) => {
        yesRadioButtonElement.addEventListener ("click", OnRadioStateChange);
        noRadioButtonElement.addEventListener ("click", OnRadioStateChange);
        yesOpenRadioButtonElement.addEventListener ("click", openOnRadioStateChange);
        noOpenRadioButtonElement.addEventListener ("click", openOnRadioStateChange);
    })

    function OnRadioStateChange (event) {
        let radio = event.target;

        if (radio === document.getElementById('quantity_ismoved_0')) {
            localisationElement.style.display = "block";
        }
        else{
            localisationElement.style.display = "none";
        }
    }

    function openOnRadioStateChange (event) {
        let radio = event.target;

        if (radio === document.getElementById('quantity_isopened_0')) {
            openLocalisationElement.style.display = "block";
            expLocalisationElement.style.display = "block";
        }
        else{
            openLocalisationElement.style.display = "none";
            expLocalisationElement.style.display = "none";
        }
    }
}
