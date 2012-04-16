## Custom Post Helper for Wordpress

This is a Custom Post type helper for WordPress to easily create custom post types,taxonomies and meta boxes. This class is based off of <a href="https://github.com/JeffreyWay/Easy-WordPress-Custom-Post-Types">Jeffrey Way</a>, <a href="https://github.com/Ginius/Wordpress-Custom-Post-Type-Helper">Gijs Jorissen</a> and <a href="http://wp.tutsplus.com/tutorials/reusable-custom-meta-boxes-part-3-extra-fields/">Tammy Hart</a>.

##Install

Include the class in your `functions.php` and add the css and js into template directory.

##Usage

To add a custom post type simply call the CP_Helper and pass a name

	$car = new CP_Helper( 'Car' );

If you want to override defaults

	$car = new CP_Helper( 'Car',
				array( 'supports' => array( 'title', 'editor', 'excerpt' ) )
	);

### Add Custom taxonomies
	
To add Custom Taxonomies, simply call the add_taxonomy.

	$car->add_taxonomy( 'Model' );

###Add Metaboxes

First argument is the title of the meta box and the second you must pass an array for each field.

	$car->add_meta_box(
		'Car Info', array(
		    array(
		        'name' => 'Car Image',
		        'label'	=> 'Car Image',
		        'desc' => 'Please select an image',
		        'type' => 'image',
		        ),
		    array(
		        'name' => 'Car Description',
		        'label'	=> 'Car Description',
		        'desc' => 'Please enter a description for the car',
		        'type' => 'wyiwyg',
		        ),
		));
