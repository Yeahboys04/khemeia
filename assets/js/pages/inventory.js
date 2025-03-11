
class InventoryManager {
    constructor() {
        // Éléments DOM
        this.table = document.getElementById('inventoryTable');
        this.searchInput = document.getElementById('searchInput');
        this.pageSizeSelect = document.getElementById('pageSizeSelect');
        this.paginationContainer = document.getElementById('pagination');
        this.startRecordSpan = document.getElementById('startRecord');
        this.endRecordSpan = document.getElementById('endRecord');
        this.totalRecordsSpan = document.getElementById('totalRecords');
        this.loadingIndicator = document.getElementById('loadingIndicator');

        // État
        this.currentPage = 1;
        this.pageSize = 10;
        this.sortColumn = 'id';
        this.sortDirection = 'asc';
        this.originalRows = [];
        this.filteredRows = [];
        this.searchTimeout = null;

        this.init();
    }

    init() {
        if (!this.table) {
            console.warn('Table d\'inventaire non trouvée dans le DOM');
            return;
        }

        // Charger les lignes originales
        const tbody = this.table.querySelector('tbody');
        this.originalRows = Array.from(tbody.querySelectorAll('tr')).filter(
            // Ignorer les lignes avec message "empty-state"
            row => !row.querySelector('.empty-state')
        );
        this.filteredRows = [...this.originalRows];
        this.updateTotalRecords();

        // Configurer les événements
        this.setupEventListeners();

        // Afficher les données initiales
        this.renderTable();

        // Tri initial
        this.sortTable('id');
    }

