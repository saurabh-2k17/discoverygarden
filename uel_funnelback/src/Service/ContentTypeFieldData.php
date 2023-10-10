<?php

namespace Drupal\uel_funnelback\Service;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class to fetch field data from Content type.
 *
 * @package Drupal\uel_funnelback\Service
 */
class ContentTypeFieldData {

  /**
   * Funnelback string replacement for field machine name.
   *
   * @var string[]
   */
  protected $metaReplacementString = [
    'title' => 'search_title',
    'field_featured_content_descripti' => 'search_text',
    'field_featured_content_image' => 'search_image',
    'job_title' => 'search_job_title',
    'location' => 'search_location',
    'phone' => 'search_phone',
    'school' => 'search_school',
    'mail' => 'search_mail',
    'field_course_school' => 'search_course_school',
    'field_course_subject' => 'search_course_subject',
    'field_quick_info_course_location' => 'search_course_location',
    'startDate' => 'search_course_dates',
    'attendance' => 'search_attendance',
    'learning' => 'search_learning',
    'ucasCode' => 'search_ucas_code',
    'courseType' => 'search_course_type',
    'courseOption' => 'search_course_option',
    'field_event_location' => 'search_event_location',
    'start_date' => 'search_start_date',
    'end_date' => 'search_end_date',
    'changed' => 'search_last_published_date',
    'field_course_type' => 'search_course_type',
    'field_course_option' => 'search_course_option',
    'field_course_template_type' => 'search_course_template_type',
    'field_course_clearing_reason' => 'search__clearing_space',
    'field_course_clearing' => 'search_clearing',
  ];

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ContentTypeFieldData constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(DateFormatterInterface $date_formatter, ConfigFactoryInterface $config_factory) {
    $this->dateFormatter = $date_formatter;
    $this->configFactory = $config_factory;
  }

  /**
   * Function to get data from node fields.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param array $content_type_fields
   *   Field names of content types to fetch data from.
   *
   * @return array|null
   *   return the field data as a key value array.
   */
  public function getContentTypeFieldsData($node, array $content_type_fields) {
    $fieldsdata = [];
    foreach ($content_type_fields as $field) {
      switch ($field) {
        case 'field_featured_content_descripti':
        case 'title':
        case 'field_course_template_type':
          $fieldsdata[$field] = $this->getDirectFieldData($node, $field);
          break;

        case 'field_featured_content_image':
          $fieldsdata[$field] = $this->getReferencedImageFieldData($node, $field);
          break;

        case 'field_course_school':
        case 'field_course_subject':
        case 'field_quick_info_course_location':
        case 'field_course_type':
          $fieldsdata[$field] = $this->getTaxonomyReferencedFieldData($node, $field);
          break;

        case 'field_study_options':
          $fieldsdata[$field] = $this->getStudyOptionsData($node, $field);
          break;

        case 'field_event_date':
          $fieldsdata[$field] = $this->getEventDates($node, $field);
          break;

        case 'field_event_location':
          $fieldsdata[$field] = $this->getReferencedContentData($node, $field);
          break;

        case 'field_tags':
          $fieldsdata[$field] = $this->getFieldTagsData($node, $field);
          break;

        case 'field_staff_profile':
          $fieldsdata[$field] = $this->getPeopleProfileData($node, $field);
          break;

        case 'changed':
          $fieldsdata[$field] = $this->getDateFieldData($node, $field);
          break;

        case 'field_course_option':
          $fieldsdata[$field] = $this->getCourseOptionData($node, $field);
          break;

        case 'field_course_clearing':
          $fieldsdata[$field] = $this->getCourseClearingData($node, $field);
          break;

        case 'field_course_clearing_reason':
          $fieldsdata[$field] = $this->getCourseClearingReasonData($node, $field);
          break;

        default:
          break;
      }
    }
    return $fieldsdata;
  }

