<?php

/**
 * Plugin Name: List of AT services
 * Description:       Creates a list of AT sservices
 * Author:            Ace Centre, Gavin Henderson
 * Author URI:        https://gavinhenderson.me
 * Version:           1
 */

function add_custom_post_type() {
    register_post_type('at_services',
        array(
            'labels'      => array(
                'name'          => 'AT Services',
                'singular_name' => 'AT Service',
            ),
                'public'      => true,
                'has_archive' => true,
                'description' => 'A list of assitive technology services',
                'rewrite'     => array( 'slug' => 'at-services' ),
        )
    );
    // var_dump($services);
}

function updateServices() {
    $response = wp_remote_post('https://servicefinder.acecentre.net/graphql', [
        'method' => 'POST',
        'headers' => [
            "content-type" => 'application/json'
        ],
        'body' => json_encode([
            'operationName' => "GetAllServices",
            'query' => "query GetAllServices {
                servicesFilter(filters: {
                  serviceTypes: [AAC, EC],
                  countries: [England]
                }) {
                  id
                  serviceName
                  phoneNumber
                  website
                  addressLines
                  ccgCodes
                  email
                  caseload
                  postcode
                  provider
                  note
                  serviceColor
                  communicationMatters
                  servicesOffered {
                    id
                    description
                    title
                  }
                  coordinates {
                    longitude
                    latitude
                  }
                  additionalContactEmail
                  additionalContactName
                  scale
                  servicesProvidedDescription
                  populationServed
                  areaCoveredText
                  staffing
                  fundedBy
                  referralProcess
                  permanentOrProject
                  yearSetUp
                  country
                }
              }
              ",
        ]),
    ]);


    $services = json_decode($response['body'])->{'data'}->{'servicesFilter'};

    // Delete all services
    $allposts = get_posts( array(
        'post_type' => 'at_services',
        'numberposts' => -1,
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash') 
    ));
    foreach ($allposts as $eachpost) {
      wp_delete_post( $eachpost->ID, true );
    }

    foreach($services as $service) {
        $content = "";

        if(isset( $service->{'addressLines'} )) {
            $content .= "<p>";
            foreach($service->{'addressLines'} as $addressLine) {
                $content .= $addressLine . "<br />";
            }
            $content .= "</p>";
        }

        if(isset( $service->{'additionalContactName'} )) {
            $content .= "<p><strong>Contact Name: </strong>" . $service->{'additionalContactName'} . "</p>";
        }

        if(isset( $service->{''} )) {
            $content .= "<p><strong>Tel: </strong>" . $service->{'phoneNumber'} . "</p>";
        }

        if(isset( $service->{'website'} )) {
            $content .= "<p><strong>Website: </strong>" . $service->{'website'} . "</p>";
        }

        if(isset( $service->{'email'} )) {
            $content .= "<p><strong>Email: </strong>" . $service->{'email'} . "</p>";
        }

        if(isset( $service->{'additionalContactEmail'} )) {
            $content .= "<p><strong>Additional Email: </strong>" . $service->{'additionalContactEmail'} . "</p>";
        }

        if(isset( $service->{'servicesOffered'} )) {
            $serviceTitles = array();

            foreach($service->{'servicesOffered'} as $serviceOffered) {
                array_push($serviceTitles, $serviceOffered->{'title'});
            }

            $content .= "<p><strong>Service Types: </strong>" . join(", ", $serviceTitles) . "</p>";
        }

        if(isset( $service->{'areaCoveredText'} )) {
            $content .= "<div><h2>Area Covered</h2><p>" . $service->{'areaCoveredText'} . "</p></div>";
        }

        if(isset( $service->{'populationServed'} )) {
            $content .= "<div><h2>Population Served</h2><p>" . $service->{'populationServed'} . "</p></div>";
        }

        if(isset( $service->{'scale'} )) {
            $content .= "<div><h2>Scale</h2><p>" . $service->{'scale'} . "</p></div>";
        }

        if(isset( $service->{'servicesProvidedDescription'} )) {
            $content .= "<div><h2>Services Provided</h2><p>" . $service->{'servicesProvidedDescription'} . "</p></div>";
        }

        if(isset( $service->{'staffing'} )) {
            $content .= "<div><h2>Staffing</h2><p>" . $service->{'staffing'} . "</p></div>";
        }

        if(isset( $service->{'fundedBy'} )) {
            $content .= "<div><h2>Funding</h2><p>" . $service->{'fundedBy'} . "</p></div>";
        }

        if(isset( $service->{'permanentOrProject'} )) {
            $content .= "<div><h2>Permanent or Project</h2><p>" . $service->{'permanentOrProject'} . "</p></div>";
        }

        if(isset( $service->{'yearSetUp'} )) {
            $content .= "<div><h2>Year Founded</h2><p>" . $service->{'yearSetUp'} . "</p></div>";
        }

        if(isset( $service->{'referralProcess'} )) {
            $content .= "<div><h2>Referral Process</h2><p>" . $service->{'referralProcess'} . "</p></div>";
        }

        $content .= "<div><i>If any of this data incorrect let us know by <a href=\"https://forms.office.com/Pages/ResponsePage.aspx?id=bFwgTJtTgU-Raj-O_eaPrEio9bWrHCRGoqxiKrWB3RlUMFdOTVVJNkM0SktaOUdDSUU1WkMwMTZRUiQlQCN0PWcu\">filling out this form</a></i></div>";

        wp_insert_post([
            'post_title' => $service->{'serviceName'},
            'post_type' => 'at_services',
            'post_status' => 'publish',
            'post_content' => $content 
        ]);
    }

    return $services;
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

function listAacServices( ) {
    $allposts = get_posts( array(
        'post_type' => 'at_services',
        'numberposts' => -1,
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash') 
    ));

    $aacServices = array_filter($allposts, function($service) {
        return str_contains($service->post_content, 'AAC services');
    });

    $content = "<ul>";

    foreach($aacServices as $post) {
        $content .= "<li><a href=\"". get_permalink($post) ."\">" . $post->post_title . "</a></li>";
    }

    $content .= "<ul>";

    return $content;
}

add_shortcode( 'list_aac_services', 'listAacServices' );

function listEcServices( ) {
    $allposts = get_posts( array(
        'post_type' => 'at_services',
        'numberposts' => -1,
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash') 
    ));

    $aacServices = array_filter($allposts, function($service) {
        return str_contains($service->post_content, 'EC services');
    });

    $content = "<ul>";

    foreach($aacServices as $post) {
        $content .= "<li><a href=\"". get_permalink($post) ."\">" . $post->post_title . "</a></li>";
    }

    $content .= "<ul>";

    return $content;
}

add_shortcode( 'list_ec_services', 'listEcServices' );

add_action( 'rest_api_init', function () {
    register_rest_route( 'at-services-list', '/update-services', array(
      'methods' => 'GET',
      'callback' => 'updateServices',
    ));
});

add_action('init', 'add_custom_post_type');

