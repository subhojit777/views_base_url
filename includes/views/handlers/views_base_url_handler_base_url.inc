<?php

/**
 * @file
 * A handler to output site's base url.
 *
 * @ingroup views_field_handlers
 */

class ViewsBaseUrlHandlerBaseUrl extends views_handler_field {
  /**
   * Add to query.
   */
  public function query() {
    // Do nothing.
  }

  /**
   * Render site's base url.
   */
  public function render($values) {
    global $base_url;

    return $base_url;
  }
}
