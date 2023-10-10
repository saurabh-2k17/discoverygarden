<?php

namespace Drupal\uel_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates Secondary Navigation Block.
 *
 * @Block(
 *  id = "secondary_navigation",
 *  admin_label = @Translation("Secondary Navigation"),
 * )
 */
class SecondaryNavigation extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The menu tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs SecondaryNavigation.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_tree, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get block content.
    $block_content = $this->getSiblingMenuItems();

    if (empty($block_content)) {
      return;
    }

    // Generate the markup for the secondary navigation block.
    return [
      '#theme' => 'uel_core_secondary_navigation_block',
      '#data' => $block_content,
      '#cache' => [
        'contexts' => ['url'],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getSiblingMenuItems() {

    // Set menu name, we will be using main menu.
    $menu_name = 'main';
    $menu_tree = $this->menuTree;

    // Get the active trail in reverse order.
    // Our current active link always will be the first array element.
    $parameters   = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $active_trail = array_keys($parameters->activeTrail);

    // But actually we need its parent, except for <front> Which has no parent.
    $parent_link_id = isset($active_trail[1]) ? $active_trail[1] : $active_trail[0];

    // Validate if current menu trail does not have any active menu.
    if ((empty($active_trail[1])) && (empty($active_trail[0]))) {
      return;
    }

    // With parent now we set it as starting point to build our custom tree.
    $parameters->setRoot($parent_link_id);
    $parameters->setMaxDepth(1);
    $tree = $menu_tree->load($menu_name, $parameters);

    // Native sort and access checks.
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    // Build a renderable array.
    $menu = $menu_tree->build($tree);
    return $this->renderer->render($menu);

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['config:system.menu.main']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
