Vue.component('form_add', {
  data: function () {
    return {
      text_uploaded: false,
      covers_uploaded: false
    };
  },
  props: {
    genres: Array
  },
  template: '\
  <form class="form" action="http://wpfolder/wp-admin/admin-post.php" method="post" enctype="multipart/form-data">\
    <input type="hidden" name="max_file_size" value="5242880">\
    <h4>Добавление книги</h4>\
    <loader_text v-on:upload="handleTextUpload"></loader_text><br>\
    <div v-if="text_uploaded">\
      <loader_covers v-on:upload="handleCoversUpload"></loader_covers><br>\
      <div v-if="covers_uploaded">\
        <label for="author">Автор книги</label>\
        <input type="text" name="author" required></input>\
        <label for="name_book">Название книги</label>\
        <input type="text" name="name_book" required></input>\
        <label for="gnrs">Жанры книги</label>\
        <select name="gnrs[]" multiple size="5">\
          <option v-for="genre in genres" v-bind:value="genre.id">{{ genre.name }}</option>\
        </select><br>\
        <label for="describe">Краткое описание</label>\
        <textarea name="describe" required></textarea>\
        <input type="submit" value="Добавить книгу"></input>\
      </div>\
    </div>\
    <input type="hidden" name="action" value="add_book">\
  </form>',
  methods: {
    handleTextUpload() {
      this.text_uploaded = true;
      console.log('Text upload');
    },
    handleCoversUpload() {
      this.covers_uploaded = true;
      console.log('Covers upload');
    }
  }
});

Vue.component('loader_text', {
  data: function () {
    return {

    };
  },
  methods: {
    upload() {
      // this.file = this.$refs.text.files[0];
      this.$emit('upload');
    }
  },
  template: '<label>Текст книги <input required type="file" ref="text" name="text_file" v-on:change="upload()"></input></label>'
});

Vue.component('loader_covers', {
  data: function () {
    return {

    };
  },
  methods: {
    upload() {
      // this.file = this.$refs.covers.files;
      this.$emit('upload');
    }
  },
  template: '<label>Обложки книг <input required type="file" ref="covers" name="cover_files[]" multiple v-on:change="upload()"></input></label>'
});

var vm = new Vue({
  el: document.querySelector('#mount_loader_book'),
  mounted: function() {
    console.log('Add book');
  }
});
