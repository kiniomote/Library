Vue.component('book', {
  data: function () {
    return {

    };
  },
  props: {
    name: String,
    imagePathes: Array,
    author: String,
    description: String,
    fileText: String,
    logg: Boolean
  },
  template: '\
    <div class="row">\
      <div class="col-6">\
        <img class="main-cover" v-bind:src="imagePathes[0]"></img>\
      </div>\
      <div class="col-6">\
        <h4>{{ name }}</h4>\
        <h6>{{ author }}</h6>\
        <div v-if="logg">\
          <p>{{ description }}</p>\
          <a download class="btn" :href="fileText">Скачать</a>\
        </div>\
        <p v-else>Чтобы получить полную информацию, авторизуйтесь</p>\
        <div class="row">\
          <div v-for="image in imagePathes">\
            <img class="add-cover" v-bind:src="image"></img>\
          </div>\
        </div>\
      </div>\
    </div>'
});

var vm = new Vue({
  el: document.querySelector('#mount_book'),
  mounted: function() {
    console.log('"Hello Al1"');
  }
});
