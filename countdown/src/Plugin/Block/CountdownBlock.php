<?php

namespace Drupal\countdown\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\countdown\Countdownservice;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Countdown' Block.
 *
 * @Block(
 *   id = "countdown_block",
 *   admin_label = @Translation("Countdown block"),
 *   category = @Translation("Countdown"),
 * )
 */
class CountdownBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

    /**
     * The date calculator
     *
     * @var \Drupal\countdown\Countdownservice
     */
    protected $countdownservice;

    /**
     * Constructs an CountdownserviceBlock object.
     *
     * @param array $configuration
     *   The block configuration.
     * @param string $plugin_id
     *   The ID of the plugin.
     * @param mixed $plugin_definition
     *   The plugin definition.
     * @param \Drupal\countdown\Countdownservice $countdownservice
     *   Date comparrisons.
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        Countdownservice $countdownservice
        ) {
            parent::__construct($configuration, $plugin_id, $plugin_definition);
            $this->countdownservice = $countdownservice;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('countdown.countdown_service')
            );
    }

  /**
   * {@inheritdoc}
   */
  public function build() {
      $config = $this->getConfiguration();

      // Get node for the current page
      $node = \Drupal::routeMatch()->getParameter('node');

      // Set variable for the block to be only shown in "event" pages
      $nodeType = 'event';

      // Check if a current page is a node
      if ($node instanceof \Drupal\node\NodeInterface) {
          $nid = $node->id();
          $nType= $node->getType();

          // Get submitted date in countdown block. If empty provide the current date.
          if (!empty($config['countdown_block_date'])) {
              $date = $config['countdown_block_date'];
          }
          else {
              $date = date("Y-m-d");
          }

          // Get the event date
            // Get event date object
            //$eventDate = $node->get('field_event_date')->getValue();

            // Get event date value
            $eventDate = $node->field_event_date->value;

          // Compare the countdown_block_date and event date and get the string
          $dateStrig = $this->countdownservice->compareDateStrings($date, $eventDate);

          if($nType == $nodeType){
            return array(
                  '#markup' => $this->t($dateStrig),
            );
          }
      }
    //return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
      $form = parent::blockForm($form, $form_state);

      $config = $this->getConfiguration();

      $form['countdown_block_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Who'),
          '#description' => $this->t('Who do you want to say hello to?'),
          '#default_value' => isset($config['countdown_block_name']) ? $config['countdown_block_name'] : '',
      ];

      // Creating the date/time element starts here

      // Provide a default date in the format YYYY-MM-DD HH:MM:SS.
      $date = isset($config['countdown_block_date']) ? $config['countdown_block_date'] : date('Y-m-d H:i:s');

      // Provide a format using regular PHP format parts (see documentation on php.net).
      // If you're using a date_select, the format will control the order of the date parts in the selector,
      // rearrange them any way you like. Parts left out of the format will not be displayed to the user.
      $format = 'YY-MM-DD HH:MM:SS';

      $form['countdown_block_date'] = array(
          '#type' => 'date', // types 'date_popup', 'date_text' and 'date_timezone' are also supported. See .inc file.
          '#title' => $this->t('Date'),
          '#default_value' => $date,
          '#date_format' => $format,
          '#date_label_position' => 'within', // See other available attributes and what they do in date_api_elements.inc
          '#date_timezone' => 'Europe/Ljubljana', // Optional, if your date has a timezone other than the site timezone.
          //'#date_increment' => 15, // Optional, used by the date_select and date_popup elements to increment minutes and seconds.
          '#date_year_range' => '-3:+3', // Optional, used to set the year range (back 3 years and forward 3 years is the default).
          '#datepicker_options' => array(), // Optional, as of 7.x-2.6+, used to pass in additional parameters from the jQuery Datepicker widget.

      );

      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
      $this->configuration['countdown_block_name'] = $form_state->getValue('countdown_block_name');
      $this->configuration['countdown_block_date'] = $form_state->getValue('countdown_block_date');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
      $default_config = \Drupal::config('countdown.settings');
      return [
          'countdown_block_name' => $default_config->get('countdown.name'),
          'countdown_block_date' => $default_config->get('countdown.date'),
      ];
  }

  /**
   * Prevent the block from being cached
   * @return number
   */
  public function getCacheMaxAge() {
      return 0;
  }
}
