<?php

/**
 * @file
 * Contains \Drupal\views_base_url\Tests\ViewsBaseUrlTest.
 */

namespace Drupal\views_base_url\Tests;

use Drupal\Core\Entity\EntityInterface;
use Drupal\simpletest\WebTestBase;

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

  /*public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
      'create article content',
    ));
  }*/

  /**
   * Test views base url field.
   */
  function testViewsBaseUrlField() {
    /*global $base_url;

    // Create 10 nodes.
    $this->drupalLogin($this->adminUser);
    $this->nodes = array();
    for ($i = 1; $i <= 10; $i++) {
      // Create node.
      $title = $this->randomName();
      $image = current($this->drupalGetTestFiles('image'));
      $edit = array(
        'title' => $title,
        'files[field_image_und_0]' => drupal_realpath($image->uri),
      );
      $this->drupalPost('node/add/article', $edit, t('Save'));
      $this->nodes[$i] = $this->drupalGetNodeByTitle($title);

      // Create path alias.
      $path = array(
        'source' => 'node/' . $this->nodes[$i]->nid,
        'alias' => "content/$title",
      );
      //path_save($path);
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
    $image = theme('image', array(
      'path' => $node->field_image[LANGUAGE_NONE][0]['uri'],
      'width' => $node->field_image[LANGUAGE_NONE][0]['width'],
      'height' => $node->field_image[LANGUAGE_NONE][0]['height'],
      'alt' => $node->field_image[LANGUAGE_NONE][0]['alt'],
    ));
    $link = l($image, $base_url . '/' . drupal_get_path_alias('node/' . $node->nid), array(
      'attributes' => array(
        'class' => 'views-base-url-test',
        'title' => $node->title,
        'rel' => 'rel-attribute',
        'target' => '_blank',
      ),
      'fragment' => 'new',
      'query' => array(
        'destination' => 'node',
      ),
      'html' => TRUE,
    ));
    $this->assertRaw($link, t('Views base url rendered as link image'));*/
  }

}
