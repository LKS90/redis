<?php

/**
 * @file
 * Contains \Drupal\redis\Form\RedisSettingsForm.
 */

namespace Drupal\redis\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Main settings and review administration screen.
 */
class RedisSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redis_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->configFactory->get('redis');

    $form['connection'] = array(
      '#type' => 'fieldset',
      '#title' => t("Connection information"),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['connection']['scheme'] = array(
      '#type' => 'textfield',
      '#title' => t("Scheme"),
      '#default_value' => 'tcp',
      '#disabled' => TRUE,
      '#description' => t("Connection scheme.") . " " . t("Only <em>tcp</em> is currently supported."),
    );
    $form['connection']['host'] = array(
      '#type' => 'textfield',
      '#title' => t("Host"),
      '#default_value' => $config->get('connection.host'),
      //'#description' => t("Redis server host. Default is <em>@default</em>.", array('@default' => Redis_Client::REDIS_DEFAULT_HOST)),
    );
    $form['connection']['port'] = array(
      '#type' => 'textfield',
      '#title' => t("Port"),
      '#default_value' => $config->get('connection.port'),
      //'#description' => t("Redis server port. Default is <em>@default</em>.", array('@default' => Redis_Client::REDIS_DEFAULT_PORT)),
    );
    $form['connection']['base'] = array(
      '#type' => 'textfield',
      '#title' => t("Database"),
      '#default_value' => $config->get('connection.base'),
      '#description' => t("Redis server database. Default is none, Redis server will autoselect the database 0."),
    );
    $form['connection']['interface'] = array(
      '#type' => 'radios',
      '#title' => t("Client"),
      '#options' => array(
        'auto' => t("None or automatic"),
        'PhpRedis' => t("PhpRedis PHP extension"),
        'Predis' => t("Predis PHP library"),
      ),
      '#default_value' => $config->get('connection.interface'),
      '#description' => t("Redis low level backend."),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = $this->configFactory->get('redis');

    $string_values = array('host', 'interface');
    foreach ($string_values as $name) {
      // Empty check is sufficient to verify that the field is indeed empty.
      if (empty($form_state['values'][$name])) {
        $config->clear('connection.' . $name);
      }
      else {
        $config->set('connection.' . $name);
      }
    }

    $numeric_values = array('base', 'port');
    foreach ($numeric_values as $name) {
      // Numeric values can be both of NULL or 0 (NULL meaning the value is not
      // not set and the client will use the default, while 0 has a business
      // meaning and should be kept as is).
      if ('0' !== $form_state['values'][$name] && empty($form_state['values'][$name])) {
        $config->clear('connection.' . $name);
      }
      else {
        $config->set('connection.' . $name);
      }
    }
  }

}