  /**
   * Function to get data from direct field.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Field name of content types to fetch data from.
   *
   * @return null|string
   *   return the field data value if present.
   */
  private function getDirectFieldData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->value)) {
      return $node->$content_type_field->value;
    }
    return NULL;
  }

  /**
   * Function to get URL from referenced image field.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Image field name of content types to fetch data from.
   *
   * @return null|string
   *   return the field data value as URL.
   */
  private function getReferencedImageFieldData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity) && !empty($node->$content_type_field->entity->image->entity)) {
      return file_create_url($node->$content_type_field->entity->image->entity->uri->value);
    }
    return NULL;
  }

  /**
   * Function to get taxonomy name from referenced field.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Taxonomy Referenced field name to get data from.
   *
   * @return null|string
   *   return the taxonomy name.
   */
  private function getTaxonomyReferencedFieldData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity)) {
      return $node->$content_type_field->entity->name->value;
    }
    return NULL;
  }

  /**
   * Get data fromm study options paragraph.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Referenced field name to get data from.
   *
   * @return array|null
   *   returns array of study option data.
   */
  private function getStudyOptionsData($node, $content_type_field) {
    $studyOption = [];
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity)) {
      foreach ($node->$content_type_field->referencedEntities() as $studyEntities) {
        foreach ($studyEntities->field_apply_now_links->referencedEntities() as $field_apply_now_links) {
          foreach ($field_apply_now_links->field_start_date->referencedEntities() as $field_start_date) {
            $studyOption['startDate'][$studyEntities->id()][] = $field_start_date->name->value;
          }
        }
        if ($studyEntities->hasField('field_attendance')) {
          $studyOption['attendance'][$studyEntities->id()][] = $this->getAllTerms($studyEntities->field_attendance->referencedEntities());
        }
        if ($studyEntities->hasField('field_learning_modes')) {
          $studyOption['learning'][] = $this->getAllTerms($studyEntities->field_learning_modes->referencedEntities());
        }
        if ($node->hasField('field_course_template_type')) {
          if ((empty($node->field_course_template_type->value)) || ($node->field_course_template_type->value === 'Undergraduate')) {
            $studyOption['ucasCode'][] = ($studyEntities->hasField('field_ucas_code') && $studyEntities->field_ucas_code->value) ? $studyEntities->field_ucas_code->value : '';
          }
        }
        else {
          $studyOption['ucasCode'][] = ($studyEntities->hasField('field_ucas_code') && $studyEntities->field_ucas_code->value) ? $studyEntities->field_ucas_code->value : '';
        }
        $studyOption['courseType'][] = ($studyEntities->hasField('field_course_type') && $studyEntities->field_course_type->entity) ? $studyEntities->field_course_type->entity->name->value : '';
        if ($studyEntities->hasField('field_course_option') && !is_null($studyEntities->field_course_option->value)) {
          $studyOption['courseOption'][] = $this->getCourseOption($studyEntities->field_course_option->value);
        }
      }
      return $studyOption;
    }
    return NULL;
  }

  /**
   * Function to get all event dates.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Referenced field name to get data from.
   *
   * @return array|null
   *   returns an array of start and end dates.
   */
  private function getEventDates($node, $content_type_field) {
    $dateValues = [];
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->value)) {
      foreach ($node->$content_type_field as $dateEntities) {
        $dateValues['start_date'][] = $this->dateFormatter->format($dateEntities->value, 'custom', 'Y-m-d');
        $dateValues['end_date'][] = $this->dateFormatter->format($dateEntities->end_value, 'custom', 'Y-m-d');
      }
      $key = array_keys($dateValues['start_date'], min($dateValues['start_date']));
      $dateValues["start_date"] = min($dateValues["start_date"]);
      $dateValues["end_date"] = $dateValues['end_date'][$key[0]];
      return $dateValues;
    }
    return NULL;
  }

  /**
   * Function to get formatted updated date.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Field name to get data from.
   *
   * @return string|null
   *   return a string of formatted date.
   */
  private function getDateFieldData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->value)) {
      return $this->dateFormatter->format($node->$content_type_field->value, 'custom', 'Y-m-d');
    }
    return NULL;
  }

  /**
   * Function to get content referenced data.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Referenced field name to get data from.
   *
   * @return null|string
   *   returns the referenced field data.
   */
  private function getReferencedContentData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity)) {
      if ($content_type_field === 'field_event_location') {
        return $node->$content_type_field->entity->field_location_name->value;
      }
      return $node->$content_type_field->entity->title->value;
    }
    return NULL;
  }

  /**
   * Function to get the tag names.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Referenced field name to get data from.
   *
   * @return array|null
   *   returns an array of tag names.
   */
  private function getFieldTagsData($node, $content_type_field) {
    $tagValues = [];
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity)) {
      foreach ($node->$content_type_field->referencedEntities() as $tagEntities) {
        $tagValues[] = $tagEntities->name->value;
      }
      return $tagValues;
    }
    return NULL;
  }

  /**
   * Function to get People profile data.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Referenced field name to get data from.
   *
   * @return array|null
   *   return an array of user details.
   */
  private function getPeopleProfileData($node, $content_type_field) {
    $profileData = [];
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->entity)) {
      $userEntity = $node->$content_type_field->entity;
      $profileData['job_title'] = ($userEntity->hasField('field_jobtitle') && $userEntity->field_jobtitle->value) ? $userEntity->field_jobtitle->value : '';
      $profileData['location'] = ($userEntity->hasField('field_location') && $userEntity->field_location->value) ? $userEntity->field_location->value : '';
      $profileData['phone'] = ($userEntity->hasField('field_telephone') && $userEntity->field_telephone->value) ? $userEntity->field_telephone->value : '';
      $profileData['school'] = ($userEntity->hasField('field_school') && $userEntity->field_school->value) ? $userEntity->field_school->value : '';
      $profileData['mail'] = $userEntity->mail->value;
      return $profileData;
    }
    return NULL;
  }

  /**
   * Function to create funnelback meta structure.
   *
   * @param array $fieldData
   *   Array of details to structure.
   *
   * @return array
   *   return an array of structured meta.
   */
  public function createMetaStructure(array $fieldData) {
    $meta = [];
    foreach ($fieldData as $field_name => $value) {
      if (in_array($field_name, array_keys($this->metaReplacementString)) && !empty($fieldData[$field_name])) {
        $meta[] = [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'funnelback:' . $this->metaReplacementString[$field_name],
            'content' => $fieldData[$field_name],
          ],
        ];
      }
      else {
        switch ($field_name) {
          case 'field_staff_profile':
            foreach ($fieldData[$field_name] as $key => $value) {
              if (!empty($value)) {
                $meta[] = [
                  '#tag' => 'meta',
                  '#attributes' => [
                    'name' => 'funnelback:' . $this->metaReplacementString[$key],
                    'content' => $value,
                  ],
                ];
              }
            }
            break;

          case 'field_study_options':
            if (!empty($fieldData["field_study_options"])) {
              foreach ($fieldData[$field_name] as $field => $fieldValue) {
                if (!empty($fieldValue)) {
                  if ($field === 'attendance') {
                    foreach ($fieldValue as $attendance) {
                      $meta[] = [
                        '#tag' => 'meta',
                        '#attributes' => [
                          'name' => 'funnelback:' . $this->metaReplacementString[$field],
                          'content' => $attendance[0],
                        ],
                      ];
                    }
                  }
                  elseif ($field === 'startDate') {
                    foreach ($fieldValue as $dates) {
                      foreach ($dates as $date) {
                        $meta[] = [
                          '#tag' => 'meta',
                          '#attributes' => [
                            'name' => 'funnelback:' . $this->metaReplacementString[$field],
                            'content' => $date,
                          ],
                        ];
                      }
                    }
                  }
                  else {
                    if (is_array($fieldValue)) {
                      foreach ($fieldValue as $value) {
                        $meta[] = [
                          '#tag' => 'meta',
                          '#attributes' => [
                            'name' => 'funnelback:' . $this->metaReplacementString[$field],
                            'content' => $value,
                          ],
                        ];
                      }
                    }
                    else {
                      $meta[] = [
                        '#tag' => 'meta',
                        '#attributes' => [
                          'name' => 'funnelback:' . $this->metaReplacementString[$field],
                          'content' => $fieldValue,
                        ],
                      ];
                    }
                  }
                }
              }
            }
            break;

          case 'field_event_date':
            foreach ($fieldData[$field_name] as $field => $fieldValue) {
              if (!empty($fieldValue)) {
                $meta[] = [
                  '#tag' => 'meta',
                  '#attributes' => [
                    'name' => 'funnelback:' . $this->metaReplacementString[$field],
                    'content' => $fieldValue,
                  ],
                ];
              }
            }
            break;

          case 'field_tags':
            if (!empty($fieldData[$field_name])) {
              foreach ($fieldData[$field_name] as $tagTitle) {
                $meta[] = [
                  '#tag' => 'meta',
                  '#attributes' => [
                    'name' => 'funnelback:search_tags',
                    'content' => $tagTitle,
                  ],
                ];
              }
            }
            break;

          default:
            break;
        }
      }
    }
    return $meta;
  }

  /**
   * Get comma separated string of taxonomy terms.
   *
   * @param array $entities
   *   Referenced taxonomy terms.
   *
   * @return string|null
   *   returns string of taxonomy terms.
   */
  public function getAllTerms(array $entities) {
    if (empty($entities)) {
      return '';
    }
    $term = [];
    foreach ($entities as $entity) {
      $term[] = $entity->name->value;
    }
    return implode(', ', $term);
  }

  /**
   * Function to get the course option data from course option field.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Field name of course option to fetch data from.
   *
   * @return null|string
   *   return the field data value if present.
   */
  private function getCourseOptionData($node, $content_type_field) {
    if ($node->hasField($content_type_field) && !empty($node->$content_type_field->value)) {
      return $this->getCourseOption($node->$content_type_field->value);
    }
    return NULL;
  }

  /**
   * Return the formatted value of course option.
   *
   * @param string $value
   *   Course option value.
   *
   * @return string
   *   returns formatted value of course option.
   */
  public function getCourseOption(string $value) {
    if ($value === 'FullTime') {
      return 'full-time';
    }
    if ($value === 'PartTime') {
      return 'part-time';
    }
    return 'full-time, part-time';
  }

  /**
   * Function to get the course clearing value.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Field name of course option to fetch data from.
   *
   * @return null|string
   *   return the field data value if present.
   */
  public function getCourseClearingData($node, $content_type_field) {
    $config = $this->configFactory->get('uel_custom_config.clearing_message_config');
    $return = NULL;
    if ($config->get('clearing_message_check') && $node->hasField($content_type_field)) {
      if ($node->$content_type_field->value === "1") {
        $return = "CLEARING";
      }
    }
    return $return;
  }

  /**
   * Function to get the course clearing value based on value of clearing field.
   *
   * @param object $node
   *   Node Object to get data from.
   * @param string $content_type_field
   *   Field name of course option to fetch data from.
   *
   * @return null|string
   *   return the field data value if present.
   */
  public function getCourseClearingReasonData($node, $content_type_field) {
    $config = $this->configFactory->get('uel_custom_config.clearing_message_config');
    $return = NULL;
    if ($config->get('clearing_message_check') && $node->hasField($content_type_field)) {
      if ($node->field_course_clearing->value === "1") {
        $return = $this->getTaxonomyReferencedFieldData($node, $content_type_field);
      }
    }
    return $return;
  }

}
