<?php

namespace Drupal\dashboard\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to upload Behat results.
 *
 * @RestResource (
 *   id = "behat_results_upload",
 *   label = @Translation("Behat Results Upload"),
 *   uri_paths = {
 *     "create" = "/behat/results/upload"
 *   }
 * )
 */
class ResultsUpload extends ResourceBase {

  /**
   * Creates a file from an endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   A 201 response, on success.
   */
  public function post(Request $request) {
    $payload = $request->getPayload();
//    $event = $payload->get('event');
    $brand = $payload->get('brand');
    $market = $payload->get('market');
//    $features = $payload->all('scenarios');
//    $device = $payload->get('device');
    $brand_market = "$brand-$market";
    $payload->add(['lastRun' => date('c')]);

    // Save payload.
    $destination = "public://$brand_market.json";
    file_put_contents($destination, json_encode($payload->all()));

    // Always return true.
    return new ModifiedResourceResponse($destination, 200);
  }
}
