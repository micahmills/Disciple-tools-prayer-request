<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Prayer_Requests_Tile
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        add_filter( 'dt_details_additional_tiles', [ $this, "dt_details_additional_tiles" ], 10, 2 );
        add_filter( "dt_custom_fields_settings", [ $this, "dt_custom_fields" ], 1, 2 );
        add_action( "dt_details_additional_section", [ $this, "dt_add_section" ], 30, 2 );
    }

    /**
     * This function registers a new tile to a specific post type
     *
     * @todo Set the post-type to the target post-type (i.e. contacts, groups, trainings, etc.)
     * @todo Change the tile key and tile label
     *
     * @param $tiles
     * @param string $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( $post_type === "contacts" || $post_type === "prayer_request" ){
            $tiles["disciple_tools_prayer_requests"] = [ "label" => __( "Prayer Requests", 'disciple-tools-prayer-requests' ) ];
        }
        return $tiles;
    }

    /**
     * @param array $fields
     * @param string $post_type
     * @return array
     */
    public function dt_custom_fields( array $fields, string $post_type = "" ) {
        /**
         * @todo set the post type
         */
        if ( $post_type === "prayer_request" ){
            /**
             * @todo Add the fields that you want to include in your tile.
             *
             * Examples for creating the $fields array
             * Contacts
             * @link https://github.com/DiscipleTools/disciple-tools-theme/blob/256c9d8510998e77694a824accb75522c9b6ed06/dt-contacts/base-setup.php#L108
             *
             * Groups
             * @link https://github.com/DiscipleTools/disciple-tools-theme/blob/256c9d8510998e77694a824accb75522c9b6ed06/dt-groups/base-setup.php#L83
             */

            /**
             * This is an example of a text field
             */
            $fields['disciple_tools_prayer_requests_text'] = [
                'name'        => __( 'Prayer Request Content', 'disciple-tools-prayer-requests' ),
                'description' => _x( 'Prayer Request Content', 'Optional Documentation', 'disciple-tools-prayer-requests' ),
                'type'        => 'textarea',
                'default'     => '',
                'tile' => 'disciple_tools_prayer_requests',
                "in_create_form" => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            ];
            /**
             * This is an example of a multiselect field
             */
            // $fields["disciple_tools_prayer_requests_multiselect"] = [
            //     "name" => __( 'Multiselect', 'disciple-tools-prayer-requests' ),
            //     "default" => [
            //         "one" => [ "label" => __( "One", 'disciple-tools-prayer-requests' ) ],
            //         "two" => [ "label" => __( "Two", 'disciple-tools-prayer-requests' ) ],
            //         "three" => [ "label" => __( "Three", 'disciple-tools-prayer-requests' ) ],
            //         "four" => [ "label" => __( "Four", 'disciple-tools-prayer-requests' ) ],
            //     ],
            //     "tile" => "disciple_tools_prayer_requests",
            //     "type" => "multi_select",
            //     "hidden" => false,
            //     'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            // ];
            /**
             * This is an example of a key select field
             */
            // $fields["disciple_tools_prayer_requests_keyselect"] = [
            //     'name' => "Key Select",
            //     'type' => 'key_select',
            //     "tile" => "disciple_tools_prayer_requests",
            //     'default' => [
            //         'first'   => [
            //             "label" => _x( 'First', 'Key Select Label', 'disciple-tools-prayer-requests' ),
            //             "description" => _x( "First Key Description", "Training Status field description", 'disciple-tools-prayer-requests' ),
            //             'color' => "#ff9800"
            //         ],
            //         'second'   => [
            //             "label" => _x( 'Second', 'Key Select Label', 'disciple-tools-prayer-requests' ),
            //             "description" => _x( "Second Key Description", "Training Status field description", 'disciple-tools-prayer-requests' ),
            //             'color' => "#4CAF50"
            //         ],
            //         'third'   => [
            //             "label" => _x( 'Third', 'Key Select Label', 'disciple-tools-prayer-requests' ),
            //             "description" => _x( "Third Key Description", "Training Status field description", 'disciple-tools-prayer-requests' ),
            //             'color' => "#366184"
            //         ],
            //     ],
            //     'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            //     "default_color" => "#366184",
            //     "select_cannot_be_empty" => true
            // ];
        }
        return $fields;
    }

    public function dt_add_section( $section, $post_type ) {
        /**
         * @todo set the post type and the section key that you created in the dt_details_additional_tiles() function
         */
        if ( ( $post_type === "contacts" ) && $section === "disciple_tools_prayer_requests" ){
            /**
             * These are two sets of key data:
             * $this_post is the details for this specific post
             * $post_type_fields is the list of the default fields for the post type
             *
             * You can pull any query data into this section and display it.
             */
            $this_post = DT_Posts::get_post( $post_type, get_the_ID() );
            $post_type_fields = DT_Posts::get_post_field_settings( $post_type );
            $post_type_label = DT_Posts::get_post_settings( get_post_type() ?: "contacts" )['label_singular'];
            ?>

            <!--
            @todo you can add HTML content to this section.
            -->
            <div class="cell small-12 medium-4">
                <div class="section-subheader"><?php echo esc_html( sprintf( _x( "Prayer Request for this %s", "Prayer Request for this Contact", 'disciple_tools' ), $post_type_label ?? $post_type ) ) ?></div>
                <?php foreach ( $this_post['prayer_request'] as $prayer_request ) :
                        $prayer_request_id = $prayer_request['ID'];
                        $prayer_request_post = DT_Posts::get_post( 'prayer_request', $prayer_request_id );
                    ?>
                    <a href="<?php echo esc_html( $prayer_request_post["permalink"] ) ?>" class="prayer_request_link">

                    <img class="dt-icon" <?php if ( $prayer_request_post['status']['key'] === 'answered' ) { echo esc_html( 'style=opacity:0.35' ); } ?> src="<?php echo esc_html( plugin_dir_url( __FILE__ ) ) ?>/praying-hands.svg"> <?php echo esc_html( $prayer_request_post["title"] ) ?><?php if ( $prayer_request_post['status']['key'] === 'answered' ) { echo esc_html( ' - '. $prayer_request_post['status']['label'] ); } ?></a><br>
                <?php endforeach; ?>

            </div>

            <div class="cell small-12 medium-4">
                <div class="section-subheader">
                    <img class="dt-icon" src="https://rsdt.local/wp-content/themes/disciple-tools-theme/dt-assets/images/name.svg">Name</span>
                </div>
                <input id="disciple_tools_prayer_requests_name" type="text" required="" class="" value="">
                <div class="section-subheader">
                    <img class="dt-icon" src="https://rsdt.local/wp-content/themes/disciple-tools-theme/dt-assets/images/edit.svg">Prayer Request Content
                </div>
                <textarea id="disciple_tools_prayer_requests_text" class="textarea"></textarea>
                <button id="disciple_tools_prayer_requests_button" class="button">Create Prayer Request</button>
            </div>

            <script>
                fields = {
                    "contacts": {
                        "values": [
                            {
                                "value": "<?php echo esc_html( get_the_ID() ) ?>"
                            }
                        ]
                    },
                    "name": "test",
                    "disciple_tools_prayer_requests_text": "test",
                    "assigned_to": <?php echo esc_html( get_current_user_id() ); ?>
                }

                var addPrayerRequestButton = document.querySelector("#disciple_tools_prayer_requests_button");

                addPrayerRequestButton.addEventListener('click', event => {
                    fields.disciple_tools_prayer_requests_text = document.querySelector("#disciple_tools_prayer_requests_text").value
                    fields.name = document.querySelector("#disciple_tools_prayer_requests_name").value

                    window.API.create_post('prayer_request', fields)
                });
            </script>
        <?php }
    }
}
Disciple_Tools_Prayer_Requests_Tile::instance();
