<?php

/**
 * @file
 * Contains \Drupal\views_base_url\Tests\ViewsBaseUrlTest.
 */

namespace Drupal\views_base_url\Tests;

use Drupal\Core\Entity\EntityInterface;
use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Random;
use Drupal\Core\Url;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Basic test for views base url.
 *
 * @group views_base_url
 */
class ViewsBaseUrlFieldTest extends WebTestBase {

  /**
   * A user with various administrative privileges.
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  static public $modules = array(
    'views_base_url_test',
  );

  /**
   * The installation profile to use with this test.
   *
   * This test class requires the "tags" taxonomy field.
   *
   * @var string
   */
  protected $profile = 'standard';

  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
      'create article content',
    ));
  }

  /**
   * Test views base url field.
   */
  function testViewsBaseUrlField() {
    global $base_url;
    $random = new Random();
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');

    // Create 10 nodes.
    $this->drupalLogin($this->adminUser);
    $this->nodes = array();
    for ($i = 1; $i <= 10; $i++) {
      // Create node.
      $title = $random->name();
      $image = current($this->drupalGetTestFiles('image'));
      $edit = array(
        'title[0][value]' => $title,
        'files[field_image_0]' => drupal_realpath($image->uri),
      );
      $this->drupalPostForm('node/add/article', $edit, t('Save'));
      $this->drupalPostForm(NULL, array('field_image[0][alt]' => $title), t('Save'));
      $this->nodes[$i] = $this->drupalGetNodeByTitle($title);

      // Create path alias.
      $path = array(
        'source' => 'node/' . $this->nodes[$i]->id(),
        'alias' => "content/$title",
      );
      \Drupal::service('path.alias_storage')->save('/node/' . $this->nodes[$i]->id(), "/content/$title");
    }
    $this->drupalLogout();

    $this->drupalGet('views-base-url-test');
    $this->assertResponse(200);

    // Check whether there are ten rows.
    $rows = $this->xpath('//div[contains(@class,"view-views-base-url-test")]/div[@class="view-content"]/div[contains(@class,"views-row")]');
    $this->assertEqual(count($rows), 10, t('There are 10 rows'));

    // We check for at least one views result that link is properly rendered as
    // image.
    $node = $this->nodes[1];
    $field = $node->get('field_image');
    $file = $field->entity;
    $value = $field->getValue();
    $image = array(
      '#theme' => 'image',
      '#uri' => $file->getFileUri(),
      '#alt' => $value[0]['alt'],
      '#attributes' => array(
        'width' => $value[0]['width'],
        'height' => $value[0]['height'],
      ),
    );
    $url = Url::fromUri($base_url . '/' . \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node->id()), array(
      'attributes' => array(
        'class' => 'views-base-url-test',
        'title' => $node->getTitle(),
        'rel' => 'rel-attribute',
        'target' => '_blank',
      ),
      'fragment' => 'new',
      'query' => array(
        'destination' => 'node',
      ),
    ));
    $link = \Drupal::l(SafeMarkup::format(str_replace("\n", NULL, $renderer->renderRoot($image))), $url);
    $this->verbose($link);
    $this->assertRaw($link, t('Views base url rendered as link image'));
  }

}
