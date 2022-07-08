<?php
/**
 * Class Primary_Category
 */
class Standard_Primary_Category {
    /**
     * Standard_Primary_Category constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'spc_add_metabox' ) );
        add_action( 'save_post', array( $this, 'spc_save_metabox' ) );
        add_shortcode( 'posts_by_primary_category', array( $this, 'posts_by_primary_category_shortcode' ) );
    }

    /**
     * add metabox function
     */
    public function spc_add_metabox() {
       
        $lists = $this->spc_generate_list();  // get the lists
        $post_types = $lists->post_type_list; // get post type list

        // check if there is any post type
        if ( ! empty( $post_types ) ) {
            // add metabox for each post type
            foreach ( $post_types as $post_type ) {
                add_meta_box (
                    'primary_category', // metabox id
                    'Choose Primary Category', // metabox title
                    array( $this, 'spc_metabox_callback' ), // metabox render callback function
                    $post_type, // screen to display metabox e.g post type
                    'side', // context
                    'high' // priority
                );
            }
        }
    }

    /**
     * metabox render callback function
     *
     * @param $post current post type object
     */
    public function spc_metabox_callback( $post ) {

        // add hidden nonce field to verify the save metabox action for the security purpose
        wp_nonce_field( 'spc_category_nonce', 'spc_category_nonce_field' );
        $lists = $this->spc_generate_list(); // get the generated lists

        $primary_category = '';
        $primary_selected_category = get_post_meta( $post->ID, 'primary_category', true ); // get saved primary category
       
        // check if primary selected category empty or not
        if ( $primary_selected_category != '' ) {
            $primary_category = $primary_selected_category;
        }
        $post_categories = $lists->categories_list;  // get list of categories associated with post

        // html to select the primary category
        $html = '';
        $html .= '<select class="primarycategory__main" name="primary_category" id="primary_category">';
        $html .= '<option value="0"> Choose Category </option>';
        // check if categories are exist then option will be added for each category
        if ( ! empty( $post_categories ) ) {
            foreach( $post_categories as $category ) {
                $html .= '<option value="' . $category->name . '" ' . selected( $primary_category, $category->name, false ) . '>' . ucwords($category->name) . '</option>';
            }
        }
        $html .= '</select>';
        $html .= '<small>Please Select a primary category for your post</small>';

        echo $html;
    }

    /**
     * Save the metabox on update or publish post
     *
     * @param $post_id current post id.
     */
    public function spc_save_metabox( $post_id ) {
        // return if nonce field is not set or nonce is not verified
        if( ! isset( $_POST['spc_category_nonce_field'] ) || ! wp_verify_nonce( $_POST['spc_category_nonce_field'],'spc_category_nonce' ) ) {
           return;
        }

        // check if primary_category selectbox has any selected option
        if ( isset( $_POST[ 'primary_category' ] ) ) {
            $primary_category = sanitize_text_field( $_POST[ 'primary_category' ] ); // sanatize text field value
            update_post_meta( $post_id, 'primary_category', $primary_category );  // update post meta and store new selected selectbox option
        }
    }

    /**
     * Build lists
     *
     * @return $list stdClass
     */
    public function spc_generate_list() {
        $list = new stdClass(); // create new stdClass

        // post type args
        $args = array(
            'public' => true, // only get publically accessable post types
            '_builtin' => false // remove builtin post types
        );
       
        $list->post_type_list = get_post_types( $args, 'names' );  // generate post type list
        $list->post_type_list['post'] = 'post'; // add buildin 'post' post type to post_type_list
        $list->categories_list = get_categories(); // generate categories list
        return $list;
    }

    // [posts_by_primary_category category="primary-category"]
    public function posts_by_primary_category_shortcode( $atts ) {
        $a = shortcode_atts( array(
            'category' => 'uncategorized',
        ), $atts );

        $defaults = array(
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'post_type'      => 'post',
        );

        $meta_query = array(
            'key'    => 'primary_category',
            'value'  => $a['category'],
        );

        $args               = wp_parse_args( $args, $defaults );
        $args['meta_query'] = array( $meta_query );

        $query = new WP_Query( $args );
        $html = '';
        ob_start();
        if ( $query->have_posts() ) : 
            while ( $query->have_posts() ) :
                $query->the_post(); ?>
                <div class="post">
                    <!-- Display the Title as a link to the Post's permalink. -->
                    <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

                    <p class="postmetadata"><?php esc_html_e( 'Posted in' ); ?> <?php the_category( ', ' ); ?></p>
                </div> <!-- closes the first div box -->
            <?php
            endwhile; 
        else : ?>
            <p><?php esc_html_e( 'Sorry, no posts with primary category ' .$a['category']. ' were found.' ); ?></p>
        <?php endif;
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
        wp_reset_postdata();
    }
}