/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */



require('./bootstrap');

import Vue from 'vue/dist/vue.esm';
import BootstrapVue from "bootstrap-vue";

window.Vue = Vue;
Vue.use(BootstrapVue)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('my-dashboard', require('./components/DashboardComponent.vue').default);
Vue.component('us-stock-list', require('./components/UsStockListComponent.vue').default);
Vue.component('idx-stock-list', require('./components/IdxStockListComponent.vue').default);
Vue.component('crypto-list', require('./components/CryptoListComponent.vue').default);
Vue.component('commodity-list', require('./components/CommodityListComponent.vue').default);

Vue.component('hold-us-stock-list', require('./components/HoldUsStockListComponent.vue').default);
Vue.component('hold-idx-stock-list', require('./components/HoldIdxStockListComponent.vue').default);
Vue.component('hold-crypto-list', require('./components/HoldCryptoListComponent').default);
Vue.component('hold-commodity-list', require('./components/HoldCommodityListComponent').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
