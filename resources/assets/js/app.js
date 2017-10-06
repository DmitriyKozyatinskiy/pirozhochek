/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('history', require('./components/History.vue'));

// const app = new Vue({
//   el: '#app'
// });

$(document).on('change', '#js-file', (event) => {
  console.log('HELLO');
  const name = event.target.files[0].name;
  console.log($(event.target).parent(), $(event.target).parent().find('span'));
  $(event.target).parent().find('span').addClass('FileLabel--IsUploaded').attr('data-file-name', name);
});