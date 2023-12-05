<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class Disciple_Tools_Prayer_Requests_Base
 * Load the core post type hooks into the Disciple.Tools system
 */
class Disciple_Tools_Prayer_Requests_Base extends DT_Module_Base {

    /**
     * Define post type variables
     * @todo update these variables with your post_type, module key, and names.
     * @var string
     */
    public $post_type = "prayer_request";
    public $module = "prayer_request_base";
    public $single_name = 'Prayer Request';
    public $plural_name ='Prayer Requests';
    public static function post_type(){
        return 'prayer_request';
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        parent::__construct();
        if ( !self::check_enabled_and_prerequisites() ){
            return;
        }

        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 20, 1 ); //after contacts

        //setup tiles and fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
         add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );

        // hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_update_fields", [ $this, "dt_post_update_fields" ], 10, 3 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 10, 2 );
        add_action( "dt_post_created", [ $this, "dt_post_created" ], 10, 3 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 10, 4 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );
        add_filter( "dt_filter_access_permissions", [ $this, "dt_filter_access_permissions" ], 20, 2 );

    }

    public function after_setup_theme(){
        $this->single_name = __( "Prayer Request", 'disciple-tools-prayer-requests' );
        $this->plural_name = __( "Prayer Requests", 'disciple-tools-prayer-requests' );
        
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( $this->post_type, $this->single_name, $this->plural_name );
        }
    }

        /**
     * Set the singular and plural translations for this post types settings
     * The add_filter is set onto a higher priority than the one in Disciple_tools_Post_Type_Template
     * so as to enable localisation changes. Otherwise the system translation passed in to the custom post type
     * will prevail.
     */
    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $settings['label_singular'] = __( "Prayer Request", 'disciple-tools-prayer-requests' );
            $settings['label_plural'] = __( "Prayer Requests", 'disciple-tools-prayer-requests' );
        }
        return $settings;
    }

    /**
     * @todo define the permissions for the roles
     * Documentation
     * @link https://github.com/DiscipleTools/Documentation/blob/master/Theme-Core/roles-permissions.md#rolesd
     */
    public function dt_set_roles_and_permissions( $expected_roles ){

        if ( !isset( $expected_roles["multiplier"] ) ){
            $expected_roles["multiplier"] = [

                "label" => __( 'Multiplier', 'disciple-tools-prayer-requests' ),
                "description" => "Interacts with Contacts and Groups",
                "permissions" => []
            ];
        }

        // if the user can access contact they also can access this post type
        foreach ( $expected_roles as $role => $role_value ){
            if ( isset( $expected_roles[$role]["permissions"]['access_contacts'] ) && $expected_roles[$role]["permissions"]['access_contacts'] ){
                $expected_roles[$role]["permissions"]['access_' . $this->post_type ] = true;
                $expected_roles[$role]["permissions"]['create_' . $this->post_type] = true;
                // $expected_roles[$role]["permissions"]['update_' . $this->post_type] = true;
                // $expected_roles[$role]["permissions"]['view_any_'.$this->post_type ] = true;
            }
        }

        if ( isset( $expected_roles["administrator"] ) ){
            $expected_roles["administrator"]["permissions"]['view_any_'.$this->post_type ] = true;
            $expected_roles["administrator"]["permissions"]['update_any_'.$this->post_type ] = true;
        }
        if ( isset( $expected_roles["dt_admin"] ) ){
            $expected_roles["dt_admin"]["permissions"]['view_any_'.$this->post_type ] = true;
            $expected_roles["dt_admin"]["permissions"]['update_any_'.$this->post_type ] = true;
        }

        return $expected_roles;
    }

    /**
     * @todo define fields
     * Documentation
     * @link https://github.com/DiscipleTools/Documentation/blob/master/Theme-Core/fields.md
     */
    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === $this->post_type ){



            /**
             * @todo configure status appropriate to your post type
             * @todo modify strings and add elements to default array
             */
            $fields['status'] = [
                'name'        => __( 'Status', 'disciple-tools-prayer-requests' ),
                'description' => __( 'Set the current status.', 'disciple-tools-prayer-requests' ),
                'type'        => 'key_select',
                'default'     => [
                    'answered' => [
                        'label' => __( 'Prayer Request Answered', 'disciple-tools-prayer-requests' ),
                        'description' => __( 'Prayer request has been answered.', 'disciple-tools-prayer-requests' ),
                        'color' => "#F43636"
                    ],
                    'active'   => [
                        'label' => __( 'Active', 'disciple-tools-prayer-requests' ),
                        'description' => __( 'Prayer request is active.', 'disciple-tools-prayer-requests' ),
                        'color' => "#4CAF50"
                    ],
                ],
                'tile'     => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/status.svg',
                "default_color" => "#366184",
                "show_in_table" => 10,
            ];
            // $fields["subassigned"] = [
            //     "name" => __( "Visible to", 'disciple_tools' ),
            //     "description" => __( "Contact or User that can view this prayer request.", 'disciple_tools' ),
            //     "type" => "connection",
            //     "post_type" => "prayer_request",
            //     "p2p_direction" => "to",
            //     "p2p_key" => "prayer_request_to_subassigned",
            //     "tile" => "status",
            //     "custom_display" => false,
            //     'icon' => get_template_directory_uri() . "/dt-assets/images/subassigned.svg?v=2",
            // ];
            $fields['assigned_to'] = [
                'name'        => __( 'Assigned To', 'disciple-tools-prayer-requests' ),
                'description' => __( "This person is responsible to follow up with and update this prayer request.", 'disciple-tools-prayer-requests' ),
                'type'        => 'user_select',
                'default'     => '',
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/assigned-to.svg',
                "show_in_table" => 16,
            ];


            /**
             * Common and recommended fields
             */
            // $fields['start_date'] = [
            //     'name'        => __( 'Start Date', 'disciple-tools-prayer-requests' ),
            //     'description' => '',
            //     'type'        => 'date',
            //     'default'     => time(),
            //     'tile' => 'details',
            //     'icon' => get_template_directory_uri() . '/dt-assets/images/date-start.svg',
            // ];
            // $fields['end_date'] = [
            //     'name'        => __( 'End Date', 'disciple-tools-prayer-requests' ),
            //     'description' => '',
            //     'type'        => 'date',
            //     'default'     => '',
            //     'tile' => 'details',
            //     'icon' => get_template_directory_uri() . '/dt-assets/images/date-end.svg',
            // ];
            // $fields["multi_select"] = [
            //     'name' => __( 'Multi-Select', 'disciple-tools-prayer-requests' ),
            //     'description' => __( "Multi Select Field", 'disciple-tools-prayer-requests' ),
            //     'type' => 'multi_select',
            //     'default' => [
            //         'item_1' => [
            //             'label' => __( 'Item 1', 'disciple-tools-prayer-requests' ),
            //             'description' => __( 'Item 1.', 'disciple-tools-prayer-requests' ),
            //         ],
            //         'item_2' => [
            //             'label' => __( 'Item 2', 'disciple-tools-prayer-requests' ),
            //             'description' => __( 'Item 2.', 'disciple-tools-prayer-requests' ),
            //         ],
            //         'item_3' => [
            //             'label' => __( 'Item 3', 'disciple-tools-prayer-requests' ),
            //             'description' => __( 'Item 3.', 'disciple-tools-prayer-requests' ),
            //         ],
            //     ],
            //     "tile" => "details",
            //     "in_create_form" => true,
            //     'icon' => get_template_directory_uri() . "/dt-assets/images/languages.svg?v=2",
            // ];


            /**
             * @todo this adds people groups support to this post type. remove if not needed.
             * Connections to other post types
             */
            // $fields["groups"] = [
            //     "name" => __( 'Related People Groups', 'disciple-tools-prayer-requests' ),
            //     'description' => __( 'The groups connected to this prayer request.', 'disciple-tools-prayer-requests' ),
            //     "type" => "connection",
            //     "tile" => 'details',
            //     "post_type" => "peoplegroups",
            //     "p2p_direction" => "to",
            //     "p2p_key" => $this->post_type."_to_groups",
            //     'icon' => get_template_directory_uri() . "/dt-assets/images/group.svg",
            // ];

            $fields['contacts'] = [
                "name" => __( 'Related Contacts', 'disciple-tools-prayer-requests' ),
                "description" => __( 'The contacts connected to this prayer request.', 'disciple-tools-prayer-requests' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => $this->post_type."_to_contacts",
                "tile" => "status",
                'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
                'create-icon' => get_template_directory_uri() . "/dt-assets/images/add-contact.svg",
                "show_in_table" => 35
            ];
            $fields['groups'] = [
                "name" => __( 'Related Groups', 'disciple-tools-prayer-requests' ),
                "description" => __( 'The groups connected to this prayer request.', 'disciple-tools-prayer-requests' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "to",
                "p2p_key" => $this->post_type."_to_groups",
                "tile" => "status",
                'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
                'create-icon' => get_template_directory_uri() . "/dt-assets/images/add-contact.svg",
                "show_in_table" => 35
            ];
        }

        /**
         * @todo this adds connection to contacts. remove if not needed.
         */
        // if ( $post_type === "contacts" ){
        //     $fields[$this->post_type] = [
        //         "name" => $this->plural_name,
        //         "description" => '',
        //         "type" => "connection",
        //         "post_type" => $this->post_type,
        //         "p2p_direction" => "from",
        //         "p2p_key" => $this->post_type."_to_contacts",
        //         "tile" => "other",
        //         'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
        //         'create-icon' => get_template_directory_uri() . "/dt-assets/images/add-group.svg",
        //         "show_in_table" => 35
        //     ];
        // }

        /**
         * @todo this adds connection to groups. remove if not needed.
         */
        $fields[$this->post_type] = [
            "name" => $this->plural_name,
            "description" => '',
            "type" => "connection",
            "post_type" => $this->post_type,
            "p2p_direction" => "from",
            "p2p_key" => $this->post_type."_to_".$post_type,
            "tile" => "status",
            "show_in_table" => 35
        ];
        return $fields;
    }

    /**
     * @todo define tiles
     * @link https://github.com/DiscipleTools/Documentation/blob/master/Theme-Core/field-and-tiles.md
     */
    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        if ( $post_type === $this->post_type ){
            $tiles["disciple_tools_prayer_requests-2"] = [ "label" => __( "Prayer Requests", 'disciple-tools-prayer-requests' ) ];
        }
        return $tiles;
    }

    /**
     * @todo define additional section content
     * Documentation
     * @link https://github.com/DiscipleTools/Documentation/blob/master/Theme-Core/field-and-tiles.md#add-custom-content
     */
    public function dt_details_additional_section( $section, $post_type ){
        if ( $section === "disciple_tools_prayer_requests-2" ) {
            $this_post = DT_Posts::get_post( $post_type, get_the_ID() );

            $post_type_fields = DT_Posts::get_post_field_settings( $post_type );
            $post_type_label = DT_Posts::get_post_settings( get_post_type() ?: "contacts" )['label_singular'];

            if ( $post_type === "prayer_request" ) { ?>
                <div class="cell small-12 medium-4">
                    <span class="prayer_request_content_container">
                    <p class="prayer_request_content"><?php echo esc_html( $this_post['disciple_tools_prayer_requests_text'] ); ?></p>
                    <?php
                    if ( current_user_can( "assign_any_contacts" ) || isset( $this_post["assigned_to"]["id"] ) && $this_post["assigned_to"]["id"] == get_current_user_id() ) : ?>
                    <a class="edit-prayer-request" style="margin-right:5px">
                        <img class="dt-blue-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/edit.svg' ) ?>">
                        edit
                    </a>
                    <?php endif ?>
                    </span>
                    </span>
                    <textarea id="disciple_tools_prayer_requests_text" class="textarea prayer_request_content_editable" style="display:none"><?php echo esc_html( $this_post['disciple_tools_prayer_requests_text'] ); ?></textarea>
                    <?php if ( get_option( 'dt_googletranslate_api_key' ) ) : ?>
                        <div class="translation_container">
                            <div class="prayer-request-translation-bubble" dir=auto></div>
                            <a class="prayer-request-translate-button showTranslation"><?php esc_html_e( "Translate with Google Translate", "disciple_tools" ) ?></a>
                            <a class="prayer-request-translate-button hideTranslation hide"><?php esc_html_e( "Hide Translation", "disciple_tools" ) ?></a>
                        </div>
                    <?php endif ?>
                    </div>
                    <script>
                        jQuery(document).on("click", '.prayer-request-translate-button.showTranslation', function() {
                        let combinedArray = [];
                        jQuery('.prayer_request_content').each(function(index, comment) {
                        let sourceText = jQuery(comment).text();
                        sourceText = sourceText.replace(/\s+/g, ' ').trim();
                        combinedArray[index] = sourceText;
                        })

                        let translation_bubble = jQuery(this).siblings('.prayer-request-translation-bubble');
                        let translation_hide = jQuery(this).siblings('.prayer-request-translate-button.hideTranslation');

                        let url = `https://translation.googleapis.com/language/translate/v2?key=${window.lodash.escape(commentsSettings.google_translate_key)}`
                        let targetLang;
                        let langcode = document.querySelector('html').getAttribute('lang') ? document.querySelector('html').getAttribute('lang').replace('_', '-') : "en";

                        if (langcode !== "zh-TW") {
                        targetLang = langcode.substr(0,2);
                        } else {
                        targetLang = langcode;
                        }

                        function google_translate_fetch(postData, translate_button, arrayStartPos = 0) {
                        fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(postData),
                            })
                            .then(response => response.json())
                            .then((result) => {

                            jQuery.each(result.data.translations, function( index, translation ) {
                                jQuery(translation_bubble[index + arrayStartPos]).append(translation.translatedText);
                            });
                            translation_hide.removeClass('hide');
                            jQuery(translate_button).addClass('hide');
                            })
                        }

                        if( combinedArray.length <= 128) {
                        let postData = {
                            "q": combinedArray,
                            "target": targetLang
                        }
                        google_translate_fetch(postData, this);
                        } else {
                        var i,j,temparray,chunk = 128;
                        for (i=0,j=combinedArray.length; i<j; i+=chunk) {
                            temparray = combinedArray.slice(i,i+chunk);

                            let postData = {
                                "q": temparray,
                                "target": targetLang
                            }
                            google_translate_fetch(postData, this, i);
                        }
                        }

                    })
                    jQuery(document).on("click", '.prayer-request-translate-button.hideTranslation', function() {
                        prayer_request_hide_translation(this)
                    })

                    function prayer_request_hide_translation(element) {
                        let translation_bubble = jQuery('.prayer-request-translation-bubble');
                        let translate_button = jQuery('.prayer-request-translate-button.showTranslation')

                        translation_bubble.empty();
                        jQuery('.prayer-request-translate-button.hideTranslation').addClass('hide');
                        translate_button.removeClass('hide');
                    }

                    function toggle_prayer_request_edit() {
                        jQuery('.prayer_request_content_container').toggle();
                        jQuery('.prayer_request_content_editable').toggle();
                        jQuery('.translation_container').toggle();
                        prayer_request_hide_translation();
                    }

                    jQuery(document).on("click", '.edit-prayer-request', function () {
                        toggle_prayer_request_edit();
                    })

                    $('#disciple_tools_prayer_requests_text').change(function(){
                        API.update_post(detailsSettings.post_type, detailsSettings.post_id, {'disciple_tools_prayer_requests_text': jQuery('#disciple_tools_prayer_requests_text').val()}).then((newPost)=>{
                            //$(`#${id}-spinner`).removeClass('active')
                            $('.prayer_request_content').text(newPost.disciple_tools_prayer_requests_text);
                            toggle_prayer_request_edit();
                        })
                    })
                    </script>
            <?php }
        }
    }

    /**
     * action when a post connection is added during create or update
     * @todo catch field changes and do additional processing
     *
     * The next three functions are added, removed, and updated of the same field concept
     */
    public function post_connection_added( $post_type, $post_id, $field_key, $value ){
//        if ( $post_type === $this->post_type ){
//            if ( $field_key === "members" ){
//                // @todo change 'members'
//                // execute your code here, if field key match
//            }
//            if ( $field_key === "coaches" ){
//                // @todo change 'coaches'
//                // execute your code here, if field key match
//            }
//        }
//        if ( $post_type === "contacts" && $field_key === $this->post_type ){
//            // execute your code here, if a change is made in contacts and a field key is matched
//        }
    }

    //action when a post connection is removed during create or update
    public function post_connection_removed( $post_type, $post_id, $field_key, $value ){
//        if ( $post_type === $this->post_type ){
//            // execute your code here, if connection removed
//        }
    }

    //filter at the start of post update
    public function dt_post_update_fields( $fields, $post_type, $post_id ){
//        if ( $post_type === $this->post_type ){
//            // execute your code here
//        }
        return $fields;
    }


    //filter when a comment is created
    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
    }

    // filter at the start of post creation
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === $this->post_type ){
            $post_fields = DT_Posts::get_post_field_settings( $post_type );
            if ( isset( $post_fields["status"] ) && !isset( $fields["status"] ) ){
                $fields["status"] = "active";
            }
        }
        return $fields;
    }

    //action when a post has been created
    public function dt_post_created( $post_type, $post_id, $initial_fields ){
    }

    //list page filters function

    /**
     * @todo adjust queries to support list counts
     * Documentation
     * @link https://github.com/DiscipleTools/Documentation/blob/master/Theme-Core/list-query.md
     */
    private static function get_my_status(){
        /**
         * @todo adjust query to return count for update needed
         */
        global $wpdb;
        $post_type = self::post_type();
        $current_user = get_current_user_id();

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT status.meta_value as status, count(pm.post_id) as count, count(un.post_id) as update_needed
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = %s and a.post_status = 'publish' )
            INNER JOIN $wpdb->postmeta status ON ( status.post_id = pm.post_id AND status.meta_key = 'status' )
            INNER JOIN $wpdb->postmeta as assigned_to ON a.ID=assigned_to.post_id
              AND assigned_to.meta_key = 'assigned_to'
              AND assigned_to.meta_value = CONCAT( 'user-', %s )
            LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
            GROUP BY status.meta_value, pm.meta_value
        ", $post_type, $current_user ), ARRAY_A);

        return $results;
    }

    //list page filters function
    private static function get_all_status_types(){
        /**
         * @todo adjust query to return count for update needed
         */
        global $wpdb;
        if ( current_user_can( 'view_any_'.self::post_type() ) ){
            $results = $wpdb->get_results($wpdb->prepare( "
                SELECT status.meta_value as status, count(status.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta status
                INNER JOIN $wpdb->posts a ON( a.ID = status.post_id AND a.post_type = %s and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = status.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE status.meta_key = 'status'
                GROUP BY status.meta_value
            ", self::post_type() ), ARRAY_A );
        } else {
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT status.meta_value as status, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'status' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = %s and a.post_status = 'publish' )
                LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = a.ID AND shares.user_id = %s )
                LEFT JOIN $wpdb->postmeta assigned_to ON ( assigned_to.post_id = pm.post_id AND assigned_to.meta_key = 'assigned_to' && assigned_to.meta_value = %s )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE ( shares.user_id IS NOT NULL OR assigned_to.meta_value IS NOT NULL )
                GROUP BY status.meta_value, pm.meta_value
            ", self::post_type(), get_current_user_id(), 'user-' . get_current_user_id() ), ARRAY_A);
        }

        return $results;
    }

    //build list page filters
    public static function dt_user_list_filters( $filters, $post_type ){
        /**
         * @todo process and build filter lists
         */
        if ( $post_type === self::post_type() ){
            $counts = self::get_my_status();
            $fields = DT_Posts::get_post_field_settings( $post_type );
            /**
             * Setup my filters
             */
            $active_counts = [];
            $update_needed = 0;
            $status_counts = [];
            $total_my = 0;
            foreach ( $counts as $count ){
                $total_my += $count["count"];
                dt_increment( $status_counts[$count["status"]], $count["count"] );
                if ( $count["status"] === "active" ){
                    if ( isset( $count["update_needed"] ) ) {
                        $update_needed += (int) $count["update_needed"];
                    }
                    dt_increment( $active_counts[$count["status"]], $count["count"] );
                }
            }

            $filters["tabs"][] = [
                "key" => "assigned_to_me",
                "label" => __( "Assigned to me", 'disciple-tools-prayer-requests' ),
                "count" => $total_my,
                "order" => 20
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'my_all',
                'tab' => 'assigned_to_me',
                'name' => __( "All", 'disciple-tools-prayer-requests' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'sort' => 'status'
                ],
                "count" => $total_my,
            ];
            foreach ( $fields["status"]["default"] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) ){
                    $filters["filters"][] = [
                        "ID" => 'my_' . $status_key,
                        "tab" => 'assigned_to_me',
                        "name" => $status_value["label"],
                        "query" => [
                            'assigned_to' => [ 'me' ],
                            'status' => [ $status_key ],
                            'sort' => '-post_date'
                        ],
                        "count" => $status_counts[$status_key]
                    ];
                    if ( $status_key === "active" ){
                        if ( $update_needed > 0 ){
                            $filters["filters"][] = [
                                "ID" => 'my_update_needed',
                                "tab" => 'assigned_to_me',
                                "name" => $fields["requires_update"]["name"],
                                "query" => [
                                    'assigned_to' => [ 'me' ],
                                    'status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                ],
                                "count" => $update_needed,
                                'subfilter' => true
                            ];
                        }
                    }
                }
            }

            if ( current_user_can( 'view_any_' . self::post_type() ) ){
                $counts = self::get_all_status_types();
                $active_counts = [];
                $update_needed = 0;
                $status_counts = [];
                $total_all = 0;
                foreach ( $counts as $count ){
                    $total_all += $count["count"];
                    dt_increment( $status_counts[$count["status"]], $count["count"] );
                    if ( $count["status"] === "active" ){
                        if ( isset( $count["update_needed"] ) ) {
                            $update_needed += (int) $count["update_needed"];
                        }
                        dt_increment( $active_counts[$count["status"]], $count["count"] );
                    }
                }
                $filters["tabs"][] = [
                    "key" => "all",
                    "label" => __( "All", 'disciple-tools-prayer-requests' ),
                    "count" => $total_all,
                    "order" => 10
                ];
                // add assigned to me filters
                $filters["filters"][] = [
                    'ID' => 'all',
                    'tab' => 'all',
                    'name' => __( "All", 'disciple-tools-prayer-requests' ),
                    'query' => [
                        'sort' => '-post_date'
                    ],
                    "count" => $total_all
                ];

                foreach ( $fields["status"]["default"] as $status_key => $status_value ) {
                    if ( isset( $status_counts[$status_key] ) ){
                        $filters["filters"][] = [
                            "ID" => 'all_' . $status_key,
                            "tab" => 'all',
                            "name" => $status_value["label"],
                            "query" => [
                                'status' => [ $status_key ],
                                'sort' => '-post_date'
                            ],
                            "count" => $status_counts[$status_key]
                        ];
                        if ( $status_key === "active" ){
                            if ( $update_needed > 0 ){
                                $filters["filters"][] = [
                                    "ID" => 'all_update_needed',
                                    "tab" => 'all',
                                    "name" => $fields["requires_update"]["name"],
                                    "query" => [
                                        'status' => [ 'active' ],
                                        'requires_update' => [ true ],
                                    ],
                                    "count" => $update_needed,
                                    'subfilter' => true
                                ];
                            }
//                        foreach ( $fields["type"]["default"] as $type_key => $type_value ) {
//                            if ( isset( $active_counts[$type_key] ) ) {
//                                $filters["filters"][] = [
//                                    "ID" => 'all_' . $type_key,
//                                    "tab" => 'all',
//                                    "name" => $type_value["label"],
//                                    "query" => [
//                                        'status' => [ 'active' ],
//                                        'sort' => 'name'
//                                    ],
//                                    "count" => $active_counts[$type_key],
//                                    'subfilter' => true
//                                ];
//                            }
//                        }
                        }
                    }
                }
            }
        }
        return $filters;
    }

    // access permission
    public static function dt_filter_access_permissions( $permissions, $post_type ){
        if ( $post_type === self::post_type() ){
            if ( DT_Posts::can_view_all( $post_type ) ){
                $permissions = [];
            }
        }
        return $permissions;
    }

    // scripts
    public function scripts(){
        if ( is_singular( $this->post_type ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            $test = "";
            // @todo add enqueue scripts
        }
    }
}


