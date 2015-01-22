<?php

class Admin_Profile_Simple extends Admin_Profile {
   private   $profileStorage;
   private   $profileCrossref;

   public function __construct() {
      $this->profileCrossref['Admin_Auth']['edgar'] = 0;
      $this->profileStorage[0] = Array(
         "firstname" => "Edgar",
         "surname" => "Figaro",
         "email" => "edgar@figaro.ff",
         "active" => true,
         "lastLogin" => "1994-04-02 06:10:50"
      );
      parent::__construct();
   }

   // Create a new profile entry
   // @return int profileId
   protected function create($user) {
      $m = $user->getAuthModule();
      $r = $user->getExternalReference();
      $this->profileStorage[] = Array(
         "firstname" => $user->getParam("firstname"),
         "surname" => $user->getParam("surname")
         "email" => $user->getParam("mail"),
         "active" => $user->getParam("active"),
         "lastLogin" => date("Y-m-d H:i:s")
      );
      end($this->profileStorage);
      $profileId = key($this->profileStorage);
      $this->profileCrossref[$m][$r] = $profileId;
      return $profileId;
   }

   // Check if profile exists or not
   // @param int profile number
   // @return boolean
   public function idExists($profileId) {
      return isset($this->profileStorage[$profileId]);
   }

   // Get the profile number by suppling authentication module name and its reference ID.
   // @return int profile ID or -1 if not found.
   public function getIdByCrossref($module, $ref) {
      if (isset($this->profileCrossref[$module][$ref])) {
         return $this->profileCrossref[$module][$ref];
      }
      else {
         return -1;
      }
   }

   // Sets profile data from database, by given profile ID.
   // @return void
   public function setById($profileId) {
      $data = $this->profileStorage[$profileId];
      $this->profileId = $profileId;
      $this->firstname = $data['firstname'];
      $this->surname = $data['surname'];
      $this->email = $data['email'];
      $this->active = $data['active'];
      $this->lastLogin = $data['lastLogin'];
   }

   // Update a profile with its user details. This is to keep profile in sync with
   // authententication engine. An example is when a person's details change in
   // Active Directory, we want to keep the profile details to match that.
   // Should be called after login, by the authentication module.
   // @return boolean
   public function setFromUser($user) {
      $profileId = $this->profileId;
      $this->profileStorage[$profileId]['firstname'] = $user->getParam("firstname");
      $this->profileStorage[$profileId]['surname'] = $user->getParam("surname");
      $this->profileStorage[$profileId]['email'] = $user->getParam("mail");
      $this->profileStorage[$profileId]['active'] = $user->getParam("active");
      $this->setById($profileId);
   }
}
?>