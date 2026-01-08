document.addEventListener('alpine:init', () => {
    Alpine.data('searchComponent', (menus = []) => ({
        searchOpen: false,
        searchQuery: '',
        allMenus: menus,
        searchHistory: [],
        selectedIndex: -1,

        init() {
            this.searchHistory = JSON.parse(localStorage.getItem('menuSearchHistory')) || [];

            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.searchOpen = false;
                    this.selectedIndex = -1;
                }
            });
        },

        showDropdown() {
            this.searchOpen = true;
            this.selectedIndex = -1;
        },

        closeDropdown() {
            setTimeout(() => {
                this.searchOpen = false;
                this.selectedIndex = -1;
            }, 200);
        },

        get filteredMenus() {
            if (!this.searchQuery.trim()) {
                return [];
            }

            const query = this.searchQuery.toLowerCase();
            return this.allMenus.filter(menu =>
                menu.name.toLowerCase().includes(query)
            );
        },

        highlightText(text, query) {
            if (!query.trim()) return text;

            const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
            return text.replace(regex, '<mark class="search-highlight">$1</mark>');
        },

        escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },

        runHistorySearch(term) {
            this.searchQuery = term;
            this.searchOpen = true;
            this.selectedIndex = -1;
            this.$nextTick(() => {
                this.$refs.searchInput.focus();
                this.$refs.searchInput.select();
            });
        },

        addToHistory(term) {
            if (!term || !term.trim()) return;

            const cleanTerm = term.trim();

            this.searchHistory = this.searchHistory.filter(
                item => item.toLowerCase() !== cleanTerm.toLowerCase()
            );

            this.searchHistory.unshift(cleanTerm);
            this.searchHistory = this.searchHistory.slice(0, 5);

            localStorage.setItem('menuSearchHistory', JSON.stringify(this.searchHistory));
        },

        clearHistory() {
            this.searchHistory = [];
            localStorage.removeItem('menuSearchHistory');
            this.selectedIndex = -1;
            this.searchOpen = true;
        },

        handleKeydown(event) {
            const items = this.getCurrentItems();

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                        if (this.searchQuery.trim()) {
                            const menu = items[this.selectedIndex];
                            this.navigateToMenu(menu);
                        } else {
                            this.runHistorySearch(items[this.selectedIndex]);
                        }
                    } else if (this.filteredMenus.length > 0) {
                        this.navigateToMenu(this.filteredMenus[0]);
                    }
                    break;
                case 'Escape':
                    this.searchOpen = false;
                    this.selectedIndex = -1;
                    this.$refs.searchInput.blur();
                    break;
            }
        },

        navigateToMenu(menu) {
            if (!menu.url) {
                console.warn('Menu tidak memiliki URL:', menu.name);
                return;
            }

            this.addToHistory(menu.name);
            this.searchOpen = false;
            window.location.href = menu.url;
        },

        getCurrentItems() {
            if (this.searchQuery.trim()) {
                return this.filteredMenus;
            } else {
                return this.searchHistory;
            }
        }
    }));
}); document.addEventListener('alpine:init', () => {
    Alpine.data('searchComponent', (menus = []) => ({
        searchOpen: false,
        searchQuery: '',
        allMenus: menus,
        searchHistory: [],
        selectedIndex: -1,

        init() {
            this.searchHistory = JSON.parse(localStorage.getItem('menuSearchHistory')) || [];

            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.searchOpen = false;
                    this.selectedIndex = -1;
                }
            });
        },

        showDropdown() {
            this.searchOpen = true;
            this.selectedIndex = -1;
        },

        closeDropdown() {
            setTimeout(() => {
                this.searchOpen = false;
                this.selectedIndex = -1;
            }, 200);
        },

        get filteredMenus() {
            if (!this.searchQuery.trim()) {
                return [];
            }

            const query = this.searchQuery.toLowerCase();
            return this.allMenus.filter(menu =>
                menu.name.toLowerCase().includes(query)
            );
        },

        highlightText(text, query) {
            if (!query.trim()) return text;

            const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
            return text.replace(regex, '<mark class="search-highlight">$1</mark>');
        },

        escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },

        runHistorySearch(term) {
            this.searchQuery = term;
            this.searchOpen = true;
            this.selectedIndex = -1;
            this.$nextTick(() => {
                this.$refs.searchInput.focus();
                this.$refs.searchInput.select();
            });
        },

        addToHistory(term) {
            if (!term || !term.trim()) return;

            const cleanTerm = term.trim();

            this.searchHistory = this.searchHistory.filter(
                item => item.toLowerCase() !== cleanTerm.toLowerCase()
            );

            this.searchHistory.unshift(cleanTerm);
            this.searchHistory = this.searchHistory.slice(0, 5);

            localStorage.setItem('menuSearchHistory', JSON.stringify(this.searchHistory));
        },

        clearHistory() {
            this.searchHistory = [];
            localStorage.removeItem('menuSearchHistory');
            this.selectedIndex = -1;
            this.searchOpen = true;
        },

        handleKeydown(event) {
            const items = this.getCurrentItems();

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                        if (this.searchQuery.trim()) {
                            const menu = items[this.selectedIndex];
                            this.navigateToMenu(menu);
                        } else {
                            this.runHistorySearch(items[this.selectedIndex]);
                        }
                    } else if (this.filteredMenus.length > 0) {
                        this.navigateToMenu(this.filteredMenus[0]);
                    }
                    break;
                case 'Escape':
                    this.searchOpen = false;
                    this.selectedIndex = -1;
                    this.$refs.searchInput.blur();
                    break;
            }
        },

        navigateToMenu(menu) {
            if (!menu.url) {
                console.warn('Menu tidak memiliki URL:', menu.name);
                return;
            }

            this.addToHistory(menu.name);
            this.searchOpen = false;
            window.location.href = menu.url;
        },

        getCurrentItems() {
            if (this.searchQuery.trim()) {
                return this.filteredMenus;
            } else {
                return this.searchHistory;
            }
        }
    }));
});