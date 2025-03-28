const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            isOpen: true,
        }
    },
    methods: {
        closeAlert() {
            console.log('Alert closed')
            this.isOpen = false
        },
}});

// Mount the Vue app
app.mount('#app');
