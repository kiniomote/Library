<?php
/*
Plugin Name: Genres of book
Description: Genres of book menu
Version: 1.0
*/

// ______________Shortcode load text______________

function handle_shortcode_loader_book() {
  wp_enqueue_script('loader_book', plugin_dir_url( __FILE__ ) . 'loader_book.js', [], '1.0', true);
  global $wpdb;
  $table_genres_of_book = $wpdb->get_blog_prefix() . 'genres_of_book';
  $genres = $wpdb->get_results("SELECT id, name FROM " . $table_genres_of_book . ";");
  $genres = arr_objs_vue_encode($genres);
  return '<div id="mount_loader_book"><form_add v-bind:genres="' . $genres . '"></form_add></div>';
}

add_shortcode('loader_book', 'handle_shortcode_loader_book');

function add_book() {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["text_file"]) && $_FILES["text_file"]["error"] == 0 && isset($_FILES["cover_files"]) && !in_array(2, $_FILES["cover_files"]["error"])) {
      global $wpdb;
      $table_books = $wpdb->get_blog_prefix() . 'books';
      $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';

      $text_file = $_FILES["text_file"];
      $covers_file = $_FILES["cover_files"];
      $overrides = ['test_form' => false];
      $media_text = wp_handle_upload($text_file, $overrides);
      $text = $media_text['url'];
      $covers = [];
      foreach($covers_file['name'] as $key => $value) {
        if ($covers_file['name'][$key]) {
          $cover = array(
            'name'     => $covers_file['name'][$key],
            'type'     => $covers_file['type'][$key],
            'tmp_name' => $covers_file['tmp_name'][$key],
            'error'    => $covers_file['error'][$key],
            'size'     => $covers_file['size'][$key]
          );
          $media_cover = wp_handle_upload($cover, $overrides);
          $covers[] = $media_cover['url'];
        }
      }
      $wpdb->insert($table_books, array(
        'name' => $_POST['name_book'],
        'image_pathes' => my_json_encode($covers),
        'author' => $_POST['author'],
        'description' => $_POST['describe'],
        'file_text' => $text),
        array('%s', '%s', '%s', '%s', '%s')
      );
      $id_book = $wpdb->insert_id;
      $genres = $_POST['gnrs'];
      foreach ($genres as $genre_id) {
        $wpdb->insert($table_genres_books, array(
          'id_genre' => $genre_id,
          'id_book' => $id_book),
          array('%d', '%d')
        );
      }
    }
  }
  wp_redirect(home_url());
  exit;
}

function my_json_encode($arr) {
  if (empty($arr)) {
    return '';
  }
  $json_arr = "['" . implode("', '", $arr) . "']";
  return $json_arr;
}

function arr_objs_vue_encode($arr) {
  $objs = [];
  foreach ($arr as $object) {
    $objs[] = "{ id : '" . $object->id . "', name : '" . $object->name . "' }";
  }
  $vue_arr = "[" . implode(", ", $objs) . "]";
  return $vue_arr;
}

add_action('admin_post_add_book', 'add_book');
add_action('admin_post_nopriv_add_book', 'add_book');

// ______________Shortcode genres______________

function handle_shortcode_genres() {
  wp_enqueue_script('genres_menu', plugin_dir_url( __FILE__ ) . 'genres_menu.js', [], '1.0', true);
  global $wpdb;
  $table_genres_of_book = $wpdb->get_blog_prefix() . 'genres_of_book';
  $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';
  $genres = [];
  $genres_id = $wpdb->get_results("SELECT DISTINCT id_genre FROM " . $table_genres_books . ";");
  foreach ($genres_id as $genre) {
    $genres[] = $wpdb->get_results("SELECT id, name, image_path FROM " . $table_genres_of_book . " WHERE id = " . $genre->id_genre . ";")[0];
  }
  $vue_genres = '';
  foreach ($genres as $genre) {
    $url = esc_url(add_query_arg('genre', $genre->id, site_url('/книги-по-жанру/')));
    $vue_genres .= '<genre image="' . $genre->image_path . '" name="' . $genre->name . '" url="' . $url . '"></genre>';
  }
  return '<div id="mount_genres">' . $vue_genres . '</div>';
}

add_shortcode('genres_book', 'handle_shortcode_genres');

// ______________Shortcode books______________

function handle_shortcode_books() {
  wp_enqueue_script('books', plugin_dir_url( __FILE__ ) . 'books.js', [], '1.0', true);
  $id_genre = isset($_GET['genre']) ? $_GET['genre'] : '';
  global $wpdb;
  $table_books = $wpdb->get_blog_prefix() . 'books';
  $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';
  $books = [];
  $books_id = $wpdb->get_results("SELECT DISTINCT id_book FROM " . $table_genres_books . " WHERE id_genre = " . $id_genre . ";");
  foreach ($books_id as $book) {
    $books[] = $wpdb->get_results("SELECT id, name, image_pathes, author FROM " . $table_books . " WHERE id = " . $book->id_book . ";")[0];
  }
  $vue_books = '';
  foreach ($books as $book) {
    $url = esc_url(add_query_arg('book', $book->id, site_url('/книга/')));
    $image = $book->image_pathes;
    $vue_books .= '<books v-bind:image="' . $image . '" name="' . $book->name . '" url="' . $url . '" author="' . $book->author . '"></books>';
  }
  return '<div id="mount_books">' . $vue_books . '</div>';
}

