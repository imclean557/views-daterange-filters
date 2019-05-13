<?php

namespace Drupal\views_daterange_filters\Plugin\views\filter;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime\Plugin\views\filter\Date;

/**
 * Date/time views filter.
 *
 * Even thought dates are stored as strings, the numeric filter is extended
 * because it provides more sensible operators.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("views_daterange_filters_daterange")
 */
class ViewsDaterangeFiltersDateRange extends Date implements ContainerFactoryPluginInterface {
  
  public function operators() {
    $operators = parent::operators();
    $operators['includes'] = [
      'title' => $this->t('Range includes'),
      'method' => 'opIncludes',
      'short' => $this->t('includes'),
      'values' => 1,
    ];
    
    return $operators;
  }
  
  protected function opIncludes($field) {    
    $end_field = substr($field, 0, -6) . '_end_value';

    $timezone = $this->getTimezone();
    $origin_offset = $this->getOffset($this->value['value'], $timezone);

    // Convert to ISO. UTC timezone is used since dates are stored in UTC.
    $value = new DateTimePlus($this->value['value'], new \DateTimeZone($timezone));
    $value = $this->query->getDateFormat($this->query->getDateField("'" . $this->dateFormatter->format($value->getTimestamp() + $origin_offset, 'custom', DateTimeItemInterface::DATETIME_STORAGE_FORMAT, DateTimeItemInterface::STORAGE_TIMEZONE) . "'", TRUE, $this->calculateOffset), $this->dateFormat, TRUE);
    
    $field = $this->query->getDateFormat($this->query->getDateField($field, TRUE, $this->calculateOffset), $this->dateFormat, TRUE);
    $end_field = $this->query->getDateFormat($this->query->getDateField($end_field, TRUE, $this->calculateOffset), $this->dateFormat, TRUE);
    
    // This is safe because we are manually scrubbing the value.   
    $this->query->addWhereExpression($this->options['group'], "$value BETWEEN $field AND $end_field");
  }

}
