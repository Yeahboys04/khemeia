import DataTable from '../components/datatable';
import DatePicker from '../components/datepicker';

document.addEventListener('DOMContentLoaded', () => {
    // Initialisation de la DataTable pour la liste des utilisateurs
    const userTable = document.querySelector('#user-table');
    if (userTable) {
        new DataTable(userTable, {
            perPage: 10,
            search: true,
            ordering: true,
            pagination: true,
            language: 'fr'
        });
    }

    // Initialisation du DatePicker pour le champ de date de fin de droit
    const endRightDateInput = document.querySelector('#form_endrightdate');
    if (endRightDateInput) {
        new DatePicker(endRightDateInput, {
            format: 'dd/mm/yyyy',
            language: 'fr',
            autoclose: true,
            clearBtn: true,
            todayHighlight: true
        });
    }
});