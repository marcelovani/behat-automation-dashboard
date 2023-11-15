<?php

namespace Drupal\dashboard\Plugin\rest\resource;

use Drupal\Core\Render\HtmlResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * REST endpoint for dynamic js.
 *
 * @RestResource(
 *   id = "dynamic_js",
 *   label = @Translation("Dashboard dynamic js"),
 *   uri_paths = {
 *     "create" = "/dynamicjs"
 *   }
 * )
 */
class DynamicJs extends ResourceBase {

  /**
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param Symfony\Component\HttpFoundation\Request $current_request
   *   The current request
   */
  public function __construct(array                   $configuration,
                                                      $plugin_id,
                                                      $plugin_definition,
                              array                   $serializer_formats,
                              LoggerInterface         $logger,
                              Request                 $current_request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_rest'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Creates a js file from an endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Drupal\Core\Render\HtmlResponse
   *   A 200 response, on success.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Thrown when temporary files cannot be written, a lock cannot be acquired,
   *   or when temporary files cannot be moved to their new location.
   */
  public function post(Request $request) {
    $payload = $request->getPayload();
    $script = $this->generateScript($payload);

    $response = new HtmlResponse();
    $response->setContent([
      '#markup' => $script,
      '#cache' => [
        'contexts' => ['url.query_args:value'],
      ],
    ]);

    return $response;
  }

  /**
   * Dynamically generates the js script.
   *
   * @param string $payload
   *   The payload used to get the data.
   *
   * @return string
   *   The generated script.
   */
  protected function generateScript($payload) {
    /** @var \Symfony\Component\HttpFoundation\InputBag $payload */
    $event = $payload->get('event');
    $brand = $payload->get('brand');
    $market = $payload->get('market');
    $features = $payload->all('scenarios');
    $brand_market = "$brand-$market";

    switch ($event) {
      case 'suite_started':
        $icon = 'wip';
        break;

      case 'suite_finished':
        $outcome = $payload->get('outcome');
        if ($outcome == 'passed') {
          $icon = 'green';
        }
        else {
          $icon = 'red';
        }
        break;

      case 'scenario_failed':
        $icon = 'red';
        break;

      default:
        throw new \Exception("Unknown event $event");
    }

    $img = "/modules/custom/dashboard/images/$icon.png";

    // Prepare script.
    $destination = "public://$brand_market.js";

    // Add event.
    $script = "// Event: $event';\n";

    // One hour;
    $threshold = 60 * 60 * 1000;

    // Store date of last run and calculate if threshold has passed.
    // @todo move all of this to functions in the main js.
    $last_run_iso = date('c');
    $script .= "var lastRunJob = new Date('$last_run_iso');\n";
    $script .= "var now = new Date();\n";
    $script .= "if (lastRunJob.getTime() + $threshold > now.getTime()) {;\n";
    $script .= "lastRun['$brand_market'] = lastRunJob;\n";

    // Add to sites list.
    $script .= "sites['$brand_market'] = 1;\n";

    // Update icon.
    $locator = "#matrix .site.$brand_market";
    $script .= "jQuery('$locator img').attr('title', 'Last run: ' + lastRunJob).attr('src', '$img');\n";

    // Add failed count.
    if (!empty($features)) {
      $count = sizeof($features);
      $failed_scenarios = json_encode(array_keys($features));

      // Add failed scenarios to the global variable.
      $script .= "failedScenarios['$brand_market'] = $failed_scenarios;\n";

      // Add a link with count.
      $script .= "var link = jQuery('<a>').attr('href', '#failures-$brand_market').attr('title', 'Last run:' + lastRunJob).text('$count');\n";
      $script .= "console.log(link);\n";
      $script .= "var failed = jQuery('<div>').addClass('failed');\n";
      $script .= "jQuery(failed).appendTo(jQuery('$locator'));\n";
      $script .= "jQuery(link).appendTo(jQuery('$locator .failed'));\n";

      // Change to align to left.
      $script .= "jQuery('$locator img').css('float', 'left');\n";

      // Add anchor.
      $script .= "var anchor = jQuery('<a>').attr('name', 'failures-$brand_market');\n";
      $script .= "jQuery(anchor).appendTo(jQuery('#failures'));\n";

      // Append failed scenarios.
      foreach ($features as $feature_name => $scenarios) {
        // Append to failures list.
        //@todo create a helper function for appending.
        $script .= "var failure = jQuery('<div>').addClass('failure').addClass('$brand_market').text('$feature_name ($brand_market)');\n";
        $script .= "jQuery(failure).appendTo(jQuery('#failures'));\n";
        $script .= "var dateTime = jQuery('<div>').addClass('failure').addClass('$brand_market').text(lastRunJob);\n";
        $script .= "jQuery(dateTime).appendTo(jQuery('#failures .failure.$brand_market'));\n";

        // Loop scenarios.
        foreach ($scenarios as $scenario_name => $scenario) {
          // Append scenario.
          $line = $scenario['line'];
          $class = $this->clean_class($scenario_name . $line);
          $script .= "var scenario = jQuery('<div>').addClass('scenario').addClass('$class').html('<strong>$scenario_name</strong>');\n";
          $script .= "jQuery(scenario).appendTo(jQuery('.failure.$brand_market'));\n";

          // Append description.
          $description = str_replace("\n", '<br/>', $scenario['description']);
          $script .= "var description = jQuery('<blockquote>').addClass('description').html('$description');\n";
          $script .= "jQuery(description).appendTo(jQuery('.failure.$brand_market .scenario.$class'));\n";

          $steps = $scenario['steps'];
          if (!empty($steps)) {
            // Append steps.
            $script .= "var stepDiv = jQuery('<div>').addClass('steps').text('Steps:');\n";
            $script .= "jQuery(stepDiv).appendTo(jQuery('.failure.$brand_market .scenario.$class'));\n";
            foreach ($steps as $item) {
              $script .= "var step = jQuery('<div>').addClass('step').text('$item');\n";
              $script .= "jQuery(step).appendTo(jQuery('.failure.$brand_market .scenario.$class .steps'));\n";
            }
          }

          $error_message = $scenario['error_message'];
          $script .= "var errorMsg = jQuery('<div>').addClass('error_message').text('$error_message');\n";
          $script .= "jQuery(errorMsg).appendTo(jQuery('.failure.$brand_market .scenario.$class'));\n";

          // Add horizontal bar
          $script .= "var bar = jQuery('<hr>');\n";
          $script .= "jQuery(bar).appendTo(jQuery('.failure.$brand_market .scenario.$class'));\n";


          $feature_file = $scenario['feature_file'];
          $screenshots = $scenario['screenshots'];
        }
      }
    }

    // Closing if condition.
    $script .= '}';

    file_put_contents($destination, $script);

    return $script;
  }

  /**
   * Convert any random string into a classname following conventions.
   *
   * - preserve valid characters, numbers and unicode alphabet
   * - preserve already-formatted BEM-style classnames
   * - convert to lowercase
   *
   * @see http://getbem.com/
   */
  private function clean_class($identifier) {

    // Convert or strip certain special characters, by convention.
    $filter = [
      ' ' => '-',
      '__' => '__', // preserve BEM-style double-underscores
      '_' => '-', // otherwise, convert single underscore to dash
      '/' => '-',
      '[' => '-',
      ']' => '',
    ];
    $identifier = strtr($identifier, $filter);

    // Valid characters in a CSS identifier are:
    // - the hyphen (U+002D)
    // - a-z (U+0030 - U+0039)
    // - A-Z (U+0041 - U+005A)
    // - the underscore (U+005F)
    // - 0-9 (U+0061 - U+007A)
    // - ISO 10646 characters U+00A1 and higher
    // We strip out any character not in the above list.
    $identifier = preg_replace('/[^\\x{002D}\\x{0030}-\\x{0039}\\x{0041}-\\x{005A}\\x{005F}\\x{0061}-\\x{007A}\\x{00A1}-\\x{FFFF}]/u', '', $identifier);

    // Convert everything to lower case.
    return strtolower($identifier);
  }

}
