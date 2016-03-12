<?php

  /**
   * Build the settings page for the Wordpress Analytics plugin,
   * using the settings library in settings.php.
   *
   * This file contains the structure of the settings page in
   * a nested array form, and the sanitization function.
   * 
   * Created by Guido W. Pettinari on 28.01.2016.
   * Part of Wordpress Analytics:
   * https://github.com/coccoinomane/wordpress_analytics
   */  


  // ==================================================================================
  // =                              Settings structure                                =
  // ==================================================================================

  /* Slug of the settings page */
  $wpan_menu_slug = 'wordpress-analytics-settings';
  
  /* Structure of the settings page. Each entry in this array is a section.
  The actual settings are listed in the 'fields' sub-array, along with their
  default values. You can add sections and fields here.
  
    For each field that you add to the $wpan_menu_structure array,
  you also need to write two functions in a new file:
    1) wpan_register_<section_name>() to populate the section with settings,
    2) wpan_display_<section_name>() to render the section in HTML.

    Similarly, for each field that you add to $wpan_menu_structure,
  remember to:
    1) add the field to the function wpan_register_<section_name>(),
    2) implement the function wpan_display_<field_name>() to render the field,
    3) sanitize the user input for the field in wpan_sanitize_options(), unless
       it is a checkbox. */

  $wpan_menu_structure = [
      'general_settings' => [
          'id' => 'general_settings',
          'name' => 'general_settings_section',
          'display' => "General settings",
          'page' => 'wpan_general_settings_page',
          'group' => 'wpan_general_settings_option_group',
          'db_key' => 'wpan:general_settings',
          'visible' => true,
          'func_register' => 'wpan_register_general_settings_fields',
          'func_display' => 'wpan_display_general_settings_section',
          'fields' => [
              'tracking_uid' => '',
              'scroll_tracking' => '0',
              'content_grouping' => '0',
              'call_tracking' => '0',
          ],
      ],
      'content_grouping' => [
          'id' => 'content_grouping',
          'name' => 'content_grouping_section',
          'display' => 'Content grouping',
          'page' => 'wpan_content_grouping_page',
          'group' => 'wpan_content_grouping_option_group',
          'db_key' => 'wpan:content_grouping',
          'visible' => true,
          'func_register' => 'wpan_register_content_grouping_fields',
          'func_display' => 'wpan_display_content_grouping_section',
          'fields' => [
              'group_index_wordpress' => '1',
              'group_index_woocommerce' => '2',
              'group_index_blog' => '3',
          ],
      ],
      // 'custom_dimensions' => [
      //     'id' => 'custom_dimensions',
      //     'name' => 'custom_dimensions_section',
      //     'display' => 'Custom dimensions',
      //     'page' => 'wpan_custom_dimensions_page',
      //     'group' => 'wpan_custom_dimensions_option_group',
      //     'db_key' => 'wpan:custom_dimensions',
      //     'visible' => true,
      //     'func_register' => 'wpan_register_custom_dimensions_fields',
      //     'func_display' => 'wpan_display_custom_dimensions_section',
      //     'fields' => [
      //         'custom_dimensions_repeater' => [
      //             'index' => '1',
      //             'type' => 'taxonomy',
      //             'name' => 'category'
      //         ],
      //     ],
      // ],
      'scroll_tracking' => [
          'id' => 'scroll_tracking',
          'name' => 'scroll_tracking_section',
          'display' => 'Scroll tracking',
          'page' => 'wpan_scroll_tracking_page',
          'group' => 'wpan_scroll_tracking_option_group',
          'visible' => true,
          'db_key' => 'wpan:scroll_tracking',
          'func_register' => 'wpan_register_scroll_tracking_fields',
          'func_display' => 'wpan_display_scroll_tracking_section',
          'fields' => [
              'pixel_threshold' => '300',
              'time_threshold' => '60',
          ],
      ],
      'call_tracking' => [
          'id' => 'call_tracking',
          'name' => 'call_tracking_section',
          'display' => 'Call tracking',
          'page' => 'wpan_call_tracking_page',
          'group' => 'wpan_call_tracking_option_group',
          'visible' => false,
          'db_key' => 'wpan:call_tracking',
          'func_register' => 'wpan_register_call_tracking_fields',
          'func_display' => 'wpan_display_call_tracking_section',
          'fields' => [
              'phone_regex_include_pattern' => '',
              'phone_regex_exclude_pattern' => '',
              'detect_phone_numbers' => '0',
          ],
      ],
      'advanced_settings' => [
          'id' => 'advanced_settings',
          'name' => 'advanced_settings_section',
          'display' => 'Advanced settings',
          'page' => 'wpan_advanced_settings_page',
          'group' => 'wpan_advanced_settings_option_group',
          'visible' => true,
          'db_key' => 'wpan:advanced_settings',
          'func_register' => 'wpan_register_advanced_settings_fields',
          'func_display' => 'wpan_display_advanced_settings_section',
          'fields' => [
              'vertical_booking_support' => '0',
              'enhanced_link_attribution' => '0',
              'debug' => '0',
          ],
      ],
      'hidden_settings' => [
          'id' => 'hidden_settings',
          'name' => 'hidden_settings_section',
          'display' => 'Hidden settings',
          'page' => 'wpan_hidden_settings_page',
          'group' => 'wpan_hidden_settings_option_group',
          'visible' => false,
          'db_key' => 'wpan:hidden_settings',
          'func_register' => 'wpan_register_hidden_settings_fields',
          'func_display' => 'wpan_display_hidden_settings_section',
          'fields' => [
              'enable_json_folder' => '0',
          ],
      ],
  ];

  /* What should we display as the title of a settings section? Set to %name% 
  to use the section's name */
  define ("wpan_section_title", '');



  // ==================================================================================
  // =                               Sanitize options                                 =
  // ==================================================================================

  /**
   * Sanitize the user's input.
   *
   * Check that the values filled by the user in the options form
   * make sense, making sure no harmful code is injected.
   *
   * The input is an associative array containing the key-value
   * pairs corresponding to the input forms filled by the user.
   * For fields left empty, $input[$key] is not set.
   *
   * Inspired by http://wordpress.stackexchange.com/a/100137/86662.
   */

  function wpan_sanitize_options ( $input ) {

    /* Initialise output array */
    $output = array ();

    /* Sanitize settings one by one */
    foreach ( $input as $key => $value ) {

      $error_code = '';

      if ( isset ( $input[ $key ] ) ) {
        
        /* If the user input something in the field, check if makes sense */
        if ( ! empty ( $input[ $key ] ) ) {

          switch ($key) {

            case 'tracking_uid':            
              if ( strlen ( trim ( $value ) ) < 13 ) {
                $error_code = 'tracking-uid-too-short';
                $error_message = 'The tracking ID should be of the form UA-XXXXXXX-Y';
                $error_type = 'error';
              }
              break;

            case 'group_index_wordpress':
              if ( $value < 0 ) {
                $error_code = 'negative-group-index-wordpress';
                $error_message = 'Wordpress group index must be positive';
                $error_type = 'error';
              }
              break;

            case 'group_index_woocommerce':
              if ( $value < 0 ) {
                $error_code = 'negative-group-index-woocommerce';
                $error_message = 'Woocommerce group index must be positive';
                $error_type = 'error';
              }
              break;

            case 'group_index_blog':
              if ( $value < 0 ) {
                $error_code = 'negative-group-index-blog';
                $error_message = 'Blog group index must be positive';
                $error_type = 'error';
              }
              break;

            case 'pixel_threshold':
              if ( $value < 0 ) {
                $error_code = 'negative-pixel-threshold';
                $error_message = 'The pixel threshold must be positive';
                $error_type = 'error';
              }
              break;

            case 'time_threshold':
              if ( $value < 0 ) {
                $error_code = 'negative-time-threshold';
                $error_message = 'The time threshold must be positive';
                $error_type = 'error';
              }
              break;

            case 'phone_exclude_regex_pattern':

            case 'phone_include_regex_pattern':
              if ( $value && preg_match("/$value/", null) === false ) {
                /* If the regex is not legit, find out what the error message is */
                $regex_error_msg = '';
                foreach ( get_defined_constants(true)['pcre'] as $k => $v ) {
                  if ( strstr ($k, "ERROR") && $v == preg_last_error() ) {
                    $regex_error_msg = $k;
                  }
                }
                $error_code = 'phone-regex-not-valid';
                $error_message = "Error in '$key'";
                /* It might happen that preg_last_error() returns an OK status code even if
                preg_match() has failed; see https://akrabat.com/preg_last_error-returns-no-
                error-on-preg_match-failure/ for why this happens */
                if ($regex_error_msg)
                  $error_message . ': ' . $regex_error_msg;
                $error_type = 'error';
              }
              elseif ( strlen ( $value ) > WPAN_MAX_REGEX_LENGTH ) {
                $error_code = 'phone-regex-too-long';
                $error_message = "Regex ($key) must be shorter than" . WPAN_MAX_REGEX_LENGTH . " characters for security reasons";
                $error_type = 'error';
              }
              break;

            case 'vertical_booking_support':
              /* Vertical booking support requires enhanced link attribution */
              $output['enhanced_link_attribution'] = true;
              $error_code = 'set-enhanced-link-attribution';
              $error_message = 'Enhanced link attribution was turned on to allow Vertical Booking support';
              $error_type = 'updated';
              break;

          } // switch

        } // if not empty


        /* Return an error message if the input didn't respect our standards. Note
        that if $error_type == 'updated', the message will be a (green) notice rather
        than a (red) error message.  */
        if ( ! empty ( $error_code ) ) {

          add_settings_error(
            $key,
            esc_attr ( $error_code ),
            $error_message . '.',
            $error_type);

        }
        
        /* If the error was not severe, store the input value in the output
        array */
        if ( empty ( $error_code ) || $error_type !== 'error' ) {

          $output[ $key ] = $value;

        }

        /* Strip all HTML and PHP tags and properly handle quoted strings.
        Thanks to Tom McFarlin: http://goo.gl/i0jL7t */
        $dont_strip = [
          'phone_regex_include_pattern',
          'phone_regex_exclude_pattern',
        ];
        if ( isset ( $output[$key] ) && ! in_array ( $key, $dont_strip ) )
          $output[$key] = strip_tags( stripslashes( $output[$key] ) );        
        
      } // if isset (input[$key])

    } // foreach

    return $output;
    
  } // wpan_sanitize_options
  
  

  require_once ( WPAN_PLUGIN_DIR . 'settings/settings.php' );
  
  