add_shortcode('books_by_genre', 'handle_shortcode_books');

// ______________Shortcode book______________

function handle_shortcode_book() {
  wp_enqueue_script('book', plugin_dir_url( __FILE__ ) . 'book.js', [], '1.0', true);
  $id_book = isset($_GET['book']) ? $_GET['book'] : '';
  global $wpdb;
  $table_books = $wpdb->get_blog_prefix() . 'books';
  $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';
  $genres_id = $wpdb->get_results("SELECT DISTINCT id_genre FROM " . $table_genres_books . " WHERE id_book = " . $id_book . ";");
  $book = $wpdb->get_results("SELECT name, image_pathes, author, description, file_text FROM " . $table_books . " WHERE id = " . $id_book . ";")[0];
  $logg = 'false';
  if (is_user_logged_in()) {
	  $logg = 'true';
  }
  $vue_book = '<book
    name="' . $book->name . '"
    v-bind:image-pathes="' . $book->image_pathes . '"
    author="' . $book->author . '"
    description="' . $book->description . '"
    file-text="' . $book->file_text . '"
    v-bind:logg="' . $logg . '"></book>';
  return '<div id="mount_book">' . $vue_book . '</div>';
}

add_shortcode('book', 'handle_shortcode_book');

// ______________Add Vue______________

function enqueue_scripts() {
  global $post;
  wp_enqueue_script('vue', 'https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js', [], '2.5.17');
};

add_action('wp_enqueue_scripts', 'enqueue_scripts');


function plugin_activation() {
  global $wpdb;
  $table_genres_of_book = $wpdb->get_blog_prefix() . 'genres_of_book';
  $table_books = $wpdb->get_blog_prefix() . 'books';
  $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';
  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

  $sql_create_genres = "CREATE TABLE {$table_genres_of_book} (
    id bigint(20) unsigned NOT NULL auto_increment,
    name nvarchar(50) NOT NULL,
    image_path nvarchar(255) NOT NULL,
    PRIMARY KEY (id)
  )
  {$charset_collate};";

  $sql_create_books = "CREATE TABLE {$table_books} (
    id bigint(20) unsigned NOT NULL auto_increment,
    name nvarchar(50) NOT NULL,
    image_pathes nvarchar(255) NOT NULL,
    author nvarchar(100) NOT NULL,
    description nvarchar(255) NOT NULL,
    file_text nvarchar(255) NOT NULL,
    PRIMARY KEY (id)
  )
  {$charset_collate};";

  $sql_create_genres_books = "CREATE TABLE {$table_genres_books} (
    id bigint(20) unsigned NOT NULL auto_increment,
    id_genre bigint(20) unsigned NOT NULL,
    id_book bigint(20) unsigned NOT NULL,
    PRIMARY KEY (id)
  )
  {$charset_collate};";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql_create_genres);
  dbDelta($sql_create_books);
  dbDelta($sql_create_genres_books);

  $wpdb->query('alter table ' . $table_genres_books . ' add foreign key (id_genre) references ' . $table_genres_of_book . '(id) on DELETE CASCADE');
  $wpdb->query('alter table ' . $table_genres_books . ' add foreign key (id_book) references ' . $table_books . '(id) on DELETE CASCADE');

  $genres = [
    ['name' => 'Фантастика', 'image_path' => plugin_dir_url(__FILE__) . 'images/fantasy.jpg'],
    ['name' => 'Комедия', 'image_path' => plugin_dir_url(__FILE__) . 'images/comedy.jpg'],
    ['name' => 'Приключения', 'image_path' => plugin_dir_url(__FILE__) . 'images/adventure.jpg'],
    ['name' => 'Ужасы', 'image_path' => plugin_dir_url(__FILE__) . 'images/horror.jpg'],
    ['name' => 'Учебники', 'image_path' => plugin_dir_url(__FILE__) . 'images/education.jpg'],
  ];

  foreach ($genres as $genre) {
    $wpdb->insert(
      $table_genres_of_book, array(
        'name' => $genre['name'],
        'image_path' => $genre['image_path']
      ), array('%s', '%s')
    );
  };
};

register_activation_hook(__FILE__, 'plugin_activation');

function plugin_deactivation() {
  global $wpdb;
  $table_genres_of_book = $wpdb->get_blog_prefix() . 'genres_of_book';
  $table_books = $wpdb->get_blog_prefix() . 'books';
  $table_genres_books = $wpdb->get_blog_prefix() . 'genres_books';
  $wpdb->query('DROP TABLE IF EXISTS ' . $table_genres_books);
  $wpdb->query('DROP TABLE IF EXISTS ' . $table_genres_of_book);
  $wpdb->query('DROP TABLE IF EXISTS ' . $table_books);
};

register_deactivation_hook(__FILE__, 'plugin_deactivation');
