<?php

class CRM_Activityical_Feed {
  
  protected $contact_id;

  private $url;

  private $hash;

  function  __construct($contact_id = NULL) {
    $this->setContactId($contact_id);
    $this->load();
  }

  private function load() {
    $dao = new CRM_Activityical_DAO_ActivityicalContact();
    $dao->contact_id = $this->contact_id;
    $dao->find();
    $dao->fetch();

    if (!empty($dao->hash)) {
      $this->hash = $dao->hash;
    }
    else {
      $this->generateHash();
    }
  }

  private function setContactId($contact_id = NULL) {
    if (!empty($contact_id) && is_numeric($contact_id)) {
      $this->contact_id = $contact_id;
    }
    else {
      $this->contact_id = CRM_Core_Session::singleton()->getLoggedInContactID();
    }
  }

  public function generateHash() {
    // Ensure we have permission to do this.
    $perm = CRM_Activityical_Permission::singleton(array('contact_id' => $this->contact_id));
    if (!$perm->manageFeedDetails()) {
      CRM_Utils_System::permissionDenied();
    }
    $hash = md5(mt_rand(0, 10000000) . microtime());
    $dao = new CRM_Activityical_DAO_ActivityicalContact();
    $dao->contact_id = $this->contact_id;
    $dao->find();
    $dao->fetch();
    $dao->hash = $hash;
    if ($dao->id) {
      $dao->update();
    }
    else {
      $dao->save();
    }
    $this->hash = $hash;
  }

  public function getUrl() {
    $url_query = array(
      'cid'=> $this->contact_id,
      'key' => $this->hash,
    );
    $url = CRM_Utils_System::url('civicrm/activityical/feed', $url_query, TRUE, NULL, FALSE);
    return $url;
  }
}