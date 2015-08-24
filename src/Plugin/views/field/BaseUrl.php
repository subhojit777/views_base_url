<?php

/**
 * @file
 * Definition of Drupal\views_base_url\Plugin\views\field\BaseUrl.
 */

namespace Drupal\views_base_url\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;

/**
 * A handler to output site's base url.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("base_url")
 */
class BaseUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['show_link'] = array('default' => FALSE);
    $options['show_link_options']['contains'] = array(
      'link_path' => array('default' => ''),
      'link_text' => array('default' => ''),
      'link_class' => array('default' => ''),
      'link_title' => array('default' => ''),
      'link_rel' => array('default' => ''),
      'link_fragment' => array('default' => ''),
      'link_query' => array('default' => ''),
      'link_target' => array('default' => ''),
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['show_link'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Display as link'),
      '#description' => $this->t('Show base URL as link. You can create a custom link using this option.'),
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
      '#title' => $this->t('Link path'),
      '#description' => $this->t('Drupal path for this link. The base url will be prepended to this path. If nothing provided then base url will appear as link.'),
      '#default_value' => $this->options['show_link_options']['link_path'],
    );

    $form['show_link_options']['link_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#description' => $this->t('Link text. If nothing provided then link path will appear as link text.'),
      '#default_value' => $this->options['show_link_options']['link_text'],
    );

    $form['show_link_options']['link_class'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link class'),
      '#description' => $this->t('CSS class to be applied to this link.'),
      '#default_value' => $this->options['show_link_options']['link_class'],
    );

    $form['show_link_options']['link_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('Title attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_title'],
    );

    $form['show_link_options']['link_rel'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link rel'),
      '#description' => $this->t('Rel attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_rel'],
    );

    $form['show_link_options']['link_fragment'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Fragment'),
      '#description' => $this->t('Provide the ID with which you want to create fragment link.'),
      '#default_value' => $this->options['show_link_options']['link_fragment'],
    );

    $form['show_link_options']['link_query'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link query'),
      '#description' => $this->t('Attach queries to the link. If there are multiple queries separate them using a space. For eg: %example1 OR %example2', array('%example1' => 'destination=node/add/page', '%example2' => 'destination=node/add/page q=some/page')),
      '#default_value' => $this->options['show_link_options']['link_query'],
    );

    $form['show_link_options']['link_target'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link target'),
      '#description' => $this->t('Target attribute for this link.'),
      '#default_value' => $this->options['show_link_options']['link_target'],
    );

    // Get a list of the available fields and arguments for token replacement.

    // Setup the tokens for fields.
    $previous = $this->getPreviousFieldLabels();
    foreach ($previous as $id => $label) {
      $options[t('Fields')]["{{ $id }}"] = substr(strrchr($label, ":"), 2 );
    }
    // Add the field to the list of options.
    $options[t('Fields')]["{{ {$this->options['id']} }}"] = substr(strrchr($this->adminLabel(), ":"), 2 );

    $count = 0; // This lets us prepare the key as we want it printed.
    foreach ($this->view->display_handler->getHandlers('argument') as $arg => $handler) {
      $options[t('Arguments')]['%' . ++$count] = $this->t('@argument title', array('@argument' => $handler->adminLabel()));
      $options[t('Arguments')]['!' . $count] = $this->t('@argument input', array('@argument' => $handler->adminLabel()));
    }

    $this->documentSelfTokens($options[t('Fields')]);

    // Default text.

    $output = [];
    $output[] = [
      '#markup' => '<p>' . $this->t('You must add some additional fields to this display before using this field. These fields may be marked as <em>Exclude from display</em> if you prefer. Note that due to rendering order, you cannot use fields that come after this field; if you need a field not listed here, rearrange your fields.') . '</p>',
    ];
    // We have some options, so make a list.
    if (!empty($options)) {
      $output[] = [
        '#markup' => '<p>' . $this->t("The following replacement tokens are available for this field. Note that due to rendering order, you cannot use fields that come after this field; if you need a field not listed here, rearrange your fields.") . '</p>',
      ];
      foreach (array_keys($options) as $type) {
        if (!empty($options[$type])) {
          $items = array();
          foreach ($options[$type] as $key => $value) {
            $items[] = $key . ' == ' . $value;
          }
          $item_list = array(
            '#theme' => 'item_list',
            '#items' => $items,
            '#list_type' => $type,
          );
          $output[] = $item_list;
        }
      }
    }
    // This construct uses 'hidden' and not markup because process doesn't
    // run. It also has an extra div because the dependency wants to hide
    // the parent in situations like this, so we need a second div to
    // make this work.
    $form['show_link_options']['help'] = array(
      '#type' => 'details',
      '#title' => $this->t('Replacement patterns'),
      '#value' => $output,
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    global $base_url;
    global $language;
    $output = '';
    $link_query = array();
    $tokens = $this->getRenderTokens($output);

    if ($this->options['show_link']) {
      if (!empty($this->options['show_link_options']['link_path'])) {
        $aliased_path = $this->viewsTokenReplace($this->options['show_link_options']['link_path'], $tokens);
        $aliased_path = \Drupal::service('path.alias_manager')->getAliasByPath("/$aliased_path");
      }

      // Link path.
      $link_path = empty($aliased_path) ? $base_url : $base_url . '/' . $aliased_path;

      // Link text.
      if (empty($this->options['show_link_options']['link_text'])) {
        if (empty($aliased_path)) {
          $link_text = SafeMarkup::checkPlain($base_url);
        }
        else {
          $link_text = SafeMarkup::checkPlain($base_url . '/' . $aliased_path);
        }
      }
      else {
        $link_text = SafeMarkup::checkPlain($this->options['show_link_options']['link_text']);
      }

      // Link class.
      $link_class = empty($this->options['show_link_options']['link_class']) ? array() : explode(' ', $this->options['show_link_options']['link_class']);

      // Link query.
      if (!empty($this->options['show_link_options']['link_query'])) {
        $queries = explode(' ', $this->options['show_link_options']['link_query']);

        foreach ($queries as $query) {
          $param = explode('=', $query);
          $link_query[$param[0]] = $param[1];
        }
      }

      // Create link with options.
      $url = Url::fromUri($link_path, array(
        'attributes' => array(
          'class' => $link_class,
          'title' => $this->options['show_link_options']['link_title'],
          'rel' => $this->options['show_link_options']['link_rel'],
          'target' => $this->options['show_link_options']['link_target'],
        ),
        'fragment' => $this->options['show_link_options']['link_fragment'],
        'query' => $link_query,
        'language' => $language,
      ));
      $output = \Drupal::l($link_text, $url);
    }
    else {
      $output = $base_url;
    }

    // Replace token with values and return it as output.
    return $this->viewsTokenReplace($output, $tokens);
  }

}
