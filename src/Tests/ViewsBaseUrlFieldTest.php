<?php

namespace Drupal\views_base_url\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Random;
use Drupal\Core\Url;

/**
 * Basic test for views base url.
 *
 * @group views_base_url
 */
class ViewsBaseUrlFieldTest extends WebTestBase {

  /**
   * A user with various administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The installation profile to use with this test.
   *
   * This test class requires the "tags" taxonomy field.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * Node count.
   *
   * Number of nodes to be created in the tests.
   *
   * @var int
   */
  protected $nodeCount = 10;

  /**
   * Nodes.
   *
   * The nodes that is going to be created in the tests.
   *
   * @var array
   */
  protected $nodes;

  /**
   * Path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'views_base_url_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'create article content',
    ]);
    $random = new Random();

    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = $this->container->get('path.alias_storage');
    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $this->pathAliasManager = $this->container->get('path.alias_manager');

    // Create $this->nodeCount nodes.
    $this->drupalLogin($this->adminUser);
    for ($i = 1; $i <= $this->nodeCount; $i++) {
      // Create node.
      $title = $random->name();
      $image = current($this->drupalGetTestFiles('image'));
      $edit = [
        'title[0][value]' => $title,
        'files[field_image_0]' => drupal_realpath($image->uri),
      ];
      $this->drupalPostForm('node/add/article', $edit, t('Save'));
      $this->drupalPostForm(NULL, ['field_image[0][alt]' => $title], t('Save'));

      $this->nodes[$i] = $this->drupalGetNodeByTitle($title);
      $path_alias_storage->save('/node/' . $this->nodes[$i]->id(), "/content/$title");
    }
    $this->drupalLogout();
  }

  /**
   * Test views base url field.
   */
  public function testViewsBaseUrlField() {
    global $base_url;

    $this->drupalGet('views-base-url-image-test');
    $this->assertResponse(200);

    // Check whether there are ten rows.
    $rows = $this->xpath('//div[contains(@class,"view-views-base-url-image-test")]/div[@class="view-content"]/div[contains(@class,"views-row")]');
    $this->assertEqual(count($rows), $this->nodeCount, t('There are @count rows', [
      '@count' => $this->nodeCount,
    ]));

    // We check for at least one views result whose link is properly rendered as
    // image.
    $node = $this->nodes[1];
    $field = $node->get('field_image');
    $value = $field->getValue();

    $image_uri = file_url_transform_relative(file_create_url($field->entity->getFileUri()));
    $image_alt = $value[0]['alt'];
    $image_width = $value[0]['width'];
    $image_height = $value[0]['height'];

    $link_class = 'views-base-url-test';
    $link_title = $node->getTitle();
    $link_rel = 'rel-attribute';
    $link_target = '_blank';
    $link_path = Url::fromUri($base_url . $this->pathAliasManager->getAliasByPath('/node/' . $node->id()), [
      'attributes' => [
        'class' => $link_class,
        'title' => $link_title,
        'rel' => $link_rel,
        'target' => $link_target,
      ],
      'fragment' => 'new',
      'query' => [
        'destination' => 'node',
      ],
    ])->toUriString();

    $elements = $this->xpath('//a[@href=:path and @class=:class and @title=:title and @rel=:rel and @target=:target]/img[@src=:url and @width=:width and @height=:height and @alt=:alt]', [
      ':path' => $link_path,
      ':class' => $link_class,
      ':title' => $link_title,
      ':rel' => $link_rel,
      ':target' => $link_target,
      ':url' => $image_uri,
      ':width' => $image_width,
      ':height' => $image_height,
      ':alt' => $image_alt,
    ]);
    $this->assertEqual(count($elements), 1, 'Views base url rendered as link image');
  }

}
