<?php

namespace Drupal\dashboard\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to upload screenshots.
 *
 * @RestResource (
 *   id = "screenshot_upload",
 *   label = @Translation("Screenshot Upload"),
 *   uri_paths = {
 *     "create" = "/screenshot/upload"
 *   }
 * )
 */
class ScreenshotUpload extends ResourceBase {
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
    $filename = $payload->get('filename');
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    // Check extension.
    if ($extension != 'png') {
      return new ModifiedResourceResponse(false, 404);
    }

    // Check file mime type.
    $image = $payload->get('image');
    $data = base64_decode($image);
    $f = finfo_open();
    $mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
    if ($mime_type != 'image/png') {
      return new ModifiedResourceResponse(false, 404);
    }

    // Save image.
    $destination = "public://$filename";
    file_put_contents($destination, $data);

    // Always return true.
    return new ModifiedResourceResponse(true, 200);
  }
}
