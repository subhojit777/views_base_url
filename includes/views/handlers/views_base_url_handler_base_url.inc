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
   * More base url options.
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['show_link'] = array('default' => FALSE);
    $options['show_link_options']['link_path'] = array('default' => '');
    $options['show_link_options']['link_text'] = array('default' => '');
    $options['show_link_options']['link_class'] = array('default' => '');
    $options['show_link_options']['link_title'] = array('default' => '');
    $options['show_link_options']['link_rel'] = array('default' => '');
    $options['show_link_options']['link_target'] = array('default' => '');

    return $options;
  }

  /**
   * More options form.
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['show_link'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display as link'),
      '#description' => t('Show base URL as link. You can create a custom link using this option.'),
      '#default_value' => $this->options['show_link'],
    );

    $form['show_link_options'] = array(
      '#type' => 'container',
      '#states' => array(
        'invisible' => array(
          ':input[type=checkbox][name="options[show_link]"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['show_link_options']['link_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Link path'),
      '#description' => t('Drupal path for this link. The base url will be prepended to this path. If nothing provided then base url will appear as link.'),
      '#default_value' => $this->options['show_link_options']['link_path'],
    );

    $form['show_link_options']['link_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#description' => t('Link text. If nothing provided then link path will appear as link text.'),
      '#default_value' => $this->options['show_link_options']['link_text'],
    );

    $form['show_link_options']['link_class'] = array(
      '#type' => 'textfield',
      '#title' => t('Link class'),
      '#description' => t('CSS class to be applied to this link.'),
      '#default_value' => $this->options['show_link_options']['link_class'],
    );

    $form['show_link_options']['link_title'] = array(
      '#type' => 'textfield',
      '#title' => t('Link title'),
      '#description' => t('Title attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_title'],
    );

    $form['show_link_options']['link_rel'] = array(
      '#type' => 'textfield',
      '#title' => t('Link rel'),
      '#description' => t('Rel attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_rel'],
    );

    $form['show_link_options']['link_target'] = array(
      '#type' => 'textfield',
      '#title' => t('Link target'),
      '#description' => t('Target attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_target'],
    );
  }

  /**
   * Render site's base url.
   */
  public function render($values) {
    global $base_url;
    $output = '';

    if ($this->options['show_link']) {
      $link_path = empty($this->options['show_link_options']['link_path']) ? $base_url : $base_url . '/' . $this->options['show_link_options']['link_path'];

      if (empty($this->options['show_link_options']['link_text'])) {
        if (empty($this->options['show_link_options']['link_path'])) {
          $link_text = $base_url;
        }
        else {
          $link_text = $base_url . '/' . $this->options['show_link_options']['link_path'];
        }
      }
      else {
        $link_text = $this->options['show_link_options']['link_text'];
      }

      $link_class = empty($this->options['show_link_options']['link_class']) ? array() : explode(' ', $this->options['show_link_options']['link_class']);

      $output = l($link_text, $link_path, array(
        'attributes' => array(
          'class' => $link_class,
          'title' => $this->options['show_link_options']['link_title'],
          'rel' => $this->options['show_link_options']['link_rel'],
          'target' => $this->options['show_link_options']['link_target'],
        ),
      ));
    }
    else {
      $output = $base_url;
    }

    return $output;
  }
}