    setupEventListeners() {
        // Tri des colonnes
        const headers = this.table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                this.sortTable(column);
            });
        });

        // Recherche avec délai
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.filterTable(this.searchInput.value);
            }, 300);
        });

        // Taille de page
        this.pageSizeSelect.addEventListener('change', () => {
            this.pageSize = parseInt(this.pageSizeSelect.value);
            this.currentPage = 1;
            this.renderTable();
        });

        // Confirmation pour l'archivage
        document.addEventListener('click', (e) => {
            if (e.target.closest('.archive-btn')) {
                if (!confirm('Êtes-vous sûr de vouloir archiver cette fiche de stockage ?')) {
                    e.preventDefault();
                }
            }
        });

        // Raccourcis clavier
        document.addEventListener('keydown', (e) => {
            // Ctrl+F / Cmd+F (focus sur la recherche)
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                if (!e.target.matches('input, textarea')) {
                    e.preventDefault();
                    this.searchInput.focus();
                }
            }

            // Échap (effacer la recherche)
            if (e.key === 'Escape' && document.activeElement === this.searchInput) {
                this.searchInput.value = '';
                this.filterTable('');
                this.searchInput.blur();
            }
        });
    }

    showLoading() {
        this.loadingIndicator.classList.add('active');
    }

    hideLoading() {
        this.loadingIndicator.classList.remove('active');
    }

    sortTable(column) {
        this.showLoading();

        // Réinitialiser toutes les classes de tri des en-têtes
        const headers = this.table.querySelectorAll('th');
        headers.forEach(header => {
            header.classList.remove('sorted-asc', 'sorted-desc');
        });

        // Si on clique sur la même colonne, on inverse le tri
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }

        // Appliquer la classe de tri à l'en-tête actif
        const currentHeader = this.table.querySelector(`th[data-sort="${column}"]`);
        if (currentHeader) {
            currentHeader.classList.add(this.sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
        }

        // Tri des lignes
        this.filteredRows.sort((a, b) => {
            const cellA = a.querySelector(`td[data-title="${column}"]`) ||
                a.querySelector(`td:nth-child(${this.getColumnIndex(column)})`);
            const cellB = b.querySelector(`td[data-title="${column}"]`) ||
                b.querySelector(`td:nth-child(${this.getColumnIndex(column)})`);

            let valueA = cellA ? cellA.textContent.trim() : '';
            let valueB = cellB ? cellB.textContent.trim() : '';

            // Gérer les valeurs spéciales comme "--"
            if (valueA === '--') valueA = '';
            if (valueB === '--') valueB = '';

            // Traitement spécial pour la colonne pureté
            if (column === 'purete') {
                return this.comparePurity(valueA, valueB);
            }
            // Vérifier si c'est une date (format dd/mm/yyyy)
            else if (/^\d{2}\/\d{2}\/\d{4}$/.test(valueA) && /^\d{2}\/\d{2}\/\d{4}$/.test(valueB)) {
                return this.compareDates(valueA, valueB);
            }
            // Vérifier si c'est un nombre
            else if (!isNaN(parseFloat(valueA)) && !isNaN(parseFloat(valueB))) {
                return this.compareNumbers(valueA, valueB);
            }
            // Sinon, comparer comme des chaînes
            else {
                return this.compareStrings(valueA, valueB);
            }
        });

        setTimeout(() => {
            this.renderTable();
            this.hideLoading();
        }, 100);
    }

    compareDates(dateA, dateB) {
        const partsA = dateA.split('/');
        const partsB = dateB.split('/');
        const jsDateA = new Date(+partsA[2], +partsA[1] - 1, +partsA[0]);
        const jsDateB = new Date(+partsB[2], +partsB[1] - 1, +partsB[0]);

        // Gestion des valeurs vides
        if (isNaN(jsDateA.getTime()) && isNaN(jsDateB.getTime())) return 0;
        if (isNaN(jsDateA.getTime())) return this.sortDirection === 'asc' ? 1 : -1;
        if (isNaN(jsDateB.getTime())) return this.sortDirection === 'asc' ? -1 : 1;

        // Comparaison standard
        if (jsDateA < jsDateB) return this.sortDirection === 'asc' ? -1 : 1;
        if (jsDateA > jsDateB) return this.sortDirection === 'asc' ? 1 : -1;
        return 0;
    }

    compareNumbers(a, b) {
        // Nettoyer les valeurs : enlever tous les % et ne garder que les chiffres
        const numA = parseFloat(a.replace(/%/g, '').replace(/[^\d.-]/g, ''));
        const numB = parseFloat(b.replace(/%/g, '').replace(/[^\d.-]/g, ''));

        // Gestion des valeurs non numériques
        if (isNaN(numA) && isNaN(numB)) {
            // Si les deux ne sont pas des nombres, les comparer comme des chaînes
            return this.compareStrings(a, b);
        }
        if (isNaN(numA)) return this.sortDirection === 'asc' ? 1 : -1;
        if (isNaN(numB)) return this.sortDirection === 'asc' ? -1 : 1;

        // Comparaison standard
        if (numA < numB) return this.sortDirection === 'asc' ? -1 : 1;
        if (numA > numB) return this.sortDirection === 'asc' ? 1 : -1;
        return 0;
    }

    compareStrings(a, b) {
        a = a.toLowerCase();
        b = b.toLowerCase();

        // Gestion des chaînes vides
        if (a === '' && b === '') return 0;
        if (a === '') return this.sortDirection === 'asc' ? 1 : -1;
        if (b === '') return this.sortDirection === 'asc' ? -1 : 1;

        // Comparaison standard
        if (a < b) return this.sortDirection === 'asc' ? -1 : 1;
        if (a > b) return this.sortDirection === 'asc' ? 1 : -1;
        return 0;
    }

    // Nouvelle méthode pour comparer les valeurs de pureté spécifiquement
    comparePurity(a, b) {
        // Vérifier si les valeurs sont vides
        const aEmpty = !a || a === 'Non spécifié';
        const bEmpty = !b || b === 'Non spécifié';

        // Si les deux sont vides
        if (aEmpty && bEmpty) return 0;

        // Si seulement une est vide - les valeurs vides sont toujours en dernier lors du tri ascendant
        // et en premier lors du tri descendant
        if (aEmpty) return this.sortDirection === 'asc' ? 1 : -1;
        if (bEmpty) return this.sortDirection === 'asc' ? -1 : 1;

        // Extraire les valeurs numériques, en ignorant les symboles comme >= et les espaces
        const extractNumber = (str) => {
            const match = str.match(/\d+(\.\d+)?/);
            return match ? parseFloat(match[0]) : NaN;
        };

        const numA = extractNumber(a);
        const numB = extractNumber(b);

        // Si les deux ont des valeurs numériques détectables
        if (!isNaN(numA) && !isNaN(numB)) {
            // Vérifier si a contient >=
            const aHasGreaterEqual = a.includes('>=');
            // Vérifier si b contient >=
            const bHasGreaterEqual = b.includes('>=');

            // Si les valeurs numériques sont égales mais l'un a un préfixe >=
            if (numA === numB && (aHasGreaterEqual !== bHasGreaterEqual)) {
                return aHasGreaterEqual ?
                    (this.sortDirection === 'asc' ? 1 : -1) :
                    (this.sortDirection === 'asc' ? -1 : 1);
            }

            // Sinon, comparer les valeurs numériques
            if (numA < numB) return this.sortDirection === 'asc' ? -1 : 1;
            if (numA > numB) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        }

        // Si une seule valeur a un nombre détectable
        if (!isNaN(numA)) return this.sortDirection === 'asc' ? -1 : 1;
        if (!isNaN(numB)) return this.sortDirection === 'asc' ? 1 : -1;

        // Si aucune n'a de nombre détectable, comparer comme des chaînes
        return this.compareStrings(a, b);
    }

    getColumnIndex(columnName) {
        const headers = Array.from(this.table.querySelectorAll('th'));
        for (let i = 0; i < headers.length; i++) {
            if (headers[i].getAttribute('data-sort') === columnName) {
                return i + 1;
            }
        }
        return 1; // Par défaut, première colonne
    }

    filterTable(searchTerm) {
        this.showLoading();
        searchTerm = searchTerm.toLowerCase();

        setTimeout(() => {
            if (searchTerm === '') {
                this.filteredRows = [...this.originalRows];
            } else {
                this.filteredRows = this.originalRows.filter(row => {
                    const cells = Array.from(row.querySelectorAll('td'));
                    return cells.some(cell =>
                        cell.textContent.toLowerCase().includes(searchTerm)
                    );
                });
            }

            this.currentPage = 1;
            this.updateTotalRecords();
            this.renderTable();
            this.hideLoading();
        }, 100);
    }

    renderTable() {
        // Calculer les indices de début et de fin pour la pagination
        const startIndex = (this.currentPage - 1) * this.pageSize;
        const endIndex = Math.min(startIndex + this.pageSize, this.filteredRows.length);

        // Mettre à jour les informations de pagination
        this.startRecordSpan.textContent = this.filteredRows.length > 0 ? startIndex + 1 : 0;
        this.endRecordSpan.textContent = endIndex;

        // Vider le tbody
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';

        // Vérifier s'il y a des résultats
        if (this.filteredRows.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="11">
                    <div class="empty-state">
                        <i class="fa fa-search"></i>
                        <h3>Aucun résultat trouvé</h3>
                        <p>Essayez de modifier votre recherche ou de réinitialiser les filtres.</p>
                        <button class="btn btn-default" id="resetFilters">
                            <i class="fa fa-refresh"></i> Réinitialiser les filtres
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(emptyRow);

            // Ajouter un événement pour réinitialiser les filtres
            document.getElementById('resetFilters')?.addEventListener('click', () => {
                this.searchInput.value = '';
                this.filterTable('');
            });
        } else {
            // Ajouter les lignes filtrées et triées
            for (let i = startIndex; i < endIndex; i++) {
                tbody.appendChild(this.filteredRows[i].cloneNode(true));
            }
        }

        // Mettre à jour la pagination
        this.renderPagination();
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredRows.length / this.pageSize);
        this.paginationContainer.innerHTML = '';

        // Pas de pagination si une seule page ou aucun résultat
        if (totalPages <= 1) return;

        // Bouton "Précédent"
        this.createPaginationItem('prev', '<i class="fa fa-chevron-left"></i>', this.currentPage > 1, () => {
            this.currentPage--;
            this.renderTable();
        });

        // Premier bouton (toujours présent si on n'est pas à la première page)
        if (this.currentPage > 2) {
            this.createPaginationItem('first', '1', true, () => {
                this.currentPage = 1;
                this.renderTable();
            });

            // Ellipsis si nécessaire
            if (this.currentPage > 3) {
                this.createPaginationItem('ellipsis-start', '...', false);
            }
        }

        // Pages autour de la page courante
        const range = 2; // Nombre de pages à montrer avant/après la page courante
        const start = Math.max(1, this.currentPage - range);
        const end = Math.min(totalPages, this.currentPage + range);

        for (let i = start; i <= end; i++) {
            this.createPaginationItem(`page-${i}`, i.toString(), true, () => {
                this.currentPage = i;
                this.renderTable();
            }, i === this.currentPage);
        }

        // Dernier bouton (toujours présent si on n'est pas à la dernière page)
        if (this.currentPage < totalPages - 1) {
            // Ellipsis si nécessaire
            if (this.currentPage < totalPages - 2) {
                this.createPaginationItem('ellipsis-end', '...', false);
            }

            this.createPaginationItem('last', totalPages.toString(), true, () => {
                this.currentPage = totalPages;
                this.renderTable();
            });
        }

        // Bouton "Suivant"
        this.createPaginationItem('next', '<i class="fa fa-chevron-right"></i>', this.currentPage < totalPages, () => {
            this.currentPage++;
            this.renderTable();
        });
    }

    createPaginationItem(id, html, enabled, onClick = null, isActive = false) {
        const li = document.createElement('li');
        li.className = isActive ? 'active' : '';

        const a = document.createElement('a');
        a.href = '#';
        a.innerHTML = html;

        if (!enabled) {
            a.className = 'disabled';
            a.style.pointerEvents = 'none';
            a.style.opacity = '0.6';
        } else if (onClick) {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                onClick();
            });
        }

        li.appendChild(a);
        this.paginationContainer.appendChild(li);
    }

    updateTotalRecords() {
        if (this.totalRecordsSpan) {
            this.totalRecordsSpan.textContent = this.filteredRows.length;
        }
    }

    exportToCSV() {
        // Récupérer les en-têtes
        const headers = Array.from(this.table.querySelectorAll('th:not(:last-child)'))
            .map(header => header.textContent.trim());

        // Récupérer les données
        const rows = [];
        this.filteredRows.forEach(row => {
            const rowData = Array.from(row.querySelectorAll('td:not(:last-child)'))
                .map(cell => {
                    // Nettoyer le contenu
                    let content = cell.textContent.trim();
                    // Échapper les guillemets doubles
                    content = content.replace(/"/g, '""');
                    // Entourer de guillemets si nécessaire
                    if (content.includes(',') || content.includes('"') || content.includes('\n')) {
                        content = `"${content}"`;
                    }
                    return content;
                });
            rows.push(rowData.join(','));
        });

        // Assembler le CSV
        const csv = [
            headers.join(','),
            ...rows
        ].join('\n');

        // Créer un blob et un lien de téléchargement
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', `inventaire-export-${new Date().toISOString().slice(0, 10)}.csv`);
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    printTable() {
        window.print();
    }

    highlightRow(rowId) {
        const row = document.querySelector(`tr[data-id="${rowId}"]`);
        if (row) {
            row.classList.add('highlight-animation');
            setTimeout(() => {
                row.classList.remove('highlight-animation');
            }, 3000);
        }
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const inventory = new InventoryManager();

    // Exposer l'instance pour le débogage et l'utilisation externe si nécessaire
    window.inventoryManager = inventory;

    // Ajouter des boutons d'export CSV et d'impression dynamiquement
    const boxTools = document.querySelector('.box-tools');
    if (boxTools) {
        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn btn-sm btn-default';
        exportBtn.innerHTML = '<i class="fa fa-download"></i> Exporter CSV';
        exportBtn.style.marginRight = '5px';
        exportBtn.addEventListener('click', () => inventory.exportToCSV());

        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-sm btn-default';
        printBtn.innerHTML = '<i class="fa fa-print"></i> Imprimer';
        printBtn.addEventListener('click', () => inventory.printTable());

        boxTools.prepend(printBtn);
        boxTools.prepend(exportBtn);
    }
});

// Ajouter des styles pour l'impression
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .content-header, .main-header, .main-sidebar, .main-footer, .box-tools, .action-buttons, .pagination-container {
                display: none !important;
            }
            
            .box {
                border: none !important;
                box-shadow: none !important;
            }
            
            .box-header {
                border-bottom: 2px solid #333 !important;
            }
            
            .inventory-table th {
                background-color: #f4f4f4 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .inventory-controls {
                display: none !important;
            }
            
            .badge-purity {
                border: 1px solid #ccc !important;
                color: #000 !important;
            }
        }
    `;
    document.head.appendChild(style);
})();