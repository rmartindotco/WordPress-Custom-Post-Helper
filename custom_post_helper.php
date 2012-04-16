<?php

class CP_Helper
    {
        public $post_type_name;
        public $post_type_args;
        public $post_type_labels;
        
        /* Class constructor */
        public function __construct( $name,  $args = array(), $labels = array() )
        {
            // Set some important variables
            $this->post_type_name       = self::uglify( $name );
            $this->post_type_args       = $args;
            $this->post_type_labels     = $labels;

            // Add action to register the post type, if the post type doesnt exist
            if( ! post_type_exists( $this->post_type_name ) )
            {
                add_action( 'init', array( &$this, 'register_post_type' ) );
            }
            //Add the required js and css files
            if(is_admin())
            {
                add_action('admin_head', array( &$this, 'add_custom_js_css' ) );
                add_action('admin_head', array( &$this, 'add_custom_scripts' ) );
            }

            // Listen for the save post hook
            $this->save();
        }
        
        /* Method which registers the post type */
        public function register_post_type()
        {       
            //Capitilize the words and make it plural
            $name       = self::beautify( $this->post_type_name  );
            $plural     = self::pluralize( $name );

            // We set the default labels based on the post type name and plural. We overwrite them with the given labels.
            $labels = array_merge(

                // Default
                array(
                    'name'                  => _x( $plural, 'post type general name' ),
                    'singular_name'         => _x( $name, 'post type singular name' ),
                    'add_new'               => _x( 'Add New', strtolower( $name ) ),
                    'add_new_item'          => __( 'Add New ' . $name ),
                    'edit_item'             => __( 'Edit ' . $name ),
                    'new_item'              => __( 'New ' . $name ),
                    'all_items'             => __( 'All ' . $plural ),
                    'view_item'             => __( 'View ' . $name ),
                    'search_items'          => __( 'Search ' . $plural ),
                    'not_found'             => __( 'No ' . strtolower( $plural ) . ' found'),
                    'not_found_in_trash'    => __( 'No ' . strtolower( $plural ) . ' found in Trash'), 
                    'parent_item_colon'     => '',
                    'menu_name'             => $plural
                ),

                // Given labels
                $this->post_type_labels

            );

            // Same principle as the labels. We set some default and overwite them with the given arguments.
            $args = array_merge(

                // Default
                array(
                    'label'                 => $plural,
                    'labels'                => $labels,
                    'public'                => true,
                    'show_ui'               => true,
                    'supports'              => array( 'title', 'editor','thumbnail' ),
                    'show_in_nav_menus'     => true,
                    '_builtin'              => false,
                ),

                // Given args
                $this->post_type_args

            );

            // Register the post type
            register_post_type( $this->post_type_name, $args );
        }
        
        /* Method to attach the taxonomy to the post type */
        public function add_taxonomy( $name, $args = array(), $labels = array() )
        {
            if( ! empty( $name ) )
            {           
                // We need to know the post type name, so the new taxonomy can be attached to it.
                $post_type_name = $this->post_type_name;

                // Taxonomy properties
                $taxonomy_name      = self::uglify( $name );
                $taxonomy_labels    = $labels;
                $taxonomy_args      = $args;

                if( ! taxonomy_exists( $taxonomy_name ) )
                    {
                        //Capitilize the words and make it plural
                            $name       = self::beautify( $name );
                            $plural     = self::pluralize( $name );
                            // Default labels, overwrite them with the given labels.
                            $labels = array_merge(

                                // Default
                                array(
                                    'name'                  => _x( $plural, 'taxonomy general name' ),
                                    'singular_name'         => _x( $name, 'taxonomy singular name' ),
                                    'search_items'          => __( 'Search ' . $plural ),
                                    'all_items'             => __( 'All ' . $plural ),
                                    'parent_item'           => __( 'Parent ' . $name ),
                                    'parent_item_colon'     => __( 'Parent ' . $name . ':' ),
                                    'edit_item'             => __( 'Edit ' . $name ), 
                                    'update_item'           => __( 'Update ' . $name ),
                                    'add_new_item'          => __( 'Add New ' . $name ),
                                    'new_item_name'         => __( 'New ' . $name . ' Name' ),
                                    'menu_name'             => __( $name ),
                                ),

                                // Given labels
                                $taxonomy_labels

                            );

                            // Default arguments, overwitten with the given arguments
                            $args = array_merge(

                                // Default
                                array(
                                    'label'                 => $plural,
                                    'labels'                => $labels,
                                    'public'                => true,
                                    'show_ui'               => true,
                                    'show_in_nav_menus'     => true,
                                    '_builtin'              => false,
                                ),

                                // Given
                                $taxonomy_args

                            );

                            // Add the taxonomy to the post type
                            add_action( 'init',
                                function() use( $taxonomy_name, $post_type_name, $args )
                                {                       
                                    register_taxonomy( $taxonomy_name, $post_type_name, $args );
                                }
                            );
                    }
                    else
                    {
                        add_action( 'init',
                                function() use( $taxonomy_name, $post_type_name )
                                {               
                                    register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
                                }
                            );
                    }
            }
        }
        
        /* Attaches meta boxes to the post type */
        public function add_meta_box( $title, $fields = array(), $context = 'normal', $priority = 'default' )
        {
            if( ! empty( $title ) )
            {       
                // We need to know the Post Type name again
                $post_type_name = $this->post_type_name;

                // Meta variables   
                $box_id         = self::uglify( $title );
                $box_title      = self::beautify( $title );
                $box_context    = $context;
                $box_priority   = $priority;

                // Make the fields global
                global $custom_fields;
                $custom_fields[$title] = $fields;

                add_action( 'admin_init',
                        function() use( $box_id, $box_title, $post_type_name, $box_context, $box_priority, $fields )
                        {               
                            add_meta_box(
                                $box_id,
                                $box_title,
                                function( $post, $data )
                                {
                                    global $post;

                                    // Nonce field for some validation
                                    wp_nonce_field( plugin_basename( __FILE__ ), 'cp_nounce' );

                                    // Get all inputs from $data
                                    $custom_fields = $data['args'][0];

                                    // Get the saved values
                                    //$meta = get_post_custom( $post->ID );

                                    // Check the array and loop through it
                                    if( ! empty( $custom_fields ) )
                                    {
                                          // Begin the field table and loop
                                        echo '<table class="form-table">';
                                        foreach ($custom_fields as $field) {

                                            $prefix = '_cp_'; //Underscore to keep it hidden from custom fields
                                            $field_id_name = $prefix  . call_user_func("CP_Helper::uglify", $field['name']);

                                            $field['id'] = $field_id_name;

                                            array_push($field, $field['id']);

                                            // get value of this field if it exists for this post
                                            $meta = get_post_meta($post->ID, $field['id'], true);

                                            // begin a table row with
                                            echo '<tr>
                                                    <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                                                    <td>';
                                                    switch($field['type']) {
                                                        // text
                                                        case 'text':
                                                            echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                                                    <br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // textarea
                                                        case 'textarea':
                                                            echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
                                                                    <br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // checkbox
                                                        case 'checkbox':
                                                            echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                                                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                                                        break;
                                                        // select
                                                        case 'select':
                                                            echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                                                            foreach ($field['options'] as $option) {
                                                                echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                                                            }
                                                            echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // radio
                                                        case 'radio':
                                                            foreach ( $field['options'] as $option ) {
                                                                echo '<input type="radio" name="'.$field['id'].'" id="'.$option['value'].'" value="'.$option['value'].'" ',$meta == $option['value'] ? ' checked="checked"' : '',' />
                                                                        <label for="'.$option['value'].'">'.$option['label'].'</label><br />';
                                                            }
                                                            echo '<span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // checkbox_group
                                                        case 'checkbox_group':
                                                            foreach ($field['options'] as $option) {
                                                                echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' /> 
                                                                        <label for="'.$option['value'].'">'.$option['label'].'</label><br />';
                                                            }
                                                            echo '<span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // tax_select
                                                        case 'tax_select':
                                                            echo '<select name="'.$field['id'].'" id="'.$field['id'].'">
                                                                    <option value="">Select One</option>'; // Select One
                                                            $terms = get_terms($field['id'], 'get=all');
                                                            $selected = wp_get_object_terms($post->ID, $field['id']);
                                                            foreach ($terms as $term) {
                                                                if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug)) 
                                                                    echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>'; 
                                                                else
                                                                    echo '<option value="'.$term->slug.'">'.$term->name.'</option>'; 
                                                            }
                                                            $taxonomy = get_taxonomy($field['id']);
                                                            echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'">Manage '.$taxonomy->label.'</a></span>';
                                                        break;
                                                        // post_list
                                                        case 'post_list':
                                                        $items = get_posts( array (
                                                            'post_type' => $field['post_type'],
                                                            'posts_per_page' => -1
                                                        ));
                                                            echo '<select name="'.$field['id'].'" id="'.$field['id'].'">
                                                                    <option value="">Select One</option>'; // Select One
                                                                foreach($items as $item) {
                                                                    echo '<option value="'.$item->ID.'"',$meta == $item->ID ? ' selected="selected"' : '','>'.$item->post_type.': '.$item->post_title.'</option>';
                                                                } // end foreach
                                                            echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // date
                                                        case 'date':
                                                            echo '<input type="text" class="datepicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                                                    <br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // slider
                                                        case 'slider':
                                                        $value = $meta != '' ? $meta : '0';
                                                            echo '<div id="'.$field['id'].'-slider"></div>
                                                                    <input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" size="5" />
                                                                    <br /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // image
                                                        case 'image':
                                                            $image = get_template_directory_uri().'/lib/custom_post_helper/cp_helper/img/image.png';  
                                                            echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';
                                                            if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium'); $image = $image[0]; }               
                                                            echo    '<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$meta.'" />
                                                                        <img src="'.$image.'" class="custom_preview_image" alt="" /><br />
                                                                            <input class="custom_upload_image_button button" type="button" value="Choose Image" data-cp-post-id="'.$post->ID.'" />
                                                                            <small>&nbsp;<a href="#" class="custom_clear_image_button">Remove Image</a></small>
                                                                            <br clear="all" /><span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        // repeatable
                                                        case 'repeatable':
                                                            echo '<a class="repeatable-add button" href="#">+</a>
                                                                    <ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
                                                            $i = 0;
                                                            if ($meta) {
                                                                foreach($meta as $row) {
                                                                    echo '<li><span class="sort hndle">|||</span>
                                                                                <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" size="30" />
                                                                                <a class="repeatable-remove button" href="#">-</a></li>';
                                                                    $i++;
                                                                }
                                                            } else {
                                                                echo '<li><span class="sort hndle">|||</span>
                                                                            <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />
                                                                            <a class="repeatable-remove button" href="#">-</a></li>';
                                                            }
                                                            echo '</ul>
                                                                <span class="description">'.$field['desc'].'</span>';
                                                        break;
                                                        //Wordpress Editor
                                                        case 'wysiwyg':
                                                                $args = array_merge(
                                                                    // Default Options
                                                                    array(
                                                                        'media_buttons' => true,
                                                                    ), $field['options'] 
                                                                );
                                                            wp_editor($meta,$field['id'],$args);
                                                        break;
                                                    } //end switch
                                            echo '</td></tr>';
                                        } // end foreach
                                        echo '</table>'; // end table
                                    }

                                },
                                $post_type_name,
                                $box_context,
                                $box_priority,
                                array( $fields )
                            );
                        }
                    );
            }

        }
        
        /* Listens for when the post type being saved */
        public function save()
        {
            // Need the post type name again
            $post_type_name = $this->post_type_name;

            add_action( 'save_post',
                function() use( $post_type_name )
                {
                    // Deny the wordpress autosave function
                    if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

                    if ($_POST && ! wp_verify_nonce( $_POST['cp_nounce'], plugin_basename(__FILE__) ) ) return;

                    global $post;

                    if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == $post_type_name )
                    {
                            if (!current_user_can('edit_page', $post->ID))
                            {    
                                return $post->ID;
                            } elseif (!current_user_can('edit_post', $post->ID)) 
                            {
                                return $post->ID;
                            }    
                        global $custom_fields;

                        // Loop through each meta box
                        foreach( $custom_fields as $title => $fields )
                        {
                            foreach ($fields as $field) {

                                    $prefix = '_cp_'; //Underscore to keep it hidden from custom fields
                                    $field_id_name = $prefix  . call_user_func("CP_Helper::uglify", $field['name']);

                                    $field['id'] = $field_id_name;

                                    array_push($field, $field['id']);

                                if($field['type'] == 'tax_select') continue;
                                if($field['type'] == 'wysiwyg'){ 
                                    $new = wpautop(wptexturize($_POST[$field['id']]));
                                }else{
                                    $new = $_POST[$field['id']];
                                }
                                $old = get_post_meta($post->ID, $field['id'], true);

                                if ($new && $new != $old) {
                                    update_post_meta($post->ID, $field['id'], $new);
                                } elseif ('' == $new && $old) {
                                    delete_post_meta($post->ID, $field['id'], $old);
                                }
                            } // end foreach
                        }
                    }
                }
            );
        }


        public function add_custom_js_css()
        {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-slider');
            wp_enqueue_script('cp-helper', get_template_directory_uri().'/js/cp-helper.js');
            wp_enqueue_style('jquery-ui-custom', get_template_directory_uri().'/css/jquery-ui-custom.css');
        }

        public function add_custom_scripts() 
        {
            global $custom_fields, $post;
            
            $output = '<script type="text/javascript">
                        jQuery(function() {';

            foreach ($custom_fields as $title => $fields) { // loop through the fields looking for certain types
                        foreach ($fields as $field) {
                        // date
                        if($field['type'] == 'date')
                            $output .= 'jQuery(".datepicker").datepicker();';
                        // slider
                        if ($field['type'] == 'slider') {
                            $value = get_post_meta($post->ID, $field['id'], true);
                            if ($value == '') $value = $field['min'];
                            $output .= '
                                    jQuery( "#'.$field['id'].'-slider" ).slider({
                                        value: '.$value.',
                                        min: '.$field['min'].',
                                        max: '.$field['max'].',
                                        step: '.$field['step'].',
                                        slide: function( event, ui ) {
                                            jQuery( "#'.$field['id'].'" ).val( ui.value );
                                        }
                                    });';
                        }
                    }
            }
            
            $output .= '});
                </script>';
                
            echo $output;
        }

        public static function beautify( $string )
        {
            return ucwords( str_replace( '_', ' ', $string ) );
        }

        public static function uglify( $string )
        {
            return strtolower( str_replace( ' ', '_', $string ) );
        }
        public static function pluralize( $string )
        {
            $last = $string[strlen( $string ) - 1];

            if( $last == 'y' )
            {
                $cut = substr( $string, 0, -1 );
                //convert y to ies
                $plural = $cut . 'ies';
            }
            else
            {
                // just attach an s
                $plural = $string . 's';
            }

            return $plural;
        }
    }