<?php

namespace Drupal\layout_builder_tabs\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layout_builder_tabs\Plugin\Layout\TabsLayout;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Makes a tabs layout.
 */
class TabsLayoutDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;
  const NUMBER_OF_TABS = 12;
  const REGION_PREFIX = 'tab_region';

  /**
   * Constructs a new BootstrapLayoutDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \NumberFormatter $numberFormatter
   *   The number formatter.
   */
  public function __construct(
    public EntityTypeManagerInterface $entity_type_manager,
    public \NumberFormatter $numberFormatter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      new \NumberFormatter("en", \NumberFormatter::SPELLOUT)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $regions = $this->getRegions(self::NUMBER_OF_TABS);
    if ($regions) {
      foreach ($regions as $id => $layout) {
        $this->derivatives[$id] = new LayoutDefinition([
          'class' => TabsLayout::class,
          'label' => $layout['label'],
          'id' => $id,
          'number_of_tabs' => $layout['number_of_tabs'],
          'category' => 'Tabs',
          'regions' => $this->getRegions($layout['number_of_tabs']),
          'theme_hook' => 'layout_tabs_section',
          'icon_map' => $this->getIconMap($layout['number_of_tabs']),
          'provider' => 'd_layout_builder_tabs',
        ]);
      }
    }

    return $this->derivatives;
  }

  /**
   * Convert integer to number in letters.
   *
   * @param int $num
   *   The number that needed to be converted.
   *
   * @return string
   *   The number in letters.
   */
  private function formatNumberInLetters(int $num): string {
    return $this->numberFormatter->format($num);
  }

  /**
   * Get the formated array of row regions based on columns count.
   *
   * @param int $columns_count
   *   The count of row columns.
   *
   * @return array
   *   The row columns 'regions'.
   */
  private function getRegions(int $columns_count): array {
    $regions = [];

    for ($i = 1; $i <= $columns_count; $i++) {
      $key = self::REGION_PREFIX . '_' . $i;
      $regions[$key] = [
        'label' => $i . ' ' . $this->t('Tabs'),
        'number_of_tabs' => $i,
      ];
    }

    return $regions;
  }

  /**
   * Get the icon map array based on columns_count.
   *
   * @param int $columns_count
   *   The count of row columns.
   *
   * @return array
   *   The icon map array.
   */
  private function getIconMap(int $columns_count): array {
    $row = [];

    for ($i = 1; $i <= $columns_count; $i++) {
      $row[] = 'square_' . $this->formatNumberInLetters($i);
    }

    return [$row];
  }

}
