// assets/js/components/datepicker.js
import '../../styles/components/datepicker.scss';

class DatePicker {
    constructor(selector, options = {}) {
        this.input = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.input) return;

        // Ajouter cette instance à une liste statique d'instances
        if (!DatePicker.instances) DatePicker.instances = [];
        DatePicker.instances.push(this);

        this.options = {
            format: options.format || 'dd/mm/yyyy',
            language: options.language || 'fr',
            autoclose: options.autoclose !== undefined ? options.autoclose : true,
            clearBtn: options.clearBtn !== undefined ? options.clearBtn : true,
            todayHighlight: options.todayHighlight !== undefined ? options.todayHighlight : true,
            ...options
        };

        this.visible = false;
        this.selectedDate = null;

        this.init();
    }

    init() {
        // Créer le conteneur du datepicker
        this.container = document.createElement('div');
        this.container.className = 'khemeia-datepicker';
        this.container.style.display = 'none';

        // Ajouter l'en-tête avec les boutons de navigation
        this.renderHeader();

        // Ajouter le corps du calendrier
        this.renderCalendar();

        // Ajouter les boutons (aujourd'hui, effacer)
        this.renderFooter();

        // Insérer après l'input
        this.input.parentNode.insertBefore(this.container, this.input.nextSibling);

        // Ajouter les événements
        this.addEventListeners();

        // Si une date est déjà dans l'input, la sélectionner
        if (this.input.value) {
            this.selectedDate = this.parseDate(this.input.value);
            this.updateCalendar();
        }
    }

    renderHeader() {
        const header = document.createElement('div');
        header.className = 'khemeia-datepicker-header';

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'khemeia-datepicker-prev';
        prevBtn.innerHTML = '&laquo;';
        prevBtn.addEventListener('click', () => this.prevMonth());

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'khemeia-datepicker-next';
        nextBtn.innerHTML = '&raquo;';
        nextBtn.addEventListener('click', () => this.nextMonth());

        this.monthYearLabel = document.createElement('div');
        this.monthYearLabel.className = 'khemeia-datepicker-title';

        header.appendChild(prevBtn);
        header.appendChild(this.monthYearLabel);
        header.appendChild(nextBtn);

        this.container.appendChild(header);
    }

    renderCalendar() {
        this.calendarContainer = document.createElement('div');
        this.calendarContainer.className = 'khemeia-datepicker-calendar';

        // Créer les en-têtes des jours
        const daysHeader = document.createElement('div');
        daysHeader.className = 'khemeia-datepicker-days-header';

        const days = this.options.language === 'fr' ?
            ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'] :
            ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];

        days.forEach(day => {
            const dayEl = document.createElement('div');
            dayEl.className = 'khemeia-datepicker-weekday';
            dayEl.textContent = day;
            daysHeader.appendChild(dayEl);
        });

        this.calendarContainer.appendChild(daysHeader);

        // Grid pour les jours
        this.daysGrid = document.createElement('div');
        this.daysGrid.className = 'khemeia-datepicker-days';
        this.calendarContainer.appendChild(this.daysGrid);

        this.container.appendChild(this.calendarContainer);

        // Initialiser avec le mois actuel
        this.currentMonth = new Date();
        this.updateCalendar();
    }

    renderFooter() {
        const footer = document.createElement('div');
        footer.className = 'khemeia-datepicker-footer';

        if (this.options.todayHighlight) {
            const todayBtn = document.createElement('button');
            todayBtn.type = 'button';
            todayBtn.className = 'khemeia-datepicker-today';
            todayBtn.textContent = this.options.language === 'fr' ? 'Aujourd\'hui' : 'Today';
            todayBtn.addEventListener('click', () => this.selectToday());
            footer.appendChild(todayBtn);
        }

        if (this.options.clearBtn) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'khemeia-datepicker-clear';
            clearBtn.textContent = this.options.language === 'fr' ? 'Effacer' : 'Clear';
            clearBtn.addEventListener('click', () => this.clear());
            footer.appendChild(clearBtn);
        }

        this.container.appendChild(footer);
    }

    updateCalendar() {
        // Mettre à jour le titre avec mois et année
        const months = this.options.language === 'fr' ?
            ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'] :
            ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        this.monthYearLabel.textContent = `${months[this.currentMonth.getMonth()]} ${this.currentMonth.getFullYear()}`;

        // Vider la grille
        this.daysGrid.innerHTML = '';

        // Calculer les jours du mois
        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();

        // Premier jour du mois
        const firstDay = new Date(year, month, 1);
        // Dernier jour du mois
        const lastDay = new Date(year, month + 1, 0);

        // Index du premier jour (0 = lundi dans notre grille)
        let firstDayIndex = firstDay.getDay() - 1;
        if (firstDayIndex < 0) firstDayIndex = 6; // Le dimanche est 0 en JS

        // Nombre total de jours dans le mois
        const daysInMonth = lastDay.getDate();

        // Créer les cellules vides pour les jours avant le début du mois
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'khemeia-datepicker-day empty';
            this.daysGrid.appendChild(emptyDay);
        }

        // Créer les cellules pour chaque jour du mois
        for (let i = 1; i <= daysInMonth; i++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'khemeia-datepicker-day';
            dayEl.textContent = i;

            // Vérifier si c'est aujourd'hui
            const dayDate = new Date(year, month, i);
            if (this.isToday(dayDate)) {
                dayEl.classList.add('today');
            }

            // Vérifier si c'est la date sélectionnée
            if (this.selectedDate && this.isSameDay(dayDate, this.selectedDate)) {
                dayEl.classList.add('selected');
            }

            // Ajouter événement de clic
            dayEl.addEventListener('click', () => this.selectDate(dayDate));

            this.daysGrid.appendChild(dayEl);
        }
    }

    addEventListeners() {
        // Ouvrir le datepicker au clic sur l'input
        this.input.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        // Fermer le datepicker au clic à l'extérieur
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target) && e.target !== this.input) {
                this.hide();
            }
        });

        // Ajouter événement d'entrée clavier pour le format de date
        this.input.addEventListener('input', () => {
            const inputValue = this.input.value;
            if (inputValue && this.isValidDate(inputValue)) {
                this.selectedDate = this.parseDate(inputValue);
                this.updateCalendar();
            }
        });
    }

    toggle() {
        if (this.visible) {
            this.hide();
        } else {
            this.show();
        }
    }

    show() {
        // Fermer tous les autres datepickers ouverts
        DatePicker.instances.forEach(instance => {
            if (instance !== this && instance.visible) {
                instance.hide();
            }
        });

        this.container.style.display = 'block';
        this.visible = true;

        // Positionner correctement le datepicker
        const inputRect = this.input.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        // Assurer que le conteneur est bien positionné
        this.container.style.position = 'absolute';



        // S'assurer que le z-index est suffisamment élevé
        this.container.style.zIndex = '1000';
    }

    hide() {
        if (this.options.autoclose) {
            this.container.style.display = 'none';
            this.visible = false;
        }
    }

    prevMonth() {
        this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
        this.updateCalendar();
    }

    nextMonth() {
        this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
        this.updateCalendar();
    }

    selectDate(date) {
        this.selectedDate = date;
        this.input.value = this.formatDate(date);
        this.updateCalendar();

        // Déclencher l'événement change
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);

        if (this.options.autoclose) {
            this.hide();
        }
    }

    selectToday() {
        this.selectDate(new Date());
    }

    clear() {
        this.selectedDate = null;
        this.input.value = '';
        this.updateCalendar();

        // Déclencher l'événement change
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);
    }

    formatDate(date) {
        if (!date) return '';

        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();

        // Format par défaut dd/mm/yyyy
        return this.options.format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year);
    }

    parseDate(dateStr) {
        if (!dateStr) return null;

        // Analyse du format dd/mm/yyyy par défaut
        const parts = dateStr.split('/');
        if (parts.length !== 3) return null;

        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);

        return new Date(year, month, day);
    }

    isValidDate(dateStr) {
        const date = this.parseDate(dateStr);
        return date instanceof Date && !isNaN(date);
    }

    isToday(date) {
        const today = new Date();
        return this.isSameDay(date, today);
    }

    isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
            date1.getMonth() === date2.getMonth() &&
            date1.getFullYear() === date2.getFullYear();
    }
}

// Export la classe pour l'utiliser ailleurs
export default DatePicker;

// Initialisation automatique pour les éléments avec classe .datepicker
document.addEventListener('DOMContentLoaded', () => {
    const datepickers = document.querySelectorAll('.datepicker');
    datepickers.forEach(input => {
        new DatePicker(input, {
            format: input.dataset.format || 'dd/mm/yyyy',
            language: input.dataset.language || 'fr',
            autoclose: input.dataset.autoclose !== 'false',
            clearBtn: input.dataset.clearBtn !== 'false',
            todayHighlight: input.dataset.todayHighlight !== 'false'
        });
    });
});