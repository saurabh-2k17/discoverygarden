<?php

namespace Drupal\uel_core\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to add wrapper with a class!
 *
 * @Filter(
 *   id = "filter_wrapper",
 *   title = @Translation("Wrapper"),
 *   description = @Translation("Provides a filter to add wrapper with a class!"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterWrapper extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $wrapper_text = '<div class="rich-txt-custom">' . $text . ' </div>';
    return new FilterProcessResult($wrapper_text);
  }

}
