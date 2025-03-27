const { createApp, defineAsyncComponent } = Vue;

const app = createApp({
    data() {
        return {
            isOpen: true,
            isVisible: window.innerWidth >= 992,
            windowWidth: window.innerWidth,
            activeSubmenu: null,
            componentsLoaded: false
        }
    },
    methods: {
        toggleSubmenu(menu) {
            this.activeSubmenu = this.activeSubmenu === menu ? null : menu;
        },
        updateWindowWidth() {
            this.windowWidth = window.innerWidth;
            if (this.windowWidth < 992) this.activeSubmenu = null;
        },
        async loadComponents() {
            // Dynamically import all components
            const modules = import.meta.glob('./components/*.vue');
            
            for (const path in modules) {
                const componentName = path.split('/').pop().replace('.vue', '');
                this.component(componentName, defineAsyncComponent(() => modules[path]()));
            }
        }
    },
    mounted() {
        window.addEventListener('resize', this.updateWindowWidth);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.updateWindowWidth);
    }
});

// Mount the Vue app
app.mount('#app');
