Vue.component('books', {
  data: function () {
    return {

    };
  },
  props: {
    name: String,
    url: String,
    image: Array,
    author: String
  },
  template: '<a :href="url"><img v-bind:src="image[0]" width="200" height="300"></img><h5>{{ name }}</h5><div>{{ author }}</div></a>'
});

var vm = new Vue({
  el: document.querySelector('#mount_books'),
  mounted: function() {
    console.log('"Hello Al1"');
  }
});
