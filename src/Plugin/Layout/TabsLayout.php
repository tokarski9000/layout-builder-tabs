<?php

namespace Drupal\layout_builder_tabs\Plugin\Layout;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Random;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A layout from our tabs layout for layout builder.
 *
 * @Layout(
 *   id = "tabs_layout_builder",
 *   deriver = "Drupal\d_layout_builder_tabs\Plugin\Deriver\TabsLayoutDeriver"
 * )
 */
class TabsLayout extends LayoutDefault implements ContainerFactoryPluginInterface {

  const WRAPPER_PREFIX = 'layout-tabs';
  const REGION_PREFIX = 'layout-tab';

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager $styles_group_manager
   *   The styles group plugin manager.
   * @param \Drupal\Component\Utility\Random $random
   *   The random service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    public ConfigFactoryInterface $config_factory,
    public EntityTypeManagerInterface $entity_type_manager,
    public StylesGroupManager $styles_group_manager,
    public Random $random,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): TabsLayout|ContainerFactoryPluginInterface|static {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /** @var \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager $styles_group_manager */
    $styles_group_manager = $container->get('plugin.manager.bootstrap_styles_group');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $entity_type_manager,
      $styles_group_manager,
      new Random()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $default_configuration = parent::defaultConfiguration();

    $regions_classes = $regions_attributes = [];
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      $regions_classes[$region_name] = '';
      $regions_attributes[$region_name] = [];
    }

    return $default_configuration + [
      'container_wrapper_classes' => self::WRAPPER_PREFIX . '-container-wrapper',
      'container_wrapper_attributes' => [],
      'container_wrapper' => [],
      'container_wrapper_bg_color_class' => '',
      'container_wrapper_bg_media' => NULL,
      'container' => self::WRAPPER_PREFIX . '-container',
      'section_classes' => self::REGION_PREFIX . '-section',
      'section_attributes' => [],
      'regions_classes' => $regions_classes,
      'regions_attributes' => $regions_attributes,
      'breakpoints' => [],
      'layout_regions_classes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions): array {
    $build = parent::build($regions);

    // Row classes and attributes.
    if ($this->configuration['section_classes']) {
      $build['#attributes']['class'] = explode(' ', $this->configuration['section_classes']);
    }

    if (!empty($this->configuration['section_attributes'])) {
      $section_attributes = $this->configuration['section_attributes'];
      $build['#attributes'] = NestedArray::mergeDeep($build['#attributes'] ?? [], $section_attributes);
    }

    // Regions classes and attributes.
    if ($this->configuration['regions_classes']) {
      foreach ($this->getPluginDefinition()->getRegionNames() as $i => $region_name) {
        $build[$region_name]['#attributes']['class'][] = self::REGION_PREFIX . '-section--' . $i;
        $id = $this->random->name(8, TRUE);
        $build[$region_name]['#attributes']['id'] = self::REGION_PREFIX . '-' . $id;
        $build[$region_name]['icon'] = $this->buildMedia($this->configuration['tabs'][$i + 1]['icon']);
      }
    }

    if ($this->configuration['regions_attributes']) {
      foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
        $region_attributes = $this->configuration['regions_attributes'][$region_name];
        if (!empty($region_attributes)) {
          $build[$region_name]['#attributes'] = NestedArray::mergeDeep($build[$region_name]['#attributes'] ?? [], $region_attributes);
        }
      }
    }

    // Container.
    if ($this->configuration['container']) {
      $theme_wrappers = [
        'layout_tabs_container' => [
          '#attributes' => [
            'class' => [$this->configuration['container']],
          ],
        ],
        'layout_tabs_container_wrapper' => [
          '#attributes' => [
            'class' => [self::WRAPPER_PREFIX . '-container-wrapper'],
          ],
        ],
      ];

      $build['#theme_wrappers'] = $theme_wrappers;
      $build['#attached']['library'][] = 'd_layout_builder_tabs/layout_tabs_script';
    }

    return $build;
  }

  /**
   * Build icon render array.
   *
   * @param int|null $id
   *   The media id.
   *
   * @return array|null
   *   The render array of the icon.
   */
  protected function buildMedia(?int $id): ?array {
    if (!$id) {
      return NULL;
    }
    $media = $this->entity_type_manager->getStorage('media')->load($id);

    return $this->entity_type_manager->getViewBuilder('media')->view($media, 'svg_inline');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $number_of_tabs = $form_state
      ->getBuildInfo()['callback_object']
      ->getCurrentLayout()
      ->getPluginDefinition()
      ->get('number_of_tabs');

    $form['use_container'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Container for tab nav'),
      '#default_value' => $this->configuration['use_container'] ?? FALSE,
    ];

    $form['overlap_previous_section'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Overlap previous section'),
      '#default_value' => $this->configuration['overlap_previous_section'] ?? FALSE,
    ];

    $color_fields = [
      'inactive_font_color' => $this->t('Inactive font color'),
      'inactive_background_color' => $this->t('Inactive background color'),
      'active_font_color' => $this->t('Active font color'),
      'active_background_color' => $this->t('Active background color'),
    ];

    for ($i = 1; $i <= $number_of_tabs; $i++) {
      $form['tabs'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('Tab @i', ['@i' => $i]),
        '#open' => $i == 1 ? TRUE : FALSE,
      ];

      $form['tabs'][$i]['tab_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Tab Title'),
        '#default_value' => $this->configuration['tabs'][$i]['tab_title'] ?? '',
      ];

      foreach ($color_fields as $field_id => $title) {
        $form['tabs'][$i][$field_id] = [
          '#type' => 'color',
          '#title' => $title,
          '#default_value' => $this->configuration['tabs'][$i][$field_id] ?? '',
        ];
      }

      $form['tabs'][$i]['icon'] = [
        '#type' => 'media_library',
        '#allowed_bundles' => ['svg'],
        '#title' => $this->t('Icon'),
        '#default_value' => $this->configuration['tabs'][$i]['icon'] ?? '',
      ];

      $form['tabs'][$i]['use_container'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use Container for tab @i', ['@i' => $i]),
        '#default_value' => $this->configuration['tabs'][$i]['use_container'] ?? FALSE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->cleanValues()->getValues();
    foreach ($values as $name => $value) {
      $this->configuration[$name] = $value;
    }
  }

}
