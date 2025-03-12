// assets/js/components/datatable.js
import '../../styles/components/datatable.scss';

class DataTable {
    constructor(selector, options = {}) {
        this.table = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.table || this.table.tagName !== 'TABLE') return;

        this.options = {
            perPage: options.perPage || 10,
            perPageOptions: options.perPageOptions || [5, 10, 25, 50, 100],
            pagination: options.pagination !== undefined ? options.pagination : true,
            search: options.search !== undefined ? options.search : true,
            ordering: options.ordering !== undefined ? options.ordering : true,
            language: options.language || 'fr',
            ...options
        };

        this.currentPage = 1;
        this.totalPages = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.originalData = [];
        this.filteredData = [];
        this.searchTerm = '';

        this.init();
    }

    init() {
        // Sauvegarder les données originales
        this.saveOriginalData();

        // Créer le wrapper
        this.createWrapper();

        // Ajouter la recherche si activée
        if (this.options.search) {
            this.createSearch();
        }

        // Ajouter les en-têtes de tri si activés
        if (this.options.ordering) {
            this.setupSorting();
        }

        // Rendre les données
        this.renderData();

        // Ajouter la pagination si activée
        if (this.options.pagination) {
            this.createPagination();
        }
    }

    saveOriginalData() {
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));

        this.headers = Array.from(this.table.querySelectorAll('thead th')).map(th => ({
            text: th.textContent.trim(),
            sortable: th.dataset.sortable !== 'false' && this.options.ordering,
        }));

        this.originalData = rows.map(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            const rowData = {
                original: row,
                cells: cells.map(cell => ({
                    text: cell.textContent.trim(),
                    html: cell.innerHTML,
                    element: cell
                }))
            };
            return rowData;
        });

        this.filteredData = [...this.originalData];
        this.totalPages = Math.ceil(this.filteredData.length / this.options.perPage);
    }

    createWrapper() {
        // Créer le wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'khemeia-datatable-wrapper';
        this.table.parentNode.insertBefore(this.wrapper, this.table);

        // Créer l'en-tête avec la recherche et le sélecteur de nombre par page
        this.header = document.createElement('div');
        this.header.className = 'khemeia-datatable-header';
        this.wrapper.appendChild(this.header);

        // Ajouter la table au wrapper
        this.tableContainer = document.createElement('div');
        this.tableContainer.className = 'khemeia-datatable-container';
        this.tableContainer.appendChild(this.table);
        this.wrapper.appendChild(this.tableContainer);

        // Ajouter la classe à la table
        this.table.classList.add('khemeia-datatable');

        // Ajouter le footer pour la pagination
        this.footer = document.createElement('div');
        this.footer.className = 'khemeia-datatable-footer';
        this.wrapper.appendChild(this.footer);

        // Créer le sélecteur de nombre par page
        this.createPerPageSelector();
    }

    createSearch() {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'khemeia-datatable-search';

        const searchLabel = document.createElement('label');
        searchLabel.textContent = this.options.language === 'fr' ? 'Rechercher : ' : 'Search: ';

        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'khemeia-datatable-search-input';
        this.searchInput.placeholder = this.options.language === 'fr' ? 'Entrez un terme...' : 'Enter a term...';

        this.searchInput.addEventListener('input', () => {
            this.searchTerm = this.searchInput.value.toLowerCase();
            this.filterData();
            this.currentPage = 1;
            this.renderData();
            this.updatePagination();
        });

        searchContainer.appendChild(searchLabel);
        searchContainer.appendChild(this.searchInput);
        this.header.appendChild(searchContainer);
    }

    createPerPageSelector() {
        const perPageContainer = document.createElement('div');
        perPageContainer.className = 'khemeia-datatable-length';

        const perPageLabel = document.createElement('label');
        perPageLabel.textContent = this.options.language === 'fr' ? 'Afficher ' : 'Show ';

        const perPageSelect = document.createElement('select');
        perPageSelect.className = 'khemeia-datatable-length-select';

        this.options.perPageOptions.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (value === this.options.perPage) {
                option.selected = true;
            }
            perPageSelect.appendChild(option);
        });

        const perPageText = document.createElement('span');
        perPageText.textContent = this.options.language === 'fr' ? ' entrées' : ' entries';

        perPageSelect.addEventListener('change', () => {
            this.options.perPage = parseInt(perPageSelect.value, 10);
            this.currentPage = 1;
            this.totalPages = Math.ceil(this.filteredData.length / this.options.perPage);
            this.renderData();
            this.updatePagination();
        });

        perPageContainer.appendChild(perPageLabel);
        perPageContainer.appendChild(perPageSelect);
        perPageContainer.appendChild(perPageText);
        this.header.appendChild(perPageContainer);
    }

    setupSorting() {
        const headerRow = this.table.querySelector('thead tr');
        const headers = headerRow.querySelectorAll('th');

        headers.forEach((header, index) => {
            if (this.headers[index].sortable) {
                header.classList.add('khemeia-datatable-sortable');

                const headerContent = header.innerHTML;
                const wrapper = document.createElement('div');
                wrapper.className = 'khemeia-datatable-th-content';
                wrapper.innerHTML = headerContent;

                const sortIcon = document.createElement('span');
                sortIcon.className = 'khemeia-datatable-sort-icon';
                wrapper.appendChild(sortIcon);

                header.innerHTML = '';
                header.appendChild(wrapper);

                header.addEventListener('click', () => {
                    this.sort(index);
                });
            }
        });
    }

    sort(columnIndex) {
        const headers = this.table.querySelectorAll('thead th');

        // Si on clique sur la même colonne, inverser la direction
        if (this.sortColumn === columnIndex) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnIndex;
            this.sortDirection = 'asc';
        }

        // Mettre à jour les classes des en-têtes
        headers.forEach(header => {
            header.classList.remove('khemeia-datatable-sorted-asc', 'khemeia-datatable-sorted-desc');
        });

        headers[columnIndex].classList.add(`khemeia-datatable-sorted-${this.sortDirection}`);

        // Trier les données
        this.filteredData.sort((a, b) => {
            const aValue = a.cells[columnIndex].text;
            const bValue = b.cells[columnIndex].text;

            // Vérifier si c'est un nombre
            if (!isNaN(aValue) && !isNaN(bValue)) {
                return this.sortDirection === 'asc' ?
                    parseFloat(aValue) - parseFloat(bValue) :
                    parseFloat(bValue) - parseFloat(aValue);
            }

            // Tri alphabétique
            return this.sortDirection === 'asc' ?
                aValue.localeCompare(bValue) :
                bValue.localeCompare(aValue);
        });

        this.renderData();
    }

    filterData() {
        if (!this.searchTerm) {
            this.filteredData = [...this.originalData];
        } else {
            this.filteredData = this.originalData.filter(row => {
                return row.cells.some(cell => {
                    return cell.text.toLowerCase().includes(this.searchTerm);
                });
            });
        }

        this.totalPages = Math.ceil(this.filteredData.length / this.options.perPage);
    }

    renderData() {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';

        if (this.filteredData.length === 0) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = this.headers.length;
            emptyCell.className = 'khemeia-datatable-empty';
            emptyCell.textContent = this.options.language === 'fr' ? 'Aucune donnée disponible' : 'No data available';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
            return;
        }

        // Calculer les indices de début et de fin pour la page actuelle
        const start = (this.currentPage - 1) * this.options.perPage;
        const end = Math.min(start + this.options.perPage, this.filteredData.length);

        // Ajouter les lignes de la page actuelle
        for (let i = start; i < end; i++) {
            const row = this.filteredData[i];
            const tr = row.original.cloneNode(true);
            tbody.appendChild(tr);
        }

        // Mettre à jour l'info de pagination
        this.updateInfo();
    }

    createPagination() {
        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'khemeia-datatable-info';
        this.footer.appendChild(paginationInfo);

        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'khemeia-datatable-pagination';

        this.paginationList = document.createElement('ul');
        paginationContainer.appendChild(this.paginationList);

        this.footer.appendChild(paginationContainer);

        this.updatePagination();
    }

    updatePagination() {
        this.paginationList.innerHTML = '';

        // Bouton précédent
        const prevButton = this.createPaginationButton('«', this.currentPage > 1);
        prevButton.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.renderData();
                this.updatePagination();
            }
        });
        this.paginationList.appendChild(prevButton);

        // Pages
        let startPage = Math.max(1, this.currentPage - 2);
        let endPage = Math.min(this.totalPages, startPage + 4);

        if (endPage - startPage < 4 && this.totalPages > 5) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = this.createPaginationButton(i, true, i === this.currentPage);
            pageButton.addEventListener('click', () => {
                this.currentPage = i;
                this.renderData();
                this.updatePagination();
            });
            this.paginationList.appendChild(pageButton);
        }

        // Bouton suivant
        const nextButton = this.createPaginationButton('»', this.currentPage < this.totalPages);
        nextButton.addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.renderData();
                this.updatePagination();
            }
        });
        this.paginationList.appendChild(nextButton);

        // Mettre à jour l'info
        this.updateInfo();
    }

    createPaginationButton(text, enabled, active = false) {
        const li = document.createElement('li');
        li.className = 'khemeia-datatable-page-item';

        if (active) {
            li.classList.add('active');
        }

        if (!enabled) {
            li.classList.add('disabled');
        }

        const button = document.createElement('button');
        button.className = 'khemeia-datatable-page-link';
        button.innerHTML = text;
        button.disabled = !enabled;

        li.appendChild(button);
        return li;
    }

    updateInfo() {
        const info = this.footer.querySelector('.khemeia-datatable-info');
        if (!info) return;

        const start = this.filteredData.length > 0 ? (this.currentPage - 1) * this.options.perPage + 1 : 0;
        const end = Math.min(start + this.options.perPage - 1, this.filteredData.length);

        if (this.options.language === 'fr') {
            info.textContent = `Affichage de ${start} à ${end} sur ${this.filteredData.length} entrées${this.searchTerm ? ` (filtrées parmi ${this.originalData.length} entrées totales)` : ''}`;
        } else {
            info.textContent = `Showing ${start} to ${end} of ${this.filteredData.length} entries${this.searchTerm ? ` (filtered from ${this.originalData.length} total entries)` : ''}`;
        }
    }
}

// Export la classe pour l'utiliser ailleurs
export default DataTable;

// Initialisation automatique pour les éléments avec id example2
document.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('#example2, .khemeia-datatable');
    tables.forEach(table => {
        new DataTable(table, {
            perPage: parseInt(table.dataset.perPage || 10, 10),
            search: table.dataset.search !== 'false',
            ordering: table.dataset.ordering !== 'false',
            pagination: table.dataset.pagination !== 'false',
            language: table.dataset.language || 'fr'
        });
    });
});