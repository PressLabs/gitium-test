<?php
/**
 * Plugin Name: Gitium Test
 * Version: 1.0
 * Description: Test if Gitium is stuck on merge_local branch after parallel theme installs. Go to Tools->Gitium test
 * Author: Presslabs
 */

add_action( 'admin_menu', 'gitium_test_menu' );
function gitium_test_menu() {
	add_management_page( 'Gitium test page', 'Gitium test', 'read', 'gitium-test', 'gitium_test_page' );
}

function gitium_test_page() {
?><div class="wrap">
	<h2>Gitium test</h2>
	<p>Test if Gitium is stuck on merge_local branch after parallel theme installs. <a href="#" onclick="return jsPurePostParallel();">Click here</a> and in the same time install a plugin/theme in order to test.</p>
	</div><?php
}

add_action( 'admin_head', 'gitium_test_admin_head' );
function gitium_test_admin_head() {
?><script type="application/javascript">
function jsPurePost(url) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) { // response is ready
      console.log(xhr.responseURL + ':' + xhr.status);
    }
  }
  xhr.open('POST', url);
  xhr.send(null);
}

function jsPurePostParallel() {
  for ( var index = 0; index < 300; index++ ) {
    jsPurePost('/wp-admin/admin-ajax.php?action=gitium_test');
  };
}
</script>
<?php
}

add_action( 'wp_ajax_gitium_test', 'gitium_test_callback' );
function gitium_test_callback() {
	$data_to_write = md5( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.()[]{}-_=+!@#%^&*~<>:;' ) );
	$file_path   = trailingslashit( WP_CONTENT_DIR ) . 'gitium-test.txt';
	$file_handle = fopen( $file_path, "w+" );
	fwrite( $file_handle, $data_to_write );
	fclose( $file_handle );
	wp_die();
}

add_action( 'admin_enqueue_scripts', 'gitium_test_enqueue' );
function gitium_test_enqueue($hook) {
	wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
