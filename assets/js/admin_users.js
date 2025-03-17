// assets/js/admin_users.js

// Import des styles
import '../scss/admin_users.scss';

// Import de Flatpickr (sélecteur de date moderne)
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

// Import de Vanilla-DataTables (alternative à jQuery DataTables)
import DataTable from 'vanilla-datatables';

// Import pour les exports
import { jsPDF } from 'jspdf';
import 'jspdf-autotable';

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du DatePicker
    initDatePicker();

    // Initialisation du DataTable
    initDataTable();

    // Validation des formulaires
    initFormValidation();
});

/**
 * Initialise les champs de date avec Flatpickr
 */
function initDatePicker() {
    const dateInputs = document.querySelectorAll('.datepicker');

    if (dateInputs.length > 0) {
        dateInputs.forEach(input => {
            flatpickr(input, {
                locale: French,
                dateFormat: 'd/m/Y',
                allowInput: true,
                altInput: true,
                altFormat: 'd/m/Y'
            });
        });
    }
}

/**
 * Initialise le tableau avec Vanilla-DataTables
 */
function initDataTable() {
    const tableElement = document.getElementById('users-table');

    if (tableElement) {
        // Ajout des attributs data-sort aux en-têtes
        const headers = tableElement.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            // Pas d'attribut de tri pour la colonne d'actions
            if (index < headers.length - 1) {
                header.setAttribute('data-sort', index === 5 ? 'date' : 'string');
            }
        });

        // Configuration du DataTable
        const dataTable = new DataTable(tableElement, {
            perPage: 10,
            perPageSelect: [5, 10, 20, 50],
            searchable: false, // On utilisera notre propre champ de recherche
            sortable: true,
            fixedColumns: true,
            labels: {
                placeholder: "Rechercher...",
                perPage: "Afficher :",
                noRows: "Aucun utilisateur trouvé",
                info: "Affichage de {start} à {end} sur {rows} utilisateurs",
                infoFiltered: "({rows} filtrés sur {rowsTotal} utilisateurs)"
            },
            layout: {
                top: "",
                bottom: "{info}{pager}"
            },
            pagerDelta: 2
        });

        // Branchement de notre champ de recherche personnalisé
        const searchInput = document.getElementById('user-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                dataTable.search(this.value);
            });
        }

        // Configuration de l'export Excel
        const exportExcelBtn = document.getElementById('export-excel');
        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', function() {
                exportTableToExcel(tableElement, 'utilisateurs');
            });
        }

        // Configuration de l'export PDF
        const exportPdfBtn = document.getElementById('export-pdf');
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', function() {
                exportTableToPDF(tableElement, 'utilisateurs');
            });
        }

        // Configuration de l'impression
        const printBtn = document.getElementById('print-table');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                printTable(tableElement);
            });
        }

        // Déplacer la pagination dans notre conteneur personnalisé
        const paginationContainer = document.getElementById('table-pagination');
        if (paginationContainer) {
            const observer = new MutationObserver(function() {
                const originalInfo = document.querySelector('.dataTable-info');
                const originalPagination = document.querySelector('.dataTable-pagination');

                if (originalInfo && originalPagination) {
                    paginationContainer.innerHTML = '';
                    const infoElement = document.createElement('div');
                    infoElement.classList.add('table-info');
                    infoElement.innerHTML = originalInfo.innerHTML;

                    paginationContainer.appendChild(infoElement);
                    paginationContainer.appendChild(originalPagination.cloneNode(true));
                }
            });

            observer.observe(document.querySelector('.dataTable-bottom'), { childList: true, subtree: true });
        }
    }
}

/**
 * Export le tableau en Excel
 */
function exportTableToExcel(table, filename) {
    // Récupération des données
    const data = getTableData(table);

    // Création du tableau Excel
    let csvContent = '';

    // Ajout des en-têtes
    csvContent += data.headers.join(',') + '\n';

    // Ajout des données
    data.rows.forEach(row => {
        csvContent += row.join(',') + '\n';
    });

    // Création du blob et téléchargement
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Export le tableau en PDF
 */
function exportTableToPDF(table, filename) {
    const data = getTableData(table);

    // Création du document PDF
    const doc = new jsPDF();

    // Titre
    doc.setFontSize(16);
    doc.text('Liste des utilisateurs', 14, 15);

    // Sous-titre avec date
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, 14, 22);

    // Ajout du tableau
    doc.autoTable({
        head: [data.headers],
        body: data.rows,
        startY: 30,
        styles: { fontSize: 9 },
        headStyles: { fillColor: [13, 110, 253], textColor: [255, 255, 255] }
    });

    // Enregistrement
    doc.save(`${filename}.pdf`);
}

/**
 * Impression du tableau
 */
function printTable(table) {
    const printWindow = window.open('', '_blank');

    // Style pour l'impression
    const style = `
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { font-size: 18px; margin-bottom: 10px; }
            .info { font-size: 12px; color: #666; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; }
            th { background-color: #f1f1f1; padding: 8px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .print-buttons { margin: 20px 0; }
            .print-buttons button { padding: 6px 12px; margin-right: 5px; cursor: pointer; }
            @media print { .print-buttons { display: none; } }
        </style>
    `;

    // Récupération des données du tableau
    const data = getTableData(table);

    // Corps HTML à imprimer
    let tableHtml = `
        <table>
            <thead>
                <tr>
                    ${data.headers.map(header => `<th>${header}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${data.rows.map(row => `
                    <tr>
                        ${row.map(cell => `<td>${cell}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    // Document complet
    printWindow.document.write(`
        <html>
        <head>
            <title>Liste des utilisateurs</title>
            ${style}
        </head>
        <body>
            <h1>Liste des utilisateurs</h1>
            <div class="info">Document généré le ${new Date().toLocaleDateString('fr-FR')}</div>
            <div class="print-buttons">
                <button onclick="window.print()">Imprimer</button>
                <button onclick="window.close()">Fermer</button>
            </div>
            ${tableHtml}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
}
