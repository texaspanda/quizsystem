<?php

namespace Drupal\ttslogic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class UserForm.
 *
 * Contains \Drupal\ttslogic\Form\UserForm.
 *
 * @package Drupal\ttslogic\Form
 */
class UserForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $terms_level = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('level');
    $terms_role = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('role');
    foreach ($terms_level as $level_term) {
      $tid_level = $level_term->tid;
      $name_level = $level_term->name;
      $select_level[$tid_level] = $name_level;
    }
    foreach ($terms_role as $role_term) {
      $tid_role = $role_term->tid;
      $name_role = $role_term->name;
      $select_role[$tid_role] = $name_role;
    }
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#required' => TRUE,
    ];
    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];
    $form['role'] = [
      '#type' => 'select',
      '#options' => $select_role,
      '#title' => $this->t('Role'),
      '#required' => TRUE,
    ];
    $form['level'] = [
      '#type' => 'select',
      '#options' => $select_level,
      '#title' => $this->t('Level'),
      '#required' => TRUE,
    ];

    // Load all active tests and add to select.
    $query = \Drupal::entityQuery('group');
    $query->condition('type', 'learning_path');
    $query->condition('field_learning_path_published', 1);
    $gids = $query->execute();
    if ($gids) {
      $learning_path = [];
      foreach ($gids as $id) {
        $group = \Drupal\group\Entity\Group::load($id);
        $learning_path[$id] = $group->get('label')->value;
      }
      $form['learning_path'] = [
        '#type' => 'select',
        '#options' => $learning_path,
        '#title' => $this->t('Kind of test'),
        '#required' => TRUE,
      ];
    }

    $form['generate'] = [
      '#type' => 'submit',
      '#value' => 'Generate new user',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ttsuser_form';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('first_name')) < 3) {
      $form_state->setErrorByName('first_name', $this->t('Please enter a full name (3+ chars)'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
    $nums = "1234567890";
    $rand_login_salt_lenght = rand(3, 5);
    $pass_lenght = rand(10, 20);
    $login_salt_elems = [];
    $pass_elems = [];
    for ($i = 0; $i < $rand_login_salt_lenght; $i++) {
      $n = rand(0, strlen($nums));
      $login_salt_elems[] = $nums[$n];
    }
    for ($i = 0; $i < $pass_lenght; $i++) {
      $n = rand(0, strlen($chars));
      $pass_elems[] = $chars[$n];
    }
    $login_salt = implode($login_salt_elems);
    $login = $form_state->getValue('first_name') . '_' . $form_state->getValue('last_name') . $login_salt;
    $password = implode($pass_elems);
    $values = [
      'field_first_name' => $form_state->getValue('first_name'),
      'field_last_name' => $form_state->getValue('last_name'),
      'field_role' => $form_state->getValue('role'),
      'field_level' => $form_state->getValue('level'),
      'mail' => $form_state->getValue('email'),
      'pass' => $password,
      'name' => $login,
      'status' => 1,
      'roles' => 'be_candidate',
    ];
    $account = User::create($values);
    $account->save();

    $learning_path = $form_state->getValue('learning_path');
    $learning_path = \Drupal\group\Entity\Group::load($learning_path);
    $learning_path->addMember($account);
    // Sending email to candidate with login and password of account and set message to admin.
    $mail_manager = \Drupal::service('plugin.manager.mail');
    $module = "ttslogic";
    $key = "ttslogic_email_candidate_generate";
    $langcode = 'en';
    $send = TRUE;
    $to = $values['mail'];
    $params['user_email'] = $values['mail'];
    $params['login'] = $login;
    $params['password'] = $password;
    $mail_manager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    \Drupal::messenger()->addMessage("Login: $login Password: $password");
  }

}
