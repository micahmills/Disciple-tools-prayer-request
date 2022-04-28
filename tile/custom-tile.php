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
        $tiles["disciple_tools_prayer_requests"] = [ "label" => __( "Prayer Requests", 'disciple-tools-prayer-requests' ) ];
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
        // if ( $post_type === "prayer_request" ){
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
            // $fields['disciple_tools_prayer_requests_text'] = [
            //     'name'        => __( 'Prayer Request Content', 'disciple-tools-prayer-requests' ),
            //     'description' => _x( 'Prayer Request Content', 'Optional Documentation', 'disciple-tools-prayer-requests' ),
            //     'type'        => 'textarea',
            //     'default'     => '',
            //     'tile' => 'disciple_tools_prayer_requests',
            //     "in_create_form" => true,
            //     'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            // ];
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
        // }
        return $fields;
    }

    public function dt_add_section( $section, $post_type ) {
        /**
         * @todo set the post type and the section key that you created in the dt_details_additional_tiles() function
         */
        if ( $section === "disciple_tools_prayer_requests" ){
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
            <?php if ( $post_type === "contacts" || $post_type === "groups" ) { ?>
                <div id="connected_Prayer_Requests" class="cell small-12 medium-4">
                <div class="section-subheader"><?php echo esc_html( sprintf( _x( "Prayer Request for this %s", "Prayer Request for this Contact", 'disciple-tools-prayer-requests' ), $post_type_label ?? $post_type ) ) ?></div>
                <?php if ( array_key_exists( 'prayer_request', $this_post ) ) :
                    foreach ( $this_post['prayer_request'] as $prayer_request ) :
                            $prayer_request_id = $prayer_request['ID'];
                            $prayer_request_post = DT_Posts::get_post( 'prayer_request', $prayer_request_id );
                        ?>
                        <a href="<?php echo esc_html( $prayer_request_post["permalink"] ) ?>" class="prayer_request_link">

                        <img class="dt-icon" <?php if ( $prayer_request_post['status']['key'] === 'answered' ) { echo esc_html( 'style=opacity:0.35' ); } ?> src="<?php echo esc_html( plugin_dir_url( __FILE__ ) ) ?>/praying-hands.svg"> <?php echo esc_html( $prayer_request_post["title"] ) ?><?php if ( $prayer_request_post['status']['key'] === 'answered' ) { echo esc_html( ' - '. $prayer_request_post['status']['label'] ); } ?></a><br>
                    <?php endforeach;
                endif;
                ?>

            </div>

            <div class="cell small-12 medium-4">
                <div class="section-subheader">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/name.svg' ) ?>"><?php _e( 'Name', 'disciple-tools-prayer-requests' ) ?></span>
                </div>
                <input id="disciple_tools_prayer_requests_name" type="text" required="" class="" value="">
                <div class="section-subheader">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/edit.svg' ) ?>"><?php _e( 'Prayer Request Content', 'disciple-tools-prayer-requests' ) ?>
                </div>
                <textarea id="disciple_tools_prayer_requests_text" class="textarea"></textarea>
                <button id="disciple_tools_prayer_requests_button" class="button"><?php _e( 'Create Prayer Request', 'disciple-tools-prayer-requests' ) ?></button>
            </div>

            <script>
                fields = {
                    "<?php echo esc_html( $post_type ); ?>": {
                        "values": [
                            {
                                "value": "<?php echo esc_html( get_the_ID() ) ?>"
                            }
                        ]
                    },
                    "name": "",
                    "disciple_tools_prayer_requests_text": "",
                    "assigned_to": <?php echo esc_html( get_current_user_id() ); ?>
                }

                var addPrayerRequestButton = document.querySelector("#disciple_tools_prayer_requests_button");

                addPrayerRequestButton.addEventListener('click', event => {
                    fields.disciple_tools_prayer_requests_text = document.querySelector("#disciple_tools_prayer_requests_text").value
                    fields.name = document.querySelector("#disciple_tools_prayer_requests_name").value

                    window.API.create_post('prayer_request', fields).then((newRecord)=>{
                        let template = `<a href="${newRecord.permalink}" class="prayer_request_link"><img class="dt-icon" ${ newRecord.status.key === 'answered' ? `style=opacity:0.35` : '' } src="<?php echo esc_html( plugin_dir_url( __FILE__ ) ) ?>/praying-hands.svg"> ${ newRecord.name }${ newRecord.status.key === 'answered' ? ` - ${ newRecord.status.label }` : '' }</a><br>`;

                        document.querySelector('#connected_Prayer_Requests').insertAdjacentHTML('beforeend', template);
                        document.querySelector("#disciple_tools_prayer_requests_text").value = "";
                        document.querySelector("#disciple_tools_prayer_requests_name").value = "";
                    });
                });
            </script>
            <?php } else if ( $post_type === "prayer_request" ) { ?>
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
}
Disciple_Tools_Prayer_Requests_Tile::instance();
