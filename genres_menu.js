Vue.component('genre', {
  data: function () {
    return {

    };
  },
  props: ['image', 'name', 'url'],
  template: '<a :href="url"><img :src="image" width="400" height="200"></img><h5>{{ name }}</h5></a>'
});

var vm = new Vue({
  el: document.querySelector('#mount_genres'),
  mounted: function() {
    console.log('"Hello Al1"');
  }
});
